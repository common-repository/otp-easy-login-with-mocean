jQuery(document).ready(function($){
	'use strict';

	//Initialize Color Picker
	$(function(){
		$('.color-field').wpColorPicker();
	});

	//Sidebar JS
	$(function(){
		var show_class 	= 'mo-sidebar-show';
		var sidebar 	= $('.mo-sidebar');
		var togglebar 	= $('.mo-sidebar-toggle');

		//Show / hide sidebar
		if(localStorage.mo_admin_sidebar_display){
			if(localStorage.mo_admin_sidebar_display == 'shown'){
				sidebar.removeClass(show_class);
			}
			else{
				sidebar.addClass(show_class);
			}
			on_sidebar_toggle();
		}

		togglebar.on('click',function(){
			sidebar.toggleClass(show_class);
			on_sidebar_toggle();
		})

		function on_sidebar_toggle(){
			if(sidebar.hasClass(show_class)){
				togglebar.text('Show');
				var display = "hidden";
			}else{
				togglebar.text('Hide');
				var display = "shown";
			}
			localStorage.setItem("mo_admin_sidebar_display",display);
		}
	});


	//Media

	function renderMediaUploader(upload_btn) {
	 
	    var file_frame, image_data;
	 
	    /**
	     * If an instance of file_frame already exists, then we can open it
	     * rather than creating a new instance.
	     */
	    if ( undefined !== file_frame ) {
	 
	        file_frame.open();
	        return;
	 
	    }
	 
	    /**
	     * If we're this far, then an instance does not exist, so we need to
	     * create our own.
	     *
	     * Here, use the wp.media library to define the settings of the Media
	     * Uploader. We're opting to use the 'post' frame which is a template
	     * defined in WordPress core and are initializing the file frame
	     * with the 'insert' state.
	     *
	     * We're also not allowing the user to select more than one image.
	     */
	    file_frame = wp.media.frames.file_frame = wp.media({
	        frame:    'post',
	        state:    'insert',
	        multiple: false
	    });
	 
	    /**
	     * Setup an event handler for what to do when an image has been
	     * selected.
	     *
	     * Since we're using the 'view' state when initializing
	     * the file_frame, we need to make sure that the handler is attached
	     * to the insert event.
	     */
	    file_frame.on( 'insert', function() {
	 	
	        // Read the JSON data returned from the Media Uploader
   		 	var json = file_frame.state().get( 'selection' ).first().toJSON();

   		 	upload_btn.siblings('.mo-upload-url').val(json.url);
   		 	upload_btn.siblings('.mo-upload-title').html(json.filename);
   		
	 
	    });
	 
	    // Now display the actual file_frame
	    file_frame.open();
 
	}





	
    $( '.mo-upload-icon' ).on( 'click', function( evt ) {
        // Stop the anchor's default behavior
        evt.preventDefault();

        // Display the media uploader
        renderMediaUploader($(this));

    });
 
   


    //Get media uploaded name
	$('.mo-upload-url').each(function(){
		var media_url = $(this).val();
		if(!media_url) return true; // Skip to next if no value is set

		var index = media_url.lastIndexOf('/') + 1;
		var media_name = media_url.substr(index);

		$(this).siblings('.mo-upload-title').html(media_name);
	})


	//Remove uploaded file
	$('.mo-remove-media').on('click',function(){
		$(this).siblings('.mo-upload-url').val('');
		$(this).siblings('.mo-upload-title').html('');
	})


	//Hide defaul field if set to geoolocation
	$('select[name="oelm-phone-options[r-default-country-code-type]"]').on( 'change', function(){
		var $cc = $('select[name="oelm-phone-options[r-default-country-code]"').closest('tr');
		$(this).val() === 'custom' ? $cc.show() : $cc.hide();
	} ).trigger('change');


	$('body').on( 'click' , 'a.oelm-sdk-dwnld',function(){

		var operator 	= $(this).closest('li').data('operator'),
			$noticeEl 	= $('.oelm-notice');

		$noticeEl.html('Downloading..please wait..').show();

		$.ajax({
			url: oelm_admin_localize.adminurl,
			type: 'POST',
			data: {
				'action'		: 'download_operator_sdk',
				'operator'		: operator,
				'download_again': $(this).data('again')
			},
			success: function(response){
				if( response.notice ){
					$noticeEl.html(response.notice).show();
				}
			}
		});

		$(this).data('again','');

	})

	$('body').on('click', '.oelm-sdk-dwnld-again', function(){
		$('ul.oelm-opt-links').find('li:visible').find('a.oelm-sdk-dwnld').data('again','yes').trigger('click');
	})


	$('select[name="oelm-phone-options[m-operator]"]').on( 'change', function(){
		var $linksEl = $( '.oelm-opt-links' );
		$linksEl.find( 'li' ).hide();
		$linksEl.find( 'li[data-operator="'+ $(this).val() +'"]' ).show();
		$('.oelm-notice').hide();
	} ).trigger('change');

});
