<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>پیام‌های سامانه ملی پیامک</h2>
            <button wire:click="refresh" class="btn" style="background: #4361ee; color: white;">
                <i class="fas fa-sync-alt"></i> بروزرسانی
            </button>
        </div>

        <!-- Location Filter -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <button 
                wire:click="setLocation(2)" 
                class="btn <?php echo e($location === 2 ? 'btn-primary' : ''); ?>"
                style="<?php echo e($location === 2 ? 'background: #4361ee; color: white;' : 'background: #6c757d; color: white;'); ?>"
            >
                <i class="fas fa-paper-plane"></i> پیام‌های ارسالی
            </button>
            <button 
                wire:click="setLocation(1)" 
                class="btn <?php echo e($location === 1 ? 'btn-primary' : ''); ?>"
                style="<?php echo e($location === 1 ? 'background: #4cc9f0; color: white;' : 'background: #6c757d; color: white;'); ?>"
            >
                <i class="fas fa-inbox"></i> پیام‌های دریافتی
            </button>
            <button 
                wire:click="setLocation(-1)" 
                class="btn <?php echo e($location === -1 ? 'btn-primary' : ''); ?>"
                style="<?php echo e($location === -1 ? 'background: #f72585; color: white;' : 'background: #6c757d; color: white;'); ?>"
            >
                <i class="fas fa-list"></i> همه پیام‌ها
            </button>
        </div>

        <!-- Error Message -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fcc;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo e($error); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Debug Info -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rawResponse): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dee2e6;">
                <h4 style="margin-bottom: 10px;">پاسخ خام از API (برای دیباگ):</h4>
                <pre style="direction: ltr; text-align: left; background: white; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; max-height: 300px;"><?php echo e($rawResponse); ?></pre>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Loading State -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; margin-bottom: 10px;"></i>
                <p>در حال بارگذاری پیام‌ها...</p>
            </div>
        <?php elseif(empty($messages)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <p>هیچ پیامی یافت نشد</p>
            </div>
        <?php else: ?>
            <!-- Messages Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>شناسه (RecId)</th>
                            <th>شماره فرستنده</th>
                            <th>شماره گیرنده</th>
                            <th>متن پیام</th>
                            <th>تاریخ ارسال</th>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($location == 2): ?>
                                <th>وضعیت تحویل</th>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($message['RecId'] ?? $message['recId'] ?? '-'); ?></strong>
                                </td>
                                <td><?php echo e($message['Sender'] ?? $message['sender'] ?? '-'); ?></td>
                                <td><?php echo e($message['Receiver'] ?? $message['receiver'] ?? '-'); ?></td>
                                <td>
                                    <div>
                                        <?php echo e(\Illuminate\Support\Str::limit($message['Body'] ?? $message['body'] ?? $message['Text'] ?? $message['text'] ?? '-', 100)); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(mb_strlen($message['Body'] ?? $message['body'] ?? $message['Text'] ?? $message['text'] ?? '') > 100): ?>
                                            <br>
                                            <small style="color: #4361ee; cursor: pointer;" onclick="this.parentElement.querySelector('.full-text').style.display = this.parentElement.querySelector('.full-text').style.display === 'none' ? 'block' : 'none'">نمایش کامل</small>
                                            <div class="full-text" style="display: none; margin-top: 5px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                                                <?php echo e($message['Body'] ?? $message['body'] ?? $message['Text'] ?? $message['text'] ?? '-'); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($message['SendDate']) || isset($message['sendDate'])): ?>
                                        <?php echo e($message['SendDate'] ?? $message['sendDate'] ?? '-'); ?>

                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($location == 2): ?>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($message['RecId']) || isset($message['recId'])): ?>
                                            <span style="background: #4cc9f0; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                                <?php echo e($this->getDeliveryStatusText($message['RecId'] ?? $message['recId'])); ?>

                                            </span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($location == 2 && (isset($message['RecId']) || isset($message['recId']))): ?>
                                        <button
                                            wire:click="checkDeliveryStatus(<?php echo e($message['RecId'] ?? $message['recId']); ?>)"
                                            class="btn btn-info btn-sm"
                                            title="بررسی وضعیت تحویل"
                                        >
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Load More Button -->
            <div style="margin-top: 20px; text-align: center;">
                <button 
                    wire:click="loadMore" 
                    class="btn" 
                    style="background: #4361ee; color: white;"
                    wire:loading.attr="disabled"
                >
                    <i class="fas fa-chevron-down"></i> 
                    بارگذاری بیشتر
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
                        <i class="fas fa-spinner fa-spin"></i>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
                <div style="margin-top: 10px; color: #666; font-size: 14px;">
                    نمایش <?php echo e(count($messages)); ?> پیام (از اندیس <?php echo e($index); ?>)
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <script>
        window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('showAlert', (event) => {
            alert(event.detail.title + '\n\n' + event.detail.text);
        });
    </script>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\sms\api-messages.blade.php ENDPATH**/ ?>