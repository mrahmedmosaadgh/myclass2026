<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use App\Models\MessageRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageRecipientFactory extends Factory
{
    protected $model = MessageRecipient::class;

    public function definition()
    {
        return [
            'message_id' => Message::factory(),
            'user_id' => User::factory(),
            'read_at' => $this->faker->optional()->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the message is unread.
     */
    public function unread()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => null,
            ];
        });
    }

    /**
     * Indicate that the message is read.
     */
    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => now(),
            ];
        });
    }
}