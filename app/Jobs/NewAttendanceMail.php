<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\NewAttendance;
use Illuminate\Support\Facades\Mail;

class NewAttendanceMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $manager_emails, $new_attendance_data;

    /**
     * Create a new job instance.
     */
    public function __construct($manager_emails, $new_attendance_data)
    {
        $this->manager_emails = $manager_emails;
        $this->new_attendance_data = $new_attendance_data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach($this->manager_emails as $manager_email){
            Mail::to($manager_email)->send(new NewAttendance($this->new_attendance_data));
        }
    }
}
