<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\GymClass;
use App\Models\Sale;
use App\Models\Checkin;
use Carbon\Carbon;
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
        // ═══════════════════════════════════════
        // USUÁRIOS
        // ═══════════════════════════════════════

        $admin = User::firstOrCreate(
            ['email' => 'admin@techfit.com'],
            [
                'name'     => 'Admin TechFit',
                'password' => bcrypt('123456'),
                'role'     => 'admin',
                'cpf'      => '000.000.000-00',
            ]
        );

        $employee = User::firstOrCreate(
            ['email' => 'recepcao@techfit.com'],
            [
                'name'     => 'Recepcionista',
                'password' => bcrypt('123456'),
                'role'     => 'employee',
                'cpf'      => '111.111.111-11',
            ]
        );

        $aluno = User::firstOrCreate(
            ['email' => 'aluno@techfit.com'],
            [
                'name'     => 'Aluno Fit',
                'password' => bcrypt('123456'),
                'role'     => 'member',
                'cpf'      => '222.222.222-22',
                'age'      => 25,
            ]
        );

        $aluno2 = User::firstOrCreate(
            ['email' => 'maria@techfit.com'],
            [
                'name'     => 'Maria Silva',
                'password' => bcrypt('123456'),
                'role'     => 'member',
                'cpf'      => '333.333.333-33',
                'age'      => 30,
            ]
        );

        $aluno3 = User::firstOrCreate(
            ['email' => 'joao@techfit.com'],
            [
                'name'     => 'João Santos',
                'password' => bcrypt('123456'),
                'role'     => 'member',
                'cpf'      => '444.444.444-44',
                'age'      => 22,
            ]
        );

        // ═══════════════════════════════════════
        // PLANOS
        // ═══════════════════════════════════════

        $mensal = Plan::firstOrCreate(
            ['name' => 'Mensal'],
            [
                'duration_days' => 30,
                'price'         => 99.90,
                'description'   => 'Acesso completo à academia por 30 dias. Ideal para quem quer flexibilidade.',
                'is_active'     => true,
            ]
        );

        $trimestral = Plan::firstOrCreate(
            ['name' => 'Trimestral'],
            [
                'duration_days' => 90,
                'price'         => 249.90,
                'description'   => '3 meses de acesso com desconto. Economia de R$50 comparado ao mensal.',
                'is_active'     => true,
            ]
        );

        $anual = Plan::firstOrCreate(
            ['name' => 'Anual'],
            [
                'duration_days' => 365,
                'price'         => 899.90,
                'description'   => 'O melhor custo-benefício! 12 meses de acesso com o maior desconto.',
                'is_active'     => true,
            ]
        );

        // ═══════════════════════════════════════
        // ASSINATURAS
        // ═══════════════════════════════════════

        Subscription::firstOrCreate(
            ['user_id' => $aluno->id, 'status' => 'active'],
            [
                'plan_id'    => $trimestral->id,
                'starts_at'  => Carbon::today()->subDays(15),
                'expires_at' => Carbon::today()->addDays(75),
                'status'     => 'active',
            ]
        );

        Subscription::firstOrCreate(
            ['user_id' => $aluno2->id, 'status' => 'active'],
            [
                'plan_id'    => $mensal->id,
                'starts_at'  => Carbon::today()->subDays(25),
                'expires_at' => Carbon::today()->addDays(5),
                'status'     => 'active',
            ]
        );

        // aluno3 sem plano ativo (para testar inadimplente)

        // ═══════════════════════════════════════
        // PRODUTOS
        // ═══════════════════════════════════════

        Product::firstOrCreate(
            ['name' => 'Whey Protein 1kg'],
            [
                'category'       => 'Suplementos',
                'description'    => 'Proteína de alta qualidade para ganho muscular rápido. Sabor chocolate.',
                'price'          => 149.90,
                'stock_quantity' => 25,
                'image_url'      => 'https://images.unsplash.com/photo-1593095948071-474c5cc2c2b0?w=400&h=300&fit=crop',
            ]
        );

        Product::firstOrCreate(
            ['name' => 'Creatina 300g'],
            [
                'category'       => 'Suplementos',
                'description'    => 'Aumento de força e resistência para treinos intensos. Monohidratada.',
                'price'          => 89.90,
                'stock_quantity' => 40,
                'image_url'      => 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?w=400&h=300&fit=crop',
            ]
        );

        Product::firstOrCreate(
            ['name' => 'Camiseta Techfit Dry-Fit'],
            [
                'category'       => 'Vestuário',
                'description'    => 'Tecido dry-fit com tecnologia de absorção de suor. Conforto máximo.',
                'price'          => 59.90,
                'stock_quantity' => 50,
                'image_url'      => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=300&fit=crop',
            ]
        );

        Product::firstOrCreate(
            ['name' => 'Garrafa Premium 1L'],
            [
                'category'       => 'Acessórios',
                'description'    => 'Garrafa premium com marcação de volume e tampa flip. Livre de BPA.',
                'price'          => 35.00,
                'stock_quantity' => 60,
                'image_url'      => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=400&h=300&fit=crop',
            ]
        );

        Product::firstOrCreate(
            ['name' => 'Luva de Treino Pro'],
            [
                'category'       => 'Acessórios',
                'description'    => 'Luva acolchoada com suporte de pulso. Ideal para musculação pesada.',
                'price'          => 45.90,
                'stock_quantity' => 30,
                'image_url'      => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=400&h=300&fit=crop',
            ]
        );

        Product::firstOrCreate(
            ['name' => 'Barra de Proteína (cx 12un)'],
            [
                'category'       => 'Suplementos',
                'description'    => 'Caixa com 12 barras de proteína. Sabores variados. Lanche pós-treino perfeito.',
                'price'          => 79.90,
                'stock_quantity' => 35,
                'image_url'      => 'https://images.unsplash.com/photo-1622484212850-eb596d769edc?w=400&h=300&fit=crop',
            ]
        );

        // ═══════════════════════════════════════
        // AULAS
        // ═══════════════════════════════════════

        GymClass::firstOrCreate(
            ['name' => 'Musculação Guiada'],
            [
                'description'   => 'Treino de musculação com acompanhamento de instrutor.',
                'instructor_id' => $employee->id,
                'schedule'      => 'Segunda 08:00',
                'day_of_week'   => 'Segunda',
                'start_time'    => '08:00',
                'end_time'      => '09:00',
                'capacity'      => 20,
                'is_active'     => true,
            ]
        );

        GymClass::firstOrCreate(
            ['name' => 'CrossFit'],
            [
                'description'   => 'Treino funcional de alta intensidade para todos os níveis.',
                'instructor_id' => $employee->id,
                'schedule'      => 'Terça 07:00',
                'day_of_week'   => 'Terça',
                'start_time'    => '07:00',
                'end_time'      => '08:00',
                'capacity'      => 15,
                'is_active'     => true,
            ]
        );

        GymClass::firstOrCreate(
            ['name' => 'Yoga & Relaxamento'],
            [
                'description'   => 'Sessão de yoga para flexibilidade, equilíbrio e bem-estar mental.',
                'instructor_id' => $employee->id,
                'schedule'      => 'Quarta 18:00',
                'day_of_week'   => 'Quarta',
                'start_time'    => '18:00',
                'end_time'      => '19:00',
                'capacity'      => 25,
                'is_active'     => true,
            ]
        );

        GymClass::firstOrCreate(
            ['name' => 'Spinning'],
            [
                'description'   => 'Aula intensa de ciclismo indoor com música motivacional.',
                'instructor_id' => $employee->id,
                'schedule'      => 'Quinta 06:30',
                'day_of_week'   => 'Quinta',
                'start_time'    => '06:30',
                'end_time'      => '07:30',
                'capacity'      => 18,
                'is_active'     => true,
            ]
        );

        // ═══════════════════════════════════════
        // VENDAS DE EXEMPLO
        // ═══════════════════════════════════════

        Sale::firstOrCreate(
            ['user_id' => $aluno->id, 'total_price' => 149.90],
            [
                'status' => 'paid',
            ]
        );

        // ═══════════════════════════════════════
        // CHECK-INS DE EXEMPLO
        // ═══════════════════════════════════════

        Checkin::firstOrCreate(
            ['user_id' => $aluno->id, 'checked_in_at' => Carbon::today()->setTime(7, 30)],
            [
                'checked_in_by' => $employee->id,
            ]
        );
    }
}
