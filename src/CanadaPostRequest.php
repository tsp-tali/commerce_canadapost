<?php

namespace Drupal\commerce_canadapost;

/**
 * Canada Post API Service.
 *
 * @package Drupal\commerce_canadapost
 */
abstract class CanadaPostRequest implements CanadaPostRequestInterface {
  /**
   * The configuration array.
   */
  protected $configuration;

  /**
   * Sets configuration for requests.
   *
   * @param array $configuration
   *   A configuration array from a CommerceShippingMethod.
   */
  public function setConfig(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * Returns authentication array for a request.
   *
   * @return array
   *   An array of authentication parameters.
   *
   * @throws \Exception
   */
  public function getAuth() {
    // Verify necessary configuration is available.
    if (empty($this->configuration['api_information']['username'])
    || empty($this->configuration['api_information']['password'])
    || empty($this->configuration['api_information']['customer_number'])) {
      throw new \Exception('Configuration is required.');
    }

    return [
      'username' => $this->configuration['api_information']['username'],
      'password' => $this->configuration['api_information']['password'],
      'customer_number' => $this->configuration['api_information']['customer_number'],
    ];
  }

}
