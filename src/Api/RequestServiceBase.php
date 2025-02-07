<?php

namespace Drupal\commerce_canadapost\Api;

use Drupal\commerce_canadapost\Plugin\Commerce\ShippingMethod\CanadaPost;
use Drupal\commerce_canadapost\UtilitiesService;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodInterface;
use Drupal\commerce_store\Entity\StoreInterface;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;

use Exception;

/**
 * CanadaPost API Service.
 *
 * @package Drupal\commerce_canadapost
 */
abstract class RequestServiceBase implements RequestServiceInterface {

  /**
   * The commerce shipment entity.
   *
   * @var \Drupal\commerce_shipping\Entity\ShipmentInterface
   */
  protected $commerceShipment;

  /**
   * The shipping method being rated.
   *
   * @var \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodInterface
   */
  protected $shippingMethod;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The utilities service class.
   *
   * @var \Drupal\commerce_canadapost\UtilitiesService
   */
  protected $utilities;

  /**
   * RequestServiceBase class constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\commerce_canadapost\UtilitiesService $utilities
   *   The utilities service class.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory,
    UtilitiesService $utilities
  ) {
    $this->logger = $logger_factory->get(COMMERCE_CANADAPOST_LOGGER_CHANNEL);
    $this->utilities = $utilities;
  }

  /**
   * Set the commerce shipment.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $commerce_shipment
   *   The commerce shipment entity.
   */
  public function setShipment(ShipmentInterface $commerce_shipment) {
    $this->commerceShipment = $commerce_shipment;
  }

  /**
   * Set the shipping method being rated.
   *
   * @param \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodInterface $shipping_method
   *   The shipping method.
   */
  public function setShippingMethod(ShippingMethodInterface $shipping_method) {
    $this->shippingMethod = $shipping_method;
  }

  /**
   * {@inheritdoc}
   */
  public function getApiSettings(
    StoreInterface $store = NULL,
    CanadaPost $shipping_method = NULL
  ) {
    return $this->utilities->getApiSettings($store, $shipping_method);
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestConfig(array $api_settings) {
    // Verify necessary configuration is available.
    if (empty($api_settings['username'])
      || empty($api_settings['password'])
      || empty($api_settings['customer_number'])) {
      throw new Exception('Configuration is required.');
    }

    $config = [
      'username' => $api_settings['username'],
      'password' => $api_settings['password'],
      'customer_number' => $api_settings['customer_number'],
      'contract_id' => $api_settings['contract_id'],
      'env' => $this->getEnvironmentMode($api_settings),
    ];

    return $config;
  }

  /**
   * Convert the environment mode to the correct format for the SDK.
   *
   * @param array $api_settings
   *   The Canada Post API settings.
   *
   * @return string
   *   The environment mode (prod/dev).
   */
  protected function getEnvironmentMode(array $api_settings) {
    return $api_settings['mode'] === 'live' ? 'prod' : 'dev';
  }

}
