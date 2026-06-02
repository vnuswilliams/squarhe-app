const textEncoder = new TextEncoder();
const textDecoder = new TextDecoder();

const OFFLINE_STORES = ['employees', 'companies', 'leaves', 'overtimes', 'remunerations', 'payslips', 'documents', 'payroll_closures'];

const requestToPromise = (request) => new Promise((resolve, reject) => {
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
});

const transactionDone = (transaction) => new Promise((resolve, reject) => {
    transaction.oncomplete = () => resolve();
    transaction.onerror = () => reject(transaction.error);
    transaction.onabort = () => reject(transaction.error);
});

export const OfflineEngine = {
    db: null,
    cryptoKey: null,
    hmacKey: null,
    dbName: 'SquarheOfflineDB',
    dbVersion: 3,
    stores: OFFLINE_STORES,

    async init() {
        if (!('indexedDB' in window) || !window.crypto?.subtle) {
            throw new Error('Offline storage requires IndexedDB and WebCrypto support.');
        }

        this.db = await this.openDB();
        this.cryptoKey = await this.getOrCreateKey('device_encryption_key', { name: 'AES-GCM', length: 256 }, ['encrypt', 'decrypt']);
        this.hmacKey = await this.getPersistentKey('device_hmac_key');
    },

    openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                if (!db.objectStoreNames.contains('data')) {
                    db.createObjectStore('data', { keyPath: 'id' });
                }

                if (!db.objectStoreNames.contains('records')) {
                    const store = db.createObjectStore('records', { keyPath: 'key' });
                    store.createIndex('store', 'store', { unique: false });
                    store.createIndex('status', 'status', { unique: false });
                    store.createIndex('store_status', ['store', 'status'], { unique: false });
                    store.createIndex('updated_at', 'updated_at', { unique: false });
                }

                if (!db.objectStoreNames.contains('outbox')) {
                    const store = db.createObjectStore('outbox', { keyPath: 'id' });
                    store.createIndex('status', 'status', { unique: false });
                    store.createIndex('created_at', 'created_at', { unique: false });
                }

                if (!db.objectStoreNames.contains('meta')) {
                    db.createObjectStore('meta');
                }
            };

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    },

    async getMeta(name) {
        const transaction = this.db.transaction('meta', 'readonly');
        return requestToPromise(transaction.objectStore('meta').get(name));
    },

    async setMeta(name, value) {
        const transaction = this.db.transaction('meta', 'readwrite');
        transaction.objectStore('meta').put(value, name);
        await transactionDone(transaction);
    },

    async getOrCreateKey(name, config, usages) {
        const savedKey = await this.getMeta(name);

        if (savedKey) {
            return savedKey;
        }

        const newKey = await window.crypto.subtle.generateKey(config, false, usages);
        await this.setMeta(name, newKey);

        return newKey;
    },

    async getPersistentKey(name) {
        return this.getMeta(name);
    },

    async setHMACSecret(rawSecret) {
        this.hmacKey = await window.crypto.subtle.importKey(
            'raw',
            textEncoder.encode(rawSecret),
            { name: 'HMAC', hash: 'SHA-256' },
            false,
            ['sign'],
        );

        await this.setMeta('device_hmac_key', this.hmacKey);
    },

    canonicalStringify(value) {
        if (value === null || typeof value !== 'object') {
            return JSON.stringify(value);
        }

        if (Array.isArray(value)) {
            return `[${value.map((item) => this.canonicalStringify(item)).join(',')}]`;
        }

        return `{${Object.keys(value).sort().map((key) => `${JSON.stringify(key)}:${this.canonicalStringify(value[key])}`).join(',')}}`;
    },

    async encrypt(data) {
        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        const ciphertext = await window.crypto.subtle.encrypt(
            { name: 'AES-GCM', iv },
            this.cryptoKey,
            textEncoder.encode(JSON.stringify(data)),
        );

        return {
            iv: Array.from(iv),
            blob: ciphertext,
        };
    },

    async decrypt(encryptedData) {
        const decrypted = await window.crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: new Uint8Array(encryptedData.iv) },
            this.cryptoKey,
            encryptedData.blob,
        );

        return JSON.parse(textDecoder.decode(decrypted));
    },

    async signPayload(payload) {
        if (!this.hmacKey) {
            throw new Error('HMAC key is not initialized.');
        }

        const signature = await window.crypto.subtle.sign(
            'HMAC',
            this.hmacKey,
            textEncoder.encode(this.canonicalStringify(payload)),
        );

        return Array.from(new Uint8Array(signature))
            .map((byte) => byte.toString(16).padStart(2, '0'))
            .join('');
    },

    async verifyServerSignature(data, signature) {
        if (!signature) {
            return false;
        }

        const expected = await this.signPayload(data);
        return expected === signature;
    },

    keyFor(storeName, id) {
        return `${storeName}:${id}`;
    },

    async saveBatch(storeName, items, status = 'synced') {
        if (!Array.isArray(items) || items.length === 0) {
            return;
        }

        const transaction = this.db.transaction('records', 'readwrite');
        const store = transaction.objectStore('records');

        for (const item of items) {
            if (!item?.id) {
                continue;
            }

            const encrypted = await this.encrypt(item);
            store.put({
                key: this.keyFor(storeName, item.id),
                id: item.id,
                store: storeName,
                ...encrypted,
                status,
                updated_at: item.updated_at || new Date().toISOString(),
            });
        }

        await transactionDone(transaction);
    },

    async saveSnapshot(snapshot) {
        const datasets = snapshot?.datasets || {};
        const transaction = this.db.transaction('meta', 'readwrite');
        transaction.objectStore('meta').put(snapshot.server_time || Date.now(), 'last_snapshot_at');
        await transactionDone(transaction);

        await Promise.all(Object.entries(datasets).map(([storeName, items]) => this.saveBatch(storeName, items)));
    },

    async saveLocal(storeName, id, data, operation = 'upsert') {
        if (!id) {
            throw new Error('A local record id is required.');
        }

        const now = new Date().toISOString();
        const encrypted = await this.encrypt({ ...data, id, updated_at: data.updated_at || now });
        const transaction = this.db.transaction('records', 'readwrite');

        transaction.objectStore('records').put({
            key: this.keyFor(storeName, id),
            id,
            store: storeName,
            ...encrypted,
            operation,
            status: 'pending',
            updated_at: now,
        });

        await transactionDone(transaction);
    },

    async getPendingData() {
        const transaction = this.db.transaction('records', 'readonly');
        const index = transaction.objectStore('records').index('status');
        const records = await requestToPromise(index.getAll('pending'));
        const pending = [];

        for (const record of records) {
            try {
                pending.push({
                    id: record.id,
                    store: record.store,
                    operation: record.operation || 'upsert',
                    updated_at: record.updated_at,
                    data: await this.decrypt(record),
                });
            } catch (error) {
                console.error('Failed to decrypt pending record', record.key, error);
            }
        }

        return pending;
    },

    async markSynced(acks = []) {
        if (!Array.isArray(acks) || acks.length === 0) {
            return;
        }

        const transaction = this.db.transaction('records', 'readwrite');
        const store = transaction.objectStore('records');

        for (const ack of acks) {
            const key = ack.key || this.keyFor(ack.store || 'employees', ack.id || ack);
            const record = await requestToPromise(store.get(key));

            if (record) {
                record.status = 'synced';
                record.operation = null;
                record.updated_at = ack.synced_at || new Date().toISOString();
                store.put(record);
            }
        }

        await transactionDone(transaction);
    },

    async getAll(storeName = 'employees') {
        const transaction = this.db.transaction('records', 'readonly');
        const records = await requestToPromise(transaction.objectStore('records').index('store').getAll(storeName));
        const decrypted = [];

        for (const item of records) {
            try {
                const data = await this.decrypt(item);
                decrypted.push({ ...data, _status: item.status, _id: item.id, _store: item.store });
            } catch (error) {
                console.error('Failed to decrypt item', item.key, error);
            }
        }

        return decrypted;
    },

    async getById(id, storeName = 'employees') {
        const transaction = this.db.transaction('records', 'readonly');
        const item = await requestToPromise(transaction.objectStore('records').get(this.keyFor(storeName, id)));

        if (!item) {
            return null;
        }

        return this.decrypt(item);
    },
};
