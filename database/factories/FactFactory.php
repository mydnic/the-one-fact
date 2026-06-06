<?php

namespace Database\Factories;

use App\Models\Fact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fact>
 */
class FactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'fact_date' => $this->faker->unique()->date(),
            'title' => rtrim($title, '.'),
            'content' => $this->faker->paragraph(),
            'tags' => $this->faker->randomElements(
                ['Hobbits', 'Gondor', 'Rings', 'Elves', 'Mordor', 'Rohan', 'Valar', 'First Age'],
                $this->faker->numberBetween(2, 4),
            ),
            'source_url' => 'https://tolkiengateway.net/wiki/'.$this->faker->word(),
            'source_title' => $this->faker->words(2, true),
            'metadata' => [
                'provider' => 'openai',
                'model' => 'gpt-4o',
            ],
        ];
    }
}
