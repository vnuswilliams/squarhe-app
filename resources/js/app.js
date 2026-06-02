import "./passkeys";
import ApexCharts from 'apexcharts'
import { OfflineEngine } from './offline-engine';

window.ApexCharts = ApexCharts
window.OfflineEngine = OfflineEngine;

document.addEventListener('alpine:init', () => {
    Alpine.store('offline', {
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
            this.localData.employees = await OfflineEngine.getAll('data');

            // Calculer les métriques locales pour le dashboard
            this.localData.metrics.totalEmployees = this.localData.employees.length;
            this.localData.metrics.onLeave = this.localData.employees.filter(e => e.status === 'on_leave').length;
            this.localData.metrics.expiring = this.localData.employees.filter(e => e.end_date && new Date(e.end_date) < new Date(Date.now() + 30*24*60*60*1000)).length;

            console.log("Local data and metrics loaded", this.localData);
        },
        },

        async bootstrap() {
            let deviceId = localStorage.getItem('device_id');
            
            // 1. Register device if not exists
            if (!deviceId) {
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
            const tx = OfflineEngine.db.transaction('data', 'readonly');
            const count = await new Promise(r => {
                const req = tx.objectStore('data').count();
                req.onsuccess = () => r(req.result);
            });

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
                            await OfflineEngine.saveBatch('data', data.employees);
                            console.log("Initial data snapshot loaded");
                            await this.loadLocalData();
                        }
                    }
                } catch (e) {
                    console.error("Snapshot fetch failed", e);
                }
            }
        },

        async checkPendingChanges() {
            const pending = await OfflineEngine.getPendingData();
            this.pendingChanges = pending.length;
            this.showSyncBanner = this.pendingChanges > 0 && this.isOnline;
        },

        async triggerSync() {
            const deviceId = localStorage.getItem('device_id');
            if (!deviceId) {
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
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    const data = await response.json();
                    // Mark as synced local
                    const tx = OfflineEngine.db.transaction('data', 'readwrite');
                    const store = tx.objectStore('data');
                    for (const id of data.ack) {
                        const item = await new Promise(r => {
                            const req = store.get(id);
                            req.onsuccess = () => r(req.result);
                        });
                        if (item) {
                            item.status = 'synced';
                            store.put(item);
                        }
                    }
                    
                    this.pendingChanges = 0;
                    this.showSyncBanner = false;
                    console.log("Sync successful");
                }
            } catch (e) {
                console.error("Sync failed", e);
            }
        }
    });
});
