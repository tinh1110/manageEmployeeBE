<?php

namespace App\Events;

use App\Models\Imported_users;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportedUser implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public Imported_users $imported_users;
    /**
     * Create a new event instance.
     */
    public function __construct(Imported_users $imported_users)
    {
        $this->imported_users = $imported_users;
    }

    public function broadcastOn()
    {
        return new Channel('imported_users');
    }

    public function broadcastAs()
    {
        return 'imported_users';
    }
}
