<?php

/**
 * Class CBRatingSystemAdminFormParts
 */
class CBRatingSystemAdminFormParts extends CBRatingSystemAdmin {

	/*
	 * Got the div based table from: http://forum.weavertheme.com/discussion/3200/div-based-table-generator
	 */
	//public static function admin_ratingForm_edit_page_custom_question( $RFSettings = array() ) {
	public static function admin_ratingForm_edit_page_custom_question($customQuestion, $form_default, $form_question, $form_criteria, $post, $savedform = false ) {
		//echo '<pre>'; print_r($RFSettings); echo '</pre>';

		//$form_id = (!empty($RFSettings) and isset($RFSettings['rfid'])) ? $RFSettings['rfid'] : self::get_next_ratingForm_id();
		/*
		$post = isset($_POST['ratingForm']) ? $_POST['ratingForm'] : array();
		if ( isset( $_POST['ratingForm'] ) && ( ! empty( $_POST['ratingForm'] ) ) && isset($_POST['ratingForm']['enable_question'])  ) {
			//$question_enabled = $_POST['ratingForm']['enable_question'];
			$customQuestion   = $_POST['ratingForm']['custom_question'];
		} else {
			//$question_enabled = isset($RFSettings->enable_question)? $RFSettings->enable_question: 0;
            //$question_enabled = 0;
			//$customQuestion   = (isset( $RFSettings->custom_question ) && isset($RFSettings->custom_question['enabled'] )) ? $RFSettings->custom_question['enabled'] : array();
            $customQuestion = array();
		}
		*/


//		echo '<pre>';
//		print_r($customQuestion);
//		echo  '</pre>';
//
//		exit();


		//var_dump($savedform);


		//



		$fieldTypes = array( 'text' => __( 'Textbox', 'cbratingsystem' ), 'checkbox' => __( 'Checkbox', 'cbratingsystem' ), 'radio' => __( 'Radio', 'cbratingsystem' ) );

		$output = __('<p>Questions are optional. Each question can be set enabled or disabled, required. Three types of questions - Radio, Checkbox and textarea input can be created. Radio and checkbox question.</p>','cbratingsystem');


		$output .= '
        <div class="cb-ratingForm-edit-custom-question-container form-item" id="custom-question">
            <label class="" for="edit-custom-question">'.__('Questions','cbratingsystem').'</label>
            <div class="edit-custom-question-fields-wrapper div_table_wrapper">
                <!--Row #0-->
                <div class="div_table_row_header_wrapper div_table_row_wrapper rowId-0">
                    <div class="div_table div_table_header r0c1 col-odd row-odd header" style="">' . __( 'Question', 'cbratingsystem' ) . '</div>

                    <div class="div_table div_table_header r0c3 col-odd row-odd header" style="width:12%">' . __( 'Controls', 'cbratingsystem' ) . '</div>
                    <div class="div_table div_table_header r0c4 col-even row-odd header" style="width:21%">' . __( 'Field Type', 'cbratingsystem' ) . '</div>
                    <div class="div_table div_table_header r0c5 col-odd row-odd header" style="">' . __( 'Field Display', 'cbratingsystem' ) . '</div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>
            ';

        if(is_array($customQuestion) && !empty($customQuestion)){
            $qs_count = count($customQuestion);
        }
        else{
            //$qs_count = 3;
            //$qs_count = apply_filters('ratingsystem_question_number', 0); //means default one questions
	        $qs_count = 1; //means one question
        }

		//var_dump($qs_count);

		//for each question
		for ( $q_id = 0; $q_id < $qs_count ; $q_id ++ ) {

            if ( $q_id == ($qs_count -1) ) {
				$class = 'last';
			} elseif ( $q_id == 0 ) {
				$class = 'first';
			} else {
				$class = 'other';
			}

			$rowId   = $q_id + 1;
			$oddEven = ( $rowId % 2 ) ? 'odd' : 'even';

            $fieldDisplay = '';

			$fieldSeperate      = '';
			$fieldTypeSelectBox = '<select data-q-id="' . $q_id . '" name="ratingForm[custom_question][' . $q_id . '][field][type]" class="question_field_type question_field_type_q_id-' . $q_id . '">';

			$stored_fieldType   = isset($customQuestion[$q_id])? $customQuestion[$q_id]['field']['type'] : 'checkbox';
			//var_dump($stored_fieldType);

			foreach ( $fieldTypes as $fieldType => $text ) {

				$default_fieldtype = $fieldType;


				//if($stored_fieldType != $fieldType) continue; //it took me fucking 2/3 hours to solve, lastly I was wrong and fixed again.

				$fieldTypeSelectBox .= '<option '.selected($stored_fieldType, $default_fieldtype, false).' value="' . $fieldType . '">' . $text . '</option>';

				if ( $fieldType == 'checkbox' ) {

					$disable_class = ($stored_fieldType == $fieldType) ? '' : '  disable_field ';
					//var_dump($disable_class);

					$fieldSeperate_checkbox_stored  = isset( $customQuestion[$q_id]['field']['checkbox']['seperated']) ? $customQuestion[$q_id]['field']['checkbox']['seperated'] : 0;

					$fieldSeperate .= '
                        <div data-q-id="' . $q_id . '" class="seperated_checkbox_div seperated_checkbox_div_q_id-' . $q_id . ' seperated_field_' . $fieldType . '_div_q_id-' . $q_id .$disable_class. '" style="float:left;">
                            <div class="checkbox_holder">
                                <input '.checked($fieldSeperate_checkbox_stored, 0, false ).' id="seperated_checkbox_input_q_id-' . $q_id . '_simple_tick"  data-q-id="' . $q_id . '" class="seperated_checkbox_input seperated_checkbox_input_q_id-' . $q_id . '" type="radio" value="0" name="ratingForm[custom_question][' . $q_id . '][field][' . $fieldType . '][seperated]" />
                                <label for="seperated_checkbox_input_q_id-' . $q_id . '_simple_tick">'.__('One Answer', 'cbratingsystem').'</label>
                            </div>
                            <div class="checkbox_holder">
                                <input '.checked($fieldSeperate_checkbox_stored, 1, false ).' id="seperated_checkbox_input_q_id-' . $q_id . '_multiple"  data-q-id="' . $q_id . '" class="seperated_checkbox_input seperated_checkbox_input_q_id-' . $q_id . '" type="radio" value="1" name="ratingForm[custom_question][' . $q_id . '][field][' . $fieldType . '][seperated]" />
                                <label for="seperated_checkbox_input_q_id-' . $q_id . '_multiple">'.__('Multiple Answers','cbratingsystem').'</label>
                            </div>
                        </div>';
				}

				$method = 'admin_display_'.$fieldType.'_field';
				$hidden = true;

				if($stored_fieldType == $fieldType){
					$hidden = false;
				}


				//var_dump($hidden);


				if ( isset($customQuestion[$q_id]) &&  method_exists( 'CBRatingSystemAdminFormParts', $method ) ) {
					//var_dump('will not appear here');

					$fieldDisplay .= self::$method( $q_id, $customQuestion[$q_id], $hidden, $post );
				} else if(method_exists('CBRatingSystemAdminFormParts', $method)) {
					//var_dump('will appear here2');
                    $fieldDisplay .= self::$method( $q_id, array(), $hidden, $post );
                }
                else{
                    $fieldDisplay .= '';
				}
			}//end field types


			$fieldTypeSelectBox .= '</select>';

			$output .= '
                <!--Row #' . $rowId . '-->
                <div data-q-id="' . $q_id . '" class="div_table_row_wrapper ' . $class . '-row rowId-' . $rowId . ' form-item-custom-question ">
                    <div class="div_table div_table_row r' . $rowId . 'c1 col-odd row-' . $oddEven . '" style="">
                        <label data-q-id="' . $q_id.'" title="'.__('Click to edit','cbratingsystem').'"
                           id="question-label-' . $q_id . '" class="question-label label-q-' . $q_id . ' option mouse_normal"
                                >' . ( isset( $customQuestion[$q_id]['title'] ) ? stripslashes( $customQuestion[$q_id]['title'] ) . ' ('.__('Click to edit','cbratingsystem').')' : __('Sample Question Title','cbratingsystem').' '.( $q_id + 1 ) . '? ('.__('Click to edit','cbratingsystem').')' ) . '</label>

                        <input class="edit-custom-question-text-q edit-custom-question-text-q-'.$q_id.' disable_field" data-q-id="' . $q_id . '" type="text" id="edit-custom-question-text-q-' . $q_id . '"
                            name="ratingForm[custom_question][' . $q_id . '][title]"
                            value="' . ( isset( $customQuestion[$q_id]['title'] ) ? stripslashes( $customQuestion[$q_id]['title'] ) : __('Sample Question Title','cbratingsystem').' ' . ( $q_id + 1 ) . '?' ) . '"
                            class="form-text disable_field edit-custom-question-label-text-q-' . $q_id . '">
                    </div>

                    <div class="div_table div_table_row r' . $rowId . 'c3 col-odd row-' . $oddEven . '" style="width:12%">
                        <p>'.__('Required Question ?').'</p>
                        <label title="'.__('Yes','cbratingsystem').'"><input '.checked(isset( $customQuestion[$q_id]['required'] ) ? intval($customQuestion[$q_id]['required'] ): 0 , 1, $echo = false ).' id="edit-custom-question-require-q-' . $q_id . '-1" type="radio" name="ratingForm[custom_question][' . $q_id . '][required]" value="1"> <span>'.__('Yes','cbratingsystem').'</span></label><br>
						<label title="'.__('No','cbratingsystem').'"><input '.checked(isset( $customQuestion[$q_id]['required'] ) ? intval($customQuestion[$q_id]['required'] ): 0, 0, $echo = false  ).' id="edit-custom-question-require-q-' . $q_id . '-0" type="radio" name="ratingForm[custom_question][' . $q_id . '][required]" value="0"> <span>'.__('No','cbratingsystem').'</span></label>
						<p>'.__('Show Question ?').'</p>
                        <label title="'.__('Yes','cbratingsystem').'"><input '.checked(isset( $customQuestion[$q_id]['enabled'] ) ? intval($customQuestion[$q_id]['enabled'] ): 0 , 1, $echo = false ).' id="edit-custom-question-enabled-q-' . $q_id . '-1" type="radio" name="ratingForm[custom_question][' . $q_id . '][enabled]" value="1"> <span>'.__('Yes','cbratingsystem').'</span></label><br>
						<label title="'.__('No','cbratingsystem').'"><input '.checked(isset( $customQuestion[$q_id]['enabled'] ) ? intval($customQuestion[$q_id]['enabled'] ): 0, 0, $echo = false  ).' id="edit-custom-question-enabled-q-' . $q_id . '-0" type="radio" name="ratingForm[custom_question][' . $q_id . '][enabled]" value="0"> <span>'.__('No','cbratingsystem').'</span></label>
                    </div>
                    <div class="div_table div_table_row r' . $rowId . 'c4 col-even row-' . $oddEven . '" style="width:21%">
                        ' . $fieldTypeSelectBox . '
                        ' . $fieldSeperate . '
                    </div>
                    <div class="div_table div_table_row r' . $rowId . 'c5 col-odd row-' . $oddEven . '" style="">
                        ' . $fieldDisplay . '
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>
            ';
		}
		$output .= '
            </div>';

        //pro version: shows add new question button, free version: shows info
		$cbrating_question_msg = __('<p>Info: Unlimited Question available in pro version.</p>','cbratingsystem');
		$cbrating_question_msg = apply_filters('cbratingsystem_add_more_question', $cbrating_question_msg , $qs_count);

        $output .= $cbrating_question_msg;

        $output .= '
            </div>';

		return $output;
	}

    /**
     * @param      $questionId
     * @param      $questionOption
     * @param      $post
     * @param bool $hidden
     *
     * @return string
     */
    public static function admin_display_checkbox_field( $questionId = 0, $questionOption, $hidden = true, $post ) {

        $defaultCheckboxCount   = 5;
	    //$seperated              = isset( $questionOption['field']['checkbox']['seperated']) ? intval( $questionOption['field']['checkbox']['seperated']) : 0;

	    //var_dump('seperated = '.$seperated);

		//echo '<pre>'; print_r($questionOption); echo '</pre>'; //die();
		//( ($post['enable_question'])? (($post['enable_question']==1)? 'checked ' : '') : ((isset($RFSettings->enable_question)) ? (($RFSettings->enable_question == 1) ? 'checked ': '') : 'checked ') )
		//$selected    = ( isset( $post['custom_question'][$questionId]['field']['checkbox']['count'] ) ? $post['custom_question'][$questionId]['field']['checkbox']['count'] : ( isset( $questionOption['field']['checkbox']['count'] ) ? $questionOption['field']['checkbox']['count'] : 2 ) );
	    $selected    = isset( $questionOption['field']['checkbox']['count'] ) ? $questionOption['field']['checkbox']['count'] : 2;
		//$selectClass = ( ( isset( $post['custom_question'][$questionId]['field']['checkbox']['seperated'] ) ) ? ( ( $post['custom_question'][$questionId]['field']['checkbox']['seperated'] == 0 ) ? 'disable_field' : '' ) : ( ( isset( $questionOption['field']['checkbox']['seperated'] ) && ( $questionOption['field']['checkbox']['seperated'] == 0 ) ) ? 'disable_field' : '' ) );
	    $selectClass = (isset( $questionOption['field']['checkbox']['seperated']) && ($questionOption['field']['checkbox']['seperated'] == 0)) ? 'disable_field' : '';

	    //var_dump($selected);
	    //var_dump($selectClass);

		$dummyTextArr = array(
			0 => __( 'Yes', 'cbratingsystem' ),
			1 => __( 'No', 'cbratingsystem' ),
			2 => __( 'Correct', 'cbratingsystem' ),
			3 => __( 'Incorrect', 'cbratingsystem' ),
			4 => __( 'None', 'cbratingsystem' ),
		);


        $select = '';
		if ( ( $defaultCheckboxCount > 1 ) && ( count( $dummyTextArr ) >= $defaultCheckboxCount ) ) {

			for ( $chkSelect = 1; ( $defaultCheckboxCount + 1 ) > $chkSelect; $chkSelect ++ ) {
				$select .= '<option '.selected($selected, $chkSelect, false).' value="' . $chkSelect . '">' . $chkSelect . '</option>';
			}
		}

		$output = '';
		$output .= '
            <div data-q-id="' . $questionId . '" class="form_item form_item_q_id-' . $questionId . ' form_item_checkbox form_item_field_display form_item_checkbox_q_id-' . $questionId . ' ' . ( ( $hidden === true ) ? 'disable_field' : '' ) . '" style="float:left;">
                <div data-q-id="' . $questionId . '" class="select_checkbox_count_div select_checkbox_count_div_q_id-' . $questionId . '">
                    <select data-q-id="' . $questionId . '" class="form_item form_select ' . $selectClass . ' select_count select_checkbox_count select_checkbox_count_q_id-' . $questionId . '"
                        name="ratingForm[custom_question][' . $questionId . '][field][checkbox][count]">
                        ' . $select . '
                    </select>
                    <label class=" ' . $selectClass . '"> options</label>
                </div>
        ';

		$checkboxCount = $defaultCheckboxCount;

	    //default one asnwer, if separated then value 1, still field name has spelling mistake that we are not solving.
	    $multiplecheck = isset( $questionOption['field']['checkbox']['seperated']) ? intval($questionOption['field']['checkbox']['seperated']) : 0; //single checkbox is default and value is 0

	   // var_dump($multiplecheck);

		for ( $checkboxId = 0; $checkboxId < $checkboxCount; $checkboxId ++ ) {

			$editable       = '1';
			$class          = '';
			$radio_style    = '';
			$label          = '';


			if( $multiplecheck ) {
				if($checkboxId >= $selected){
					$class          = 'disable_field';
					$radio_style    = 'display: none;';
					$editable       = '0';
				}
				else{
					$class          = '';
					$radio_style    = 'display: block;';
					$editable       = '1';
				}

				$label    = ( isset( $questionOption['field']['checkbox'][$checkboxId]['text'] ) ? stripslashes( $questionOption['field']['checkbox'][$checkboxId]['text'] ) : $dummyTextArr[ $checkboxId ] );


			}
			else{
				$editable = false;
				$label    = isset($questionOption['title']) ? $questionOption['title'] : __('Sample Question Title 1', 'cbratingsystem');
				if ( $checkboxId > 0 ) {

					$class          = 'disable_field';
					$radio_style    = 'display: none;';
					$label    = ( isset( $questionOption['field']['checkbox'][$checkboxId]['text'] ) ? stripslashes( $questionOption['field']['checkbox'][$checkboxId]['text'] ) : $dummyTextArr[ $checkboxId ] );

				}
			}

			$output .= '
                <div style="'.$radio_style.'" data-checkbox-field-text-id="' . $checkboxId . '" data-q-id="' . $questionId . '" class="' . $class . ' field_display_checkbox_div field_display_checkbox_div_q_id-' . $questionId . ' field_display_checkbox_div_q_id-' . $questionId . '_field_id-' . $checkboxId . '">
                    <input type="checkbox" name="" value="1" class="form-checkbox">

                    <input labelEditable="' . $editable . '" data-checkbox-field-text-id="' . $checkboxId . '" data-q-id="' . $questionId . '" type="text" id="edit-custom-question-checkbox-field-text-' . $checkboxId . '-q-' . $questionId . '"
                        name="ratingForm[custom_question][' . $questionId . '][field][checkbox][' . $checkboxId . '][text]" style=""
                        value="' . $label . '"
                        class="form-text edit_field_display_input_label disable_field edit-custom-question-field-checkbox-q-id-' . $questionId . ' edit-custom-question-field-checkbox-' . $checkboxId . '-label-text-q-' . $questionId . '">

                    <label labelEditable="' . $editable . '" data-checkbox-field-text-id="' . $checkboxId . '" data-q-id="' . $questionId . '" title="Click to edit"
                        class="question-field-label question-field-checkbox-label label-q-' . $questionId . '-checkbox-' . $checkboxId . ' option mouse_normal"
                            >' . $label . '</label>
                </div>
            ';
		}//end each checkbox

		$output .= '</div>';

		//return '-- chk --'.$output.'-- chk --';
	    return $output;
	}

    /**
     * @param      $questionId
     * @param      $questionOption
     * @param      $post
     * @param bool $hidden
     *
     * @return string
     */
    public static function admin_display_radio_field( $questionId = 0, $questionOption, $hidden = true, $post ) {
	    //note if field type is radio there will be no calculation for "seperated"
		//$defaultRadiosCount = 4;
        $select = '';
        $output = '';

		$selected    = isset( $questionOption['field']['radio']['count'] ) ? $questionOption['field']['radio']['count'] : 2;

	    //$selectClass = ( ( isset( $post['custom_question'][$questionId]['field']['radio']['seperated'] ) ) ? ( ( $post['custom_question'][$questionId]['field']['radio']['seperated'] == 0 ) ? 'disable_field' : '' ) : ( ( isset( $questionOption['field']['radio']['seperated'] ) && ( $questionOption['field']['radio']['seperated'] == 0 ) ) ? 'disable_field' : '' ) );
	    $selectClass = '';


		$radio_arr = array(
			0 => __( 'Yes', 'cbratingsystem' ),
			1 => __( 'No', 'cbratingsystem' ),
			2 => __( 'Correct', 'cbratingsystem' ),
			3 => __( 'Incorrect', 'cbratingsystem' ),
			//4 => __( 'None', 'cbratingsystem' ),
		);

	    $radio_count = count($radio_arr); //default count is 4

		//if ( ( $defaultRadiosCount > 1 ) && ( count( $dummyTextArr ) >= $defaultRadiosCount ) ) {
			for ( $chkSelect = 2; $chkSelect <= $radio_count;  $chkSelect ++ ) {
				$select .= '<option '.selected($selected, $chkSelect, false).' value="' . $chkSelect . '">' . $chkSelect . '</option>';
			}
		//}


		$output .= '
            <div data-q-id="' . $questionId . '" class="form_item form_item_q_id-' . $questionId . ' form_item_radio form_item_field_display form_item_radio_q_id-' . $questionId . ' ' . ( ( $hidden === true ) ? 'disable_field' : '' ) . '" style="float:left;">
                <div data-q-id="' . $questionId . '" class="select_radio_count_div select_radio_count_div_q_id-' . $questionId . '">
                    <select data-q-id="' . $questionId . '" class="form_item form_select ' . $selectClass . ' select_count select_radio_count select_radio_count_q_id-' . $questionId . '"
                        name="ratingForm[custom_question][' . $questionId . '][field][radio][count]">
                        ' . $select . '
                    </select>
                    <label class=" ' . $selectClass . '"> options</label>
                </div>
        ';

		//$radioCount = $defaultRadiosCount;

		for ( $radioId = 0; $radioId < $radio_count; $radioId ++ ) {

			$editable       = '1';
			$class          = '';
			$radio_style    = '';

			if($radioId >= $selected){
				$class          = 'disable_field';
				$radio_style    = 'display: none;';
			}
			else{
				$class          = '';
				$radio_style    = 'display: block;';
			}



			$label    = ( isset( $questionOption['field']['radio'][$radioId]['text'] ) ? stripslashes( $questionOption['field']['radio'][$radioId]['text'] ) : $radio_arr[$radioId] );

			//var_dump('4m rdo label '.$radioId.'='.$label.'<br/>');

			$output .= '
                <div style="'.$radio_style.'" data-radio-field-text-id="' . $radioId . '" data-q-id="' . $questionId . '" class="' . $class . ' field_display_radio_div field_display_radio_div_q_id-' . $questionId . ' field_display_radio_div_q_id-' . $questionId . '_field_id-' . $radioId . '">
                    <input type="radio" name="" value="1" class="form-radio">
                    <label labelEditable="' . $editable . '" data-radio-field-text-id="' . $radioId . '" data-q-id="' . $questionId . '" title="Click to edit"
                        class="question-field-label question-field-radio-label label-q-' . $questionId . '-radio-' . $radioId . ' option mouse_normal"
                            >' . $label . '</label>
                    <input labelEditable="' . $editable . '" data-radio-field-text-id="' . $radioId . '" data-q-id="' . $questionId . '" type="text" id="edit-custom-question-radio-field-text-' . $radioId . '-q-' . $questionId . '"
                        name="ratingForm[custom_question][' . $questionId . '][field][radio][' . $radioId . '][text]" style=""
                        value="' . $label . '"
                        class="form-text edit_field_display_input_label disable_field edit-custom-question-field-radio-q-id-' . $questionId . ' edit-custom-question-field-radio-' . $radioId . '-label-text-q-' . $questionId . '">
                </div>
            ';
		}//end each radio loop
		$output .= '    </div>';

	   // return '-- rdo --'.$output.'-- rdo --';
	   return $output;
	}

    /**
     * @param      $questionId
     * @param      $questionOption
     * @param      $post
     * @param bool $hidden
     *
     * @return string
     */
    public static function admin_display_text_field( $questionId = 0, $questionOption, $hidden = true, $post ) {

	    //var_dump($hidden);

		$output = '';
		$output .= '
            <div data-q-id="' . $questionId . '" class="form_item form_item_q_id-' . $questionId . ' form_item_text form_item_field_display form_item_text_q_id-' . $questionId . ' ' . ( ( $hidden === true ) ? 'disable_field' : '' ) . '">
                <input type="text" value="" />
            </div>
        ';

	    //return '-- txt --'.$output.'-- txt --';
	    return $output;
	}
}