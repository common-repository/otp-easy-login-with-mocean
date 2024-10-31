<?php
/**
* Plugin Name: SMS OTP Easy Login with Mocean
* Plugin URI: http://moceanapi.com
* Author: MoceanAPI
* Version: 1.1.2
* Author URI: https://profiles.wordpress.org/moceanapiplugin/
* Description: Allows user to signup/login using OTP sms in woocommerce
* Tags: woocommerce, OTP Login, mobile login woocommerce, phone login, signup
*/


//Exit if accessed directly
if(!defined('ABSPATH')){
	return;
}

if ( ! function_exists( 'oelwm_fs' ) ) {
    // Create a helper function for easy SDK access.
    function oelwm_fs() {
        global $oelwm_fs;

        if ( ! isset( $oelwm_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/lib/freemius/start.php';

            $oelwm_fs = fs_dynamic_init( array(
                'id'                  => '10845',
                'slug'                => 'otp-easy-login-with-mocean',
                'type'                => 'plugin',
                'public_key'          => 'pk_53b4093b372f47edcb48009241283',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'oelm',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $oelwm_fs;
    }

    // Init Freemius.
    oelwm_fs();
    // Signal that SDK was initiated.
    do_action( 'oelwm_fs_loaded' );
}

define("oelm_PATH",plugin_dir_path(__FILE__)); // Plugin path
define("oelm_URL",plugins_url('',__FILE__)); // plugin url
define("oelm_PLUGIN_BASENAME",plugin_basename( __FILE__ ));
define("oelm_VERSION","1.0"); //Plugin version
define("oelm_LITE",true);

/**
 * Initialize
 *
 * @since    1.0.0
 */
function oelm_init(){


	do_action('oelm_before_plugin_activation');

	if ( ! class_exists( 'oelm' ) ) {
		require oelm_PATH.'/includes/class-oelm.php';
	}

	oelm();


}
add_action( 'plugins_loaded','oelm_init', 15 );

function oelm(){
	return oelm::get_instance();
}


/**
 * WooCommerce not activated admin notice
 *
 * @since    1.0.0
 */
function oelm_install_wc_notice(){
	?>
	<div class="error">
		<p><?php _e( 'WooCommerce Easy Login Popup is enabled but not effective. It requires WooCommerce in order to work.', 'oelm-woocommerce' ); ?></p>
	</div>
	<?php
}
