mw.loader.using( 'ext.bluespice', function() {
	if ( $.cookie( 'bs-bluespiceprojectfeedbackhelperpanel-firstload' ) == null){
		$('#bs-bluespiceprojectfeedbackhelperpanel').delay( 1500 ).fadeIn( 'slow' );

		$.cookie( 'bs-bluespiceprojectfeedbackhelperpanel-firstload', 'false', {
				path: '/',
				expires: 7 // remind once a week
			});
	} else {
		$('#bs-bluespiceprojectfeedbackhelperpanel').show();
	}

	if ( $.cookie( 'bs-bluespiceprojectfeedbackhelperpanel-hide' ) == 'true' ){
		$('#bs-bluespiceprojectfeedbackhelperpanel').hide();
	} else{
		$('#bs-bluespiceprojectfeedbackhelperpanel-closebutton').click(function(){
			if( confirm( $(this).attr('data-confirm-msg') ) ) {
				$.ajax({
					dataType: "json",
					type: 'post',
					url: mw.util.wikiScript( 'api' ),
					data: {
						action: 'bs-bluespiceprojectfeedbackhelper',
						task: 'disableFeedback',
						format: 'json',
						token: mw.user.tokens.get('editToken', '')
					}
				});
			}
			$( '#bs-bluespiceprojectfeedbackhelperpanel' ).fadeOut( 'fast' );
			$.cookie( 'bs-bluespiceprojectfeedbackhelperpanel-hide', 'true', {
				path: '/',
				expires: 7 // remind once a week
			});
		});
	}
} );