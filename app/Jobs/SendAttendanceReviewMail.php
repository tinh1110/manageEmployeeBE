<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\AttendanceReviewed;
use Illuminate\Support\Facades\Mail;

class SendAttendanceReviewMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_email, $data;

    /**
     * Create a new job instance.
     */
    public function __construct($user_email, $data)
    {
        $this->user_email = $user_email;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user_email)->send(new AttendanceReviewed($this->data));
    }
}
