jQuery(document).ready(function ($) {

    /******************** edited at 22-7 end here *****************/
	var starSelectionErrorMessage = 'Please select atleast one.';

	$('.form-item-custom-criteria-label').each(function ($) {
		var label_id = jQuery(this).attr('data-label-id');

		/* If this is not the first criteria textfield, let's make it hidden. */
		if (label_id != 0) {
			jQuery(this).hide();
			jQuery('.custom-criteria-wrapper-label-id-' + label_id).hide();
		}

		/*
		 * And hiding all the "star" divs. Will show them onKeyPress
		 * to the corresponding criteria textfield.
		 */
		jQuery('.label-star-id-' + label_id).hide();

		/*
		 * Just as said above. Showing the "star" div.
		 * Checkout this great solution for browser autocomplete
		 * issue: http://stackoverflow.com/a/6112637/1337075
		 */
		jQuery('#edit-custom-criteria-label-' + label_id).bind('input', function ($) {
			jQuery('.label-star-id-' + label_id).show();

			if (jQuery(this).hasClass('error')) {
				jQuery(this).removeClass('error');
			}
		});

		/* Clicking on the "Star Title Label", allowing user/admin to edit the "Title". */
		jQuery('.label-' + label_id + '-option-star').each(function ($) {
			jQuery(this).click(function () {
				var star_id = jQuery(this).attr('data-star-id');
				jQuery(this).hide();
				jQuery('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).show();
				jQuery('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).focus();
				//jQuery('#edit-custom-criteria-enable-star-'+star_id).attr('checked','checked');
			});
		});

		/*
		 * After completion of editing when user will click outside of the editing textbox,
		 * hiding the field and showing the "Star Title Label".
		 */
		jQuery('.edit-custom-criteria-label-text-' + label_id + '-star').each(function ($) {

            jQuery(this).blur(function () {
				if (jQuery('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).hasClass('error')) {
					jQuery('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).removeClass('error');
				}
				var star_id = jQuery(this).attr('data-star-id');
				var val = jQuery(this).val();

				if (val.length != 0) {
					if (jQuery('.edit-custom-criteria-label-hidden-' + label_id + '-star-' + star_id)) {
						jQuery('.edit-custom-criteria-label-hidden-' + label_id + '-star-' + star_id).remove();
					}
					var hiddenTitle = '<input data-label-id="' + label_id + '" data-star-id="' + star_id + '" type="hidden" id="edit-custom-criteria-hidden-star-' + star_id + '" name="ratingForm[custom_criteria][' + label_id + '][stars][' + star_id + '][titleHidden]" value="' + val + '" class="form-text disable_field edit-custom-criteria-label-hidden-' + label_id + '-star edit-custom-criteria-label-hidden-' + label_id + '-star-' + star_id + '">';
					jQuery(this).parent().append(hiddenTitle);
					jQuery(this).hide();
					jQuery('.label-' + label_id + '-option-star-' + star_id).text(val);
					jQuery('.label-' + label_id + '-option-star-' + star_id).show();
					jQuery('#edit-custom-criteria-enable-label-' + label_id + '-star-' + star_id).attr('checked', 'checked');
				} else {
					jQuery('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).addClass('error');
				}
			});
		});

	});

	/* Clicking on the "Question Title Label", allowing user/admin to edit the "Question". */
	jQuery('div.form-item-custom-question .question-label').each(function ($) {

        jQuery(this).click(function () {

            var q_id = jQuery(this).attr('data-q-id');
            //console.log(q_id);
			jQuery(this).hide();
			jQuery('input#edit-custom-question-text-q-' + q_id).show();
			jQuery('input#edit-custom-question-text-q-' + q_id).focus();
			//jQuery('#edit-custom-criteria-enable-star-'+star_id).attr('checked','checked');
		});
	});

	/*
	 * After completion of editing when user will click outside of the editing textbox,
	 * hiding the field and showing the "Question Title Label".
	 */
	jQuery('div.form-item-custom-question').each(function ($) {
		var q_id = jQuery(this).attr('data-q-id');

		jQuery('input#edit-custom-question-text-q-' + q_id).blur(function ($) {
			if (jQuery(this).hasClass('error')) {
				jQuery(this).removeClass('error');
			}
			var val = jQuery(this).val();

			if (val.length != 0) {
				jQuery('label.label-q-' + q_id).text(val);
				jQuery('label.label-q-' + q_id).show();
				jQuery(this).hide();
				jQuery('input#edit-custom-question-enable-q-' + q_id).attr('checked', 'checked');
				jQuery('div.form-item-custom-question-required-q-id-' + q_id).removeClass('disable_field');
			} else {
				jQuery(this).addClass('error');
			}
		});

		jQuery('input#edit-custom-question-enable-q-' + q_id).each(function ($) {
			if (jQuery(this).is(':checked')) {
				jQuery('div.form-item-custom-question-required-q-id-' + q_id).removeClass('disable_field');
			}

			jQuery(this).click(function ($) {
				jQuery('div.form-item-custom-question-required-q-id-' + q_id).toggleClass('disable_field');
			});
		});
	});

	jQuery('.add_more_criteria_link').click(function ($) {

		var showing_label_id = jQuery(this).attr('data-showing-label-id');
		var label_id = (showing_label_id - 1);
		var label_text_field_selctor = '#edit-custom-criteria-label-' + label_id;
		var label_text_field_value = jQuery(label_text_field_selctor).val();

		var starChecked = 0;
		jQuery('.label-star-id-' + label_id + ' input[type="checkbox"]').each(function () {
			if (jQuery(this).is(':checked')) {
				starChecked++;
			}
		});

		if ((starChecked < 1)) {
			if ((label_text_field_value.length == 0)) {
				jQuery(label_text_field_selctor).addClass('error');
			}
			if (jQuery('.label-star-id-' + label_id).is(':visible')) {
				jQuery('.label-star-id-' + label_id).addClass('error');
				jQuery('.label-star-id-' + label_id).attr('title', starSelectionErrorMessage);
			}
		} else {
			if (jQuery(label_text_field_selctor).hasClass('error')) {
				jQuery(label_text_field_selctor).removeClass('error');
			}
			if (jQuery('.label-star-id-' + label_id).hasClass('error')) {
				jQuery('.label-star-id-' + label_id).removeClass('error');

				if (jQuery('.label-star-id-' + label_id).attr('title') == starSelectionErrorMessage) {
					jQuery('.label-star-id-' + label_id).attr('title', '');
				}
			}

			jQuery('.custom-criteria-wrapper-label-id-' + showing_label_id).show();
			jQuery('.label-id-' + showing_label_id).show();
			jQuery(this).hide();
		}
	});

	jQuery('.display_item').show();
//});

//jQuery(document).ready(function($) {

	/*
	 * Code for read more feture for comment section at admin.
	 */

	$('.js_read_link').click(function () {
		var readMoreText = $(this).html();

		$('.read_more_span').toggleClass('disable_field');
		$(this).toggleClass('read_more');

		if (!$(this).hasClass('read_more')) {
			$(this).html(' ...Less');
		} else {
			$(this).html(' ...More');
		}
	});

	//invertText();
	//console.log(ratingFormEnableDisableStateText);


	/*
	 * Code for AJAXified Enable/Disable of the form at rating form listing page at admin.
	 */

	$('.cbrp-form-listing .enable_disable_click').each(function () {
		var ratingFormId = $(this).attr('data-form-id');

		$(this).click(function () {
			var is_active = $(this).attr('data-form-status');
			var nonce = $('#cb_form_ajax_nonce_action_name').val();

			var cbRatingFormEnableData = {};
			cbRatingFormEnableData['ratingFormId'] = ratingFormId;
			cbRatingFormEnableData['is_active'] = is_active;
			cbRatingFormEnableData['nonce'] = nonce;

			//console.log(cbRatingFormEnableData);

			$.ajax({
				type   : 'POST',
				url    : ajaxurl,
				data   : {
					action                : 'cbAdminRatingFormListingAjaxFunction',
					cbRatingFormEnableData: cbRatingFormEnableData
				},
				success: function (data, textStatus, XMLHttpRequest) {
					//console.log(data);
					if (data) {
						var parsed = $.parseJSON(data);

						if (parsed) {
							$('.enable_disable_click_form_' + ratingFormId).empty().text(parsed.text);
							$('.enable_disable_click_form_' + ratingFormId).attr('data-form-status', parsed.is_active);
							$('.enable_disable_click_form_' + ratingFormId).toggleClass('disable');
							$('.enable_disable_click_form_' + ratingFormId).toggleClass('enable');

							//invertText();
						}
					}

				},
				error  : function (MLHttpRequest, textStatus, errorThrown) {
					alert(errorThrown);
				}
			});
		});

		$('.enable_disable_click_form_' + ratingFormId).hover(
			function () {
				var state = $(this).attr('data-form-status');

				$(this).text(ratingFormEnableDisableStateText[state + '_hover']);
			},
			function () {
				var state = $(this).attr('data-form-status');

				$(this).text(ratingFormEnableDisableStateText[state + '_normal']);
			}
		);
	});


	/*
	 * RatingForm Edit page: Custom Question: field type selection.
	 */
	$('.question_field_type').each(function () {
		var q_id = jQuery(this).attr('data-q-id');

		$(this).change(function () {
			var val = $(this).val();

			$('.seperated_checkbox_div_q_id-' + q_id).each(function () {
				jQuery(this).addClass('disable_field');
			});
			if ((val == 'checkbox')) {
				$('.seperated_field_' + val + '_div_q_id-' + q_id).removeClass('disable_field');
				$('.form_item_q_id-' + q_id).addClass('disable_field');
				$('.form_item_' + val + '_q_id-' + q_id).removeClass('disable_field');
			} else {
				//$('.seperated_field_'+val+'_div_q_id-'+q_id).removeClass('disable_field');
				$('.form_item_q_id-' + q_id).addClass('disable_field');

				if ((val != 'none')) {
					$('.form_item_' + val + '_q_id-' + q_id).removeClass('disable_field');
				}
			}
		});

		var val = $(this).val();

		$('.seperated_checkbox_div_q_id-' + q_id).each(function () {
			jQuery(this).addClass('disable_field');
		});
		if ((val == 'checkbox')) {
			$('.seperated_field_' + val + '_div_q_id-' + q_id).removeClass('disable_field');
			$('.form_item_q_id-' + q_id).addClass('disable_field');
			$('.form_item_' + val + '_q_id-' + q_id).removeClass('disable_field');
		} else {
			//$('.seperated_field_'+val+'_div_q_id-'+q_id).removeClass('disable_field');
			$('.form_item_q_id-' + q_id).addClass('disable_field');

			if ((val != 'none')) {
				$('.form_item_' + val + '_q_id-' + q_id).removeClass('disable_field');
			}
		}
	});

	/*
	 *  If "Seperated" is not selected then disable the edit option and show the main question.
	 */
	$('.seperated_checkbox_input').each(function () {

		var q_id          = jQuery(this).attr('data-q-id');
		var val           = jQuery('.question_field_type_q_id-' + q_id).val();
		var field_id      = 0;   // As the checkbox is only with the main question, So we don't need to show multiple checkbox
		var questionLabel = jQuery('input#edit-custom-question-text-q-' + q_id).val().substring(0, 10);
		var seperated     = jQuery('.seperated_checkbox_input_q_id-' + q_id).val();
		var totalFieldCount  = (jQuery('.select_' + val + '_count_q_id-' + q_id + ' option:last-child').val());
		var curentFieldCount = (jQuery('.select_' + val + '_count_q_id-' + q_id + '  :selected').val());

		//console.log(seperated);

		jQuery(this).change(function () {

			var curentFieldCount = (jQuery('.select_' + val + '_count_q_id-' + q_id + '  :selected').val());

			if (jQuery(this).val() == 0) {

				jQuery('.select_checkbox_count_div_q_id-' + q_id).hide();

				for (var j = 0; totalFieldCount > j; j++) {

					jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + j).hide();
					//jQuery('.field_display_'+val+'_div_q_id-'+q_id+'_field_id-'+j).addClass('disable_field');
				}
				jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-0').show();
				jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-0 label').show();
				jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-0 input#edit-custom-question-' + val + '-field-text-0-q-' + q_id).hide();

				jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('labeleditable', 'false');
				jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('title', '');
				jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).empty().text(questionLabel + '...');

				jQuery('input#edit-custom-question-' + val + '-field-text-' + field_id + '-q-' + q_id).attr('labeleditable', 'false');

			} else if (jQuery(this).val() == 1) {

				jQuery('.select_checkbox_count_div_q_id-' + q_id).show();
				jQuery('.select_checkbox_count_q_id-' + q_id).removeClass('disable_field');

				for (var j = 0; curentFieldCount > j; j++) {

					jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + j).show();
					jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + j).removeClass('disable_field');
				}

				jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('labeleditable', 'true');
				jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('title', 'Click to Edit');

				jQuery('input#edit-custom-question-' + val + '-field-text-' + field_id + '-q-' + q_id).attr('labeleditable', 'true');
				var seperatedQLabel = jQuery('input#edit-custom-question-' + val + '-field-text-' + field_id + '-q-' + q_id).val();
				jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).empty().text(seperatedQLabel);
			}
		});

		var curentFieldCount = (jQuery('.select_' + val + '_count_q_id-' + q_id + '  :selected').val());

		var currentChkItemName = jQuery(this).attr('name');
		var currentChkItemVal = jQuery('input[name="' + currentChkItemName + '"]:checked').val();

		//console.log(currentChkItemVal, curentFieldCount, q_id);

		if (currentChkItemVal == 0) {

			jQuery('.select_checkbox_count_div_q_id-' + q_id).hide();

			for (var j = 0; totalFieldCount > j; j++) {

				jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + j).hide();
				//jQuery('.field_display_'+val+'_div_q_id-'+q_id+'_field_id-'+j).addClass('disable_field');
			}

			jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-0').show();
			jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-0 label').show();
			jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-0 input#edit-custom-question-' + val + '-field-text-0-q-' + q_id).hide();

			jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('labeleditable', 'false');
			jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('title', '');
			jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).empty().text(questionLabel + '...');

			jQuery('input#edit-custom-question-' + val + '-field-text-' + field_id + '-q-' + q_id).attr('labeleditable', 'false');

		} else if (jQuery(this).val() == 1) {

			jQuery('.select_checkbox_count_div_q_id-' + q_id).show();
			jQuery('.select_checkbox_count_q_id-' + q_id).removeClass('disable_field');
			for (var j = 0; curentFieldCount > j; j++) {

				jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + j).show();
				jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + j).removeClass('disable_field');
			}

			jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('labeleditable', 'true');
			jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('title', 'Click to Edit');

			jQuery('input#edit-custom-question-' + val + '-field-text-' + field_id + '-q-' + q_id).attr('labeleditable', 'true');
			var seperatedQLabel = jQuery('input#edit-custom-question-' + val + '-field-text-' + field_id + '-q-' + q_id).val();
			jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).empty().text(seperatedQLabel);
		}


	});


	/* Clicking on the "Question Field Title Label", allowing user/admin to edit the "Question Field Title". */
	jQuery('.question-field-label').each(function ($) {

		jQuery(this).click(function () {
            //console.log('iamhere');
			var q_id = jQuery(this).attr('data-q-id');
			var val = jQuery('.question_field_type_q_id-' + q_id).val();
			var field_id = jQuery(this).attr('data-' + val + '-field-text-id');
			var labelEditable = jQuery(this).attr('labeleditable');


			if ((val == 'checkbox') || (val == 'radio')) {
                //console.log(labelEditable);
				if (labelEditable == 'true') {
					jQuery(this).hide();
					jQuery('input#edit-custom-question-' + val + '-field-text-' + field_id + '-q-' + q_id).show().removeClass('disable_field').focus();
				}
			}
		});
	});

	/*
	 * After completion of editing when user will click outside of the editing textbox,
	 * hiding the field and showing the "Question Title Label".
	 */
	jQuery('.edit_field_display_input_label').each(function () {
		//console.log(jQuery(this).val());
		jQuery(this).blur(function ($) {

			var labelEditable = jQuery(this).attr('labeleditable');
			if (labelEditable == 'true') {

				var q_id = jQuery(this).attr('data-q-id');
				var val = jQuery('.question_field_type_q_id-' + q_id).val();
				var field_id = jQuery(this).attr('data-' + val + '-field-text-id');
				console.log(val);
				//console.log('input#edit-custom-question-'+val+'-field-text-'+field_id+'-q-'+q_id);

				if ((val == 'checkbox') || (val == 'radio')) {

					if (jQuery(this).hasClass('error')) {
						jQuery(this).removeClass('error');
					}
					var inputVal = jQuery(this).val();
					//console.log(inputVal + ' (Click to edit)');
					if (inputVal.length != 0) {
						jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).text(inputVal);
						jQuery('label.label-q-' + q_id + '-' + val + '-' + field_id).show();
						jQuery(this).hide();
						//jQuery('input#edit-custom-question-enable-q-'+q_id).attr('checked','checked');
						//jQuery('div.form-item-custom-question-required-q-id-'+q_id).removeClass('disable_field');
					} else {
						jQuery(this).addClass('error');
					}
				}
			}
		});
	});

	jQuery('.select_count').each(function () {
		jQuery(this).change(function () {
			var q_id = jQuery(this).attr('data-q-id');
			var val = jQuery('.question_field_type_q_id-' + q_id).val();
			//var field_id = jQuery(this).attr('data-'+val+'-field-text-id');
			var selectInputVal = jQuery(this).val();

			jQuery('.field_display_' + val + '_div_q_id-' + q_id).each(function () {
				jQuery(this).hide();
			})
			for (var i = 0; selectInputVal > i; i++) {
				jQuery('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + i).show();
			}
		});
	});

    // comment edit
    $('.cbcomment_edit').hide();
    $('.cbcomment_editbox_noedit').hide();
    $('.cbcomment').each(function(){
        jQuery(this).click(function () {
            var cb_id   =    jQuery(this).attr('data-id');
            var cb_text =    jQuery(this).text();
            $('.cbcomment_edit_'+cb_id).val($.trim(cb_text));
            $('.cbcomment_edit').hide();
            jQuery(this).hide();
            $('.cbcomment_edit_'+cb_id).show();
            $('.cbcomment_edit_'+cb_id).focus();

        });
    });

    $('.comment_wrapper').each(function(){
        jQuery(this).hover(function () {
                var _this = jQuery(this).children('p.comment_status_column');
                $(_this).children('.comment_status').show();
                var check_unverified = $(_this).children('.cb-comment-label').text();
                if(check_unverified == 'unverified'){

                    $(_this).children('.unapproved').hide();
                    $(_this).children('.approved').hide();
                }
                var data_status = $(_this).attr('data-status');
                $(_this).children('.'+data_status).hide();

            },
            function(){
                var _this = jQuery(this).parent('div').next('p');
                $(_this).children('.comment_status').hide();

            });
    });
    $('.cbcomment_edit').each(function(e){

        $(this).blur(function(e){
            var _this = jQuery(this);
            e.preventDefault();
            var cbRatingData = {} ;
            cbRatingData['id']                              = jQuery(this).attr('data-id');
            cbRatingData['form_id']                         = jQuery(this).attr('data-form-id');
            cbRatingData['post_id']                         = jQuery(this).attr('data-post-id');
            cbRatingData['cb_text']                         = jQuery(this).val();
            $.ajax({
                type   : 'POST',
                url    : commentAjax.ajaxUrl,
                timeout: 20000,     // For 20 secs.
                data   : {
                    action      : 'cbCommentEditAjaxFunction',
                    cbRatingData: cbRatingData
                },
                success: function (data, textStatus, XMLHttpRequest) {
                    var _cb_text = jQuery(_this).val();
                    jQuery(_this).hide();
                    $('.comment_'+ cbRatingData['id'] ).show();
                    $('.comment_'+ cbRatingData['id'] ).text(_cb_text);
                }
            });
        });
    });

    ///comment_status
    $('.comment_status').hide();
    $('.comment_status_column').hover(function(){
            $(this).children('.comment_status').show();
            var check_unverified = $(this).children('.cb-comment-label').text();
            if(check_unverified == 'unverified'){
                $(this).children('.unapproved').hide();
                $(this).children('.approved').hide();
            }
            var data_status = $(this).attr('data-status');
            $(this).children('.'+data_status).hide();

        },
        function(){

            $(this).children('.comment_status').hide();
        });

    // fixing cooment tr color according to status

    $('.cb-spam').css('color','#aa1100');
    $('.cb-approved').css('color','green');
    $('.cb-unapproved').css('color','#dd0000');
    $('.cb-delete').css('color','#000000');
    $('p[data-status ="approved"]').parents('tr').addClass('cb-tr-approved');
    $('p[data-status ="approved"]').parents('tr').children('th').addClass('cb-td-approved');
    $('p[data-status ="unapproved"]').parents('tr').addClass('cb-tr-unapproved');
    $('p[data-status ="unapproved"]').parents('tr').children('th').addClass('cb-td-unapproved');
    $('p[data-status ="spam"]').parents('tr').addClass('cb-tr-spam');
    $('p[data-status ="spam"]').parents('tr').children('th').addClass('cb-td-spam');
    $('.comment_wrapper span.comment_status:not(:last-child)').append(' | ');

    //  chabging comment atatus
    $('.comment_status').click(function(e){

        var _this = jQuery(this);
        e.preventDefault();
        var cbRatingData = {} ;
        cbRatingData['id']                  = jQuery(this).attr('data-id');
        cbRatingData['form_id']             = jQuery(this).attr('data-form-id');
        cbRatingData['post_id']             = jQuery(this).attr('data-post-id');
        cbRatingData['comment_status']      = jQuery(this).attr('data-comment-status');

        $.ajax({
            type   : 'POST',
            url    : commentAjax.ajaxUrl,
            timeout: 20000,     // For 20 secs.
            data   : {
                action      : 'cbCommentAjaxFunction',
                cbRatingData: cbRatingData
            },
            success: function (data, textStatus, XMLHttpRequest) {
                //jQuery(_this).parent().append('<p><span style="padding: 3px;">'+cbRatingData['comment_status'].charAt(0).toUpperCase() + cbRatingData['comment_status'].slice(1)+' saved</span><span class="remove_cb_msg">x</span></p>');
                jQuery('.remove_cb_msg').click(function(){
                    jQuery(this).parent().remove();
                });

                if( cbRatingData['comment_status'] == "approved"){
                    $(_this).parents('td').parent('tr').removeClass().addClass('cb-tr-approved');
                    $(_this).parents('td').parent('tr').children().first().removeClass().addClass('cb-td-approved').addClass('check-column');
                    $(_this).parent('p.comment_status_column').attr('data-status','approved');
                }
                else if(cbRatingData['comment_status'] == "unapproved"){
                    $(_this).parents('td').parent('tr').removeClass().addClass('cb-tr-unapproved');
                    $(_this).parents('td').parent('tr').children().first().removeClass().addClass('cb-td-unapproved').addClass('check-column');
                    $(_this).parent('p.comment_status_column').attr('data-status','unapproved');
                }
                else if(cbRatingData['comment_status'] == "spam"){

                    $(_this).parents('td').parent('tr').removeClass().addClass('cb-tr-spam');
                    $(_this).parents('td').parent('tr').children().first().removeClass().addClass('cb-td-spam').addClass('check-column');
                    $(_this).parent('p.comment_status_column').attr('data-status','spam');
                }

                $(_this).siblings().show();
                $(_this).hide();

                if(cbRatingData['comment_status'] == "delete"){
                    $(_this).parents('td').parent('tr').hide();
                }

            }
        });
    }); // end of comment status click
});// end of dom ready