<?php

namespace Drupal\commerce_canadapost;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Exception;

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
   * @var Shipment
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
   * Fetch rates from the Canada Post API.
   *
   * @throws \Exception
   */
  public function getRates() {
    // Validate a commerce shipment has been provided.
    if (empty($this->commerce_shipment)) {
      throw new Exception('Shipment not provided');
    }
    $rates = [];
    $auth = $this->getAuth();

    $request = new Rate(
      $auth['username'],
      $auth['password'],
      $auth['customer_number'],
      $this->commerce_shipment
    );

    $canadapost_rates = $request->getRates();
    if (!empty($canadapost_rates)) {
      foreach ($canadapost_rates as $rate) {
        $service_code = $rate['code'];
        $service_name = $rate['name'];
        $price = new Price((string) $rate['price'], 'CAD');

        $shipping_service = new ShippingService(
          $service_code,
          $service_name
        );
        $rates[] = new ShippingRate(
          $service_code,
          $shipping_service,
          $price
        );
      }
    }

    return $rates;
  }

}
