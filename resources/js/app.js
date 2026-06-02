import "./passkeys";
import ApexCharts from 'apexcharts'
import { OfflineEngine } from './offline-engine';

window.ApexCharts = ApexCharts
window.OfflineEngine = OfflineEngine;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(() => {
            navigator.serviceWorker.ready.then((registration) => {
                registration.active?.postMessage({ type: 'CACHE_APP_SHELL' });
            });
        }).catch((error) => console.error('Service worker registration failed', error));
    });
}

const createOfflineStore = () => ({
    isOnline: navigator.onLine,
    pendingChanges: 0,
    showSyncBanner: false,
    localData: {
        employees: [],
        metrics: {
            totalEmployees: 0,
            onLeave: 0,
            expiring: 0,
            payroll: 0
        }
    },

    async init() {
        await OfflineEngine.init();

        window.addEventListener('online', () => {
            this.isOnline = true;
            this.checkPendingChanges();
        });
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.loadLocalData();
        });

        if (this.isOnline) {
            await this.bootstrap();
        } else {
            await this.loadLocalData();
        }

        this.checkPendingChanges();
    },

    async loadLocalData() {
        this.localData.employees = await OfflineEngine.getAll('employees');

        // Calculer les métriques locales pour le dashboard
        this.localData.metrics.totalEmployees = this.localData.employees.length;
        this.localData.metrics.onLeave = this.localData.employees.filter(e => e.status === 'on_leave').length;
        this.localData.metrics.expiring = this.localData.employees.filter(e => e.end_date && new Date(e.end_date) < new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)).length;

        console.log("Local data and metrics loaded", this.localData);
    },

    async bootstrap() {
        let deviceId = localStorage.getItem('device_id');

        // 1. Register device if not exists
        if (!deviceId || !OfflineEngine.hmacKey) {
            try {
                const res = await fetch('/api/device/register', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                if (res.ok) {
                    const config = await res.json();
                    localStorage.setItem('device_id', config.device_id);
                    await OfflineEngine.setHMACSecret(config.secret);
                    deviceId = config.device_id;
                    console.log("Device registered successfully");
                }
            } catch (e) {
                console.error("Device registration failed", e);
                return;
            }
        }

        // 2. Fetch initial snapshot if data store is empty
        const count = (await OfflineEngine.getAll('employees')).length;

        if (count === 0) {
            try {
                const res = await fetch('/api/sync/snapshot', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Device-Id': deviceId
                    }
                });
                if (res.ok) {
                    const signature = res.headers.get('X-Server-Signature');
                    const data = await res.json();

                    if (await OfflineEngine.verifyServerSignature(data, signature)) {
                        await OfflineEngine.saveSnapshot(data);
                        console.log("Initial data snapshot loaded");
                        await this.loadLocalData();
                    }
                }
            } catch (e) {
                console.error("Snapshot fetch failed", e);
            }
        } else {
            await this.triggerSync();
        }
    },

    async checkPendingChanges() {
        const pending = await OfflineEngine.getPendingData();
        this.pendingChanges = pending.length;
        this.showSyncBanner = this.pendingChanges > 0 && this.isOnline;
    },

    async triggerSync() {
        const deviceId = localStorage.getItem('device_id');
        if (!deviceId || !OfflineEngine.hmacKey) {
            console.error("Device ID missing. Needs registration.");
            return;
        }

        const pendingData = await OfflineEngine.getPendingData();
        const payload = {
            changes: pendingData,
            timestamp: Date.now()
        };

        const signature = await OfflineEngine.signPayload(payload);

        try {
            const response = await fetch('/api/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Device-Id': deviceId,
                    'X-Payload-Signature': signature,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: OfflineEngine.canonicalStringify(payload)
            });

            if (response.ok) {
                const data = await response.json();
                const signature = response.headers.get('X-Server-Signature');

                if (!(await OfflineEngine.verifyServerSignature(data, signature))) {
                    throw new Error('Invalid server signature.');
                }

                await OfflineEngine.markSynced(data.ack);
                await OfflineEngine.saveSnapshot(data);
                await this.loadLocalData();

                this.pendingChanges = 0;
                this.showSyncBanner = false;
                console.log("Sync successful");
            }
        } catch (e) {
            console.error("Sync failed", e);
        }
    }
});

const registerOfflineStore = (AlpineInstance) => {
    if (!AlpineInstance || AlpineInstance.store('offline')) {
        return;
    }

    AlpineInstance.store('offline', createOfflineStore());
};

if (window.Alpine) {
    registerOfflineStore(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => {
        registerOfflineStore(window.Alpine);
    });
}
