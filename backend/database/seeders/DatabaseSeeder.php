<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\GymClass;
use App\Models\Sale;
use App\Models\Checkin;
use App\Models\Achievement;
use App\Models\Tenant;
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
        // TENANTS (Academias)
        // ═══════════════════════════════════════
        $tenant = Tenant::firstOrCreate(
            ['domain' => 'app.techfit.com'],
            [
                'name' => 'TechFit Academy (Matriz)',
                'settings' => json_encode(['primary_color' => '#00f2ff', 'logo_url' => '']),
                'is_active' => true,
            ]
        );

        // Define o tenant atual para que as models recebam o tenant_id via trait
        session(['tenant_id' => $tenant->id]);

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

        Product::updateOrCreate(
            ['name' => 'Whey Protein 1kg'],
            [
                'category'       => 'Suplementos',
                'description'    => 'Proteína Premium Techfit. Pureza máxima e sabor chocolate belga.',
                'price'          => 149.90,
                'stock_quantity' => 25,
                'image_url'      => '/products/whey.png',
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Creatina 300g'],
            [
                'category'       => 'Suplementos',
                'description'    => 'Creatina Monohidratada Techfit. Força bruta e recuperação rápida.',
                'price'          => 89.90,
                'stock_quantity' => 40,
                'image_url'      => '/products/creatina.png',
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Camiseta Techfit Dry-Fit'],
            [
                'category'       => 'Vestuário',
                'description'    => 'Regata Techfit Black Edition. Tecido ultra-leve para performance extrema.',
                'price'          => 59.90,
                'stock_quantity' => 50,
                'image_url'      => '/products/camiseta.png',
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Garrafa Premium 1L'],
            [
                'category'       => 'Acessórios',
                'description'    => 'Shaker Techfit Black Stealth. Minimalista, 1L de pura hidratação.',
                'price'          => 35.00,
                'stock_quantity' => 60,
                'image_url'      => '/products/garrafa.png',
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Luva de Treino Pro'],
            [
                'category'       => 'Acessórios',
                'description'    => 'Luvas Techfit Grip Pro. Proteção e aderência para treinos pesados.',
                'price'          => 45.90,
                'stock_quantity' => 30,
                'image_url'      => 'https://images.unsplash.com/photo-1599058917232-d750c1859d7c?w=400&h=300&fit=crop',
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Barra de Proteína (cx 12un)'],
            [
                'category'       => 'Suplementos',
                'description'    => 'Techfit Protein Bar Black. O lanche perfeito para sua rotina de elite.',
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

        // ═══════════════════════════════════════
        // CONQUISTAS
        // ═══════════════════════════════════════

        Achievement::firstOrCreate(
            ['name' => 'Primeiro Passo'],
            [
                'description' => 'Realizou seu primeiro check-in na academia.',
                'icon' => '👟',
                'type' => 'checkin_count',
                'required_value' => 1,
            ]
        );

        Achievement::firstOrCreate(
            ['name' => 'Frequentador Assíduo'],
            [
                'description' => 'Completou 10 check-ins.',
                'icon' => '🔥',
                'type' => 'checkin_count',
                'required_value' => 10,
            ]
        );

        Achievement::firstOrCreate(
            ['name' => 'Rato de Academia'],
            [
                'description' => 'Completou 30 check-ins.',
                'icon' => '🐭',
                'type' => 'checkin_count',
                'required_value' => 30,
            ]
        );

        Achievement::firstOrCreate(
            ['name' => 'Mestre da Estratégia'],
            [
                'description' => 'Gerou 5 treinos com IA.',
                'icon' => '🧠',
                'type' => 'workout_count',
                'required_value' => 5,
            ]
        );
    }
}
