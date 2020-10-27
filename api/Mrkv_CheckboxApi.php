<?php

if ( !class_exists( 'Mrkv_CheckboxApi' ) ) {
    class Mrkv_CheckboxApi
    {
        private $login;

        private $password;

        private $cashbox_key;

        private $is_dev;

        private $access_token = '';

        public function __construct($login,$password,$cashbox_key,$is_dev = false)
        {
            $this->login = $login;
            $this->password = $password;
            $this->cashbox_key = $cashbox_key;
            $this->is_dev = $is_dev;
            $this->getBearToken();
        }

        public function getBearToken()
        {
            $params = ['login'=>$this->login,'password'=>$this->password];
            $response = $this->makePostRequest('/api/v1/cashier/signin',$params);
            $this->access_token = $response['access_token'] ?? '';
        }

        public function connect()
        {
            $cashbox_key = $this->cashbox_key;
            $header_params = ['cashbox_key'=>$cashbox_key];
            $response =  $this->makePostRequest('/api/v1/shifts',[],$header_params);
            return $response;
        }

        public function disconnect()
        {
            $response = $this->makePostRequest('/api/v1/shifts/close');
            return $response;
        }

        public function getShifts()
        {
            $url = '/api/v1/shifts';
            $response = $this->makeGetRequest($url);
            ppre($response);
        }

        public function getCurrentCashierShift()
        {
            $url = '/api/v1/cashier/shift';
            $response = $this->makeGetRequest($url);
            return $response;
        }

        public function getCurrentCashboxInfo()
        {
            $url = '/api/v1/cash-registers/info';
            $header_params = ['cashbox_key'=>$this->cashbox_key];
            $response = $this->makeGetRequest($url,[],$header_params);
            ppre($response);
        }

        public function checkConnection($shift_id)
        {
            $url = '/api/v1/shifts/'.$shift_id;
            $response = $this->makeGetRequest($url);
            return $response;
        }

        public function create_receipt($params)
        {
            $response = $this->makePostRequest('/api/v1/receipts/sell',$params);
            return $response;
        }

        private function makePostRequest($route,$params = [],$header_params = [])
        {
            $url_host = $this->is_dev ? 'https://dev-api.checkbox.in.ua' : 'https://api.checkbox.in.ua';
            $url = $url_host.$route;
            // $curl=curl_init();

            $header = [];

            if ($this->access_token) {
                $authorization = "Authorization: Bearer ".$this->access_token; // Prepare the authorisation token
                $header[] = 'Content-Type: application/json';
                $header[] = $authorization;
            }
            if (isset($header_params['cashbox_key'])) {
                $header[] = 'X-License-Key:'.$header_params['cashbox_key'];
            }

            // curl_setopt($curl, CURLOPT_HTTPHEADER, $header );
            // curl_setopt($curl,CURLOPT_URL, $url);
            // curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
            // curl_setopt($curl,CURLOPT_POST,true);
            if (!empty($params)) {
                // curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($params));
                $fields = $params;
            }
            // curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            // $responce = curl_exec($curl);
            // $headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT );
            // curl_close($curl);

            $responce = wp_remote_post($url_host, array(
                'method' => 'POST',
                'headers' => $header,
                'timeout'     => 60,
                'redirection' => 5,
                'blocking'    => true,
                'httpversion' => '1.0',
                'sslverify' => false,
                'body' => json_encode($fields))
            );
            return $responce;
        }

        private function makeGetRequest($route,$params = [],$header_params = [])
        {
            $url_host = $this->is_dev ? 'https://dev-api.checkbox.in.ua' : 'https://api.checkbox.in.ua';
            $url = $url_host.$route;
            $curl=curl_init();
            $header = [];

            if ($this->access_token) {
                $authorization = "Authorization: Bearer ".$this->access_token; // Prepare the authorisation token
                $header[] = 'Content-Type: application/json';
                $header[] = $authorization;
            }
            if (isset($header_params['cashbox_key'])) {
                $header[] = 'X-License-Key:'.$header_params['cashbox_key'];
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, $header );
            curl_setopt($curl,CURLOPT_URL, $url);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
            if (!empty($params)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
            }
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            $responce = curl_exec($curl);
            $headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT );
            curl_close($curl);
            return json_decode($responce,true);
        }

    }
}
