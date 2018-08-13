<?php

namespace Drupal\Tests\commerce_canadapost\Unit;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\AddressInterface;
use Drupal\commerce_canadapost\Api\RatingService;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\physical\Weight;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * Class CanadaPostRateRequestTest.
 *
 * @coversDefaultClass \Drupal\commerce_canadapost\Api\RatingService
 * @group commerce_canadapost
 */
class CanadaPostRateRequestTest extends UnitTestCase {

  /**
   * @var \Drupal\commerce_canadapost\Api\RatingServiceInterface
   */
  protected $ratingService;

  /**
   * @var \Drupal\commerce_shipping\Entity\ShipmentInterface
   */
  protected $shipment;

  /**
   * Set up requirements for test.
   */
  public function setUp() {
    parent::setUp();
    define('COMMERCE_CANADAPOST_LOGGER_CHANNEL', 'commerce_canadapost');

    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config = $this->prophesize(ImmutableConfig::class);
    $config_factory->get('commerce_canadapost.settings')
      ->willReturn($config->reveal());
    $config->get('api.rate.origin_postal_code')
      ->willReturn('');
    $config->get('api.mode')
      ->willReturn('');
    $config->get('api.username')
      ->willReturn('mock_name');
    $config->get('api.password')
      ->willReturn('mock_pwd');
    $config->get('api.customer_number')
      ->willReturn('mock_cn');
    $logger_factory = $this->prophesize(LoggerChannelFactoryInterface::class);
    $logger = $this->prophesize(LoggerChannelInterface::class);
    $logger_factory->get(COMMERCE_CANADAPOST_LOGGER_CHANNEL)
      ->willReturn($logger->reveal());

    $this->ratingService = new RatingService($config_factory->reveal(), $logger_factory->reveal());

  }

  /**
   * ::covers getRates.
   */
  public function testGetRates() {
    $shipment = $this->mockShipment();
    $mock_handler = new MockHandler([
      new Response(
        200,
        [],
        file_get_contents(__DIR__ . '/../Mocks/rating-response-success.xml')),
    ]);
    $rates = $this->ratingService->getRates($shipment, ['handler' => $mock_handler]);

    // Test the parsed response
    foreach ($rates as $rate) {
      /* @var \Drupal\commerce_shipping\ShippingRate $rate */
      $this->assertInstanceOf('Drupal\commerce_shipping\ShippingRate', $rate);
      $this->assertInstanceOf('Drupal\commerce_price\Price', $rate->getAmount());
      $this->assertGreaterThan(0, $rate->getAmount()->getNumber());
      $this->assertEquals($rate->getAmount()->getCurrencyCode(), 'CAD');
      $this->assertNotEmpty($rate->getService()->getLabel());
    }
    $this->assertTrue(is_array($rates));
  }

  /**
   * Creates a mock Drupal Commerce shipment entity.
   *
   * @return \Drupal\commerce_shipping\Entity\ShipmentInterface
   *   A mocked commerce shipment object.
   */
  private function mockShipment() {
    // Mock a Drupal Commerce Order and associated objects.
    $order = $this->prophesize(OrderInterface::class);
    $store = $this->prophesize(StoreInterface::class);

    // Mock the getAddress method to return a Canadian address.
    $store->getAddress()
      ->willReturn(new Address('CA', 'YK', 'Whitehorse', '', 'Y1A4P9', '', '9031 Quartz Road'));
    $order->getStore()->willReturn($store->reveal());

    // Mock a Drupal Commerce shipment and associated objects.
    $shipment = $this->prophesize(ShipmentInterface::class);
    $profile = $this->prophesize(ProfileInterface::class);
    $address_list = $this->prophesize(FieldItemListInterface::class);
    $address = $this->prophesize(AddressInterface::class);

    // Mock the address list to return a Canadian address.
    $address->getPostalCode()->willReturn('Y1A2C6');
    $address_list->first()->willReturn($address->reveal());
    $profile->get('address')->willReturn($address_list->reveal());
    $shipment->getShippingProfile()->willReturn($profile->reveal());
    $shipment->getOrder()->willReturn($order->reveal());

    // Mock the shipments weight.
    $shipment->getWeight()->willReturn(new Weight(1000, 'g'));

    // Return the mocked shipment object.
    return $shipment->reveal();
  }
}
