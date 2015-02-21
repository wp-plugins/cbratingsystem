<?php

require_once( ABSPATH . 'wp-admin/includes/template.php' );
require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdmin.php' );
require_once( CB_RATINGSYSTEM_PATH . '/data.php' );

/**
 * Class CBratinglogreportoutput
 */
class CBratinglogreportoutput extends CBRatingSystemAdmin {

    public static  $cb_avg_comment_log_data = array(

    );

    /**
     * averageReportPageOutput
     */
    public static function averageReportPageOutput() {

        $path   = admin_url( 'admin.php?page=rating_avg_reports' );
        $sort   = (isset($_GET['sort']) &&  $_GET['sort'] == 'asc' ) ? 'asc' : 'desc';
        $sortby = ( ! empty( $_GET['sortby'] ) and (  $sort ) ) ? $_GET['sortby'] : 'form_id';
        $summaryData = CBRatingSystemData::get_ratings_summary( array(), $sortby, $sort, true );
        ?>
        <div class="wrap columns-2">
            <div class="icon32 icon32_cbrp_admin icon32-cbrp-rating-avg" id="icon32-cbrp-rating-avg"><br></div>
            <h2><?php _e( "Codeboxr Rating System Rating Average", 'cbratingsystem' ) ?></h2>
            <div class="metabox-holder has-right-sidebar" id="poststuff">
                <div id="message"
                </div>
                <div id="post-body" class="post-body">
                    <div id="stuff-box">
                        <!-- Starting of Average rating Listing -->
                        <div class="cbrp-log-report-average">
                            <form method="post">
                                <div style="clear: both;"></div>

                                <?php if ( $summaryData ) : ?>
                                    <?php foreach ( $summaryData as $rowId => $rows ) :
                                        if ( ! empty( $rows->per_post_rating_count ) and ( ! empty( $rows->per_post_rating_summary ) ) ) :
                                            ?>

                                            <?php
                                            $log_average = array();
                                            $log_post_id = $rows->post_id;
                                            $log_post_title = $rows->post_title;
                                            $log_form_id = $rows->form_id;
                                            $log_average = ( $rows->per_post_rating_summary > 0 ) ? "<strong>" . ( number_format( ( ( $rows->per_post_rating_summary / 100 ) * 5 ), 2 ) ) . " out of 5</strong>" : '-';

                                            $userRoleLabels = CBRatingSystem::user_role_label();

                                           /* if ( ! empty( $rows->custom_user_rating_summary ) ) {
                                               // var_dump($rows->custom_user_rating_summary);
                                                foreach ( $rows->custom_user_rating_summary as $userType => $avg ) {
                                                 //  echo '<pre>'; print_r($userRoleLabels[$userType]); echo '</pre>'; die();
                                                    $log_average .= '</br>' . $userRoleLabels[$userType] . ' : ';
                                                    //var_dump($avg );
                                                    $log_average .= ( ( (float) $avg > 0.0 ) ? "<strong>" . ( number_format( ( ( (float) $avg / 100 ) * 5 ), 2 ) ) . " out of 5</strong>" : '-' );
                                                }
                                            }*/

                                            $log_ratingCount = $rows->per_post_rating_count;
                                            $log_id          = $rows->id;

                                            $table   = CBRatingSystemData::get_user_ratings_table_name();
                                            global $wpdb;
                                            $sql     = $wpdb->prepare( "SELECT id FROM $table WHERE post_id=%d AND form_id=%d ", $log_post_id,$log_form_id );
                                            $results = $wpdb->get_results( $sql,ARRAY_A);
                                            $results =(maybe_unserialize($results[0]['id']));

                                                if ( ! empty( $rows->per_criteria_rating_summary ) ) {
                                                    $log_criteria_rating = '<ul>';
                                                    foreach ( $rows->per_criteria_rating_summary as $cId => $criteria ) {
                                                        $log_criteria_rating .= "<li>" . $criteria['label'] . ": <strong>" . ( number_format( ( $criteria['value'] / 100 ) * count( $criteria['stars'] ), 2 ) ) . " out of " . count( $criteria['stars'] ) . "</strong>";
                                                    }
                                                    $log_criteria_rating .= '</ul>';

                                                } else {
                                                    $log_criteria_rating = '-';
                                                }

                                            ?>
                                        <?php
                                        else : //echo "<td colspan='7' align='center'>No Results Found</td>";
                                        endif;
                                        array_push(CBratinglogreportoutput::$cb_avg_comment_log_data,array('id_user_table'=>$results,'id'=>$log_id,'rating_count'=>$log_ratingCount,'criteria_rating'  =>$log_criteria_rating,'post'=>$log_post_id,'posttitle'=>$log_post_title, 'formid'   =>$log_form_id,'avgrating' =>$log_average

                                        ));
                                    endforeach; ?>
                                    <?php else : //echo "<td colspan='7' align='center'>No Results Found</td>";
                                        ?>
                                    <?php endif;
                                    ?>
                            </form>
                        </div>
                        <!-- Ending of Average rating Listing -->
                    </div>
                </div>

            </div>
        </div>
        <?php $list_table = new Cbratingavglog();
        $list_table->prepare_items();
        ?>
        <form id="movies-filter" method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php $list_table->display();?>
        </form>
        <?php

    }

}

require_once( ABSPATH . 'wp-admin/includes/template.php' );
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Cbratingavglog
 */
class Cbratingavglog extends WP_List_Table{

    public  $cb_user_comment_log_data = array(

    );
    public   $cb_rating_avg_data_ = array(

    );

    /**
     *
     */
    public function __construct() {
        parent::__construct( array(
            'singular'  => 'wdcheckbox',     //singular name of the listed records
            'plural'    => 'wdcheckboxs',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        $this->cb_user_comment_log_data =CBratinglogreportoutput::$cb_avg_comment_log_data;
        //self::cb_create_table();
    }
    /**
     * @param $item
     * @param $column_name
     * @return mixed
     */
    public  function column_default($item, $column_name){ // this columns takes the valus from emample data with keys and echo it
        switch($column_name){
            case 'post':
            case 'posttitle':
            case 'formid':
            case 'avgrating':
            case 'criteria_rating':
            case 'rating_count':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * @param $item
     * @return string
     */
    public  function column_cb($item){
        return sprintf(
            '<input class="cbwdchkbox-%4$s"  type="checkbox" name="%1$s[]" value="%4$s" />',
            /*$1%s*/ $this->_args['singular'] ,
            /*$5%s*/ $item['id'] ,
            /*$4%s*/ $item['id'] ,//Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id'] ,
            /*$3%s*/ $item['id']  //The value of the checkbox should be the record's id
        );
    }

    /**
     * @return array
     */
    public function get_columns(){

        $columns = array(
            'cb'                                => '<input type="checkbox"  />', //Render a checkbox instead of text
            'post'                              => __( 'Post', 'cbratingsystem' ),
            'posttitle'                         => __( 'Post Title', 'cbratingsystem' ),
            'formid'                            => __( 'Form Id', 'cbratingsystem' ),
            'avgrating'                         => __( 'Average Rating', 'cbratingsystem' ),
            'criteria_rating'                   => __( 'Criteria Rating', 'cbratingsystem' ),
            'rating_count'                      =>  __( 'Rating Count', 'cbratingsystem' ),
        );

        return $columns;
    }

    /**
     * @return array
     */
    function get_bulk_actions() {

        $actions = array(
            'delete'    => __( 'Delete', 'cbratingsystem' )
        );
        return $actions;
    }

    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {

        if( 'delete'=== $this->current_action()  ) {

            if(!empty($_GET['wdcheckbox']))
                {
                     $avgid = $_GET['wdcheckbox'];

                    global $wpdb;
                    $table_name1    = CBRatingSystemData::get_user_ratings_summury_table_name();
                    $table_name     = CBRatingSystemData::get_user_ratings_table_name();
                    foreach($avgid as $id){
                        $sql        = $wpdb->prepare( "SELECT post_id ,form_id FROM $table_name1 WHERE id=%d ", $id );
                        $results = $wpdb->get_results( $sql,ARRAY_A);
                        //echo '<pre>'; print_r($results[0]['form_id']); echo '</pre>';
                        $sql         = $wpdb->prepare( "DELETE FROM $table_name WHERE post_id=%d AND form_id=%d", $results[0]['post_id'] ,$results[0]['form_id']);
                        $wpdb->query( $sql );

                    }
                    $table_name  = CBRatingSystemData::get_user_ratings_summury_table_name();
                    $sql         = $wpdb->prepare( "DELETE FROM $table_name WHERE id IN (" . implode( ',', $avgid ) . ")", null );
                    $wpdb->query( $sql );
                   // global $wpdb;


        }
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#message').show();
                    $('#message').html('Successfully deleted');
                    var ids = '<?php echo json_encode($avgid);?>';
                    var trainindIdArray = ids.split(',');
                    $.each(trainindIdArray, function(index, value) {
                        //alert(index + ': ' + value);   // alerts 0:[1 ,  and  1:2]
                        value = value.replace('"','');
                        value = value.replace('[','');
                        value = value.replace(']','');
                        value = value.replace('"','');
                        //console.log(value);
                        $('.cbwdchkbox-'+value).parent().parent().hide();

                    });
                    setTimeout(function() {   //calls click event after a certain time
                        $('#message').hide();
                    }, 2000);

                });

            </script>
<?php
    }
  }

    /**
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(

            'post'                  => array('post',false),
            'posttitle'             => array('posttitle',false),
            'formid'                => array('formid',false),
            'avgrating'             => array('avgrating',false),
            'criteria_rating'       => array('criteria_rating',false),
            'rating_count'          => array('rating_count',false)
        );

        return $sortable_columns;
    }

    /**
     *
     */
    public function prepare_items() {

        global $wpdb; //This is used only if making any database queries
        /**
         * First, lets decide how many records per page to show
         */
        $per_page   = 5;
        $columns    = $this->get_columns();
        $hidden     = array();
        $sortable   = $this->get_sortable_columns();

        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

        $data = CBratinglogreportoutput::$cb_avg_comment_log_data ;

        function usort_reorder($a,$b){

            $orderby    = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order      = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result     = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');

        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    } // end of function prepare_items
    /**
     *
     */
    public function cb_create_table(){

        self:: prepare_items();
        self::display();
    }


}// end of class
