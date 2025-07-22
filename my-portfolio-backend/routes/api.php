<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HeroController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Hero section routes
Route::get('/hero', [HeroController::class, 'index']);
Route::post('/hero', [HeroController::class, 'store']);

// Skills routes
Route::apiResource('skills', SkillController::class);
Route::delete('/skill-items/{id}', [SkillController::class, 'destroyItem']);

// Projects routes
Route::apiResource('projects', ProjectController::class);

// Services routes
Route::get('/service-section', [ServiceController::class, 'getServiceSection']);
Route::put('/service-section', [ServiceController::class, 'updateServiceSection']);
Route::get('/services', [ServiceController::class, 'getServices']);
Route::get('/services/{id}', [ServiceController::class, 'getService']);
Route::post('/services', [ServiceController::class, 'storeService']);
Route::put('/services/{id}', [ServiceController::class, 'updateService']);
Route::delete('/services/{id}', [ServiceController::class, 'deleteService']);

// Experience routes
Route::get('/experience-section', [ExperienceController::class, 'getExperienceSection']);
Route::put('/experience-section', [ExperienceController::class, 'updateExperienceSection']);
Route::get('/experiences', [ExperienceController::class, 'getExperiences']);
Route::get('/experiences/{id}', [ExperienceController::class, 'getExperience']);
Route::post('/experiences', [ExperienceController::class, 'storeExperience']);
Route::put('/experiences/{id}', [ExperienceController::class, 'updateExperience']);
Route::delete('/experiences/{id}', [ExperienceController::class, 'deleteExperience']);

Route::apiResource('contacts', ContactController::class);
