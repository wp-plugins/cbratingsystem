jQuery(document).ready(function ($) {

	jQueryReviewRatingDisplay('', false);

	function jQueryReviewRatingDisplay(varReviewRatingFormName, ajax) { //console.log("hellow front review js!");
		jQuery('.reviews_container .review_wrapper').each(function () {
			var ratingFormID = jQuery(this).attr('data-form-id');
			var postID = jQuery(this).attr('data-post-id');
			var reviewID = jQuery(this).attr('data-review-id');

			if (ajax === false) {
				varReviewRatingFormName = 'reviewContent_post_' + postID + '_form_' + ratingFormID;
			} else {
				varReviewRatingFormName = 'reviewContent_post_' + postID + '_form_' + ratingFormID + '_ajax';
			}
			//console.log(varReviewRatingFormName);

			if (window[varReviewRatingFormName]['review_' + reviewID] !== undefined) {
				//var varReviewRatingFormName = 'reviewContent_post_'+postID+'_form_'+ratingFormID;
				reviewRatingDisplay($, ratingFormID, postID, reviewID, window[varReviewRatingFormName]['review_' + reviewID]);
			} else {
				var varReviewRatingFormName = varReviewRatingFormName;

				if (ajax === true) {
					varReviewRatingFormName = 'reviewContent_post_' + postID + '_form_' + ratingFormID + '_ajax';
					reviewRatingDisplay($, ratingFormID, postID, reviewID, window[varReviewRatingFormName]['review_' + reviewID]);
				}
			}

			//console.log(window[varReviewRatingFormName]['review_'+reviewID] === undefined);
			//console.log(reviewID);
			//console.log(window[varReviewRatingFormName]);
			//reviewRatingDisplay($, ratingFormID, postID, reviewID, window[varReviewRatingFormName]['review_'+reviewID]);
		});
	}

	jQuery('.load_more_button').each(function () {
		jQuery(this).click(function () {

			var ratingFormID = jQuery(this).attr('data-form-id');
			var postID = jQuery(this).attr('data-post-id');
			var start = jQuery(this).attr('data-start');
			var offset = jQuery(this).attr('data-offset');
			var end = jQuery(this).attr('data-end');
			var nonce = $('.reviews_container_post-' + postID + '_form-' + ratingFormID + ' #cb_ratingForm_front_review_nonce_field').val();
			var cbReviewData = {};

			jQuery('.load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).show();
			jQuery('.load_more_button_form-' + ratingFormID + '_post-' + postID).addClass('disabled_cbrp_button');

			$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').empty();
			$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').removeClass('error_message');

			cbReviewData['ratingFormID'] = ratingFormID;
			cbReviewData['postID'] = postID;
			cbReviewData['start'] = start;
			cbReviewData['offset'] = offset;
			cbReviewData['end'] = end;
			cbReviewData['nonce'] = nonce;

			$.ajax({
				type   : 'POST',
				url    : ratingFormAjax.ajaxurl,
				data   : {
					action      : 'cbReviewAjaxFunction',
					cbReviewData: cbReviewData
				},
				success: function (data, textStatus, XMLHttpRequest) {
					//console.log(data);
					if (data) {
						try {
							//readOnlyRatingForm = data;
							//readOnlyRatingDisplay($, ratingFormID, postID, data, true);

							jQuery('.load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).hide();
							jQuery('.load_more_button_form-' + ratingFormID + '_post-' + postID).removeClass('disabled_cbrp_button');

							var appendableData = $.parseJSON(data);
							//console.log(appendableData);
							$('.reviews_container_div_post-' + postID + '_form-' + ratingFormID).append(appendableData.html);
							$('.load_more_button_form-' + ratingFormID + '_post-' + postID).attr('data-end', appendableData.end).attr('data-start', appendableData.start);

							var varReviewRatingFormName = 'reviewContent_post_' + postID + '_form_' + ratingFormID + '_ajax';
							jQueryReviewRatingDisplay(varReviewRatingFormName, true);

							jQuery('.load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).hide();

							if (appendableData.isFinished == 1) {
								$('.load_more_button_form-' + ratingFormID + '_post-' + postID).hide();
							}

						} catch (e) {
							jQuery('.load_more_waiting_icon_form-' + ratingFormID + '_post-' + postID).hide();
							jQuery('.load_more_button_form-' + ratingFormID + '_post-' + postID).removeClass('disabled_cbrp_button');

							$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').html(cbrpRatingFormReviewContent.failure_msg);
							$('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').addClass('error_message');
						}
					}
					//console.log(textStatus);
					//alert(data);

				},
				error  : function (MLHttpRequest, textStatus, errorThrown) {
					alert(errorThrown);
				}
			});
		});
	});

	function reviewRatingDisplay($, ratingFormID, postID, review_id, reviewRatingForm) {
		//console.log( reviewRatingForm.options );
		$('.review_rating_review-' + review_id + ' .criteria-wrapper .criteria-star-wrapper').each(function () {
			var criteria_id = $(this).attr('data-criteria-id');
			var option = $.parseJSON(reviewRatingForm.options); //console.log(option);

			var value = option['review_' + review_id + '_criteria_' + criteria_id + '_value'];
			var count = option['review_' + review_id + '_criteria_' + criteria_id + '_count'];
			var redOnlyHint = option['review_' + review_id + '_criteria_' + criteria_id + '_redOnlyHint'];
			var hints = option['review_' + review_id + '_criteria_' + criteria_id + '_hints'];

			//console.log(option, 'review_'+review_id+'_criteria_'+criteria_id+'_value', value, count, redOnlyHint, hints);
			//console.log($(this).raty);
			$(this).raty({
				path      : reviewRatingForm.img_path,
				readOnly  : true,
				noRatedMsg: 'No Rating',
				number    : count,
				score     : Math.round(value),
				hints     : hints
			});
		});
	}

});