<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'MRKV_CHECKBOX_DISCONNECT' ) ) {
    class MRKV_CHECKBOX_DISCONNECT {
        
        private $api;

        public function __construct( $api ) {
            $this->api = $api;
        }

        /**
         * Closes a shift and returns a structured response.
         * @return array Array with 'status' (bool) and optional 'message' (string).
         */
        public function connect() {
            $request_result = $this->api->mrkv_checkbox_make_request( 'POST', '/api/v1/shifts/close', array() );
            
            if ( ! empty( $request_result['id'] ) || (! empty( $request_result['status_code'] ) && $request_result['status_code'] == 400)) {
                return array( 'status' => true );
            }

            return array( 
                'status'  => false, 
                'message' => $request_result['message'] ?? __( 'Unknown error closing shift', 'checkbox' ),
                'status_code' => $request_result['status_code'] ?? '' 
            );
        }
    }
}