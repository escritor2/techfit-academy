<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AIController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GymClassController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ClassBookingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NutritionController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\WebhookController;

// ROTAS PÚBLICAS

Route::middleware('throttle:6,1')->group(function () {
    // Rota de fallback para autenticação (evita erro 500 se o token expirar)
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorized. Please login again.'], 401);
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [ProfileController::class, 'forgotPassword']);
    Route::post('/reset-password', [ProfileController::class, 'resetPassword']);
});

// Catálogo público
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/gym-classes', [GymClassController::class, 'index']);
Route::get('/plans', [PlanController::class, 'index']);

Route::get('/debug-data', function() {
    return [
        'users_total' => \App\Models\User::count(),
        'members_total' => \App\Models\User::where('role', 'member')->count(),
        'products_total' => \App\Models\Product::count(),
        'tenants_total' => \App\Models\Tenant::count(),
        'database_name' => config('database.connections.'.config('database.default').'.database'),
    ];
});

// WEBHOOKS DE PAGAMENTO (Públicas, sem auth)

Route::post('/webhooks/stripe', [WebhookController::class, 'stripe']);
Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadoPago']);

// ROTAS PROTEGIDAS (AUTH:SANCTUM)

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ──
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ── Perfil do Usuário ──
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);

    // ── IA / Treinos ──
    Route::post('/ai/generate-workout', [AIController::class, 'generateWorkout']);
    Route::get('/ai/workout-status/{id}', [AIController::class, 'workoutStatus']);
    Route::get('/my/workouts', [AIController::class, 'myWorkouts']);

    // ── Aulas / Reservas ──
    Route::post('/class-bookings', [ClassBookingController::class, 'store']);
    Route::delete('/class-bookings/{id}', [ClassBookingController::class, 'cancel']);
    Route::get('/my/bookings', [ClassBookingController::class, 'myBookings']);

    // ── Vendas (Membro compra na loja) ──
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/my/sales', [SaleController::class, 'index']);

    // ── Métricas Corporais ──
    Route::get('/metrics', [MetricsController::class, 'index']);
    Route::post('/metrics', [MetricsController::class, 'store']);
    Route::get('/metrics/history', [MetricsController::class, 'history']);

    // ── Nutrição / Dieta ──
    Route::post('/nutrition/generate', [NutritionController::class, 'generate']);
    Route::get('/nutrition/latest', [NutritionController::class, 'latest']);

    // ── Rankings & Conquistas ──
    Route::get('/ranking/global', [RankingController::class, 'leaderboard']);
    Route::get('/ranking/monthly', [RankingController::class, 'monthly']);
    Route::get('/achievements', [AchievementController::class, 'myAchievements']);
    Route::get('/achievements/all', [AchievementController::class, 'index']);

    // ── Dashboards Específicos ──
    Route::get('/member/dashboard', [DashboardController::class, 'getMemberData'])->middleware('role:member,admin');
    Route::get('/employee/dashboard', [DashboardController::class, 'getEmployeeData'])->middleware('role:employee,admin');
    Route::get('/admin/dashboard', [DashboardController::class, 'getAdminData'])->middleware('role:admin');

    // ── Gestão de Membros & Check-ins (Admin/Employee) ──
    Route::middleware('role:admin,employee')->group(function () {
        Route::post('/checkins', [CheckinController::class, 'store']);
        Route::get('/checkins/today', [CheckinController::class, 'todayList']);

        Route::get('/admin/members', [MemberController::class, 'index']);
        Route::post('/admin/members', [MemberController::class, 'store']);
        Route::get('/admin/members/{id}', [MemberController::class, 'show']);
        Route::put('/admin/members/{id}', [MemberController::class, 'update']);

        Route::get('/admin/subscriptions', [SubscriptionController::class, 'index']);
        Route::post('/admin/subscriptions', [SubscriptionController::class, 'store']);
        Route::get('/admin/subscriptions/{id}', [SubscriptionController::class, 'show']);
        Route::post('/subscriptions/checkout', [SubscriptionController::class, 'checkout']);
    });

    // ── Gestão Administrativa (Admin Only) ──
    Route::middleware('role:admin')->group(function () {
        Route::delete('/admin/members/{id}', [MemberController::class, 'destroy']);

        // Gestão de Produtos
        Route::post('/admin/products', [ProductController::class, 'store']);
        Route::put('/admin/products/{id}', [ProductController::class, 'update']);
        Route::delete('/admin/products/{id}', [ProductController::class, 'destroy']);

        // Gestão de Planos
        Route::post('/admin/plans', [PlanController::class, 'store']);
        Route::put('/admin/plans/{id}', [PlanController::class, 'update']);
        Route::delete('/admin/plans/{id}', [PlanController::class, 'destroy']);

        // Gestão de Aulas
        Route::post('/admin/gym-classes', [GymClassController::class, 'store']);
        Route::put('/admin/gym-classes/{id}', [GymClassController::class, 'update']);
        Route::delete('/admin/gym-classes/{id}', [GymClassController::class, 'destroy']);

        // Gestão de Assinaturas (Remoção)
        Route::delete('/admin/subscriptions/{id}', [SubscriptionController::class, 'destroy']);
    });
});
