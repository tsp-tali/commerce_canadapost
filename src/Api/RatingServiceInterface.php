<?php

namespace Drupal\commerce_canadapost\Api;

use Drupal\commerce_canadapost\Plugin\Commerce\ShippingMethod\CanadaPost;
use Drupal\commerce_shipping\Entity\ShipmentInterface;

/**
 * Defines the interface for the Rating API integration service.
 */
interface RatingServiceInterface {

  /**
   * Get rates from the Canada Post API.
   *
   * @param CanadaPost $canadaPost
   *   The CanadaPost shipping plugin.
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The shipment.
   * @param array $options
   *   The options array.
   *
   * @return \Drupal\commerce_shipping\ShippingRate[]
   *   The rates returned by Canada Post.
   */
  public function getRates(CanadaPost $canadaPost, ShipmentInterface $shipment, array $options);

}
