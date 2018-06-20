<?php

namespace Drupal\commerce_canadapost\Commands;

use Drupal\commerce_canadapost\Api\TrackingServiceInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for the Commerce Canada Post module.
 */
class Commands extends DrushCommands {

  /**
   * The Tracking API service.
   *
   * @var \Drupal\commerce_canadapost\Api\TrackingServiceInterface
   */
  protected $trackingApi;

  /**
   * Constructs a new Commands object.
   *
   * @param \Drupal\commerce_canadapost\Api\TrackingServiceInterface
   *   The Tracking API service.
   */
  public function __construct(TrackingServiceInterface $tracking_api) {
    $this->trackingApi = $tracking_api;
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
    $tracking_summary = $this->trackingApi->fetchTrackingNumber($tracking_pin);

    $this->output->writeln(var_export($tracking_summary, TRUE));
  }

}
