@extends('statamic::layout')

@section('content')
    <div class="flex flex-col items-start mb-3">
        <h1 class="flex-1">Statamic Webhooks</h1>

        <p class="mt-1">
            Use Webhooks to trigger deployments or microservices.
        </p>
    </div>

    <div class="mt-4">
        <publish-form
            title="Settings"
            action="{{ cp_route('legrisch.statamic-webhooks.update') }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
        />
    </div>
@stop