<?php

if ( ! class_exists( 'Mrkv_CheckboxApi' ) ) {
	class Mrkv_CheckboxApi {

		private $login;

		private $password;

		private $cashbox_key;

		private $is_dev;

		private $access_token = '';

		public function __construct( $login, $password, $cashbox_key, $is_dev = true ) {
			$this->login       = $login;
			$this->password    = $password;
			$this->cashbox_key = $cashbox_key;
			$this->is_dev      = $is_dev;
			$this->getBearToken();
		}

		public function getBearToken() {
			$params             = array(
				'login'    => $this->login,
				'password' => $this->password,
			);
			$header_params      = array( 'X-Client-Name' => 'Morkva' );
			$response           = $this->makePostRequest( '/api/v1/cashier/signin', $params, $header_params );
			$this->access_token = $response['access_token'];
		}

		public function connect() {
			 $cashbox_key  = $this->cashbox_key;
			$header_params = array(
				'cashbox_key'   => $cashbox_key,
				'X-Client-Name' => 'Morkva',
			);
			$response      = $this->makePostRequest( '/api/v1/shifts', array(), $header_params );
			return $response;
		}

		public function disconnect() {
			$header_params = array( 'X-Client-Name' => 'Morkva' );
			$response      = $this->makePostRequest( '/api/v1/shifts/close', array(), $header_params );
			return $response;
		}

		public function getShifts() {
			$header_params = array( 'X-Client-Name' => 'Morkva' );
			$url           = '/api/v1/shifts';
			$response      = $this->makeGetRequest( $url, array(), $header_params );
		}

		public function getCurrentCashierShift() {
			$header_params = array( 'X-Client-Name' => 'Morkva' );
			$url           = '/api/v1/cashier/shift';
			$response      = $this->makeGetRequest( $url, array(), $header_params );
			return $response;
		}

		public function getCurrentCashboxInfo() {
			$url           = '/api/v1/cash-registers/info';
			$header_params = array(
				'X-Client-Name' => 'Morkva',
				'cashbox_key'   => $this->cashbox_key,
			);
			$response      = $this->makeGetRequest( $url, array(), $header_params );
			return $response;
		}

		public function checkConnection( $shift_id ) {
			$header_params = array( 'X-Client-Name' => 'Morkva' );
			$url           = '/api/v1/shifts/' . $shift_id;
			$response      = $this->makeGetRequest( $url, array(), $header_params );
			return $response;
		}

		public function create_receipt( $params ) {
			 $header_params = array( 'X-Client-Name' => 'Morkva' );
			$response       = $this->makePostRequest( '/api/v1/receipts/sell', $params, $header_params );
			return $response;
		}

		private function makePostRequest( $route, $params = array(), $header_params = array() ) {
			$url_host = $this->is_dev ? 'https://dev-api.checkbox.in.ua' : 'https://api.checkbox.in.ua';
			$url      = $url_host . $route;

			$header = array( 'Content-type' => 'application/json' );

			if ( $this->access_token ) {
				$header = array_merge( $header, array( 'Authorization' => 'Bearer ' . trim( $this->access_token ) ) );
			}

			if ( isset( $header_params['cashbox_key'] ) ) {
				$header = array_merge( $header, array( 'X-License-Key' => $header_params['cashbox_key'] ) );
			}

			if ( isset( $header_params['X-Client-Name'] ) ) {
				$header = array_merge( $header, array( 'X-Client-Name' => $header_params['X-Client-Name'] ) );
			}

			$responce = wp_remote_post(
				$url,
				array(
					'method'      => 'POST',
					'headers'     => $header,
					'timeout'     => 60,
					'redirection' => 5,
					'blocking'    => true,
					'httpversion' => '1.0',
					'sslverify'   => false,
					'body'        => json_encode( $params ),
				)
			);

			return isset( $responce['body'] ) ? (array) json_decode( $responce['body'] ) : '';
		}

		private function makeGetRequest( $route, $params = array(), $header_params = array() ) {
			$url_host = $this->is_dev ? 'https://dev-api.checkbox.in.ua' : 'https://api.checkbox.in.ua';
			$url      = $url_host . $route;

			$header = array( 'Content-type' => 'application/json' );
			if ( $this->access_token ) {
				$header = array_merge( $header, array( 'Authorization' => 'Bearer ' . trim( $this->access_token ) ) );
			}

			if ( isset( $header_params['cashbox_key'] ) ) {
				$header = array_merge( $header, array( 'X-License-Key' => $header_params['cashbox_key'] ) );
			}

			if ( isset( $header_params['X-Client-Name'] ) ) {
				$header = array_merge( $header, array( 'X-Client-Name' => $header_params['X-Client-Name'] ) );
			}

			if ( $params ) {
				$params = http_build_query( $params );
			} else {
				$params = '';
			}

			$responce = wp_remote_get(
				$url,
				array(
					'method'      => 'GET',
					'headers'     => $header,
					'timeout'     => 60,
					'redirection' => 5,
					'blocking'    => true,
					'httpversion' => '1.0',
					'sslverify'   => false,
					'body'        => $params,
				)
			);

			return isset( $responce['body'] ) ? (array) json_decode( $responce['body'] ) : '';
		}

	}
}
