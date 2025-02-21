<?php

use Illuminate\Support\Facades\Route;
use Legrisch\StatamicWebhooks\Http\Controllers\SettingsController;

Route::middleware('web')->group(function () {
  Route::get('/legrisch/statamic-webhooks', [SettingsController::class, 'index'])->name('legrisch.statamic-webhooks.index');
  Route::post('/legrisch/statamic-webhooks', [SettingsController::class, 'update'])->name('legrisch.statamic-webhooks.update');
});
