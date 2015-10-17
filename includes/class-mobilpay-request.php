<?php
// Mobilpay Payment Request Class
/**
 * @copyright NETOPIA https://github.com/mobilPay
 * @author Claudiu Tudose
 * @version 1.0
 */
class Mobilpay_Payment_Request {

	const PAYMENT_TYPE_SMS  = 0x01;
	const PAYMENT_TYPE_CARD	= 0x02;

	public $m_signature   = null;
	public $m_service     = null;
	public $m_type	      = self::PAYMENT_TYPE_SMS;
	public $m_details     = null;
	public $m_price	      = null;
	public $m_currency    = null;
	public $m_tran_id     = null;
	public $m_timestamp   = null;
	public $m_return_url  = null;
	public $m_confirm_url = null;
	public $m_first_name  = null;
	public $m_last_name   = null;
	public $m_msisdn      = null;
	public $m_params      = array();
	
	function Mobilpay_Payment_Request() {
	}
	
	public function builParametersList() {
		if ( is_null($this->m_signature) || is_null($this->m_tran_id) || is_null($this->m_timestamp) )
			return null;
		$params['signature'] = urlencode($this->m_signature);
		if ( $this->m_service != null ) {
			$params['service'] = urlencode($this->m_service);
		}
		$params['tran_id'] = urlencode($this->m_tran_id);
		$params['timestamp'] = urlencode($this->m_timestamp);
		if ( $this->m_type == null ) {
			$this->m_type = self::PAYMENT_TYPE_SMS;
		}
		$params['type'] = urlencode($this->m_type);
		if ( $this->m_details != null ) {
			$params['details'] = urlencode($this->m_details);
		}
		if ( $this->m_price != null ) {
			$params['price'] = urlencode(sprintf('%.02f', $this->m_price));
		}
		if ( $this->m_currency != null ) {
			$params['currency']	= urlencode($this->m_currency);
		}
		if ( !is_null($this->m_return_url) )
			$params['return_url'] = urlencode($this->m_return_url);
		if ( !is_null($this->m_confirm_url) )
			$params['confirm_url'] = urlencode($this->m_confirm_url);
		if ( !is_null($this->m_first_name) )
			$params['first_name'] = urlencode($this->m_first_name);
		if ( !is_null($this->m_last_name) )
			$params['last_name'] = urlencode($this->m_last_name);
		if ( !is_null($this->m_msisdn) )
			$params['msisdn'] = urlencode($this->m_msisdn);
		if ( is_array($this->m_params) ) {
			foreach ( $this->m_params as $key=>$value ) {
				if ( isset($params[$key]) )
					continue;
				$params[$key] = urlencode($value);
			}
		}
		$params['crc'] = Mobilpay_Global::buildCRC($params);
		return $params;
	}
	
	static function buildQueryString( $params ) {
		$crc_pairs = array();
		foreach ($params as $key=>$value)
			$crc_pairs[] = "{$key}={$value}";
		return implode('&', $crc_pairs);
	}

	public function buildAccessParameters( $public_key, &$env_key, &$enc_data ) {
		$params = $this->builParametersList();
		if ( is_null($params) )
			return false;
		$src_data = Mobilpay_Payment_Request::buildQueryString($params);
		$enc_data = '';
		$env_keys = array();
		$result = openssl_seal($src_data, $enc_data, $env_keys, array($public_key));
		if ( $result === false ) {
			$env_key = null;
			$enc_data = null;
			return false;
		}
		$env_key = base64_encode($env_keys[0]);
		$enc_data = base64_encode($enc_data);
		return true;
	}

}
