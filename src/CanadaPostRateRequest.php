<?php

namespace Drupal\commerce_canadapost;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;

/**
 * Class CanadaPostRateRequest.
 *
 * @package Drupal\commerce_canadapost
 */
class CanadaPostRateRequest extends CanadaPostRequest {
  /**
   * @var \Drupal\commerce_shipping\Entity\ShipmentInterface
   */
  protected $commerce_shipment;

  /**
   * @var array
   */
  protected $configuration;

  /**
   * @var CanadaPostShipment
   */
  protected $canadapost_shipment;

  /**
   * Set the shipment for rate requests.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $commerce_shipment
   *   A Drupal Commerce shipment entity.
   */
  public function setShipment(ShipmentInterface $commerce_shipment) {
    $this->commerce_shipment = $commerce_shipment;
  }

  /**
   * Fetch rates from the CanadaPost API.
   */
  public function getRates() {
    // Validate a commerce shipment has been provided.
    $rates = [];
    for ($i = 0; $i < 5; $i++) {

      $price = new Price((string) rand(2, 50), 'USD');

      $shipping_service = new ShippingService(
        'code_' . $i,
        'Some Random Name_' . $i
      );
      $rates[] = new ShippingRate(
        'code_' . $i,
        $shipping_service,
        $price
      );
    }
    return $rates;
  }

  /**
   * Gets the rate type: whether we will use negotiated rates or standard rates.
   *
   * @return bool
   *   Returns true if negotiated rates should be requested.
   */
  public function getRateType() {
    return boolval($this->configuration['rate_options']['rate_type']);
  }

}
