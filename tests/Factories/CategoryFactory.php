<?php

namespace Openplain\FilamentTreeView\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Openplain\FilamentTreeView\Tests\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(rand(1, 3), true),
            'description' => fake()->sentence(),
            'parent_id' => null,
            'order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withParent(int|Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent instanceof Category ? $parent->id : $parent,
        ]);
    }
}
