<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ImportUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $rows;

    /**
     * Create a new job instance.
     */
    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->rows as $row) 
        {
            if($row['dob']) $dob = Carbon::createFromFormat('d/m/Y', $row['dob'])->format('Y-m-d');
            User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => $row['password'],
                'address' => $row['address'],
                'phone_number' => $row['phone_number'],
                'dob' => $dob ?? $row['dob'],
                'details' => $row['details'],
                'gender' => $row['gender'],
                'role_id' => $row['role_id'],
                'status' => $row['status'],
                'created_by_id' => 1
            ]);
        }
    }
}
