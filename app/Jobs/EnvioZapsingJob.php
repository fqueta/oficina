<?php

namespace App\Jobs;

use App\Http\Controllers\admin\OrcamentoController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnvioZapsingJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $token;
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new OrcamentoController)->send_to_zapSing($this->token);
    }
}
