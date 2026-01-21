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
use App\Http\Controllers\Trainer\TrainingManageController;
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

    // Moje tréningy page
    Route::get('/moje-treningy', [MyTrainingsController::class, 'index'])
        ->name('my-trainings.index');

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
            Route::get('users/autocomplete', [AdminUserController::class, 'autocomplete'])->name('users.autocomplete');
            Route::resource('users', AdminUserController::class)->except(['show']);

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
    });
});
