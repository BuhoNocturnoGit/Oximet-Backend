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
            'Estado_Registro' => 'Activo',
            'Rol_Solicitado' => 'Operador',
            'Rol_Asignado' => 'Operador',
            'Rol' => 'Operador',
            'Telefono' => fake()->numerify('##########'),
            'Activo' => 1,
            'Bloqueado' => 0,
        ];
    }

    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'Estado_Registro' => 'Activo',
            'Rol_Solicitado' => 'Admin',
            'Rol_Asignado' => 'Admin',
            'Rol' => 'Admin',
            'Activo' => 1,
        ]);
    }

    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'Estado_Registro' => 'Activo',
            'Rol_Solicitado' => 'Supervisor',
            'Rol_Asignado' => 'Supervisor',
            'Rol' => 'Supervisor',
            'Activo' => 1,
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'Estado_Registro' => 'Pendiente',
            'Rol' => null,
            'Activo' => 0,
        ]);
    }
}
