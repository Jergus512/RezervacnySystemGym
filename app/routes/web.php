<?php

use App\Http\Controllers\Admin\TrainingController as AdminTrainingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\MyTrainingsController;
use App\Http\Controllers\Reception\CreditsController as ReceptionCreditsController;
use App\Http\Controllers\Reception\TrainingController as ReceptionTrainingController;
use App\Http\Controllers\Reception\UnregistrationController as ReceptionUnregistrationController;
use App\Http\Controllers\Trainer\TrainingManageController;
use App\Http\Controllers\Trainer\TrainerStatisticsController;
use App\Http\Controllers\TrainingCalendarController;
use App\Http\Controllers\TrainingRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

// Public JSON events feed for FullCalendar
Route::get('/training-events', [TrainingCalendarController::class, 'events'])->name('training-calendar.events');

// Authentication
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware('auth')->group(function () {
    // Training calendar - page
    Route::get('/kalendar-treningov', [TrainingCalendarController::class, 'index'])->name('training-calendar.index');

    // Training registration
    Route::post('/trainings/{training}/register', [TrainingRegistrationController::class, 'store'])
        ->name('trainings.register');

    // Training unregistration (refund credits)
    Route::delete('/trainings/{training}/register', [TrainingRegistrationController::class, 'destroy'])
        ->name('trainings.unregister');

    // Moje tréningy page (hlavná stránka na správu tréningov)
    Route::get('/moje-treningy', [MyTrainingsController::class, 'index'])
        ->name('my-trainings.index');

    // Štatistiky - kredity & tréningy
    Route::get('/moje-kredity/evidencia', [\App\Http\Controllers\UserCreditsController::class, 'index'])
        ->name('user-credits.history');

    Route::get('/moje-treningy/statistiky', [\App\Http\Controllers\UserCreditsController::class, 'trainings'])
        ->name('user-trainings.history');

    // Oznamy (pre všetkých prihlásených)
    Route::get('/oznamy', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/oznamy/current.json', [AnnouncementController::class, 'current'])->name('announcements.current');

    // Current user (regular user) JSON endpoints
    Route::get('/me/credits', [MeController::class, 'credits'])->name('me.credits');


    Route::prefix('reception')->name('reception.')->middleware('reception')->group(function () {
        // No separate receptionist dashboard; navigation is in the shared topbar.
        Route::get('/', function () {
            return redirect()->route('reception.calendar');
        })->name('home');

        // Read-only calendar
        Route::get('/kalendar-treningov', function () {
            return view('training.calendar');
        })->name('calendar');

        Route::get('/pridanie-kreditov', [ReceptionCreditsController::class, 'create'])->name('credits.create');
        Route::get('/pridanie-kreditov/search', [ReceptionCreditsController::class, 'search'])->name('credits.search');
        Route::post('/pridanie-kreditov', [ReceptionCreditsController::class, 'store'])->name('credits.store');
        Route::get('/pridanie-kreditov/{user}/credits', [ReceptionCreditsController::class, 'credits'])->name('credits.current');

        // Reception user unregistration from trainings
        Route::get('/odhlasenie-z-treningov', [ReceptionUnregistrationController::class, 'index'])->name('unregistration.index');
        Route::get('/odhlasenie-z-treningov/search', [ReceptionUnregistrationController::class, 'search'])->name('unregistration.search');
        Route::get('/odhlasenie-z-treningov/{user}/trainings', [ReceptionUnregistrationController::class, 'trainings'])->name('unregistration.trainings');
        Route::post('/odhlasenie-z-treningov', [ReceptionUnregistrationController::class, 'unregister'])->name('unregistration.unregister');

        // Reception training cancellation page
        Route::get('/zrusenie-treningov', [ReceptionTrainingController::class, 'index'])->name('trainings.index');
        Route::post('/treningy/{training}/toggle-active', [ReceptionTrainingController::class, 'toggleActive'])->name('trainings.toggle');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::group(['middleware' => function ($request, $next) {
            /** @var \App\Models\User|null $user */
            $user = auth()->user();

            if (! $user || ! $user->isAdmin()) {
                abort(403);
            }

            return $next($request);
        }], function () {
            // Admin training management
            Route::get('trainings', [AdminTrainingController::class, 'index'])->name('trainings.index');
            Route::get('trainings/archive', [AdminTrainingController::class, 'archive'])->name('trainings.archive');
            Route::get('trainings/{training}/edit', [AdminTrainingController::class, 'edit'])->name('trainings.edit');
            Route::put('trainings/{training}', [AdminTrainingController::class, 'update'])->name('trainings.update');
            Route::delete('trainings/{training}', [AdminTrainingController::class, 'destroy'])->name('trainings.destroy');

            // Admin oznamy
            Route::get('announcements/archive', [AdminAnnouncementController::class, 'archive'])
                ->name('announcements.archive');
            Route::resource('announcements', AdminAnnouncementController::class)->except(['show']);

            // Admin analytics
            Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])
                ->name('analytics.index');

            // Odmeny trénerov podľa hodnotenia
            Route::get('analytics/trainer-rewards', [\App\Http\Controllers\Admin\AnalyticsController::class, 'trainerRewards'])
                ->name('analytics.trainer-rewards');

            // Admin settings - penalty configuration for refunds
            Route::get('settings', [\App\Http\Controllers\Admin\PenaltySettingController::class, 'edit'])
                ->name('settings.edit');
            Route::put('settings', [\App\Http\Controllers\Admin\PenaltySettingController::class, 'update'])
                ->name('settings.update');

            // Admin audit logs
            Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])
                ->name('audit-logs.index');
            Route::get('audit-logs/{audit}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])
                ->name('audit-logs.show');
            Route::get('trainings/{training}/audit', [\App\Http\Controllers\Admin\AuditLogController::class, 'training'])
                ->name('audit-logs.training');

            Route::get('users/autocomplete', [AdminUserController::class, 'autocomplete'])->name('users.autocomplete');
            Route::resource('users', AdminUserController::class)->except(['show']);
        });
    });

    Route::prefix('trainer')->name('trainer.')->group(function () {
        Route::get('/vytvorene-treningy', [TrainingManageController::class, 'index'])
            ->name('trainings.index');

        Route::get('/vytvorenie-treningu', [TrainingManageController::class, 'create'])
            ->name('trainings.create');
        Route::post('/vytvorenie-treningu', [TrainingManageController::class, 'store'])
            ->name('trainings.store');

        Route::get('/treningy/{training}/edit', [TrainingManageController::class, 'edit'])
            ->name('trainings.edit');
        Route::put('/treningy/{training}', [TrainingManageController::class, 'update'])
            ->name('trainings.update');
        Route::delete('/treningy/{training}', [TrainingManageController::class, 'destroy'])
            ->name('trainings.destroy');

        Route::post('/treningy/{training}/cancel', [TrainingManageController::class, 'cancel'])
            ->name('trainings.cancel');

        // Štatistiky trénera - využijeme existujúcu AnalyticsController logiku
        Route::get('/statistiky', [TrainerStatisticsController::class, 'index'])
            ->name('stats.index');
    });

    // Routes pre hodnotenie trénerov (pre všetkých prihlásenych používateľov)
    Route::post('/trainers/{trainer}/ratings', [\App\Http\Controllers\TrainerRatingController::class, 'store'])
        ->name('trainer-ratings.store');
    Route::get('/trainers/{trainer}/ratings', [\App\Http\Controllers\TrainerRatingController::class, 'getTrainerRatings'])
        ->name('trainer-ratings.index');
    Route::get('/trainers/{trainer}/ratings/check', [\App\Http\Controllers\TrainerRatingController::class, 'getUserRatingForTraining'])
        ->name('trainer-ratings.check');
    Route::get('/trainings/{training}/my-rating', [\App\Http\Controllers\TrainerRatingController::class, 'getUserRatingForTraining'])
        ->name('trainer-ratings.my-rating');
});
