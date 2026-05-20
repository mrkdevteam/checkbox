<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'MRKV_CHECKBOX_API' ) ) {

    /**
     * Class MRKV_CHECKBOX_API
     * Handles low-level communication with the Checkbox.ua REST API with integrated logging.
     */
    class MRKV_CHECKBOX_API {
        private const API_URL     = 'https://api.checkbox.in.ua';
        private const CLIENT_NAME = 'Morkva';

        private $is_dev;
        private $access_token;
        private $cashbox_key;
        private $logger;

        public function __construct( $cashbox_key, $access_token = '', $is_dev = false ) {
            $this->cashbox_key  = $cashbox_key;
            $this->access_token = $access_token;
            $this->is_dev       = $is_dev;

            // Initialize the logger
            if ( class_exists( 'MRKV_CHECKBOX_LOG' ) ) {
                $this->logger = new MRKV_CHECKBOX_LOG();
            }
        }

        /**
         * Perform a request to the Checkbox API.
         */
        public function mrkv_checkbox_make_request( string $method, string $route, array $params = [], string $type = '' ) {
            $base_url = self::API_URL;
            $url      = $base_url . $route;
            $method   = strtoupper( $method );

            // 1. Build Headers
            $headers = [
                'Content-Type'  => 'application/json',
                'X-Client-Name' => self::CLIENT_NAME,
                'Accept'        => 'application/json',
            ];

            if ( $this->access_token ) {
                $headers['Authorization'] = 'Bearer ' . trim( $this->access_token );
            }

            if ( $this->cashbox_key ) {
                $headers['X-License-Key'] = $this->cashbox_key;
            }

            // 2. Prepare Request Arguments
            $args = [
                'method'      => $method,
                'headers'     => $headers,
                'timeout'     => 60,
                'redirection' => 5,
                'blocking'    => true,
                'httpversion' => '1.1',
                'sslverify'   => apply_filters( 'mrkv_checkbox_api_sslverify', true ), 
            ];

            if ( $method === 'GET' && ! empty( $params ) ) {
                $url = add_query_arg( $params, $url );
            } elseif ( in_array( $method, [ 'POST', 'PUT', 'PATCH' ] ) ) {
                $args['body'] = json_encode( $params );
            }

            $args = apply_filters( 'mrkv_checkbox_api_request_args', $args, $route );

            // --- LOG REQUEST ---
            if ( $this->logger ) {
                $this->logger->add_data_request( [
                    'url'    => $url,
                    'method' => $method,
                    'args'   => $args
                ] );
            }

            // 3. Execute Request
            $response = wp_remote_request( $url, $args );

            // 4. Transport Error Handling (WP_Error)
            if ( is_wp_error( $response ) ) {
                $error_data = [ 
                    'error'  => $response->get_error_message(), 
                    'status' => 'transport_error' 
                ];

                if ( $this->logger ) {
                    $this->logger->add_data_error( $error_data );
                }

                do_action( 'mrkv_checkbox_api_error', $error_data, $url, $args );
                return $error_data;
            }

            $status_code = wp_remote_retrieve_response_code( $response );
            $body        = wp_remote_retrieve_body( $response );

            // 5. Raw Response Handling
            if ( $type === 'pdf' || $type === 'raw' ) {
                return $body;
            }

            $data = json_decode( $body, true );

            // 6. Process API-level Errors (4xx, 5xx)
            if ( $status_code >= 400 ) {
                $error_result = [
                    'error'       => $data['message'] ?? wp_remote_retrieve_response_message( $response ),
                    'status_code' => $status_code,
                    'details'     => $data['errors'] ?? ( $data['detail'] ?? [] ),
                    'raw_body'    => $body
                ];
                
                if ( $this->logger ) {
                    $this->logger->add_data_error( $error_result );
                }

                do_action( 'mrkv_checkbox_api_log_failure', $error_result, $url, $args );
                return $error_result;
            }

            // Success Hook & Log
            if ( $this->logger ) {
                $this->logger->add_data_message( "API Success: $method $route" );
            }

            do_action( 'mrkv_checkbox_api_success', $data, $route );

            return is_array( $data ) ? $data : [];
        }
    }
}