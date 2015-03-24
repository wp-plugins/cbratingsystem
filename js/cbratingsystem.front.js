jQuery(document).ready(function ($) {

    /*
     * Text area character limit.
     * http://www.yourinspirationweb.com/en/jquery-tips-tricks-how-to-limit-characters-inside-a-textarea/
     */
    //$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbratingsystem_comment_box textarea').each(function () {
      $('.cbrating_commentarea').each(function () {
        //console.log($('ratingFormOptions_form_'+ratingFormID));
        //var characters = ratingFormOptions.limit;
        var $this = $(this);
        var ratingFormID = $this.data('formid');// rating form id

        var char_limit = window['ratingFormOptions_form_'+ratingFormID].limit;
        //var resultSelector = ".cbrating_comment_limit_text_form_" + ratingFormID + "_post_" + postID;

        if (char_limit > 0) {
            //$(resultSelector).append(cbrating_prefix.string_prefix + " <strong>" + characters + "</strong> " + cbrating_prefix.string_postfix);
            var $commentlabel = $this.next('.cbrating_comment_limit_label');
            if($commentlabel.length)  $commentlabel.append(cbrating_prefix.string_prefix + " <strong>" + char_limit + "</strong> " + cbrating_prefix.string_postfix);


            $this.keyup(function () {
                if ($(this).val().length > char_limit) {
                    $(this).val($(this).val().substr(0, char_limit));
                }
                if ($commentlabel.length) {
                    var remaining = char_limit - $(this).val().length;
                    $commentlabel.html(cbrating_prefix.string_prefix + " <strong>" + remaining + "</strong> " + cbrating_prefix.string_postfix);
                    if (remaining <= 10) {
                        $commentlabel.addClass('comment_red_alert');
                    }
                    else {
                        $commentlabel.removeClass('comment_red_alert');
                    }
                }
            });
        }
    });//end comment area


	var cbratingCars = new Array();

	$('.cbrp-content-container').each(function () {

		var countcbipcheck = $(this).attr('data-count');

		var ratingFormID = $(this).attr('data-form-id');
		var postID = $(this).attr('data-post-id');
		var varRatingFormName = 'ratingForm_post_' + postID + '_form_' + ratingFormID;
		var varReadOnlyRatingFormName = 'readOnlyRatingForm_post_' + postID + '_form_' + ratingFormID;

		$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-rating-buffer-form-' + ratingFormID + ' .criteria-star-wrapper').each(function () {
			var label_id = $(this).attr('data-label-id');
			var hints = cbRatingSystemhintListings(varRatingFormName, 'criteria-label-' + ratingFormID + '-stars-' + label_id);

			$(this).raty({
				scoreName : 'criteria[' + ratingFormID + '][' + label_id + '][value]',
				cancel    : true,
				cancelHint: window[varRatingFormName].cancel_hint,
				target    : '.criteria-star-hint-id-' + label_id + '-form-' + ratingFormID,
				hints     : hints,
				path      : window[varRatingFormName].img_path,
				noRatedMsg: 'No Rating',
				number    : $(hints).length,
				start     : $('.criteria-star criteria-star-label-id-' + label_id).val()
			});

		});

		readOnlyRatingDisplay($, ratingFormID, postID, window[varReadOnlyRatingFormName], false);
		//readOnlyRatingDisplaycopy($, ratingFormID, postID, window[varReadOnlyRatingFormName], false);

		/*
		 * Text area character limit.
		 * http://www.yourinspirationweb.com/en/jquery-tips-tricks-how-to-limit-characters-inside-a-textarea/
		 */
        /*
		$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbratingsystem_comment_box textarea').each(function () {
		//$('.cbrating_commentarea').each(function () {
            //console.log($('ratingFormOptions_form_'+ratingFormID));
			//var characters = ratingFormOptions.limit;
			var characters = window['ratingFormOptions_form_'+ratingFormID].limit;
			var resultSelector = ".cbrating_comment_limit_label_form_" + ratingFormID + "_post_" + postID;
			//console.log(ratingFormOptions);
			if (characters != 0) {
				$(resultSelector).append(cbrating_prefix.string_prefix + " <strong>" + characters + "</strong> " + cbrating_prefix.string_postfix);

				$(this).keyup(function () {
					if ($(this).val().length > characters) {
						$(this).val($(this).val().substr(0, characters));
					}
					if (resultSelector != false) {
						var remaining = characters - $(this).val().length;
						$(resultSelector).html(cbrating_prefix.string_prefix + " <strong>" + remaining + "</strong> " + cbrating_prefix.string_postfix);
						if (remaining <= 10) {
							$(resultSelector).addClass('comment_red_alert');
						}
						else {
							$(resultSelector).removeClass('comment_red_alert');
						}
					}
				});
			}
		});
        */

		$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-button-form-' + ratingFormID).click(function (event) {

            /*
            //this is not used.
            var entityMap = {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': '&quot;',
                "'": '&#39;',
                "/": '&#x2F;'
            };

            function escapeHtml(string) {
                return String(string).replace(/[&<>"'\/]/g, function (s) {
                    return entityMap[s];
                });
            }
             */

            //var whiteRe = /\s*/;
            //var spaceRe = /\s+/;
            //var equalsRe = /\s*=/;
            //var curlyRe = /\s*\}/;
            //var tagRe = /#|\^|\/|>|\{|&|=|!/;



            event.preventDefault();
            var cb_this_comment_status = $(this).attr('data-comment-status');
            var cb_this_buddypress_post = $(this).attr('data-buddypress-post');
            var cb_this_hash = $(this).attr('data-hash');

            //console.log(cb_this_comment_status);
            //console.log('hi');
			var rp_id = $('.cbrp-content-container-form-' + ratingFormID + '-post-' + postID + ' input[name="rp_id"]').val();
			var nonce = $('.cbrp-content-container-form-' + ratingFormID + '-post-' + postID + ' #cb_ratingForm_front_form_nonce_field').val();
			var values = {};
			var criteriaCount = {};
			var question = {};
			question[ratingFormID] = {};
			var valuesCount = 0;
			var criteria = 0;
			var questionError = false;
			var qvalue = {};
			var userName = '';
			var userEmail = '';
			var buttonObject = $(this).parents('.cbrp-content-container');
			var mainParent = $(this).parents('.cbrp-content-container');


			$('.cbrp_load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).show();
			$(this).attr('disabled', 'disabled').addClass('disabled_cbrp_button');

			$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').removeClass('error_message');

			$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' input[name^="criteria["]').each(function () {
				var label_id = $(this).parent().attr('data-label-id');
				var value = $(this).val();
				var hints = cbRatingSystemhintListings(varRatingFormName, 'criteria-label-' + ratingFormID + '-stars-' + label_id);

				criteriaCount[label_id] = cbRatingSystemhintListings(varRatingFormName, 'criteria-label-' + ratingFormID + '-stars-' + label_id + '-count');
				criteria++;


				if (value != '' && value != undefined && value != null) {
					var calculatedValue = ( (value / parseInt(criteriaCount[label_id])) * 100 );
					if (value <= 100) {
						values[label_id] = calculatedValue.toFixed(2);
						values[label_id + '_starCount'] = cbRatingSystemhintListings(varRatingFormName, 'criteria-label-' + ratingFormID + '-stars-' + label_id + '-count');
						values[label_id + '_stars'] = hints;
						values[label_id + '_actualValue'] = value;
						valuesCount++;

						$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .criteria-id-wrapper-' + label_id).removeClass('cbratingsystem-error');
					}
				} else {
					$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .criteria-id-wrapper-' + label_id).addClass('cbratingsystem-error');
				}
			});
            var hide_this_user_name = $('#cbratingsystem_hide_name_checkbox_field_' + ratingFormID + '_post_' + postID ).prop("checked");
          //  console.log(hide_this_user_name);return false;

			$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' input[name^="question["]').each(function () {
				var q_id = $(this).attr('data-q-id');
				var nameAttr = $(this).attr('name');
				var inputType = $(this).attr('type');

				qvalue[q_id + '_type'] = inputType;

				if (($(this).attr('data-' + inputType + '-field-text-id') === undefined) || (inputType == 'radio') || (inputType == 'text')) {
					if (inputType == 'checkbox') {
						if ($(this).is(':checked')) {
							qvalue[inputType + '-' + q_id] = 1;
						} else {
							qvalue[inputType + '-' + q_id] = 0;
						}
					} else if (inputType == 'radio') {
						qvalue[inputType + '-' + q_id] = $('input[name="' + nameAttr + '"]:checked').val();
					} else {
						qvalue[inputType + '-' + q_id] = $('input[name="' + nameAttr + '"]').val();
					}

				} else {
					var fieldId = $(this).attr('data-' + inputType + '-field-text-id');

					if (inputType == 'checkbox') {
						//qvalue = {};

						if ($(this).is(':checked')) {
							qvalue[q_id + '_' + fieldId] = 1;
						} else {
							qvalue[q_id + '_' + fieldId] = 0;
						}
					}
				}

				if (qvalue != '' && qvalue != undefined && qvalue != null && qvalue.length != 0) {
					question[ratingFormID][q_id] = qvalue;
				}
			});

			var commentValue = $('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' textarea[name="comment[' + ratingFormID + ']"]').val();

            //console.log(commentValue);


            var comment_r = $('.cbrs_comment_textarea').attr('data-required');
            if(comment_r == '1'){
                if(commentValue.length == 0){
                    jQuery('.cbrs_comment_textarea').addClass('cbratingsystem-error');
                    questionError = true;
                }
                else{
                    jQuery('.cbrs_comment_textarea').removeClass('cbratingsystem-error');
                    questionError = false;
                }
            }

			jQuery('input.required').each(function () {
				var id          = jQuery(this).attr('id');
				var q_id        = jQuery(this).attr('data-q-id');
				var nameAttr    = jQuery(this).attr('name');
				var inputType   = $(this).attr('type');
				var count = 0;
				var ansCount = 0;

				if (typeof(question[ratingFormID][q_id]) != 'undefined') {
					if (inputType == 'checkbox') {


                        var question_Checked = false;
                        jQuery('input.required[data-q-id="' + q_id + '"]').each(function () {
                            if($(this).prop('checked') == true){
                                question_Checked = true;
                            }
                        });
                        if(question_Checked == false){
                            jQuery('label[for="question-form-' + ratingFormID + '-q-' + q_id + '"]').addClass('cbratingsystem-error');
                            questionError = true;
                        }
                        else{
                            jQuery('label[for="question-form-' + ratingFormID + '-q-' + q_id + '"]').removeClass('cbratingsystem-error');
                          //  questionError = true;
                        }
					} else {

						var varible = question[ratingFormID][q_id][inputType + '-' + q_id];

						if ((varible == '') || (varible == undefined) || (varible == null)) {
							jQuery('label[for="question-form-' + ratingFormID + '-q-' + q_id + '"]').addClass('cbratingsystem-error');
							questionError = true;
						} else {
							jQuery('label[for="question-form-' + ratingFormID + '-q-' + q_id + '"]').removeClass('cbratingsystem-error');
						}
					}
				}

			});

			mainParent.find('.user_name_field').each(function () {
				var placeholder = jQuery(this).attr('placeholder');
				var val = jQuery(this).val().replace(placeholder, '');
				var formId = jQuery(this).attr('data-form-id');
				var id = jQuery(this).attr('id');

				if (val.length == 0) {
					mainParent.find('#' + id).addClass('cbratingsystem-error');
					questionError = true;
				} else {
					mainParent.find('#' + id).removeClass('cbratingsystem-error');
					userName = val;
				}
			});

			mainParent.find('.user_email_field').each(function () {
				var placeholder = jQuery(this).attr('placeholder');
				var val = jQuery(this).val().replace(placeholder, '');
				var formId = jQuery(this).attr('data-form-id');
				var id = jQuery(this).attr('id');

				if (val.length == 0) {
					mainParent.find('#' + id).addClass('cbratingsystem-error');
					questionError = true;
				} else {
					if (checkEmailValidity(val)) {
						mainParent.find('#' + id).removeClass('cbratingsystem-error');
						userEmail = val;
					} else {
						mainParent.find('#' + id).addClass('cbratingsystem-error');
						questionError = true;
					}
				}
			});

			if ((valuesCount != criteria) || (questionError === true)) {
				jQuery('.cbrp_load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).hide();
				$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-button-form-' + ratingFormID).removeAttr('disabled').removeClass('disabled_cbrp_button');

				var errTxt = '';
				$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').empty();
				if ((valuesCount !== criteria)) {
					errTxt += window[varRatingFormName].pleaseFillAll_msg + '<br/>';
				}
				if ((questionError === true)) {
					errTxt += window[varRatingFormName].pleaseCheckTheBox_msg + '<br/>';
				}

				if (errTxt.length != 0) {
					$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').html(errTxt);
					$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('error_message');
				}
			} else if ((valuesCount === criteria) && (questionError === false)) {

				var cbRatingData                    = {};
				cbRatingData['rp_id']               = rp_id;
				cbRatingData['cbrp_nonce']          = nonce;
				cbRatingData['values']              = values;
				cbRatingData['criteriaCount']       = criteriaCount;
				cbRatingData['question']            = question;
				cbRatingData['comment']             = commentValue;
				cbRatingData['comment_limit']       = window['ratingFormOptions_form_'+ratingFormID].limit;
				cbRatingData['user_name']           = userName;
				cbRatingData['user_email']          = userEmail;
                cbRatingData['comment_status']      = cb_this_comment_status;
                cbRatingData['comment_hash']        = cb_this_hash;
                cbRatingData['buddypress_post']     = cb_this_buddypress_post;
                cbRatingData['hide_this_user_name'] = hide_this_user_name;



				$.ajax({
					type   : 'POST',
					url    : ratingFormAjax.ajaxurl,
					timeout: 20000,     // For 20 secs.
					data   : {
						action      : 'cbRatingAjaxFunction',
						cbRatingData: cbRatingData
					},
					success: function (data, textStatus, XMLHttpRequest) {

						if (data.length > 0) {
							//readOnlyRatingForm = data;
							try {

								var parsedData = $.parseJSON(data);

								if (parsedData.hasOwnProperty('hints')) {
									readOnlyRatingDisplay($, ratingFormID, postID, data, true);
									//readOnlyRatingDisplaycopy($, ratingFormID, postID, data, true);
                                    //console.log(parsedData.comment_status);
									jQuery('.cbrp_load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).hide();

									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' #cbrp-report').remove();
									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' #cbrp-form').remove();
									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-rating-buffer-form-' + ratingFormID).remove();
									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-switch-report-form-' + ratingFormID).show();

									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').empty();

									if (parsedData.hasOwnProperty('errorMessage')) {
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').html(parsedData.errorMessage);
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('error_message');
										//console.log("already rated");
									} else {
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').removeClass('error_message').removeClass('cbratingsystem-error');
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('cbrp_status');
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').text(window[varRatingFormName].thanks_msg);

										//updating the new added comment to the review list
										//console.log(parsedData.lastcomment[0]);
										$lastcomment = parsedData.lastcomment[0];
										$theme_key = parsedData.theme_key;
										$reviewID = jQuery(this).attr('data-review-id');


										//html code chunk for adding into the review section if there
										//is no other reviews yet
										$lastcomment_html = '<h3 id="cbratingfrom_reviews_title" class="cbratingfrom_reviews_title">Reviews</h3>';
										$lastcomment_html += '<div id="reviews_container_' + postID + '" data-post-id="' + postID + '" data-form-id="' + ratingFormID + '" class="reviews_container_' + $theme_key + '_theme reviews_container reviews_container_post-' + postID + '_form-' + ratingFormID + ' ">';
										$lastcomment_html += '<div data-post-id="' + postID + '" data-form-id="' + ratingFormID + '" class="reviews_container_div_' + $theme_key + '_theme reviews_container_div reviews_container_div_post-' + postID + '_form-' + ratingFormID + ' ">';
										$lastcomment_html += $lastcomment;
										$lastcomment_html += '</div>';
										$lastcomment_html += '</div>';

										//if previous rating/revew exists
										if ($("#reviews_container_" + postID).length > 0) {
											$("#reviews_container_" + postID + " div.reviews_container_div").prepend($lastcomment);
											readOnlyRatingDisplay($, ratingFormID, postID, data, true);
											readOnlyRatingDisplaycopy($, ratingFormID, postID, data, true);
										}
										else {
											//console.log('put rate first time');
											//a new rating and first time rating
											$("#cbrp_container_" + postID).after($lastcomment_html);
											readOnlyRatingDisplay($, ratingFormID, postID, data, true);
											readOnlyRatingDisplaycopy($, ratingFormID, postID, data, true);
										}
									}
								} else if (parsedData.hasOwnProperty('validation')) {
									jQuery('.cbrp_load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).hide();
									if (parsedData.hasOwnProperty('errorMessage')) {
										buttonObject.removeAttr('disabled').removeClass('disabled_cbrp_button');
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').html(parsedData.errorMessage);
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('error_message');
									} else {
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').removeClass('error_message').removeClass('cbratingsystem-error');
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('cbrp_status');
										$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').text(window[varRatingFormName].thanks_msg);
									}
								} else {
									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' #cbrp-report').remove();
									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' #cbrp-form').remove();
									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-rating-buffer-form-' + ratingFormID).remove();
									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-switch-report-form-' + ratingFormID).remove();

									var failure_msg = window['ratingForm_post_' + postID + '_form_' + ratingFormID].failure_msg;

									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').html(failure_msg);
									$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('error_message');
								}
							} catch (e) {
								failure_msg = window['ratingForm_post_' + postID + '_form_' + ratingFormID].failure_msg;

								$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').html(failure_msg);
								$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('error_message');

								$('.cbrp_load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).hide();
								$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-button-form-' + ratingFormID).removeAttr('disabled').removeClass('disabled_cbrp_button');
							}

						} else {
							$('.cbrp_load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).hide();
							$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-button-form-' + ratingFormID).removeAttr('disabled').removeClass('disabled_cbrp_button');
						}
						//console.log(textStatus);
						//alert(data);

					},
					error  : function (MLHttpRequest, textStatus, errorThrown) {
						var failure_msg = window['ratingForm_post_' + postID + '_form_' + ratingFormID].failure_msg;

						$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').append(failure_msg);
						$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('error_message');

						$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-button-form-' + ratingFormID).removeAttr('disabled').removeClass('disabled_cbrp_button');
					}
				});
			}
		});

		if (countcbipcheck > 0) {

			$('.cbrp-rating-buffer-form-' + ratingFormID).hide();
			$('.cbrp-switch-report-form-' + ratingFormID).show();
		}
		$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbratingsystem-tabswitch').click(function () {
			var showing_div_selector = $(this).attr('data-show-div');

			$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID).children().hide();
			$('.' + showing_div_selector + '-post-' + postID).show();
		});
	});

	/**
	 * readOnlyRatingDisplay for displaying the single rating with
	 * editors rating in rating form using ajax
	 *
	 * @param $
	 * @param ratingFormID
	 * @param postID
	 * @param readOnlyRatingForm
	 * @param ajax
	 */
	function readOnlyRatingDisplaycopy($, ratingFormID, postID, readOnlyRatingForm, ajax) {
		if (ajax) {

			//done by sudarshan
			var parsedss = $.parseJSON(readOnlyRatingForm);
			var parsedHintsss = $.parseJSON(parsedss.hints);
			var ratingObj = parsedss.lastcomment[1].rating;

			var i = 0;
			for (var key in ratingObj) {

				var rating_key = i + '_starCount';
				var rating_val = i;
				var rating_acval = i + '_actualValue';
				if (ratingObj.hasOwnProperty(rating_key) && ratingObj.hasOwnProperty(rating_acval) && ratingObj.hasOwnProperty(rating_val)) {

					var testratingSelector = $('.reviews_container_div').children('div:eq(0)').children().children('div:eq(2)').children('div.criteria-id-wrapper-' + i).children('div:eq(1)');
					//testratingSelector.append('hello');
					var ratingscore = (ratingObj[rating_val] / 100) * ratingObj[rating_key];

					testratingSelector.raty({
						path      : parsedss.img_path,
						readOnly  : parsedss.is_rated,
						noRatedMsg: 'No Rating',
						number    : ratingObj[rating_key],
						score     : Math.round(ratingscore)
					});

				}
				i++;
			}
		}

	}

	var checkone = 1;

	function readOnlyRatingDisplay($, ratingFormID, postID, readOnlyRatingForm, ajax) {

		var varRatingFormName = 'ratingForm_post_' + postID + '_form_' + ratingFormID;
		//console.log(varRatingFormName);
		var varReadOnlyRatingFormName = 'readOnlyRatingForm_post_' + postID + '_form_' + ratingFormID;
		//console.log(varReadOnlyRatingFormName);
		if (ajax) {
			var parsed = $.parseJSON(readOnlyRatingForm);
			var parsedHints = $.parseJSON(parsed.hints);
		}

		//rating for the per user review on ajax request
		//if(checkone ==1){
		/* if(ajax) {

		 //done by sudarshan
		 var parsedss = $.parseJSON(readOnlyRatingForm);
		 var parsedHintsss = $.parseJSON(parsedss.hints);
		 var ratingObj = parsedss.lastcomment[1].rating;

		 var i = 0;
		 for (var key in ratingObj) {

		 var rating_key = i+'_starCount';
		 var rating_val = i;
		 var rating_acval = i+'_actualValue';
		 if(ratingObj.hasOwnProperty(rating_key) && ratingObj.hasOwnProperty(rating_acval) && ratingObj.hasOwnProperty(rating_val)) {

		 var testratingSelector = $('.reviews_container_div').children('div:eq(0)').children().children('div:eq(2)').children('div.criteria-id-wrapper-'+i).children('div:eq(1)');
		 //testratingSelector.append('hello');
		 var ratingscore = (ratingObj[rating_val]/100)*ratingObj[rating_key];

		 testratingSelector.raty({
		 path: parsedss.img_path,
		 readOnly: parsedss.is_rated,
		 noRatedMsg: 'No Rating',
		 number: ratingObj[rating_key],
		 score: Math.round(ratingscore)
		 });

		 }
		 i++;
		 }
		 }
		 checkone =0;*/

		//end of from adnnan
		$('.reviews_container_post-' + postID + '_form-' + ratingFormID + ' .reviews_container_div_post-' + postID + '_form-' + ratingFormID + ' #criteria-star-wrapper').each(function () {

			var label_id = $(this).attr('data-label-id');
			var criteria_id = $(this).attr('data-criteria-id');
			//var option = $.parseJSON(readOnlyRatingForm);
			//console.log(parsedHints);
			if (ajax) {
				var parsed = window.parsed;
				var parsedHints = window.parsedHints;
				var starRating = window.starRating;

				//console.log(myCars);
				//console.log (parsed.img_path);
				//console.log(parsedHints);
				//console.log(starRating);

				/*$(this).raty({
				 path: parsed.img_path,
				 readOnly: parsed.is_rated,
				 noRatedMsg: 'No Rating',
				 number: parsedHints['readonly-criteria-label-'+ratingFormID+'-stars-'+label_id+'-count'],
				 score: Math.round(starRating)
				 });*/

				$('.criteria-average-label-form-' + ratingFormID + '-label-' + label_id + ' .rating').empty().text((starRating).toFixed(2) + '/' + parsedHints['readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count']);
				$('.rating-average-label-form-' + ratingFormID + ' .total_rates_count').empty().text(parsed.ratingsCount);
			}
		});

		//rating for right now submission
		$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-switch-report-form-' + ratingFormID + ' .criteria-star-wrapper').each(function () {
			var label_id = $(this).attr('data-label-id');
			var hints = cbRatingSystemhintListings(varReadOnlyRatingFormName, 'readonly-criteria-label-' + ratingFormID + '-stars-' + label_id);
			var valueArea = 'readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-value';

			if (ajax) {
				var parsed = $.parseJSON(readOnlyRatingForm); //console.log(parsed);
				var parsedHints = $.parseJSON(parsed.hints); //console.log(parsedHints['readonly-criteria-label-'+ratingFormID+'-stars-'+label_id+'-count']);
				var starRating = ( (parsedHints[valueArea] / 100) * parsedHints['readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'] ); //console.log(starRating);
				//var avgRating = ( (parsedHints['readonly-criteria-label-'+ratingFormID+'-post-'+postID+'-avgvalue'] / 100) * parsedHints['readonly-criteria-label-'+ratingFormID+'-stars-'+label_id+'-count'] )
				//console.log(parsedHints[valueArea]);
				//storing the data for putting into ajax request review show
				window.parsedHints = parsedHints;//console.log(window.parsedHints);
				window.parsed = parsed;//console.log( window.parsed);
				window.starRating = starRating;//console.log(window.starRating);
                cbratingCars.push(starRating);
				$(this).raty({
					path      : parsed.img_path,
					readOnly  : parsed.is_rated,
					noRatedMsg: 'No Rating',
					number    : parsedHints['readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'],
					score     : Math.round(starRating)
				});

				$('.criteria-average-label-form-' + ratingFormID + '-label-' + label_id + '-postid-' + postID + ' .rating').empty().text((starRating).toFixed(2) + '/' + parsedHints['readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count']);
				$('.rating-average-label-form-' + ratingFormID + '-postid-' + postID + '  .total_rates_count').empty().text(parsed.ratingsCount);

				//console.log(starRating.toFixed(2));

			} else {
				parsedHints = $.parseJSON(readOnlyRatingForm.hints);

				starRating = ( (parsedHints[valueArea] / 100) * parsedHints['readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'] );

				$(this).raty({
					path      : readOnlyRatingForm.img_path,
					readOnly  : isRated(varReadOnlyRatingFormName),
					noRatedMsg: 'No Rating',
					number    : parsedHints['readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'],
					score     : Math.round(starRating)
				});
			}
		});

		/* For Editor Section */
		$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .cbrp-switch-report-form-' + ratingFormID + ' .editor-criteria-star-wrapper').each(function () {
			var label_id = $(this).attr('data-label-id');
			var hints = cbRatingSystemhintListings(varReadOnlyRatingFormName, 'editor-readonly-criteria-label-' + ratingFormID + '-stars-' + label_id);
			var valueArea = 'editor-readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-value';

			if (ajax) {
				var parsed = $.parseJSON(readOnlyRatingForm);
				var parsedHints = $.parseJSON(parsed.hints);
				//console.log(parsed);

				var starRating = ( (parsedHints[valueArea] / 100) * parsedHints['editor-readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'] );
				//var avgRating = ( (parsedHints['readonly-criteria-label-'+ratingFormID+'-post-'+postID+'-avgvalue'] / 100) * parsedHints['readonly-criteria-label-'+ratingFormID+'-stars-'+label_id+'-count'] )

				if (typeof(starRating) != 'number') {
					starRating = 0;
				}
				if (!parsedHints.hasOwnProperty('editor-readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count')) {
					var editorStarCount = parsedHints['readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'];
				} else {
					var editorStarCount = parsedHints['editor-readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'];
				}

				$(this).raty({
					path      : parsed.img_path,
					readOnly  : parsed.is_rated,
					noRatedMsg: 'No Rating',
					number    : editorStarCount,
					score     : Math.round(starRating)
				});
				//console.log('startrating'+)
				$('.editor-criteria-average-label-form-' + ratingFormID + '-label-' + label_id + '-postid-' + postID + ' .rating').empty().text((starRating).toFixed(2) + '/' + editorStarCount);

				$('.editor-rating-average-label-form-' + ratingFormID + ' .total_rates_count').empty().text(parsed.editorRatingsCount);
				//console.log('If I see this msg then I am too happy');
				//is raty action missing here
			} else {
				parsedHints = $.parseJSON(readOnlyRatingForm.hints);

				starRating = ( (parsedHints[valueArea] / 100) * parsedHints['editor-readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'] );

				if (typeof(starRating) != 'number') {
					starRating = 0;
				}
				if (!parsedHints.hasOwnProperty('editor-readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count')) {
					var editorStarCount = parsedHints['readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'];
				} else {
					var editorStarCount = parsedHints['editor-readonly-criteria-label-' + ratingFormID + '-stars-' + label_id + '-count'];
				}

				$(this).raty({
					path      : readOnlyRatingForm.img_path,
					readOnly  : isRated(varReadOnlyRatingFormName),
					noRatedMsg: 'No Rating',
					number    : editorStarCount,
					score     : Math.round(starRating)
				});
			}
		});

		if (ajax) {
			var avgFormRating = ( (parseInt(parsedHints['readonly-criteria-label-' + ratingFormID + '-post-' + postID + '-avgvalue']) / 100) * 5 );
			var editorAvgFormRating = ( (parseInt(parsedHints['editor-readonly-criteria-label-' + ratingFormID + '-post-' + postID + '-avgvalue']) / 100) * 5 );
			//var totalRatings = ( parseInt($('.rating-average-label-form-'+ratingFormID+' .total_rates_count').text()) + 1 );

			$('.rating-average-label-form-' + ratingFormID + '-postid-' + postID + ' .rating').empty().text((avgFormRating).toFixed(2) + '/5');

			if (typeof( (editorAvgFormRating).toFixed(2)) != 'string' || typeof( (editorAvgFormRating).toFixed(2)) != 'numeric' || typeof( (editorAvgFormRating).toFixed(2)) != 'integer') {
				var editorAvg = 0;
			}

			$('.editor-rating-average-label-form-' + ratingFormID + '-postid-' + postID + ' .rating').empty().text((editorAvgFormRating).toFixed(2) + '/5');
		}

	}

	/**
	 * Code for read more feature for comment section at admin.
	 */
	$('.js_read_link').click(function () {
		var readMoreText = $(this).html();

		$('.read_more_paragraph').toggle();
		$(this).toggleClass('read_more');

		if (!$(this).hasClass('read_more')) {
			$(this).html(' ...Less');
		} else {
			$(this).html(' ...More');
		}

		return false;
	});

	function isInt(value) {
		if ((undefined === value) || (null === value)) {
			return false;
		}
		return value % 1 == 0;
	}

	function isRated(scope) {
		var ret = false;

		if (window[scope].is_rated) ret = true;

		return ret;
	}

	function cbRatingSystemhintListings(varName, hintArea) {

		var hint = '';
		var hint_arr = $.parseJSON(window[varName].hints);

		if (jQuery(hint_arr[hintArea]).size() > 0) {
			hint = hint_arr[hintArea];
		}

		return hint;
	}

	function cbReviewRatingSystemhintListings(variable, hintArea) {

		var hint = '';
		var hint_arr = $.parseJSON(variable.hintArea);

		if (jQuery(hint_arr[hintArea]).size() > 0) {
			hint = hint_arr[hintArea];
		}

		return hint;
	}

	/**
	 * Checking for email validation
	 *
	 * @param e
	 * @returns {Array|{index: number, input: string}|*|match|match|match}
	 */
	function checkEmailValidity(e) {
		return e.match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$");
	}

});
