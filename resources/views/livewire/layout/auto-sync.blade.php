<div id="auto-sync-wrapper" 
     wire:ignore
     data-refresh-interval="{{ $refreshInterval }}"
     style="display: none;">
    <!-- کامپوننت مخفی برای سینک خودکار -->
</div>

<script>
    (function() {
        let syncInterval = null;
        let checkInterval = null;
        let lastSyncTime = null;
        let currentRefreshInterval = 0;

        function initializeAutoSync() {
            // پاک کردن interval های قبلی
            if (syncInterval) {
                clearInterval(syncInterval);
                syncInterval = null;
            }
            if (checkInterval) {
                clearInterval(checkInterval);
                checkInterval = null;
            }

            const wrapper = document.getElementById('auto-sync-wrapper');
            if (!wrapper) {
                console.warn('Auto sync wrapper not found');
                return;
            }

            const refreshInterval = parseInt(wrapper.getAttribute('data-refresh-interval'));
            currentRefreshInterval = refreshInterval;
            
            if (refreshInterval <= 0) {
                console.log('⏸️ سینک خودکار غیرفعال است (refresh_interval = 0)');
                return;
            }

            console.log('🔄 سینک خودکار فعال شد', {
                refresh_interval: refreshInterval,
                unit: 'دقیقه'
            });

            function performSync() {
                console.log('🔄 شروع سینک خودکار...', {
                    refresh_interval: refreshInterval,
                    last_sync: lastSyncTime ? new Date(lastSyncTime).toLocaleString('fa-IR') : 'اولین بار'
                });

                // فراخوانی API برای سینک
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                if (!csrfToken) {
                    console.error('❌ CSRF token not found');
                    return;
                }

                fetch('/api/residents/sync', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        lastSyncTime = Date.now();
                        localStorage.setItem('lastAutoSyncTime', lastSyncTime.toString());
                        console.log('✅ سینک خودکار با موفقیت انجام شد', data.data);
                        console.log('📊 آمار سینک:', {
                            'همگام‌سازی شده': data.data.synced_count,
                            'ایجاد شده': data.data.created_count,
                            'به‌روزرسانی شده': data.data.updated_count,
                            'کل در دیتابیس': data.data.total_in_db
                        });
                        
                        // Dispatch event برای به‌روزرسانی داده‌ها
                        if (typeof Livewire !== 'undefined') {
                            Livewire.dispatch('data-synced');
                            Livewire.dispatch('residents-synced');
                        }
                    } else {
                        console.error('❌ خطا در سینک خودکار:', data.message);
                    }
                })
                .catch(error => {
                    console.error('❌ خطا در سینک خودکار:', error);
                });
            }

            function checkIfSyncNeeded() {
                const now = Date.now();
                const lastSyncFromStorage = localStorage.getItem('lastAutoSyncTime');
                
                if (!lastSyncFromStorage) {
                    // اولین بار - بلافاصله سینک کن
                    console.log('🔄 اولین سینک - بلافاصله انجام می‌شود');
                    performSync();
                    return;
                }

                const lastSync = parseInt(lastSyncFromStorage);
                lastSyncTime = lastSync;
                
                // محاسبه دقیق زمان گذشته (به دقیقه)
                const elapsedMinutes = Math.floor((now - lastSync) / (1000 * 60));
                
                if (elapsedMinutes >= refreshInterval) {
                    // زمان sync رسیده است
                    console.log('⏰ زمان سینک رسیده است', {
                        elapsed_minutes: elapsedMinutes,
                        refresh_interval: refreshInterval
                    });
                    performSync();
                } else {
                    // هنوز زمان sync نرسیده
                    const remainingMinutes = refreshInterval - elapsedMinutes;
                    const remainingSeconds = Math.floor(((refreshInterval * 60) - (now - lastSync)) / 1000);
                    if (remainingSeconds % 60 === 0) { // فقط هر دقیقه یکبار لاگ کن
                        console.log('⏳ زمان سینک بعدی:', {
                            remaining_minutes: remainingMinutes,
                            next_sync: new Date(lastSync + (refreshInterval * 60 * 1000)).toLocaleString('fa-IR')
                        });
                    }
                }
            }

            // بررسی اولیه
            checkIfSyncNeeded();

            // تنظیم interval برای بررسی زمان سینک (هر 10 ثانیه یکبار چک می‌کند)
            checkInterval = setInterval(() => {
                checkIfSyncNeeded();
            }, 10000); // هر 10 ثانیه یکبار
        }

        // مقداردهی اولیه
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initializeAutoSync, 1000);
        });

        // گوش دادن به تغییرات تنظیمات
        document.addEventListener('livewire:init', () => {
            Livewire.on('settings-updated', () => {
                console.log('⚙️ تنظیمات به‌روزرسانی شد، راه‌اندازی مجدد سینک خودکار...');
                // Reload page to get new refresh_interval
                setTimeout(() => {
                    location.reload();
                }, 500);
            });
        });
    })();
</script>
