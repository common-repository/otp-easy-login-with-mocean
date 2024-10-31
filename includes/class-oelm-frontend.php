<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class oelm_Phone_Frontend{

	protected static $_instance = null;
	public $settings;

	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct(){
		$this->settings = get_option( 'oelm-phone-options', true );
		$this->hooks();
	}

	public function hooks(){

		if( $this->settings['l-enable-login-with-otp'] === "yes" ){
			add_action( 'woocommerce_login_form_end', array( $this, 'wc_login_with_otp_form' ) );
			add_filter( 'gettext', array( $this, 'wc_login_username_field_i8n' ), 999, 3 );
		}

		if( $this->settings['r-enable-phone'] === "yes" ){
			add_action( 'woocommerce_register_form_start', array( $this, 'wc_register_phone_input' ) );
			add_action( 'woocommerce_edit_account_form_start', array( $this, 'wc_myaccount_edit_phone_input' ) );
			add_filter(  'oelm_get_phone_forms', array( $this, 'add_wc_register_form_for_phone' ) );
		}
		
		
		//add_filter(	'wc_get_template', array( $this, 'override_myaccount_template' ), 9999999, 5 );
		add_action( 'wp_enqueue_scripts' ,array( $this,'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts' , array( $this,'enqueue_scripts' ), 0 );
		
	}


	public function add_wc_register_form_for_phone( $register_forms ){
		$register_forms[] = 'woocommerce-register-nonce'; // wc registration
		$register_forms[] = 'save_account_details'; //wc edit account
		return $register_forms;
	}


	public function wc_login_with_otp_form(){
		$args = apply_filters( 'oelm_wc_otp_login_btn', self::wc_register_phone_input_args() );
		return oelm_get_login_with_otp_form( $args );

	}


	//Enqueue stylesheets
	public function enqueue_styles(){
		wp_enqueue_style( 'oelm-style', oelm_URL.'/assets/css/oelm-style.css', array(), oelm_VERSION );
		$settings = get_option( 'oelm-phone-options', true );
		$style = '';
		if( $settings[ 'l-login-display' ] === "yes" ){
			$style = "
				.mo-el-form-login{
					display: none;
				}
			";
		}
		wp_add_inline_style('oelm-style', $style );
	}

	//Enqueue javascript
	public function enqueue_scripts(){
		wp_enqueue_script( 'oelm-phone-js', oelm_URL.'/assets/js/oelm-phone-js.js', array('jquery'), oelm_VERSION, true ); // Main JS

		$settings = get_option( 'oelm-phone-options', true );

		wp_localize_script('oelm-phone-js','oelm_phone_localize',array(
			'adminurl'  			=> admin_url().'admin-ajax.php',
			'resend_wait' 			=> $settings['otp-resend-wait'],
			'phone_form_classes'	=> json_encode( self::phone_form_classes() ),
			'auto_submit_reg' 		=> $settings['r-auto-submit'],
			'show_phone' 			=> $settings['r-phone-field'],
			'notices' 				=> array(
				'empty_phone' 	=> oelm_add_notice( __( 'Please enter a phone number', 'otp-login-woocommerce' ), 'error' ),
				'empty_email' 	=> oelm_add_notice( __( 'Email address cannot be empty.', 'otp-login-woocommerce' ), 'error' ),
				'empty_password'=> oelm_add_notice( __( 'Please enter a password.', 'otp-login-woocommerce' ), 'error' ),
				'invalid_phone' => oelm_add_notice( __( 'Please enter a valid phone number without any special characters & country code.', 'otp-login-woocommerce' ), 'error' ),
			),
			'login_first' 	=> $settings['l-login-display'],
			//'phone_first' 			=> $settings['r-phone-first'],
		));
	}


	public function override_myaccount_template( $template, $template_name, $args, $template_path, $default_path ){

		if( $template_name === 'myaccount/form-login.php' ){
			$template = mo_locate_template( 'oelm-form-login.php', oelm_PATH.'/templates/' );
		}
		return $template;
	}

	public static function wc_register_phone_input_args( $args = array() ){
		$default_args = array(
			'label' 		=> __('Phone', 'otp-login-woocommerce'),
			'cont_class' 	=> array(
				'woocommerce-form-row',
				'woocommerce-form-row--wide',
				'form-row form-row-wide'
			),
			'input_class' 	=> array(
				'woocommerce-Input',
				'input-text',
				'woocommerce-Input--text'
			)
		);
		return wp_parse_args( $args, $default_args );
	}

	public function wc_myaccount_edit_phone_input(){
		return oelm_get_phone_input_field( self::wc_register_phone_input_args(
			array(
				'form_type' 	=> 'update_user',
				'default_phone' => oelm_get_user_phone( get_current_user_id(), 'number' ),
				'default_cc'	=> oelm_get_user_phone( get_current_user_id(), 'code' ),
			)
		) );
	}

	public function wc_register_phone_input(){
		return oelm_get_phone_input_field( self::wc_register_phone_input_args() );
	}

	public function wc_register_phone_form(){
		return oelm_phone_input_form( self::wc_register_phone_input_args() );
	}


	public static function phone_form_classes(){
		return apply_filters( 'oelm_phone_form_classes', array(
			'woocommerce-form-register'
		) );
	}


	public function wc_login_username_field_i8n( $translation, $text, $domain ){
		if( $domain === 'woocommerce' && strcmp( $translation, 'Username or email address' ) === 0 ){
			return __( 'Phone or Email address', 'otp-login-woocommerce' );
		}
		return $translation;
	}

}

function oelm_phone_frontend(){
	return oelm_Phone_Frontend::get_instance();
}
oelm_phone_frontend();
