<?php

namespace Drupal\commerce_canadapost;

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
    $xmlRequest = $this->createXml();

    // Set up curl request.
    $curl = curl_init('https://ct.soa-gw.canadapost.ca/rs/ship/price');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
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
    $canadapost_rates = [];
    if (!$xml) {
      \Drupal::logger('commerce_canadapost')
        ->error('Error Retrieving Canada Post response');
    }
    else {
      if ($xml->{'price-quotes'}) {
        $priceQuotes = $xml->{'price-quotes'}->children('http://www.canadapost.ca/ws/ship/rate-v3');
        if ($priceQuotes->{'price-quote'}) {
          foreach ($priceQuotes as $priceQuote) {
            $code = $priceQuote->{'service-code'}->__toString();
            $price = $priceQuote->{'price-details'}->{'due'}->__toString();
            $service_name = $priceQuote->{'service-name'}->__toString();
            $canadapost_rates[] = [
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

    return $canadapost_rates;

  }

  /**
   * Generate the XML to send to Canada Post.
   *
   * @return string
   *   The XML to send to Canada Post.
   * @throws \Exception
   */
  private function createXml() {
    // todo get actual weight.
    $weight = 1;
    // todo dispatch event to change package weight.
    $order_id = $this->shipment->order_id->target_id;
    $order = Order::load($order_id);
    $store = $order->getStore();
    $address = $store->getAddress();
    $originPostalCode = $address->getPostalCode();
    $postalCode = $this->shipment->getShippingProfile()->address->postal_code;
    $postalCode = str_replace(' ', '', $postalCode);

    $xmlRequest
      = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v3">
  <customer-number>{$this->customerNumber}</customer-number>
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
