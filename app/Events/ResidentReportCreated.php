<?php

namespace App\Events;

use App\Models\ResidentReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidentReportCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $residentReport;

    /**
     * Create a new event instance.
     */
    public function __construct(ResidentReport $residentReport)
    {
        $this->residentReport = $residentReport;
    }
}
