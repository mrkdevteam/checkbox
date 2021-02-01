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
            $header_params = ['X-Client-Name'=>'Morkva'];
            $response = $this->makePostRequest('/api/v1/cashier/signin',$params, $header_params);
            $this->access_token = $response['access_token'] ?? '';
        }

        public function connect()
        {
            $cashbox_key = $this->cashbox_key;
            $header_params = ['cashbox_key'=>$cashbox_key,'X-Client-Name'=>'Morkva'];
            $response =  $this->makePostRequest('/api/v1/shifts', [], $header_params);
            return $response;
        }

        public function disconnect()
        {
            $header_params = ['X-Client-Name'=>'Morkva'];
            $response = $this->makePostRequest('/api/v1/shifts/close', [], $header_params);
            return $response;
        }

        public function getShifts()
        {
            $header_params = ['X-Client-Name'=>'Morkva'];
            $url = '/api/v1/shifts';
            $response = $this->makeGetRequest($url, [], $header_params);
        }

        public function getCurrentCashierShift()
        {
            $header_params = ['X-Client-Name'=>'Morkva'];
            $url = '/api/v1/cashier/shift';
            $response = $this->makeGetRequest($url,[], $header_params);
            return $response;
        }

        public function getCurrentCashboxInfo()
        {
            $url = '/api/v1/cash-registers/info';
            $header_params = ['X-Client-Name'=>'Morkva','cashbox_key'=>$this->cashbox_key];
            $response = $this->makeGetRequest($url,[],$header_params);
            return $response;
        }

        public function checkConnection($shift_id)
        {
            $header_params = ['X-Client-Name'=>'Morkva'];
            $url = '/api/v1/shifts/'.$shift_id;
            $response = $this->makeGetRequest($url, [], $header_params);
            return $response;
        }

        public function create_receipt($params)
        {
            $header_params = ['X-Client-Name'=>'Morkva'];
            $response = $this->makePostRequest('/api/v1/receipts/sell',$params, $header_params);
            return $response;
        }

        private function makePostRequest($route,$params = [],$header_params = [])
        {
            $url_host = $this->is_dev ? 'https://dev-api.checkbox.in.ua' : 'https://api.checkbox.in.ua';
            $url = $url_host.$route;

            $header = ['Content-type'=>'application/json'];

            if ($this->access_token) {
                $header = array_merge($header,['Authorization'=>'Bearer ' .trim($this->access_token)]);
            }

            if (isset($header_params['cashbox_key'])) {
                $header = array_merge($header,['X-License-Key'=>$header_params['cashbox_key']]);
            }

            if (isset($header_params['X-Client-Name'])) {
                $header = array_merge($header,['X-Client-Name'=>$header_params['X-Client-Name']]);
            }

            $responce = wp_remote_post($url, array(
                'method' => 'POST',
                'headers' => $header,
                'timeout'     => 60,
                'redirection' => 5,
                'blocking'    => true,
                'httpversion' => '1.0',
                'sslverify' => false,
                'body' => json_encode($params))
            );

            return isset($responce['body']) ? (array)json_decode($responce['body']):'';
        }

        private function makeGetRequest($route,$params = [],$header_params = [])
        {
            $url_host = $this->is_dev ? 'https://dev-api.checkbox.in.ua' : 'https://api.checkbox.in.ua';
            $url = $url_host.$route;

            $header = ['Content-type'=>'application/json'];
            if ($this->access_token) {
                $header = array_merge($header,['Authorization'=>'Bearer ' .trim($this->access_token)]);
            }

            if (isset($header_params['cashbox_key'])) {
                $header = array_merge($header,['X-License-Key'=>$header_params['cashbox_key']]);
            }

            if (isset($header_params['X-Client-Name'])) {
                $header = array_merge($header,['X-Client-Name'=>$header_params['X-Client-Name']]);
            }


            if ($params) {
                $params = http_build_query($params);
            } else {
                $params = '';
            }

            $responce = wp_remote_get($url, array(
                    'method' => 'GET',
                    'headers' => $header,
                    'timeout'     => 60,
                    'redirection' => 5,
                    'blocking'    => true,
                    'httpversion' => '1.0',
                    'sslverify' => false,
                    'body' => $params
            ));

            return isset($responce['body']) ? (array)json_decode($responce['body']):'';
        }

    }
}
