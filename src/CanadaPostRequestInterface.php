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

}
