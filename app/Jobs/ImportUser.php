<?php

namespace App\Jobs;

use App\Imports\ImportUsers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ImportUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $fileName;
    private $id;
    public function __construct($filename,$id)
    {
        $this->id = $id;
        $this->fileName = $filename;

    }
    public function handle(): void
    {
        Excel::import(new ImportUsers($this->fileName,$this->id),storage_path('app/public/import_users/'.$this->fileName));
    }
}
