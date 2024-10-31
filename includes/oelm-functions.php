<?php



//Internationalization
if( !function_exists( 'oelm_load_plugin_textdomain' ) ):
    function oelm_load_plugin_textdomain() {
            $domain = 'otp-login-woocommerce';
            $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
            load_textdomain( $domain, WP_LANG_DIR . '/'.$domain.'-' . $locale . '.mo' ); //wp-content languages
            load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' ); // Plugin Languages
    }
    add_action('plugins_loaded','oelm_load_plugin_textdomain',100);
endif;


//Get tempalte
if( !function_exists( 'mo_get_template' ) ){
	function mo_get_template ( $template_name, $path = '', $args = array(), $return = false ) {

	    $located = mo_locate_template ( $template_name, $path );

	    if ( $args && is_array ( $args ) ) {
	        extract ( $args );
	    }

	    if ( $return ) {
	        ob_start ();
	    }

	    // include file located
	    if ( file_exists ( $located ) ) {
	        include ( $located );
	    }

	    if ( $return ) {
	        return ob_get_clean ();
	    }
	}
}


//Locate template
if( !function_exists( 'mo_locate_template' ) ){
	function mo_locate_template ( $template_name, $template_path ) {

	    // Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				'templates/' . $template_name,
				$template_name,
			)
		);

		//Check woocommerce directory for older version
		if( !$template && class_exists( 'woocommerce' ) ){
			if( file_exists( WC()->plugin_path() . '/templates/' . $template_name ) ){
				$template = WC()->plugin_path() . '/templates/' . $template_name;
			}
		}

	    if ( ! $template ) {
	        $template = trailingslashit( $template_path ) . $template_name;
	    }

	    return $template;
	}
}


//Add notice
function oelm_add_notice( $message, $notice_type = 'error' ){

	$classes = $notice_type === 'error' ? 'oelm-notice-error' : 'oelm-notice-success';

	$html = '<div class="'.$classes.'">'.$message.'</div>';

	return apply_filters('oelm_notice_html',$html,$message,$notice_type);
}

//Phone input field
function oelm_get_phone_input_field( $args = array(), $return = false ){

	$settings 	= get_option( 'oelm-phone-options', true );

	if( $settings['r-default-country-code-type'] === 'geolocation' ){
		$default_cc = oelm_Geolocation::get_phone_code();
	}else{
		$default_cc = $settings['r-default-country-code'];
	}

	$args = wp_parse_args( $args, array(
		'label' 		=> __( 'Phone', 'otp-login-woocommerce' ),
		'input_class' 	=> array(),
		'cont_class'	=> array(),
		'label_class' 	=> array(),
		'show_phone' 	=> $settings['r-phone-field'],
		'show_cc'	 	=> $settings['r-show-country-code-as'],
		'default_phone' => '',
		'default_cc' 	=> $default_cc,
		'form_token' 	=> mt_rand( 1000, 9999 ),
		'form_type' 	=> 'register_user'
	) );

	return mo_get_template( 'oelm-phone-input.php', oelm_PATH.'/templates/', $args, $return );
}



//OTP login form
function oelm_get_login_with_otp_form( $args = array(), $return = false ){

	$settings 	= get_option( 'oelm-phone-options', true );
    $default_cc = '';
    if( $settings['r-default-country-code-type'] === 'geolocation' ){
		$default_cc = oelm_Geolocation::get_phone_code();
	}else{
		$default_cc = $settings['r-default-country-code'];
	}
	$args = wp_parse_args( $args, array(
		'label' 			=> __( 'Phone', 'otp-login-woocommerce' ),
		'button_class' 		=> array(
			'button', 'btn'
		),
		'input_class' 		=> array(),
		'cont_class'		=> array(),
		'label_class' 		=> array(),
		'form_token' 		=> mt_rand( 1000, 9999 ),
		'form_type' 		=> 'login_with_otp',
		'redirect' 			=> trim( $settings['l-redirect'] ) ? esc_attr( $settings['l-redirect'] ) : $_SERVER['REQUEST_URI'],
		'is_login_popup' 	=> false,
		'login_first' 	=> $settings['l-login-display'],
        'default_cc'    => $default_cc,
	) );

	return mo_get_template( 'oelm-otp-login-button.php', oelm_PATH.'/templates/', $args, $return );
}


//Phone input form
function oelm_phone_input_form( $args = array(), $return = false ){

	$phone_input = oelm_get_phone_input_field( $args, true );

	$args = array(
		'phone_input' => $phone_input
	);

	return mo_get_template( 'oelm-phone-input-form.php', oelm_PATH.'/templates/', $args, $return );

}

//OTP Form
function oelm_phone_otp_form( $args, $return = false ){

	$settings = get_option( 'oelm-phone-options', true );

	$args = wp_parse_args( $args, array(
		'otp_length'	=> $settings['otp-digits']
	) );
	return mo_get_template( 'oelm-form-otp.php', oelm_PATH.'/templates', $args, $return );

}
add_action( 'wp_footer', 'oelm_phone_otp_form' );

//Get user phone number
function oelm_get_user_phone( $user_id, $code_or_phone = '' ){

	$code 	= esc_attr( get_user_meta( $user_id, 'oelm_phone_code', true ) );
	$number = esc_attr( get_user_meta( $user_id, 'oelm_phone_no', true ) );

	if( $code_or_phone === 'number' ){
		return $number;
	}else if( $code_or_phone === 'code' ){
		return $code;
	}

	return array(
		'code' 		=> $code,
		'number' 	=> $number
	);
}


/*
 * Search user by phone number
*/
function oelm_get_user_by_phone( $phone_no, $phone_code = '' ){

	$meta_query_args = array(
		'relation' => 'AND',
		array(
			'key' 		=> 'oelm_phone_no',
			'value' 	=> $phone_no,
			'compare' 	=> '='
		)
	);

	if( $phone_code ){
		$meta_query_args[] = array(
			'key' 		=> 'oelm_phone_code',
			'value' 	=> $phone_code,
			'compare' 	=> '='
		);
	}

	$args = array(
		'meta_query' => $meta_query_args
	);

	$user_query = new WP_User_Query( $args );

	$phone_users = $user_query->get_results();

	//In case there are more than one user registered with the same mobile number but different phone code ( Highly Unlikely ).
	//Get current user's location phone code
	if( count( $phone_users ) > 1 ){
		$phone_code = !$phone_code ? oelm_Geolocation::get_phone_code() : $phone_code;
		foreach ( $phone_users as $phone_user ) {
			if( oelm_get_user_phone( $phone_user->ID, 'code', true ) !== $phone_code ) continue;
			return $phone_user;
		}
	}
	elseif ( count( $phone_users ) === 1 ){
		return $phone_users[0];
	}
	else{
		return false;
	}

}


//Operator info
function oelm_operator_data(){

	$operator_dir = wp_get_upload_dir()['basedir'] .'/mocean-sms-sdk';

	$operators = array(
		'mocean' => array(
			'download' 	=> 'https://dl.dropboxusercontent.com/s/aw67ni7pwrciw9y/mocean.zip?dl=0',
			'doc' 		=> 'https://dl.dropboxusercontent.com/s/8lvgbtbcru7uf9a/otp%20easy%20login%20woocommerce%20step%20by%20step%20tutorial.docx?dl=0', // Remember to change this
			'loader' 	=> $operator_dir.'/mocean/vendor/autoload.php',
			'myscript' 	=> oelm_PATH.'/includes/servicesScripts/class-oelm-mocean.php'
		),
	);

	foreach ( $operators as $operator => $data ) {
		if( is_dir( $operator_dir.'/'.$operator ) ){
			$operators[ $operator ][ 'location' ] = $operator_dir.'/'.$operator ;
		}
	}

	return $operators;
}
?>