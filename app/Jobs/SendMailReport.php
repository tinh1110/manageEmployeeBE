<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendMailReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $files = $this->data['image'];
        $report = $this->data['report'];
        $user = $this->data['user'];
        $admin = User::where('email', "anhnhim1110@gmail.com")->first();
        Mail::send('emails.report_mail', [
            'user' => $user,
            'report' => $report,
        ], function ($msg) use ($files, $admin) {
            $msg->to( $admin->email, 'Góp ý')->subject('Góp ý của nhân viên về ' . $this->data['subject']);
            if (count($files)) {
                foreach ($files as $file) {
                    $msg->attach(asset('storage/'.$file));
                }
            }
        });
    }
}
