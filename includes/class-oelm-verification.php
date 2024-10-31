<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class oelm_Phone_Verification{

	public static $settings;
	public static $ip_address;

	public function __construct(){
		self::$settings = get_option( 'oelm-phone-options', true );
		$this->hooks();
	}

	/**
	 * Hooks
	*/
	public function hooks(){

		add_action( 'wp_ajax_oelm_phone_register_form_submit', array( $this, 'process_phone_form' ) );
		add_action( 'wp_ajax_nopriv_oelm_phone_register_form_submit', array( $this, 'process_phone_form' ) );

		add_action( 'wp_ajax_oelm_otp_form_submit', array( $this, 'process_otp_form' ) );
		add_action( 'wp_ajax_nopriv_oelm_otp_form_submit', array( $this, 'process_otp_form' ) );

		add_action( 'wp_ajax_oelm_resend_otp', array( $this, 'resendOTP' ) );
		add_action( 'wp_ajax_nopriv_oelm_resend_otp', array( $this, 'resendOTP' ) );

		add_action( 'init', array( $this, 'process_phone_form' ), 5 );

		add_action( 'user_register', array( $this, 'handle_phone_on_user_registration' ) );

		add_filter( 'authenticate', array( $this, 'process_login' ), 5, 3 );

		add_action( 'oelm_otp_validation_success', array( $this, 'wc_myaccount_update_phone' ), 10, 2 );
		add_action( 'oelm_otp_validation_success', array( $this, 'login_user_with_otp' ), 10, 2 );

		add_action( 'wp_ajax_nopriv_oelm_login_with_otp', array( $this, 'process_login_with_otp_form' ) );
	}


	/**
	 * Update phone from woocommerce my account page
	 *
	 * @param  	string 		$parent_form_type 	Parent form type - update in this case
	 * @param 	array 		$otp_data 			User phone otp data
	*/

	public function wc_myaccount_update_phone( $parent_form_type, $otp_data ){

		if( $parent_form_type === 'update_user' ){
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'oelm_phone_no', sanitize_text_field( $otp_data['phone_no'] ) );
			update_user_meta( $user_id, 'oelm_phone_code', sanitize_text_field( $otp_data['phone_code'] ) );
		}

	}

	/**
	 * Login user with OTP after OTP Verification
	 *
	 * @param  	string 		$parent_form_type 	Parent form type
	 * @param 	array 		$otp_data 			User phone otp data
	*/

	public function login_user_with_otp( $parent_form_type, $otp_data ){

		if( $parent_form_type === 'login_user_with_otp' ){

			$user = oelm_get_user_by_phone( $otp_data['phone_no'], $otp_data['phone_code'] );

			if( $user ){
				//Logging user
				wp_clear_auth_cookie();
			    wp_set_current_user ( $user->ID );
			    wp_set_auth_cookie  ( $user->ID );

			    $redirect = '';

			    if ( isset( $_POST['parentFormData'][ 'redirect' ] ) ) {
					$redirect = sanitize_text_field($_POST['parentFormData'][ 'redirect' ]);
				}

				$redirect = wp_validate_redirect( apply_filters( 'oelm_login_with_otp_redirect', $redirect ) );

			    wp_send_json(array(
			    	'redirect' 	=> $redirect,
					'error' 	=> 0,
					'notice' 	=> oelm_add_notice( __( 'Login successful', 'otp-login-woocommerce' ), 'success' )
				));
			}
		}

	}

	/**
	 * Login with username/Phone and password
	 *
	 * @param  	object 		$user 				User object if exists
	 * @param 	string 		$username 			Username/Phone
	 * @param 	string 		$password 			Password
	*/
	public function process_login( $user, $username, $password ){

		$user_to_login = null;

		//Check if username provided is a phone number
		$phone_user = oelm_get_user_by_phone( $username );

		if( !$phone_user ){
			return $user;
		}

		//if password validates
		if ( wp_check_password( $password, $phone_user->user_pass, $phone_user->ID ) ){
			return $phone_user;
		}

		return $user;

	}

	/**
	 * Forms with phone input field
	 *
	 * @param  	string 		$form_type 		Form type
	*/
	public static function is_a_phone_form( $form_type = '' ){

		//These forms will do the user registration.
		$user_register_forms = apply_filters( 'oelm_user_register_phone_forms', array() );

		if( $form_type === 'register_user' ){
			$forms = $user_register_forms;
		}
		else{
			$forms = apply_filters( 'oelm_get_phone_forms', array_merge(
				array(),
				$user_register_forms
			) );
		}

		$is_a_phone_form = false;

		foreach( $forms as $form ){
			if( isset( $_POST[ $form ] ) ){
				$is_a_phone_form = true;
				break;
			}
		}

		return $is_a_phone_form;

	}


	/**
	 * Save phone fields on user registration
	 *
	 * @param  	int 	$user_id 		User ID
	*/
	public function handle_phone_on_user_registration( $user_id ){

		if( !self::is_a_phone_form( 'register' ) ) return;

		//Proceed only if user is registered with a phone number
		if( !isset( $_POST['oelm-reg-phone'] ) || !isset( $_POST['oelm-form-token'] ) ){
			return;
		}

		$phone_otp_data = oelm_Otp_Handler::get_otp_data();
		if( !$phone_otp_data['verified'] ) return;

		$phone_code = sanitize_text_field( $phone_otp_data['phone_code'] );
		$phone 		= sanitize_text_field( $phone_otp_data['phone_no'] );

		update_user_meta( $user_id, 'oelm_phone_no', $phone );
		update_user_meta( $user_id, 'oelm_phone_code', $phone_code );

	}

	/**
	 * Resend OTP
	 *
	*/
	public function resendOTP(){

		try {

			$SMSSent = oelm_Otp_Handler::resendOTPSMS();

			if( is_wp_error( $SMSSent ) ){
				throw new mo_Exception( $SMSSent );
			}
			wp_send_json(array(
				'error' 	=> 0,
				'notice' 	=> oelm_add_notice( __( 'OTP Resent', 'otp-login-woocommerce' ), 'success' )
			));
		} catch (Exception $e) {
			do_action( 'oelm_otp_resend_failed', $phone_no, $e );
			wp_send_json(array(
				'error' 	 => 1,
				'error_code' => $e->getWpErrorCode(),
				'notice' 	 => oelm_add_notice( $e->getMessage(), 'error' )
			));
		}


	}

	/**
	 * Process form with phone input field
	 *
	*/
	public function process_phone_form(){

		try {

			//If phone field is empty
			if( !isset( $_POST['oelm-reg-phone'] ) || !trim( $_POST['oelm-reg-phone'] )  || !isset( $_POST['oelm-form-token'] ) ){
				if( self::is_a_phone_form() && self::$settings['r-phone-field'] === 'required' ){
					throw new mo_Exception( __( 'Phone field cannot be empty', 'otp-login-woocommerce' ) );
				}
				return;
			}

			$phone_no = sanitize_text_field( $_POST['oelm-reg-phone'] );

			//Check for phone code
			if( self::$settings['r-show-country-code-as'] !== 'disable' ){
				if( !isset( $_POST['oelm-reg-phone-cc'] ) || !$_POST['oelm-reg-phone-cc'] ){
					throw new mo_Exception( __( 'Please select country code', 'otp-login-woocommerce' ) );
				}
				$phone_code = sanitize_text_field( $_POST['oelm-reg-phone-cc'] );
			}else{
				$phone_code = self::$settings['r-default-country-code-type'] === 'geolocation' && oelm_Geolocation::get_phone_code() ? oelm_Geolocation::get_phone_code() : self::$settings['r-default-country-code'];
			}

			//If user register form, do the registration.
			if( self::is_a_phone_form( 'register' ) ){

				$user = oelm_get_user_by_phone( $phone_no, $phone_code );

				if( $user ){

					//If this is an update and the same number is entered, skip
					if( isset( $_POST['oelm-form-type'] ) && $_POST['oelm-form-type'] === 'update_user' && $user->ID === get_current_user_id() ){
						return;
					}

					throw new mo_Exception( "Sorry, this phone number is already in use." );
				}

			}

			$phone_otp_data = oelm_Otp_Handler::get_otp_data();

			if( !is_array( $phone_otp_data ) ){
				$phone_otp_data = array();
			}

			//If phone has been verified, return
			if( isset( $phone_otp_data[ 'phone_no' ] ) && $phone_otp_data['phone_no'] === $phone_no && isset( $phone_otp_data[ 'phone_code' ] ) && $phone_otp_data['phone_code'] === $phone_code && isset( $phone_otp_data['verified'] ) && $phone_otp_data['verified'] && isset( $phone_otp_data['form_token'] ) && $phone_otp_data['form_token'] === $_POST['oelm-form-token']  ){
				return;
			}else{

				$form_validation = apply_filters( 'oelm_phone_form_validation', new WP_Error(), $phone_code, $phone_no, $phone_otp_data );

				if( $form_validation->get_error_code() ){
					throw new mo_Exception( $form_validation->get_error_message() );
				}

				//Send OTP SMS only if its ajax call.
				if( !wp_doing_ajax() ){
					wp_die( __( 'Please verify your mobile number', 'otp-login-woocommerce' ) );
				};

				$otp = oelm_Otp_Handler::sendOTPSMS( $phone_code, $phone_no );

				if( is_wp_error( $otp ) ){
					throw new mo_Exception( $otp->get_error_message() );
				}

				wp_send_json(array(
					'otp_sent' 	=> 1,
					'phone' 	=> $phone_code.$phone_no,
					'phone_no' 	=> $phone_no,
					'phone_code'=> $phone_code,
					'error' 	=> 0,
					'otp_txt' 	=> sprintf( __( 'Please enter the OTP sent to <br> %s', 'otp-login-woocommerce' ), $phone_code.$phone_no ),
				));
			}


		} catch (Exception $e) {

			$notice = apply_filters( 'oelm_phone_register_errors', $e->getMessage() );

			do_action( 'oelm_phone_register_failed' );

			wp_send_json(array(
				'error' 	=> 1,
				'notice' 	=> oelm_add_notice( $notice, 'error' )
			));
		}


	}

	/**
	 * Process OTP Form
	*/
	public function process_otp_form(){

		try {

			if( isset( $_POST['otp'] ) ){


				$phone_otp_data = oelm_Otp_Handler::get_otp_data();

				if( !is_array( $phone_otp_data ) ){
					$phone_otp_data = array();
				}

				//Check for incorrect limit
				if( isset( $phone_otp_data['incorrect'] ) && $phone_otp_data['incorrect'] > self::$settings['otp-incorrect-limit'] ){
					throw new mo_Exception( __( 'Number of tries exceeded, Please try again in few minutes', 'otp-login-woocommerce' ) );
				}

				if( isset( $phone_otp_data['otp'] ) && ( $phone_otp_data['otp'] === (int) $_POST['otp'] ) ){

					if( isset( $phone_otp_data['expiry'] ) && strtotime('now') > (int) $phone_otp_data['expiry'] ){
						throw new mo_Exception( __( 'OTP Expired', 'otp-login-woocommerce' ) );
					}

					oelm_Otp_Handler::set_otp_data( array(
						'verified' 			=> true,
						'form_token' 		=> sanitize_text_field( $_POST['token'] ),
						'incorrect' 		=> 0,
						'sent_items' 		=> 0,
						'expiry' 			=> '',
						'created' 			=> '',
					) );

					$parent_form_type = sanitize_text_field(isset( $_POST['parentFormData'] )) && sanitize_text_field(isset( $_POST['parentFormData']['oelm-form-type'] )) ? sanitize_text_field($_POST['parentFormData']['oelm-form-type']) : '';

					//Hook functions on OTP verification
					do_action( 'oelm_otp_validation_success', $parent_form_type, oelm_Otp_Handler::get_otp_data() );

					wp_send_json(array(
						'error' 	=> 0,
						'notice' 	=> oelm_add_notice( __( 'Thank you for verifying your number.', 'otp-login-woocommerce' ), 'success' )
					));

				}

				$incorrect = isset( $phone_otp_data['incorrect'] ) ? $phone_otp_data['incorrect'] + 1 : 1;

                $incorrect = 0;

				oelm_Otp_Handler::set_otp_data( 'incorrect', $incorrect );

			}

			throw new mo_Exception( __( 'Invalid OTP', 'otp-login-woocommerce' ) );

		} catch (Exception $e) {

			$notice = apply_filters( 'oelm_otp_errors', $e->getMessage() );

			wp_send_json(array(
				'error' 	=> 1,
				'notice' 	=> oelm_add_notice( $notice, 'error' )
			));
		}

	}

	/**
	 * Process login with OTP Form
	 *
	 * @param  	int 	$user_id 		User ID
	*/
	public function process_login_with_otp_form(){

		try {

			if( !isset( $_POST['oelm-phone-login'] ) || !trim($_POST['oelm-phone-login']) ){
				throw new mo_Exception( __( 'Phone field cannot be empty', 'otp-login-woocommerce' ) );
			}

			$phone_no  	= sanitize_text_field( $_POST['oelm-phone-login'] );
			$phone_user = oelm_get_user_by_phone( $phone_no );

			if( !$phone_user ){
				throw new mo_Exception( __( 'We cannot find an account with that mobile number', 'otp-login-woocommerce' ) );
			}

			$phone_code = oelm_get_user_phone( $phone_user->ID, 'code' );

			if( !$phone_code ){
				throw new mo_Exception( __( 'Something went wrong. Please contact site administrator.', 'otp-login-woocommerce' ) );
			}

			//Send OTP SMS
			$otp = oelm_Otp_Handler::sendOTPSMS( $phone_code, $phone_no );

			if( is_wp_error( $otp ) ){
				throw new mo_Exception( $otp->get_error_message() );
			}

			wp_send_json(array(
				'otp_sent' 		=> 1,
				'phone_code' 	=> $phone_code,
				'phone_no' 		=> $phone_no,
				'error' 		=> 0,
				'otp_txt' 		=> sprintf( __( 'Please enter the OTP sent to <br> %s', 'otp-login-woocommerce' ), $phone_code.$phone_no ),
			));

		} catch ( Exception $e ) {

			$notice = apply_filters( 'oelm_login_with_otp_errors', $e->getMessage() );

			wp_send_json(array(
				'error' 	=> 1,
				'notice' 	=> oelm_add_notice( $notice, 'error' )
			));
		}


	}


}

new oelm_Phone_Verification();