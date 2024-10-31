<?php

use Mocean\Client;
use Mocean\Client\Credentials\Basic;


class oelm_Mocean{

	protected static $_instance = null;
	static private $account_sid, $auth_token, $senders_number;
	public static $settings;
	
	public function __construct(){
		self::$settings = get_option( 'oelm-services-options', true );
		$this->set_credentials();
	}

	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	private function set_credentials(){	
		$this->account_sid = self::$settings['mocean-account-sid'];
		$this->auth_token = self::$settings['mocean-auth-token'];
		$this->senders_number = self::$settings['mocean-sender-number'];	
	}

	public function sendSMS( $phone, $message ){

		$mocean = new Client(new Basic(
			$this->account_sid,
			$this->auth_token
		));
		

		try {
		    $mocean->message()->send([
				'mocean-to' => $phone,
				'mocean-from' => $this->senders_number,
				'mocean-text' => $message,
				'mocean-resp-format' => 'json'
			]);
			
			
			
		} catch (Exception $e) {
		    // output error message if fails
		    return new WP_Error( 'operator-error', $e->getMessage() );
		}

	}

}

function oelm_mocean(){
	return oelm_Mocean::get_instance();
}

?>
