<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cria o Administrador automaticamente se ele não existir
        User::firstOrCreate(
            ['email' => 'admin@techfit.com'],
            [
                'name' => 'Admin TechFit',
                'password' => bcrypt('123456'),
                'role' => 'admin'
            ]
        );

        // Cria a Recepcionista
        User::firstOrCreate(
            ['email' => 'recepcao@techfit.com'],
            [
                'name' => 'Recepcionista',
                'password' => bcrypt('123456'),
                'role' => 'employee'
            ]
        );

        // Cria o Aluno
        $aluno = User::firstOrCreate(
            ['email' => 'aluno@techfit.com'],
            [
                'name' => 'Aluno Fit',
                'password' => bcrypt('123456'),
                'role' => 'member'
            ]
        );

        // Cria uma compra de teste para o aluno para não ficar vazio no painel
        \App\Models\Sale::firstOrCreate(
            ['user_id' => $aluno->id],
            [
                'total_price' => 149.90,
                'status' => 'paid'
            ]
        );
    }
}
