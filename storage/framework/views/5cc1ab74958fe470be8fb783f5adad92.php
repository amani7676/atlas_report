<div>
    <button 
        wire:click="syncResidents" 
        wire:loading.attr="disabled"
        class="btn btn-primary"
        style="display: flex; align-items: center; gap: 8px;"
        <?php if($syncing): ?> disabled <?php endif; ?>
    >
        <i class="fas fa-sync-alt" wire:loading.class="fa-spin"></i>
        <span wire:loading.remove>همگام‌سازی از API</span>
        <span wire:loading>در حال همگام‌سازی...</span>
    </button>
</div>

<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\layout\sync-button.blade.php ENDPATH**/ ?>