<span class="oelm-or"><?php _e( 'Or', 'otp-login-woocommerce' ); ?></span>
<button type="button" class="oelm-open-lwo-btn button btn <?php echo implode( ' ', $button_class ); ?> "><?php _e( 'Login with OTP', 'otp-login-woocommerce' ); ?></button>

<div class="oelm-lwo-form-placeholder" <?php if( $login_first !== 'yes' ): ?> style="display: none;" <?php endif; ?> >

	<div class="oelm-login-phinput-cont <?php echo esc_attr( implode( ' ', $cont_class ) ); ?>">

		<?php if( $label ): ?>
			<label class="<?php echo esc_attr( implode( ' ', $label_class ) ); ?>" for="oelm-login-phone"> <?php echo $label; ?>&nbsp;<span class="required">*</span></label>
		<?php endif; ?>


		<?php if( $is_login_popup ): ?>

			<div class="mo-aff-group">
				<div class="mo-aff-input-group">
					<span class="mo-aff-input-icon fas fa-phone"></span>
					<input type="text" placeholder="<?php _e( 'Phone', 'otp-login-woocommerce' ); ?>" name="oelm-phone-login" class="oelm-phone-login oelm-phone-input <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" required autocomplete="tel">
				</div>
			</div>
		
		<?php else: ?>
		<?php $cc_list = include oelm_PATH.'/countries/phone.php'; ?>
			<select class="oelm-phone-cc oelm-reg-phone-cc-select <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" name="oelm-reg-phone-cc" id="oelm-reg-phone-cc">
					<option disabled><?php _e( 'Select Country Code', 'otp-login-woocommerce' ); ?></option>
					<?php foreach( $cc_list as $country_code => $country_phone_code ): ?>
						<option value="<?php echo $country_phone_code; ?>" <?php echo $country_phone_code === $default_cc ? 'selected' : ''; ?> ><?php echo $country_code.' '.$country_phone_code; ?></option>
					<?php endforeach; ?>
			</select>
			<input type="text" placeholder="<?php _e( 'Just enter the numbers after the + sign and your country code [example: +1(6628223300)]', 'otp-login-woocommerce' ); ?>" name="oelm-phone-login" class="oelm-phone-login oelm-phone-input <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" required  autocomplete="tel" >

		<?php endif; ?>

	</div>

	<input type="hidden" name="oelm-form-token" value="<?php echo $form_token; ?>">
	<input type="hidden" name="oelm-form-type" value="login_user_with_otp">
	<input type="hidden" name="redirect" value="<?php echo $redirect; ?>">
	<button type="submit" class="oelm-login-otp-btn <?php echo implode( ' ', $button_class ); ?> "><?php _e( 'Login with OTP', 'otp-login-woocommerce' ); ?></button>
	<span class="oelm-or"><?php _e( 'Or', 'otp-login-woocommerce' ); ?></span>
	<button type="button" class="oelm-low-back <?php echo implode( ' ', $button_class ); ?>"><?php _e( 'Login with Email & Password', 'otp-login-woocommerce' ); ?></button>
</div>