<?php

namespace Drupal\commerce_canadapost;

use CanadaPost\Rating;
use Drupal\commerce_order\Entity\Order;

/**
 * Rate API Wrapper.
 */
class Rate extends CanadaPost {

  /**
   * @var \Drupal\commerce_shipping\Entity\Shipment
   */
  protected $shipment;

  /**
   * Constructor.
   *
   * @param string $username Canada Post username
   * @param string $password Canada Post password
   * @param string $customerNumber Canada Post customer number
   * @param \Drupal\commerce_shipping\Entity\Shipment $shipment
   */
  public function __construct(
    $username,
    $password,
    $customerNumber,
    $shipment
  ) {
    parent::__construct($username, $password, $customerNumber);
    $this->shipment = $shipment;
  }

  /**
   * Get rates from Canada Post.
   */
  public function getRates() {
    $order_id = $this->shipment->order_id->target_id;
    $order = Order::load($order_id);
    $store = $order->getStore();
    $address = $store->getAddress();
    $originPostalCode = $address->getPostalCode();
    $postalCode = $this->shipment->getShippingProfile()->address->postal_code;
    $postalCode = str_replace(' ', '', $postalCode);
    $request = new Rating([
      'username' => $this->getUsername(),
      'password' => $this->getPassword(),
      'customerNumber' => $this->getCustomerNumber(),
      'customerNumber' => $this->con,
    ]);

    $canadapost_rates = $request->getRates($originPostalCode, $postalCode, 1);

    return $canadapost_rates;

  }

}
