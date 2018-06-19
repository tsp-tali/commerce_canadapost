<?php

namespace Drupal\commerce_canadapost;

interface CanadaPostRequestInterface {

  /**
   * Set the request configuration.
   *
   * @param array $configuration
   *   A configuration array from a CommerceShippingMethod.
   */
  public function setConfig(array $configuration);

  /**
   * Returns authentication array for a request.
   *
   * @return array
   *   An array of authentication parameters.
   */
  public function getAuth();

}
