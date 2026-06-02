// resources/js/offline-engine.js

export const OfflineEngine = {
    db: null,
    cryptoKey: null,
    hmacKey: null,
    dbName: 'SquarheOfflineDB',

    async init() {
        this.db = await this.openDB();
        this.cryptoKey = await this.getOrCreateKey('device_encryption_key', { name: "AES-GCM", length: 256 }, ["encrypt", "decrypt"]);
        this.hmacKey = await this.getPersistentKey('device_hmac_key');
        console.log("🔐 Offline Engine Initialized.");
    },

    openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, 1);
            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('data')) db.createObjectStore('data', { keyPath: 'id' });
                if (!db.objectStoreNames.contains('meta')) db.createObjectStore('meta');
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    },

    async getOrCreateKey(name, config, usages) {
        const tx = this.db.transaction('meta', 'readonly');
        const store = tx.objectStore('meta');
        const savedKey = await new Promise(r => {
            const req = store.get(name);
            req.onsuccess = () => r(req.result);
        });

        if (savedKey) return savedKey;

        const newKey = await window.crypto.subtle.generateKey(config, false, usages);

        const txSave = this.db.transaction('meta', 'readwrite');
        txSave.objectStore('meta').put(newKey, name);
        
        return newKey;
    },

    async getPersistentKey(name) {
        const tx = this.db.transaction('meta', 'readonly');
        const store = tx.objectStore('meta');
        return await new Promise(r => {
            const req = store.get(name);
            req.onsuccess = () => r(req.result);
        });
    },

    async setHMACSecret(rawSecret) {
        const encoder = new TextEncoder();
        const keyData = encoder.encode(rawSecret);

        this.hmacKey = await window.crypto.subtle.importKey(
            "raw",
            keyData,
            { name: "HMAC", hash: "SHA-256" },
            false,
            ["sign"]
        );

        const tx = this.db.transaction('meta', 'readwrite');
        tx.objectStore('meta').put(this.hmacKey, 'device_hmac_key');
    },

    async encrypt(data) {
        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        const encoded = new TextEncoder().encode(JSON.stringify(data));
        
        const ciphertext = await window.crypto.subtle.encrypt(
            { name: "AES-GCM", iv: iv },
            this.cryptoKey,
            encoded
        );

        return {
            iv: Array.from(iv),
            blob: ciphertext
        };
    },

    async decrypt(encryptedData) {
        const decrypted = await window.crypto.subtle.decrypt(
            { name: "AES-GCM", iv: new Uint8Array(encryptedData.iv) },
            this.cryptoKey,
            encryptedData.blob
        );

        return JSON.parse(new TextDecoder().decode(decrypted));
    },

    async signPayload(payload) {
        if (!this.hmacKey) throw new Error("HMAC Key not initialized.");

        const encoder = new TextEncoder();
        const data = encoder.encode(JSON.stringify(payload));
        
        const signature = await window.crypto.subtle.sign(
            "HMAC",
            this.hmacKey,
            data
        );

        return Array.from(new Uint8Array(signature))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
    },

    async verifyServerSignature(data, signature) {
        const expected = await this.signPayload(data);
        return expected === signature;
    },

    async saveBatch(storeName, items) {
        const tx = this.db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        for (const item of items) {
            const encrypted = await this.encrypt(item);
            await store.put({
                id: item.id,
                ...encrypted,
                status: 'synced',
                updated_at: item.updated_at || new Date().toISOString()
            });
        }
    },

    async saveLocal(id, data, status = 'pending') {
        const encrypted = await this.encrypt(data);
        const record = {
            id: id,
            ...encrypted,
            status: status,
            updated_at: new Date().toISOString()
        };
        const tx = this.db.transaction('data', 'readwrite');
        await tx.objectStore('data').put(record);
    },

    async getPendingData() {
        return new Promise((resolve) => {
            const tx = this.db.transaction('data', 'readonly');
            const store = tx.objectStore('data');
            const request = store.openCursor();
            const pending = [];
            request.onsuccess = (e) => {
                const cursor = e.target.result;
                if (cursor) {
                    if (cursor.value.status === 'pending') {
                        pending.push(cursor.value);
                    }
                    cursor.continue();
                } else {
                    resolve(pending);
                }
            };
        });
    },

    async getAll(storeName = 'data') {
        const tx = this.db.transaction(storeName, 'readonly');
        const store = tx.objectStore(storeName);
        const results = await new Promise(r => {
            const req = store.getAll();
            req.onsuccess = () => r(req.result);
        });

        // Déchiffrer chaque élément
        const decrypted = [];
        for (const item of results) {
            try {
                const data = await this.decrypt(item);
                decrypted.push({ ...data, _status: item.status, _id: item.id });
            } catch (e) {
                console.error("Failed to decrypt item", item.id, e);
            }
        }
        return decrypted;
    },

    async getById(id, storeName = 'data') {
        const tx = this.db.transaction(storeName, 'readonly');
        const store = tx.objectStore(storeName);
        const item = await new Promise(r => {
            const req = store.get(id);
            req.onsuccess = () => r(req.result);
        });
        if (!item) return null;
        return await this.decrypt(item);
    }
};
