<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidentsSynced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $syncedCount;
    public $createdCount;
    public $deletedCount;

    /**
     * Create a new event instance.
     */
    public function __construct($syncedCount, $createdCount, $deletedCount)
    {
        $this->syncedCount = $syncedCount;
        $this->createdCount = $createdCount;
        $this->deletedCount = $deletedCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('residents-sync'),
        ];
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'residents.synced';
    }
    
    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'synced_count' => $this->syncedCount,
            'created_count' => $this->createdCount,
            'deleted_count' => $this->deletedCount,
            'message' => "دیتابیس اقامت‌گران کاملاً جایگزین شد. حذف شده: {$this->deletedCount}, ایجاد شده: {$this->createdCount}",
            'time' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
