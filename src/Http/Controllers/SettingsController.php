<?php

namespace Legrisch\StatamicWebhooks\Http\Controllers;

use Legrisch\StatamicWebhooks\Settings\Settings;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Config;
use Statamic\Facades\Git;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class SettingsController extends CpController
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function index(Request $request)
  {
    if (!User::current()->can('manage webhooks')) {
      // TODO naive Permissions handling
      return;
    }

    $blueprint = $this->formBlueprint();
    $fields = $blueprint->fields();

    $values = Settings::read(false);

    $fields = $fields->addValues($values);

    $fields = $fields->preProcess();

    return view('statamic-webhooks::settings', [
      'blueprint' => $blueprint->toPublishArray(),
      'values' => $fields->values(),
      'meta' => $fields->meta(),
    ]);
  }

  public function update(Request $request)
  {
    if (!User::current()->can('manage webhooks')) {
      // TODO naive Permissions handling
      return;
    }

    $blueprint = $this->formBlueprint();
    $fields = $blueprint->fields()->addValues($request->all());

    // Perform validation. Like Laravel's standard validation, if it fails,
    // a 422 response will be sent back with all the validation errors.
    $fields->validate();

    // Perform post-processing. This will convert values the Vue components
    // were using into values suitable for putting into storage.
    $values = $fields->process()->values();

    Settings::write($values->toArray());

    if (Config::get('statamic.git.enabled', false)) {
      Git::commit(__('statamic-webhooks::general.git_commit_message'));
    }
  }

  protected function formBlueprint()
  {

    $events = [
      'Statamic\Events\AssetContainerBlueprintFound' => 'AssetContainerBlueprintFound',
      'Statamic\Events\AssetContainerDeleted' => 'AssetContainerDeleted',
      'Statamic\Events\AssetContainerSaved' => 'AssetContainerSaved',
      'Statamic\Events\AssetDeleted' => 'AssetDeleted',
      'Statamic\Events\AssetFolderDeleted' => 'AssetFolderDeleted',
      'Statamic\Events\AssetFolderSaved' => 'AssetFolderSaved',
      'Statamic\Events\AssetSaved' => 'AssetSaved',
      'Statamic\Events\AssetUploaded' => 'AssetUploaded',
      'Statamic\Events\BlueprintDeleted' => 'BlueprintDeleted',
      'Statamic\Events\BlueprintSaved' => 'BlueprintSaved',
      'Statamic\Events\CollectionDeleted' => 'CollectionDeleted',
      'Statamic\Events\CollectionSaved' => 'CollectionSaved',
      'Statamic\Events\EntryBlueprintFound' => 'EntryBlueprintFound',
      'Statamic\Events\EntryDeleted' => 'EntryDeleted',
      'Statamic\Events\EntrySaved' => 'EntrySaved',
      'Statamic\Events\EntrySaving' => 'EntrySaving',
      'Statamic\Events\FieldsetDeleted' => 'FieldsetDeleted',
      'Statamic\Events\FieldsetSaved' => 'FieldsetSaved',
      'Statamic\Events\FormBlueprintFound' => 'FormBlueprintFound',
      'Statamic\Events\FormDeleted' => 'FormDeleted',
      'Statamic\Events\FormSaved' => 'FormSaved',
      'Statamic\Events\FormSubmitted' => 'FormSubmitted',
      'Statamic\Events\GlobalSetDeleted' => 'GlobalSetDeleted',
      'Statamic\Events\GlobalSetSaved' => 'GlobalSetSaved',
      'Statamic\Events\GlobalVariablesBlueprintFound' => 'GlobalVariablesBlueprintFound',
      'Statamic\Events\NavDeleted' => 'NavDeleted',
      'Statamic\Events\NavSaved' => 'NavSaved',
      'Statamic\Events\ResponseCreated' => 'ResponseCreated',
      'Statamic\Events\RoleDeleted' => 'RoleDeleted',
      'Statamic\Events\RoleSaved' => 'RoleSaved',
      'Statamic\Events\SubmissionCreated' => 'SubmissionCreated',
      'Statamic\Events\SubmissionDeleted' => 'SubmissionDeleted',
      'Statamic\Events\SubmissionSaved' => 'SubmissionSaved',
      'Statamic\Events\TaxonomyDeleted' => 'TaxonomyDeleted',
      'Statamic\Events\TaxonomySaved' => 'TaxonomySaved',
      'Statamic\Events\TermBlueprintFound' => 'TermBlueprintFound',
      'Statamic\Events\TermDeleted' => 'TermDeleted',
      'Statamic\Events\TermSaved' => 'TermSaved',
      'Statamic\Events\UserDeleted' => 'UserDeleted',
      'Statamic\Events\UserGroupDeleted' => 'UserGroupDeleted',
      'Statamic\Events\UserGroupSaved' => 'UserGroupSaved',
      'Statamic\Events\UserSaved' => 'UserSaved',
    ];


    return Blueprint::makeFromSections([
      'webhooks' => [
        'fields' => [
          'webhooks' => [
            'type' => 'replicator',
            'collapse' => true,
            'display' => 'Webhooks',
            'sets' => [
              'webhook' => [
                'display' => 'Webhook',
                'fields' => [
                  'name' => [
                    'handle' => 'name',
                    'field' => [
                      'input_type' => 'text',
                      'antlers' => false,
                      'display' => "Name",
                      'type' => "text",
                      'icon' => "text",
                      'width' => 100,
                      'listable' => "visible",
                      'validate' => ['required', 'alphadash']
                    ],
                  ],
                  'url' => [
                    'handle' => 'url',
                    'field' => [
                      'input_type' => 'text',
                      'antlers' => false,
                      'display' => "Url",
                      'type' => "text",
                      'icon' => "text",
                      'width' => 100,
                      'listable' => "hidden",
                      'validate' => ['required', 'url']
                    ],
                  ],
                  'debounced' => [
                    'handle' => 'debounced',
                    'field' => [
                      'type' => 'toggle',
                      'default' => false,
                      'display' => __('statamic-webhooks::general.debounced'),
                      'instructions' => __('statamic-webhooks::general.debounced_instructions'),
                      'width' => 50,
                      'listable' => "hidden",
                      'validate' => ['required']
                    ]
                  ],
                  'debounce_in_seconds' => [
                    'handle' => 'debounce_in_seconds',
                    'field' => [
                      'type' => 'float',
                      'default' => 0,
                      'icon' => 'float',
                      'display' => __('statamic-webhooks::general.debounce_in_seconds'),
                      // 'instructions' => __('statamic-webhooks::general.debounce_in_seconds_instructions'),
                      'width' => 50,
                      'listable' => "hidden",
                      'validate' => ['required_if:debounced,true'],
                      'if' => [
                        'debounced' => 'equals true'
                      ]
                    ]
                  ],
                  'include_payload' => [
                    'handle' => 'include_payload',
                    'field' => [
                      'type' => 'toggle',
                      'default' => true,
                      'display' => __('statamic-webhooks::general.include_payload_display'),
                      'instructions' => __('statamic-webhooks::general.include_payload_instructions'),
                      'width' => 100,
                      'listable' => "hidden",
                      'validate' => ['required']
                    ],
                  ],
                  'events' => [
                    'handle' => 'events',
                    'field' => [
                      'type' => 'select',
                      'display' => __('statamic-webhooks::general.events_display'),
                      'instructions' => __('statamic-webhooks::general.events_instructions'),
                      'options' => $events,
                      'validate' => ['required'],
                      'multiple' => true,
                      'placeholder' => __('statamic-webhooks::general.events_placeholder'),
                      'taggable' => true,
                    ]
                  ],
                  'headers' => [
                    'handle' => 'headers',
                    'field' => [
                      'type' => 'replicator',
                      'display' => __('statamic-webhooks::general.headers_display'),
                      'sets' => [
                        'header' => [
                          'display' => __('statamic-webhooks::general.header_set_display'),
                          'fields' => [
                            'key' => [
                              'handle' => 'key',
                              'field' => [
                                'input_type' => 'text',
                                'antlers' => false,
                                'display' => __('statamic-webhooks::general.header_set_key'),
                                'type' => "text",
                                'icon' => "text",
                                'width' => 50,
                                'listable' => "hidden",
                                'validate' => ['required']
                              ],
                            ],
                            'value' => [
                              'handle' => 'value',
                              'field' => [
                                'input_type' => 'text',
                                'antlers' => false,
                                'display' => __('statamic-webhooks::general.header_set_value'),
                                'type' => "text",
                                'icon' => "text",
                                'width' => 50,
                                'listable' => "hidden",
                                'validate' => ['required']
                              ],
                            ],
                          ]
                        ]
                      ]
                    ]
                  ],
                  'request_body' => [
                    'handle' => 'request_body',
                    'field' => [
                      'type' => 'textarea',
                      'display' => __('statamic-webhooks::general.request_body_display'),
                      'instructions' => __('statamic-webhooks::general.request_body_instructions'),
                    ]
                  ],
                ]
              ]
            ]
          ],
        ],
      ],
    ]);
  }
}
