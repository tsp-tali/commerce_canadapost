<?php

namespace Drupal\commerce_canadapost\Api;

use Drupal\commerce_shipping\Entity\ShipmentInterface;

/**
 * Defines the interface for the Rating API integration service.
 */
interface RatingServiceInterface {

  /**
   * Get rates from the Canada Post API.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The shipment.
   * @param array $options
   *   The options array.
   *
   * @return \Drupal\commerce_shipping\ShippingRate[]
   *   The rates returned by Canada Post.
   */
  public function getRates(ShipmentInterface $shipment, array $options);

}
