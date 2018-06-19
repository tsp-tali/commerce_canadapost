<?php

namespace Drupal\commerce_canadapost\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;
use CanadaPost\Exception\ClientException;
use CanadaPost\Tracking;

/**
 * Drush commands for the Commerce Canada Post module.
 */
class Commands extends DrushCommands {

  /**
   * The Canada Post module configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The logger service for the Commerce Canada Post module.
   *
   * Drush commands objects already have a `logger` property that is not the
   * logger we want.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $moduleLogger;

  /**
   * Constructs a new Commands object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->config = $config_factory->get('commerce_canadapost.settings');
    $this->moduleLogger = $logger_factory->get(COMMERCE_CANADAPOST_LOGGER_CHANNEL);
  }

  /**
   * Fetch the tracking number for the given tracking PIN.
   *
   * @command commerce-canadapost-tracking-number
   *
   * @param $tracking-pin
   *   The tracking PIN for which to fetch the tracking number.
   *
   * @usage commerce-cp-tn 1234567
   *   Fetch the tracking number for the 1234567 tracking PIN.
   *
   * @aliases commerce-cp-tn
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
      $this->moduleLogger->error($message);
      return;
    }

    // Dump the response for now; we need to find a way to test with a real
    // shipment that does not return simply 'No Pin History'.
    $this->output->writeln(var_export($tracking_summary, TRUE));
  }

}
