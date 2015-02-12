<?php

class CBRatingSystemFrontReview extends CBRatingSystemFront {

	/**
	 * @param array $ratingFormArray
	 * @param       $postid
	 * @param int   $page
	 *
	 * @return string
	 */
	public static function rating_reviews( $ratingFormArray = array(), $postid, $page = 1 ) {
		//var_dump('starting from here');

		if ( empty( $ratingFormArray ) ) {
			$defaultFormId  = get_option( 'cbratingsystem_defaultratingForm' );
			$form_id        = apply_filters( 'rating_form_array', $defaultFormId );

			$ratingFormArray = CBRatingSystemData::get_ratingForm( $form_id );
		}

		//note followed this simple example, it works
		if($page <= 0) $page = 1;

		//var_dump($ratingFormArray['review_limit']);

		$perpage     = (isset($ratingFormArray['review_limit'])  && intval($ratingFormArray['review_limit']) > 0 )? intval($ratingFormArray['review_limit']): 10;

		//$start = ($page - 1)*$perpage;

		$theme_key  = get_option( 'cbratingsystem_theme_key' );

		//$reviewOptions['limit']['start']    = $start;
		$reviewOptions['limit']['perpage']  = $perpage;
		$reviewOptions['limit']['page']     = $page;

		$reviewOptions['theme']             = $theme_key;
		$reviewOptions['post_id']           = $postid;

		//cbxdump($reviewOptions);

		$output = self::build_user_rating_review( $reviewOptions, $ratingFormArray );

		return $output;
	}


	/**
	 * [rating_reviews_shorttag description]
	 *
	 * @param  array   $ratingFormArray
	 * @param  integer $postid
	 * @param  integer $start
	 *
	 * @return string
	 */
	public static function rating_reviews_shorttag( $ratingFormArray = array(), $postid, $page = 1 ) {
		//this function needs take care

		if ( empty( $ratingFormArray ) ) {

			$defaultFormId  = get_option( 'cbratingsystem_defaultratingForm' );
			$form_id        = apply_filters( 'rating_form_array', $defaultFormId );
			$ratingFormArray = CBRatingSystemData::get_ratingForm( $form_id );
		}

		if($page <= 0) $page = 1;

		//$offset = ( $start + $ratingFormArray['review']['review_limit'] );
		$perpage     = (isset($ratingFormArray['review_limit'])  && intval($ratingFormArray['review_limit']) > 0 )? intval($ratingFormArray['review_limit']): 10;
		//$theme_key = get_option('cbratingsystem_theme_key');

		$reviewOptions['limit']['page']         = $page;
		$reviewOptions['limit']['perpage']      = $perpage;
		//$reviewOptions['limit']['offset']       = $ratingFormArray['review']['review_limit'];
		$reviewOptions['theme']                 = $ratingFormArray['theme_key'];
		$reviewOptions['post_id']               = $postid;
		$reviewOptions['form_id']               = $ratingFormArray['id'];

		$output = self::build_user_rating_review( $reviewOptions, $ratingFormArray );
		return $output;
	}

    /**
     *
     */
    public static function cbReviewAjaxFunction() {
		if ( isset( $_POST['cbReviewData'] ) and ! empty( $_POST['cbReviewData'] ) ) {
			$returnedData = $_POST['cbReviewData'];
			//echo '<pre>CBR:'; print_r($returnedData); echo '</pre>'; //die();
			if ( wp_verify_nonce( $returnedData['nonce'], 'cb_ratingForm_front_review_nonce_field' ) ) {
				//echo '<pre>CBR:'; print_r($returnedData); echo '</pre>'; //die();
				$theme_key = get_option( 'cbratingsystem_theme_key' );

				$option['form_id']          = $returnedData['ratingFormID'];
				$option['post_id']          = $returnedData['postID'];
				$option['theme']            = $theme_key;

				//limit
				$option['limit']['page']    = $returnedData['page']+1; //go for next page, increase page value by 1
				$option['limit']['perpage'] = $returnedData['perpage'];
				//$option['limit']['end']     = ( $returnedData['start'] + $returnedData['offset'] );


				$ratingFormArray = CBRatingSystemData::get_ratingForm( $returnedData['ratingFormID'] );

				//echo '<pre>CBR:'; print_r($option); echo '</pre>'; die();
				//$reviewOptions = array(), $ratingFormArray = array(), $ajax = false
				$results = self::build_user_rating_review( $option, $ratingFormArray, true );

				$encoded = json_encode( $results );
				//echo '<pre>CBR:'; print_r($results); echo '</pre>'; die();

				echo $encoded;

			}
		}

		die();
	}

    /**
     * @param $comment_status
     * @param $session
     * @param $ip
     *
     * @return bool
     */
    public static function check_cpmment_status ($comment_status, $session, $ip){
        global $current_user, $wpdb;

        $show_own_review = false;
        $comment_show    = false;
        $user_session    = '';
        $user_ip         = '';

        $user_id = get_current_user_id();
        if ( $user_id == 0 ) {
            $user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME];
            $user_ip      = CBRatingSystem::get_ipaddress();
        } elseif ( $user_id > 0 ) {
            $user_session = 'user-' . $user_id; //this is string
            $user_ip      = CBRatingSystem::get_ipaddress();
        }
        if ( $user_session == $session && $user_ip == $ip ) {
            $show_own_review = true;
        }
        if($show_own_review || $comment_status == 'approved'){
            $comment_show = true;
        }
        else{
            $comment_show = false;
        }
        return $comment_show;
    }

    /**
     * @param array $comment
     * @param       $session
     * @param       $ip
     *
     * @return bool
     */
    public static function check_permission( $comment = array(), $session, $ip ) {
		$ratingFormArray['comment_view_allowed_users'] = $comment;

		global $current_user, $wpdb;
		$user_id = get_current_user_id();
		if ( $user_id != 0 ) {
			$role               = $wpdb->prefix . 'capabilities';
			$current_user->role = array_keys( $current_user->$role );
			$role               = $current_user->role[0];
		}
		//var_dump($ratingFormArray['comment_view_allowed_users']);
		$user_id = get_current_user_id();
		if ( $user_id == 0 && in_array( 'guest', $ratingFormArray['comment_view_allowed_users'] ) ) {
			$showreview = true;
		} else if ( in_array( $role, $ratingFormArray['comment_view_allowed_users'] ) ) {
			$showreview = true;
		} else {
			$showreview = false;
		}

		if ( $user_id == 0 ) {
			$user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME]; //this is string
			$user_ip      = CBRatingSystem::get_ipaddress();
		} elseif ( $user_id > 0 ) {
			$user_session = 'user-' . $user_id; //this is string
			$user_ip      = CBRatingSystem::get_ipaddress();
		}
		if ( $user_session == $session && $user_ip == $ip ) {
			$show_own_review = true;
		} else {
			$show_own_review = false;
		}
		if ( $show_own_review || $showreview ) {
			$show_review_final = true;
		} else {
			$show_review_final = false;
		}

		return $show_review_final;
	}

	/**
	 * Returns Reviews Html Output
	 *
	 * @param  array   $reviewOptions
	 * @param  array   $ratingFormArray
	 * @param  boolean $ajax
	 *
	 * @return string
	 */
	public static function build_user_rating_review( $reviewOptions = array(), $ratingFormArray = array(), $ajax = false ) {

        global $wpdb;

        $postID = ( isset( $reviewOptions['post_id'] ) ? $reviewOptions['post_id'] : get_the_ID() );
		$postID = (int) $postID;

		$post = get_post($postID);
		if(in_array( $post->post_type, $ratingFormArray['post_types'] )) :

		if ( ! empty( $reviewOptions['form_id'] ) ) {
			$form_id = $reviewOptions['form_id'];
		} else {
			$defaultFormId = get_option( 'cbratingsystem_defaultratingForm' );
			$form_id       = apply_filters( 'rating_form_array', $defaultFormId );
		}
	    $form_id = (int)$form_id;



		if ( ! isset( $reviewOptions['limit']['perpage'] ) ) { // As we only need to get this ratingFormArray from DB to get the offset value.
			$ratingFormArray = CBRatingSystemData::get_ratingForm( $form_id );
		}

		if ( is_string( $reviewOptions['theme'] ) and ! empty( $reviewOptions['theme'] ) ) {
			$theme_key = $reviewOptions['theme'];
		} else {
			$theme_key = get_option( 'cbratingsystem_theme_key' );
		}

		$page       =  isset( $reviewOptions['limit']['page'])  ? $reviewOptions['limit']['page'] :  1;
		if($page <= 0) $page = 1;
		$perpage    =  isset( $reviewOptions['limit']['perpage'])? $reviewOptions['limit']['perpage']: $ratingFormArray['review_limit'];
		//$start      = isset( $reviewOptions['limit']['start'] ) ? $reviewOptions['limit']['start']: (($page -1)*$perpage) ;

		//let's confirm the limit array once again
		$reviewOptions['limit']['page']     = $page;
		$reviewOptions['limit']['perpage']  = $perpage;
		//$reviewOptions['limit']['start']    = $start;



		$totalLimit = $wpdb->get_var( "SELECT per_post_rating_count AS count FROM " . $wpdb->prefix . "cbratingsystem_ratings_summary WHERE form_id='$form_id' AND post_id='$postID'" );



		$reviews = array();

		if ( $totalLimit > $perpage ) {

			$showLoadMoreButton = true;

			$reviews = CBRatingSystemData::get_user_ratings_with_ratingForm( array( $form_id ), array( $postID ), array(), '', 'time', 'DESC', $reviewOptions['limit'], true );
		} else {
			$showLoadMoreButton = false;

			$reviews = CBRatingSystemData::get_user_ratings_with_ratingForm( array( $form_id ), array( $postID ), array(), '', 'time', 'DESC', array(), true );
		}

		$output = $mainContent = '';
        $jsArray      = array();
		$shownReviews = 0;

		if ( ! empty( $reviews[0] ) ) {
			$output .= '<h3 id="cbratingfrom_reviews_title" class="cbratingfrom_reviews_title">' .sprintf( __( "Reviews (%d)", 'cbratingsystem' ), $totalLimit) . '</h3>';
			$output .= '<div id="reviews_container_' . $postID . '" data-post-id="' . $postID . '" data-form-id="' . $form_id . '" class="reviews_container reviews_container_' . $theme_key . '_theme  reviews_container_post-' . $postID . '_form-' . $form_id . ' ">';

			$output .= '<div data-post-id="' . $postID . '" data-form-id="' . $form_id . '" class="reviews_container_div_' . $theme_key . '_theme reviews_container_div reviews_container_div_post-' . $postID . '_form-' . $form_id . ' ">';

			//$shownReviews = 0;

			if ( ! empty( $reviews ) && is_array( $reviews ) ) {

				//$shownReviews = 0;

				foreach ( $reviews as $reviewKey => $review ) {
                    $comment_status = self::check_cpmment_status($review->comment_status , $review->user_session, $review->user_ip );
                    $show_reviews_user = self::check_permission( $ratingFormArray['comment_view_allowed_users'], $review->user_session, $review->user_ip );

					if ( $show_reviews_user &&  $comment_status) {

                        //$output .= '<a name="cbrating-'.$form_id.'-review-'.$review->id.'" id="cbrating-'.$form_id.'-review-'.$review->id.'"></a>';
						$mainContent .= '<div id="cbrating-' . $form_id . '-review-' . $review->id . '" data-review-id="' . $review->id . '" data-post-id="' . $postID . '" data-form-id="' . $form_id . '" class="reviews_wrapper_' . $theme_key . '_theme review_wrapper review_wrapper_post-' . $postID . '_form-' . $form_id . ' review_wrapper_post-' . $postID . '_form-' . $form_id . '_review-' . $review->id . '">';
						$mainContent .= '    <div class="reviews_rating_' . $theme_key . '_theme review_rating review_rating_review-' . $review->id . '">';

						if ( ! empty( $review->rating ) and is_array( $review->rating ) ) {
							// User Details part.
                                if ( $review->user_id != 0 ) {

                                    $user_url = get_author_posts_url( $review->user_id );
                                    $name     = get_the_author_meta( 'display_name', $review->user_id );

                                    if(!empty($user_url) && $ratingFormArray ['show_user_link_in_review']  == '1' ){
                                        $name = '<a target="_blank" href="' . $user_url . '">'.$name .'</a>';
                                    }
                                    //finally check the settings
                                    if($ratingFormArray ['show_user_avatar_in_review']  == '1'){
                                        $gravatar = get_avatar( $review->user_id, 36 );
                                    }
                                    else{
                                        $gravatar = '';
                                    }
                                    $name        = apply_filters('cbrating_edit_review_user_link' , $name ,  $review->user_id);
                                    $gravatar    = apply_filters('cbrating_edit_review_user_avatar' , $gravatar ,   $review->user_id);

                                    $user_html ='  <p class="cbrating_user_name">' . ( ! empty( $user_url ) ? '<span class="user_gravatar">' . $gravatar . $name. '</span>' : '<span class="user_gravatar">' . $gravatar . $name . '</span>' ) . '</p>';

                                    if(isset($ratingFormArray['buddypress_active']) && intval($ratingFormArray['buddypress_active'])){
                                        if(function_exists('bp_is_active')){

                                            $rating_review_filtered_authorlink = apply_filters('cbratingsystem_buddypress_authorlink',array('show_image' => $ratingFormArray['show_user_avatar_in_review'] , 'show_link' => $ratingFormArray['show_user_link_in_review'] ,'review_user_id'=>$review->user_id,'user_html'=>$user_html));
                                            $user_html = $rating_review_filtered_authorlink['user_html'];
                                        }
                                    }
                                } else {
                                    $user_url  = '';
                                    $name      = ( ! empty( $review->user_name  ) ? $review->user_name : 'Anonymous' );
                                    if($ratingFormArray ['show_user_avatar_in_review']  == '1'){
                                        $gravatar  = get_avatar( 0, 36, 'gravatar_default' );
                                    }
                                    else{
                                        $gravatar = '';
                                    }

                                    $user_html ='  <p class="cbrating_user_name">' . (!empty( $user_url ) ? '<span class="user_gravatar">' . $gravatar . $name . '</span>' : '<span class="user_gravatar">' . $gravatar . $name . '</span>' ) . '</p>';
                                }

                            $modified_review = (array) $review;

                            $user_html =  apply_filters('cbrating_edit_review_user_info' , $user_html ,  $review->user_id , $ratingFormArray ,$modified_review, $review );
							
                                    $mainContent .= '    <div class="reviews_user_details_' . $theme_key . '_theme review_user_details">
                                                           '.$user_html.'
                                                            <span class="user_rate_time"><a title="' . date( 'l, F d, Y \a\t j:ia', $review->created ) . '" href="' . get_permalink( $postID ) . '#cbrating-' . $form_id . '-review-' . $review->id . '">' . CBRatingSystemFunctions :: codeboxr_time_elapsed_string( $review->created ) . '</a></span>
                                                        </div>
                                                        <div class="clear" style="clear:both;"></div> ';
                                   $mainContent .= '    <div data-form-id="' . $form_id . '" class="all-criteria-wrapper all_criteria_warpper_' . $theme_key . '_theme  all-criteria-wrapper-form-' . $form_id . ' all-criteria-wrapper-form-' . $form_id . $theme_key . '_theme">';


		                            foreach ( $review->rating as $criteriId => $value ) {

										if ( is_numeric( $criteriId ) ) {
		                                    $firstLabel     = '';
											$value          = ( ( $value / 100 ) * $review->rating[$criteriId . '_starCount'] );

											$jsArray['review'][$review->id]['ratingForm']  = $form_id;
											$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_value']       = $value;
											$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_count']       = $review->rating[$criteriId . '_starCount'];
											$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_redOnlyHint'] = $review->rating[$criteriId . '_stars'][$value-1];
											$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_hints']       = $review->rating[$criteriId . '_stars'];

											$mainContent .= '<div data-form-id="' . $form_id . '" data-criteria-id="' . $criteriId . '" class="criteria_warpper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $criteriId . ' criteria-id-wrapper-' . $criteriId . '-form-' . $form_id . ' ">
				                                                <div class="criteria_label_warpper_' . $theme_key . '_theme criteria-label-wrapper">
				                                                    <span class="criteria-label criteria-label-id-' . $criteriId . '" ><strong>' . __( $review->custom_criteria[$criteriId]['label'], 'cbratingsystem' ) . '</strong></span>
				                                                </div>
				                                                <div data-form-id="' . $form_id . '" data-criteria-id="' . $criteriId . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $criteriId . '-form-' . $form_id . '" id="criteria-star-wrapper-' . $review->id . '"></div>
				                                                <div class="readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $form_id . '-label-' . $criteriId . '">
				                                                    <span class="starTitle">' . ( sanitize_text_field( $review->rating[$criteriId . '_stars'][( $value - 1 )] ) ) . '</span>
				                                                </div>
		                                                     </div> ';

										}
									}
							$mainContent .= '</div>
                                        <div class="clear" style="clear:both;"></div>';
												
                       	////////////////////////////////////////////////
                       			
                  		// Question Display part.
							$mainContent .= '<div data-form-id="' . $form_id . '" class="question_wrapper_' . $theme_key . '_theme question-wrapper question-wrapper-form-' . $form_id . '">';

//							echo '<pre>';
//							var_dump($review->question);
//							echo '</pre>';
//
//							echo '<pre>';
//							var_dump($review->custom_question);
//							echo '</pre>';

							if ( ! empty( $review->question ) && is_array( $review->question ) ) {

								foreach ( $review->question as $questionId => $question ) {
									if ( is_array( $question ) ) {
										$single_question =  $review->custom_question[$questionId];
										//cbxdump($single_question);

										$type       = $single_question['field']['type'];
										$fieldArr   = $single_question['field'][$type];

										$seperated  = isset($fieldArr['seperated']) ? intval($fieldArr['seperated']): 0;

										$valuesText = array();

										foreach ( $question as $key => $val ) {
											$valuesText[$review->id][$questionId][] = '<strong>' . __( stripcslashes( $fieldArr[$key]['text'] ), 'cbratingsystem' ) . '</strong>';
										}



										if ( ( ! empty( $valuesText ) ) ) {
											$mainContent .= '
		                                        <div data-form-id="' . $form_id . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $form_id . ' ">
		                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
		                                                <span class="question-label question-label-id-' . $questionId . '" >' . ( isset( $review->custom_question[$questionId] ) ? __( stripslashes( $review->custom_question[$questionId]['title'] ), 'cbratingsystem' ) : '' ) . '</span>
		                                                <span class="question-label-hiphen">' . ( isset( $review->custom_question[$questionId] ) ? ' - ' : '' ) . '</span>
		                                                <span class="answer"><strong>' . ( implode( ', ', $valuesText[$review->id][$questionId] ) ) . '</strong></span>
		                                            </div>
		                                        </div>';
										}
									} else {
										/*$type       = $single_question['field']['type'];
										$fieldArr   = $single_question['field'][$type];

										$seperated  = isset($fieldArr['seperated']) ? intval($fieldArr['seperated']): 0;*/
										$single_question =  $review->custom_question[$questionId];
										//cbxdump($single_question);
										$type       = $single_question['field']['type'];


										$seperated  = isset($fieldArr['seperated']) ? intval($fieldArr['seperated']): 0;


										if ( $seperated == 0 ) {
											if ( $type == 'text' ) {
												$mainContent .= '
			                                        <div data-form-id="' . $form_id . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $form_id . ' ">
			                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
			                                                <span class="question-label question-label-id-' . $questionId . '" >' . ( isset( $review->custom_question[$questionId] ) ? __( stripslashes( $review->custom_question[$questionId]['title'] ), 'cbratingsystem' ) : '' ) . '</span>
			                                                <span class="question-label-hiphen">' . ( isset( $review->custom_question[$questionId] ) ? ' - ' : '' ) . '</span>
			                                                <span class="answer"><strong>' . $question . '</strong></span>
			                                            </div>
			                                        </div>';

											} else {
												$fieldArr   = $single_question['field'][$type];
												$mainContent .= '
			                                        <div data-form-id="' . $form_id . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $form_id . ' ">
			                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
			                                                <span class="question-label question-label-id-' . $questionId . '" >' . ( isset( $review->custom_question[$questionId] ) ? __( stripslashes( $review->custom_question[$questionId]['title'] ), 'cbratingsystem' ) : '' ) . '</span>
			                                                <span class="question-label-hiphen">' . ( isset( $review->custom_question[$questionId] ) ? ' - ' : '' ) . '</span>
			                                                <span class="answer"><strong>' . ( ( $question == 1 ) ? __( "Yes", 'cbratingsystem' ) : __( "No", 'cbratingsystem' ) ) . '</strong></span>
			                                            </div>
			                                        </div>';

											}

										}
									}
								}//end each single question loop
							}
							$mainContent .= '    </div>
                                        <div class="clear" style="clear:both;"></div>';
                       			
                       			
                       			///////////////////////////////////////////	
                       			// Comment Display part
							if ( ! empty( $review->comment ) && is_string( $review->comment ) ) {

								//$comment = CBRatingSystemFunctions :: text_summary_mapper( $review->comment );
								$comment = $review->comment;
								/*
								if ( is_array( $comment ) && ! empty( $comment['summury'] ) && isset( $comment['rest'] ) ) {

									$comment_output = ' <p class="comment">
				                                                    ' . stripslashes( $comment['summury'] ) .
														( ! empty( $comment['rest'] ) ?
															'   <span style="display:none;" class="read_more_paragraph disable_field">' . $comment['rest'] . '</span>' : '' ) .
														'</p>
														<a href="#" class="js_read_link read_more"> ...More</a>';
								} else {

									$comment_output = '<p class="comment">' . $comment['summury'] . '</p>';
								}
								*/

								$comment_output = '<p class="comment">' . $comment. '</p>';

								$mainContent .= '<div class="review_user_rating_comment_' . $theme_key . '_theme review_user_rating_comment">
                                           			 <strong>Comment : </strong> ' . $comment_output;
                                if($review ->comment_status != 'approved'){

                                   $mainContent .= '<br> <strong>Comment Status : </strong> ' . ucfirst($review ->comment_status) . '

                                       			    </div>
                                        		    <div class="clear" style="clear:both;"></div>
                                     ';
                                }
                                else{
                                    $mainContent .= '

                                       			 </div>
                                        		<div class="clear" style="clear:both;"></div>
                                    ';
                                }

							}
                       			
                       			//////////////////////////////////////////////////			
						}
						$mainContent .='</div>';
						$mainContent .='</div>
						<div class="clear" style="clear:both;"></div>';

						$shownReviews ++;
					}// end of if approved
				}//end for each review
				$output .= $mainContent;
			}	
			$output .= '</div>';
			$output .= '</div>';

			//var_dump($form_id);

			if ( $showLoadMoreButton === true ) {
				//$output .= "<div class=\"load_more_button_".$theme_key."_theme load_more_button load_more_button_form-" . $form_id . "_post-$postID\" data-form-id=\"$form_id\" data-post-id=\"$postID\" data-offset=\"$offset\" data-start=\"$start\" data-end=\"$currentOffset\" clickable=\"true\">";
				$output .= '<p class="cbratingload_more_button load_more_button load_more_button_'.$theme_key.'_theme  load_more_button_form-'.$form_id.'_post-'.$postID.'" >';
					//$output .= '<a  href="#" data-form-id="'.$form_id.'" data-post-id="'.$postID.'" data-page="'.$page.'" data-perpage="'.$perpage.'" data-start="'.$start.'" clickable="true">'.__( 'Load More', 'cbratingsystem' );
					$output .= '<a  href="#" data-form-id="'.$form_id.'" data-post-id="'.$postID.'" data-page="'.$page.'" data-perpage="'.$perpage.'"  clickable="true">'.__( 'Load More', 'cbratingsystem' );
						$output .= '<span style="display:none;" class="cbrating_waiting_icon cbrating_waiting_icon_form-' . $form_id . '_post-' . $postID . '"><img alt="' . __( "Loading", 'cbratingsystem' ) . '" src="' . CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'images/ajax-loader.gif" /></span>';
						$output .= '<input type="hidden" id="cb_ratingForm_front_review_nonce_field" value="' . wp_create_nonce( 'cb_ratingForm_front_review_nonce_field' ) . '" />';
					$output .= '</a>';
				$output .= "</p>";
				$output .= '<div  class="ratingFormReviewStatus ratingFormReviewStatus_' . $theme_key . '_theme  ratingFormReviewStatus-review-form-' . $form_id . '"></div>';
			}
			//$output .= '</div>';//this is the error
		}

		
		$jsSettings = self::front_end_review_js_settings( $reviews, $jsArray, $postID, $ajax );
		$output .= '<script type="text/javascript">' . $jsSettings . '</script>';

		$totalpages = ceil($totalLimit / $perpage);

		//var_dump($totalpages);

		if ( $ajax === true ) {
			return array(
				'html'          => $mainContent . '<script type="text/javascript">' . $jsSettings . '</script>',
				'page'          =>  $page ,
				'perpage'       => $perpage,
				'isFinished' => ( $page >= $totalpages  ) ? '1' : '0'
			);
		}

		return $output;
        else: return '';
        endif;
	}

	public static function front_end_review_js_settings( $reviews, $jsArray, $postId, $ajax = false ) {
		$js = '';

		//echo '<pre>'; print_r(($jsArray)); echo '</pre>'; //die();

		If ( ! empty( $jsArray['review'] ) ) {
			foreach ( $jsArray['review'] as $review => $reviewArr ) {
				$JSON['review_' . $review] = array(
					'img_path'    => CB_RATINGSYSTEM_PLUGIN_DIR_IMG,
					'options'     => json_encode( $jsArray['review'][$review]['criteria'] ),
					'cancel_hint' => __( "Cancel rating", 'cbratingsystem' ),
					'is_rated'    => 1,
				);
			}

			if ( $ajax === true ) {
				$js .= '
                    var reviewContent_post_' . $postId . '_form_' . $reviewArr['ratingForm'] . '_ajax = ' . json_encode(
						$JSON
					) . ';
                ';
			} else {
				$js .= '
                    var reviewContent_post_' . $postId . '_form_' . $reviewArr['ratingForm'] . ' = ' . json_encode(
						$JSON
					) . ';
                ';
			}
		}

		$js .= '
            var cbrpRatingFormReviewContent = ' . json_encode(
				array(
					'failure_msg'   => __( 'Failed to load, please refresh this page.', 'cbratingsystem' ),
					'success_msg'   => __( 'All loaded, you are at end.', 'cbratingsystem' )
				)
			) . ';
        ';

		//echo '<pre>'; print_r(($js)); echo '</pre>'; //die();

		return $js;
	}

	function getRatingAverageWithCustomCondition() {
		CBRatingSystemData::get_user_ratings_with_ratingForm();
	}
}