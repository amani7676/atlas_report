<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatabaseRecordChanged
{
    use Dispatchable, SerializesModels;

    public $model;
    public $tableName;
    public $action; // 'created', 'updated', 'deleted'
    public $originalData;
    public $changedData;
    public $residentId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Model $model,
        string $tableName,
        string $action,
        ?int $residentId = null,
        ?array $originalData = null,
        ?array $changedData = null
    ) {
        $this->model = $model;
        $this->tableName = $tableName;
        $this->action = $action;
        $this->residentId = $residentId;
        $this->originalData = $originalData;
        $this->changedData = $changedData;
    }
}
