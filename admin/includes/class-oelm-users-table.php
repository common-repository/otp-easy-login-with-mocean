<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class oelm_Users_Table{


	protected static $_instance = null;

	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	public function __construct(){
		add_action( 'edit_user_profile', array( $this, 'edit_profile_page' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_customer_meta_fields' ) );
		add_action( 'user_profile_update_errors', array( $this, 'verify_user_fields' ), 10, 3 );
		add_filter( 'mo_el_user_profile_fields', array( $this, 'remove_phone_fields' ) );
	}


	public function remove_phone_fields( $fields ){
		unset( $fields['oelm-reg-phone'], $fields['oelm-reg-phone-cc']  );
		return $fields;
	}


	public function verify_user_fields( $wp_error, $update, $user ){
		if( isset( $_POST['oelm-user-reg-phone'] ) && $_POST['oelm-user-reg-phone'] ){
			if( !isset( $_POST['oelm-user-reg-phone-cc'] ) || !$_POST['oelm-user-reg-phone-cc'] ){
				$wp_error->add( 'no-phone-code', __( 'Please select country code', 'otp-login-woocommerce' ) );
			}
			$user_by_phone = oelm_get_user_by_phone( sanitize_text_field($_POST['oelm-user-reg-phone'], sanitize_text_field($_POST['oelm-user-reg-phone-cc'])) );
			if( $user_by_phone && $user_by_phone->ID !== $user->ID  ){
				$wp_error->add( 'user-already-exists', sprintf( __( 'User: #%1s is already registered with %2s phone number', 'otp-login-woocommerce' ), $user->ID, esc_attr( $_POST['oelm-user-reg-phone'] ) ) );
			}
		}
	}


	public function edit_profile_page( $user ){
		
		$phoneCodes = (array) include oelm_PATH.'/countries/phone.php';
		?>
		<table class="form-table">
			<tr>
				<th><?php  _e( 'Phone', 'otp-login-woocommerce' ); ?></th>
				<td>
					<select name="oelm-user-reg-phone-cc">
						<option disabled><?php _e( 'Select Country Code', 'otp-login-woocommerce' ); ?></option>
						<?php foreach( $phoneCodes as $country_code => $country_phone_code ): ?>
							<option value="<?php echo $country_phone_code; ?>" <?php echo $country_phone_code === get_user_meta( $user->ID, 'oelm_phone_code',true) ? 'selected' : ''; ?> ><?php echo $country_code.' '.$country_phone_code; ?></option>
						<?php endforeach; ?>
				</select>
					<input type="text" name="oelm-user-reg-phone" value="<?php echo get_user_meta( $user->ID, 'oelm_phone_no',true); ?>">
				</td>
			</tr>
		</table>
		<?php
	}


	/**
	 * Save Address Fields on edit user pages.
	 *
	 * @param int $user_id User ID of the user being saved
	 */
	public function save_customer_meta_fields( $user_id ) {

		if( isset( $_POST['oelm-user-reg-phone'] ) ){
			update_user_meta( $user_id, 'oelm_phone_no', sanitize_text_field( $_POST['oelm-user-reg-phone'] ) );
		}

		if( isset( $_POST['oelm-user-reg-phone-cc'] ) ){
			update_user_meta( $user_id, 'oelm_phone_code', sanitize_text_field( $_POST['oelm-user-reg-phone-cc'] ) );
		}

	}

}

function oelm_users_table(){
	return oelm_Users_Table::get_instance();
}
oelm_users_table();

?>
