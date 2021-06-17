# Statamic Webhooks

This Statamic addon provides an easy to use interface to register webhooks for certain Statamic Events. Trigger deployments or microservices with ease.

![Statamic Webhooks](https://user-images.githubusercontent.com/46897060/118682352-a6a0d100-b800-11eb-99b5-9967abf11f77.png)

## Features

- Easy to use interface
- Custom headers per webhook
- Choose to include payload
- Enable/disable webhooks and headers
- Efficient concurrent execution of POST requests
- Debounced webhooks (when using an async queue driver)
- Ability to use a custom request body per webhook

## Installation

Run `composer require legrisch/statamic-webhooks`

In case you want to debounce the webhooks, make sure to setup an [async queue driver](https://laravel.com/docs/5.0/queues).

## Usage

After installation, visit the control panel to add webhooks: `Tools` → `Webhooks`.

To manually trigger a webhook call it like this:
`$success = EventListener::triggerByWebhookName('webhook-name')`
If called directly, debouncing is not considered.

---

## License

This project is licensed under the MIT License.
