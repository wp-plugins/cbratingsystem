<?php
/**
 *
 */
require_once( ABSPATH . 'wp-admin/includes/template.php' );
if(class_exists('cbratingsystemaddonfunctions')){

    require_once( WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.'cbratingsystemaddon'.DIRECTORY_SEPARATOR.'cbratingsystemaddonfunctions.php');
}

/**
 * Class CBRatingSystemAdminReport
 */
class CBRatingSystemAdminReport extends CBRatingSystemAdmin {
    /**
     * cbCommentEditAjaxFunction
     */
    public static  $cb_user_comment_log_data = array(

    );



    /**
     * cbCommentAjaxFunction
     */
    public static function cbCommentAjaxFunction(){


	    //cbratingsystemcomment
        if ( isset( $_POST['cbRatingData'] ) and ! empty( $_POST['cbRatingData'] ) ) {



            $returnedData                  = $_POST['cbRatingData'];

	        //verify nonce
	        check_ajax_referer( 'cbratingsystemcomment-'.$returnedData['id'],'nonce');

            $insertArray['id']             = $returnedData['id'];


            $insertArray['form_id']        = (int)$returnedData['form_id'];
	        $insertArray['post_id']        = (int)$returnedData['post_id'];

            //$insertArray['form_id']        = (int) $insertArray['form_id'];
	        //$insertArray['post_id']        = (int) $insertArray['post_id'];

	        $insertArray['comment_status'] = $returnedData['comment_status'];
            $id      = array($insertArray['id']);
            $post_id = array($insertArray['post_id']);
            $form_id = array($insertArray['form_id']);
            if( $insertArray['comment_status'] != 'delete'){

                $return                        = CBRatingSystemData::update_rating_comment( $insertArray );
            }
            else{
                $return                        = CBRatingSystemData::delete_ratings($id,$post_id,$form_id);
            }
            $cb_return_data = __('Saved','cbratingsystem');
            echo json_encode($cb_return_data);
            die();

        }

    }

    /**
     *
     */
    public static function logReportPageOutput() {

		$path = admin_url( 'admin.php?page=rating_reports' );

		if ( ! empty( $_GET['sortby'] ) && ( ! empty( $_GET['sort'] ) ) ) {
			$sorbys = array(
				'post_id', 'post_title', 'form_id', 'avg', 'time'
			);
			$sort   = ( $_GET['sort'] == 'asc' ) ? 'asc' : 'desc';
			$sortby = ( in_array( $_GET['sortby'], $sorbys ) ? $_GET['sortby'] : '' );
		} else {
			$sort   = 'desc';
			$sortby = '';
		}

		$postID = ( ( ! empty( $_GET['post'] ) and ( is_numeric( $_GET['post'] ) ) ) ? (integer) ( $_GET['post'] ) : '' );
		$formID = ( ( ! empty( $_GET['form'] ) and ( is_numeric( $_GET['form'] ) ) ) ? (integer) ( $_GET['form'] ) : '' );
		$userID = ( ( ! empty( $_GET['user'] ) and ( is_numeric( $_GET['user'] ) ) ) ? (integer) ( $_GET['user'] ) : '' );

		$data = CBRatingSystemData::get_user_ratings_with_ratingForm( array( $formID ), array( $postID ), array( $userID ), '', $sortby, $sort, array(), true );

		?>

		<div class="wrap columns-2">
		<div class="icon32 icon32_cbrp_admin icon32-cbrp-user-ratings" id="icon32-cbrp-user-ratings"><br></div>
		<h2><?php _e( 'Codeboxr Rating System User Rating Logs', 'cbratingsystem' ) ?></h2>

		<div class="metabox-holder has-right-sidebar" id="poststuff">

		<div class="messages"></div>

		<div id="post-body" class="post-body">

		<div class="cbrp-log-report">
		<form method="post">
			<div style="clear: both;"></div>
			<div class="stuffbox">

					<?php if ( $data ) : ?>

						<?php foreach ( $data as $rowId => $rows ) : ?>

								<?php

								$log_id_hidden  = $rows->form_id . '-' . $rows->post_id . '-' . $rows->id; // (form_id)-(post_id)-(log_id)
								$log_id         = $rows->id;
								$user_type      = ( $rows->user_id > 0 ) ? sprintf( __( 'User%s', 'cbratingsystem' ), " (ID: $rows->user_id)" ) : __( 'Guest', 'cbratingsystem' );
								$user_name      = ( ( $rows->user_id > 0 ) ? get_the_author_meta( 'display_name', $rows->user_id ) : ( ! empty( $rows->user_name ) ? $rows->user_name : 'Anonymous' ) );
								$user_email     = ( ( $rows->user_id > 0 ) ? get_the_author_meta( 'email', $rows->user_id ) : ( ! empty( $rows->user_email ) ? $rows->user_email : '--' ) );
								$log_post_id    = $rows->post_id;
								$log_post_title = $rows->post_title;
								$log_post_type  = $rows->post_type;
								$log_form_id    = $rows->form_id;
								$log_average    = ( $rows->average > 0 ) ? '<strong>' . ( ( $rows->average / 100 ) * 5 ) . ' / 5</strong>' : '-';

								if ( ! empty( $rows->rating ) ) {

									$log_criteria_rating = '<ul>';
									foreach ( $rows->rating as $cId => $value ) {

										if ( is_numeric( $cId ) ) {

											$log_criteria_rating .= '<li>' . $rows->custom_criteria[$cId]['label'] . ': <strong>' . number_format( ( $value / 100 ) * count( $rows->custom_criteria[$cId]['stars'] ), 2 ) . '/' . count( $rows->custom_criteria[$cId]['stars'] ) . '</strong>';
										}
									}
									$log_criteria_rating .= '</ul>';
								} else {
									$log_criteria_rating = '-';
								}

								$log_q_a    = '';
								$valuesText = array();

								if ( ! empty( $rows->question ) and is_array( $rows->question ) ) {
									$log_q_a .= '<ul>';

									foreach ( $rows->question as $questionId => $value ) {

										$ratingFormId = $rows->form_id;
										$type         = $rows->custom_question[$questionId]['field']['type'];

                                        if(array_key_exists($type,$rows->custom_question[$questionId]['field']))
										$fieldArr     = $rows->custom_question[$questionId]['field'][$type];
                                        else $fieldArr     = array();


                                        if(array_key_exists('seperated', $fieldArr))
											$seperated    = $fieldArr['seperated'];
                                        else $seperated  = 0;

										if ( is_array( $value ) ) {
											foreach ( $value as $key => $val ) {
												$valuesText[$rows->form_id][$questionId][] = '<strong>' . __( stripcslashes( $fieldArr[$key]['text'] ), 'cbratingsystem' ) . '</strong>';
											}

											if ( ( ! empty( $valuesText ) ) ) {
												$log_q_a .= '
                                                                        <li>
                                                                            <div data-q-id="' . $questionId . '" class="question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
                                                                                <div class="question-label-wrapper">
                                                                                    <span class="question-label question-label-id-' . $questionId . '" >' . ( isset( $rows->custom_question[$questionId] ) ? __( stripslashes( $rows->custom_question[$questionId]['title'] ), 'cbratingsystem' ) : '' ) . '</span>
                                                                                    <span class="question-label-hiphen">' . ( isset( $rows->custom_question[$questionId] ) ? ' - ' : '' ) . '</span>
                                                                                    <span class="answer"><strong>' . ( implode( ', ', $valuesText[$rows->form_id][$questionId] ) ) . '</strong></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    ';
											}
										} else {
											if ( $seperated == 0 ) {

												$log_q_a .= '
                                                                        <li>
                                                                            <div data-form-id="' . $ratingFormId . '" data-q-id="' . $questionId . '" class="question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
                                                                                <div class="question-label-wrapper">
                                                                                    <span class="question-label question-label-id-' . $questionId . '" >' . ( isset( $rows->custom_question[$questionId] ) ? __( stripslashes( $rows->custom_question[$questionId]['title'] ), 'cbratingsystem' ) : '' ) . '</span>
                                                                                    <span class="question-label-hiphen">' . ( isset( $rows->custom_question[$questionId] ) ? ' - ' : '' ) . '</span>
                                                                                    <span class="answer"><strong>' . ( ( $value == 1 ) ? __( 'Yes', 'cbratingsystem' ) : __( $value, 'cbratingsystem' ) ) . '</strong></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    ';
											}
										}
									}

									$log_q_a .= '</ul>';
								}

								//$log_comment        = ( $rows->comment ) ? CBRatingSystemFunctions :: text_summary_mapper( $rows->comment ) : '-';
								$log_comment        = ( $rows->comment ) ? stripslashes($rows->comment) : '-';
								$log_comment_status = $rows->comment_status;

								$log_date = date( 'M d, Y @ H:m', $rows->created );
								$log_host_ip = $rows->user_ip;
								?>
                                <?php

                                    $comment_status_list = array('delete','unapproved','approved','spam');
                                    $comment_status = $log_comment_status;
                                ?>
                                 <?php array_push(CBRatingSystemAdminReport::$cb_user_comment_log_data,array('ID'=>$log_id,'id'=>$log_id,'user_name' =>$user_name,'user_id' =>$userID,'userinfo'  =>$user_email,'post'=>$log_post_id,'type' =>$log_post_type,'posttitle'=>$log_post_title, 'formid'   =>$log_form_id,'avgrating' =>$log_average, 'criteriarating' =>$log_criteria_rating, 'qa'  =>
                                    $log_q_a,'comment'  =>$log_comment, 'commentstatus'  =>$comment_status,'date'  =>$log_date,'ip'=>$log_host_ip
                            ));


                            ?>

						<?php endforeach; ?>
					<?php else : //echo "<td colspan='13' align='center'>No Results Found</td>"; ?>
					<?php endif; ?>

			                </div>
		                </form>
		            </div>
		        </div>
		    </div>
		</div>

	<?php
         $user_log =  new Cbratinguserlog(array());
         $user_log->prepare_items();
        ?>
        <div class="cbratinguserlog">
            <form id="user-filter" method="get">

                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <?php
                $user_log->display();?>
            </form>
        </div>


       <?php
	}

}

require_once( ABSPATH . 'wp-admin/includes/template.php' );

if(!class_exists('WP_List_Table')){

    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Cbratinguserlog
 */
class Cbratinguserlog extends WP_List_Table{

    public  $cb_user_comment_log_data = array(

    );

    public   $cb_rating_avg_data_ = array(

    );

    /**
     * @param array $args
     */
    public function __construct($args = array() ) {

        parent::__construct( array(

            'singular'  => 'wdcheckbox',     //singular name of the listed records
            'plural'    => 'wdcheckboxs',
            'ajax'      => false ,
            'screen'    => isset( $args['screen'] ) ? $args['screen'] : null,

        ) );

        $this->cb_user_comment_log_data = CBRatingSystemAdminReport::$cb_user_comment_log_data;

    }

    /**
     * @param $item
     * @param $column_name
     * @return mixed
     */
    public  function column_default($item, $column_name){ // this columns takes the valus from emample data with keys and echo it
        switch($column_name){

            case 'id':
            case 'userinfo':
            case 'post':
            case 'type':
            case 'formid':
            case 'avgrating':
            case 'criteriarating':
            case 'qa':
            case 'comment':

            return $item[$column_name];

            default:
            return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * @param $item
     * @return string
     */
    public function column_userinfo($item){
       // var_dump($item);
        $output = '';
        $output .='<p id="user-log-'.$item['formid'].'-'.$item['post'].'-'.$item['userinfo'].'" ><strong>User Name:</strong>'.$item['user_name'].'</p>';
        $output .='<p><strong>User Email:</strong><a href="'.get_edit_user_link().'">'.$item['userinfo'].'</a></p>';
        $output .='<p><strong>User IP:</strong>'.$item['ip'].'</p>';
        return sprintf($output);
    }

    /**
     * @param $item
     * @return string
     */
    public function column_id($item){

        $actions = array(
            'edit'      => sprintf('<a href="?">'.__('Edit','cbratingsystem').'</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?">'.__('Delete','cbratingsystem').'</a>',$_REQUEST['page'],'delete',$item['ID']),
        );

        return sprintf('<span class = "user-rating-log-%1$s" style="">%1$s </span>',
            /*$1%s*/ $item['id'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    /**
     * @param $item
     * @return string
     */
	/*
    public function column_commentstatus($item){
	    //this function is not used any more.
	   // var_dump('useful');

        $log_comment_status     = '';
        $comment_status_list    = array('delete','unapproved','approved','spam');
        $comment_status         = $item['commentstatus'];
        $log_id                 = $item['id'];
        $log_form_id            = $item['formid'];
        $log_post_id            = $item['post'];
        $log_comment_label      = '<span class = "cb-comment-label cb-'.$comment_status.'">' .ucfirst($comment_status) .'</span><br>';
        $log_comment_status     .= '<a href="#" class="comment_status '.$comment_status_list[0].'" data-id="'.$log_id.'" data-form-id ="'.$log_form_id.'" data-post-id = "'.$log_post_id.'" data-comment-status ="'.$comment_status_list[0].'" > |Delete|</a>';
        $log_comment_status     .= '<a href="#" class="comment_status '.$comment_status_list[1].'" data-id="'.$log_id.'" data-form-id ="'.$log_form_id.'" data-post-id = "'.$log_post_id.'" data-comment-status ="'.$comment_status_list[1].'"  >|Unapprove|</a>';
        $log_comment_status     .= '<a href="#" class="comment_status '.$comment_status_list[2].'" data-id="'.$log_id.'" data-form-id ="'.$log_form_id.'" data-post-id = "'.$log_post_id.'" data-comment-status ="'.$comment_status_list[2].'"> |Approve|</a>';
        $log_comment_status     .= '<a href="#" class="comment_status '.$comment_status_list[3].'" data-id="'.$log_id.'" data-form-id ="'.$log_form_id.'" data-post-id = "'.$log_post_id.'" data-comment-status ="'.$comment_status_list[3].'">|Spam|</a>';
        $log_comment_status_wrapper  = '';
        $log_comment_status_wrapper .= '<p  data-status = "'.$comment_status.'" class="comment_status_column  column-'.$item['commentstatus'].'">'.$log_comment_label.$log_comment_status.'</p>';

        return $log_comment_status_wrapper;
    }//unused function, please check in addon
	*/
    /**
     * @param $item
     * @return string
     */
    public  function column_post($item){
        /*
        echo '<pre>';
        print_r($item);
        echo '</pre>';
        */

        $output ='<a id="review-id'.$item['id'].'" href="'.get_permalink( $item['post'] ).'"target="_blank" title ="'.$item['posttitle'].'">'.$item['post'].'</a>';
        return sprintf(
          $output             //The value of the checkbox should be the record's id
        );
    }

    /**
     * @param $item
     * @return string
     */
    public  function column_cb($item){

        return sprintf(
            '<input class="cbwdchkbox"  type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'] ,  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }

    /**
     * @param $item
     * @return string
     */
    public  function column_comment($item){

	    //$nonce = wp_create_nonce( 'my-action_'.$post->ID );

        $output                 = '';
        $output_date            = ' <div class ="cbratingdate"><p><strong>On: </strong>'.$item['date'].'</p></div>';
        $output_click           = apply_filters('cbratingsystem_commentedit_title','(Edit Option in premium version)');
        $cb_comment_box         = apply_filters('cbratingsystem_commentedit_class','cbratingcomment_noedit');
        $cb_comment_edit_box    = apply_filters('cbratingsystem_commenteditbox_class','cbratingcomment_editbox_noedit');


	    //var_dump($item['comment']);
		/*
        if ( is_array( $item['comment'] ) && ! empty(  $item['comment'] ['summury'] ) ) :
           $output .= ' <div class ="cbratingcomment_container cbratingcomment_container_'. $item['id']. '">
                <div class="'.$cb_comment_box.' cbratingcomment_'. $item['id'].'" data-id = "'.$item['id'].'" title ="'.$output_click.'">
                    '. $item['comment']['summury'];
                     if ( ! empty( $item['comment']['rest'] ) ) :
                         $output .='  <span class="read_more_span disable_field">'. $item['comment']['rest'].'</span>
                        <a href="#" class="js_read_link read_more"> ...More</a>';
                     endif;
           $output .='</div>';
           $output .=' <textarea style="display:none;" class="'.$cb_comment_edit_box.' cbratingcomment_edit_'. $item['id'].'" data-form-id ="'. $item['formid'].'" data-post-id = " '.$item['post'].'" data-id = "'.$item['id'].'"></textarea>
            </div>';
        else:
		*/
             $output .= '<div class ="cbratingcomment_container cbratingcomment_container_'. $item['id'].'">
                <div class="'.$cb_comment_box.' cbratingcomment_'.$item['id'].'" data-id = "'.$item['id'].'" title ="'.$output_click.'">
                    '.$item['comment'].'
                </div>
                <textarea style="display:none;" class="'.$cb_comment_edit_box.' cbratingcomment_edit_'.$item['id'].'" data-form-id ="'.$item['formid'].'" data-post-id = "'. $item['post'].'"  data-id = "'. $item['id'].'"></textarea>
            </div>';

        //endif;

        $log_comment_status  = '';
        $comment_status_list = array('delete','unapproved','approved','spam');
        $comment_status      = $item['commentstatus'];
        $log_id              = $item['id'];
        $log_form_id         = $item['formid'];
        $log_post_id         = $item['post'];
        $log_comment_status_wrapper  = '';

	    /*
        if(class_exists('cbratingsystemaddonfunctions')){

            $log_comment_status_wrapper .= cbratingsystemaddonfunctions ::cbratingsystem_comment_statuslabels($comment_status,$comment_status_list,$log_id,$log_post_id,$log_form_id);

        }// end of if class exists

	    */

	    $log_comment_status_wrapper = apply_filters('cbrating_comment_status_mod', $log_comment_status_wrapper, $comment_status, $comment_status_list, $log_id, $log_post_id, $log_form_id );

        $output = $output_date.'<div class ="cbratingdash_comment_wrapper">'.$output.$log_comment_status_wrapper.'</div>';

        return sprintf($output);

    }

    /**
     * @return array
     */
    public function get_columns(){
        $columns = array(
            'cb'                     => '<input type="checkbox"  />', //Render a checkbox instead of text
            'id'                     => __('ID: ','cbratingsystem'),
            'userinfo'               => __('User Info: ','cbratingsystem'),
            'post'                   => __('Post Id: ','cbratingsystem'),
            'type'                   => __('Post Type: ','cbratingsystem'),
            'formid'                 => __('Form Id: ','cbratingsystem'),
            'avgrating'              => __('Average Rating: ','cbratingsystem'),
            'criteriarating'         => __('Criteria Rating: ','cbratingsystem'),
            'qa'                     => __('Q/A: ','cbratingsystem'),
            'comment'                => __('Comment: ','cbratingsystem'),



        );
        return $columns;
    }

    /**
     * @return array
     */
    public function get_bulk_actions() {

        $bulk_actions = apply_filters('cbratingsystem_comment_status_bulk_action',array(

            'delete'            => __( 'Delete', 'cbratingsystem' )

        ));
        return $bulk_actions;

    }

    /**
     * process_bulk_action
     */
    public function process_bulk_action() {


        $action = $this->current_action();

        switch ( $action ) {

            case 'delete':

                    if(!empty($_GET['wdcheckbox']))
                    {
                        global $wpdb;
                        $avgid      = $_GET['wdcheckbox'];
                        $formIds    = array();
                        $postIds    = array();
                        $table_name1 = CBRatingSystemData::get_user_ratings_table_name();

                        foreach($avgid as $id){

                            $id      = (int)$id;

                            $sql     = $wpdb->prepare( "SELECT post_id ,form_id FROM $table_name1 WHERE id=%d ", $id );
                            $results = $wpdb->get_results( $sql,ARRAY_A);
                            array_push($formIds , $results[0]['form_id']) ;
                            array_push($postIds , $results[0]['post_id']);
                        }

                        $table_name1 = CBRatingSystemData::get_user_ratings_table_name();
                        $table_name  = CBRatingSystemData::get_user_ratings_summury_table_name();
                        $table_name  = CBRatingSystemData::get_user_ratings_table_name();
                        //$sql = $wpdb->prepare( "DELETE FROM $table_name WHERE id IN (" . implode( ',', $avgid ) . ")", null );
                        $sql = "DELETE FROM $table_name WHERE id IN (" . implode( ',', $avgid ) . ")";
                        $wpdb->query( $sql );

                       foreach($postIds as $index=>$id){

                           $formId = $formIds[$index];
                           $postId = $id;
                           $ratingAverage        = CBRatingSystemFront::viewPerCriteriaRatingResult( $formId, $postId );
                           //$perPostAverageRating = isset($ratingAverage['perPost'][$postId])? $ratingAverage['perPost'][$postId] : '';
                           $perPostAverageRating = $ratingAverage['perPost'][$postId];
                           $postType             = get_post_type( $postId );

                           $ratingsCount = $ratingAverage['ratingsCount'][$formId . '-' . $postId];

                           $rating = array(
                               'form_id'                     => $formId,
                               'post_id'                     => $postId,
                               'post_type'                   => $postType,
                               'per_post_rating_count'       => $ratingsCount,
                               'per_post_rating_summary'     => $perPostAverageRating,
                               'per_criteria_rating_summary' => maybe_serialize( $ratingAverage['avgPerCriteria'] ),
                           );

                           $success = CBRatingSystemData::update_rating_summary( $rating );


                        }
                        ?>
                        <script type="text/javascript">

                            jQuery(document).ready(function ($) {

                                $('.messages').show();
                                $('.messages').html('Successfully done');
                                var ids             = '<?php echo json_encode($avgid);?>';
                                var trainindIdArray = ids.split(',');

                                $.each(trainindIdArray, function(index, value) {

                                    value = value.replace('"','');
                                    value = value.replace('[','');
                                    value = value.replace(']','');
                                    value = value.replace('"','');
                                    $('.user-rating-log-'+value).parents('tr').hide();


                                });
                                setTimeout(function() {   //calls click event after a certain time
                                    $('.messages').hide();
                                }, 2000);

                            });

                    </script>
                <?php

                    }
                break;

            case 'approve':

                if(class_exists('cbratingsystemaddonfunctions')){


                    if(!empty($_GET['wdcheckbox']))
                    {
                        $avgid = $_GET['wdcheckbox'];
                        $cbsommentstatus = 'approved';
                        cbratingsystemaddonfunctions ::cbratingsystem_comment_statuschange($avgid,$cbsommentstatus);

                    }// end of if get

                }// end of if class exists

                break;
            case 'spam':

                if(class_exists('cbratingsystemaddonfunctions')){


                    if(!empty($_GET['wdcheckbox']))
                    {
                        $avgid = $_GET['wdcheckbox'];
                        $cbsommentstatus = 'spam';
                        cbratingsystemaddonfunctions ::cbratingsystem_comment_statuschange($avgid,$cbsommentstatus);

                    }// end of if get

                }// end of if class exists


                break;
            case 'unapprove':

                if(class_exists('cbratingsystemaddonfunctions')){


                    if(!empty($_GET['wdcheckbox']))
                    {
                        $avgid = $_GET['wdcheckbox'];
                        $cbsommentstatus = 'unapproved';
                        cbratingsystemaddonfunctions ::cbratingsystem_comment_statuschange($avgid,$cbsommentstatus);

                    }// end of if get

                }// end of if class exists


                break;

            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }

    /**
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'id'                    => array('id',false),
            'post'                  => array('post',false),
            'type'                  => array('type',false),
            'formid'                => array('formid',false),

        );
        return $sortable_columns;
    }

    /**
     * prepare_items
     */
    public function prepare_items() {

        global $wpdb; //This is used only if making any database queries
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->process_bulk_action();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = CBRatingSystemAdminReport::$cb_user_comment_log_data ;

        function usort_reorder($a,$b){

            $orderby    = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order      = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result     = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='desc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');

        $current_page = $this->get_pagenum();

        $total_items = count($data);

        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );

    } // end of function prepare_items
    /**
     * cb_create_table
     */
    public function cb_create_table(){

        self:: prepare_items();
        self::display();
    }


}//end of class
