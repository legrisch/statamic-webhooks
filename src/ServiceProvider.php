<?php

namespace Legrisch\StatamicWebhooks;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Events\AssetSaved;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;

class EntryPolicy
{
  public function edit($user, $entry)
  {
    return $user->hasPermission("manage webhooks");
  }
}

class ServiceProvider extends AddonServiceProvider
{

  protected $routes = [
    'cp' => __DIR__ . '/routes/cp.php',
  ];

  protected $listen = [
    AssetSaved::class => [
      EventListener\EventListener::class
    ],
  ];

  public function __call($action, $event)
  {
    list($type, $action) = explode('.', $action);

    $this->invoke($type, $action, $event);
  }

  public function boot()
  {
    parent::boot();

    $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'statamic-webhooks');
    $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'statamic-webhooks');

    Nav::extend(function ($nav) {
      $nav->content('Webhooks')
        ->section('Tools')
        ->can('manage webhooks')
        ->route('legrisch.statamic-webhooks.index')
        ->icon('hyperlink');
    });

    $this->app->booted(function () {
      Permission::register('manage webhooks')
        ->label('Manage Webhooks');
    });
  }
}
