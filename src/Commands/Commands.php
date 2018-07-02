<?php

namespace Drupal\commerce_canadapost\Commands;

use Drupal\commerce_canadapost\Api\RatingServiceInterface;
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
   * The Rating API service.
   *
   * @var \Drupal\commerce_canadapost\Api\RatingServiceInterface
   */
  protected $ratingApi;

  /**
   * Constructs a new Commands object.
   *
   * @param \Drupal\commerce_canadapost\Api\RatingServiceInterface $service_api
   *   The Rating API service.
   * @param \Drupal\commerce_canadapost\Api\TrackingServiceInterface $tracking_api
   *   The Tracking API service.
   */
  public function __construct(RatingServiceInterface $service_api, TrackingServiceInterface $tracking_api) {
    $this->trackingApi = $tracking_api;
    $this->ratingApi = $service_api;
  }

}
