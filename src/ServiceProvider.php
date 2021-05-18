<?php

namespace Legrisch\StatamicWebhooks;

use Statamic\Providers\AddonServiceProvider;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;

use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Events\AssetContainerDeleted;
use Statamic\Events\AssetContainerSaved;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetFolderDeleted;
use Statamic\Events\AssetFolderSaved;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Events\BlueprintDeleted;
use Statamic\Events\BlueprintSaved;
use Statamic\Events\CollectionDeleted;
use Statamic\Events\CollectionSaved;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntrySaving;
use Statamic\Events\FieldsetDeleted;
use Statamic\Events\FieldsetSaved;
use Statamic\Events\FormBlueprintFound;
use Statamic\Events\FormDeleted;
use Statamic\Events\FormSaved;
use Statamic\Events\FormSubmitted;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\GlobalVariablesBlueprintFound;
use Statamic\Events\NavDeleted;
use Statamic\Events\NavSaved;
use Statamic\Events\ResponseCreated;
use Statamic\Events\RoleDeleted;
use Statamic\Events\RoleSaved;
use Statamic\Events\SubmissionCreated;
use Statamic\Events\SubmissionDeleted;
use Statamic\Events\SubmissionSaved;
use Statamic\Events\TaxonomyDeleted;
use Statamic\Events\TaxonomySaved;
use Statamic\Events\TermBlueprintFound;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermSaved;
use Statamic\Events\UserDeleted;
use Statamic\Events\UserGroupDeleted;
use Statamic\Events\UserGroupSaved;
use Statamic\Events\UserSaved;

class EntryPolicy
{
  public function edit($user)
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
    AssetContainerBlueprintFound::class => [EventListener\EventListener::class],
    AssetContainerDeleted::class => [EventListener\EventListener::class],
    AssetContainerSaved::class => [EventListener\EventListener::class],
    AssetDeleted::class => [EventListener\EventListener::class],
    AssetFolderDeleted::class => [EventListener\EventListener::class],
    AssetFolderSaved::class => [EventListener\EventListener::class],
    AssetSaved::class => [EventListener\EventListener::class],
    AssetUploaded::class => [EventListener\EventListener::class],
    BlueprintDeleted::class => [EventListener\EventListener::class],
    BlueprintSaved::class => [EventListener\EventListener::class],
    CollectionDeleted::class => [EventListener\EventListener::class],
    CollectionSaved::class => [EventListener\EventListener::class],
    EntryBlueprintFound::class => [EventListener\EventListener::class],
    EntryDeleted::class => [EventListener\EventListener::class],
    EntrySaved::class => [EventListener\EventListener::class],
    EntrySaving::class => [EventListener\EventListener::class],
    FieldsetDeleted::class => [EventListener\EventListener::class],
    FieldsetSaved::class => [EventListener\EventListener::class],
    FormBlueprintFound::class => [EventListener\EventListener::class],
    FormDeleted::class => [EventListener\EventListener::class],
    FormSaved::class => [EventListener\EventListener::class],
    FormSubmitted::class => [EventListener\EventListener::class],
    GlobalSetDeleted::class => [EventListener\EventListener::class],
    GlobalSetSaved::class => [EventListener\EventListener::class],
    GlobalVariablesBlueprintFound::class => [EventListener\EventListener::class],
    NavDeleted::class => [EventListener\EventListener::class],
    NavSaved::class => [EventListener\EventListener::class],
    ResponseCreated::class => [EventListener\EventListener::class],
    RoleDeleted::class => [EventListener\EventListener::class],
    RoleSaved::class => [EventListener\EventListener::class],
    SubmissionCreated::class => [EventListener\EventListener::class],
    SubmissionDeleted::class => [EventListener\EventListener::class],
    SubmissionSaved::class => [EventListener\EventListener::class],
    TaxonomyDeleted::class => [EventListener\EventListener::class],
    TaxonomySaved::class => [EventListener\EventListener::class],
    TermBlueprintFound::class => [EventListener\EventListener::class],
    TermDeleted::class => [EventListener\EventListener::class],
    TermSaved::class => [EventListener\EventListener::class],
    UserDeleted::class => [EventListener\EventListener::class],
    UserGroupDeleted::class => [EventListener\EventListener::class],
    UserGroupSaved::class => [EventListener\EventListener::class],
    UserSaved::class => [EventListener\EventListener::class],
  ];

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
