jQuery(document).ready(function($){


	var otpForm = function( $otpForm, $parentForm ){
		var self 			= this;
		self.$otpForm 		= $otpForm;
		self.$parentForm 	= $parentForm;
		self.$verifyBtn 	= self.$otpForm.find('.oelm-otp-verify-btn');
		self.$inputs 		= self.$otpForm.find('.oelm-otp-input');
		self.$noticeCont 	= self.$otpForm.find('.oelm-notice');
		self.$resendLink 	= self.$otpForm.find('.oelm-otp-resend-link');
		self.noticeTimout 	= self.resendTimer = false;
		self.activeNumber 	= self.activeCode = '';
		self.formType 		= self.$parentForm.find('input[name="oelm-form-type"]').length ? self.$parentForm.find('input[name="oelm-form-type"]').val() : '';

		//Methods
		self.getPhoneNumber 	= self.getPhoneNumber.bind(this);
		self.validateInputs 	= self.validateInputs.bind(this);
		self.setPhoneData 		= self.setPhoneData.bind(this);
		self.onSuccess 			= self.onSuccess.bind(this);
		self.startResendTimer 	= self.startResendTimer.bind(this);
		self.showNotice 		= self.showNotice.bind(this);
		self.onOTPSent 			= self.onOTPSent.bind(this);

		self.$otpForm.on( 'keyup', '.oelm-otp-input', self.switchInput );
		self.$otpForm.on( 'submit', { otpForm: self }, self.onSubmit );
		self.$resendLink.on( 'click', { otpForm: self }, self.resendOTP );
		self.$otpForm.find('.oelm-otp-no-change').on( 'click', { otpForm: self }, self.onNumberChange );

		self.$otpForm.find( 'input[name="oelm-form-token"]' ).val( self.$parentForm.find( 'input[name="oelm-form-token"]' ).val() );

	}


	otpForm.prototype.onSubmit = function( event ){
		event.preventDefault();
		var otpForm = event.data.otpForm;
		if( !otpForm.validateInputs() ) return false;

		var form_data = {
			'otp': otpForm.getOtpValue(),
			'token': otpForm.$otpForm.find( 'input[name="oelm-form-token"]' ).val(),
			'action': 'oelm_otp_form_submit',
			'parentFormData': objectifyForm( otpForm.$parentForm.serializeArray() ),
		}

		$.ajax({
			url: oelm_phone_localize.adminurl,
			type: 'POST',
			data: form_data,
			success: function(response){
				if( response.notice ){
					otpForm.showNotice( response.notice );
				}

				if( response.error === 0 ){
					otpForm.onSuccess();
					otpForm.$otpForm.trigger( 'oelm_on_otp_success', [response] );
				}
			}
		});

	}

	otpForm.prototype.showNotice = function( notice ){
		var _t = this;
		clearTimeout(this.noticeTimout);
		this.$noticeCont.html( notice ).show();
		this.noticeTimout = setTimeout(function(){
			_t.$noticeCont.hide();
		},4000)
	}

	otpForm.prototype.onSuccess = function(){
		this.$otpForm.hide();
		this.$inputs.val('');
		this.$parentForm.show();
	}

	otpForm.prototype.switchInput = function( event ){

		if( $(this).val().length === parseInt( $(this).attr('maxlength') ) && $(this).next('input.oelm-otp-input').length !== 0 ){
			$(this).next('input.oelm-otp-input').focus();
		}

		//Backspace is pressed
		if( $(this).val().length === 0 && event.keyCode == 8 && $(this).prev('input.oelm-otp-input').length !== 0 ){
			$(this).prev('input.oelm-otp-input').focus().val('');
		}
		
	}

	otpForm.prototype.onNumberChange = function( event ){
		var otpForm = event.data.otpForm;
		otpForm.$otpForm.hide();
		otpForm.$parentForm.show();
		otpForm.$inputs.val('');
	}

	otpForm.prototype.validateInputs = function(){
		var passedValidation = true;
		this.$inputs.each( function( index, input ){
			var $input = $(input);
			if( !parseInt( $input.val() ) && parseInt( $input.val() ) !== 0 ){
				$input.focus();
				passedValidation = false;
				return false;
			}
		} );
		return passedValidation;
	}

	otpForm.prototype.getOtpValue = function(){
		var otp = '';
		this.$inputs.each( function( index, input ){
			otp += $(input).val();
		});
		return otp;
	}

	otpForm.prototype.setPhoneData = function( data ){
		this.$otpForm.find('.oelm-otp-no-txt').html( data.otp_txt );
		this.activeNumber = data.phone_no;
		this.activeCode   = data.phone_code;
	}

	otpForm.prototype.getPhoneNumber = function( $only ){
		
	}

	otpForm.prototype.startResendTimer = function(){
		var _t 				= this,
			$cont 			= this.$otpForm.find('.oelm-otp-resend'),
			$resendLink 	= $cont.find('.oelm-otp-resend-link'),
			$timer 			= $cont.find('.oelm-otp-resend-timer'),
			resendTime 		= parseInt( oelm_phone_localize.resend_wait );

		if( resendTime === 0 ) return;

		$resendLink.addClass('oelm-disabled');

		clearInterval( this.resendTimer );

		this.resendTimer = setInterval(function(){
			$timer.html('('+resendTime+')');
			if( resendTime <= 0 ){
				clearInterval( _t.resendTimer );
				$resendLink.removeClass('oelm-disabled');
				$timer.html('');
			}
			resendTime--;
		},1000) 
	}

	otpForm.prototype.resendOTP = function( event ){
		event.preventDefault();
		var otpForm = event.data.otpForm;

		otpForm.startResendTimer();

		var form_data = {
			action: 'oelm_resend_otp'
		}

		$.ajax({
			url: oelm_phone_localize.adminurl,
			type: 'POST',
			data: form_data,
			success: function(response){
				if(response.notice){
					otpForm.showNotice( response.notice );
				}
			}
		});
	}

	otpForm.prototype.onOTPSent = function( response ){
		var otpFormHandler = this;
		otpFormHandler.$otpForm.show();
		otpFormHandler.startResendTimer();
		otpFormHandler.setPhoneData( response );
		otpFormHandler.$parentForm.hide();
	}

	var i = 0;
	var PhoneForm = function( $phoneForm ){
		var self = this;
		self.$phoneForm = $phoneForm;
		self.prepare();
		self.$phoneInput 	= self.$phoneForm.find( '.oelm-phone-input' );
		self.$phoneCode 	= self.$phoneForm.find( '.oelm-phone-cc' );
		self.$otpForm 		= self.$phoneForm.next('form.oelm-otp-form');
		self.otpFormHandler = self.$otpForm.length ? new otpForm( self.$otpForm, self.$phoneForm ) : null;
		self.$noticeCont 	= self.$phoneForm.find('.oelm-notice');
		self.formType 		= self.$phoneForm.find('input[name="oelm-form-type"]').length ? self.$phoneForm.find('input[name="oelm-form-type"]').val() : ''
		self.$submit_btn 	= self.$phoneForm.find('button[type="submit"]');

		//Methods
		self.sendFormData 				= self.sendFormData.bind(this);
		self.getPhoneNumber 			= self.getPhoneNumber.bind(this);
		self.getOTPFormPreviousState 	= self.getOTPFormPreviousState.bind(this);
	}

	PhoneForm.prototype.prepare = function(){
		$( $('.oelm-form-placeholder').html() ).insertAfter(this.$phoneForm); //OTP form
		this.$phoneForm.prepend( '<div class="oelm-notice"></div>' ); //Notice element
	}


	PhoneForm.prototype.sendFormData = function( form_data ){

		var phoneForm 		= this;

		if( phoneForm.$submit_btn.length && phoneForm.$submit_btn.attr('name') ){
			form_data = form_data + '&' + phoneForm.$submit_btn.attr('name') + '=' + phoneForm.$submit_btn.val();
		}

		phoneForm.$submit_btn.addClass('oelm-processing');

		$.ajax({
			url: oelm_phone_localize.adminurl,
			type: 'POST',
			data: form_data,
			success: function(response){

				if( response.notice ){
					phoneForm.$noticeCont.html( response.notice ).show();
				}
				//Display otp form
				if( response.otp_sent ){
					phoneForm.otpFormHandler.onOTPSent( response );
				}

				phoneForm.$phoneForm.trigger( 'oelm_phone_register_form_submit', [ form_data, response ] );

				phoneForm.$submit_btn.removeClass('oelm-processing');
				
			}
		});
	}


	PhoneForm.prototype.getPhoneNumber = function( $only ){
		var phoneForm 		= this,
			phoneNumber 	= '';

		code 	= phoneForm.$phoneCode.length && phoneForm.$phoneCode.val().trim() ? phoneForm.$phoneCode.val().toString() : '';
		number 	= phoneForm.$phoneInput.val().toString().trim();

		if( $only === 'code' ){
			return code;
		}
		else if( $only === 'number' ){
			return number;
		}
		else{
			return code+number;
		}
	}


	PhoneForm.prototype.getOTPFormPreviousState = function(){
		var phoneFormHandler = this;
		//If requested for changing phone number & same number is put again.
 		if( ( !phoneFormHandler.$phoneCode.length || phoneFormHandler.otpFormHandler.activeCode ===  phoneFormHandler.getPhoneNumber('code') ) && phoneFormHandler.otpFormHandler.activeNumber ===  phoneFormHandler.getPhoneNumber('number') ){
 			phoneFormHandler.$otpForm.show();
 			phoneFormHandler.$phoneForm.hide();
 			return true;
 		}

 		return false;
	}


	var RegisterForm = function( $phoneForm ){

		PhoneForm.call( this, $phoneForm );
		var self 			= this;
	
		self.$changePhone 	= self.$phoneForm.find('.oelm-reg-phone-change');
		self.verifiedPHone 	= false;

		//Methods
		self.fieldsValidation = self.fieldsValidation.bind(this);

		//event
		self.$phoneForm.on( 'submit', { phoneForm: self }, self.onSubmit );
		self.$otpForm.on( 'oelm_on_otp_success', { phoneForm: self }, self.onOtpSuccess );
		self.$changePhone.on( 'click', { phoneForm: self }, self.changePhone );

		//If this is an update form
		if( self.getPhoneNumber( 'number' ) && self.formType === 'update_user' ){
			self.verifiedPHone = self.getPhoneNumber();
		}

	}


	RegisterForm.prototype = Object.create( PhoneForm.prototype );

	RegisterForm.prototype.fieldsValidation = function(){
		var phoneFormHandler = this,
			$phoneForm 		= phoneFormHandler.$phoneForm,
			error_string 		= ''; 

		if( phoneFormHandler.getPhoneNumber( 'number' ).length !== ( parseInt( phoneFormHandler.getPhoneNumber( 'number'  ) ) ).toString().length ){
			error_string 		= oelm_phone_localize.notices.invalid_phone;
		}
			
		//If is a woocommerce register form
		if( $phoneForm.find('input[name="woocommerce-register-nonce"]').length ){

			var $emailField 	= $phoneForm.find('input[name="email"]'),
				$passwordField 	= $phoneForm.find('input[name="password"]');

			//If email field is empty
			if( $emailField.length && !$emailField.val() ){
				error_string = oelm_phone_localize.notices.empty_email;
			}

			if( $passwordField.length && !$passwordField.val() ){
				error_string = oelm_phone_localize.notices.empty_password;
			}

		}

		console.log(error_string+'23');

		if( error_string ){
			phoneFormHandler.$noticeCont.html( error_string ).show();
			return false;
		}

		return true;

	}

	RegisterForm.prototype.onSubmit = function( event ){
		var phoneFormHandler = event.data.phoneForm;
		phoneFormHandler.$noticeCont.hide();

		//If number is optional
		if( phoneFormHandler.getPhoneNumber('number').length === 0 && oelm_phone_localize.show_phone !== 'required' ){
			return;
		}

		//Check if OTP form exists & number is already verified 
		if( !phoneFormHandler.otpFormHandler || phoneFormHandler.verifiedPHone === phoneFormHandler.getPhoneNumber() ) return;

		event.preventDefault();
 		event.stopImmediatePropagation();

 		if( !phoneFormHandler.fieldsValidation() ) return;

 		//If requested for changing phone number & same number is not put again.
 		
 		if( !phoneFormHandler.getOTPFormPreviousState() ) {
 			phoneFormHandler.verifiedPHone = false;
 			var form_data = phoneFormHandler.$phoneForm.serialize()+'&action=oelm_phone_register_form_submit';
			phoneFormHandler.sendFormData( form_data );
 			$(window).scrollTop( phoneFormHandler.$phoneForm.offset().top );
		}
	}


	RegisterForm.prototype.onOtpSuccess = function( event, response ){
		var phoneForm 	= event.data.phoneForm,
			otpFormHandler 	= phoneForm.otpFormHandler;

		phoneForm.verifiedPHone = phoneForm.getPhoneNumber();

		phoneForm.$phoneInput
			.prop('readonly', true)
			.addClass( 'oelm-disabled' );
		phoneForm.$changePhone.show();

		if( response.notice ){
			if( oelm_phone_localize.auto_submit_reg === "yes" ){
				phoneForm.$phoneForm.find('[type="submit"]').trigger('click');
			}
			phoneForm.$noticeCont.html( response.notice ).show();
		}

	}

	RegisterForm.prototype.changePhone = function( event ){
		$(this).hide();
		event.data.phoneForm.$phoneInput
			.prop( 'readonly', false )
			.focus();
	}


	$('input[name="oelm-reg-phone"]').each( function( key, form ){
		new RegisterForm( $(this).closest('form') );
	} );





	var LoginForm = function( $phoneForm, $parentForm ){

		var self 				= this;
		self.$parentForm 		= $parentForm;
		self.$phoneForm 		= $phoneForm;

		this.createFormHTML();

		PhoneForm.call( this, $phoneForm );

		self.$parentOTPLoginBtn = self.$parentForm.find('.oelm-open-lwo-btn');
		self.$loginOTPBtn 		= self.$phoneForm.find( '.oelm-login-otp-btn' );

		//event
		self.$phoneForm.on( 'submit', { phoneForm: self }, self.onLogin );
		self.$parentOTPLoginBtn.on( 'click', { phoneForm: self }, self.openLoginForm );
		self.$otpForm.on( 'oelm_on_otp_success', { phoneForm: self }, self.onOtpSuccess );

		//Back to parent form
		$('.oelm-low-back').on('click',function(){
			self.$parentForm.show();
			self.$phoneForm.hide();
		})

	}


	LoginForm.prototype = Object.create( PhoneForm.prototype );

	LoginForm.prototype.createFormHTML = function(){
		
		var formHTMLPlaceholder = this.$parentForm.find('.oelm-lwo-form-placeholder');
		//attach form elements
		this.$phoneForm.append( formHTMLPlaceholder.html() );
		formHTMLPlaceholder.remove();
		//If otp login form is displayed first
		if( oelm_phone_localize.login_first === "yes" ){
			this.$parentForm.hide();
		}
		else{
			this.$phoneForm.hide();
		}
	}

	LoginForm.prototype.onLogin = function( event ){

		event.preventDefault();
		event.stopImmediatePropagation();
		var phoneFormHandler = event.data.phoneForm;
		phoneFormHandler.$noticeCont.hide();

		if( !phoneFormHandler.getOTPFormPreviousState()  ){
			var form_data = 'action=oelm_login_with_otp&'+phoneFormHandler.$phoneForm.serialize()
			phoneFormHandler.sendFormData( form_data );
		}

	}


	LoginForm.prototype.onOtpSuccess = function( event, response ){
		var phoneFormHandler = event.data.phoneForm;

		if( response.notice ){
			phoneFormHandler.$noticeCont.html( response.notice ).show();
		}

		if( response.redirect ){
			window.location = response.redirect;
		}

	}
	LoginForm.prototype.openLoginForm = function( event, response ){
		var phoneFormHandler = event.data.phoneForm;
		phoneFormHandler.$phoneForm.show();
		phoneFormHandler.$parentForm.hide();
		$('.mo-el-notice').hide();
	}


	$('.oelm-open-lwo-btn').each( function( key, el ){
		var $parentForm = $(this).closest('form');
		//attach login with otp form
		$('<form class="mo-lwo-form"></form>').insertAfter( $parentForm );
		var $loginForm = $parentForm.next('.mo-lwo-form');
		new LoginForm( $loginForm, $parentForm );
	} );


	//converts serializeArray to json object
	function objectifyForm(formArray) {//serialize data function

	  var returnArray = {};
	  for (var i = 0; i < formArray.length; i++){
	    returnArray[formArray[i]['name']] = formArray[i]['value'];
	  }
	  return returnArray;
	}


	$('.mo-el-form-popup, .mo-el-form-inline').on('mo_el_form_tab_switched', function(){

		$(this).find('.oelm-notice').hide();

		var lwoForm = $(this).find('.mo-lwo-form'),
			parentLoginForm = $(this).find('.mo-el-form-login');

		//If login with OTP form is to be displayed first.
		if( parentLoginForm.length ){
			if( oelm_phone_localize.login_first === "yes" && lwoForm.length ){
				lwoForm.show();
				parentLoginForm.hide();
			}
			else{
				lwoForm.hide();
				parentLoginForm.show();
			}
		}

		$otpForm = $(this).find( '.oelm-otp-form' ); 
		if( $otpForm.length ){
			$otpForm.hide();
		}
	})


})