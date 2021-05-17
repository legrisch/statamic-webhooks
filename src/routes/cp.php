<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
  Route::get('/legrisch/statamic-webhooks', 'SettingsController@index')->name('legrisch.statamic-webhooks.index');
  Route::post('/legrisch/statamic-webhooks', 'SettingsController@update')->name('legrisch.statamic-webhooks.update');
});
