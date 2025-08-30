<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class CompanyProfileCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;


    /**
     * Create a new event instance.
     *
     * @return void
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
