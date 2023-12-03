<?php

namespace App\Jobs;

use App\Imports\AttendanceImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;


class ImportAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $filename;
    protected int $id;
    protected int $imported_id;
    /**
     * Create a new job instance.
     */
    public function __construct($filename,$id,$imported_id)
    {
        $this->filename = $filename;
        $this->id = $id;
        $this->imported_id = $imported_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Excel::import(new AttendanceImport($this->filename,$this->id,$this->imported_id),storage_path('app/import_attendance/'.$this->filename));
    }

}

