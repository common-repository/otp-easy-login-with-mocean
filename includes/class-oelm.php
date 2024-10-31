<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class oelm{

	protected static $_instance = null;

	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	
	public function __construct(){
		$this->includes();
		$this->hooks();
	}

	/**
	 * File Includes
	*/
	public function includes(){

		$settings = get_option( 'oelm-phone-options', true );

		require_once oelm_PATH.'includes/class-oelm-exception.php';
		require_once oelm_PATH.'includes/oelm-functions.php';
		require_once oelm_PATH.'includes/class-oelm-geolocation.php';

		if($this->is_request('frontend')){

			$operators = oelm_operator_data();
			$activeOperator = $settings['m-operator'];
			
			if( isset( $operators[ $activeOperator ] ) && isset( $operators[ $activeOperator ]['location'] ) ){

				$operatorData = $operators[ $activeOperator ];
				require_once $operatorData['loader'];
				require_once $operatorData['myscript'];
			}

			require_once oelm_PATH.'includes/class-oelm-frontend.php';
		}
		
		if($this->is_request('admin')) {
			require_once oelm_PATH.'admin/class-oelm-admin-settings.php';
			require_once oelm_PATH.'admin/includes/class-oelm-users-table.php';
		}

		//Compatibilty with login/signup popup
		if(  class_exists( 'mo_El_Core' ) && ( defined( 'oelm_PRO' ) || !self::hasTrialExpired() ) ) {

			if($this->is_request('admin')){
				require_once oelm_PATH.'admin/includes/class-oelm-el-fields.php';
			}
			if($this->is_request('frontend')){
				require_once oelm_PATH.'includes/class-oelm-easy-login-functions.php';
			}
		}

		require_once oelm_PATH.'includes/class-oelm-verification.php';
		require_once oelm_PATH.'includes/class-oelm-otp-handler.php';

	}


	public static function hasTrialExpired(){
		$installed_date = get_option( 'oelm-installed-date' );
		if( !$installed_date ){
			update_option( 'oelm-installed-date', strtotime("now") );
			return false;
		}
		$todaysdate = strtotime("now");

		if( ( ( $todaysdate - $installed_date ) / ( 3600 * 24 ) ) > 15 ){
			return true;
		}
		return false;
	}


	/**
	 * Hooks
	*/
	public function hooks(){
		add_action( 'wp_loaded', array( $this, 'on_install' ) );
	}


	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}


	/**
	* On install
	*/
	public function on_install(){

		$version_option = 'oelm-version';
		$db_version 	= get_option( $version_option );

		//If first time installed
		if( !$db_version ){
			
		}

		if( version_compare( $db_version, oelm_VERSION, '<') ){
			//Update to current version
			update_option( $version_option, oelm_VERSION);
		}
	}

}

?>