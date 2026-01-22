<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Guest;
use App\Models\TicketSale;
use App\Models\User;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketSale>
 */
class TicketSaleFactory extends Factory
{
    protected $model = TicketSale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'guest_id' => Guest::factory(),
            'sold_by' => User::factory(),
            'value' => $this->faker->randomFloat(2, 50, 500),
            'payment_method' => $this->faker->randomElement(PaymentMethod::cases())->value,
            'buyer_name' => $this->faker->name(),
            'buyer_document' => $this->faker->numerify('###########'),
            'notes' => $this->faker->sentence(),
        ];
    }
}
