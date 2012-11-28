<?php

class Wdsi_Mailchimp {

	private $_raw_api_key;
	private $_parsed_key = array();

	public function __construct ($raw_api_key) {
		$this->_raw_api_key = $raw_api_key;
		$this->_parse_key();
	}


	public function get_raw_api_key () {
		return $this->_raw_api_key;
	}

	public function get_api_key () {
		return !empty($this->_parsed_key["key"])
			? $this->_parsed_key["key"]
			: false
		;
	}

	public function get_api_server () {
		return !empty($this->_parsed_key["server"])
			? $this->_parsed_key["server"]
			: false
		;
	}

	/**
	 * Remote list getting.
	 * @return array Array of lists from MailChimp associated with raw API key
	 */
	public function get_lists () {
		$lists = $this->_remote_request('lists');

		return !empty($lists['data'])
			? $lists['data']
			: array()
		;
	}

	/**
	 * Subscribe $email to $list
	 * @param  string $list  MailChimp list ID
	 * @param  string $email Valid email address
	 * @return bool True on success, false if something went wrong.
	 */
	public function subscribe_to ($list, $email) {
		if (!is_email($email)) return false;
		$result = $this->_remote_request('listSubscribe', array(
			"id" => $list,
			"email_address" => $email,
		));
		return $result;
	}

	/**
	 * Parses the raw MailChimp API key into key/server info.
	 * @return bool True on success, false on failure.
	 */
	private function _parse_key () {
		if (preg_match('/-/', $this->_raw_api_key)) list($key, $server) = explode('-', $this->_raw_api_key);
		else return false;
		if (!$key || !$server) return false;
		$this->_parsed_key = array(
			"server" => $server, 
			"key" => $key
		);
		return true;
	}

	/**
	 * MailChimp API call dispatcher
	 * @param  string $method MailChimp API method to call: http://apidocs.mailchimp.com/api/1.3/
	 * @param  array  $args Additional arguments hash (associative array)
	 * @return mixed Whatever MailChimp tells us in return, parsed into native PHP type. False on failure.
	 */
	private function _remote_request ($method, $args=array()) {
		$server = $this->get_api_server();
		$key = $this->get_api_key();
		if (!$server || !$key) return false;

		$parsed_args = $this->_parse_args($args);

		$resp = wp_remote_get("http://{$server}.api.mailchimp.com/1.3/?method={$method}&output=json&apikey={$key}{$parsed_args}");
		if(is_wp_error($resp)) return false; // Request fail
		if (wp_remote_retrieve_response_code($resp) != 200) return false; // Request fail
		$body = wp_remote_retrieve_body($resp);
		if (!$body) return false; // This should never happen

		return json_decode($body, true);
	}

	private function _parse_args ($args) {
		if (!$args || !is_array($args)) return false;
		$parsed = array();
		foreach ($args as $key => $value) {
			$parsed[] = urlencode($key) . '=' . urlencode($value);
		}
		if (empty($parsed)) return false;
		return '&' . join('&', $parsed);
	}
}