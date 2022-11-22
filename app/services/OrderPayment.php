<?php

namespace App\Services;

class OrderPayment
{

    public function makePayment($order)
    {
        $data = array(
            'order' => array(
                'amount' => (int)$order->amount,
                'currency' => 'BDT',
                'redirect_url' => 'http://www.yoursite.com/payment',
                'ipn_url' => 'http://www.yoursite.com/ipn',
                'validity' => 900,
            ),
            'product' => array(
                'name' => $order->product_name,
                'description' => $order->product_details
            ),
            'billing' => array(
                'customer' => array(
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'address' => array(
                        'street' => 'House 1, Road1, Gulshan 1',
                        'city' => 'Dhaka',
                        'state' => 'Dhaka',
                        'zipcode' => 1212,
                        'country' => 'BGD',
                    ),
                ),
            ),
        );
        $client = new \GuzzleHttp\Client();
        $appkey = 'db09e1518d5f4ebddc74db6877791573';
        $secretkey = '882320eeca83f9e79e61cb9b15b57b81';
        $token = base64_encode($appkey . ":" . md5($secretkey . time()));
        try {
            $guzzleResponse = $client->post('https://api-sandbox.portwallet.com/payment/v2/invoice', [
                'headers' => ['content-type'   => "application/json", 'Authorization' => 'Bearer ' . $token],
                'body' => json_encode($data),
            ]);
            if ($guzzleResponse->getStatusCode() == 201) {
                $payment_data=json_decode($guzzleResponse->getBody()->getContents(), true);
                // $order->invoice_id=$payment_data->data->invoice_id;
                // $order->invoice_url=$payment_data['data']['action']['url'];
                //$order->save();
                return $payment_data;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // you can catch here 400 response errors and 500 response errors
            // You can either use logs here use Illuminate\Support\Facades\Log;
            $error['error'] = $e->getMessage();
            $error['request'] = $e->getRequest();
            if ($e->hasResponse()) {
                if ($e->getResponse()->getStatusCode() == '400') {
                    $error['response'] = $e->getResponse();
                }
            }
            Log::error('Error occurred in get request.', ['error' => $error]);
        } catch (\Exception $e) {
            //other errors 
        }
    }
}