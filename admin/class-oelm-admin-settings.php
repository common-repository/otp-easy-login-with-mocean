<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class oelm_Admin_Settings{

	protected static $_instance = null;

	public static $callbacks;
	public $all_options_array = array();
	public $tabs = array();


	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct(){

		self::$callbacks = include (oelm_PATH.'admin/includes/class-oelm-callbacks.php');

		$this->set_tabs(); // Set tabs

		add_action( 'admin_init', array( $this, 'set_default_options' ) );

		add_action('admin_menu',array($this,'add_menu_page'));
		add_action('admin_enqueue_scripts',array($this,'enqueue_scripts'));

		add_action('admin_init',array($this,'display_all_settings'));
		add_filter( 'plugin_action_links_' . oelm_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		add_filter( 'oelm_setting_args', array( $this, 'phone_operator_setting' ) );

		add_action( 'wp_ajax_download_operator_sdk', array( $this, 'download_operator_sdk' ) );
		add_action( 'plugins_loaded', array( $this, 'on_version_update' ), 20 );

		add_action( 'oelm_admin_settings_start', array( $this, 'popup_trial_notice' ) );
	}


	public function on_version_update(){

		$version_option = 'oelm-version';
		$db_version 	= get_option( $version_option );

		if( version_compare( $db_version, '1.0', '=' ) ){
			$this->fetch_sdk( 'mocean', true );
		}
	}


	public function set_tabs(){

		if( !empty( $this->tabs ) ){
			return $this->tabs;
		}

		$this->tabs = array(
			'phone' 	=> __( 'Phone','otp-login-woocommerce' ),
			'services' 	=> __( 'Services','otp-login-woocommerce' ),
			'balance_tab_boys' 	=> __( 'Check Balance Tab', 'otp-login-woocommerce' )
		);

	}


	public function set_default_options(){

		$default_options = $this->get_all_options_array();
		if( empty( $default_options ) ) return;

		foreach ($default_options as $option_name => $settings ) {

			//Return current option value from the database
			$option_value = (array) get_option($option_name) ;

			foreach ($settings as $setting) {
				if( $setting['type'] === 'setting' && isset( $setting['default'] ) && isset( $setting['id'] ) && !isset( $option_value[$setting['id']]) ){
					$option_value[$setting['id']] = $setting['default'];
				}
			}



			update_option( $option_name, $option_value );

		}
	}


	public function get_all_options_array(){

		if( !empty( $this->all_options_array ) ){
			return $this->all_options_array;
		}

		foreach ($this->tabs as $key => $title) {

			$path = oelm_PATH.'admin/includes/options/'.$key.'-options.php';

			if( file_exists( $path ) ){
				$this->all_options_array[ 'oelm-'.$key.'-options' ] = include $path;
			}
		}

		return $this->all_options_array;
	}


	public function enqueue_scripts($hook) {

		//Enqueue Styles only on plugin settings page
		if( $hook !== 'toplevel_page_oelm' && $hook !== 'login-signup-popup_page_oelm' ){
			return;
		}

		wp_enqueue_media(); // media gallery
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style( 'oelm-admin-style', oelm_URL . '/admin/assets/css/oelm-admin-style.css', array(), oelm_VERSION, 'all' );
		wp_enqueue_script( 'oelm-admin-js', oelm_URL . '/admin/assets/js/oelm-admin-js.js', array( 'jquery','wp-color-picker'), oelm_VERSION, false );

		wp_localize_script('oelm-admin-js','oelm_admin_localize',array(
			'adminurl'  => admin_url().'admin-ajax.php',
		));

	}


	public function add_menu_page(){

		if( defined( 'mo_EL_PATH' ) ){
			add_submenu_page(
				'mo-el',
				'OTP Login Settings',
				'OTP Login',
				'manage_options',
				'oelm',
				array($this,'menu_page_callback')
			);
		}else{
			add_menu_page(
				'SMS OTP Easy Login Settings', //Page Title
				'SMS OTP Easy Login', // Menu Titlle
				'manage_options',// capability
				'oelm', // Menu Slug
				array($this,'menu_page_callback') // callback
			);
		}
	}

	public function menu_page_callback(){
		$args = array(
			'tabs' 		=> $this->tabs
		);
		mo_get_template( "oelm-admin-display.php", oelm_PATH.'/admin/templates/', $args );
	}


	public function display_all_settings(){

		$default_options = $this->get_all_options_array();

		foreach ( $default_options as $option_name => $settings ) {
			$this->generate_settings( $settings, $option_name, $option_name, $option_name);
		}
	}


	public function generate_settings( $setting_fields, $page, $group, $option_name ){

		if(empty($setting_fields)){
			return;
		}

		foreach ($setting_fields as $field) {

			//Arguments for add_settings_field
			$args = $field;

			if( !isset($field['id']) || !isset($field['type']) || !isset($field['callback']) ) {
				continue;
			}

			//Check for callback functions
			if( is_callable( array( self::$callbacks, $field['callback'] ) ) ){
				$callback = array( self::$callbacks, $field['callback'] );
			}
			elseif ( is_callable( $field['callback'] ) ) {
				$callback = $field['callback'];
			}
			else{
				continue;
			}

			$title = isset($field['title']) ? $field['title'] : null;

			//Add a section
			if( $field['type'] === 'section' ){

				$section_args = array(
					'id' 		=> $field['id'],
					'title' 	=> $title,
					'callback' 	=> $callback,
					'page' 		=>$page
				);

				$section_args = apply_filters( 'oelm_section_args', $section_args );
				call_user_func_array( 'add_settings_section', array_values( $section_args ) );

			}

			//Add a setting field
			elseif( $field['type'] === 'setting' ){

				$setting_args = array(
					'id' 		=> $field['id'],
					'title' 	=> $title,
					'callback' 	=> $callback,
					'page' 		=> $page,
					'section' 	=> $field['section'],
					'args' 		=> $args
				);

				$setting_args = apply_filters( 'oelm_setting_args', $setting_args );

				call_user_func_array( 'add_settings_field', array_values( $setting_args ) );

			}

		}

		register_setting( $group, $option_name);

	}


	/**
	 * Show action links on the plugin screen.
	 *
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=oelm' ) . '" target="_blank">' . __('Settings', 'otp-login-woocommerce' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}


	public function phone_operator_setting( $args ){
		if( $args['id'] === 'm-operator' ){
			$args['callback'] = array( $this, 'phone_operator_setting_output' );
		}
		return $args;
	}

	//Modify  output for phone operator setting
	public function phone_operator_setting_output( $args ){

		$html = call_user_func( array( self::$callbacks, $args['callback'] ), $args );
		$operator_data = oelm_operator_data();
		ob_start();

		?>
		<ul class="oelm-opt-links">

			<?php foreach( $operator_data as $operator => $data ): ?>
				<li data-operator="<?php echo $operator; ?>" style="display: none;">
					<a class="oelm-sdk-dwnld" href="#">Download</a>
					<a href="<?php echo $data['doc']; ?>" target="_blank">Documentation</a>
				</li>
			<?php endforeach; ?>

		</ul>
		<span class="oelm-notice"></span>
		<?php
		$html .= ob_get_clean();
		echo $html;
	}


	protected function fetch_sdk( $operator, $force_download = false ){
		$base_dir = wp_get_upload_dir()['basedir'];

		//Check if SDK folder exists
		if( !is_dir( $base_dir.'/mocean-sms-sdk' ) ){
			mkdir( $base_dir.'/mocean-sms-sdk' );
		}
		$upload_dir = $base_dir.'/mocean-sms-sdk';

		//Check if SDK already installed
		if( is_dir( $upload_dir.'/'.$operator ) && !$force_download ){
			return new WP_Error( 'exists', 'You already have this SDK. <a class="oelm-sdk-dwnld-again" href="#">Download again</a> ' );
		}

		// If the function it's not available, require it.
		if ( ! function_exists( 'download_url' ) ) {
		    require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		//Download
		$operator_data 	= oelm_operator_data()[ $operator ];
		$permfile 		= $upload_dir.'/'.$operator.'.zip';
		$tmpfile 		= download_url( $operator_data['download'], $timeout = 300 );

		//Check if download was succesfull
		if( is_wp_error( $tmpfile ) ){
			return $tmpfile;
		}
		copy( $tmpfile, $permfile );
		unlink( $tmpfile ); // must unlink afterwards

		//Unzip
		WP_Filesystem();
		$unzipfile = unzip_file( $permfile, $upload_dir );

		return $unzipfile;
	}


	public function download_operator_sdk(){

		if( !isset( $_POST['operator'] ) ) return;

		try {

			$fetchSdk = $this->fetch_sdk( $_POST['operator'], isset( $_POST['download_again'] ) && $_POST['download_again'] === "yes" );

			if( is_wp_error( $fetchSdk ) ){
				throw new mo_Exception( $fetchSdk );
			}

			//All good
			wp_send_json( array(
				'error' 	=> 0,
				'notice' 	=> 'Downloaded succesfully'
			) );

		} catch ( mo_Exception $e) {
			wp_send_json( array(
				'error' 	=> 1,
				'notice' 	=> $e->getMessage()
			) );
		}


	}


	public function popup_trial_notice(){
		if( !class_exists( 'mo_El_Core' ) || defined( 'oelm_PRO' ) ) return;
		?>
		<div class="oelm-trial-notice">
			<span>Welcome to OTP login using Mocean API, visit Moceanapi.com to get your API credentials and start using this plugin now!</span>
		</div>
		<?php
	}


}

function oelm_admin_settings(){
	return oelm_Admin_Settings::get_instance();
}

oelm_admin_settings();

?>
