<?php

/**
 * Class CBRatingSystemAdminFormParts
 */
class CBRatingSystemAdminFormParts extends CBRatingSystemAdmin {

	/*
	 * Got the div based table from: http://forum.weavertheme.com/discussion/3200/div-based-table-generator
	 */
	function admin_ratingForm_edit_page_custom_question( $RFSettings = array() ) {
		//echo '<pre>'; print_r($RFSettings); echo '</pre>'; die();

		//$form_id = (!empty($RFSettings) and isset($RFSettings['rfid'])) ? $RFSettings['rfid'] : self::get_next_ratingForm_id();
		$post = $_POST['ratingForm'];
		if ( isset( $_POST['ratingForm'] ) and ( ! empty( $_POST['ratingForm'] ) ) ) {
			$question_enabled = $_POST['ratingForm']['enable_question'];
			$customQuestion   = $_POST['ratingForm']['custom_question'];
		} else {
			$question_enabled = $RFSettings->enable_question;
			$customQuestion   = ( $RFSettings->custom_question ) ? $RFSettings->custom_question['enabled'] : array();
		}
		//echo '<pre>'; var_dump($customQuestion); echo '</pre>'; die();
		//echo '<pre>'; var_dump(( isset($_POST['ratingForm']['enable_question'])? (($question_enabled==1)?'checked ':'') : 'Notchecked ' )); echo '</pre>'; //die();

		$fieldTypes = array( 'checkbox' => __( 'Checkbox', 'cbratingsystem' ), 'radio' => __( 'Radio', 'cbratingsystem' ), 'text' => __( 'Textbox', 'cbratingsystem' ) );

		$output = '';
		$output .= '
            <div class="form-item form-type-checkbox form-item-question-enabled" id="enable-question">
                <input type="checkbox" id="edit-question-enabled" name="ratingForm[enable_question]" value="1" ' . ( ( $post ) ? ( ( $post['enable_question'] == 1 ) ? 'checked ' : '' ) : ( ( isset( $RFSettings->enable_question ) ) ? ( ( $RFSettings->enable_question == 1 ) ? 'checked ' : '' ) : 'checked ' ) ) . 'class="form-checkbox">
                <label class="option" for="edit-question-enabled">Enable question?</label>
            </div>
        ';
		$output .= '
        <div class="cb-ratingForm-edit-custom-question-container form-item" id="custom-question">
            <label class="" for="edit-custom-question">Custom Questions</label>
            <div class="edit-custom-criteria-fields-wrapper div_table_wrapper">
                <!--Row #0-->
                <div class="div_table_row_header_wrapper div_table_row_wrapper rowId-0">
                    <div class="div_table div_table_header r0c1 col-odd row-odd header" style="">' . __( 'Question', 'cbratingsystem' ) . '</div>
                    <div class="div_table div_table_header r0c2 col-even row-odd header" style="width:12%">' . __( 'Show/Hide', 'cbratingsystem' ) . '</div>
                    <div class="div_table div_table_header r0c3 col-odd row-odd header" style="width:12%">' . __( 'Is Required?', 'cbratingsystem' ) . '</div>
                    <div class="div_table div_table_header r0c4 col-even row-odd header" style="width:21%">' . __( 'Field Type', 'cbratingsystem' ) . '</div>
                    <div class="div_table div_table_header r0c5 col-odd row-odd header" style="">' . __( 'Field Dispaly', 'cbratingsystem' ) . '</div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>
            ';
        if(is_array($customQuestion) && !empty($customQuestion)){
            $qs_count = count($customQuestion);
        }
        else{
            $qs_count = 3;
            $qs_count = apply_filters('ratingsystem_question_number',3);
        }

		for ( $q_id = 0; $q_id <$qs_count ; $q_id ++ ) {

            if ( $q_id == 9 ) {
				$class = 'last';
			} elseif ( $q_id == 0 ) {
				$class = 'first';
			} else {
				$class = 'other';
			}

			$rowId   = $q_id + 1;
			$oddEven = ( $rowId % 2 ) ? 'odd' : 'even';

			$method = 'display_' . $customQuestion[$q_id]['field']['type'] . '_field';
			if ( method_exists( 'CBRatingSystemAdminFormParts', $method ) ) {

                $fieldDisplay = self::$method( $q_id, $customQuestion[$q_id], $post );

            } else {

					$fieldDisplay = '';
			}

			//echo '<pre>'.$q_id.': '; var_dump(( (isset($customQuestion[$q_id]['field']['checkbox']['seperated']) and ($customQuestion[$q_id]['field']['checkbox']['seperated'] == '1')) ? 'checked' : '')); echo '</pre>'; //die();
			//echo '<pre>'.$q_id.': '; var_dump(( (isset($customQuestion[$q_id]['field']['checkbox']['seperated']) and ($customQuestion[$q_id]['field']['checkbox']['seperated'] == '0')) ? 'checked' : '')); echo '</pre>'; //die();

			$fieldSeperate      = '';
			$fieldTypeSelectBox = '<select data-q-id="' . $q_id . '" name="ratingForm[custom_question][' . $q_id . '][field][type]" class="question_field_type question_field_type_q_id-' . $q_id . '">';
			//$fieldTypeSelectBox .= '<option value="none">Field type..</option>';
			foreach ( $fieldTypes as $fieldType => $text ) {

                if ( $fieldType == $customQuestion[$q_id]['field']['type'] ) {
					$fieldTypeSelectBox .= '<option selected value="' . $fieldType . '">' . $text . '</option>';
				} else {
					$fieldTypeSelectBox .= '<option value="' . $fieldType . '">' . $text . '</option>';
				}

				if ( $fieldType == 'checkbox' ) {
					$fieldSeperate .= '
                        <div data-q-id="' . $q_id . '" class="seperated_checkbox_div seperated_checkbox_div_q_id-' . $q_id . ' seperated_field_' . $fieldType . '_div_q_id-' . $q_id . ' disable_field" style="float:left;">
                            <div class="checkbox_holder">
                                <input id="seperated_checkbox_input_q_id-' . $q_id . '_simple_tick" ' . ( ( isset( $customQuestion[$q_id]['field']['checkbox']['seperated'] ) and ( $customQuestion[$q_id]['field']['checkbox']['seperated'] == 0 ) ) ? 'checked' : '' ) . ' data-q-id="' . $q_id . '" class="seperated_checkbox_input seperated_checkbox_input_q_id-' . $q_id . '" type="radio" value="0" name="ratingForm[custom_question][' . $q_id . '][field][' . $fieldType . '][seperated]" />
                                <label for="seperated_checkbox_input_q_id-' . $q_id . '_simple_tick">Simple Tick</label>
                            </div>
                            <div class="checkbox_holder">
                                <input id="seperated_checkbox_input_q_id-' . $q_id . '_multiple" ' . ( ( isset( $customQuestion[$q_id]['field']['checkbox']['seperated'] ) and ( $customQuestion[$q_id]['field']['checkbox']['seperated'] == 1 ) ) ? 'checked' : ( ! isset( $customQuestion[$q_id]['field']['checkbox']['seperated'] ) ? 'checked' : '' ) ) . ' data-q-id="' . $q_id . '" class="seperated_checkbox_input seperated_checkbox_input_q_id-' . $q_id . '" type="radio" value="1" name="ratingForm[custom_question][' . $q_id . '][field][' . $fieldType . '][seperated]" />
                                <label for="seperated_checkbox_input_q_id-' . $q_id . '_multiple">Multiple Tick</label>
                            </div>
                        </div>';
				}
				$method = 'admin_display_' . $fieldType . '_field';
				if ( method_exists( 'CBRatingSystemAdminFormParts', $method ) ) {
					$fieldDisplay .= self::$method( $q_id, $customQuestion[$q_id], $post, true );
				} else {
					$fieldDisplay .= '';
				}
			}
			$fieldTypeSelectBox .= '</select>';

			$output .= '
                <!--Row #' . $rowId . '-->
                <div data-q-id="' . $q_id . '" class="div_table_row_wrapper ' . $class . '-row rowId-' . $rowId . ' form-item-custom-question ">
                    <div class="div_table div_table_row r' . $rowId . 'c1 col-odd row-' . $oddEven . '" style="">
                        <label data-q-id="' . $q_id . '" title="Click to edit"
                            class="question-label label-q-' . $q_id . ' option mouse_normal"
                                >' . ( ( $customQuestion[$q_id]['title'] ) ? stripslashes( $customQuestion[$q_id]['title'] ) . ' (Click to edit)' : 'Sample question ' . ( $q_id + 1 ) . '? (Click to edit)' ) . '</label>

                        <input data-q-id="' . $q_id . '" type="text" id="edit-custom-question-text-q-' . $q_id . '"
                            name="ratingForm[custom_question][' . $q_id . '][title]"
                            value="' . ( ( $customQuestion[$q_id]['title'] ) ? stripslashes( $customQuestion[$q_id]['title'] ) : 'Sample question ' . ( $q_id + 1 ) . '?' ) . '"
                            class="form-text disable_field edit-custom-question-label-text-q-' . $q_id . '">
                    </div>
                    <div class="div_table div_table_row r' . $rowId . 'c2 col-even row-' . $oddEven . '" style="width:12%">
                        <input type="checkbox" id="edit-custom-question-enable-q-' . $q_id . '"
                            name="ratingForm[custom_question][' . $q_id . '][enabled]"
                            value="1" ' . ( isset( $customQuestion[$q_id]['enabled'] ) ? ( ( $customQuestion[$q_id]['enabled'] == 1 ) ? 'checked ' : '' ) : '' ) . 'class="form-checkbox">
                    </div>
                    <div class="div_table div_table_row r' . $rowId . 'c3 col-odd row-' . $oddEven . '" style="width:12%">
                        <input type="checkbox" id="edit-custom-question-require-q-' . $q_id . '"
                            name="ratingForm[custom_question][' . $q_id . '][required]"
                            value="1" ' . ( isset( $customQuestion[$q_id]['required'] ) ? ( ( $customQuestion[$q_id]['required'] == 1 ) ? 'checked ' : '' ) : '' ) . 'class="form-checkbox">
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

        $cbrating_question_msg = apply_filters('cbratingsystem_add_more_question',array('<p>To add unlimited Question buy our premium version</p>',$qs_count));
        $output .= $cbrating_question_msg[0];
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
    public static function admin_display_checkbox_field( $questionId, $questionOption, $post, $hidden = false ) {

        $defaultCheckboxCount = 5;
		//echo '<pre>'; print_r($questionOption); echo '</pre>'; //die();
		//( ($post['enable_question'])? (($post['enable_question']==1)? 'checked ' : '') : ((isset($RFSettings->enable_question)) ? (($RFSettings->enable_question == 1) ? 'checked ': '') : 'checked ') )
		$selected    = ( isset( $post['custom_question'][$questionId]['field']['checkbox']['count'] ) ? $post['custom_question'][$questionId]['field']['checkbox']['count'] : ( ( $questionOption['field']['checkbox']['count'] ) ? $questionOption['field']['checkbox']['count'] : 2 ) );
		$selectClass = ( ( isset( $post['custom_question'][$questionId]['field']['checkbox']['seperated'] ) ) ? ( ( $post['custom_question'][$questionId]['field']['checkbox']['seperated'] == 0 ) ? 'disable_field' : '' ) : ( ( isset( $questionOption['field']['checkbox']['seperated'] ) and ( $questionOption['field']['checkbox']['seperated'] == 0 ) ) ? 'disable_field' : '' ) );

		$dummyTextArr = array(
			1 => __( 'Yes', 'cbratingsystem' ),
			2 => __( 'No', 'cbratingsystem' ),
			3 => __( 'Correct', 'cbratingsystem' ),
			4 => __( 'Incorrect', 'cbratingsystem' ),
			5 => __( 'None', 'cbratingsystem' ),
		);

		if ( ( $defaultCheckboxCount > 1 ) and ( count( $dummyTextArr ) >= $defaultCheckboxCount ) ) {

			for ( $chkSelect = 1; ( $defaultCheckboxCount + 1 ) > $chkSelect; $chkSelect ++ ) {
				if ( $chkSelect == $selected ) {
					$select .= '<option selected value="' . $chkSelect . '">' . $chkSelect . '</option>';
				}
                else {
					$select .= '<option value="' . $chkSelect . '">' . $chkSelect . '</option>';
				}
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

		for ( $checkboxId = 0; $checkboxId < $checkboxCount; $checkboxId ++ ) {
			if ( $selected > $checkboxId ) {
				$class = '';
			} else {
				$class = 'disable_field';
			}

			//echo '<pre>'; var_dump(($questionOption['field']['checkbox'][$checkboxId]['text'])); echo '</pre>'; //die();

			if ( isset( $post['custom_question'][$questionId]['field']['checkbox']['seperated'] ) ) {
				if ( ( $post['custom_question'][$questionId]['field']['checkbox']['seperated'] == '0' ) ) {
				    //var_dump($checkboxId);
                   if ( $checkboxId > 0 ) {
						$class    = 'disable_field';
						$editable = 'true';
						$label    = ( ( $post['custom_question'][$questionId]['field']['checkbox'][$checkboxId]['text'] ) ? stripslashes( $post['custom_question'][$questionId]['field']['checkbox'][$checkboxId]['text'] ) : $dummyTextArr[( $checkboxId + 1 )] );
					} else {
						$editable = 'false';
						$label    = substr( $questionOption['title'], 0, 10 ) . '...';
					}
				} elseif ( ( $post['custom_question'][$questionId]['field']['checkbox']['seperated'] == '1' ) ) {
					$class    = 'disable_field';
					$editable = 'true';
					$label    = ( ( $post['custom_question'][$questionId]['field']['checkbox'][$checkboxId]['text'] ) ? stripslashes( $post['custom_question'][$questionId]['field']['checkbox'][$checkboxId]['text'] ) : $dummyTextArr[( $checkboxId + 1 )] );
				}
			} else {
				if ( isset( $questionOption['field']['checkbox']['seperated'] ) and ( $questionOption['field']['checkbox']['seperated'] == '0' ) ) {
					if ( $checkboxId > 0 ) {
						$class    = 'disable_field';
						$editable = 'true';
						$label    = ( ( $questionOption['field']['checkbox'][$checkboxId]['text'] ) ? stripslashes( $questionOption['field']['checkbox'][$checkboxId]['text'] ) : $dummyTextArr[( $checkboxId + 1 )] );
					} else {
						$editable = 'false';
						$label    = substr( $questionOption['title'], 0, 10 ) . '...';
					}
				} else {
					$editable = 'true';
					$label    = ( ( $questionOption['field']['checkbox'][$checkboxId]['text'] ) ? stripslashes( $questionOption['field']['checkbox'][$checkboxId]['text'] ) : $dummyTextArr[( $checkboxId + 1 )] );
					//echo '<pre>'; print_r($label); echo '</pre>'; //die();
				}
			}

			$output .= '
                <div data-checkbox-field-text-id="' . $checkboxId . '" data-q-id="' . $questionId . '" class="' . $class . ' field_display_checkbox_div field_display_checkbox_div_q_id-' . $questionId . ' field_display_checkbox_div_q_id-' . $questionId . '_field_id-' . $checkboxId . '">
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
		}
		$output .= '</div>';

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
    public static function admin_display_radio_field( $questionId, $questionOption, $post, $hidden = false ) {
		$defaultRadiosCount = 5;

		$selected    = ( isset( $post['custom_question'][$questionId]['field']['radio']['count'] ) ? $post['custom_question'][$questionId]['field']['radio']['count'] : ( ( $questionOption['field']['radio']['count'] ) ? $questionOption['field']['radio']['count'] : 2 ) );
		$selectClass = ( ( isset( $post['custom_question'][$questionId]['field']['radio']['seperated'] ) ) ? ( ( $post['custom_question'][$questionId]['field']['radio']['seperated'] == 0 ) ? 'disable_field' : '' ) : ( ( isset( $questionOption['field']['radio']['seperated'] ) and ( $questionOption['field']['radio']['seperated'] == 0 ) ) ? 'disable_field' : '' ) );

		$dummyTextArr = array(
			1 => __( 'Yes', 'cbratingsystem' ),
			2 => __( 'No', 'cbratingsystem' ),
			3 => __( 'Correct', 'cbratingsystem' ),
			4 => __( 'Incorrect', 'cbratingsystem' ),
			5 => __( 'None', 'cbratingsystem' ),
		);

		if ( ( $defaultRadiosCount > 1 ) and ( count( $dummyTextArr ) >= $defaultRadiosCount ) ) {

			for ( $chkSelect = 1; ( $defaultRadiosCount + 1 ) > $chkSelect; $chkSelect ++ ) {
				if ( $chkSelect == $selected ) {
					$select .= '<option selected value="' . $chkSelect . '">' . $chkSelect . '</option>';
				} else {
					$select .= '<option value="' . $chkSelect . '">' . $chkSelect . '</option>';
				}
			}
		}

		$output = '';
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

		$radioCount = $defaultRadiosCount;

		for ( $radioId = 0; $radioId < $radioCount; $radioId ++ ) {
			if ( $selected > $radioId ) {
				$class = '';
			} else {
				$class = 'disable_field';
			}

			//echo '<pre>'; var_dump(($questionOption['field']['radio'][$radioId]['text'])); echo '</pre>'; //die();

			if ( isset( $post['custom_question'][$questionId]['field']['radio']['seperated'] ) ) {
				if ( ( $post['custom_question'][$questionId]['field']['radio']['seperated'] == '0' ) ) {
					if ( $radioId > 0 ) {
						$class    = 'disable_field';
						$editable = 'true';
						$label    = ( ( $post['custom_question'][$questionId]['field']['radio'][$radioId]['text'] ) ? stripslashes( $post['custom_question'][$questionId]['field']['radio'][$radioId]['text'] ) : $dummyTextArr[( $radioId + 1 )] );
					} else {
						$editable = 'false';
						$label    = substr( $questionOption['title'], 0, 10 ) . '...';
					}
				} elseif ( ( $post['custom_question'][$questionId]['field']['radio']['seperated'] == '1' ) ) {
					$class    = 'disable_field';
					$editable = 'true';
					$label    = ( ( $post['custom_question'][$questionId]['field']['radio'][$radioId]['text'] ) ? stripslashes( $post['custom_question'][$questionId]['field']['radio'][$radioId]['text'] ) : $dummyTextArr[( $radioId + 1 )] );
				}
			} else {
				if ( isset( $questionOption['field']['radio']['seperated'] ) and ( $questionOption['field']['radio']['seperated'] == '0' ) ) {
					if ( $radioId > 0 ) {
						$class    = 'disable_field';
						$editable = 'true';
						$label    = ( ( $questionOption['field']['radio'][$radioId]['text'] ) ? stripslashes( $questionOption['field']['radio'][$radioId]['text'] ) : $dummyTextArr[( $radioId + 1 )] );
					} else {
						$editable = 'false';
						$label    = substr( $questionOption['title'], 0, 10 ) . '...';
					}
				} else {
					$editable = 'true';
					$label    = ( ( $questionOption['field']['radio'][$radioId]['text'] ) ? stripslashes( $questionOption['field']['radio'][$radioId]['text'] ) : $dummyTextArr[( $radioId + 1 )] );
					//echo '<pre>'; print_r($label); echo '</pre>'; //die();
				}
			}

			$output .= '
                <div data-radio-field-text-id="' . $radioId . '" data-q-id="' . $questionId . '" class="' . $class . ' field_display_radio_div field_display_radio_div_q_id-' . $questionId . ' field_display_radio_div_q_id-' . $questionId . '_field_id-' . $radioId . '">
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
		}
		$output .= '    </div>';

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
    public static function admin_display_text_field( $questionId, $questionOption, $post, $hidden = false ) {
		$output = '';
		$output .= '
            <div data-q-id="' . $questionId . '" class="form_item form_item_q_id-' . $questionId . ' form_item_text form_item_field_display form_item_text_q_id-' . $questionId . ' ' . ( ( $hidden === true ) ? 'disable_field' : '' ) . '">
                <input type="text" value="" />
            </div>
        ';

		return $output;
	}
}