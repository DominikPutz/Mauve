<?php

/**
 * Super-simple, minimum abstraction MailChimp API v3 wrapper
 * MailChimp API v3: http://developer.mailchimp.com
 * This wrapper: https://github.com/drewm/mailchimp-api
 *
 * @author Drew McLellan <drew.mclellan@gmail.com>
 * @version 2.2
 */
class UM_MailChimp_V3
{
	private $api_key;
	public $api_endpoint = 'https://<dc>.api.mailchimp.com/3.0';

	/*  SSL Verification
		Read before disabling:
		http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/
	*/
	public $verify_ssl = false;

	/**
	 * Create a new instance
	 * @param string $api_key Your MailChimp API key
	 * @throws \Exception
	 */
	public function __construct($api_key)
	{
		$this->api_key = $api_key;

		if (strpos($this->api_key, '-') === false) {
			throw new \Exception("Invalid MailChimp API key `{$api_key}` supplied.");
		}

		list(, $data_center) = explode('-', $this->api_key);
		$this->api_endpoint  = str_replace('<dc>', $data_center, $this->api_endpoint);
	}

	/**
	 * Create a new instance of a Batch request. Optionally with the ID of an existing batch.
	 * @param string $batch_id Optional ID of an existing batch, if you need to check its status for example.
	 * @return UM_MailChimp_Batch New Batch object.
	 */
	public function new_batch($batch_id = null)
	{
		return new UM_MailChimp_Batch($this, $batch_id);
	}

	/**
	 * Convert an email address into a 'subscriber hash' for identifying the subscriber in a method URL
	 * @param   string $email The subscriber's email address
	 * @return  string          Hashed version of the input
	 */
	public function subscriberHash($email)
	{
		return md5(strtolower($email));
	}

	/**
	 * Make an HTTP DELETE request - for deleting data
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (if any)
	 * @param   int $timeout Timeout limit for request in seconds
	 * @return  array|false   Assoc array of API response, decoded from JSON
	 */
	public function delete($method, $args = array(), $timeout = 10)
	{
		return $this->makeRequest('delete', $method, $args, $timeout);
	}

	/**
	 * Make an HTTP GET request - for retrieving data
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (usually your data)
	 * @param   int $timeout Timeout limit for request in seconds
	 * @return  array|false   Assoc array of API response, decoded from JSON
	 */
	public function get($method = '', $args = array(), $timeout = 10)
	{
		return $this->makeRequest('get', $method, $args, $timeout);
	}

	/**
	 * Make an HTTP PATCH request - for performing partial updates
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (usually your data)
	 * @param   int $timeout Timeout limit for request in seconds
	 * @return  array|false   Assoc array of API response, decoded from JSON
	 */
	public function patch($method, $args = array(), $timeout = 10)
	{
		return $this->makeRequest('patch', $method, $args, $timeout);
	}

	/**
	 * Make an HTTP POST request - for creating and updating items
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (usually your data)
	 * @param   int $timeout Timeout limit for request in seconds
	 * @return  array|false   Assoc array of API response, decoded from JSON
	 */
	public function post($method, $args = array(), $timeout = 10)
	{
		return $this->makeRequest('post', $method, $args, $timeout);
	}

	/**
	 * Make an HTTP PUT request - for creating new items
	 * @param   string $method URL of the API request method
	 * @param   array $args Assoc array of arguments (usually your data)
	 * @param   int $timeout Timeout limit for request in seconds
	 * @return  array|false   Assoc array of API response, decoded from JSON
	 */
	public function put($method, $args = array(), $timeout = 10)
	{
		return $this->makeRequest('put', $method, $args, $timeout);
	}

	/**
	 * Performs the underlying HTTP request. Not very exciting.
	 * @param  string $http_verb The HTTP verb to use: get, post, put, patch, delete
	 * @param  string $method The API method to be called
	 * @param  array $args Assoc array of parameters to be passed
	 * @param int $timeout
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	private function makeRequest($http_verb, $method = '', $args = array(), $timeout = 10)
	{
		$url = $this->api_endpoint . '/' . $method;

		$this->last_request = array(
			'method'  => $http_verb,
			'path'    => $method,
			'url'     => $url,
			'body'    => '',
			'timeout' => $timeout,
		);

		$host = apply_filters("um_mailchimp_api_request_host", false );
		if ( $host && !defined('WP_PROXY_HOST') ) {
			$array = explode( ':', $host );
			define('WP_PROXY_HOST', $array[0]);
			if( !empty( $array[1] ) && !defined('WP_PROXY_PORT') ) {
				define('WP_PROXY_PORT', $array[1]);
			}
		}

		if( !defined('WP_PROXY_PORT') && $port = apply_filters("um_mailchimp_api_request_port", false ) ) {
			define( 'WP_PROXY_PORT', $port );
		}

		if( !defined('WP_PROXY_USERNAME') && $username = apply_filters("um_mailchimp_api_request_username", false ) ) {
			define( 'WP_PROXY_USERNAME', $username );
		}

		if( !defined('WP_PROXY_PASSWORD') && $password = apply_filters("um_mailchimp_api_request_password", false ) ) {
			define( 'WP_PROXY_PASSWORD', $password );
		}

		$response = wp_remote_request( $url, array(
			'method' => strtoupper( $http_verb ),
			'headers' => array(
				'Accept' => 'application/vnd.api+json',
		'Content-Type' => 'application/vnd.api+json',
				'Authorization' => 'apikey ' . $this->api_key
			),
			'user-agent' => 'UltimateMember/MailChimp-API/3.0',
			'timeout' => $timeout,
			'sslverify' => apply_filters( 'um_mailchimp_api_request_sslverify', true ),
			'httpversion' => '1.1',
			'body' => strtoupper( $http_verb ) == 'GET' ? $args : json_encode( $args )
		) );

		$body = wp_remote_retrieve_body($response);
		if( !( $formattedResponse = json_decode( $body, true ) ) ) {
			$formattedResponse = '';
		}

		if( UM()->options()->get( 'mailchimp_enable_log' ) ) {
			ob_start();
			debug_print_backtrace();
			$trace = ob_get_clean();
			UM()->Mailchimp()->log()->add( array(
				'method'   => $http_verb,
				'url'      => $url,
				'status'   => !is_wp_error( $response ) && !( isset( $formattedResponse[ 'status' ] ) && is_numeric( $formattedResponse[ 'status' ] ) ),
				'args'     => $args,
				'response' => $formattedResponse,
				'trace'    => $trace
			) );
		}

		return $formattedResponse;
	}
}