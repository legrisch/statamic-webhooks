<?php

return [
  'git_commit_message' => 'Webhook settings changed',
  'events_display' => 'Events',
  'events_instructions' => 'Read here about the individual events: [https://statamic.dev/extending/events#available-events](https://statamic.dev/extending/events#available-events)',
  'events_placeholder' => 'Select your events',

  'include_payload_display' => 'Include Event Data',
  'include_payload_instructions' => 'Include event data in POST request.',

  'debounced' => 'Debounced',
  'debounced_instructions' => 'Debounce the webhook to only call it once in a specific timeframe.',

  'debounce_in_seconds' => 'Timeframe in seconds',
  'debounce_in_seconds_instructions' => '',

  'headers_display' => 'Request Headers',
  'header_set_display' => 'Header',
  'header_set_key' => 'Key',
  'header_set_value' => 'Value',

  'request_body_display' => 'Request Body',
  'request_body_instructions' => 'Provide a custom request body in JSON format. If a value is set, the body of the request is overriden.'
];
