<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendMailForUsers implements ShouldQueue
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
        $files = $this->data['event']->image;
        foreach ($this->data['users'] as $user){
            Mail::send('emails.send_mail_for_dues', [
                'user' => $user,
                'event' => $this->data['event'],
            ], function ($msg) use ($files, $user) {
                $msg->to( $user->email, 'Thông báo')->subject('Thông báo ' . $this->data['event']->type);
                if (count($files)) {
                    foreach ($files as $file) {
                        $msg->attach(asset('storage/'.$file));
                    }
                }

            });
        }

    }
}
