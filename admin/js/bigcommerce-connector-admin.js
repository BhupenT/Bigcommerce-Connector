(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */


	$(document).ready(function() {

		let postCount,
			progressStep,
			processStatus,
			resourceErrors = [],
			progressValue;


		//Intialize progressbar


    	function ajaxSyncposts(postid) {
    		$.ajax({
                url: global_object.post_url,
                type: 'post',
                data: {
                    action: 'sync_posts',
                    postid: postid,
                    syncpost: global_object.syncpost
                },
                beforeSend: function() {
                    
                },
                complete: function(response) {
                	var syncStatus;
                	if(response.status == 200) {

                		syncStatus = 'Synched';
                		$('#progress-data').text(response.responseText);
                		var log = '<p>'+response.responseText+' <strong>status: '+syncStatus+'</strong></p>';
                		$('#sync-log').append(log);

                	}else if (response.status == 300) {

						syncStatus = 'Ajax error occurred';
						processStatus++;
						$('#progress-data').text('Ajax error');
						var log = '<p class="error">'+response.responseText+' <strong>status: '+syncStatus+'</strong></p>';
						$('#sync-log').append(log);

					}else if(response.status == 400) {

						syncStatus = 'API sync error';
						processStatus++;
						$('#progress-data').text('Sync error unknown');
						var log = '<p class="error">'+response.responseText+' could not be synched <strong>status: '+syncStatus+'</strong></p>';
						$('#sync-log').append(log);

					}else if(response.status == 404) {

						syncStatus = 'Resource not found';
						processStatus++;
						resourceErrors.push(postid);
						$('#progress-data').text('Sync error resource not found');
						var log = '<p class="error">'+response.responseText+' could not be synched <strong>status: '+syncStatus+'</strong></p>';
						$('#sync-log').append(log);

					}else{

                		syncStatus = 'Unknown error';
                		processStatus++;
                		$('#progress-data').text('WP Sync Error');
                		var log = '<p class="error">WP error while proccessing '+'<strong>'+postid+' status: '+syncStatus+'</strong></p>';
                		$('#sync-log').append(log);

                	}

	                var value = $('#progressbar').progressbar( "option", "value" );
	                if(value === false) {
	                	value = 0
	                }
	                progressValue = (Number(value)+Number(progressStep)).toFixed(2);
	                //console.log(progressValue);
	                
	                $( "#progressbar" ).progressbar( "option", "value", Number(progressValue));

                }

            });
    	}

		$(document).on('click', '#sync-now', function() {
			if (confirm('Are you sure you want to Sync all pages and post?')) {

				var button = $(this);
				$( "#progressbar" ).progressbar({
		    		value: false,
		    		change: function() {
		    			if($('#progressbar').progressbar('value') !== false) {
		    				$('.progress-status').text( $('#progressbar').progressbar('value') + '%');
		    			}
		    		},
		    		complete: function() {
		    			if(processStatus == 0) {
		    				var errorStatus = '';
		    			}else{
		    				var errorStatus = '- '+processStatus+' errors';
		    			}
		    			$('.progress-status').text('Synching completed '+errorStatus);
		    			$('.connector .progress-label').hide();
		    			$('#sync-log').slideDown(600);
		    			$('html, body').animate({
			                scrollTop: ($('#sync-log').offset().top)-20
			            }, 800);
		    			$('#sync-now').prop('disabled', false);
		    			console.log(resourceErrors);
		    			if(resourceErrors.length) {
		    				$('#Fixerrors').show();
		    			}

		    		}
		    	});

                $.ajax({
                    url: global_object.post_url,
                    type: 'post',
                    data: {
                        action: 'fetch_posts',
                        security: global_object.security,
                    },
                    beforeSend: function() {
                    	button.attr('disabled', 'disabled');
                        $('.connector .progress-container').show();
                        $('.connector .progress-label').show();
                        $('#sync-log').text('');
                        $('#sync-log').slideUp(600);
                        processStatus = 0;
                    },
                    complete: function(response) {
                    	if(response.status == 200) {
                    	
                    		response = $.parseJSON(response.responseText);
                    		postCount = response.length;
	                    	progressStep = (100/postCount);
	                    	//console.log(response);
	                    	for (var id in response ) {
	                			ajaxSyncposts(response[id]);
	                    	}
	                    }else{
	                    	button.prop('disabled', false);
	                    	$('#progress-data').text('sync error, please try refreshing the page and try again');
	                    }
                    },


                });
            }

            return false;

		});


		$(document).on('click', '#Fixerrors', function() {
			console.log(resourceErrors);
			var button = $(this);
			if(resourceErrors.length) {
				if (confirm('Please confirm that you wish to clear the sync data that had been synched previously.'+
					'Note: this means that you had error on synching due to resource not found in the Bigcommerce side and this step '+
					'is neccessary to do so. Thanks')) {
					$.ajax({
	                    url: global_object.post_url,
	                    type: 'post',
	                    data: {
	                        action: 'cleanup_postmetas',
	                        postids: JSON.stringify(resourceErrors),
	                        cleanup: global_object.cleanup,
	                    },
	                    beforeSend: function() {
	                    	button.prop('disabled', true);
	                    },
	                    complete: function(response) {
	                    	if(response.status == 200) {
	                    		resourceErrors = [];
	                    		alert('all the post that were synched and not found in the Bigcommerce has been reseted.'+
	                    			'Please sync again if you wish to create a new page for those that had errors synching.');
	                    		button.fadeOut(700);
	                    		$('.ui-progressbar .ui-progressbar-value').addClass('error-fixed');
	                    		$('.progress-status').text('Synching completed and '+processStatus+' errors'+' has been Fixed.');
	                    		setTimeout(function() {
	                    			$('#sync-log').slideUp(600);
	                    		}, 2000);


		                    }else{

		                    	$('#progress-data').text('Error, please try refreshing the page and try again');
		                    }
	                    },


	                });
	            }
			}
			return false;

		})

	});
	

})( jQuery );