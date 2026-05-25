<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'MRKV_CHECKBOX_LOG' ) ) {

    /**
     * Class MRKV_CHECKBOX_LOG
     * Handles custom logging for debugging, requests, and errors.
     */
    class MRKV_CHECKBOX_LOG {

        /**
         * @var bool Enable general logging
         */
        private $active_log;

        /**
         * @var bool Enable request-specific logging
         */
        private $active_log_request;

        /**
         * Constructor
         */
        public function __construct() {
			$settings_data = (array) get_option( 'mrkv_checkbox', [] );
            $this->active_log         = $settings_data['debug']['log'] ?? false;
            $this->active_log_request = $settings_data['debug']['log'] ?? false;
        }

        /**
         * Log error data.
         * * @param mixed $error Data to log.
         */
        public function add_data_error( $error ) {
            if ( $this->active_log ) {
                $this->write_to_log( 'error', $error );
            }
        }

        /**
         * Log action messages.
         * * @param mixed $message Data to log.
         */
        public function add_data_message( $message ) {
            if ( $this->active_log ) {
                $this->write_to_log( 'action', $message );
            }
        }

        /**
         * Log API request/response data.
         * * @param mixed $request Data to log.
         */
        public function add_data_request( $request ) {
            if ( $this->active_log_request ) {
                $this->write_to_log( 'request', $request );
            }
        }

        /**
         * Internal helper to format and write the log entry.
         */
        private function write_to_log( $level, $data ) 
        {
            if ( ! function_exists( 'wc_get_logger' ) ) {
                return;
            }

            $logger = wc_get_logger();
            $context = [ 'source' => 'mrkv-checkbox' ];
            $timestamp = gmdate( "Y-m-d H:i:s" );

            if ( ! is_scalar( $data ) ) {
                $data = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
            }

            switch ( $level ) {
                case 'error':
                    $logger->error( $data, $context );
                    break;
                case 'request':
                    $logger->info( '[API Request/Response]: ' . $data, $context );
                    break;
                case 'action':
                default:
                    $logger->notice( '[Action Message]: ' . $data, $context );
                    break;
            }
            
            do_action( 'mrkv_checkbox_after_log_write', $level, $data, $timestamp );
        }
    }
}