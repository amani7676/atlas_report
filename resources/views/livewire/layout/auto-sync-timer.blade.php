@if($showTimer)
    <div id="auto-sync-timer-container"
         wire:ignore
         style="display: flex; align-items: center; gap: 8px; padding: 5px 12px; background: rgba(67, 97, 238, 0.1); border-radius: 20px; font-size: 14px; color: var(--primary-color); font-weight: 500;">
        <i class="fas fa-clock"></i>
        <span id="timer-display">--:--</span>
        <span id="sync-status" style="display: none; color: #28a745;">
            <i class="fas fa-sync fa-spin"></i> در حال همگام‌سازی...
        </span>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            const refreshInterval = {{ $refreshInterval }};
            let initialTimeRemaining = {{ $initialTimeRemaining }};
            const timerContainer = document.getElementById('auto-sync-timer-container');
            const timerDisplay = document.getElementById('timer-display');
            const syncStatus = document.getElementById('sync-status');
            
            // تبدیل به عدد صحیح
            let timeRemaining = Math.floor(initialTimeRemaining);
            let timerInterval = null;
            let syncInProgress = false;
            let lastServerCheck = Date.now();
            let timerStarted = false;
            
            function formatTime(seconds) {
                // تبدیل به عدد صحیح
                const totalSeconds = Math.floor(seconds);
                const minutes = Math.floor(totalSeconds / 60);
                const secs = totalSeconds % 60;
                const m = String(minutes).padStart(2, '0');
                const s = String(secs).padStart(2, '0');
                return m + ':' + s;
            }
            
            // دریافت زمان باقی‌مانده از سرور (هر 30 ثانیه یکبار)
            function syncTimeFromServer() {
                const now = Date.now();
                if (now - lastServerCheck < 30000) {
                    return; // کمتر از 30 ثانیه گذشته، نیازی به sync نیست
                }
                
                lastServerCheck = now;
                
                @this.call('getTimeRemaining').then((remaining) => {
                    if (remaining !== null && remaining !== undefined && !isNaN(remaining)) {
                        const serverTime = Math.floor(remaining);
                        const currentTime = Math.floor(timeRemaining);
                        
                        // فقط اگر تفاوت بیشتر از 5 ثانیه است، از سرور استفاده کن
                        if (Math.abs(serverTime - currentTime) > 5) {
                            timeRemaining = serverTime;
                            if (timerDisplay) {
                                timerDisplay.textContent = formatTime(timeRemaining);
                            }
                        }
                    }
                }).catch((error) => {
                    console.warn('Error syncing time from server:', error);
                });
            }
            
            function updateTimer() {
                if (syncInProgress) {
                    return;
                }
                
                // sync زمان از سرور هر 30 ثانیه یکبار (فقط بعد از شروع تایمر)
                if (timerStarted) {
                    syncTimeFromServer();
                }
                
                if (timeRemaining > 0) {
                    timeRemaining = Math.floor(timeRemaining - 1);
                    if (timerDisplay) {
                        timerDisplay.textContent = formatTime(timeRemaining);
                    }
                    
                    // تغییر رنگ وقتی کمتر از 30 ثانیه باقی مانده
                    if (timeRemaining < 30 && timerContainer) {
                        timerContainer.style.background = 'rgba(255, 158, 0, 0.1)';
                        timerContainer.style.color = 'var(--warning-color)';
                    } else if (timerContainer) {
                        timerContainer.style.background = 'rgba(67, 97, 238, 0.1)';
                        timerContainer.style.color = 'var(--primary-color)';
                    }
                } else {
                    // تایمر به صفر رسیده
                    if (timerDisplay) {
                        timerDisplay.textContent = '00:00';
                    }
                    if (timerContainer) {
                        timerContainer.style.background = 'rgba(247, 37, 133, 0.1)';
                        timerContainer.style.color = 'var(--danger-color)';
                    }
                    
                    // نمایش وضعیت sync
                    if (timerDisplay) timerDisplay.style.display = 'none';
                    if (syncStatus) syncStatus.style.display = 'inline';
                    
                    syncInProgress = true;
                    
                    // فراخوانی متد sync در Livewire
                    @this.call('performSync').then(() => {
                        // بعد از sync، زمان باقی‌مانده را از سرور بگیر
                        @this.call('getTimeRemaining').then((remaining) => {
                            if (remaining !== null && remaining !== undefined && !isNaN(remaining)) {
                                timeRemaining = Math.floor(remaining);
                            } else {
                                timeRemaining = refreshInterval * 60;
                            }
                            
                            if (timerDisplay) {
                                timerDisplay.textContent = formatTime(timeRemaining);
                                timerDisplay.style.display = 'inline';
                            }
                            if (syncStatus) syncStatus.style.display = 'none';
                            if (timerContainer) {
                                timerContainer.style.background = 'rgba(67, 97, 238, 0.1)';
                                timerContainer.style.color = 'var(--primary-color)';
                            }
                            
                            syncInProgress = false;
                        });
                    }).catch((error) => {
                        console.error('Error in sync:', error);
                        // حتی در صورت خطا، تایمر را reset کن
                        timeRemaining = refreshInterval * 60;
                        if (timerDisplay) {
                            timerDisplay.textContent = formatTime(timeRemaining);
                            timerDisplay.style.display = 'inline';
                        }
                        if (syncStatus) syncStatus.style.display = 'none';
                        syncInProgress = false;
                    });
                }
            }
            
            // شروع تایمر
            if (refreshInterval > 0 && timerContainer) {
                timerContainer.style.display = 'flex';
                
                // مقداردهی اولیه نمایش
                if (timerDisplay) {
                    timerDisplay.textContent = formatTime(timeRemaining);
                }
                
                // شروع interval
                timerStarted = true;
                timerInterval = setInterval(updateTimer, 1000);
            }
            
            // پاک کردن interval وقتی کامپوننت unmount می‌شود
            Livewire.on('livewire:before-unload', () => {
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
            });
        });
    </script>
@endif

