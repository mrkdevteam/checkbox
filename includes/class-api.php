<?php
# Include file to namespace
namespace Checkbox;

/**
 * Class connect by api
 * */
class API
{
    /**
     * @var string Login
     * */
    private $login;

    /**
     * @var string Password
     * */
    private $password;

    /**
     * @var string Cashbox key
     * */
    private $cashbox_key;

    /**
     * @var boolean Is development
     * */
    private $is_dev;

    /**
     * @var string Access token
     * */
    private $access_token = '';

    /**
     * @var string Api signin url
     * */
    const API_SIGNIN_URL = '/api/v1/cashier/signin';

    /**
     * @var string Api shifts url
     * */
    const API_SHIFTS_URL = '/api/v1/shifts';

    /**
     * @var string Api shifts close url
     * */
    const API_SHIFTS_CLOSE_URL = '/api/v1/shifts/close';

    /**
     * @var string Api cashier shifts url
     * */
    const API_CASHIER_SHIFT = '/api/v1/cashier/shift';

    /**
     * @var string Api cash register url
     * */
    const API_CASH_REGISTER = '/api/v1/cash-registers/info';

    /**
     * @var string Api receipt sell url
     * */
    const API_RECEIPT_SELL = '/api/v1/receipts/sell';

    /**
     * @var string Api receipt get url
     * */
    const API_RECEIPT_GET = '/api/v1/receipts/';

    /**
     * @var string Api dev checkbox
     * */
    const API_DEV_CHECKBOX = 'https://dev-api.checkbox.in.ua';

    /**
     * @var string Api main checkbox
     * */
    const API_MAIN_CHECKBOX = 'https://api.checkbox.in.ua';

    /**
     * Class constructor
     *
     * @param string $login
     * @param string $password
     * @param string $cashbox_key
     * @param boolean $is_dev
     */
    public function __construct(string $login, string $password, string $cashbox_key, bool $is_dev)
    {
        # Set all variables
        $this->login       = $login;
        $this->password    = $password;
        $this->cashbox_key = $cashbox_key;
        $this->is_dev      = $is_dev;
        $this->getBearToken();
    }

    /**
     * Get Bearer Token
     *
     * @return void
     */
    public function getBearToken(): void
    {
        # Set params
        $params             = array(
            'login'    => $this->login,
            'password' => $this->password,
        );

        # Set header params
        $header_params      = array( 'X-Client-Name' => 'Morkva' );

        # Set response request
        $response           = $this->makePostRequest(self::API_SIGNIN_URL, $params, $header_params);

        if(isset($response['access_token']))
        {
             # Set access token
            $this->access_token = $response['access_token'];
        }
        else
        {
            $script_start_text = "[" . date('Y-m-d H:i:s') . "] [debug] " . print_r($response, 1) . "\r\n";
            # Write text to degug.log file
            file_put_contents( __DIR__ . '/../logs/checkbox.log', $script_start_text, FILE_APPEND );

            $this->access_token = '';
        }
    }

    /**
     * Open cashier shift
     *
     * @return array|null
     */
    public function connect(): ?array
    {
        # Set Cashbox key
        $cashbox_key  = $this->cashbox_key;

        # Set Header params
        $header_params = array(
            'cashbox_key'   => $cashbox_key,
            'X-Client-Name' => 'Morkva',
        );

        # Set response request
        $response      = $this->makePostRequest(self::API_SHIFTS_URL, [], $header_params);

        # Return response
        return $response;
    }

    /**
     * Close cashier shift
     *
     * @return array|null
     */
    public function disconnect(): ?array
    {
        # Set header params
        $header_params = array( 'X-Client-Name' => 'Morkva' );

        # Set response request
        $response      = $this->makePostRequest(self::API_SHIFTS_CLOSE_URL, [], $header_params);

        # Return response
        return $response;
    }

    /**
     * Get all cashier shifts
     *
     * @return array|null
     */
    public function getShifts(): ?array
    {
        # Set header params
        $header_params = array( 'X-Client-Name' => 'Morkva' );

        # Set shift url
        $url           = self::API_SHIFTS_URL;

        # Set response request
        $response      = $this->makeGetRequest($url, [], $header_params);

        # Return response
        return $response;
    }

    /**
     * Get current cashier shifts
     *
     * @return array|null
     */
    public function getCurrentCashierShift(): ?array
    {
        # Set header params
        $header_params = array( 'X-Client-Name' => 'Morkva' );

        # Set shift url
        $url           = self::API_CASHIER_SHIFT;

        # Set response request
        $response      = $this->makeGetRequest($url, [], $header_params);

        # Return response
        return $response;
    }

    /**
     * Get current cashbox info
     *
     * @return array|null
     */
    public function getCurrentCashboxInfo(): ?array
    {
        # Set url
        $url           = self::API_CASH_REGISTER;

        # Set header params
        $header_params = array(
            'X-Client-Name' => 'Morkva',
            'cashbox_key'   => $this->cashbox_key,
        );

        # Set response request
        $response      = $this->makeGetRequest($url, [], $header_params);

        # Return response
        return $response;
    }

    /**
     * Check a status of cashier shift by ID
     *
     * @param int $shift_id
     * @return array|null
     */
    public function checkConnection(int $shift_id): ?array
    {
        # Set header params
        $header_params = array( 'X-Client-Name' => 'Morkva' );

        # Set url
        $url           = self::API_SHIFTS_URL . '/' . $shift_id;

        # Set response request
        $response      = $this->makeGetRequest($url, [], $header_params);

        # Return response
        return $response;
    }

    /**
     * Create receipt
     *
     * @param array $params parameters required for receipt creation
     * @return array|null
     */
    public function createReceipt(array $params): ?array
    {
        # Set header params
        $header_params = array( 'X-Client-Name' => 'Morkva' );

        # Set response request
        $response       = $this->makePostRequest(self::API_RECEIPT_SELL, $params, $header_params);

        # Return response
        return $response;
    }

    /**
     * Get receipt
     *
     * @param array $params parameters required for receipt creation
     * @return array|null
     */
    public function getReceipt($uuid)
    {
        # Set header params
        $header_params = array( 'X-Client-Name' => 'Morkva' );

        # Set url
        $url           = self::API_RECEIPT_GET . $uuid;

        # Set response request
        $response      = $this->makeGetRequest($url, [], $header_params);

        # Return response
        return $response;
    }

    /**
     * Make POST request
     *
     * @param string $route
     * @param array $params
     * @param array $header_params
     * @return array|null If successful returns a response, if an error appeared while doing request returns error, 
     */
    private function makePostRequest(string $route, array $params = [], array $header_params = []): ?array
    {
        # Get url host
        $url_host = self::API_MAIN_CHECKBOX;
        # Get completed url
        $url      = $url_host . $route;

        # Create header
        $header = array( 'Content-type' => 'application/json' );

        # Check access token
        if ($this->access_token) 
        {
            # Update header
            $header = array_merge($header, array( 'Authorization' => 'Bearer ' . trim($this->access_token) ));
        }

        # Check cashbox key
        if (isset($header_params['cashbox_key'])) 
        {
            # Update header
            $header = array_merge($header, array( 'X-License-Key' => $header_params['cashbox_key'] ));
        }

        # Check client name
        if (isset($header_params['X-Client-Name'])) 
        {
            # Update header
            $header = array_merge($header, array( 'X-Client-Name' => $header_params['X-Client-Name'] ));
        }

        # Send query
        $response = wp_remote_post(
            $url,
            array(
                'method'      => 'POST',
                'headers'     => $header,
                'timeout'     => 60,
                'redirection' => 5,
                'blocking'    => true,
                'httpversion' => '1.0',
                'sslverify'   => false,
                'body'        => json_encode($params),
            )
        );

        # Check error
        if (is_wp_error($response)) 
        {
            # Return error
            return [
                'error' => [
                    'code' => $response->get_error_code(),
                    'message' => $response->get_error_message()
                ]
            ];
        }

        # Return response
        return isset($response['body']) ? (array) json_decode($response['body']) : null;
    }

    /**
     * Make GET request
     *
     * @param string $route
     * @param array $params
     * @param array $header_params
     * @return array|null
     */
    private function makeGetRequest(string $route, array $params = [], array $header_params = []): ?array
    {
        # Get url host
        $url_host = self::API_MAIN_CHECKBOX;
        # Get completed url
        $url      = $url_host . $route;

        # Create header
        $header = array( 'Content-type' => 'application/json' );

        # Check access token
        if ($this->access_token) 
        {
            # Update header
            $header = array_merge($header, array( 'Authorization' => 'Bearer ' . trim($this->access_token) ));
        }

        # Check cashbox key
        if (isset($header_params['cashbox_key'])) 
        {
            # Update header
            $header = array_merge($header, array( 'X-License-Key' => $header_params['cashbox_key'] ));
        }

        # Check client name
        if (isset($header_params['X-Client-Name'])) 
        {
            # Update header
            $header = array_merge($header, array( 'X-Client-Name' => $header_params['X-Client-Name'] ));
        }

        # Check params
        if ($params) 
        {
            # Create query fields
            $params = http_build_query($params);
        } 
        else 
        {
            # Empty params
            $params = '';
        }

        # Send query
        $response = wp_remote_get(
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

        # Check error
        if (is_wp_error($response)) 
        {
            # Return error
            return [
                'error' => [
                    'code' => $response->get_error_code(),
                    'message' => $response->get_error_message()
                ]
            ];
        }

        # Return response
        return isset($response['body']) ? (array) json_decode($response['body']) : null;
    }
}
