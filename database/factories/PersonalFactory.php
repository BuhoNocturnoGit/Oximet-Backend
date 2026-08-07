<?php

namespace Database\Factories;

use App\Models\Personal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Personal>
 */
class PersonalFactory extends Factory
{
    protected $model = Personal::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'Nombre' => fake()->firstName(),
            'Apellidos' => fake()->lastName(),
            'Correo' => fake()->unique()->safeEmail(),
            'Contrasena' => static::$password ??= Hash::make('password123'),
            'id_rol' => 3,
            'estado' => 'Activo',
            'Telefono' => fake()->numerify('##########'),
        ];
    }

    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_rol' => 1,
            'estado' => 'Activo',
        ]);
    }

    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_rol' => 2,
            'estado' => 'Activo',
        ]);
    }
}
