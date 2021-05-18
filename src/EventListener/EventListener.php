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
    $curls = [];
    foreach (self::webhooks() as $webhook) {
      try {
        if ($webhook['events'] && in_array($eventClass, $webhook['events'])) {
          $curl = self::trigger($webhook, $event);
          array_push($curls, $curl);
        }
      } catch (\Throwable $th) {
        Log::error('Unable to handle webhook: ' . $th->getMessage());
        throw new \Exception('Unable to handle webhook: ' . $th->getMessage(), 1);
      }
    }

    $multiCurl = curl_multi_init();
    foreach ($curls as $curl) {
      curl_multi_add_handle($multiCurl, $curl);
    }

    do {
      $status = curl_multi_exec($multiCurl, $active);
      if ($active) {
        curl_multi_select($multiCurl);
      }
    } while ($active && $status == CURLM_OK);

    foreach ($curls as $curl) {
      curl_multi_remove_handle($multiCurl, $curl);
    }
    curl_multi_close($multiCurl);
  }

  public static function trigger($webhook, $event)
  {
    $curl = curl_init($webhook['url']);
    $headers = array(
      'Content-Type:application/json',
    );

    if (isset($webhook['headers']) && count($webhook['headers']) > 0) {
      foreach ($webhook['headers'] as $header) {
        array_push($headers, "{$header['key']}: {$header['value']}");
      }
    }

    $includePayload = $webhook['include_payload'] ?? false;
    if ($includePayload) {
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
        'event' => str_replace('Statamic\\Events\\', '', get_class($event))
      ]));
    }

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    return $curl;
  }
}
