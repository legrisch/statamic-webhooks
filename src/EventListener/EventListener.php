<?php

namespace Legrisch\StatamicWebhooks\EventListener;

use Legrisch\StatamicWebhooks\Settings\Settings;
use Illuminate\Support\Facades\Log;
use Mpbarlow\LaravelQueueDebouncer\Facade\Debouncer;

class MyJob
{
  use \Illuminate\Foundation\Bus\Dispatchable;

  public function handle()
  {
    Log::info('handled MyJob!');
  }
}

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
    try {
      $eventClass = get_class($event);
      $curls = [];
      foreach (self::webhooks() as $webhook) {

        if ($webhook['events'] && in_array($eventClass, $webhook['events'])) {
          $debounced = (bool) $webhook['debounced'] ?? false;
          $debounceInSeconds = (float) $webhook['debounce_in_seconds'] ?? 0;
          if ($debounced && $debounceInSeconds > 0) {
            Debouncer::debounce(function () use ($webhook, $event) {
              self::triggerDebounced($webhook, $event);
            }, now()->addSeconds($debounceInSeconds));
          } else {
            $curl = self::trigger($webhook, $event);
            array_push($curls, $curl);
          }
        }
      }

      if (count($curls) < 1) return;

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
    } catch (\Throwable $th) {
      Log::error('Unable to handle webhook: ' . $th->getMessage());
      throw new \Exception('Unable to handle webhook: ' . $th->getMessage(), 1);
    }
  }

  public static function setupCurl($webhook, $event)
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

  public static function triggerDebounced($webhook, $event)
  {
    try {
      $curl = self::setupCurl($webhook, $event);
      curl_exec($curl);
      curl_close($curl);
    } catch (\Throwable $th) {
      Log::error('Unable to handle webhook: ' . $th->getMessage());
      throw new \Exception('Unable to handle webhook: ' . $th->getMessage(), 1);
    }
  }

  public static function trigger($webhook, $event)
  {
    $curl = self::setupCurl($webhook, $event);
    return $curl;
  }
}
