<?php

namespace Drupal\commerce_canadapost;

abstract class CanadaPost {

  const BASE_URL = 'https://ct.soa-gw.canadapost.ca/rs/';

  /**
   * @var string
   */
  protected $username;

  /**
   * @var string
   */
  protected $password;

  /**
   * @var string
   */
  protected $customerNumber;

  /**
   * Constructor.
   *
   * @param string|null $username Canada Post username
   * @param string|null $password Canada Post password
   * @param string|null $customerNumber Canada Post customer number
   * @param LoggerInterface|null $logger PSR3 compatible logger (optional)
   */
  public function __construct(
    $username = NULL,
    $password = NULL,
    $customerNumber = NULL
  ) {
    $this->username = $username;
    $this->password = $password;
    $this->customerNumber = $customerNumber;
  }

  /**
   * @return string
   */
  public function getUsername() {
    return $this->username;
  }

  /**
   * @param string $username
   */
  public function setUsername($username) {
    $this->username = $username;
  }

  /**
   * @return string
   */
  public function getPassword() {
    return $this->password;
  }

  /**
   * @param string $password
   */
  public function setPassword($password) {
    $this->password = $password;
  }

  /**
   * @return string
   */
  public function getCustomerNumber() {
    return $this->customerNumber;
  }

  /**
   * @param string $customerNumber
   */
  public function setCustomerNumber($customerNumber) {
    $this->customerNumber = $customerNumber;
  }
}
