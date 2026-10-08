<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pertanyaan' => rtrim(fake()->unique()->sentence(6), '.').'?',
            'jawaban' => '<p>'.fake()->paragraph().'</p>',
            'urutan_ke' => fake()->numberBetween(1, 20),
        ];
    }
}
