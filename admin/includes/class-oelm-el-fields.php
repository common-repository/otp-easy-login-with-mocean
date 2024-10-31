<?php

class oelm_Aff_Fields{

	public $elFields;


	public function __construct(){
		
		if( !mo_el()->aff->fields ) return;
		$this->elFields = mo_el()->aff->fields;
		add_action( 'mo_aff_easy-login-woocommerce_add_predefined_fields', array( $this, 'easy_login_field_settings' ), 15 );
	}

	public function easy_login_field_settings( ){
		
		$this->predefined_phoneCode_field();
		$this->predefined_phone_field();
	}



	public function predefined_phoneCode_field(){

		$field_type_id = $field_id = 'oelm-reg-phone-cc';

		$this->elFields->add_type(
			$field_type_id,
			'phone_code', 
			'Phone Code',
			array(
				'is_selectable' => 'no',
				'can_delete'	=> 'no',
				'icon' 			=> 'fas fa-code',
			)
		);

		$setting_options = $this->elFields->settings['mo_aff_phone_code'];
		//Removing settings as we will use from the mobile login settings page.
		unset( $setting_options['country_choose'], $setting_options['for_country_id'], $setting_options['country_list'], $setting_options['default'], $setting_options['phone_code_display_type'] );

		$my_settings = array(
			'unique_id' => array(
				'disabled' => 'disabled'
			),
			'cols' 		=> array(
				'value' => 'onehalf'
			),
			'icon' 		=> array(
				'value' => 'fas fa-phone'
			),
			'placeholder' => array(
				'value' => 'Phone Code'
			)
		);
		
		$setting_options = array_merge(
			$setting_options,
			$my_settings
		);

		$this->elFields->create_field_settings(
			$field_type_id,
			$setting_options
		);

		$this->elFields->add_field(
			$field_id,
			$field_type_id,
			array(
				'active' 	=> 'yes',
				'required' 	=> 'yes',
				'unique_id' => $field_id,
			),
			10			
		);
	}

	public function predefined_phone_field(){

		$field_type_id = $field_id = 'oelm-reg-phone';

		$this->elFields->add_type(
			$field_type_id,
			'phone',
			'Phone',
			array(
				'is_selectable' => 'no',
				'can_delete'	=> 'no',
				'icon' 			=> 'fas fa-phone'
			)
		);

		$setting_options = $this->elFields->settings['mo_aff_phone'];

		$my_settings = array(
			'unique_id' => array(
				'disabled' => 'disabled'
			),
			'cols' 		=> array(
				'value' => 'onehalf'
			),
			'placeholder' => array(
				'value' => 'Phone'
			)
		);
		
		$setting_options = array_merge(
			$setting_options,
			$my_settings
		);

		$this->elFields->create_field_settings(
			$field_type_id,
			$setting_options
		);

		$this->elFields->add_field(
			$field_id,
			$field_type_id,
			array(
				'active' 	=> 'yes',
				'required' 	=> 'yes',
				'unique_id' => $field_id,
			),
			15			
		);
	}

}

new oelm_Aff_Fields();

?>