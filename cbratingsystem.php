<?php
/*
  Plugin Name: CodeBoxr Multi Criteria Rating System
  Plugin URI: http://codeboxr.com/product/multi-criteria-flexible-rating-system-for-wordpress
  Description: Rating system for Posts and Pages from CodeBoxr.
  Version: 3.3.1
  Author: Codeboxr
  Author URI: mailto:info@codeboxr.com
 */
defined( 'ABSPATH' ) OR exit;

//define the constants
define( 'CB_RATINGSYSTEM_PLUGIN_VERSION', '3.3.1' ); //need for checking verson
define( 'CB_RATINGSYSTEM_FILE', __FILE__ );
define( 'CB_RATINGSYSTEM_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
define( 'CB_RATINGSYSTEM_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( CB_RATINGSYSTEM_FILE ) ) );
define( 'CB_RATINGSYSTEM_PLUGIN_NAME', 'Rating System' );
define( 'CB_RATINGSYSTEM_PLUGIN_SLUG_NAME', 'cbratingsystem' );
define( 'CB_RATINGSYSTEM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CB_RATINGSYSTEM_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'CB_RATINGSYSTEM_PLUGIN_DIR_IMG', plugin_dir_url( __FILE__ ) . 'images/' );
define( 'CB_RATINGSYSTEM_RAND_MIN', 0 );
define( 'CB_RATINGSYSTEM_RAND_MAX', 999999 );
define( 'CB_RATINGSYSTEM_COOKIE_EXPIRATION_14DAYS', time() + 1209600 ); //Expiration of 14 days.
define( 'CB_RATINGSYSTEM_COOKIE_EXPIRATION_7DAYS', time() + 604800 ); //Expiration of 7 days.
define( 'CB_RATINGSYSTEM_COOKIE_NAME', 'cbrating-cookie-session' );

//to handle multibyte
mb_internal_encoding('utf-8');

//require_once ABSPATH . 'wp-admin/includes/user.php';
//used for maximum database related operations
require_once( CB_RATINGSYSTEM_PATH . '/data.php' );

//used for core widgets
require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemWidget.php' );

//bootstrap the rating plugin
add_action( 'init', array( 'CBRatingSystem', 'init' ) );

//actions on install and on uninstall/delete
//plugin activation hook
register_activation_hook( __FILE__, array( 'CBRatingSystem', 'cbratingsystem_activation' ) );
//plugin deactivation hook
//register_deactivation_hook( __FILE__, array( 'CBRatingSystem', 'cbratingsystem_deactivation' ) ); //we are not using it still now

//plugin uninstall/delete hook
register_uninstall_hook( __FILE__, array( 'CBRatingSystem', 'cbratingsystem_uninstall' ) );

//init widgets
add_action( 'widgets_init', array( 'CBRatingSystem', 'initWidgets' ) );

/**
 * Class CBRatingSystem
 */
class CBRatingSystem {

	public static $version = CB_RATINGSYSTEM_PLUGIN_VERSION;
	//end of function cbuninstall_plugin
    /**
     * init plugin
     * include all pages
     * load language
     */
    public static function init() {

		load_plugin_textdomain( 'cbratingsystem', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		/* Runs on plugin activated */
        require_once( CB_RATINGSYSTEM_PATH . '/cbratinglogreportoutput.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/CBRatingSystemFunctions.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemFront.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemFrontReview.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdmin.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdminDashboard.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdminFormParts.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdminReport.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemCalculation.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemMetaBox.php' );
	    require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemTool.php' );
		require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemTheme.php' );


		add_filter( 'rating_form_array', array( 'CBRatingSystemMetaBox', 'ratingForm_add_meta_data_filter' ) );

        /**
         * load only in admin backend part
         *
         */
        if ( is_admin() ) {

			if ( class_exists( 'CBRatingSystemAdmin' ) ) {
				CBRatingSystemAdmin::init();
			}

            //ajax request to save review
			add_action( 'wp_ajax_nopriv_cbRatingAjaxFunction', array( 'CBRatingSystemFront', 'cbRatingAjaxFunction' ) );
			add_action( 'wp_ajax_cbRatingAjaxFunction', array( 'CBRatingSystemFront', 'cbRatingAjaxFunction' ) );

            // ajax request  for comment moderation
            add_action( 'wp_ajax_nopriv_cbCommentAjaxFunction', array( 'CBRatingSystemAdminReport', 'cbCommentAjaxFunction' ) );
            add_action( 'wp_ajax_cbCommentAjaxFunction', array( 'CBRatingSystemAdminReport', 'cbCommentAjaxFunction' ) );




            add_action( 'wp_ajax_nopriv_cbReviewAjaxFunction', array( 'CBRatingSystemFrontReview', 'cbReviewAjaxFunction' ) );
			add_action( 'wp_ajax_cbReviewAjaxFunction', array( 'CBRatingSystemFrontReview', 'cbReviewAjaxFunction' ) );

			add_action( 'wp_ajax_nopriv_cbAdminRatingFormListingAjaxFunction', array( 'CBRatingSystemAdmin', 'cbAdminRatingFormListingAjaxFunction' ) );
			add_action( 'wp_ajax_cbAdminRatingFormListingAjaxFunction', array( 'CBRatingSystemAdmin', 'cbAdminRatingFormListingAjaxFunction' ) );

			$customPostTypes = self::post_types();

			if ( ! empty( $customPostTypes['custom']['types'] ) ) {
				foreach ( $customPostTypes['custom']['types'] as $type => $typeLabel ) {
					add_filter( "manage_{$type}_posts_columns", array( 'CBRatingSystem', 'add_rating_column' ) );

					add_action( "manage_{$type}_posts_custom_column", array( 'CBRatingSystem', 'rating_column_content' ), 10, 2 );
				}
			}

			add_filter( 'manage_pages_columns', array( 'CBRatingSystem', 'add_rating_column' ) );
			add_filter( 'manage_posts_columns', array( 'CBRatingSystem', 'add_rating_column' ) );

			add_action( 'manage_posts_custom_column', array( 'CBRatingSystem', 'rating_column_content' ), 10, 2 );
			add_action( 'manage_pages_custom_column', array( 'CBRatingSystem', 'rating_column_content' ), 10, 2 );

			add_action( 'admin_head', array( 'CBRatingSystem', 'rating_column_style_admin_head' ) );

			/* Meta box */
			add_action( 'load-post.php', array( 'CBRatingSystem', 'post_meta_boxes_setup' ) );
			add_action( 'load-post-new.php', array( 'CBRatingSystem', 'post_meta_boxes_setup' ) );

        } else {
            //fontend
			/* Load JS and CSS at the front-end */
			//add_action('wp_head', array('CBRatingSystemTheme', 'build_custom_theme_css'));
			//add_action('wp_enqueue_scripts', array('CBRatingSystem', 'load_scripts_and_styles'));


            add_filter('query_vars', array('CBRatingSystem', 'email_verify_var'));
            add_action('template_redirect', array('CBRatingSystem', 'email_verify'), 0);

            //shortcodes
            add_shortcode( 'cbratingsystem', array( 'CBRatingSystem', 'cbratingsystem_shorttag' ) );
			add_shortcode( 'cbratingavg', array( 'CBRatingSystem', 'cbratingsystem_avg' ) );
            add_shortcode( 'cbratingtoprateduser', array( 'CBRatingSystem', 'cbratingsystem_top_rated_user' ) );

			/* Add rating form to Page/Post according to form settings */
			add_filter( 'the_content', array( 'CBRatingSystem', 'main_content_with_rating_form' ) );

			//Create a COOKIE for the currect user. For registerred or guest users.
			CBRatingSystem::init_cookie();
		}

	}

	public  static function redirect($url){


		if(headers_sent())
		{
			$string = '<script type="text/javascript">';
			$string .= 'window.location = "' .$url . '"';
			$string .= '</script>';

			echo $string;
		}
		else
		{
			wp_safe_redirect($url);

		}
		exit;
	}

    /**
     * Add params var to wordpress reserver params
     *
     *
     * @param $params
     * @return array
     */

   public static  function email_verify_var($params){

        $params[] = 'cbratingemailverify';
        return $params;
    }

    /**
     * Verify Guest User Email
     */

    public static function email_verify(){
        $email_verify   = get_query_var('cbratingemailverify');
        //var_dump($email_verify);exit(1);
        if($email_verify !=''){
            $rating['comment_status'] = $email_verify;
            CBRatingSystemData::update_rating_hash( $rating );
        }

        	//sql
    }

	/**
	 * Register Widgets
	 *
	 */
	public static function initWidgets() {
		register_widget( 'CBRatingSystemWidget' );
		register_widget( 'CBRatingSystemUserWidget' );
	}

    /**
     * called when plugin is installed
     */
    public static function cbratingsystem_activation() {

        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "activate-plugin_{$plugin}" );


		$previous_version = get_site_option( 'cbratingsystem_plugin_version' );

		if ( $previous_version === false ) {
			//Install the DB tables for this plugin
			CBRatingSystemData::update_table();

			add_site_option( 'cbratingsystem_plugin_version', self::$version );

		} elseif ( self::$version != $previous_version ) {
			//Install the DB tables for this plugin version
			CBRatingSystemData::update_table();
			//error_log('in modify mode');
			//CBRatingSystemData::modify_tables();

			update_site_option( 'cbratingsystem_plugin_version', self::$version );
		} elseif ( self::$version == $previous_version ) {
			CBRatingSystemData::update_table();
		}

	}

    public static function cbratingsystem_deactivation()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );

        # Uncomment the following line to see the function in action
        # exit( var_dump( $_GET ) );
    }

    /**
     * called when plugin uninstalled/delete
     * delete all options if delete all saved from tools page
     */

    public static function cbratingsystem_uninstall() {


        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        check_admin_referer( 'bulk-plugins' );

        // Important: Check if the file is the one
        // that was registered during the uninstall hook.
        if ( __FILE__ != WP_UNINSTALL_PLUGIN )
            return;

        # Uncomment the following line to see the function in action
        # exit( var_dump( $_GET ) );


        $checkuninstall = intval( get_option( 'cbratingsystem_deleteonuninstall' ) );

        if ( $checkuninstall == 1 ) {

            CBRatingSystemData::delete_tables();
            CBRatingSystemData::delete_options();
            CBRatingSystemData::delete_metakeys();
        }

	}

    /**
     * add_common_styles_scripts
     */
    public static function add_common_styles_scripts() {

        wp_enqueue_style( 'cbrp-common-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/cbrating.common.style.css', array(), self::$version );
        //wp_enqueue_style( 'jquery-uniform-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/uniform.aristo.min.css', array(), self::$version );
        wp_enqueue_style( 'jquery-chosen-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/chosen.min.css', array(), self::$version );
        wp_enqueue_style( 'jquery-selectize-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/jquery.selectize.css', array(), self::$version );

		wp_enqueue_script( 'cbrp-common-script', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/cbrating.common.script.js', array( 'jquery' ), self::$version );
		//wp_enqueue_script( 'jquery-uniform', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/jquery.uniform.js', array( 'jquery' ), self::$version );
		wp_enqueue_script( 'jquery-chosen', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/chosen.jquery.js', array( 'jquery' ), self::$version );
		wp_enqueue_script( 'jquery-selectize', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/jquery.selectize.min.js', array( 'jquery' ), self::$version );



	}

    /**
     * @return array
     * localize js file for language
     */

    public static function get_language_strings() {

		$strings = array(
			'string_prefix'  => __( '', 'cbratingsystem' ),
			'string_postfix' => __( 'characters', 'cbratingsystem' ),

		);

		return $strings;
	}

    /**
     * load_scripts_and_styles
     */
    public static function load_scripts_and_styles() {

		wp_enqueue_script( 'jquery-raty-min', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/jquery.raty.min.js', array( 'jquery' ) );
		//wp_enqueue_script('jquery-progressbar', RTP_PLUGIN_DIR_JS . 'external/jquery.ui.progressbar.min.js', array('jquery', 'jquery-ui-widget', 'jquery-ui-core'));
		wp_enqueue_script( 'cbrp-front-js', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/cbratingsystem.front.js', array( 'jquery' ), self::$version, true );
		wp_enqueue_script( 'cbrp-front-review-js', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/cbratingsystem.front.review.js', array( 'jquery' ), self::$version, true );



        wp_enqueue_style( 'dashicons' );

        wp_enqueue_style( 'cbrp-basic-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/basic.style.css', array('dashicons'), self::$version );
		wp_enqueue_style( 'cbrp-basic-review-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/basic.review.style.css', array(), self::$version );

		$theme_key = get_option( 'cbratingsystem_theme_key' );
		if ( is_string( $theme_key ) and ! empty( $theme_key ) ) {
			wp_enqueue_style( 'cbrp-extra-theme-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/extra.theme.style.css', array(), self::$version );
		}
		wp_localize_script(
			'cbrp-front-js',
			'my_string_prefix',
			self::get_language_strings() // inside class
		);
		self::add_common_styles_scripts();
	}

    /**
     * front_end_js_init
     */
    public static function front_end_js_init() {
		//self::add_common_styles_scripts();
	}
    /**
     * add_rating_column
     * in backend table
     */
	public static function add_rating_column( $columns ) {
		return array_merge(
			$columns,
			array( 'rating' => __( 'Average Rating', 'cbratingsystem') )
		);
	}

    /**
     * @param $column
     * @param $post_id
     */
    public static function rating_column_content( $column, $post_id ) {
        if ( 'rating' != $column )
            return;
		$ratings = CBRatingSystemData::get_ratings_summary( array( 'post_id' => array( $post_id ) ), 'form_id', 'ASC', true );

		if ( ! empty( $ratings ) ) {
			$log_average = '<ul>';

			foreach ( $ratings as $rowId => $rows ) {
				if ( $rows->per_post_rating_summary > 0 ) {
					$log_average .= "<li><strong>" . $rows->form_id . ": " . $rows->name . "</strong>";
					$log_average .= "<span style=\"display:block; padding-left:10px;\">" . ( ( $rows->per_post_rating_summary / 100 ) * 5 ) . " out of 5</span>";
					$log_average .= "</li>";
				}
			}

			$log_average .= '</ul>';
		} else {
			$log_average = __( 'No average rating', 'cbratingsystem' );

		}

		echo $log_average;
	}

    /**
     * rating_column_style_admin_head
     * change the table col width of backend
     */
    public static function rating_column_style_admin_head() {

        echo '<style type="text/css">';
		/*echo '.column-rating { width: 13% !important; }';
        echo '.column-qa { width: 15% !important; }';
        echo '.column-comment { width: 23% !important; }';
        echo '.column-criteriarating { width: 10% !important; }';
        echo '.column-userinfo { width: 11% !important; }';*/
        //echo '.column-id { width: 3% !important; }';
        echo '.column-comment { width: 23% !important; }';
		echo '</style>';
	}

    /**
     * @param $atts
     * @return string
     * called for shortcode
     */
    public static function cbratingsystem_shorttag( $atts ) {
		global $post;
		if ( ! is_object( $post ) ) {
			return '';
		}

		$options = shortcode_atts(
			array(
				'form_id'   	=> '',
				'post_id'   	=> $post->ID, //if post id missing then take from loop
				'theme_key' 	=> get_option('cbratingsystem_theme_key'), // set the default theme
				'showreview' 	=> 1
			), $atts
		);


        if( $options['theme_key'] == ''){
            $options['theme_key'] = 'basic';
        }
        if( $options['post_id'] == ''){
            $options['post_id'] = $post->ID;
        }

	    //var_dump($options);

		$output = self::cbratingsystem_shorttag_output( $options );

		return $output;
	}

    /**
     * @param $atts
     * @return string
     */
    public static function cbratingsystem_avg( $atts ) {
		global $post;
		if ( ! is_object( $post ) ) {
			return '';
		}
		//Example: [cbratingsystem rating_form_id=1]
		$options = shortcode_atts(
			array(
				'post_ids'     => '',
				'form_id'      => '',
				'show_title'   => 0, // set the default theme
				'show_form_id' => 0, //if post id missing then take from loop
				'show_text'    => 0,
				'show_star'    => 1,
				'show_single'  => 0,
				'text_label'   => __('Rating: ', 'cbratingsystem')
			), $atts
		);

		if(intval($options['post_ids']) == 0 )
			$options['post_ids'] = $post->ID;


		if(intval($options['form_id']) == 0)
			$options['form_id'] = self::get_default_ratingFormId();

		//$options['form_id'] = apply_filters('rating_form_array', $options['form_id']);

		$option = array( 'post_id' => explode( ",", $options['post_ids'] ), 'form_id' => array(intval($options['form_id'])));

		$output = self::standalone_singlePost_rating_summary( $option, $options['show_title'], $options['show_form_id'], $options['show_text'], $options['show_star'], $options['show_single'], $options['text_label'] );

		return $output;
	}
    // top rated user shortcode
    /**
     * @param $atts
     *
     * @return string
     */
    public static function cbratingsystem_top_rated_user($atts){

        global $post;
        if ( ! is_object( $post ) ) {
            return '';
        }
        //Example: [cbratingsystem rating_form_id=1]
        $options = shortcode_atts(
            array(
                'post_id'       => '', // comma separate post id
                'form_id'       => '', // one form id
                'user_id'       => '', // set comma separate user id
                'post_time'     => '0', //if post time not exit this date takes  //array( 1 => '24 hours', 7 => 'Week', 15 => '15 Days', 30 => 'Month', 0 => 'All' ); one of this value
                'limit'         => 10, // data  limit
                'posttype'      => '0', // one post type
                'post_filter'   => '',// post_id or post_type,
                'order'         => 'DESC',
                'title'         => __('Top Rated Users', 'cbratingsystem')
            ), $atts
        );
        //var_dump($options);
        $limit = $options['limit'];
        if ( $options['post_time'] != 0 ) {

            $date = CBRatingSystemFunctions::get_calculated_date(  $options['post_time'] );
            $options['post_date'] = $date;
        }
        $data = CBRatingSystemData::get_top_rated_post( $options , false, $limit );

        $cbrp_output = '<ul class="cbrp-top-rated-wpanel" style="">';
          if ( ! empty( $data ) ) :

        foreach ( $data as $newdata ) :

            $cbrp_output .=  '<li class="cbrp-top-rated-userlist">';

                    $author_info = get_userdata( (int) $newdata['post_author']);

            $cbrp_output .=  '<div style=""> <a style="" href="'. get_author_posts_url((int) $newdata['post_author']).' ">'. $author_info->display_name.' </a></div>
                    <div style=""> '. $newdata['post_count'].' Posts</div>';

                    $rating = ( ( $newdata['rating']/ 100 ) *  5);
                   ?>
                    <script>
                        jQuery(document).ready(function ($) {
                            $('#cbrp-top-rated<?php echo $newdata['post_author'].'_'.$post->ID; ?>').raty({
                                half    : true,
                                path    : '<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG ; ?>',
                                score   : <?php echo number_format($rating, 2, '.', ''); ?>,
                                readOnly: true,
                                hintList: ['', '', '', '', '']
                            });
                        });
                    </script>
                    <?php $cbrp_output .=  ' <strong>'. number_format($rating, 2, '.', '') . '/5</strong> ';

            $cbrp_output .=  '<div id ="cbrp-top-rated'. $newdata['post_author'].'_'.$post->ID .'" style=""></div>

                </li>';
            endforeach;
         else:
             $cbrp_output .=  ' <li class="cbrp-top-rated-userlist">'.__('No Results found','cbratingsystem').' </li>';
        endif;
        $cbrp_output .=  '</ul>';
        return $cbrp_output;

    }

    /**
     * @param $options
     * @return string
     */
    public static function cbratingsystem_shorttag_output( $options ) {
		//echo 'I am here';
		//var_dump($options);

		if ( ! empty( $options ) ) {
			if ( empty( $options['form_id'] ) || $options['form_id'] == '' ) {
				$defaultFormId = get_option( 'cbratingsystem_defaultratingForm' );
				$form_id       = apply_filters( 'rating_form_array', $defaultFormId );
			} else {
				$form_id = $options['form_id'];
			}


			if ( isset( $form_id ) && is_numeric( $form_id ) ) {
				$ratingFormArray              = CBRatingSystemData::get_ratingForm( $form_id );
				$ratingFormArray['form_id']   = $options['form_id'];
				$ratingFormArray['post_id']   = $options['post_id'];
				$ratingFormArray['theme_key'] = $options['theme_key'];
				$show_review                   = intval($options['showreview']);

				$post_id = $ratingFormArray['post_id'];

				if ( class_exists( 'CBRatingSystemFront' ) && ( $ratingFormArray['is_active'] == 1 ) && ( ( $ratingFormArray['enable_shorttag'] == 1 ) ) ) {

					//get the rating form
					$form = CBRatingSystemFront::add_ratingForm_to_content( $ratingFormArray );

					//cbxdump($ratingFormArray);

					//get the review list
					if ( class_exists( 'CBRatingSystemFrontReview' ) && ( isset($ratingFormArray['review_enabled']) && $ratingFormArray['review_enabled'] == 1 ) && $show_review ) {
						if ( is_singular() ) {
							$review = CBRatingSystemFrontReview::rating_reviews_shorttag( $ratingFormArray, $post_id );

							if ( ! empty( $review ) ) {
								CBRatingSystemTheme::build_custom_theme_css();
								CBRatingSystem::load_scripts_and_styles();

								$form = $form . $review;
							}
						}
					}

					return $form;
				}
			}

		}
	}//end cbratingsystem_shorttag_output

	/*
	 * Auto integration after or before post with disable option
	 * Load the HTML for the current rating form.
	 * @param (array)$options
	 *      - rating_form_id : The form id, which will be loaded.
	 */

	public static function main_content_with_rating_form( $content ) {

		$defaultFormId = get_option( 'cbratingsystem_defaultratingForm' );
		//$form_id = apply_filters('rating_form_array', $defaultFormId);
		$form_id = (int)apply_filters( 'rating_form_array', $defaultFormId );

		$post_id = get_the_ID();

		$form   = '';
		$review = '';



		if ( $form_id > 0 ) {
			$ratingFormArray            = CBRatingSystemData::get_ratingForm( $form_id );
			$ratingFormArray['form_id'] = $form_id;
			$ratingFormArray['post_id'] = $post_id;



			if ( class_exists( 'CBRatingSystemFront' ) and ( $ratingFormArray['is_active'] == 1 ) ) {
				$theme_key = get_option( 'cbratingsystem_theme_key' );

				$ratingFormArray['theme_key'] = $theme_key;
				if ( $ratingFormArray['position'] != 'none' ) {
					$form .= CBRatingSystemFront::add_ratingForm_to_content( $ratingFormArray );
				}

				if ( $ratingFormArray['position'] == 'top' ) {
					CBRatingSystemTheme::build_custom_theme_css();
					CBRatingSystem::load_scripts_and_styles();

					$output = $form . $content;
				} else if ( $ratingFormArray['position'] == 'bottom' ) {
					CBRatingSystemTheme::build_custom_theme_css();
					CBRatingSystem::load_scripts_and_styles();

					$output = $content . $form;
				} else {
					$output = $content;
				}
			} else {
				$output = $content;
			}

			if ( class_exists( 'CBRatingSystemFrontReview' ) and ( $ratingFormArray['review_enabled'] == 1 ) and ( ! ( $ratingFormArray['position'] == 'none' ) ) ) {
				if ( is_single() or is_page() ) {
					$review .= CBRatingSystemFrontReview::rating_reviews( $ratingFormArray, $post_id );

					if ( ! empty( $review ) ) {
						CBRatingSystemTheme::build_custom_theme_css();
						CBRatingSystem::load_scripts_and_styles();

						$output = $output . $review;
					}
				}
			}
		} else {
			$output = $content;
		}

		return $output;
	}//end  main_content_with_rating_form

	/**
	 * Standalone Rating Form
	 *
	 * @param        $form_id
	 * @param        $post_id
	 * @param string $theme_key
	 *
	 * @return string
	 */
	public static function standalonePostingRatingSystemForm( $form_id, $post_id, $theme_key = '', $showreview = true ) {

		//$id = get_the_ID();
		$form   = '';
		$review = '';
		$output = '';

		$theme_key = ( $theme_key == '' ) ? get_option( 'cbratingsystem_theme_key' ) : $theme_key;

		$form_id = apply_filters( 'rating_form_array', $form_id );


		if ( is_int( $form_id ) || is_numeric( $form_id ) ) {
			$ratingFormArray = CBRatingSystemData::get_ratingForm( $form_id );

			$ratingFormArray['form_id'] = $form_id;
			$ratingFormArray['post_id'] = $post_id;

			if ( class_exists( 'CBRatingSystemFront' ) and ( $ratingFormArray['is_active'] == 1 ) ) {
				//$theme_key = get_option('cbratingsystem_theme_key');

				$ratingFormArray['theme_key'] = $theme_key;
                $ratingFormArray = apply_filters('cbratingsystem_change_options' ,$ratingFormArray );


				$form .= CBRatingSystemFront::add_ratingForm_to_content( $ratingFormArray );


				CBRatingSystemTheme::build_custom_theme_css();
				CBRatingSystem::load_scripts_and_styles();

				$output = $form;

			}
            //var_dump($ratingFormArray);
			if ( class_exists( 'CBRatingSystemFrontReview' ) && ( $ratingFormArray['review']['review_enabled'] == 1 ) && $showreview ) {
				//if(is_single() || is_page()){
				if ( is_singular() ) {
					$review .= CBRatingSystemFrontReview::rating_reviews_shorttag( $ratingFormArray, $post_id, 0 );

					if ( ! empty( $review ) ) {
						CBRatingSystemTheme::build_custom_theme_css();
						CBRatingSystem::load_scripts_and_styles();

						$output = $output . $review;
					}
				}
			}
		}

		return $output;

	}

	//end standalonePostingRatingSystemForm
    /**
     * post_meta_boxes_setup
     * add post meta box for rating
     */

    public static function post_meta_boxes_setup() {
		/* Add meta boxes on the 'add_meta_boxes' hook. */
		add_action( 'add_meta_boxes', array( 'CBRatingSystemMetaBox', 'add_post_meta_boxes' ) );

		/* Save post meta on the 'save_post' hook. */
		add_action( 'save_post', array( 'CBRatingSystemMetaBox', 'save_post_meta_data' ), 10, 2 );
	}

	/**
	 * Cookie initialization for the every user
	 */
	public static function init_cookie() {
		//global $current_user;

		if ( is_user_logged_in() ) {
			$cookie_value = 'user-' . get_current_user_id();
		} else {
			$cookie_value = 'guest-' . rand( CB_RATINGSYSTEM_RAND_MIN, CB_RATINGSYSTEM_RAND_MAX );
		}

		if ( ! isset( $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME] ) && empty( $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME] ) ) {
			setcookie( CB_RATINGSYSTEM_COOKIE_NAME, $cookie_value, CB_RATINGSYSTEM_COOKIE_EXPIRATION_14DAYS, SITECOOKIEPATH, COOKIE_DOMAIN );

			//$_COOKIE var accepts immediately the value so it will be retrieved on page first load.
			$_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME] = $cookie_value;

		} elseif ( isset( $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME] ) ) {
			if ( substr( $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME], 0, 5 ) != 'guest' ) {
				setcookie( CB_RATINGSYSTEM_COOKIE_NAME, $cookie_value, CB_RATINGSYSTEM_COOKIE_EXPIRATION_14DAYS, SITECOOKIEPATH, COOKIE_DOMAIN );

				//$_COOKIE var accepts immediately the value so it will be retrieved on page first load.
				$_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME] = $cookie_value;
			}
		}
	}

	/**
	 * Get the ip address of the user
	 *
	 * @return string|void
	 */
	public static function get_ipaddress() {

		if ( empty( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {

			$ip_address = $_SERVER["REMOTE_ADDR"];
		} else {

			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		if ( strpos( $ip_address, ',' ) !== false ) {

			$ip_address = explode( ',', $ip_address );
			$ip_address = $ip_address[0];
		}

		return esc_attr( $ip_address );
	}

	public static function form_default_criteria(){
		$form_criteria = array(
			'custom_criteria'   => array
			(
				'0' => array
				(
					'enabled' => 1,
					'label' => __('Criteria 1', 'cbratingsystem'),
					'stars' => array
					(
						'0' => array('enabled' => 1,'title' => __('Worst','cbratingsystem')),
						'1' => array('enabled' => 1,'title' => __('Bad','cbratingsystem')),
						'2' => array('enabled' => 1,'title' => __('Not Bad','cbratingsystem')),
						'3' => array('enabled' => 1,'title' => __('Good','cbratingsystem')),
						'4' => array('enabled' => 1,'title' => __('Best','cbratingsystem'))
					)

				),
				'1' => array
				(
					'enabled' => 1,
					'label' => __('Criteria 2','cbratingsystem'),
					'stars' => array
					(
						'0' => array('enabled' => 1,'title' => __('Worst','cbratingsystem')),
						'1' => array('enabled' => 1,'title' => __('Bad','cbratingsystem')),
						'2' => array('enabled' => 1,'title' => __('Not Bad','cbratingsystem')),
						'3' => array('enabled' => 1,'title' => __('Good','cbratingsystem')),
						'4' => array('enabled' => 1,'title' => __('Best','cbratingsystem'))
					)

				),
				'2' => array
				(
					'enabled'   => 1,
					'label'     => __('Criteria 3', 'cbratingsystem'),
					'stars' => array
					(
						'0' => array('enabled' => 1,'title' => __('Worst','cbratingsystem')),
						'1' => array('enabled' => 1,'title' => __('Bad','cbratingsystem')),
						'2' => array('enabled' => 1,'title' => __('Not Bad','cbratingsystem')),
						'3' => array('enabled' => 1,'title' => __('Good','cbratingsystem')),
						'4' => array('enabled' => 1,'title' => __('Best','cbratingsystem'))
					)

				)


			)
		);

		return $form_criteria;
	}

	public static function form_default_question(){
		$form_question = array(

			'custom_question'       => array(
				'0' => array(
					'title'         => __('Sample question title 1','cbratingsystem'),
					'required'      => 0,
					'enabled'       => 0,
					'field'         => array(
						'type'          => 'checkbox',
						'checkbox'      => array(
							'seperated' => 0,
							'count'     => 2,
							'0'         => array('text' => __('Yes','cbratingsystem')),
							'1'         => array('text' => __('No','cbratingsystem')),
							'2'         => array('text' => __('Correct','cbratingsystem')),
							'3'         => array('text' => __('Incorrect','cbratingsystem')),
							'4'         => array('text' => __('None','cbratingsystem'))
						),
						'radio'      => array(
							'count'     => 2,
							'0'         => array('text' => __('Yes','cbratingsystem')),
							'1'         => array('text' => __('No','cbratingsystem')),
							'2'         => array('text' => __('Correct','cbratingsystem')),
							'3'         => array('text' => __('Incorrect','cbratingsystem')),
							//'4'         => array('text' => __('None','cbratingsystem'))
						)


					)
				)
			)

	);
		return $form_question;
	}

	/**
	 * @return array|mixed|void
	 */
	public static function form_default_extra_fields(){
		$postTypes          = CBRatingSystem::post_types();
		$userRoles          = CBRatingSystem::user_roles();
		$editorUserRoles    = CBRatingSystem::editor_user_roles();

		// 9 default extra fields  //note review field is now separeated
		$default_extra_fields = array(
			'view_allowed_users'            => array(
				'label'                 => __('Allowed User Roles Who Can View Rating','cbratingsytem'),
				'desc'                  => __( 'Which user group can view rating', 'cbratingsystem' ),
				'type'                  => 'multiselect',
				'user_types'            => true,
				'multiple'              => true,
				'placeholder'           => __('Choose User Group ...','cbratingsystem'),
				'default'               => array('guest','administrator','editor'),
				'required'              => true,
				'options'               => $userRoles,
				'extrafield'            => true,
				'errormsg'              => __('You must give access to at least one User Group who can View Rating','cbratingsystem')
			), //view allowed user

			'comment_view_allowed_users'        => array(
				'label'                 => __('Allowed User Roles Who Can View Rating Review','cbratingsytem'),
				'desc'                  => __( 'Which user group can view rating', 'cbratingsystem' ),
				'type'                  => 'multiselect',
				'user_types'            => true,
				'multiple'              => true,
				'placeholder'           => __('Choose User Group ...','cbratingsystem'),
				'default'               => array('guest','administrator','editor'),
				'required'              => true,
				'options'               => $userRoles,
				'extrafield'            => true,
				'errormsg'              => __('You must give access to at least one User Group who can View Comment','cbratingsystem')
			),//review view allowed user

			'comment_moderation_users'          => array(
				'label'                 => __('Enable Rating Moderation for User Group','cbratingsytem'),
				'desc'                  => __( 'Which user groups comments will be reviewed.', 'cbratingsystem' ),
				'type'                  => 'multiselect',
				'user_types'            => true,
				'multiple'              => true,
				'placeholder'           => __('Choose User Group ...','cbratingsystem'),
				'default'               => array('guest'),
				'required'              => false,
				'options'               => $userRoles,
				'extrafield'            => true
			), // which user group's comment will be moderated

			'comment_required'                  => array(
				'label'         => __('Comment required', 'cbratingsystem'),
				'desc'          => __( 'This option will make the comment box required', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 0,
				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				),
				'extrafield'    => true,

			), //comment box while rating required
			'show_user_avatar_in_review'        => array(
				'label'         => __('Author Avatar in Review', 'cbratingsystem'),
				'desc'          => __( 'Show/hide reviewer\'s profile picture or avatar in review', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 0,
				'tooltip'       => __('Control reviewers\'s avatar','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				),
				'extrafield'    => true,

			), // show user's avater or profile picture in review
			'show_user_link_in_review'          => array(
				'label'         => __('Show Author Link in Review', 'cbratingsystem'),
				'desc'          => __( 'Link user to their author page in each review', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 0,
				'tooltip'       => __('Control reviewers\'s link  ','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				),
				'extrafield'    => true
			),  //show user's link/profile/author link in review
			'show_editor_rating'            => array(
				'label'         => __('Show Editor Rating', 'cbratingsystem'),
				'desc'          => __( 'Show/hide rating editor user group rating', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 0,
				'tooltip'       => __('Which user group is rating editor is selectable ','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				),
				'extrafield'    => true
			),  // show editor rating on frontend yes/no
			'review_enabled'                =>  array(
				'label'         => __('Show/Hide Reviews', 'cbratingsystem'),
				'desc'          => __( 'Control showing reviews on frontend', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				),
				'extrafield'    => true
			), // show hide reviews
			'review_limit'                  => array(
				'label'         => __('Review Limit Per Page', 'cbratingsystem'),
				'desc'          => __( 'How many reviews will be shown per page or in ajax request', 'cbratingsystem' ),
				'type'          => 'text',
				'numeric'       => true,
				'default'       => 10,
				'tooltip'       => __('Review Limit','cbratingsystem'),
				'placeholder'   => __('Review Limit', 'cbratingsystem'),
				'required'      => true,
				'extrafield'    => true,
				'errormsg'      => __('Review Limit is required, must be numeric value','cbratingsystem')
			) //default per page reviews limit


		);



		$default_extra_fields = apply_filters('cbratingsystem_default_extra_fields', $default_extra_fields);
		return $default_extra_fields;
	}

	/**
	 * @return array
	 */
	public static function form_default_fields(){

		$postTypes          = CBRatingSystem::post_types();
		$userRoles          = CBRatingSystem::user_roles();
		$editorUserRoles    = CBRatingSystem::editor_user_roles();

		$form_default = array(

			'id'                => array(
									'type'          => 'hidden',
									'default'       => 0
									) ,
			'name'              => array(
				'label'         => __('Form Title', 'cbratingsystem'),
				'desc'          => __( 'Write form name', 'cardselectbox' ),
				'type'          => 'text',
				'default'       => __('Example Rating Form', 'cbratingsystem'),
				'tooltip'       => __('Form Name','cbratingsystem'),
				'placeholder'   => __('Rating Form Name', 'cbratingsystem'),
				'required'      => true,
				'min'           => 5,
				'max'           => 500,
				'errormsg'      => __('Form title missing or empty, maximum length 500, minimum length 5','cbratingsystem')
			) ,

			'is_active'         => array(
				'label'         => __('Form Status', 'cbratingsystem'),
				'desc'          => __( 'Enable disable the form', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options'       => array(
										'1' => __('Enabled','cbratingsystem'),
										'0' => __('Disabled','cbratingsystem')
									)

			),  // create the form but will be active or inactive

			'post_types'        =>  array(
				'label'                 => __('Post Type Selection','cbratingsytem'),
				'desc'                  => __( 'This form will work for the selected post types', 'cbratingsystem' ),
				'type'                  => 'multiselect',
				'multiple'              => true,
				'post_types'            => true,
				'default'               => array('post', 'page'),
				'tooltip'               => __('Post type selection, works with builtin or custom post types','cbratingsystem'),
				'placeholder'           => __('Choose post type(s)...','cbratingsystem'),

				'required'              => true,
				'options'               => $postTypes,
				'errormsg'              => __('Post type is missing or at least one post type must be selected','cbratingsystem')
			),  // post type supports

			'show_on_single'    => array(
				'label'         => __('Show on single post/page', 'cbratingsystem'),
				'desc'          => __( 'Enable disable for single article', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				)
			),  // show hide on single article pages

			'show_on_home'      => array(
				'label'         => __('Show on Home/Frontpage', 'cbratingsystem'),
				'desc'          => __( 'Enable disable for home/frontpage', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				)
			), //show on home or frontpage
			'show_on_arcv'          => array(
				'label'         => __('Show on Archives', 'cbratingsystem'),
				'desc'          => __( 'Enable disable for archive pages', 'cardselectbox' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				)
			),    //show on any kind of archive
			'position'          =>  array(
				'label'         => __('Auto Integration', 'cbratingsystem'),
				'desc'          => __( 'Enable disable for shortcode', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 'bottom',
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'top'       => __('Top (Before Content)','cbratingsystem'),
					'bottom'    => __('Bottom (After Content)','cbratingsystem'),
					'none'      => __('Disable Auto Integration','cbratingsystem')
				)
			),  //other possible, top and none
			'enable_shorttag'   => array(
				'label'         => __('Enable Shortcode', 'cbratingsystem'),
				'desc'          => __( 'Enable disable for shortcode', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				)
			), //enable disable shortcode
			'logging_method'    => array(
										'label'                 => __('Loggin Method','cbratingsytem'),
										'desc'                  => __( 'Log user rating by ip or cookie or both to protect multiple rating, useful for guest rating', 'cardselectbox' ),
										'type'                  => 'multiselect',
										'multiple'              => 'yes',

										'default'               => array('ip','cookie'),
										'tooltip'               => __('Log user rating for guest using ip and cookie','cbratingsystem'),
										'placeholder'           => __('Choose logging method...','cbratingsystem'),

										'required'              => true,
										'options'               => array(
																		'ip' => __('IP','cbratingsystem'),
																		'cookie' => __('Cookie','cbratingsystem')
																	),
										'errormsg'              => __('At least one logging method should be enabled','cbratingsystem')
										),  // Logging method

			'allowed_users'     =>  array(
				'label'                 => __('Allowed User Roles Who Can Rate','cbratingsytem'),
				'desc'                  => __( 'Which user group can rate article with this Rating Form', 'cbratingsystem' ),
				'type'                  => 'multiselect',
				'user_types'            => true,
				'placeholder'           => __('Choose User Group ...','cbratingsystem'),
				'multiple'              => true,
				'default'               => array('administrator','editor'),
				'required'              => true,
				'options'               => $userRoles,
				'errormsg'              => __('You must select one user group for Rating Editor user','cbratingsystem')
			),

			'editor_group'      => array(
				'label'                 => __('Rating Editor User Group','cbratingsytem'),
				'desc'                  => __( 'Which group of user will be Rating Editor', 'cbratingsystem' ),
				'type'                  => 'multiselect',
				'user_types'            => true,
				'placeholder'           => __('Choose Rating Editor User Group ...','cbratingsystem'),
				'multiple'              => false,
				'default'               => 'administrator',
				'required'              => true,
				'options'               => $editorUserRoles,
				'errormsg'              => __('You must select one user group for Rating Editor user','cbratingsystem')
			), //which group of users will be treated as editor  //administrator'

			'enable_comment'        => array(
				'label'         => __('Enable Comment', 'cbratingsystem'),
				'desc'          => __( 'Enable Comment with Rating', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				)
			),  //enable comment box
			'comment_limit'         => array(
				'label'         => __('Comment Limit Length', 'cbratingsystem'),
				'desc'          => __( 'Comment limit length prevents user from submitting long comment', 'cardselectbox' ),
				'type'          => 'text',
				'default'       => 200,
				'numeric'       => true,
				'tooltip'       => __('Comment text length limit','cbratingsystem'),
				'placeholder'   => __('Comment Length', 'cbratingsystem'),
				'required'      => true,
				'errormsg'      => __('Comment limit can not empty or must be numeric','cbratingsystem')
			) , //limit comment box char limit
			'enable_question'       => array(
				'label'         => __('Enable Question', 'cbratingsystem'),
				'desc'          => __( 'Enable Question with Rating', 'cbratingsystem' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),
				'required'      => true,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				),
				'errormsg'      => __('Enable question field is missing or value must be 0 or 1','cbratingsystem')

			), // Enable Questions
			'show_credit_to_codeboxr'   => array(
				'label'         => __('Show Credit', 'cbratingsystem'),
				'desc'          => __( 'This will show a small link under rating form to codeboxr.com', 'cardselectbox' ),
				'type'          => 'radio',
				'default'       => 1,
				'tooltip'       => __('Enabled by default','cbratingsystem'),

				'required'      => false,
				'options' => array(
					'1' => __('Yes','cbratingsystem'),
					'0' => __('No','cbratingsystem')
				)
			), //spelling mistake in field name
			//'extrafields'               => $default_extra_fields,

		);

		$default_extra_fields = CBRatingSystem::form_default_extra_fields();
		$form_default = array_merge($form_default, $default_extra_fields);

		return $form_default;
	}

    /**
     * @return array
     */
    public static function post_types() {
		$post_type_args      = array(
			'builtin' => array(
				'options' => array(
					'public'   => true,
					'_builtin' => true,
					'show_ui'  => true,
				),
				'label'   => __( 'Built in post types', 'cbratingsystem' ),
			)

		);

	    $post_type_args = apply_filters('cbratingsystem_post_types', $post_type_args);

		$output    = 'objects'; // names or objects, note names is the default
		$operator  = 'and'; // 'and' or 'or'
		$postTypes = array();

		foreach ( $post_type_args as $postArgType => $postArgTypeArr ) {
			$types = get_post_types( $postArgTypeArr['options'], $output, $operator );

			if ( ! empty( $types ) ) {
				foreach ( $types as $type ) {
					$postTypes[$postArgType]['label']              = $postArgTypeArr['label'];
					$postTypes[$postArgType]['types'][$type->name] = $type->labels->name;
				}
			}
		}

		return $postTypes;

	}

	/**
	 * get the user roles for voting purpose
	 *
	 * @param string $useCase
	 *
	 * @return array
	 */
	public static function user_roles( $useCase = 'admin' ) {
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
	/**
	 * get the editor user roles
	 *
	 * @param string $useCase
	 *
	 * @return array
	 */
	public static function editor_user_roles( $useCase = 'admin' ) {
		global $wp_roles;

		$userRoles = array();

		switch ( $useCase ) {
			default:
			case 'admin':
				$userRoles = array(
					'Registered' => get_editable_roles(),
				);
				break;

			case 'front':
				foreach ( get_editable_roles() as $role => $roleInfo ) {
					$userRoles[$role] = $roleInfo['name'];
				}
				break;
		}



		return $userRoles;

	}

    /**
     * @param        $roles
     * @param string $userId
     *
     * @return bool
     */
    public static function current_user_can_use_ratingsystem( $roles, $userId = '' ) {
		//$allUserRoles = self::user_roles('front');

		if ( ! empty( $userId ) ) {
			$user_id = get_userdata( $userId ); //echo $user_id;
		} else {
			$user_id = get_current_user_id(); //echo $user_id;
		}


		if ( is_user_logged_in() ) {
			$user = new WP_User( $user_id );

			if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {

				$user->roles[] = 'guest';

				$intersectedRoles = array_intersect( $roles, $user->roles );

			}
		} else {
			if ( in_array( 'guest', $roles ) ) {
				$intersectedRoles = array( 'guest' );
				//var_dump($intersectedRoles);
			}
			//$intersectedRoles = array('guest');
		}

		if ( ! empty( $intersectedRoles ) ) {
			return true;
		}

		return false;
	}

    /**
     * @param        $roles
     * @param string $userId
     *
     * @return bool
     */
    public static function current_user_can_view_ratingsystem( $roles, $userId = '' ) {
		//$allUserRoles = self::user_roles('front');

		if ( ! empty( $userId ) ) {
			$user_id = get_userdata( $userId ); //echo $user_id;
		} else {
			$user_id = get_current_user_id(); //echo $user_id;
		}


		if ( is_user_logged_in() ) {
			$user = new WP_User( $user_id );

			if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {

				$user->roles[] = 'guest';

				$intersectedRoles = array_intersect( $roles, $user->roles );

			}
		} else {
			if ( in_array( 'guest', $roles ) ) {
				$intersectedRoles = array( 'guest' );
				//var_dump($intersectedRoles);
			}
			//$intersectedRoles = array('guest');
		}

		if ( ! empty( $intersectedRoles ) ) {
			return true;
		}

		return false;
	}

    /**
     * Can this form be marked as default form
     *
     * @param $formId
     * @return bool
     */
    public static function can_automatically_make_deafult_form( $formId ) {
		global $wpdb;
		$table_name = CBRatingSystemData::get_ratingForm_settings_table_name();

		$sql        =  "SELECT COUNT(id) AS count FROM $table_name";

		$return     = false;

		$count = $wpdb->get_var( $sql );

		if ( ! ( $count > 1 ) ) {
			$return = true;
		}
		if ( ! empty( $formId ) ) {
			if ( $formId == get_option( 'cbratingsystem_defaultratingForm' ) ) {
				$return = false;
			}
		}

		return $return;
	}

    /**
     * @param $arr
     * @return stdClass
     */
    public static function array_to_object( $arr ) {
		$post = new stdClass;
		foreach ( $arr as $key => $val ) {
			if ( is_array( $val ) ) {
				//$post->$key = self::array_to_object($val);
				$post->$key = $val;
			} else {
				$post->$key = trim( strip_tags( $arr[$key] ) );
			}
		}

		return $post;
	}

	/**
	 * Return the default form id with applying filter if form id set different from post id
	 *
	 * @return mixed|void
	 */
	public  static function get_default_ratingFormId() {
		$defaultFormId = get_option( 'cbratingsystem_defaultratingForm' );
		$form_id       = apply_filters( 'rating_form_array', $defaultFormId );

		return $form_id;
	}

	/**
	 * Show avg rating for multiple posts for same form id,
	 * calls from shortcode or direct function call or from widget
	 *
	 * @param     $option
	 * @param int $show_title
	 * @param int $show_form_id
	 * @param int $show_text
	 * @param int $show_star
	 * @param int $show_single
	 * @param int $text_label
	 *
	 * @return string
	 */
	function standalone_singlePost_rating_summary( $option, $show_title = 0, $show_form_id = 0, $show_text = 0, $show_star = 1, $show_single = 0 , $text_label = '') {
//		echo '<pre>';
//		print_r($option);
//		echo '</pre>';

		CBRatingSystem::load_scripts_and_styles();

		if($option == NULL) return '';

		$text_label    = ($text_label == '')? __('Rating: ','cbratingsystem') : $text_label;

		$post_ids      = ( (empty( $option['post_id']  ) || !is_array($option['post_id']) || (sizeof($option['post_id']  > 0)  ) ) ? array(get_the_ID()) : $option['post_id'] );
		$form_ids      = ( (empty( $option['form_id']  ) || !is_array($option['form_id']) || (sizeof($option['form_id']  > 0)  ) )? array(self::get_default_ratingFormId()) : $option['form_id'] );

		$rating_smmary = array( 'post' => array() );
		$show = '';
		if ( $show_single == 1 ) {
			//show only for first item
			$option['post_id'] = array($post_ids[0]);
			$option['form_id'] = array($form_ids[0]);

			$average_rating = CBRatingSystemData::get_ratings_summary( $option );




			if(!sizeof($average_rating)){
				$average_rating['form_id'] 					= $form_ids[0];
				$average_rating['post_id'] 					= $post_ids[0];
				$average_rating['post_title'] 				= get_the_title($post_ids[0]);
				$average_rating['per_post_rating_summary']	= 0;
				$average_rating['found']                    = 0;

			}
			else{
				$average_rating  							= $average_rating[0]; //no rating found for this post and form id
				$average_rating['found']                    = 1;
			}

			$show .= '<div class="cbratingavgratelist">';
			if ( $show_title == 1 ) {
				$show .= '<p>'.__('Post: ','cbratingsystem'). $average_rating['post_title'] . '</p>';
			}
			if ( $show_form_id == 1 ) {
				$show .= '<p>'.__('Form: ','cbratingsystem') . $average_rating['form_id'] . '</p>';
			}

			if ( $show_text == 1 ) {
				$show .= '<p>'.$text_label. number_format( ( ( $average_rating['per_post_rating_summary'] / 100 ) * 5 ), 2 ) . '/5</p>';
			}

			$clip_back        = 120;
			$star_clip_amount = number_format( ( ( $average_rating['per_post_rating_summary'] / 100 ) * 120 ), 2 );

			if ( $show_star == 1 ) {
				?>
				<script>
					jQuery(document).ready(function ($) {
						$('.cbrp-alone-rated<?php echo $average_rating['post_id']; ?>').raty({
							half    : true,
							path    : '<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>',
							score   : <?php echo ( ($average_rating['per_post_rating_summary']/100)*5); ?>,
							readOnly: true,
							hintList: ['', '', '', '', '']
						});
					});
				</script>
				<?php
				$show .= '  <div class="cbrp-alone-rated cbrp-alone-rated' . $average_rating['post_id'] . '" id="cbrp-alone-rated' . $average_rating['post_id'] . '" style="margin: 0;"></div>';
			}
			$show .= '</div>';

			// echo   $show;

		} else {
			//var_dump('I am here');
			//$show = '';
			if ( ! empty( $post_ids ) && ! empty( $form_ids ) ) {
				//$position = 30;
				foreach ( $post_ids as $post_id ) {


					//$average_ratings = CBRatingSystemData::get_ratings($form_id,$post_id);
					//$option=array('post_id'=>$post_id,'form_id'=>$form_id);
					//unset( $whrOpt );
					$option = array();

					$option['form_id']   	= $form_ids;
					$option['post_id'] 		= array($post_id);

//					echo '<pre>';
//					print_r($option);
//					echo '</pre>';

					$average_rating = CBRatingSystemData::get_ratings_summary( $option );

					if(!sizeof($average_rating)){
						$average_rating['form_id'] 					= $form_ids[0];
						$average_rating['post_id'] 					= $post_ids[0];
						$average_rating['post_title'] 				= get_the_title($post_ids[0]);
						$average_rating['per_post_rating_summary']	= 0;
						$average_rating['found']                    = 0;

					}
					else{
						$average_rating  							= $average_rating[0];
						$average_rating['found']  					= 1;
					}


					$show .= '<div class="cbratingavgratelist" style="position:relative;">';
					if ( $show_title == 1 ) {
						$show .= '<p>'.__('Post: ','cbratingsystem') . $average_rating['post_title'] . '</p>';
					}
					if ( $show_form_id == 1 ) {
						$show .= '<p>'.__('Form: ','cbratingsystem') . $average_rating['form_id'] . '<p>';
					}

					if ( $show_text == 1 ) {
						$show .= '<p>'.$text_label.'' . number_format( ( ( $average_rating['per_post_rating_summary'] / 100 ) * 5 ), 2 ) . '/5<p>';
					}

					$clip_back        = 120;
					$star_clip_amount = number_format( ( ( $average_rating['per_post_rating_summary'] / 100 ) * 120 ), 2 );
					if ( $show_star == 1 ) {
						?>
						<script>
							jQuery(document).ready(function ($) {
								$('.cbrp-alone-rated<?php echo $post_id; ?>').raty({
									half    : true,
									path    : '<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>',
									score   : <?php echo (($average_rating['per_post_rating_summary']/100)*5); ?>,
									readOnly: true,
									hintList: ['', '', '', '', '']
								});
							});
						</script>
						<?php
						$show .= '  <div class="cbrp-alone-rated cbrp-alone-rated' . $average_rating['post_id'] . '" id="cbrp-alone-rated' . $post_id . '" style="margin: 0;"></div>';
					}
					$show .= '</div>';
					//$position += 30;
				}

				//echo $show;
			}

		}

		return $show;
	}

	/**
	 * @return array
	 */
	public static function user_role_label() {
		return array(
			'guest'      => 'Guest Users',
			'registered' => 'Registered Users',
			'editor'     => 'Editor Users',
		);
	}

	function cbrating_if_col_exists($table_name, $column_name){
		global $wpdb;
		foreach ($wpdb->get_col("DESC $table_name", 0) as $column ) {
			if ($column == $column_name) {
				return true;
			}
		}

		return false;
	}


}//end class cbratingsystem


/**
 * This function  add standalone rating form or custom rating form call.
 * This will add both rating form and review list both based on form setting and permission
 *
 * Also it'll not be cached just like as the comment system of wp
 *
 * @param $form_id  rating form id, if empty then will use the default one
 * @param $post_id  post id, if empty then will use the post id from the loop
 * @param $theme_key if empty then will use from default setting
 * @param $showreview   shows review if true
 *
 * @return string
 */
function standalonePostingRatingSystem( $form_id = '', $post_id = '', $theme_key = '', $showreview = true ) {

	global $post;

	if ( $form_id == '' ) {
		$form_id = get_option( 'cbratingsystem_defaultratingForm' );
		$form_id = apply_filters( 'rating_form_array', $form_id );

	}

	$form_id = intval($form_id);

	//need to add translation
	if($form_id == 0) return __('Form id not found','cbratingsystem');

	// get the id of the current post via param or db
	if ( $post_id == '' ) {
		$post_id = $post->ID;
	}


	//var_dump($post);
	//if post id doesn't exists then we can not render the form.
	//need to add translation
	if(!is_int($post_id)) return __('Post id not found','cbratingsystem');



	return CBRatingSystem::standalonePostingRatingSystemForm( $form_id, $post_id, $theme_key, $showreview );
}//end standalonePostingRatingSystem

/**
 * Show avg rating for multiple posts for same form id,
 * calls from shortcode or direct function call or from widget
 *
 * @param     $option
 * @param int $show_title
 * @param int $show_form_id
 * @param int $show_text
 * @param int $show_star
 * @param int $show_single
 * @param int $text_label
 *
 * @return string
 */

function standaloneSinglePostRatingSummary($option, $show_title = 0, $show_form_id = 0, $show_text = 0, $show_star = 1, $show_single = 1 , $text_label = ''){
	return CBRatingSystem::standalone_singlePost_rating_summary( $option, $show_title , $show_form_id, $show_text , $show_star , $show_single , $text_label );
}

if(!function_exists('cbxdump')){
	function cbxdump($arr){
		if(is_array($arr)):
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
		else:
			var_dump($arr);
		endif;

	}
}