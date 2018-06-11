<?php

namespace Drupal\commerce_canadapost;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
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

    $response = $this->sendRequest();
    if (!empty($response['rates'])) {
      foreach ($response['rates'] as $rate) {
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

  /**
   * Send the request to Canada Post.
   *
   * @throws \Exception
   */
  public function sendRequest() {
    $xmlRequest = $this->createXml();
    $auth = $this->getAuth();

    // Set up curl request.
    $curl = curl_init('https://ct.soa-gw.canadapost.ca/rs/ship/price');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $auth['username'] . ':' . $auth['password']);
    curl_setopt(
      $curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/vnd.cpc.ship.rate-v3+xml',
        'Accept: application/vnd.cpc.ship.rate-v3+xml',
      )
    );
    $curl_response = curl_exec($curl);
    // Execute REST Request
    if (curl_errno($curl)) {
      echo 'Curl error: ' . curl_error($curl) . "\n";
    }

    curl_close($curl);

    $xml = simplexml_load_string('<root>' . preg_replace('/<\?xml.*\?>/', '', $curl_response) . '</root>');
    $response = [];
    if (!$xml) {
      \Drupal::logger('commerce_canadapost')
        ->error('Error Retrieving Canada Post response');
    }
    else {
      $response['rates'] = [];
      if ($xml->{'price-quotes'}) {
        $priceQuotes = $xml->{'price-quotes'}->children('http://www.canadapost.ca/ws/ship/rate-v3');
        if ($priceQuotes->{'price-quote'}) {
          foreach ($priceQuotes as $priceQuote) {
            $code = $priceQuote->{'service-code'}->__toString();
            $price = $priceQuote->{'price-details'}->{'due'}->__toString();
            $service_name = $priceQuote->{'service-name'}->__toString();
            $response['rates'][] = [
              'code' => $code,
              'price' => $price,
              'name' => $service_name,
            ];
          }
        }
      }
      if ($xml->{'messages'}) {
        $messages = $xml->{'messages'}->children('http://www.canadapost.ca/ws/messages');
        foreach ($messages as $message) {
          $error_code = $message->code->__toString();
          $error_message = $message->description->__toString();
          \Drupal::logger('commerce_canadapost')
            ->error($error_code . ': ' . $error_message);
        }
      }

    }

    return $response;

  }

  /**
   * Generate the XML to send to Canada Post.
   *
   * @return string
   *   The XML to send to Canada Post.
   * @throws \Exception
   */
  private function createXml() {
    $auth = $this->getAuth();
    $weight = 1;
    // todo dispatch event to change package weight
    $order_id = $this->commerce_shipment->order_id->target_id;
    $order = Order::load($order_id);
    $store = $order->getStore();
    $address = $store->getAddress();
    $originPostalCode = $address->getPostalCode();
    $postalCode = $this->commerce_shipment->getShippingProfile()->address->postal_code;

    $xmlRequest
      = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v3">
  <customer-number>{$auth['customer_number']}</customer-number>
  <parcel-characteristics>
    <weight>{$weight}</weight>
  </parcel-characteristics>
  <origin-postal-code>{$originPostalCode}</origin-postal-code>
  <destination>
    <domestic>
      <postal-code>{$postalCode}</postal-code>
    </domestic>
  </destination>
</mailing-scenario>
XML;
    return $xmlRequest;
  }

}
