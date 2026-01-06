<div>
    @if($showAlarm)
    <div id="persistent-alarm" 
         style="position: fixed; top: 20px; right: 20px; left: auto; z-index: 10000; min-width: 320px; max-width: 400px; animation: slideInRight 0.5s ease-out;"
         wire:ignore.self>
        <div style="background: linear-gradient(135deg, #ff6b6b, #ee5a52); color: white; padding: 16px 20px; border-radius: 12px; box-shadow: 0 8px 25px rgba(238, 90, 82, 0.3); display: flex; align-items: flex-start; gap: 15px; position: relative; overflow: hidden;">
            
            <!-- Background decoration -->
            <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;"></div>
            
            <!-- Alert icon -->
            <div style="flex-shrink: 0; font-size: 24px; animation: pulse 2s infinite;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <!-- Alert content -->
            <div style="flex: 1; position: relative; z-index: 1;">
                <div style="font-weight: 700; margin-bottom: 6px; font-size: 16px; line-height: 1.3;">
                    توجه: نیاز به همگام‌سازی
                </div>
                <div style="font-size: 13px; opacity: 0.95; line-height: 1.4; margin-bottom: 12px;">
                    داده‌ها ممکن است به‌روز نباشند. برای دریافت آخرین اطلاعات روی دکمه همگام‌سازی کلیک کنید.
                </div>
                
                <!-- Sync button -->
                <button wire:click="syncAndRefresh" 
                        wire:loading.attr="disabled"
                        style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid rgba(255, 255, 255, 0.3); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; transition: all 0.3s; display: flex; align-items: center; gap: 8px; backdrop-filter: blur(10px);"
                        @if($syncing) disabled @endif
                        onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='translateY(0)'">
                    <i class="fas fa-sync-alt" wire:loading.class="fa-spin"></i>
                    <span wire:loading.remove>همگام‌سازی داده‌ها</span>
                    <span wire:loading>در حال همگام‌سازی...</span>
                </button>
            </div>
            
            <!-- Close button -->
            <button wire:click="closeAlarm" 
                    style="flex-shrink: 0; background: rgba(255, 255, 255, 0.2); border: none; color: white; cursor: pointer; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s; font-size: 14px; position: relative; z-index: 1;"
                    onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='rotate(90deg)'"
                    onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='rotate(0)'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <style>
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }

    .alarm-hiding {
        animation: slideOutRight 0.3s ease-in forwards;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        #persistent-alarm {
            top: 10px !important;
            right: 10px !important;
            left: 10px !important;
            min-width: auto !important;
            max-width: none !important;
        }
        
        #persistent-alarm > div {
            padding: 12px 16px !important;
        }
        
        #persistent-alarm .alert-icon {
            font-size: 20px !important;
        }
        
        #persistent-alarm .alert-title {
            font-size: 14px !important;
        }
        
        #persistent-alarm .alert-message {
            font-size: 12px !important;
        }
        
        #persistent-alarm button {
            padding: 6px 12px !important;
            font-size: 12px !important;
        }
    }

    @media (max-width: 480px) {
        #persistent-alarm {
            top: 5px !important;
            right: 5px !important;
            left: 5px !important;
        }
        
        #persistent-alarm > div {
            padding: 10px 14px !important;
            flex-direction: column !important;
            gap: 10px !important;
        }
        
        #persistent-alarm .alert-content {
            text-align: center !important;
        }
        
        #persistent-alarm .close-button {
            position: absolute !important;
            top: 8px !important;
            left: 8px !important;
        }
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        // Listen for refresh page event
        Livewire.on('refreshPage', () => {
            setTimeout(() => {
                location.reload();
            }, 2000);
        });
        
        // Add keyboard shortcut (ESC to close)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const alarm = document.getElementById('persistent-alarm');
                if (alarm) {
                    @this.call('closeAlarm');
                }
            }
        });
    });
    </script>
</div>
