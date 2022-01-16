<?php

namespace Checkbox;

class API
{
    private $login;

    private $password;

    private $cashbox_key;

    private $is_dev;

    private $access_token = '';

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
        $params             = array(
            'login'    => $this->login,
            'password' => $this->password,
        );
        $header_params      = array( 'X-Client-Name' => 'Morkva' );
        $response           = $this->makePostRequest('/api/v1/cashier/signin', $params, $header_params);
        $this->access_token = $response['access_token'];
    }

    /**
     * Open cashier shift
     *
     * @return array|null
     */
    public function connect(): ?array
    {
        $cashbox_key  = $this->cashbox_key;
        $header_params = array(
            'cashbox_key'   => $cashbox_key,
            'X-Client-Name' => 'Morkva',
        );
        $response      = $this->makePostRequest('/api/v1/shifts', [], $header_params);
        return $response;
    }

    /**
     * Close cashier shift
     *
     * @return array|null
     */
    public function disconnect(): ?array
    {
        $header_params = array( 'X-Client-Name' => 'Morkva' );
        $response      = $this->makePostRequest('/api/v1/shifts/close', [], $header_params);
        return $response;
    }

    /**
     * Get all cashier shifts
     *
     * @return array|null
     */
    public function getShifts(): ?array
    {
        $header_params = array( 'X-Client-Name' => 'Morkva' );
        $url           = '/api/v1/shifts';
        $response      = $this->makeGetRequest($url, [], $header_params);
        return $response;
    }

    /**
     * Get current cashier shifts
     *
     * @return array|null
     */
    public function getCurrentCashierShift(): ?array
    {
        $header_params = array( 'X-Client-Name' => 'Morkva' );
        $url           = '/api/v1/cashier/shift';
        $response      = $this->makeGetRequest($url, [], $header_params);
        return $response;
    }

    /**
     * Get current cashbox info
     *
     * @return array|null
     */
    public function getCurrentCashboxInfo(): ?array
    {
        $url           = '/api/v1/cash-registers/info';
        $header_params = array(
            'X-Client-Name' => 'Morkva',
            'cashbox_key'   => $this->cashbox_key,
        );
        $response      = $this->makeGetRequest($url, [], $header_params);
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
        $header_params = array( 'X-Client-Name' => 'Morkva' );
        $url           = '/api/v1/shifts/' . $shift_id;
        $response      = $this->makeGetRequest($url, [], $header_params);
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
        $header_params = array( 'X-Client-Name' => 'Morkva' );
        $response       = $this->makePostRequest('/api/v1/receipts/sell', $params, $header_params);
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
        $url_host = $this->is_dev ? 'https://dev-api.checkbox.in.ua' : 'https://api.checkbox.in.ua';
        $url      = $url_host . $route;

        $header = array( 'Content-type' => 'application/json' );

        if ($this->access_token) {
            $header = array_merge($header, array( 'Authorization' => 'Bearer ' . trim($this->access_token) ));
        }

        if (isset($header_params['cashbox_key'])) {
            $header = array_merge($header, array( 'X-License-Key' => $header_params['cashbox_key'] ));
        }

        if (isset($header_params['X-Client-Name'])) {
            $header = array_merge($header, array( 'X-Client-Name' => $header_params['X-Client-Name'] ));
        }

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

        if (is_wp_error($response)) {
            return [
                'error' => [
                    'code' => $response->get_error_code(),
                    'message' => $response->get_error_message()
                ]
            ];
        }

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
        $url_host = $this->is_dev ? 'https://dev-api.checkbox.in.ua' : 'https://api.checkbox.in.ua';
        $url      = $url_host . $route;

        $header = array( 'Content-type' => 'application/json' );
        if ($this->access_token) {
            $header = array_merge($header, array( 'Authorization' => 'Bearer ' . trim($this->access_token) ));
        }

        if (isset($header_params['cashbox_key'])) {
            $header = array_merge($header, array( 'X-License-Key' => $header_params['cashbox_key'] ));
        }

        if (isset($header_params['X-Client-Name'])) {
            $header = array_merge($header, array( 'X-Client-Name' => $header_params['X-Client-Name'] ));
        }

        if ($params) {
            $params = http_build_query($params);
        } else {
            $params = '';
        }

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

        if (is_wp_error($response)) {
            return [
                'error' => [
                    'code' => $response->get_error_code(),
                    'message' => $response->get_error_message()
                ]
            ];
        }

        return isset($response['body']) ? (array) json_decode($response['body']) : null;
    }
}
