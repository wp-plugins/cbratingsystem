jQuery(document).ready(function ($) {

    var q_title     = 'Sample Question Title';
    var click_note  = 'Click to edit';

    //form setting page
    // Switches option sections
    $('.ratingtabgroup').hide();
    var cbratingactivetab = '';

    if (cbratingactivetab != '' && $(cbratingactivetab).length ) {
        //console.log('reset first one');
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(cbratingactivetab).fadeIn();
    } else {
        $('.ratingtabgroup:first').fadeIn();
    }


    $('.ratingtabgroup .collapsed').each(function(){
        $(this).find('input:checked').parent().parent().parent().nextAll().each(
            function(){
                if ($(this).hasClass('last')) {
                    $(this).removeClass('hidden');
                    return false;
                }
                $(this).filter('.hidden').removeClass('hidden');
            });
    });

    if (cbratingactivetab != '' && $(cbratingactivetab + '-tab').length ) {
        $(cbratingactivetab + '-tab').addClass('nav-tab-active');
    }
    else {
        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
    }

    $('.nav-tab-wrapper a').click(function(evt) {
        evt.preventDefault();

        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active').blur();
        var clicked_group = $(this).attr('href');
        $('.ratingtabgroup').hide();
        $(clicked_group).fadeIn();
    });
    //end form setting page


    //question js starts

    //question, show x number of radio or checkbox for question
    //ok
    // $('.select_count').each(function () {
    $('.edit-custom-question-fields-wrapper').on('change','select.select_count',function() {
        // $(this).change(function () {
        var $this   = $(this);
        var q_id    = $this.attr('data-q-id');
        var val     = $('.question_field_type_q_id-' + q_id).val();

        var selectInputVal = $this.val();

        $('.field_display_' + val + '_div_q_id-' + q_id).each(function () {

            //console.log($('.field_display_' + val + '_div_q_id-' + q_id)) ;

            $(this).hide();
        });

        for (var i = 0; selectInputVal > i; i++) {
            $('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + i).show();
        }
        //});
    });
    //ok

    //ok
    $('.edit-custom-question-fields-wrapper').on('change','select.question_field_type',function(){

        var fieldtype = $(this).val();  //variable name 'fieldtype' is better than 'val'
        var q_id = $(this).attr('data-q-id');
        //console.log($(this).next('.seperated_checkbox_div_q_id-' + q_id));

        //disable the 4th col all fields
        $('.form_item_text_q_id-' + q_id).addClass('disable_field');
        $('.form_item_checkbox_q_id-' + q_id).addClass('disable_field');
        $('.form_item_radio_q_id-' + q_id).addClass('disable_field');

        //except selected field type
        $('.form_item_' + fieldtype + '_q_id-' + q_id).removeClass('disable_field');

        //now disable seperated box if not checkbox
        //seperated_checkbox_div seperated_checkbox_div_q_id-0 seperated_field_checkbox_div_q_id-0 disable_field
        if(fieldtype == 'checkbox'){
            $('.seperated_field_checkbox_div_q_id-'+q_id).removeClass('disable_field');
        }
        else{
            $('.seperated_field_checkbox_div_q_id-'+q_id).addClass('disable_field');
        }

    });
    //ok


    //question-label label-q-0 option mouse_normal
    //edit-custom-question-text-q edit-custom-question-text-q-0 disable_field
        //edit-custom-question-text-q-0

    //question-field-label question-field-radio-label label-q-0-radio-0 option mouse_normal
    //form-text edit_field_display_input_label disable_field edit-custom-question-field-radio-q-id-0 edit-custom-question-field-radio-0-label-text-q-0
        //edit-custom-question-checkbox-field-text-0-q-0


    //click label.question-label
    //blue input.edit-custom-question-text-q

    //click label.question-field-label
    //blur input.edit_field_display_input_label

    //ok
	//make the label editable for first column
	//$('div.form-item-custom-question .question-label').each(function (e) {
    //$('.edit-custom-criteria-fields-wrapper').on('click','label.form-item-custom-question-label',function() {

    $('.edit-custom-question-fields-wrapper').on('click','label.question-label',function() {

        //$(this).click(function () {

        //console.log('clicked');


            var q_id = $(this).attr('data-q-id');
            //console.log(q_id);
			$(this).hide();

			$('input#edit-custom-question-text-q-' + q_id).show();
			$('input#edit-custom-question-text-q-' + q_id).focus();
			//jQuery('#edit-custom-criteria-enable-star-'+star_id).attr('checked','checked');
		//});
	});

    //make the label editable for first column
    //ok

	/*
	 * After completion of editing when user will click outside of the editing textbox,
	 * hiding the field and showing the "Question Title Label".
	 */

    $('.edit-custom-question-fields-wrapper').on('blur','input.edit-custom-question-text-q',function() {
       // console.log($(this));



        var q_id = jQuery(this).attr('data-q-id');
        if ($(this).hasClass('error')) {
            $(this).removeClass('error');
        }
        var val = $(this).val(); //get the inline edit input field value

        if (val.length != 0) {
            //$('label.label-q-' + q_id).text(val); //show the edit text from input field back to label
            //$('label.label-q-' + q_id).show();

            $(this).hide();


            $('#question-label-' + q_id).text(val); //show the edit text from input field back to label
            $('#question-label-' + q_id).show();


            //$('input#edit-custom-question-enable-q-' + q_id).attr('checked', 'checked');
            //$('div.form-item-custom-question-required-q-id-' + q_id).removeClass('disable_field');
        } else {
            $(this).addClass('error');
        }

    });


    //ok
    //question: Clicking on the "Question Field Title Label", allowing user/admin to edit the "Question Field Title".
    //$('.question-field-label').each(function (e) {

    $('.edit-custom-question-fields-wrapper').on('click','label.question-field-label',function() {
        var $this = $(this);

        //console.log('i am here');
        var q_id            = $this.attr('data-q-id');
        var field_type      = $('.question_field_type_q_id-' + q_id).val();
        var field_id        = $this.attr('data-' + field_type + '-field-text-id');
        var labelEditable   = $this.attr('labeleditable');


        if ((field_type == 'checkbox') || (field_type == 'radio')) {
            //console.log(labelEditable);
            if (labelEditable == '1') {
                $this.hide();
                $('input#edit-custom-question-' + field_type + '-field-text-' + field_id + '-q-' + q_id).show().removeClass('disable_field').focus();
            }
        }

    }); //ok


    /*
     * After completion of editing when user will click outside of the editing textbox,
     * hiding the field and showing the "Question Title Label".
     */
    //ok
    //$('.edit_field_display_input_label').each(function () {

    $('.edit-custom-question-fields-wrapper').on('blur','input.edit_field_display_input_label',function() {

        //console.log('hellow');

        //console.log(jQuery(this).val());
        //$(this).blur(function () {

        var labelEditable = $(this).attr('labeleditable');
        if (labelEditable == '1') {

            var q_id        = $(this).attr('data-q-id');
            var field_type  = $('.question_field_type_q_id-' + q_id).val();//which type of input field is selected
            var field_id    = $(this).attr('data-' + field_type + '-field-text-id');
            //console.log(field_id);
            //console.log('input#edit-custom-question-'+val+'-field-text-'+field_id+'-q-'+q_id);

            if ((field_type == 'checkbox') || (field_type == 'radio')) {

                if ($(this).hasClass('error')) {
                    $(this).removeClass('error');
                }
                var inputVal = $(this).val();
                //console.log(inputVal + ' (Click to edit)');
                if (inputVal.length != 0) {
                    $('label.label-q-' + q_id + '-' + field_type + '-' + field_id).text(inputVal);
                    $('label.label-q-' + q_id + '-' + field_type + '-' + field_id).show();
                    $(this).hide();
                    //jQuery('input#edit-custom-question-enable-q-'+q_id).attr('checked','checked');
                    //jQuery('div.form-item-custom-question-required-q-id-'+q_id).removeClass('disable_field');
                } else {
                    $(this).addClass('error');
                }
            }
        }
        // });
    }); //ok should also work for newly loaded content



    /*
     *  If "Seperated" is not selected then disable the edit option and show the main question.
     */

    $('.edit-custom-question-fields-wrapper').on('change','input.seperated_checkbox_input',function() {
    //$('.seperated_checkbox_input').each(function () {
        //console.log('hi');
       //console.log($(this).val());


        var q_id                = $(this).attr('data-q-id');
        var field_type          = $('.question_field_type_q_id-' + q_id).val();

        //why field id is zero at first ?
        var field_id            = 0;   // As the checkbox is only with the main question, So we don't need to show multiple checkbox

        //main question label
        var questionLabel       = $('input#edit-custom-question-text-q-' + q_id).val().substring(0, 10);

        //var seperated           = $('.seperated_checkbox_input_q_id-' + q_id).val();
        var seperated           = $(this).val()
        //var totalFieldCount     = ($('.select_' + field_type + '_count_q_id-' + q_id + ' option:last-child').val());
        var totalFieldCount     = $('.select_' + field_type + '_count_q_id-' + q_id + ' option').length;

        //console.log(totalFieldCount);

        var curentFieldVal    = $('.select_' + field_type + '_count_q_id-' + q_id + '  :selected').val();

        //console.log(curentFieldVal);




        //var curentFieldVal = ($('.select_' + field_type + '_count_q_id-' + q_id + '  :selected').val());



        if ($(this).val() == 0 ) {
            //single checkbox mode

            //if single checkbox is selected then hide the count box for checkbox
            $('.select_checkbox_count_div_q_id-' + q_id).hide();

            for (var j = 0; totalFieldCount > j; j++) {
                $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-' + j).hide();
                //jQuery('.field_display_'+val+'_div_q_id-'+q_id+'_field_id-'+j).addClass('disable_field');
            }
            $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-0').show();
            $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-0 label').show();
            //$('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-0 input#edit-custom-question-' + field_type + '-field-text-0-q-' + q_id).hide();
            $('input#edit-custom-question-' + field_type + '-field-text-' + 0 + '-q-' + q_id).attr('labeleditable', '0');

            //$('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('labeleditable', '0');
            //$('label.label-q-' + q_id + '-' + val + '-' + field_id).attr('title', '');
            //$('label.label-q-' + q_id + '-' + val + '-' + field_id).empty().text(questionLabel + '...');

            //$('input#edit-custom-question-' + val + '-field-text-' + field_id + '-q-' + q_id).attr('labeleditable', '0');

        } else if ($(this).val() == 1) {
            //multiple mode

            //if single checkbox is selected then hide the count box for checkbox
            $('.select_checkbox_count_div_q_id-' + q_id).show(); //show select box wrapper

            $('.select_checkbox_count_q_id-' + q_id).removeClass('disable_field'); //show select box

            //single checkbox fields class "field_display_checkbox_div field_display_checkbox_div_q_id-X field_display_checkbox_div_q_id-X_field_id-X"
            for (var j = 0; j < totalFieldCount; j++) {
                if(j  < curentFieldVal){
                    //enable the checkbox wrapper
                    $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-' + j).show();
                    $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-' + j).removeClass('disable_field');
                }


                //make the checkbox label editable and title tip
                //question-field-label question-field-checkbox-label label-q-0-checkbox-0 option mouse_normal
                $('label.label-q-' + q_id + '-' + field_type + '-' + j).attr('labeleditable', '1');
                $('label.label-q-' + q_id + '-' + field_type + '-' + j).attr('title', click_note);

                $('input#edit-custom-question-' + field_type + '-field-text-' + j + '-q-' + q_id).attr('labeleditable', '1');
                var seperatedQLabel = $('input#edit-custom-question-' + field_type + '-field-text-' + j + '-q-' + q_id).val();
                //console.log(seperatedQLabel);
                $('label.label-q-' + q_id + '-' + field_type + '-' + j).empty().text(seperatedQLabel);
            }


            //id="edit-custom-question-checkbox-field-text-0-q-0"  class="form-text edit_field_display_input_label disable_field edit-custom-question-field-checkbox-q-id-0 edit-custom-question-field-checkbox-0-label-text-q-0">
            //$('input#edit-custom-question-' + field_type + '-field-text-' + field_id + '-q-' + q_id).attr('labeleditable', '1');
            //var seperatedQLabel = $('input#edit-custom-question-' + field_type + '-field-text-' + field_id + '-q-' + q_id).val();
           // $('label.label-q-' + q_id + '-' + field_type + '-' + field_id).empty().text(seperatedQLabel);
        }

    });

    //question end







    //criteria js actions

    //blur event label input, when user click outside of any label input event , focus lost for label input
    //this will also work on newly added inputs/ajax loaded labels
    $('.edit-custom-criteria-fields-wrapper').on('blur','input.edit-custom-criteria-label-text-star',function(e){

        var $this = $(this);

        var star_id     = $this.attr('data-star-id');
        var label_id    = $this.attr('data-label-id');

        if ($this.hasClass('error')) {
            $this.removeClass('error');
        }

        //var star_id = $(this).attr('data-star-id');

        var val = $this.val();

        if (val.length != 0) {
            /*
            if ($this) {
                $this.remove();
            }
            var hiddenTitle = '<input data-label-id="' + label_id + '" data-star-id="' + star_id + '" type="hidden" id="edit-custom-criteria-hidden-star-' + star_id + '" name="ratingForm[custom_criteria][' + label_id + '][stars][' + star_id + '][titleHidden]" value="' + val + '" class="form-text disable_field edit-custom-criteria-label-hidden-' + label_id + '-star edit-custom-criteria-label-hidden-' + label_id + '-star-' + star_id + '">';
            $this.parent().append(hiddenTitle);
            */

            $this.hide();

            $('.label-' + label_id + '-option-star-' + star_id).text(val);
            $('.label-' + label_id + '-option-star-' + star_id).show();
            $this.attr('checked', 'checked');
        } else {
            $this.addClass('error');
        }

    }); //

    //click on label, render input field for that label
    //this will also work on newly added labels/ajax loaded labels
    $('.edit-custom-criteria-fields-wrapper').on('click','label.custom-criteria-single-label',function(e){

        // Clicking on the "Star Title Label", allowing user/admin to edit the "Title".
       // $('.label-' + label_id + '-option-star').each(function (e) {
            //$(this).click(function () {
                var $this = $(this);
                $this.hide();  //hide label

                var star_id      = $this.attr('data-star-id');
                var label_id     = $this.attr('data-label-id');

                //console.log('it works'+star_id);
                //but show the input box
                $('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).show();
                $('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).focus();

                //we will not auto check them for just edit the label
                //jQuery('#edit-custom-criteria-enable-star-'+star_id).attr('checked','checked');
           // });
        //}); //need to enable again

    });


    //end //click on label, render input field for that label

    /******************** edited at 22-7 end here *****************/
    //custom criteria
    var starSelectionErrorMessage = 'Please select at least one.';



    //$('.form-item-custom-criteria-label').each(function (e) {
        /*
        var label_id = $(this).attr('data-label-id');

        // If this is not the first criteria textfield, let's make it hidden.
        if (label_id != 0) {
            $(this).hide();
            $('.custom-criteria-wrapper-label-id-' + label_id).hide();

        }
        */


        // And hiding all the "star" divs. Will show them onKeyPress to the corresponding criteria textfield.

        //$('.label-star-id-' + label_id).hide();

        /*
         * Just as said above. Showing the "star" div.
         * Checkout this great solution for browser autocomplete
         * issue: http://stackoverflow.com/a/6112637/1337075
         */
        /*
        $('#edit-custom-criteria-label-' + label_id).bind('input', function (e) {
            $('.label-star-id-' + label_id).show();
            if ($(this).hasClass('error')) {
                $(this).removeClass('error');
            }
        });
        */

        // this part is reproduced
        /*
        // Clicking on the "Star Title Label", allowing user/admin to edit the "Title".
        $('.label-' + label_id + '-option-star').each(function (e) {
            $(this).click(function () {
                var star_id = jQuery(this).attr('data-star-id');
                $(this).hide();
                $('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).show();
                $('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).focus();
                //jQuery('#edit-custom-criteria-enable-star-'+star_id).attr('checked','checked');
            });
        }); //need to enable again
        */
        // end this part is reproduced

        //criteria label edit input blur event, fires when user completes edit and click anywhere else in the
        /*
         * After completion of editing when user will click outside of the editing textbox,
         * hiding the field and showing the "Star Title Label".
         */
        /*
        $('.edit-custom-criteria-label-text-' + label_id + '-star').each(function (e) {
            $(this).blur(function () {
                if ($('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).hasClass('error')) {
                    $('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).removeClass('error');
                }
                var star_id = $(this).attr('data-star-id');
                var val = $(this).val();

                if (val.length != 0) {
                    if ($('.edit-custom-criteria-label-hidden-' + label_id + '-star-' + star_id)) {
                        $('.edit-custom-criteria-label-hidden-' + label_id + '-star-' + star_id).remove();
                    }
                    var hiddenTitle = '<input data-label-id="' + label_id + '" data-star-id="' + star_id + '" type="hidden" id="edit-custom-criteria-hidden-star-' + star_id + '" name="ratingForm[custom_criteria][' + label_id + '][stars][' + star_id + '][titleHidden]" value="' + val + '" class="form-text disable_field edit-custom-criteria-label-hidden-' + label_id + '-star edit-custom-criteria-label-hidden-' + label_id + '-star-' + star_id + '">';
                    $(this).parent().append(hiddenTitle);
                    $(this).hide();
                    $('.label-' + label_id + '-option-star-' + star_id).text(val);
                    $('.label-' + label_id + '-option-star-' + star_id).show();
                    $('#edit-custom-criteria-enable-label-' + label_id + '-star-' + star_id).attr('checked', 'checked');
                } else {
                    $('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).addClass('error');
                }
            });
        });
        */
         //need to enable again

    //});

    //add more criteria
    //add-new-criteria-total cb-add-new-link button  // this code is unused
    /*
    $('.add_more_criteria_link').click(function (e) {

        console.log('from core');

		var showing_label_id        = $(this).attr('data-showing-label-id');
		var label_id = (showing_label_id - 1);
		var label_text_field_selctor = '#edit-custom-criteria-label-' + label_id;
		var label_text_field_value = $(label_text_field_selctor).val();

		var starChecked = 0;
        $('.label-star-id-' + label_id + ' input[type="checkbox"]').each(function () {
			if ($(this).is(':checked')) {
				starChecked++;
			}
		});

		if ((starChecked < 1)) {
			if ((label_text_field_value.length == 0)) {
                $(label_text_field_selctor).addClass('error');
			}
			if ($('.label-star-id-' + label_id).is(':visible')) {
                $('.label-star-id-' + label_id).addClass('error');
                $('.label-star-id-' + label_id).attr('title', starSelectionErrorMessage);
			}
		} else {
			if ($(label_text_field_selctor).hasClass('error')) {
                $(label_text_field_selctor).removeClass('error');
			}
			if ($('.label-star-id-' + label_id).hasClass('error')) {
                $('.label-star-id-' + label_id).removeClass('error');

				if ($('.label-star-id-' + label_id).attr('title') == starSelectionErrorMessage) {
                    $('.label-star-id-' + label_id).attr('title', '');
				}
			}

            $('.custom-criteria-wrapper-label-id-' + showing_label_id).show();
            $('.label-id-' + showing_label_id).show();
            $(this).hide();
		}
	});
    */

    $('.display_item').show();




	/*
	 * Code for read more feature for comment section at admin.
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



    //comment related

    /*
    // comment edit
    $('.cbcomment_edit').hide();
    $('.cbcomment_editbox_noedit').hide();

    $('.cbcomment').each(function(){
        $(this).click(function () {
            var cb_id   =    jQuery(this).attr('data-id');
            var cb_text =    jQuery(this).text();
            $('.cbcomment_edit_'+cb_id).val($.trim(cb_text));
            $('.cbcomment_edit').hide();
            jQuery(this).hide();
            $('.cbcomment_edit_'+cb_id).show();
            $('.cbcomment_edit_'+cb_id).focus();

        });
    });

    $('.cbcomment_edit').each(function(e){

        $(this).blur(function(e){
            e.preventDefault();

            var $this = $(this);


            var cbRatingData = {} ;

            cbRatingData['id']                              = $this.attr('data-id');
            cbRatingData['form_id']                         = $this.attr('data-form-id');
            cbRatingData['post_id']                         = $this.attr('data-post-id');
            cbRatingData['cb_text']                         = $this.val();


            $.ajax({
                type   : 'POST',
                url    : ajaxurl,
                //url    : commentAjax.ajaxUrl,
                timeout: 20000,     // For 20 secs.
                data   : {
                    action      : 'cbCommentEditAjaxFunction',
                    cbRatingData: cbRatingData
                },
                success: function (data, textStatus, XMLHttpRequest) {
                    var _cb_text = jQuery($this).val();
                    jQuery($this).hide();
                    $('.comment_'+ cbRatingData['id'] ).show();
                    $('.comment_'+ cbRatingData['id'] ).text(_cb_text);
                }
            });
        });
    });
    */
    /*
    $('.cbratingdash_comment_wrapper').each(function(){

        $(this).hover(function () {
            //console.log('hover');
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
    */



    ///comment_status
    //$('.comment_status').hide();
    /*
    $('.comment_status_column').hover(function(){
            //$(this).children('.comment_status').show();
            var check_unverified = $(this).children('.cb-comment-label').text();
            if(check_unverified == 'unverified'){
                $(this).children('.unapproved').hide();
                $(this).children('.approved').hide();
            }
            var data_status = $(this).attr('data-status');
            $(this).children('.'+data_status).hide();

    },
    function(){

        //$(this).children('.comment_status').hide();
    });
    */

    // fixing cooment tr color according to status
    /*
    $('.cb-spam').css('color','#aa1100');
    $('.cb-approved').css('color','green');
    $('.cb-unapproved').css('color','#dd0000');
    $('.cb-delete').css('color','#000000');

    */

    $('p.cbrating_comment_status_column[data-status ="approved"]').parents('tr').addClass('cb-tr-approved');
    $('p.cbrating_comment_status_column[data-status ="approved"]').parents('tr').children('th').addClass('cb-td-approved');

    $('p.cbrating_comment_status_column[data-status ="unapproved"]').parents('tr').addClass('cb-tr-unapproved');
    $('p.cbrating_comment_status_column[data-status ="unapproved"]').parents('tr').children('th').addClass('cb-td-unapproved');

    $('p.cbrating_comment_status_column[data-status ="spam"]').parents('tr').addClass('cb-tr-spam');
    $('p.cbrating_comment_status_column[data-status ="spam"]').parents('tr').children('th').addClass('cb-td-spam');

    //$('.comment_wrapper span.comment_status:not(:last-child)').append(' | ');

    /*//  cbrating comment atatus
    $('.cbrating_comment_status').click(function(e){
        e.preventDefault();

        var _this = jQuery(this);

        var cbRatingData = {} ;
        cbRatingData['id']                  = jQuery(this).attr('data-id');
        cbRatingData['form_id']             = jQuery(this).attr('data-form-id');
        cbRatingData['post_id']             = jQuery(this).attr('data-post-id');
        cbRatingData['comment_status']      = jQuery(this).attr('data-comment-status');

        $.ajax({
            type   : 'POST',
            //url    : commentAjax.ajaxUrl,
            url    : ajaxurl,
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
                    $(_this).parent('p.cbrating_comment_status_column').attr('data-status','approved');
                }
                else if(cbRatingData['comment_status'] == "unapproved"){
                    $(_this).parents('td').parent('tr').removeClass().addClass('cb-tr-unapproved');
                    $(_this).parents('td').parent('tr').children().first().removeClass().addClass('cb-td-unapproved').addClass('check-column');
                    $(_this).parent('p.cbrating_comment_status_column').attr('data-status','unapproved');
                }
                else if(cbRatingData['comment_status'] == "spam"){

                    $(_this).parents('td').parent('tr').removeClass().addClass('cb-tr-spam');
                    $(_this).parents('td').parent('tr').children().first().removeClass().addClass('cb-td-spam').addClass('check-column');
                    $(_this).parent('p.cbrating_comment_status_column').attr('data-status','spam');
                }

                $(_this).siblings().show();
                $(_this).hide();

                if(cbRatingData['comment_status'] == "delete"){
                    $(_this).parents('td').parent('tr').hide();
                }

            }
        });
    }); // end of comment status click*/



});// end of dom ready