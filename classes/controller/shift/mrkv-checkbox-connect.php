<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'MRKV_CHECKBOX_CONNECT' ) ) {
    class MRKV_CHECKBOX_CONNECT {
        
        private $api;

        public function __construct( $api ) {
            $this->api = $api;
        }

        /**
         * Opens a shift and returns a structured response.
         * @return array Array with 'status' (bool) and optional 'message' (string).
         */
        public function connect() {
            $request_result = $this->api->mrkv_checkbox_make_request( 'POST', '/api/v1/shifts', array() );
            
            // Check if 'id' exists in response (success)
            if ( ! empty( $request_result['id'] ) || (! empty( $request_result['status_code'] ) && $request_result['status_code'] == 400)) {
                return array( 'status' => true );
            }

            // Return false with error message from API or default message
            return array( 
                'status'  => false, 
                'message' => $request_result['message'] ?? __( 'Unknown error opening shift', 'checkbox' ),
                'status_code' => $request_result['status_code'] ?? ''
            );
        }
    }
}