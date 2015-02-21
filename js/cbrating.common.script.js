jQuery(document).ready(function ($) {
	var elems = ['.cbrp-form-listing', '.cb-ratingForm-edit', '.cbrp-log-report', '.cbrp-log-report-average', '.cbratingsystem-theme-settings', '.cbrp_front_content'];
	$('#edit-post-type').chosen();
	$('#edit-allowed-users').chosen();
	$('#edit-editor-group').chosen();
	$('#edit-view-allowed-users').chosen();
	$('#edit-comment-view-allowed-users').chosen();
    $('#edit-comment-moderation-users').chosen();
    $('.form-checkbox').uniform();
    $('.form-radio').uniform();
    $('.cbrp-content-container .form-text').uniform();
   // $('.item-question').uniform();


    $('.custom-criteria-enable-checkbox').uniform();
    $('.seperated_checkbox_input').uniform();

    for (i in elems) {
		//$(elems[i] + ' input[type="checkbox"], input[type="radio"]').uniform();


		$(elems[i] + ' select.question_field_type, ' + elems[i] + ' .select_count').selectize({
			create   : true,
			sortField: 'text'
		});
	}
});