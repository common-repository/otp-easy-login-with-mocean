<div class="oelm-reg-phinput-cont <?php echo esc_attr( implode( ' ', $cont_class ) ); ?>">

	<?php if( $label ): ?>
		<label class="<?php echo esc_attr( implode( ' ', $label_class ) ); ?>" for="oelm-reg-phone"> <?php echo $label; ?><?php if( $show_phone === 'required' ): ?>&nbsp;<span class="required">*</span><?php endif; ?></label>
	<?php endif; ?>

	<div class="<?php echo $show_cc !== 'disable' ? 'oelm-reg-has-cc' : ''; ?>">

		<?php if( $show_cc !== 'disable' ): ?>
			
			<?php $cc_list = include oelm_PATH.'/countries/phone.php'; ?>

			<?php if( $show_cc === 'selectbox' && !empty( $cc_list ) ): ?>
				<select class="oelm-phone-cc oelm-reg-phone-cc-select <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" name="oelm-reg-phone-cc" id="oelm-reg-phone-cc">
					<option disabled><?php _e( 'Select Country Code', 'otp-login-woocommerce' ); ?></option>
					<?php foreach( $cc_list as $country_code => $country_phone_code ): ?>
						<option value="<?php echo $country_phone_code; ?>" <?php echo $country_phone_code === $default_cc ? 'selected' : ''; ?> ><?php echo $country_code.' '.$country_phone_code; ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<?php if( $show_cc === 'input' ): ?>
				<input name="oelm-reg-phone-cc" class="oelm-phone-cc oelm-reg-phone-cc-text <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" value="<?php echo $default_cc; ?>" placeholder="<?php __( 'Country Code', 'otp-login-woocommerce' ); ?>" id="oelm-reg-phone-cc" <?php echo $show_phone === 'required' ? 'required' : ''; ?>>
			<?php endif; ?>

		<?php endif; ?>

		<div class="oelm-regphin">
			<input type="text" class="oelm-phone-input oelm-reg-phone <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" name="oelm-reg-phone" id="oelm-reg-phone" autocomplete="tel" value="<?php echo $default_phone; ?>" <?php echo $show_phone === 'required' ? 'required' : ''; ?>/>
			<span class="oelm-reg-phone-change"><?php _e( 'Change?', 'otp-login-woocommerce' ); ?></span>
		</div>

		<input type="hidden" name="oelm-form-token" value="<?php echo $form_token; ?>">

		<input type="hidden" name="oelm-form-type" value="<?php echo $form_type; ?>">

	</div>

</div>