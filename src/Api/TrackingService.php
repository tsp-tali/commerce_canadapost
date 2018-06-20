<?php

namespace Drupal\commerce_canadapost\Api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use CanadaPost\Tracking;

/**
 * Provides the default Tracking API integration services.
 */
class TrackingService implements TrackingServiceInterface {

  /**
   * The Canada Post configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;
  
  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a new TrackingService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->config = $config_factory->get('commerce_canadapost.settings');
    $this->logger = $logger_factory->get(COMMERCE_CANADAPOST_LOGGER_CHANNEL);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchTrackingNumber($tracking_pin) {
    $config = [
      'username' => $this->config->get('api.username'),
      'password' => $this->config->get('api.password'),
    ];

    try {
      $tracking = new Tracking($config);
      $tracking_summary = $tracking->getSummary($tracking_pin);
    }
    catch (ClientException $exception) {
      $message = sprintf(
        'An error has been returned by the Canada Post when fetching the tracking summary for the tracking PIN "%s". The error was: "%s"',
        $tracking_pin,
        json_encode($exception->getResponseBody())
      );
      $this->logger->error($message);
      return;
    }

    // return the full response for now; we need to find a way to test with a
    // real shipment that does not return simply 'No Pin History'.
    return $tracking_summary;
  }

}
