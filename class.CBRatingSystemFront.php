<?php
/*
** todo::  need to move this function into a class, may be into a helper class. I don't understand why this function is kept standalone here
*/
function cbratingsystem_user_roles_front( $useCase = 'admin' ) {
	global $wp_roles;
	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/user.php' );

	}
	$userRoles = array();

	switch ( $useCase ) {
		default:
		case 'admin':
			$userRoles = array(
				'Anonymous'  => array(
					'guest' => array(
						'name' => __( "Guest", 'cbratingsystem' ),
					),
				),
				'Registered' => get_editable_roles(),
			);
			break;

		case 'front':
			foreach ( get_editable_roles() as $role => $roleInfo ) {
				$userRoles[$role] = $roleInfo['name'];
			}
			$userRoles['guest'] = __( "Guest", 'cbratingsystem' );
			break;
	}

	return $userRoles;

}

// Buddypress integration, check if buddypress is installed or not

if(function_exists('bp_is_active')){
	require_once( WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.'buddypress'.DIRECTORY_SEPARATOR.'bp-blogs'.DIRECTORY_SEPARATOR.'bp-blogs-activity.php');
}

/**
 * Class CBRatingSystemFront
 */
class CBRatingSystemFront {

	/**
	 * Front end content builder function
	 *
	 * @param $ratingFormArray
	 * @param $theme_key
	 *
	 * @return string
	 */
	public static function add_ratingForm_to_content( $ratingFormArray ) {

		global $post, $wpdb, $wp_roles;


		//var_dump(CBRatingSystem::current_user_can_use_ratingsystem( $ratingFormArray['allowed_users'] ));

		$user_type    = ( CBRatingSystem::current_user_can_use_ratingsystem( $ratingFormArray['allowed_users'] ) ) ? 'registered' : 'guest';

		$post_id      = ( isset( $ratingFormArray['post_id'] ) && $ratingFormArray['post_id'] != '' ) ? $ratingFormArray['post_id'] : get_the_ID();
		$post_id      = (int) $post_id;
		$post_type    = get_post_type( $post_id );
		$user_id      = get_current_user_id();
		$user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME];
		//$userObject   = get_current_user();
		$user         = get_current_user_id();

		$theme_key    = $ratingFormArray['theme_key'];

		//var_dump($theme_key);

		$whrOpt['form_id'][] = $ratingFormArray['id'];
		$whrOpt['post_id'][] = $post_id;
        $comment_status_list = array('unverified','unapproved','approved','spam');

        if ( $user_id == 0 ) {
            $userRoles = array('guest');
        }
        else{
            global $current_user;
            $userRoles = $current_user->roles;
        }


        $ratingFormArray['comment_moderation_users'] = is_array($ratingFormArray['comment_moderation_users'])? $ratingFormArray['comment_moderation_users'] :array();
        $comment_status_of_user = in_array($userRoles[0],$ratingFormArray['comment_moderation_users']);
        $cb_comment_status      = '';

		$cb_comment_filtered_status = apply_filters('cbratingsystem_comment_status',array('email_verify_guest' => (isset($ratingFormArray['email_verify_guest'])? $ratingFormArray['email_verify_guest'] : 0), 'comment_status_of_user' => $comment_status_of_user,'cb_comment_status' =>'approved'));

		$cb_comment_status = $cb_comment_filtered_status['cb_comment_status'];

		//registered user or guest user
		if ( $user_id == 0 ) {
			$user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME]; //this is string
			$user_ip      = CBRatingSystem::get_ipaddress();
		} elseif ( $user_id > 0 ) {
			$user_session = 'user-' . $user_id; //this is string
			$user_ip      = CBRatingSystem::get_ipaddress();
		}

		$commonElements  = array_intersect( $ratingFormArray['allowed_users'], $ratingFormArray['view_allowed_users'] );
		$excludeElements = array_diff( $ratingFormArray['view_allowed_users'], $commonElements );


		if ( ! empty( $excludeElements ) ) {
			$viewrating = true;
		} else {
			$viewrating = false;
		}

		$table_name1 = CBRatingSystemData::get_user_ratings_table_name();
		$table_name2 = CBRatingSystemData::get_ratingForm_settings_table_name();
		$sql_get_loggin = "SELECT logging_method FROM $table_name2 where id=" . $ratingFormArray['id'];
		$query_result   = $wpdb->get_results( $sql_get_loggin );
		$query_result2  = maybe_unserialize( $query_result[0]->logging_method );

		//getting the data according to the administrative settings using IP/Cookie for the last comment
		if ( in_array( "cookie", $query_result2 ) && ! in_array( "ip", $query_result2 ) ) {
			$sql   = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $table_name1 ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_id=%d AND ur.user_session = %s", $ratingFormArray['id'], $post_id, $user_id, $user_session );
			$count = $wpdb->get_var( $sql );
		} else if ( ! in_array( "cookie", $query_result2 ) && in_array( "ip", $query_result2 ) ) {
			$sql   = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $table_name1 ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_id=%d AND ur.user_ip = %s", $ratingFormArray['id'], $post_id, $user_id, $user_ip );
			$count = $wpdb->get_var( $sql );
		} else {
			$sql   = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $table_name1 ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_id=%d AND ur.user_ip = %s", $ratingFormArray['id'], $post_id, $user_id, $user_ip );
			$count = $wpdb->get_var( $sql );
		}


		//var_dump($whrOpt);

		//get rating summary
		$avgRatingData = CBRatingSystemData::get_ratings_summary( $whrOpt );

		if ( $user_id == 0 ) {
			$isUserSubmittedRating = CBRatingSystemData::get_user_ratings_with_ratingForm( array( $ratingFormArray['id'] ), array( $post_id ), array( $user_id ), $user_session );
		} elseif ( $user_id > 0 ) {
			$isUserSubmittedRating = CBRatingSystemData::get_user_ratings_with_ratingForm( array( $ratingFormArray['id'] ), array( $post_id ), array( $user_id ) );
		}
		if ( ( is_archive() and ( $ratingFormArray['show_on_arcv'] == 0 ) ) or ( is_home() && ( $ratingFormArray['show_on_home'] == 0 ) ) or ( ( is_single() ) && ( $ratingFormArray['show_on_single'] == 0 ) ) ) {

            $content = get_post_field('post_content', $post_id);
            return $content;
		}

		//cbxdump($avgRatingData);

       // var_dump(CBRatingSystem::current_user_can_use_ratingsystem( $ratingFormArray['allowed_users'] ));
		if ( CBRatingSystem::current_user_can_use_ratingsystem( $ratingFormArray['allowed_users'] ) && $ratingFormArray['show_on_single'] == 1 && in_array( $post->post_type, $ratingFormArray['post_types'] ) ) {
            if ( ( $ratingFormArray['is_active'] == 1 ) && $count < 1 ) {




                    //if(sizeof($avgRatingData) > 0) :
                    if ( sizeof($avgRatingData) > 0 && $avgRatingData[0]['per_post_rating_summary'] > 100 ) { //echo "hello 1"; why 100

//                        echo '<pre>';
//                        print_r($avgRatingData);
//                        echo '</pre>';

                        $ratingAverage                  = self::viewPerCriteriaRatingResult( $ratingFormArray['id'], $post_id, $user_id );
                        $perPostAverageRating           = $ratingAverage['perPost'][$post_id];
                        $perCriteriaAverageRating       = $ratingAverage['avgPerCriteria'];
                        $customPerPostAverageRating     = $ratingAverage['customUser']['perPost'];
                        $customPerCriteriaAverageRating = $ratingAverage['customUser']['perCriteria'];
                        $customPerPostRateCount         = $ratingAverage['customUser']['perPostRateCount'];
                        $rating = array(
                            'form_id'                     => $ratingFormArray['id'],
                            'post_id'                     => $post_id,
                            'post_type'                   => $post_type,
                            'per_post_rating_count'       => ( `per_post_rating_count` ),
                            'per_post_rating_summary'     => number_format( $perPostAverageRating, 2 ),
                            'custom_user_rating_summary'  => maybe_serialize( $ratingAverage['customUser'] ),
                            'per_criteria_rating_summary' => maybe_serialize( $ratingAverage['avgPerCriteria'] ),
                        );

                        $return = CBRatingSystemData::update_rating_summary( $rating );

                    } else {

//                        echo '<pre>';
//                        print_r($avgRatingData);
//                        echo '</pre>';


                        if(sizeof($avgRatingData) > 0){
                            $perPostAverageRating           = $avgRatingData[0]['per_post_rating_summary'];
                            $perCriteriaAverageRating       = $avgRatingData[0]['per_criteria_rating_summary'];
                            $customPerPostAverageRating     = $avgRatingData[0]['custom_user_rating_summary']['perPost'];
                            $customPerCriteriaAverageRating = $avgRatingData[0]['custom_user_rating_summary']['perCriteria'];
                            $customPerPostRateCount         = $avgRatingData[0]['custom_user_rating_summary']['perPostRateCount'];
                        }
                        else{
                            /*
                            $perPostAverageRating           = $avgRatingData[0]['per_post_rating_summary'];
                            $perCriteriaAverageRating       = $avgRatingData[0]['per_criteria_rating_summary'];
                            $customPerPostAverageRating     = $avgRatingData[0]['custom_user_rating_summary']['perPost'];

                            $customPerPostRateCount         = $avgRatingData[0]['custom_user_rating_summary']['perPostRateCount'];
                            $customPerCriteriaAverageRating = $avgRatingData[0]['custom_user_rating_summary']['perCriteria'];
                            */

                            $perPostAverageRating           = 0;
                            $perCriteriaAverageRating       = array();
                            $customPerPostAverageRating     = array('registered' => 0 ,'editor' => 0);
                            $customPerPostRateCount         = array('registered' => 0 ,'editor' => 0);
                            $customPerCriteriaAverageRating = array();
                        }


                    }//end of else
                    //endif;

	                $nothome = 0;

                    if ( is_home() || is_front_page() ) {
                        $nothome = 0;
                    }
	                else{
		                $nothome = 1;
	                }

                $display = '<div class="cbrp_front_content">';
                    $display .= '<!--RatingForm Content-->
                        <h3 id="cbratingfrom_title" class="cbratingfrom_title" data-home="' . $nothome . '">' . __( 'Ratings', 'cbratingsystem' ) . ' </h3>
                        <div id="cbrp_container_' . $post_id . '" class="cbrp_container_' . $theme_key . '_theme cbrp-content-container cbrp-content-container-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-form-id="' . $ratingFormArray['id'] . '" data-post-id="' . $post_id . '">
                            <div class="cbrp_wrapper_' . $theme_key . '_theme cbrp-content-wprapper cbrp-content-wprapper-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-form-id="' . $ratingFormArray['id'] . '">
                                <div class="cbrp_switch_report_' . $theme_key . '_theme cbrp-switch-report cbrp-switch-report-form-' . $ratingFormArray['id'] . ' cbrp-switch-report-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" style="' . ( ( ! empty( $isUserSubmittedRating ) ) ? 'display:block;' : '' ) . '" data-form-id="' . $ratingFormArray['id'] . '" data-post-id="' . $post_id . '">
                                    <div class="allUser_criteria user_criteria">
                                        <div class="report-title" id="cbrp-report-title">
                                            <span style="line-height: 30px;">' . __( 'Current Average Ratings', 'cbratingsystem' ) . '</span>
                                        </div>
                                        <div class="clear" style="clear:both"></div>
                                            <div class="criteria-container">';

	                                        //cbxdump($perCriteriaAverageRating);
                                            if ( ! empty( $perCriteriaAverageRating ) ) {
                                                foreach ( $perCriteriaAverageRating as $cId => $criteria ) {

	                                                //cbxdump($criteria);

	                                                $labels = array();
	                                                foreach($criteria['stars'] as $star){
		                                                if($star['title'] == '' || $star['enabled'] != 1) continue;
		                                                $labels[] = $star['title'];
	                                                }

                                                    $cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-count']       = count( $labels );
                                                    $cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-value']       = $criteria['value'];
                                                    $cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-post-' . $post_id . '-avgvalue'] = $perPostAverageRating;
                                                    $display .= '
                                                            <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="readonly_criteria_wrapper_' . $theme_key . '_theme readonly-criteria-wrapper readonly-criteria-id-wrapper-' . $cId . ' readonly-criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                                                <div class="readonly_criteria_label_wrapper_' . $theme_key . '_theme readonly-criteria-label-wrapper readonly-criteria-label-wrapper-form-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '">
                                                                    <span class="readonly-criteria-label criteria-label-form-' . $ratingFormArray['id'] . ' readonly-criteria-label-id-' . $cId . '" >' . __( $ratingFormArray['custom_criteria'][$cId]['label'], 'cbratingsystem' ) . '</span>
                                                                </div>
                                                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-wrapper readonly-criteria-star-wrapper-id-' . $cId . '-form-' . $ratingFormArray['id'] . ' readonly-criteria-star-wrapper-id-' . $cId . ' criteria-star-wrapper-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">

                                                                </div>
                                                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-hint readonly-criteria-star-hint-form-' . $ratingFormArray['id'] . '-id-' . $cId . ' criteria-star-hint-id-' . $cId . '"></div>
                                                                <div class="criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . ' ">
                                                                    <span>' . __( 'Avg', 'cbratingsystem' ) . ':  </span>
                                                                    <span class="rating">' . ( number_format( ( ( $criteria['value'] / 100 ) * count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ), 2 ) ) . '/' . ( count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ) . '</span>
                                                                </div>
                                                            </div>
                                                            ';
                                                }
                                        } else {
                                            foreach ( $ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray ) {
												if($firstLabelArray['label'] == '' || $firstLabelArray['enabled'] != 1) continue;
	                                            $labels = array();
	                                            foreach($firstLabelArray['stars'] as $star){
		                                            if($star['title'] == '' || $star['enabled'] != 1) continue;
		                                            $labels[] = $star['title'];
	                                            }

                                                $cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]            = array_values( $labels );
                                                $cCriteria['criteria-stars-' . $firstLabel]                                                 = json_encode( array_values( $labels ) );
                                                $cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count( $labels);
                                                $display .= '
                                                        <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                                            <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
                                                                <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . __( $firstLabelArray['label'], 'cbratingsystem' ) . '</span>
                                                            </div>
                                                            <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">
                                                            </div>
                                                            <div class="criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '">
                                                                <span>' . __( 'Avg', 'cbratingsystem' ) . ':</span>
                                                                <span class="rating">0/' . ( count( $firstLabelArray['stars'] ) ) . '</span>
                                                            </div>
                                                        </div>
                                                            ';
                                            }

                                        }
                                            $display .='</div>';//end of <div class="criteria-container">
                                            $display .= '<div class="clear" style="clear:both"></div>
                                                                <div class="rating-average-label-form-' . $ratingFormArray['id'] . '-postid-' . $post_id . ' readonly_criteria_average_label_form_' . $theme_key . '_theme readonly-criteria-average-label-form rating-average-label-form-' . $ratingFormArray['id'] . '">
                                                                    <span>' . __( 'Total Avg Rating', 'cbratingsystem' ) . ': </span>
                                                                    <span class="rating">' . ( number_format( ( $perPostAverageRating / 100 ) * 5, 2 ) ) . '/5' . '</span>
                                                                    <span class="total_rates">  ' . __( 'based on', 'cbratingsystem' ) . ' <span class="total_rates_count">' . ( ! empty( $avgRatingData[0]['per_post_rating_count'] ) ? (integer) $avgRatingData[0]['per_post_rating_count'] : '0' ) . '</span> rating(s) </span>
                                                                </div>';

                                    $display .='</div>';//end of  <div class="allUser_criteria user_criteria">

                                    if($ratingFormArray ['show_editor_rating'] == '1'){
                                        if ( ! empty( $customPerCriteriaAverageRating['editor'] ) ) {
                                            $display .= '<div class="editor_criteria user_criteria">
                                                    <div class="report-title" id="cbrp-report-title">
                                                        <span style="line-height: 30px;">' . __( 'Editor Avg. Rating ', 'cbratingsystem' ) . '</span>
                                                    </div>
                                                    <div class="clear" style="clear:both"></div>
                                                        <div class="criteria-container">';
                                            foreach ( $customPerCriteriaAverageRating['editor'] as $cId => $criteria ) {
                                                $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-count']       = count( $ratingFormArray['custom_criteria'][$cId]['stars'] );
                                                $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-value']       = $criteria['value'];
                                                $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-post-' . $post_id . '-avgvalue'] = $perPostAverageRating;
                                                $display .= '
                                                                        <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="readonly_criteria_wrapper_' . $theme_key . '_theme readonly-criteria-wrapper readonly-criteria-id-wrapper-' . $cId . ' readonly-criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                                                            <div class="readonly_criteria_label_wrapper_' . $theme_key . '_theme readonly-criteria-label-wrapper readonly-criteria-label-wrapper-form-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '">
                                                                                <span class="readonly-criteria-label criteria-label-form-' . $ratingFormArray['id'] . ' readonly-criteria-label-id-' . $cId . '" >' . __( $ratingFormArray['custom_criteria'][$cId]['label'], 'cbratingsystem' ) . '</span>
                                                                            </div>
                                                                            <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="editor-criteria-star-wrapper readonly-criteria-star-wrapper-id-' . $cId . '-form-' . $ratingFormArray['id'] . ' readonly-criteria-star-wrapper-id-' . $cId . ' criteria-star-wrapper-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">

                                                                            </div>
                                                                            <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-hint readonly-criteria-star-hint-form-' . $ratingFormArray['id'] . '-id-' . $cId . ' criteria-star-hint-id-' . $cId . '"></div>
                                                                            <div class="editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '">
                                                                                <span>' . __( 'Avg ', 'cbratingsystem' ) . ': </span>
                                                                                <span class="rating">' . ( number_format( ( ( $criteria['value'] / 100 ) * count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ), 2 ) ) . '/' . ( count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ) . '</span>
                                                                            </div>
                                                                        </div>
                                                                        ';
                                            }//end foreach
                                        } else {
                                            $display .= '<div class="editor_criteria user_criteria">
                                                     <div class="report-title" id="cbrp-report-title">
                                                         <span style="line-height: 30px;">' . __( 'Editors Average Rating', 'cbratingsystem' ) . '</span>
                                                    </div>
                                                     <div class="clear" style="clear:both"></div>

                                                     <div class="criteria-container">';
                                            foreach ( $ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray ) {
                                                $cCriteria['editor-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]            = array_values( $firstLabelArray['stars'] );
                                                $cCriteria['criteria-stars-' . $firstLabel]                                                        = json_encode( array_values( $firstLabelArray['stars'] ) );
                                                $cCriteria['editor-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count( $firstLabelArray['stars'] );
                                                $display .= '
                                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                                        <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
                                                            <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . __( $firstLabelArray['label'], 'cbratingsystem' ) . '</span>
                                                        </div>
                                                        <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="editor-criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">
                                                        </div>
                                                        <div class="editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '">
                                                            <span>' . __( 'Avg ', 'cbratingsystem' ) . ': </span>
                                                            <span class="rating">0/' . ( count( $firstLabelArray['stars'] ) ) . '</span>
                                                        </div>
                                                    </div>
                                                        ';
                                            }
                                        }//end of else for editor
                                        $display .='</div>';//end of editor editor_criteria



                                    $display .=' <div class="clear" style="clear:both"></div>
                                                <div class="editor-rating-average-label-form-' . $ratingFormArray['id'] . '-postid-' . $post_id . ' readonly_criteria_average_label_form_' . $theme_key . '_theme readonly-criteria-average-label-form editor-rating-average-label-form-' . $ratingFormArray['id'] . '">
                                                     <span>' . __( 'Total Avg. Rating ', 'cbratingsystem' ) . ': </span>
                                                    <span class="rating">' . ( number_format( ( $customPerPostAverageRating['editor'] / 100 ) * 5, 2 ) ) . '/5' . '</span>
                                                    <span class="total_rates">  ' . __( 'based on', 'cbratingsystem' ) . ' <span class="total_rates_count">' . ( ! empty( $customPerPostRateCount['editor'] ) ? (integer) $customPerPostRateCount['editor'] : '0' ) . '</span> rating(s) </span>
                                                </div>';
                                    $display .='</div>';//end of editor editor_criteria_container
                                    }// end of if show editor rating

                                $display .='</div>';//end of <div cbrp_switch_report_

                                if ( empty( $isUserSubmittedRating ) ) {
                                        $display .= '
                                            <div class="cbrp-rating-buffer-form-' . $ratingFormArray['id'] . '-post-' . $post_id . ' cbrp_rating_buffer_' . $theme_key . '_theme cbrp-rating-buffer cbrp-rating-buffer-form-' . $ratingFormArray['id'] . ' cb-rating-buffer-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '">
                                                <div style="clear:both;"></div>
                                                <!--Criteria Area-->
                                                <div class="criteria_listings_' . $theme_key . '_theme criteria-listings criteria-listings-form-' . $ratingFormArray['id'] . '">';

			                                    if ( ! empty( $ratingFormArray['custom_criteria'] ) ) {
			                                        foreach ( $ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray ) {
														if($firstLabelArray['label'] == '' ||  $firstLabelArray['enabled'] != 1) continue;
				                                        //cbxdump($firstLabelArray);
				                                        //var_dump('miao');

				                                        $star_labels = array();
				                                        foreach($firstLabelArray['stars'] as $star){
					                                        if(!$star['enabled']) continue;
					                                        $star_labels[] = $star['title'];
				                                        }


			                                            $cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]                     = array_values( $star_labels );
			                                            $cCriteria['criteria-stars-' . $firstLabel]                                                          = json_encode( array_values( $star_labels ) );
			                                            $cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count']          = count( $star_labels );
			                                            $cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count( $star_labels );
			                                            $display .= '
			                                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
			                                                        <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
			                                                            <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . __( $firstLabelArray['label'], 'cbratingsystem' ) . '</span>
			                                                        </div>
			                                                        <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">

			                                                        </div>
			                                                        <div class="criteria_star_hint_' . $theme_key . '_theme criteria-star-hint criteria-star-hint-id-' . $firstLabel . ' criteria-star-hint-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '"></div>
			                                                    </div>';

			                                        }
			                                    }//end if any enabled criteria


	                                            //cbxdump($ratingFormArray['custom_question']);

	                                            //show custom question
						                        if ( isset( $ratingFormArray['custom_question'] ) && sizeof($ratingFormArray['custom_question']) > 0 && empty( $isUserSubmittedRating ) && ( $ratingFormArray['enable_question'] == 1 ) ) {
						                            $display .= '<div style="clear:both;"></div>
							                                    <div class="question_box">';

								                            foreach ( $ratingFormArray['custom_question'] as $q_id => $q_arr ) {

									                            //skip question that are not enabled
									                            if(!isset($q_arr['enabled']) || $q_arr['enabled'] != 1) continue;



							                                    if ( isset($q_arr['required'] ) &&   $q_arr['required']  == 1) {
							                                        $requiredClass = 'required';
							                                        $requiredIcon  = '<span class="form-required" title="'.__('Required Field','cbratingsystem').'">*</span>';
							                                    } else {
							                                        $requiredClass = '';
							                                        $requiredIcon  = '';
							                                    }

								                                $method = 'display_' . $q_arr['field']['type'] . '_field';
									                           // var_dump($method);

								                                if ( method_exists( 'CBRatingSystemFront', $method ) ) {
								                                    $fieldDisplay = self::$method( $q_id, $q_arr, array( 'required_class' => $requiredClass, 'required_text' => $requiredIcon ), $ratingFormArray );
								                                } else {
								                                    $fieldDisplay = '';
								                                }

								                                //print single question
									                            $display        .= '<div class="item-question">';
								                                    $display    .= $fieldDisplay;
								                                $display        .= '</div>';

								                            }//end foreach question

						                            $display     .= '</div>';
						                        }//end custom question
                                    // hook here to hide name aaded 17-12 -14-@codeboxr hQ
                                    $ratingreview_hide_name_html = '';
                                    $display .= apply_filters('cbratingsystem_hide_current_user_name', $ratingreview_hide_name_html , $ratingFormArray , $post_id ,$theme_key);


                        if ( ( $ratingFormArray['enable_comment'] == 1 ) and empty( $isUserSubmittedRating ) ) {
                            if($ratingFormArray['comment_required'] == '1'){
                                $comment_class = '<span class="form-required" title="This field is required.">*</span>';
                                $comment_div_class = '1';
                            }
                            else{
                                $comment_class = '';
                                $comment_div_class = '0';
                            }
                            $display .= '
                                    <div style="clear:both;"></div>
                                    <div class="cbratingsystem_comment_box">';
                                        $display .= '<label class = "">'.__('Comment/Note:','cbratingsystem').'</label>'.$comment_class;
                                        $display .= '<textarea class = "cbrs_comment_textarea '.$comment_div_class.'"  data-required = "'.$comment_div_class.'" name="comment[' . $ratingFormArray['id'] . ']" style="width:97.5%; height:50px;"></textarea>';
                                        $display .= '<span class="comment_limit_text_' . $theme_key . '_theme comment_limit_text comment_limit_text_form_' . $ratingFormArray['id'] . '_post_' . $post_id . '"></span>';
                                        $display .= '
                                    </div>
                                    <div style="clear:both;"></div>

                            ';
                        }

                        if ( $user_id == 0 ) {
                            $display .= '<div class="user_info">
                                            <div class="user_name">
                                                <input id="user_name_field-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '" class="user_name_field required" type="text" name="userinfo[' . $ratingFormArray['id'] . '][name]" value="" placeholder="Your Name" required />
                                            </div>
                                            <div class="user_email">
                                                <input id="user_email_field-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '" class="user_email_field required" type="text" name="userinfo[' . $ratingFormArray['id'] . '][email]" value="" placeholder="Your Email" required />
                                            </div>
                                         </div>

                            ';
                        } else {
                            $display .= '<div class="user_info"></div>';
                            /*
                            $display .= '<div class="user_info">
                                            <!--input type="hidden" name="userinfo[' . $ratingFormArray['id'] . '][name]" value="' . $userObject->display_name . '" /-->
                                            <!--input type="hidden" name="userinfo[' . $ratingFormArray['id'] . '][email]" value="' . $userObject->email . '" /-->
                                        </div>
                            ';
                            */

                        }
	                                $buddypress_post = isset($ratingFormArray['buddypress_post']) ? $ratingFormArray['buddypress_post']: 0;

                                    $user_hash = '';
                                    $display .= '
                                    <div style="clear:both;"></div>
                                    </div>
                                    <div style="clear:both;"></div>
                                    <div class="submit_button_wrapper">
                                        <button class="button cbrp-button cbrp-button-form-' . $ratingFormArray['id'] . '" data-hash = "'.$user_hash.'"  data-buddypress-post = "'.$buddypress_post.'" data-comment-status ="'.$cb_comment_status.'" id="submit-rating" type="submit" name="op" value=""><span id="cbrp-button-label">' . __( 'Submit', 'cbratingsystem' ) . '</span></button>
                                        <div style="display:none;" class="cbrp_load_more_waiting_icon cbrp_load_more_waiting_icon_form-' . $ratingFormArray['id'] . '_post-' . $post_id . '"><img alt="' . __( 'Loading', 'cbratingsystem' ) . '" src="' . CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'images/ajax-loader.gif" /></div>
                                    </div>
                                    <div style="clear:both;"></div>
                                    </div>
                                    <div id="cbrp-report" data-show-div="cbrp-switch-report-form-' . $ratingFormArray['id'] . '" class="cbratingsystem-tabswitch cbrp-rating-buffer cbrp-rating-buffer-form-' . $ratingFormArray['id'] . ' cbrp-rating-buffer-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-post-id ="' . $post_id . '">' . __( 'View Average', 'cbratingsystem' ) . '</div>
                                    <div id="cbrp-form" data-show-div="cbrp-rating-buffer-form-' . $ratingFormArray['id'] . '" class="cbratingsystem-tabswitch cbrp-switch-report cbrp-switch-report-form-' . $ratingFormArray['id'] . ' cbrp-switch-report-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-post-id ="' . $post_id . '">' . __( 'Rate this', 'cbratingsystem' ) . '</div>
                                    <div id="status" class="ratingFormStatus ratingFormStatus-form-' . $ratingFormArray['id'] . '"></div>

                                 ';

                                }
                        $display .='</div>';//end of <div cbrp_wrapper_
                        $display .= '

                            <input type="hidden" name="rp_id" value="' . $ratingFormArray['id'] . '-' . $post_id . '" />
                            <input type="hidden" id="cb_ratingForm_front_form_nonce_field" value="' . wp_create_nonce( 'cb_ratingForm_front_form_nonce_field' ) . '" />
                            <input type="hidden" name="formId" value="ratingForm" />
                        ';

                        $jsSettings = self::front_end_js_settings( $ratingFormArray, $cCriteria, $post_id );
                        $display    .= '<script type="text/javascript">' . $jsSettings . '</script>';
                    //show credit to codeboxr
                    if($ratingFormArray['show_credit_to_codeboxr'] == '1'){

                        $cbrating_credit_msg  = __('Rating System by codeboxr','cbratingsystem');
                        $credit         = '<span class ="codeboxr_rating_credit"><a rel="external" href="http://codeboxr.com" target="_blank">'.$cbrating_credit_msg.'</a></span>';
                        $display        .=  apply_filters('cbratingsystem_codeboxr_credit',$credit);
                    }

                    $display        .='</div>';//end of <div cbrp_container_
                $display            .='</div>';//end of <div class="cbrp_front_content">
                $content             = $display;
                self::viewPerCriteriaRatingResult( $ratingFormArray, $post_id, $user_id );
                return $content;

			}// end of count >1 &&  $ratingFormArray['is_active'] == 1 )
			else{// already given a rating
				
				if ( CBRatingSystem::current_user_can_use_ratingsystem( $ratingFormArray['allowed_users'] ) && $ratingFormArray['show_on_single'] == 1 && in_array( $post->post_type, $ratingFormArray['post_types'] ) ) {

                    /*
                    echo '<pre>';
                    print_r($avgRatingData);
                    echo '</pre>';
                    */

					if ( $avgRatingData[0]['per_post_rating_summary'] > 100 ) {
						//var_dump('hi there before');

						$ratingAverage              = self::viewPerCriteriaRatingResult( $ratingFormArray['id'], $post_id, $user_id );
						$perPostAverageRating       = $ratingAverage['perPost'][$post_id];
						$perCriteriaAverageRating   = $ratingAverage['avgPerCriteria'];
						$customPerPostAverageRating     = $ratingAverage['customUser']['perPost'];
						$customPerCriteriaAverageRating = $ratingAverage['customUser']['perCriteria'];
						$customPerPostRateCount         = $ratingAverage['customUser']['perPostRateCount'];

						$rating = array(
							'form_id'                     => $ratingFormArray['id'],
							'post_id'                     => $post_id,
							'post_type'                   => $post_type,
							'per_post_rating_count'       => ( `per_post_rating_count` ),
							'per_post_rating_summary'     => number_format( $perPostAverageRating, 2 ),
							'custom_user_rating_summary'  => maybe_serialize( $ratingAverage['customUser'] ),
							'per_criteria_rating_summary' => maybe_serialize( $ratingAverage['avgPerCriteria'] ),
						);

						$return = CBRatingSystemData::update_rating_summary( $rating );

					} else {

						//var_dump('hi there');




                        $perPostAverageRating           = $avgRatingData[0]['per_post_rating_summary'];
						$perCriteriaAverageRating       = $avgRatingData[0]['per_criteria_rating_summary'];
						$customPerPostAverageRating     = $avgRatingData[0]['custom_user_rating_summary']['perPost'];
						$customPerCriteriaAverageRating = $avgRatingData[0]['custom_user_rating_summary']['perCriteria'];
						$customPerPostRateCount         = $avgRatingData[0]['custom_user_rating_summary']['perPostRateCount'];

						//cbxdump($perCriteriaAverageRating);

						//var_dump(is_serialized($perCriteriaAverageRating));
						$perCriteriaAverageRating =  maybe_unserialize($perCriteriaAverageRating);

						//cbxdump($perCriteriaAverageRating);
					}







					
					$display     = '<div class="cbrp_front_content">';
					$display    .= '<!--RatingForm Content-->
                    <h3 id="cbratingfrom_title" class="cbratingfrom_title"> ' . __( 'Ratings', 'cbratingsystem' ) . '</h3>
                    <div id="cbrp_container_' . $post_id . '" class="cbrp_container_' . $theme_key . '_theme cbrp-content-container cbrp-content-container-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-form-id="' . $ratingFormArray['id'] . '" data-post-id="' . $post_id . '"  data-count= "' . $count . '" style="display:bock;">
                        <div class="cbrp_wrapper_' . $theme_key . '_theme cbrp-content-wprapper cbrp-content-wprapper-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-form-id="' . $ratingFormArray['id'] . '">
                            <div class="cbrp_switch_report_' . $theme_key . '_theme cbrp-switch-report cbrp-switch-report-form-' . $ratingFormArray['id'] . '" style="' . ( ( ! empty( $isUserSubmittedRating ) ) ? 'display:block;' : '' ) . '" data-form-id="' . $ratingFormArray['id'] . '">
                                <div class="allUser_criteria user_criteria">
                                    <div class="report-title" id="cbrp-report-title">
                                        <span style="line-height: 30px;">' . __( 'Current Average Ratings', 'cbratingsystem' ) . '</span>
                                    </div>
                                    <div class="clear" style="clear:both"></div>

                                        <div class="criteria-container">';
                                        if ( ! empty( $perCriteriaAverageRating ) ) {


//											//var_dump(is_serialized($perCriteriaAverageRating));
//	                                        if(is_serialized($perCriteriaAverageRating)  === FALSE && !is_array($perCriteriaAverageRating)){
//		                                        $perCriteriaAverageRating = json_decode($perCriteriaAverageRating);
//	                                        }
//	                                        else{
//		                                        $perCriteriaAverageRating = maybe_unserialize($perCriteriaAverageRating);
//	                                        }


	                                        //cbxdump($perCriteriaAverageRating);

											foreach ( $perCriteriaAverageRating as $cId => $criteria ) {
												$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-count']       = count( $ratingFormArray['custom_criteria'][$cId]['stars'] );
												$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-value']       = $criteria['value'];
												$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-post-' . $post_id . '-avgvalue'] = $perPostAverageRating;
												$display .= '
					                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="readonly_criteria_wrapper_' . $theme_key . '_theme readonly-criteria-wrapper readonly-criteria-id-wrapper-' . $cId . ' readonly-criteria-wrapper-form-' . $ratingFormArray['id'] . '">
					                                    <div class="readonly_criteria_label_wrapper_' . $theme_key . '_theme readonly-criteria-label-wrapper readonly-criteria-label-wrapper-form-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '">
					                                        <span class="readonly-criteria-label criteria-label-form-' . $ratingFormArray['id'] . ' readonly-criteria-label-id-' . $cId . '" >' . __( $ratingFormArray['custom_criteria'][$cId]['label'], 'cbratingsystem' ) . '</span>
					                                    </div>
					                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-wrapper readonly-criteria-star-wrapper-id-' . $cId . '-form-' . $ratingFormArray['id'] . ' readonly-criteria-star-wrapper-id-' . $cId . ' criteria-star-wrapper-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper"></div>
					                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-hint readonly-criteria-star-hint-form-' . $ratingFormArray['id'] . '-id-' . $cId . ' criteria-star-hint-id-' . $cId . '"></div>
					                                    <div class="criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '">
					                                        <span>' . __( 'Avg', 'cbratingsystem' ) . ': </span>
					                                        <span class="rating">' . ( number_format( ( ( $criteria['value'] / 100 ) * count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ), 2 ) ) . '/' . ( count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ) . '</span>
					                                    </div>
					                                </div>
					                                ';
											}
									} else {
										foreach ( $ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray ) {
											$cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]            = array_values( $firstLabelArray['stars'] );
											$cCriteria['criteria-stars-' . $firstLabel]                                                 = json_encode( array_values( $firstLabelArray['stars'] ) );
											$cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count( $firstLabelArray['stars'] );
											$display .= '
				                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
				                                    <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
				                                        <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . __( $firstLabelArray['label'], cbratingsystem ) . '</span>
				                                    </div>
				                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">
				                                        <!--input data-label-id="' . $firstLabel . '" class="criteria-star criteria-star-label-id-' . $firstLabel . '-currentScore" type="hidden" id="criteria-star" name="criteria[' . $firstLabel . '][value]" value="" /-->
				                                    </div>
				                                    <div class="criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '">
				                                        <span>' . __( 'Avg', 'cbratingsystem' ) . ': </span>
				                                        <span class="rating">0/' . ( count( $firstLabelArray['stars'] ) ) . '</span>
				                                    </div>
				                                </div>
				                                    ';
										}
									}

					                 $display.='</div>';// end of criteria-container
					                 $display .= '<div class="clear" style="clear:both"></div>
			                                <div class="rating-average-label-form-' . $ratingFormArray['id'] . '-postid-' . $post_id . ' readonly_criteria_average_label_form_' . $theme_key . '_theme readonly-criteria-average-label-form rating-average-label-form-' . $ratingFormArray['id'] . '">
			                                    <span>' . __( 'Total Avg Rating', 'cbratingsystem' ) . ': </span>
			                                    <span class="rating">' . ( number_format( ( $perPostAverageRating / 100 ) * 5, 2 ) ) . '/5' . '</span>
			                                    <span class="total_rates">  ' . __( 'based on', 'cbratingsystem' ) . ' <span class="total_rates_count">' . ( ! empty( $avgRatingData[0]['per_post_rating_count'] ) ? (integer) $avgRatingData[0]['per_post_rating_count'] : '0' ) . '</span> rating(s) </span>
			                                </div>';
					                    
					                    
									$display.='</div>';//end of allUser_criteria user_criteria

				                    if($ratingFormArray ['show_editor_rating'] == '1'){

				                        if ( ! empty( $customPerCriteriaAverageRating['editor'] ) ) {
				                            $display .= '<div class="editor_criteria user_criteria">
				                                <div class="report-title" id="cbrp-report-title">
				                                    <span style="line-height: 30px;">' . __( 'Editors Average Rating', 'cbratingsystem' ) . '</span>
				                                </div>
				                                <div class="clear" style="clear:both"></div>

				                                  <div class="criteria-container">';
				                            foreach ( $customPerCriteriaAverageRating['editor'] as $cId => $criteria ) {
				                                $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-count']       = count( $ratingFormArray['custom_criteria'][$cId]['stars'] );
				                                $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-value']       = $criteria['value'];
				                                $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-post-' . $post_id . '-avgvalue'] = $perPostAverageRating;
				                                $display .= '
							                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="readonly_criteria_wrapper_' . $theme_key . '_theme readonly-criteria-wrapper readonly-criteria-id-wrapper-' . $cId . ' readonly-criteria-wrapper-form-' . $ratingFormArray['id'] . '">
							                                    <div class="readonly_criteria_label_wrapper_' . $theme_key . '_theme readonly-criteria-label-wrapper readonly-criteria-label-wrapper-form-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '">
							                                        <span class="readonly-criteria-label criteria-label-form-' . $ratingFormArray['id'] . ' readonly-criteria-label-id-' . $cId . '" >' . __( $ratingFormArray['custom_criteria'][$cId]['label'], 'cbratingsystem' ) . '</span>
							                                    </div>
							                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="editor-criteria-star-wrapper readonly-criteria-star-wrapper-id-' . $cId . '-form-' . $ratingFormArray['id'] . ' readonly-criteria-star-wrapper-id-' . $cId . ' criteria-star-wrapper-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">

							                                    </div>
							                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-hint readonly-criteria-star-hint-form-' . $ratingFormArray['id'] . '-id-' . $cId . ' criteria-star-hint-id-' . $cId . '"></div>
							                                    <div class="editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '"-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '">
							                                        <span>' . __( 'Avg', 'cbratingsystem' ) . ': </span>
							                                        <span class="rating">' . ( number_format( ( ( $criteria['value'] / 100 ) * count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ), 2 ) ) . '/' . ( count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ) . '</span>
							                                    </div>
							                                </div>
							                                ';
				                            }
				                        } else {
				                            $display .= '<div class="editor_criteria user_criteria">
				                               <div class="report-title" id="cbrp-report-title">
				                                    <span style="line-height: 30px;">' . __( 'Editors Average Rating', 'cbratingsystem' ) . '</span>
				                                </div>
				                                <div class="clear" style="clear:both"></div>

				                                    <div class="criteria-container">';

				                            foreach ( $ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray ) {
				                                $cCriteria['editor-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]            = array_values( $firstLabelArray['stars'] );
				                                $cCriteria['criteria-stars-' . $firstLabel]                                                        = json_encode( array_values( $firstLabelArray['stars'] ) );
				                                $cCriteria['editor-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count( $firstLabelArray['stars'] );
				                                $display .= '
							                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
							                                    <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
							                                        <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . __( $firstLabelArray['label'], 'cbratingsystem' ) . '</span>
							                                    </div>
							                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="editor-criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">

							                                    </div>
							                                    <div class="editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '"-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '">
							                                        <span>' . __( 'Avg', 'cbratingsystem' ) . ': </span>
							                                        <span class="rating">0/' . ( count( $firstLabelArray['stars'] ) ) . '</span>
							                                    </div>
							                                </div>
							                                    ';
				                            }
				                        }

				                        $display .= ' </div>';	//end of editor critaria_container


										$display .= '

				                                <div class="clear" style="clear:both"></div>
				                                <div class="editor-rating-average-label-form-' . $ratingFormArray['id'] . '-postid-' . $post_id . ' readonly_criteria_average_label_form_' . $theme_key . '_theme readonly-criteria-average-label-form editor-rating-average-label-form-' . $ratingFormArray['id'] . '">
				                                    <span>' . __( 'Total Avg Rating', 'cbratingsystem' ) . ': </span>
				                                    <span class="rating">' . ( number_format( ( $customPerPostAverageRating['editor'] / 100 ) * 5, 2 ) ) . '/5' . '</span>
				                                    <span class="total_rates"> ' . __( 'based on', 'cbratingsystem' ) . '<span class="total_rates_count">' . ( ! empty( $customPerPostRateCount['editor'] ) ? (integer) $customPerPostRateCount['editor'] : '0' ) . '</span> rating(s) </span>
				                                </div>';




										$display .= ' </div>';	//end of editor critaria_
				                    }// end of if show editor rating part
									
					$display.='</div>';// end of cbrp_switch_report

					if ( empty( $isUserSubmittedRating ) ) {
								$display .= '
		                            <div class="cbrp_rating_buffer_' . $theme_key . '_theme cbrp-rating-buffer cbrp-rating-buffer-form-' . $ratingFormArray['id'] . '">
		                                <div style="clear:both;"></div>
		                                <!--Criteria Area-->
		                                <div class="criteria_listings_' . $theme_key . '_theme criteria-listings criteria-listings-form-' . $ratingFormArray['id'] . '">';

		                        if ( ! empty( $ratingFormArray['custom_criteria'] ) ) {
									foreach ( $ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray ) {
										$cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]                     = array_values( $firstLabelArray['stars'] );
										$cCriteria['criteria-stars-' . $firstLabel]                                                          = json_encode( array_values( $firstLabelArray['stars'] ) );
										$cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count']          = count( $firstLabelArray['stars'] );
										$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count( $firstLabelArray['stars'] );
										$display .= '
		                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
		                                        <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
		                                            <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . __( $firstLabelArray['label'], 'cbratingsystem' ) . '</span>
		                                        </div>
		                                        <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">

		                                        </div>
		                                        <div class="criteria_star_hint_' . $theme_key . '_theme criteria-star-hint criteria-star-hint-id-' . $firstLabel . ' criteria-star-hint-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '"></div>
		                                    </div>
		                                    ';
									}
								}


								if ( ! empty( $ratingFormArray['custom_question']['enabled'] ) and empty( $isUserSubmittedRating ) and ( $ratingFormArray['enable_question'] == 1 ) ) {
									$display .= '
		                                <div style="clear:both;"></div>
		                                <div class="question_box">
		                                    ';
									foreach ( $ratingFormArray['custom_question']['enabled'] as $q_id => $q_arr ) {
										if ( ! empty( $ratingFormArray['custom_question']['required'] ) ) {
											if ( in_array( $q_id, $ratingFormArray['custom_question']['required'] ) ) {
												$requiredClass = 'required';
												$requiredIcon  = '<span class="form-required" title="This field is required.">*</span>';
											} else {
												$requiredClass = '';
												$requiredIcon  = '';
											}
										}

										$method = 'display_' . $q_arr['field']['type'] . '_field';
										if ( method_exists( 'CBRatingSystemFront', $method ) ) {
											$fieldDisplay = self::$method( $q_id, $q_arr, array( 'required_class' => $requiredClass, 'required_text' => $requiredIcon ), $ratingFormArray );
										} else {
											$fieldDisplay = '';
										}

										$display .= '<div class="item-question">';
										$display .= $fieldDisplay;
										$display .= '</div>';
									}
									$display .= '
		                                </div>
		                        ';
								}


								$display.=' <div style="clear:both;"></div></div>';//end of rating_buffer
								$display.='<div style="clear:both;"></div></div>
								<div id="status" class="ratingFormStatus ratingFormStatus-form-' . $ratingFormArray['id'] . '"></div>';//end of criteria_listing

					}//end if user submitted rating

						$display.='</div>';//end of cbrp_wrapper
						$display .= '
                        <input type="hidden" name="rp_id" value="' . $ratingFormArray['id'] . '-' . $post_id . '" />
                        <input type="hidden" id="cb_ratingForm_front_form_nonce_field" value="' . wp_create_nonce( 'cb_ratingForm_front_form_nonce_field' ) . '" />
                        <input type="hidden" name="formId" value="ratingForm" />
                   ';


                    if($ratingFormArray['show_credit_to_codeboxr'] == '1'){

                        //$cbrating_credit_msg  = __('Rating System by codeboxr','cbratingsystem');
                        //$display        .= '<span class ="codeboxr_rating_credit">'.apply_filters('cbratingsystem_codeboxr_credit',$cbrating_credit_msg).'</span>';

                        $cbrating_credit_msg  = __('Rating System by Codeboxr','cbratingsystem');
                        $credit         = '<span class ="codeboxr_rating_credit"><a rel="external follow" href="http://codeboxr.com" target="_blank">'.$cbrating_credit_msg.'</a></span>';
                        $display        .=  apply_filters('cbratingsystem_codeboxr_credit',$credit);


                    }
					$display.='</div>';//end of cbrp_container

					if(!isset($cCriteria ) ) $cCriteria = array();
					$jsSettings = self::front_end_js_settings( $ratingFormArray, $cCriteria, $post_id );

					$display .= '<script type="text/javascript">' . $jsSettings . '</script>';
				$display.='</div>';//end of cbrp_front_content
				$content = $display;				

				self::viewPerCriteriaRatingResult( $ratingFormArray, $post_id, $user_id );
				return $content;		
				}//end of if allowed user
			}//end of already given a rating
		}// end of allowed user
		else if ( $viewrating ) {

			$current_user = get_userdata(get_current_user_id());

			//var_dump($current_user);

            if($current_user === FALSE) $current_user = new stdClass();

            //var_dump($excludeElements);


            $current_user_roles = isset($current_user->roles) ? $current_user->roles : NULL;

            if($current_user_roles == NULL || !is_array($current_user_roles)){
                //means guest
                $current_user_roles = array();
                $current_user->ID = 0;

            }

            $has_cap = array_intersect( $current_user_roles, $excludeElements );


			if ( $current_user->ID == 0 && in_array( 'guest', $excludeElements ) ) {
				$testfinalview = true;

			} elseif (is_array($has_cap) && !empty($has_cap)  ) {
                //var_dump($excludeElements);
				$testfinalview = true;

			} else {

				$testfinalview = false;

			}

			//var_dump($testfinalview);



			if ( ( $ratingFormArray['is_active'] == 1 ) && $testfinalview == true && in_array( $post->post_type, $ratingFormArray['post_types'] ) ) {
				//cbxdump($avgRatingData);
				if ( isset($avgRatingData[0]['per_post_rating_summary']) && $avgRatingData[0]['per_post_rating_summary'] > 100 ) {


					$ratingAverage            = self::viewPerCriteriaRatingResult( $ratingFormArray['id'], $post_id, $user_id );
					$perPostAverageRating     = $ratingAverage['perPost'][$post_id];
					$perCriteriaAverageRating = $ratingAverage['avgPerCriteria'];

					$customPerPostAverageRating     = $ratingAverage['customUser']['perPost'];
					$customPerCriteriaAverageRating = $ratingAverage['customUser']['perCriteria'];
					$customPerPostRateCount         = $ratingAverage['customUser']['perPostRateCount'];

					$rating = array(
						'form_id'                     => $ratingFormArray['id'],
						'post_id'                     => $post_id,
						'post_type'                   => $post_type,
						'per_post_rating_count'       => ( `per_post_rating_count` ),
						'per_post_rating_summary'     => number_format( $perPostAverageRating, 2 ),
						'custom_user_rating_summary'  => maybe_serialize( $ratingAverage['customUser'] ),
						'per_criteria_rating_summary' => maybe_serialize( $ratingAverage['avgPerCriteria'] ),
					);

					$return = CBRatingSystemData::update_rating_summary( $rating );


				} else {

					//cbxdump($avgRatingData[0]);

					$perPostAverageRating           = $avgRatingData[0]['per_post_rating_summary'];
					$perCriteriaAverageRating       = $avgRatingData[0]['per_criteria_rating_summary'];
					$customPerPostAverageRating     = $avgRatingData[0]['custom_user_rating_summary']['perPost'];
					$customPerCriteriaAverageRating = $avgRatingData[0]['custom_user_rating_summary']['perCriteria'];
					$customPerPostRateCount         = $avgRatingData[0]['custom_user_rating_summary']['perPostRateCount'];

				}

				if ( ! is_home() ) {
					$nothome = 1;
				}
				$display = '<div class="cbrp_front_content">';
				$display .= '<!--RatingForm Content-->
                    <h3 id="cbratingfrom_title" class="cbratingfrom_title" data-home="' . $nothome . '">' . __( 'Rating', 'cbratingsystem' ) . ' </h3>
                    <div id="cbrp_container_' . $post_id . '" class="cbrp_container_' . $theme_key . '_theme cbrp-content-container cbrp-content-container-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-form-id="' . $ratingFormArray['id'] . '" data-post-id="' . $post_id . '">
                        <div class="cbrp_wrapper_' . $theme_key . '_theme cbrp-content-wprapper cbrp-content-wprapper-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-form-id="' . $ratingFormArray['id'] . '">
                            <div class="show_cbrp_switch_report_' . $theme_key . '_theme all_cbrp-switch-report cbrp-switch-report-form-' . $ratingFormArray['id'] . ' all_cbrp-switch-report-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" style="' . ( ( ! empty( $isUserSubmittedRating ) ) ? 'display:block;' : '' ) . '" data-form-id="' . $ratingFormArray['id'] . '" data-post-id="' . $post_id . '">
                                <div class="User_criteria user_criteria">
                                    <div class="report-title" id="cbrp-report-title">
                                        <span style="line-height: 30px;">' . __( 'Current Average Ratings', 'cbratingsystem' ) . '</span>
                                    </div>
                                    <div class="clear" style="clear:both"></div>

                                        <div class="criteria-container">
                ';

				if ( ! empty( $perCriteriaAverageRating ) ) {
					foreach ( $perCriteriaAverageRating as $cId => $criteria ) {
						$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-count']       = count( $ratingFormArray['custom_criteria'][$cId]['stars'] );
						$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-value']       = $criteria['value'];
						$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-post-' . $post_id . '-avgvalue'] = $perPostAverageRating;
						$display .= '
                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="readonly_criteria_wrapper_' . $theme_key . '_theme readonly-criteria-wrapper readonly-criteria-id-wrapper-' . $cId . ' readonly-criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                    <div class="readonly_criteria_label_wrapper_' . $theme_key . '_theme readonly-criteria-label-wrapper readonly-criteria-label-wrapper-form-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '">
                                        <span class="readonly-criteria-label criteria-label-form-' . $ratingFormArray['id'] . ' readonly-criteria-label-id-' . $cId . '" >' . __( $ratingFormArray['custom_criteria'][$cId]['label'], 'cbratingsystem' ) . '</span>
                                    </div>
                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-wrapper readonly-criteria-star-wrapper-id-' . $cId . '-form-' . $ratingFormArray['id'] . ' readonly-criteria-star-wrapper-id-' . $cId . ' criteria-star-wrapper-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">
                                        <!--input data-label-id="' . $cId . '" class="criteria-star criteria-star-label-id-' . $cId . '-currentScore" type="hidden" id="criteria-star" name="criteria[' . $cId . '][value]" value="" /-->
                                    </div>
                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-hint readonly-criteria-star-hint-form-' . $ratingFormArray['id'] . '-id-' . $cId . ' criteria-star-hint-id-' . $cId . '"></div>
                                     
                                    <div class="criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . ' ">
                                        <span>' . __( 'Avg', 'cbratingsystem' ) . ':  </span>
                                        <span class="rating">' . ( number_format( ( ( $criteria['value'] / 100 ) * count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ), 2 ) ) . '/' . ( count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ) . '</span>
                                    </div>
                                </div>
                                ';
					}
				} else {
					foreach ( $ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray ) {
						$cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]            = array_values( $firstLabelArray['stars'] );
						$cCriteria['criteria-stars-' . $firstLabel]                                                 = json_encode( array_values( $firstLabelArray['stars'] ) );
						$cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count( $firstLabelArray['stars'] );
						$display .= '
                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                    <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
                                        <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . __( $firstLabelArray['label'], 'cbratingsystem' ) . '</span>
                                    </div>
                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">
                                        <!--input data-label-id="' . $firstLabel . '" class="criteria-star criteria-star-label-id-' . $firstLabel . '-currentScore" type="hidden" id="criteria-star" name="criteria[' . $firstLabel . '][value]" value="" /-->
                                   		
                                    </div>
                                    <div class="criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '">
                                        <span>' . __( 'Avg', 'cbratingsystem' ) . ': </span>
                                        <span class="rating">0/' . ( count( $firstLabelArray['stars'] ) ) . '</span>
                                    </div>
                                </div>
                                    ';
					}
				}

				$display .= '</div><div class="clear" style="clear:both"></div>
                                <div class="rating-average-label-form-' . $ratingFormArray['id'] . '-postid-' . $post_id . ' readonly_criteria_average_label_form_' . $theme_key . '_theme readonly-criteria-average-label-form rating-average-label-form-' . $ratingFormArray['id'] . '">
                                    <span>' . __( 'Total Avg Rating', 'cbratingsystem' ) . ': </span>
                                    <span class="rating">' . ( number_format( ( $perPostAverageRating / 100 ) * 5, 2 ) ) . '/5' . '</span>
                                    <span class="total_rates">  ' . __( 'based on', 'cbratingsystem' ) . ' <span class="total_rates_count">' . ( ! empty( $avgRatingData[0]['per_post_rating_count'] ) ? (integer) $avgRatingData[0]['per_post_rating_count'] : '0' ) . '</span> rating(s) </span>
                                </div></div>';


				//for editor
                if($ratingFormArray ['show_editor_rating'] == '1'){


				if ( ! empty( $customPerCriteriaAverageRating['editor'] ) ) {

					$display .= '<div class="editor_criteria user_criteria">
                        		<div class="report-title" id="cbrp-report-title">
                                    <span style="line-height: 30px;">' . __( 'Editor Ave Rating ', 'cbratingsystem' ) . '</span>
                                </div>
                                <div class="clear" style="clear:both"></div>
                                
                                    <div class="criteria-container">';
									foreach ( $customPerCriteriaAverageRating['editor'] as $cId => $criteria ) {
										$cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-count']       = count( $ratingFormArray['custom_criteria'][$cId]['stars'] );
										$cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-value']       = $criteria['value'];
										$cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-post-' . $post_id . '-avgvalue'] = $perPostAverageRating;
										$display .= '
				                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="readonly_criteria_wrapper_' . $theme_key . '_theme readonly-criteria-wrapper readonly-criteria-id-wrapper-' . $cId . ' readonly-criteria-wrapper-form-' . $ratingFormArray['id'] . '">
				                                    <div class="readonly_criteria_label_wrapper_' . $theme_key . '_theme readonly-criteria-label-wrapper readonly-criteria-label-wrapper-form-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '">
				                                        <span class="readonly-criteria-label criteria-label-form-' . $ratingFormArray['id'] . ' readonly-criteria-label-id-' . $cId . '" >' . __( $ratingFormArray['custom_criteria'][$cId]['label'], 'cbratingsystem' ) . '</span>
				                                    </div>
				                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="editor-criteria-star-wrapper readonly-criteria-star-wrapper-id-' . $cId . '-form-' . $ratingFormArray['id'] . ' readonly-criteria-star-wrapper-id-' . $cId . ' criteria-star-wrapper-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">
				                                        <!--input data-label-id="' . $cId . '" class="criteria-star criteria-star-label-id-' . $cId . '-currentScore" type="hidden" id="criteria-star" name="criteria[' . $cId . '][value]" value="" /-->
				                                    </div>
				                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-hint readonly-criteria-star-hint-form-' . $ratingFormArray['id'] . '-id-' . $cId . ' criteria-star-hint-id-' . $cId . '"></div>
				                                    <div class="editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '">
				                                        <span>' . __( 'Avg ', 'cbratingsystem' ) . ': </span>
				                                        <span class="rating">' . ( number_format( ( ( $criteria['value'] / 100 ) * count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ), 2 ) ) . '/' . ( count( $ratingFormArray['custom_criteria'][$cId]['stars'] ) ) . '</span>
				                                    </div>
				                                </div>
				                                ';
									}
				} else {
					$display .= '<div class="editor_criteria user_criteria">
                                 <div class="report-title" id="cbrp-report-title">
                                    <span style="line-height: 30px;">' . __( 'Editors Average Rating', 'cbratingsystem' ) . '</span>
                                </div>
                                <div class="clear" style="clear:both"></div>
                                
                                    <div class="criteria-container">';
									foreach ( $ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray ) {
										$cCriteria['editor-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]            = array_values( $firstLabelArray['stars'] );
										$cCriteria['criteria-stars-' . $firstLabel]                                                        = json_encode( array_values( $firstLabelArray['stars'] ) );
										$cCriteria['editor-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count( $firstLabelArray['stars'] );
										$display .= '
				                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
				                                    <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
				                                        <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . __( $firstLabelArray['label'], 'cbratingsystem' ) . '</span>
				                                    </div>
				                                    <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="editor-criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">
				                                        <!--input data-label-id="' . $firstLabel . '" class="criteria-star criteria-star-label-id-' . $firstLabel . '-currentScore" type="hidden" id="criteria-star" name="criteria[' . $firstLabel . '][value]" value="" /-->
				                                    </div>
				                                    <div class="editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '-postid-' . $post_id . ' readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '">
				                                        
				                                        <span>' . __( 'Avg ', 'cbratingsystem' ) . ': </span>
				                                        <span class="rating">0/' . ( count( $firstLabelArray['stars'] ) ) . '</span>
				                                    </div>
				                                </div>
				                                    ';
									}
				}

				$display .= '
                           
                                <div class="clear" style="clear:both"></div>
                                <div class="editor-rating-average-label-form-' . $ratingFormArray['id'] . '-postid-' . $post_id . ' readonly_criteria_average_label_form_' . $theme_key . '_theme readonly-criteria-average-label-form editor-rating-average-label-form-' . $ratingFormArray['id'] . '">
                                    <span>' . __( 'Total Avg Rating ', 'cbratingsystem' ) . ': </span>
                                    <span class="rating">' . ( number_format( ( $customPerPostAverageRating['editor'] / 100 ) * 5, 2 ) ) . '/5' . '</span>
                                    <span class="total_rates">  ' . __( 'basedon', 'cbratingsystem' ) . ' <span class="total_rates_count">' . ( ! empty( $customPerPostRateCount['editor'] ) ? (integer) $customPerPostRateCount['editor'] : '0' ) . '</span> rating(s) </span>
                                </div>
                                </div>                                
                            </div>
                        ';
				$display .= '                      
                    </div>';

                }
				$jsSettings = self::front_end_js_settings( $ratingFormArray, $cCriteria, $post_id );
				$display .= '<script type="text/javascript">' . $jsSettings . '</script>';
                if($ratingFormArray['show_credit_to_codeboxr'] == '1'){

                    //$cbrating_credit_msg  = __('Rating System by codeboxr','cbratingsystem');
                    //$display        .= '<span class ="codeboxr_rating_credit" style = "font-size:xx-small!important;float: right;">'.apply_filters('cbratingsystem_codeboxr_credit',$cbrating_credit_msg).'</span>';


                    $cbrating_credit_msg  = __('Rating System by codeboxr','cbratingsystem');
                    $credit         = '<span class ="codeboxr_rating_credit"><a rel="external" href="http://codeboxr.com" target="_blank">'.$cbrating_credit_msg.'</a></span>';
                    $display        .=  apply_filters('cbratingsystem_codeboxr_credit', $credit);

                }
				$display .= '</div>';
				$display .= '</div>';
				$display .= '</div>';
				$content = $display;
                //var_dump('i am here agaon man');

				self::viewPerCriteriaRatingResult( $ratingFormArray, $post_id, $user_id );


                if($current_user->ID == 0){
                    $cb_log_in_link = '<a href=" '.wp_login_url( get_permalink() ).'" title="'.__('Login','cbratingsystem').'">'.__('login','cbratingsystem').'</a>';
                    $content       .= '<p>'.sprintf(__('Please %s to rate','cbratingsystem'),$cb_log_in_link).'</p>';
                }
                else{

                    $content .= '<p>'.__('You are not allowed to rate.','cbratingsystem').'</p>';
                }


                return $content;
			
			}//end of if viewrating true
			
		}
	}


	/**
	 * Front end js settings
	 *
	 * @param $ratingFormArray
	 * @param $cCriteria
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function front_end_js_settings( $ratingFormArray, $cCriteria, $post_id ) {
		global $post;

        //undefined $is_rated

		$js = '
            var ratingFormOptions = ' . json_encode(
				array(
					'limit' => $ratingFormArray['comment_limit'],
				)
			) . ';
        ';
		$js .= '
            var ratingForm_post_' . $post_id . '_form_' . $ratingFormArray['id'] . ' = ' . json_encode(
				array(
					'img_path'              => CB_RATINGSYSTEM_PLUGIN_DIR_IMG,
					'hints'                 => json_encode( $cCriteria ),
					'cancel_hint'           => __( 'Click to cancel given rating', 'cbratingsystem' ),
					//'is_rated'              =>   $is_rated, //need to find why this is not defined
                    'is_rated'              =>   '',
					'thanks_msg'            => __( 'Thank you for rating', 'cbratingsystem'),
					'pleaseFillAll_msg'     => __( 'Please rate to all criteria', 'cbratingsystem' ),
					'pleaseCheckTheBox_msg' => __( 'Please fill all required fields', 'cbratingsystem' ),
					'failure_msg'           => __( 'Rating save error', 'cbratingsystem' ),
				)
			) . ';
        ';

		$js .= '
            var readOnlyRatingForm_post_' . $post_id . '_form_' . $ratingFormArray['id'] . ' = ' . json_encode(
				array(
					'img_path' => CB_RATINGSYSTEM_PLUGIN_DIR_IMG,
					'hints'    => json_encode( $cCriteria ),
					'is_rated' => 1,
				)
			) . ';
        ';
		$js .= '
            var ratingFormAjax = ' . json_encode(
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			) . ';
        ';

		return $js;
	}

	/**
	 * Ajax function for the review submission
	 */
	public static function cbRatingAjaxFunction() {
		global $wpdb;

		$user_id    = get_current_user_id(); //returns 0 if guest
		$user_info   = '';

		if ( isset( $_POST['cbRatingData'] ) && ! empty( $_POST['cbRatingData'] ) ) {
			$returnedData = $_POST['cbRatingData'];

			//var_dump($returnedData);


			if ( wp_verify_nonce( $returnedData['cbrp_nonce'], 'cb_ratingForm_front_form_nonce_field' ) ) {
				if ( ! empty( $returnedData['values'] ) ) {

					list( $insertArray['form_id'], $insertArray['post_id'] ) = explode( '-', $returnedData['rp_id'] );

					if ( $user_id == 0 ) {
						$user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME]; //this is string
						$user_ip      = CBRatingSystem::get_ipaddress();

					} elseif ( $user_id > 0 ) {
						$user_session = 'user-' . $user_id; //this is string
						$user_ip      = CBRatingSystem::get_ipaddress();
						$user_info = get_userdata($user_id);
					}

					$table_name1 = CBRatingSystemData::get_user_ratings_table_name();
					$table_name2 = CBRatingSystemData::get_ratingForm_settings_table_name();

					$sql_get_loggin = "SELECT logging_method FROM $table_name2 where id=" . $insertArray['form_id'];

					//$sql_get_loggin = maybe_unserialize($sql_get_loggin[0]->logging_method);
					$query_result  = $wpdb->get_results( $sql_get_loggin );
					$query_result2 = maybe_unserialize( $query_result[0]->logging_method );
					//getting the data according to the administrative settings using IP/Cookie for the last comment
					if ( in_array( "cookie", $query_result2 ) && ! in_array( "ip", $query_result2 ) ) {

						$sql   = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $table_name1 ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_id=%d AND ur.user_session = %s", $insertArray['form_id'], $insertArray['post_id'], $user_id, $user_session );
						$count = $wpdb->get_var( $sql );
					} else if ( ! in_array( "cookie", $query_result2 ) && in_array( "ip", $query_result2 ) ) {

						$sql   = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $table_name1 ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_id=%d AND ur.user_ip = %s", $insertArray['form_id'], $insertArray['post_id'], $user_id, $user_ip );
						$count = $wpdb->get_var( $sql );
					} else {
						$sql   = $wpdb->prepare( "SELECT COUNT(ur.id) AS count FROM $table_name1 ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_id=%d AND ur.user_ip = %s", $insertArray['form_id'], $insertArray['post_id'], $user_id, $user_ip );
						$count = $wpdb->get_var( $sql );
					}

					if ( $count < 1 ) { // first time rating

						$insertArray['post_type'] = get_post_type( $insertArray['post_id'] );
						$insertArray['created']   = time();

						$question   = array();

						//var_dump('question');
						//cbxdump($returnedData['question']);

						if ( ! empty( $returnedData['question'][$insertArray['form_id']] ) && is_array( $returnedData['question'][$insertArray['form_id']] ) ) {
							foreach ( $returnedData['question'][$insertArray['form_id']] as $qID => $qValue ) {
								if ( is_array( $qValue ) && ! empty( $qValue ) ) {
									foreach ( $qValue as $key => $val ) {
										$type = $qValue[$qID . '_type'];
										if ( isset( $qValue[$type . '-' . $qID] ) && ! empty( $qValue[$type . '-' . $qID] ) ) {
											$question[$qID] = $qValue[$type . '-' . $qID];
										} elseif ( ( $key != ( $qID . '_type' ) ) && ( $key != ( $type . '-' . $qID ) ) && ! empty( $val ) ) {
											$key = str_replace( $qID . '_', '', $key );

											if ( is_numeric( $key ) ) {
												$question[$qID][$key] = $val;
											}
										}
									}
								} else {
									$question[$qID] = $qValue;
								}
							}
						}

						$insertArray['question'] = maybe_serialize( $question );

						$comment = esc_html( sanitize_text_field( $returnedData['comment'] ) );

						if ( strlen( $comment ) <= $returnedData['comment_limit'] ) {
							$insertArray['comment'] = $comment;
						} elseif ( strlen( $comment ) > $returnedData['comment_limit'] ) {
							$insertArray['comment'] = substr( $comment, 0, - ( $returnedData['comment_limit'] ) );
						}

						if ( ! is_user_logged_in() ) {
							if ( ! empty( $returnedData['user_name'] ) ) {
								$insertArray['user_name'] = sanitize_text_field( trim( $returnedData['user_name'] ) );
							} else {
								$encoded = json_encode(
									array(
										'validation'   => 1,
										'errorMessage' => __( 'Name field can\'t be left blank.', 'cbratingsystem' )
									)
								);

								echo $encoded;
								die();
							}
							if ( ! empty( $returnedData['user_email'] ) and is_email( trim( $returnedData['user_email'] ) ) ) {
								$insertArray['user_email'] = sanitize_text_field( trim( $returnedData['user_email'] ) );
							} else {
								$encoded = json_encode(
									array(
										'validation'   => 1,
										'errorMessage' => __( 'Please enter a valid email address.', 'cbratingsystem' )
									)
								);


								echo $encoded;
								die();
							}

						}
						/*else {
                            //guest user
							$insertArray['user_name']  = '';
							$insertArray['user_email'] = $returnedData['user_email'];
							$insertArray['user_ip']      = $user_ip;
							$insertArray['user_session'] = $user_session;

						}*/

						$insertArray['user_name']    = ($user_id > 0)? $user_info->user_login: $returnedData['user_name'];
						$insertArray['user_email']   = ($user_id > 0)? $user_info->user_email : $returnedData['user_email'];
						$insertArray['user_ip']      = $user_ip;
						$insertArray['user_session'] = $user_session;



						/*
                         * @example:
                         * $returnedData['values'] = array(
                         *      0 => 100          // label_id/criteria_id => ( (Score For this label / Star count) * 100)
                         * )
                         */
						$insertArray['rating'] = maybe_serialize( $returnedData['values'] );

						foreach ( $returnedData['values'] as $key => $val ) {
							if ( is_numeric( $key ) ) {
								$average[$key] = $val;
							}
						}

                        $hash_comment = $insertArray['user_ip'].$insertArray['user_session'].$insertArray['user_email'].time();
                        $hash_comment = md5($hash_comment);

						$insertArray['average']             = ( array_sum( $average ) / count( $average ) );
						//$insertArray['user_ip']      = CBRatingSystem::get_ipaddress();
						//$insertArray['user_session'] = ( $user_id != 0 ) ? 'user-' . $user_id : $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME];
						$insertArray['user_id']             = $user_id;
						$insertArray['form_id']             = (int) $insertArray['form_id'];
						$insertArray['post_id']             = (int) $insertArray['post_id'];
                        $insertArray['comment_status']      = $returnedData['comment_status'];
                        $insertArray['comment_hash']        = $hash_comment ;
                        $insertArray['allow_user_to_hide']  = isset($returnedData["hide_this_user_name"]) ? $returnedData["hide_this_user_name"] : 0 ;


						$return                      = CBRatingSystemData::update_rating( $insertArray );

                        if( $insertArray['comment_status'] == 'unverified' &&  $insertArray['comment_hash'] != '' &&  $insertArray['user_email'] !=''){

                            $cb_subject = __('Verify Your email','cbratingsystem');
                            $cb_message = get_site_url().'?cbratingemailverify='.$insertArray['comment_hash'];
                            $from =  get_option('admin_email');
                            //var_dump($from);
                            wp_mail( $insertArray['user_email'], $cb_subject, $cb_message );
                           // mail($insertArray['user_email'],$cb_subject,$cb_message,"From: $from\n");
                        }


                        // buddypress post added 29-10-14 codeboxr
                        if(isset($returnedData["buddypress_post"]) && $returnedData["buddypress_post"] == '1' || is_user_logged_in()){
                            if(function_exists('bp_is_active')){
                                // buddy press active
                                $buddy_post =  array(
                                    'id'                => false,                  // Pass an existing activity ID to update an existing entry.
                                    'action'            => bp_core_get_userlink(bp_loggedin_user_id()).__(' has rated this post ','cbratingsystem').get_permalink($insertArray['post_id']),                     // The activity action - e.g. "Jon Doe posted an update"
                                    'content'           => $insertArray['comment'],                     // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"
                                    'component'         => 'ratingcomponent',                  // The name/ID of the component e.g. groups, profile, mycomponent
                                    'type'              => 'ratingcomponent_activity',                  // The activity type e.g. activity_update, profile_updated
                                    'primary_link'      => '',                     // Optional: The primary URL for this item in RSS feeds (defaults to activity permalink)
                                    'user_id'           => bp_loggedin_user_id(),  // Optional: The user to record the activity for, can be false if this activity is not for a user.
                                    'item_id'           => false,                  // Optional: The ID of the specific item being recorded, e.g. a blog_id
                                    'secondary_item_id' => false,                  // Optional: A second ID used to further filter e.g. a comment_id
                                    'recorded_time'     => bp_core_current_time(), // The GMT time that this activity was recorded
                                    'hide_sitewide'     => false,                  // Should this be hidden on the sitewide activity stream?
                                    'is_spam'           => false,                  // Is this activity item to be marked as spam?
                                ) ;
                               do_action('cbratingsystem_buddypress_activity_post', $buddy_post);
                            }//end checking if buddypress is installed
                        }

						$lastcommentrt               = $return; // storing the last commend id for safety

						if ( $return ) {
							//getting the criteria rating result
							$ratingAverage = self::viewPerCriteriaRatingResult( $insertArray['form_id'], $insertArray['post_id'], $user_id );

							$ratingsCount = $ratingAverage['ratingsCount'][$insertArray['form_id'] . '-' . $insertArray['post_id']];

							if ( ! empty( $ratingsCount ) ) {
								$rating = array(
									'form_id'                     => $insertArray['form_id'],
									'post_id'                     => $insertArray['post_id'],
									'post_type'                   => $insertArray['post_type'],
									'per_post_rating_count'       => $ratingsCount,
									'per_post_rating_summary'     => number_format( $ratingAverage['perPost'][$insertArray['post_id']], 2 ),
									'custom_user_rating_summary'  => maybe_serialize( $ratingAverage['customUser'] ),
									'per_criteria_rating_summary' => maybe_serialize( $ratingAverage['avgPerCriteria'] ),
								);

								foreach ( $ratingAverage['avgPerCriteria'] as $cId => $criteria ) {
									$cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count( $criteria['stars'] );
									$cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = $criteria['value'];
								}
								if ( ! empty( $ratingAverage['customUser'] ['perCriteria']['editor'] ) ) {
									foreach ( $ratingAverage['customUser'] ['perCriteria']['editor'] as $cId => $criteria ) {
										$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count( $ratingAverage['avgPerCriteria'][$cId]['stars'] );
										$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = $criteria['value'];
									}
								} else {
									foreach ( $ratingAverage['avgPerCriteria'] as $cId => $criteria ) {
										$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count( $ratingAverage['avgPerCriteria'][$cId]['stars'] );
										$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = 0;
									}
								}
								$cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = $rating['per_post_rating_summary'];
								if ( ! empty( $ratingAverage['customUser'] ['perCriteria']['editor'] ) ) {
									$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = $ratingAverage['customUser']['perPost']['editor'];
								} else {
									$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = 0;
								}
								$return = CBRatingSystemData::update_rating_summary( $rating );

								$editorCount = ( isset( $ratingAverage['customUser']['perPostRateCount']['editor'] ) ? (int)$ratingAverage['customUser']['perPostRateCount']['editor'] : 0 );

								//review part here
								if ( empty( $ratingFormArray ) ) {
									$defaultFormId = get_option( 'cbratingsystem_defaultratingForm' );
									$ratingFormId  = apply_filters( 'rating_form_array', $defaultFormId );

									//getting the rating form data from here.
									$ratingFormArray = CBRatingSystemData::get_ratingForm( $insertArray['form_id'] );
								}

								//get the theme for doing frontend UI works
								$theme_key                              = get_option( 'cbratingsystem_theme_key' );
								$reviewOptions['theme']                 = $theme_key;
                                $reviewOptions['comment_status']        = $insertArray['comment_status'];
                                $reviewOptions["hide_this_user_name"]   = isset($returnedData["hide_this_user_name"])? $returnedData["hide_this_user_name"]: 0;

								$lastcomment = ( is_numeric( $lastcommentrt ) ) ? self::build_user_rating_review_single( $reviewOptions, $ratingFormArray, $lastcommentrt ) : '';

								//echo $lastcomment;
								if ( $return ) {
									$encoded = json_encode(
										array(
											'img_path'           => CB_RATINGSYSTEM_PLUGIN_DIR_IMG,
											'hints'              => json_encode( $cCriteria ),
											'is_rated'           => 1,
											'ratingsCount'       => $ratingsCount,
											'editorRatingsCount' => $editorCount,
											'lastcomment'        => $lastcomment,
											'theme_key'          => $reviewOptions['theme'],
											'firstcomment'       => true ,// false
                                            'comment_status'     => $insertArray['comment_status']
										)
									);
									echo $encoded;
								}
							}
						}
					} else { //at least one rating done
						$summary = CBRatingSystemData::get_ratings_summary( array( 'form_id' => array( $insertArray['form_id'] ), 'post_id' => array( $insertArray['post_id'] ) ) );

						if ( ! empty( $summary[0] ) ) {
							foreach ( $summary[0]['per_criteria_rating_summary'] as $cId => $criteria ) {
								$cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count( $criteria['stars'] );
								$cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = $criteria['value'];
							}
							if ( ! empty( $ratingAverage['customUser'] ['perCriteria']['editor'] ) ) {
								foreach ( $ratingAverage['customUser'] ['perCriteria']['editor'] as $cId => $criteria ) {
									$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count( $ratingAverage['avgPerCriteria'][$cId]['stars'] );
									$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = $criteria['value'];
								}
							} else {
								if ( ! empty( $summary[0] ) ) {
									foreach ( $summary[0]['per_criteria_rating_summary'] as $cId => $criteria ) {
										$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count( $ratingAverage['avgPerCriteria'][$cId]['stars'] );
										$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = 0;
									}
								}
							}


							$cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = $rating['per_post_rating_summary'];
							if ( ! empty( $ratingAverage['customUser'] ['perCriteria']['editor'] ) ) {
								$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = $ratingAverage['customUser']['perPost']['editor'];
							} else {
								$cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = 0;
							}


                            $encoded = json_encode(
								array(
									'img_path'     => CB_RATINGSYSTEM_PLUGIN_DIR_IMG,
									'hints'        => json_encode( $cCriteria ),
									'is_rated'     => 1,
									'ratingsCount' => $summary[0]['per_post_rating_count'],
									'errorMessage' => __( 'You have already rated this.', 'cbratingsystem' )
								)
							);

							echo $encoded;
						} else {
							$encoded = json_encode(
								array(
									'is_rated'     => 1,
									'errorMessage' => __( 'An error occurred while storing data. Please ensure that all data are resonable. If problem persist please contact the administrator.', 'cbratingsystem' ),
								)
							);

							echo $encoded;
						}
					}
					//end you already submitted your rating.
				}
			}
		}

		die();
	}

	/**
	 * Get the single review via ajax
	 *
	 * @param array $reviewOptions
	 * @param array $ratingFormArray
	 * @param int   $lastcommentid
	 * @param bool  $ajax
	 *
	 * @return array|string
	 */
	public static function build_user_rating_review_single( $reviewOptions = array(), $ratingFormArray = array(), $lastcommentid, $ajax = false ) {
		global $wpdb;

        $firstLabel = '' ;

		$post_id = ( ! empty( $reviewOptions['post_id'] ) ? $reviewOptions['post_id'] : get_the_ID() );
		if ( ! empty( $reviewOptions['form_id'] ) ) {
			$ratingFormId = $reviewOptions['form_id'];
		} else {
			$defaultFormId = get_option( 'cbratingsystem_defaultratingForm' );
			$ratingFormId  = apply_filters( 'rating_form_array', $defaultFormId );
		}
		if ( is_string( $reviewOptions['theme'] ) and ! empty( $reviewOptions['theme'] ) ) {
			$theme_key = $reviewOptions['theme'];
		} else {
			$theme_key = 'basic';
		}

		$reviews = CBRatingSystemData::get_user_ratings_with_ratingForm_lastID( $lastcommentid, true );
		$output = $mainContent = '';
		if ( ! empty( $reviews[0] ) ) {
			if ( ! empty( $reviews ) and is_array( $reviews ) ) {
				$jsArray      = array();
				$shownReviews = 0;	
				
					foreach ( $reviews as $reviewKey => $review ) {
						$mainContent .= '<div id="cbrating-' . $ratingFormId . '-review-' . $review->id . '" data-review-id="' . $review->id . '" data-post-id="' . $post_id . '" data-form-id="' . $ratingFormId . '" class="cbratingsinglerevbox reviews_wrapper_' . $theme_key . '_theme review_wrapper review_wrapper_post-' . $post_id . '_form-' . $ratingFormId . ' review_wrapper_post-' . $post_id . '_form-' . $ratingFormId . '_review-' . $review->id . '">';
						$mainContent .= '    <div class="cbratingboxinner '.$reviewOptions['comment_status'].' reviews_rating_' . $theme_key . '_theme review_rating review_rating_review-' . $review->id . '">';
	
						if ( ! empty( $review->rating ) and is_array( $review->rating ) ) {
	
							// User Details part.
								if ( $review->user_id != 0 ) {

                                    $user_url = get_author_posts_url( $review->user_id );
                                    $name     = get_the_author_meta( 'display_name', $review->user_id );
                                    if(!empty($user_url) && $ratingFormArray ['show_user_link_in_review']  == '1' ){
                                        $name = '<a target="_blank" href="' . $user_url . '">'.$name .'</a>';
                                    }
                                    $name      = apply_filters('cbrating_edit_review_user_link' , $name ,  $review->user_id);

                                    //finally check the settings
                                    if($ratingFormArray ['show_user_avatar_in_review']  == '1'){
                                        $gravatar = get_avatar( $review->user_id, 36 );
                                    }
                                    else{
                                        $gravatar = '';
                                    }

                                    $gravatar    = apply_filters('cbrating_edit_review_user_avatar' , $gravatar ,   $review->user_id);

                                    $user_html ='  <p class="cbrating_user_name">' . ( ! empty( $user_url ) ? '<span class="user_gravatar">' . $gravatar . $name. '</span>' : '<span class="user_gravatar">' . $gravatar . $name . '</span>' ) . '</p>';

                                    if(isset($ratingFormArray['buddypress_active']) && $ratingFormArray['buddypress_active'] == '1'){

                                        if(function_exists('bp_is_active')){

                                            $rating_review_filtered_authorlink = apply_filters('cbratingsystem_buddypress_authorlink', array('show_image' => $ratingFormArray['show_user_avatar_in_review'] , 'show_link' => $ratingFormArray['show_user_link_in_review'] ,'review_user_id'=>$review->user_id,'user_html'=>$user_html));
                                            $user_html = $rating_review_filtered_authorlink['user_html'];

                                        }

                                    }

								} else {

									$user_url = '';
									$name     = ( ! empty( $review->user_name ) ? $review->user_name : 'Anonymous' );
                                    if($ratingFormArray ['show_user_avatar_in_review']  == '1'){
                                        $gravatar  = get_avatar( 0, 36, 'gravatar_default' );
                                    }
                                    else{
                                        $gravatar = '';
                                    }
                                    $user_html ='  <p class="cbrating_user_name">' . ( ! empty( $user_url ) ? '<span class="user_gravatar">' . $gravatar . $name . '</span>' : '<span class="user_gravatar">' . $gravatar . $name . '</span>' ) . '</p>';
								}
                                $user_html =  apply_filters('cbrating_edit_review_user_info' , $user_html , $review->user_id , $ratingFormArray , $reviewOptions, $review );


								$mainContent .= '  <div class="reviews_user_details_' . $theme_key . '_theme review_user_details">
                                            			'.$user_html.'
                                            			<span class="user_rate_time"><a title="' . date( 'l, F d, Y \a\t j:ia', $review->created ) . '" href="' . get_permalink( $post_id ) . '#cbrating-' . $ratingFormId . '-review-' . $review->id . '">' . CBRatingSystemFunctions :: codeboxr_time_elapsed_string( $review->created ) . '</a></span>
                                        			</div>
                                        			<div class="clear" style="clear:both;"></div>
                        		';

								// Criteria Display part.
								$mainContent .= '<div data-form-id="' . $ratingFormId . '" class="all_criteria_warpper_' . $theme_key . '_theme all-criteria-wrapper all-criteria-wrapper-form-' . $ratingFormId . '">';

								
								foreach ( $review->rating as $criteriId => $value ) {
									if ( is_numeric( $criteriId ) ) {
										$value                                                                                                            = ( ( $value / 100 ) * $review->rating[$criteriId . '_starCount'] );
										$jsArray['review'][$review->id]['ratingForm']                                                                     = $ratingFormId;
										$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_value']       = $value;
										$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_count']       = $review->rating[$criteriId . '_starCount'];
										$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_redOnlyHint'] = $review->rating[$criteriId . '_stars'][$value-1];


										$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_hints']       = $review->rating[$criteriId . '_stars'];
										$imgtitle                                                                                                         = '';

										$hello    = '';
										$i        = 0;
										$ratvalue = round( $value );

		                                for ( ; $i < $ratvalue; $i ++ ) {
		                                    $hello .= '<img src="' . plugins_url( '/images/star-on.png', __FILE__ ) . '" alt="' . $i . '" title="' . $imgtitle . '">';

		                                }


		                                $whiteimg = $review->rating[$criteriId . '_starCount'] - $ratvalue;
		                                for ( $j = 0; $j < $whiteimg; $j ++ ) {
		                                    $hello .= '<img src="' . plugins_url( '/images/star-off.png', __FILE__ ) . '" alt="' . $j . '" title="' . $imgtitle . '">';

		                                }


										$mainContent .= '<div data-form-id="' . $ratingFormId . '" data-criteria-id="' . $criteriId . '" class="criteria_warpper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $criteriId . ' criteria-id-wrapper-' . $criteriId . '-form-' . $ratingFormId . ' ">
			                                                <div class="criteria_label_warpper_' . $theme_key . '_theme criteria-label-wrapper">
			                                                    <span class="criteria-label criteria-label-id-' . $criteriId . '" ><strong>' . __( $review->custom_criteria[$criteriId]['label'], 'cbratingsystem' ) . '</strong></span>
			                                                </div>
			                                                <div id="criteria-star-wrapper-' . $review->id . '"  data-form-id="' . $ratingFormId . '" data-criteria-id="' . $criteriId . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $criteriId . '-form-' . $ratingFormId . '" >

			                                                    <!--input data-criteria-id="' . $criteriId . '" class="criteria-star criteria-star-label-id-' . $criteriId . '-currentScore" type="hidden" id="criteria-star" name="criteria[' . $criteriId . '][value]" value="" /-->
			                                                </div>
			                                                <div class="readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $ratingFormId . '-label-' . $criteriId . '">
			                                                    <span class="starTitle">' . ( sanitize_text_field( $review->rating[$criteriId . '_stars'][( $value - 1 )] ) ) . '</span>
			                                                </div>
		                                            </div>';

									}
								}

								$mainContent .='</div><div class="clear" style="clear:both;"></div>';
								// Question Display part.
								$mainContent .= '    <div data-form-id="' . $ratingFormId . '" class="question_wrapper_' . $theme_key . '_theme question-wrapper question-wrapper-form-' . $ratingFormId . '">';

								if ( ! empty( $review->question ) && is_array( $review->question ) ) {

									//print_r($review->question);

									foreach ( $review->question as $questionId => $value ) {

										if ( is_array( $value ) ) {

											$single_question =  $review->custom_question[$questionId];
											//cbxdump($single_question);

											$type       = $single_question['field']['type'];
											$seperated  = isset($fieldArr['seperated']) ? intval($fieldArr['seperated']): 0;
											$fieldArr   = $single_question['field'][$type];





											//$type       = $review->custom_question['enabled'][$questionId]['field']['type'];
											//$fieldArr   = $review->custom_question['enabled'][$questionId]['field'][$type];
											//$seperated  = $fieldArr['seperated'];
											$valuesText = array();
		
											foreach ( $value as $key => $val ) {

												$valuesText[$review->id][$questionId][] = '<strong>' . __( stripcslashes( $fieldArr[$key]['text'] ), 'cbratingsystem' ) . '</strong>';
											}
		
											if ( ( ! empty( $valuesText ) ) ) {
												$mainContent .= '
		                                        <div data-form-id="' . $ratingFormId . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
		                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
		                                                <span class="question-label question-label-id-' . $questionId . '" >' . ( isset( $review->custom_question[$questionId] ) ? __( stripslashes( $review->custom_question[$questionId]['title'] ), 'cbratingsystem' ) : '' ) . '</span>
		                                                <span class="question-label-hiphen">' . ( isset( $review->custom_question[$questionId] ) ? ' - ' : '' ) . '</span>
		                                                <span class="answer"><strong>' . ( implode( ', ', $valuesText[$review->id][$questionId] ) ) . '</strong></span>
		                                            </div>
		                                        </div>
		                                        ';
											}
										} else {
											/*$type      = $review->custom_question['enabled'][$questionId]['field']['type'];
											$fieldArr  = $review->custom_question['enabled'][$questionId]['field'][$type];
											$seperated = $fieldArr['seperated'];*/

											$single_question =  $review->custom_question[$questionId];
											$seperated  = isset($fieldArr['seperated']) ? intval($fieldArr['seperated']): 0;

											$type       = $single_question['field']['type'];

	
											if ( $seperated == 0 ) {
												if ( $type == 'text' ) {
													$mainContent .= '
			                                        <div data-form-id="' . $ratingFormId . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
			                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
			                                                <span class="question-label question-label-id-' . $questionId . '" >' . ( isset( $review->custom_question[$questionId] ) ? __( stripslashes( $review->custom_question[$questionId]['title'] ), 'cbratingsystem' ) : '' ) . '</span>
			                                                <span class="question-label-hiphen">' . ( isset( $review->custom_question[$questionId] ) ? ' - ' : '' ) . '</span>
			                                                <span class="answer"><strong>' . $value . '</strong></span>
			                                            </div>
			                                        </div>';
												} else {
													$fieldArr   = $single_question['field'][$type];
													$mainContent .= '
			                                        <div data-form-id="' . $ratingFormId . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
			                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
			                                                <span class="question-label question-label-id-' . $questionId . '" >' . ( isset( $review->custom_question[$questionId] ) ? __( stripslashes( $review->custom_question[$questionId]['title'] ), 'cbratingsystem' ) : '' ) . '</span>
			                                                <span class="question-label-hiphen">' . ( isset( $review->custom_question[$questionId] ) ? ' - ' : '' ) . '</span>
			                                                <span class="answer"><strong>' . ( ( $value == 1 ) ? __( 'Yes', 'cbratingsystem' ) : __( 'No', 'cbratingsystem' ) ) . '</strong></span>
			                                            </div>
			                                        </div>';
												}

											}
										}//end of else
									}//end of foreach
								}
						$mainContent .= '    </div>
                                        	<div class="clear" style="clear:both;"></div>';

						            // Comment Display part
									if ( ! empty( $review->comment ) and is_string( $review->comment ) ) {
										$comment = CBRatingSystemFunctions :: text_summary_mapper( $review->comment );
									if ( is_array( $comment ) and ! empty( $comment['summury'] ) and isset( $comment['rest'] ) ) {
										$comment_output = ' <p class="comment">
		                                                    ' . stripslashes( $comment['summury'] ) .
											( ! empty( $comment['rest'] ) ?
												'   <span style="display:none;" class="read_more_paragraph disable_field">' . $comment['rest'] . '</span>' : '' ) .
											'</p>
		                                    <a href="#" class="js_read_link read_more"> ...More</a>';
									} else {
										$comment_output = '<p class="comment">' . $comment['summury'] . '</p>';
									}
                                    if($reviewOptions['comment_status'] != 'approved'){

                                        if($reviewOptions['comment_status'] == 'unverified'){
                                           $review_status = 'Your comment is'. $reviewOptions['comment_status'].'[please check your mail to verify]';
                                        }
                                        else{
                                            $review_status =  $reviewOptions['comment_status'];
                                        }

                                             $mainContent .= '<div class="review_user_rating_comment_' . $theme_key . '_theme review_user_rating_comment">
		                                            	        <strong>Comment : </strong> ' . $comment_output .' ('.$review_status.')'.'
		                                        	          </div>
		                                        	          <div class="clear" style="clear:both;"></div>
		                                     ';
                                        }
                                    else{
                                             $mainContent .= '<div class="review_user_rating_comment_' . $theme_key . '_theme review_user_rating_comment">
		                                            	            <strong>Comment : </strong> ' . $comment_output .'
		                                        	          </div>
		                                        	         <div class="clear" style="clear:both;"></div>
		                                     ';

                                        }

								}

								
							}// end if ( ! empty( $review->rating ) and is_array( $review->rating ) )
						$mainContent .='</div>';
						$mainContent .='</div><div class="clear" style="clear:both;"></div>';
						$shownReviews ++;
					}// end foreach 
					$output .= $mainContent;
			}// end  if ( ! empty( $reviews ) and is_array( $reviews ) )
			$jsSettings = self::front_end_review_js_settings( $reviews, $jsArray, $post_id, $ajax );
			$output .= '<script type="text/javascript">' . $jsSettings . '</script>';

			if ( $ajax === true ) {
				return array(

					'html' => $mainContent . '<script type="text/javascript">' . $jsSettings . '</script>',

				);
			}
			$output = array( $output, $review );

			return $output;
			
		}// end  empty( $reviews[0] 

	}

	/**
	 * Front end js review
	 *
	 * @param      $reviews
	 * @param      $jsArray
	 * @param      $post_id
	 * @param bool $ajax
	 *
	 * @return string
	 */
	public static function front_end_review_js_settings( $reviews, $jsArray, $post_id, $ajax = false ) {
		$js = '';

		If ( ! empty( $jsArray['review'] ) ) {
			foreach ( $jsArray['review'] as $review => $reviewArr ) {
				$JSON['review_' . $review] = array(
					'img_path'    => CB_RATINGSYSTEM_PLUGIN_DIR_IMG,
					'options'     => json_encode( $jsArray['review'][$review]['criteria'] ),
					'cancel_hint' => __( 'Click to cancel given rating', 'cbratingsystem' ),
					'is_rated'    => 1,
				);
			}

			if ( $ajax === true ) {
				$js .= '
                    var reviewContent_post_' . $post_id . '_form_' . $reviewArr['ratingForm'] . '_ajax = ' . json_encode(
						$JSON
					) . ';
                ';
			} else {
				$js .= '
                    var reviewContent_post_' . $post_id . '_form_' . $reviewArr['ratingForm'] . ' = ' . json_encode(
						$JSON
					) . ';
                ';
			}
		}

		$js .= '
            var cbrpRatingFormReviewContent = ' . json_encode(
				array(
					'failure_msg' => __( 'An error occurred while processing the data. Please ensure that all data are resonable. If problem persist please contact the administrator.', 'cbratingsystem' ),
				)
			) . ';
        ';

		return $js;
	}

    /**
     * @param $ratingFormArray
     * @param $post_id
     * @param int $user_id
     * @return mixed
     */
    public static function viewPerCriteriaRatingResult( $ratingFormArray, $post_id, $user_id = 0 ) {
		//$time1 = microtime();
		if ( ! empty( $post_id ) ) {
			if ( ! is_array( $ratingFormArray ) and is_numeric( $ratingFormArray ) ) {
				$ratingFormArray = CBRatingSystemData::get_ratingForm( $ratingFormArray );
			}

			if ( is_array( $ratingFormArray ) ) {

				$data['form_id']                                                                 = $ratingFormArray['id'];
				$data['ratings']                                                                 = CBRatingSystemData::get_ratings( $ratingFormArray['id'], $post_id );
				$data['avgRatingArray']['ratingsCount'][$ratingFormArray['id'] . '-' . $post_id] = count( $data['ratings'] );

				foreach ( $data['ratings'] as $k => $rating ) {
					foreach ( $rating['rating'] as $cId => $value ) {
						if ( is_numeric( $cId ) ) {
							$data['ratingsValueArray'][$k]['criterias'][$cId]['value']          = $value;
							$data['ratingsValueArray'][$k]['criterias'][$cId]['count']          = count( $ratingFormArray['custom_criteria'][$cId]['stars'] );
							$data['ratingsValueArray'][$k]['criterias'][$cId]['criteria_array'] = $ratingFormArray['custom_criteria'][$cId]['stars'];
						}
					}
					$data['ratingsValueArray'][$k]['user_id'] = $data['ratings'][$k]['user_id'];
				}
				$data['criteria'] = $ratingFormArray['custom_criteria'];

				//echo '<pre>CBR:'; print_r($ratingFormArray); echo '</pre>'; die();

				$userIdToMatch = array(
					'guest'      => array( 0 ),
					'registered' => - 1,
				);

				//var_dump($ratingFormArray['editor_group']);

				$userWithCustomRole = new WP_User_Query( array( 'role' => $ratingFormArray['editor_group'][0], 'fields' => 'ID' ) );

				if ( ! empty( $userWithCustomRole->total_users ) ) {
					$userIds = $userWithCustomRole->results;

					$userIdToMatch['editor'] = $userIds;
				}

				$data['userIdToMatch'] = $userIdToMatch;


				//echo '<pre>CBR:'; print_r($userWithCustomRole); echo '</pre>'; die();

				CBRatingSystemCalculation::allUserPerCriteriaAverageCalculation( $data, $post_id );

				//echo '<pre>CBR:'; print_r($data['avgRatingArray']); echo '</pre>'; die();

				return $data['avgRatingArray'];
			}
		}
	}

    /**
     * @param $questionId
     * @param $questionOption
     * @param array $required
     * @param array $ratingFormArray
     * @param bool $hidden
     * @return string
     */
    public static function display_checkbox_field( $questionId, $questionOption, $required = array(), $ratingFormArray = array(), $hidden = false ) {

	   // cbxdump($questionOption);

	    $seperated = ( isset( $questionOption['field']['checkbox']['seperated'] ) ) ? intval($questionOption['field']['checkbox']['seperated']) : 0;
		unset( $questionOption['field']['checkbox']['seperated'] );

		$output = '';
		//$seperated = 0;
		if ( $seperated == 1 ) {
			$checkboxCount = count( $questionOption['field']['checkbox'] );
			//echo '<pre>'; var_dump(($questionOption['field']['checkbox'][$checkboxId]['text'])); echo '</pre>'; die();
			$output .= '
                <div data-q-id="' . $questionId . '" class="form_item_checkbox form_item_field_display form_item_checkbox_q_id-' . $questionId . ' ">
                    <label for="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '">' . stripslashes( $questionOption['title'] ) . $required['required_text'] . '</label>
            ';

			for ( $checkboxId = 0; $checkboxId < $checkboxCount; $checkboxId ++ ) {
				if ( ! empty( $questionOption['field']['checkbox'][$checkboxId]['text'] ) ) {
					$output .= '
                        <div class="add_left_margin">
                            <input data-form-id="' . $ratingFormArray['id'] . '" data-checkbox-field-text-id="' . $checkboxId . '" data-q-id="' . $questionId . '" type="checkbox" id="edit-custom-question-checkbox-field-text-' . $checkboxId . '-q-' . $questionId . '"
                                name="question[' . $ratingFormArray['id'] . '-' . $questionId . '-' . $checkboxId . ']"
                                value="' . ( $checkboxId + 1 ) . '"
                                class="form-text ' . $required['required_class'] . ' custom-question-field-checkbox-q-id-' . $questionId . ' custom-question-field-checkbox-' . $checkboxId . '-label-text-q-' . $questionId . '">
                            <label class="question-field-label question-field-checkbox-label label-q-' . $questionId . '-checkbox-' . $checkboxId . ' option mouse_normal"
                                for="edit-custom-question-checkbox-field-text-' . $checkboxId . '-q-' . $questionId . '"
                                >' . stripslashes( $questionOption['field']['checkbox'][$checkboxId]['text'] ) . '</label>

                        </div>
                    ';
				}
			}
			$output .= '    </div>';
		} else {
			$output .= '<label style="margin-right:20px;" for="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '">' . stripslashes( $questionOption['title'] ) . $required['required_text'] . '</label>';
			$output .= '<input data-q-id="' . $questionId . '" id="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '" class="form-text ' . $required['required_class'] . ' " type="checkbox" name="question[' . $ratingFormArray['id'] . '-' . $questionId . ']" value="1" />';
		}

		return $output;
	}

    /**
     * @param $questionId
     * @param $questionOption
     * @param array $required
     * @param array $ratingFormArray
     * @param bool $hidden
     * @return string
     */
    public static function display_radio_field( $questionId, $questionOption, $required = array(), $ratingFormArray = array(), $hidden = false ) {
		$seperated = 1;
		unset( $questionOption['field']['radio']['seperated'] );
		$output = '';

		if ( $seperated == 1 ) {
			$radioCount = count( $questionOption['field']['radio'] );

			$output .= '
                <div data-q-id="' . $questionId . '" class="form_item_radio form_item_field_display form_item_radio_q_id-' . $questionId . ' ">
                    <label for="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '">' . stripslashes( $questionOption['title'] ) . $required['required_text'] . '</label>
            ';

			for ( $radioId = 0; $radioId < $radioCount; $radioId ++ ) {
				if ( ! empty( $questionOption['field']['radio'][$radioId]['text'] ) ) {
					$output .= '
                        <div class="add_left_margin">
                            <input data-form-id="' . $ratingFormArray['id'] . '" data-radio-field-text-id="' . $radioId . '" data-q-id="' . $questionId . '" type="radio" id="edit-custom-question-radio-field-text-' . $radioId . '-q-' . $questionId . '"
                                name="question[' . $ratingFormArray['id'] . '-' . $questionId . ']"
                                value="' . ( $radioId + 1 ) . '"
                                class="form-text ' . $required['required_class'] . ' custom-question-field-radio-q-id-' . $questionId . ' custom-question-field-radio-' . $radioId . '-label-text-q-' . $questionId . '">
                            <label class="question-field-label question-field-radio-label label-q-' . $questionId . '-radio-' . $radioId . ' option mouse_normal"
                                for="edit-custom-question-radio-field-text-' . $radioId . '-q-' . $questionId . '"
                                >' . stripslashes( $questionOption['field']['radio'][$radioId]['text'] ) . '</label>
                        </div>
                    ';
				}
			}
			$output .= '    </div>';
		}

		return $output;
	}

    /**
     * @param $questionId
     * @param $questionOption
     * @param array $required
     * @param array $ratingFormArray
     * @param bool $hidden
     * @return string
     */
    public static function display_text_field( $questionId, $questionOption, $required = array(), $ratingFormArray = array(), $hidden = false ) {
		$output = '';
		$output .= '<label for="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '">' . stripslashes( $questionOption['title'] ) . $required['required_text'] . '</label>';
		$output .= '<input data-q-id="' . $questionId . '" id="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '" class="' . $required['required_class'] . ' add_left_margin" type="text" name="question[' . $ratingFormArray['id'] . '-' . $questionId . ']" value="" style="color:black;" />';

		return $output;
	}
}
