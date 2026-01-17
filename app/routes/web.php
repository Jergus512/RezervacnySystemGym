<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\TrainingCalendarController;
use App\Http\Controllers\TrainingRegistrationController;
use App\Http\Controllers\MyTrainingsController;
use App\Http\Controllers\Trainer\TrainingManageController;

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
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

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

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::group(['middleware' => function ($request, $next) {
            if (! auth()->user() || ! auth()->user()->is_admin) {
                abort(403);
            }
            return $next($request);
        }], function () {
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
    });
});
