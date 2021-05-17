<?php

namespace Legrisch\StatamicWebhooks\EventListener;

use Legrisch\StatamicWebhooks\Settings\Settings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventListener
{
  public static $settings;

  private static function getSettings()
  {
    if (!isset(self::$settings)) {
      self::$settings = Settings::read();
    }
    return self::$settings;
  }

  private static function webhooks()
  {
    $enabledWebhooks = array_values(array_filter(self::getSettings()['webhooks'] ?? [], function ($webhook) {
      $enabled = $webhook['enabled'];
      return (bool) $enabled;
    }));
    return $enabledWebhooks;
  }

  public static function handle($event)
  {
    $eventClass = get_class($event);
    foreach (self::webhooks() as $webhook) {
      try {
        if ($webhook['events'] && in_array($eventClass, $webhook['events'])) {
          self::trigger($webhook, $event);
        }
      } catch (\Throwable $th) {
        Log::error('Unable to handle webhook: ' . $th->getMessage());
        throw new \Exception('Unable to handle webhook: ' . $th->getMessage(), 1);
      }
    }
  }

  public static function trigger($webhook, $event)
  {
    if (isset($webhook['headers']) && count($webhook['headers']) > 0) {
      $header = [];
      foreach ($webhook['header'] as $h) {
        if ($h && $h['enabled']) {
          $header[$h['key']] = $h['value'];
        }
      }

      Http::withHeaders($header)->post($webhook['url'], [
        'event' => str_replace('Statamic\\Events\\', '', get_class($event))
      ]);
    } else {
      Http::post($webhook['url'], [
        'event' => str_replace('Statamic\\Events\\', '', get_class($event))
      ]);
    }
  }
}
