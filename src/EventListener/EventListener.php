<?php

namespace Legrisch\StatamicWebhooks\EventListener;

use Legrisch\StatamicWebhooks\Settings\Settings;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
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
    Http::pool(function (Pool $pool) use ($eventClass, $event) {
      $requests = [];
      foreach (self::webhooks() as $webhook) {
        try {
          if ($webhook['events'] && in_array($eventClass, $webhook['events'])) {
            $request = self::trigger($webhook, $event, $pool);
            array_push($requests, $request);
          }
        } catch (\Throwable $th) {
          Log::error('Unable to handle webhook: ' . $th->getMessage());
          throw new \Exception('Unable to handle webhook: ' . $th->getMessage(), 1);
        }
      }
      return $requests;
    });
  }

  public static function trigger($webhook, $event, Pool $pool)
  {
    if (isset($webhook['headers']) && count($webhook['headers']) > 0) {
      $header = [];
      foreach ($webhook['header'] as $h) {
        if ($h && $h['enabled']) {
          $header[$h['key']] = $h['value'];
        }
      }

      return $pool->withHeaders($header)->post($webhook['url'], [
        'event' => str_replace('Statamic\\Events\\', '', get_class($event))
      ]);
    } else {
      return $pool->post($webhook['url'], [
        'event' => str_replace('Statamic\\Events\\', '', get_class($event))
      ]);
    }
  }
}
