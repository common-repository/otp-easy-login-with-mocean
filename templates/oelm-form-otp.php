<div class="oelm-form-placeholder">
	<form class="oelm-otp-form">

		<div class="oelm-otp-sent-txt">
			<span class="oelm-otp-no-txt"></span>
			<span class="oelm-otp-no-change"> <?php _e( "Change", 'otp-login-woocommerce' ); ?></span>
		</div>

		<div class="oelm-otp-notice-cont">
			<div class="oelm-notice"></div>
		</div>

		<div class="oelm-otp-input-cont">
			<?php for ( $i= 0; $i < $otp_length; $i++ ): ?>
				<input type="text" maxlength="1" autocomplete="off" name="oelm-otp[]" class="oelm-otp-input">
			<?php endfor; ?>
		</div>

		<input type="hidden" name="oelm-otp-phone-no" >
		<input type="hidden" name="oelm-otp-phone-code" >

		<button type="submit" class="button btn oelm-otp-verify-btn"><?php _e( 'Verify', 'otp-login-woocommerce' ); ?> </button>

		<div class="oelm-otp-resend">
			<a class="oelm-otp-resend-link"><?php _e( 'Not received your code? Resend code', 'otp-login-woocommerce' ); ?></a>
			<span class="oelm-otp-resend-timer"></span>
		</div>

		<input type="hidden" name="oelm-form-token" value="">

	</form>

</div>