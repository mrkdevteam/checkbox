<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'MRKV_CHECKBOX_SHIFT_STATUS' ) ) {
    class MRKV_CHECKBOX_SHIFT_STATUS {
        
        private $api;

        public function __construct( $api ) {
            $this->api = $api;
        }

        /**
         * Checks the status of a shift and returns a structured response.
         * @return array Array with 'status' (bool) and optional 'message' (string).
         */
        public function check_shift_status() {
            $request_result = $this->api->mrkv_checkbox_make_request( 'GET', '/api/v1/cashier/shift', array() );
            return array(
                'status'      => strtolower( $request_result['status'] ?? 'closed' ),
                'message'     => $request_result['message'] ?? __( 'Unknown error getting shift status', 'checkbox' ),
                'status_code' => $request_result['status_code'] ?? ''
            );
        }
    }
}