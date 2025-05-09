<?php

namespace App\Http\Controllers\__Compute;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class MetaController extends Controller{

    public function whatsappMessage(){
        $url = 'https://graph.facebook.com/v17.0/133880856478926/messages';
        $auth_token = 'EAAEWftZB8KMcBOwOkumWoolkJCO0QUxTg5ePZBY9QjliQQbR3o8SAiEh0dSIdBeVIloz3JhZCaLcGFZAuemYXrBGcqqiB5kgZBhehbGHmy6CnkyoMjJMmOSIdJzcGZB2KHNZC9EAQa3YrFNxSHyzS71xvfMPrV5h1bQuVfcIu2PVFpk9gouM3ZCWibzteOIacJC8UmDOQDICqlKmiRYfYanoc4JxZATe9RVVuc6EZD';

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => '345678',
            'type' => 'template',
            'template' => [
                'name' => 'hello_world',
                'language' => [
                    'code' => 'en_US'
                ]
            ]
        ];

        $client = new Client();
        $response = $client->post($url,[
            'headers' => [ 'Authorization' => 'Bearer ' . $auth_token, "Content-Type: application/json" ],
            'json' => $data
        ]);

        dd($response->getBody()->getContents());
    }

    public static function formatNumber($number){
        $new_number = preg_replace('/[^0-9]/', '', $number ?? '');
        if(strlen($new_number) == 10){
            $new_number .= "1" . $new_number;
        }

        return strlen($new_number) == 11 ? $new_number : false;
    }

}