<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exists to prevent redeclaration
if ( ! class_exists( 'MRKV_CHECKBOX_AUTHORIZATION' ) ) 
{
    /**
     * Class for handling Checkbox API cashier authorization and token retrieval.
     */
    class MRKV_CHECKBOX_AUTHORIZATION 
    {
        /** 
         * @var string Cashier username/login 
         */
        private $cashier_login;

        /** 
         * @var string Cashier password 
         */
        private $cashier_password;

        /** 
         * @var object API controller instance 
         */
        private $api;

        /**
         * Constructor for the authorization controller.
         * * @param string $cashbox_slug      Unique identifier for the cashbox.
         * @param string $cashier_login     Login credential for the cashier.
         * @param string $cashier_password  Password credential for the cashier.
         * @param object $api               The API class instance used to perform requests.
         */
        public function __construct( $cashbox_slug, $cashier_login, $cashier_password, $api ) {
            $this->cashier_login    = $cashier_login;
            $this->cashier_password = $cashier_password;
            $this->api              = $api;
        }

        /**
         * Authenticates the cashier and retrieves an access token from the API.
         * * @return string The access token on success, or an empty string on failure.
         */
        public function mrkv_checkbox_get_authorization_token() {
            $params = [
                'login'    => $this->cashier_login,
                'password' => $this->cashier_password,
            ];

            # Perform the sign-in request via the API controller
            $request_result = $this->api->mrkv_checkbox_make_request( 'POST', '/api/v1/cashier/signin', $params );

            # Return the token if it exists in the response, otherwise return an empty string
            return $request_result['access_token'] ?? '';
        }
    }
}