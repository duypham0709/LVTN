<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement(array: [
            'Cà chua bi',
            'Rau muống',
            'Bí đỏ',
            'Khoai tây',
            'Táo xanh',
            'Xà lách',
            'Dưa leo',
            'Ớt chuông đỏ',
            'Cà rốt',
            'Bắp cải',
            'Nho đen',
            'Chuối chín',
            'Bưởi da xanh',
            'Cam sành',
            'Dưa hấu',
            'Hành lá',
            'Tỏi Lý Sơn',
            'Gừng tươi',
            'Khoai lang',
            'Mướp đắng',
        ]);

        $expiryDate = $this->faker
            ->optional()
            ->dateTimeBetween('now', '+1 year');

        return [
            'name' => ucfirst(string: $name),
            'slug' => Str::slug(title: $name) . '-' . $this->faker->unique()->numberBetween(int1: 1, int2: 1000),
            'category_id' => Category::inRandomOrder()->first()->id,
            'description' => $this->faker->sentence(nbWords: 10),
            'price' => $this->faker->randomFloat(nbMaxDecimals: 2, min: 10000, max: 200000),
            'stock' => $this->faker->numberBetween(int1: 0, int2: 100),
            'status' => $this->faker->randomElement(array: ['in_stock', 'out_of_stock']),
            'unit' => $this->faker->randomElement(array: ['kg', 'bó', 'túi', 'hộp']),
            'expiry_date' => $expiryDate?->format('Y-m-d'),
        ];
    }
}
