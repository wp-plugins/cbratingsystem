<?php
/*
  Plugin Name: CodeBoxr Rating System
  Plugin URI: http://codeboxr.com/product/multi-criteria-flexible-rating-system-for-wordpress
  Description: Rating system for Posts and Pages from CodeBoxr.
  Version: 3.2.24
  Author: Codeboxr
  Author URI: mailto:info@codeboxr.com
 */

//define the constants
define( 'CB_RATINGSYSTEM_PLUGIN_VERSION', '3.2.24' ); //need for checking verson
define( 'CB_RATINGSYSTEM_FILE', __FILE__ );
define( 'CB_RATINGSYSTEM_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
define( 'CB_RATINGSYSTEM_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( CB_RATINGSYSTEM_FILE ) ) );
define( 'CB_RATINGSYSTEM_PLUGIN_NAME', 'Rating System' );
define( 'CB_RATINGSYSTEM_PLUGIN_SLUG_NAME', 'cbratingsystem' );
define( 'CB_RATINGSYSTEM_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'CB_RATINGSYSTEM_PLUGIN_DIR_IMG', plugin_dir_url( __FILE__ ) . 'images/' );
define( 'CB_RATINGSYSTEM_RAND_MIN', 0 );
define( 'CB_RATINGSYSTEM_RAND_MAX', 999999 );
define( 'CB_RATINGSYSTEM_COOKIE_EXPIRATION_14DAYS', time() + 1209600 ); //Expiration of 14 days.
define( 'CB_RATINGSYSTEM_COOKIE_EXPIRATION_7DAYS', time() + 604800 ); //Expiration of 7 days.
define( 'CB_RATINGSYSTEM_COOKIE_NAME', 'cbrating-cookie-session' );
//var_dump(CB_RATINGSYSTEM_PATH);
//all datebase in data.php
require_once( CB_RATINGSYSTEM_PATH . '/data.php' );
//all widget in this page
require_once( CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemWidget.php' );

//bootstrap the rating plugin
add_action( 'init', array( 'CBRatingSystem', 'init' ) );

//actions on install and on uninstall/delete
//while activating a plugin
register_activation_hook( __FILE__, array( 'CBRatingSystem', 'install_plugin' ) );
//while deleting a plugin
register_uninstall_hook( __FILE__, array( 'CBRatingSystem', 'uninstall_plugin' ) );

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

			add_action( 'wp_ajax_nopriv_cbRatingAjaxFunction', array( 'CBRatingSystemFront', 'cbRatingAjaxFunction' ) );
			add_action( 'wp_ajax_cbRatingAjaxFunction', array( 'CBRatingSystemFront', 'cbRatingAjaxFunction' ) );

            // added later for comment modaretion
            add_action( 'wp_ajax_nopriv_cbCommentAjaxFunction', array( 'CBRatingSystemAdminReport', 'cbCommentAjaxFunction' ) );
            add_action( 'wp_ajax_cbCommentAjaxFunction', array( 'CBRatingSystemAdminReport', 'cbCommentAjaxFunction' ) );

            // to edit comment
            add_action( 'wp_ajax_nopriv_cbCommentEditAjaxFunction', array( 'CBRatingSystemAdminReport', 'cbCommentEditAjaxFunction' ) );
            add_action( 'wp_ajax_cbCommentEditAjaxFunction', array( 'CBRatingSystemAdminReport', 'cbCommentEditAjaxFunction' ) );


            add_action( 'wp_ajax_nopriv_cbReviewAjaxFunction', array( 'CBRatingSystemFrontReiview', 'cbReviewAjaxFunction' ) );
			add_action( 'wp_ajax_cbReviewAjaxFunction', array( 'CBRatingSystemFrontReiview', 'cbReviewAjaxFunction' ) );

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
			/* Load JS and CSS at the front-end */
			//add_action('wp_head', array('CBRatingSystemTheme', 'build_custom_theme_css'));
			//add_action('wp_enqueue_scripts', array('CBRatingSystem', 'load_scripts_and_styles'));

			/* Add ShortTag functionanlity */
            add_filter('query_vars', array('CBRatingSystem', 'email_verify_var'));
            add_action('template_redirect', array('CBRatingSystem', 'email_verify'), 0);

            add_shortcode( 'cbratingsystem', array( 'CBRatingSystem', 'cbratingsystem_shorttag' ) );
			add_shortcode( 'cbratingavg', array( 'CBRatingSystem', 'cbratingsystem_avg' ) );
			/* Add rating form to Page/Post according to form settings */
			add_filter( 'the_content', array( 'CBRatingSystem', 'main_content_with_rating_form' ) );

			//Create a COOKIE for the currect user. For registerred or guest users.
			CBRatingSystem::init_cookie();
		}

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
     * Verify Email
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
		//register_widget( 'CBRatingSystemStandaloneWidget' );
	}

    /**
     * called when plugin is installed
     */
    public static function install_plugin() {
		//First delete all the existing tables (like previously installed) and remove all existing variable options.
		//self::uninstall_plugin();

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

    /**
     * called when plugin uninstalled
     * delete all options if delete all saved from tools page
     */

    public static function uninstall_plugin() {


        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        check_admin_referer( 'bulk-plugins' );

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

		wp_enqueue_script( 'cbrp-common-script', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/cbrating.common.script.js', array( 'jquery' ), self::$version );
		wp_enqueue_script( 'jquery-uniform', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/jquery.uniform.js', array( 'jquery' ), self::$version );
		wp_enqueue_script( 'jquery-chosen', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/chosen.jquery.js', array( 'jquery' ), self::$version );
		wp_enqueue_script( 'jquery-selectize', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/jquery.selectize.min.js', array( 'jquery' ), self::$version );

		wp_enqueue_style( 'cbrp-common-style', CB_RATINGSYSTEM_PLUGIN_DIR . 'css/cbrating.common.style.css', array(), self::$version );
		wp_enqueue_style( 'jquery-uniform-style', CB_RATINGSYSTEM_PLUGIN_DIR . 'css/uniform.aristo.min.css', array(), self::$version );
		wp_enqueue_style( 'jquery-chosen-style', CB_RATINGSYSTEM_PLUGIN_DIR . 'css/chosen.min.css', array(), self::$version );
		wp_enqueue_style( 'jquery-selectize-style', CB_RATINGSYSTEM_PLUGIN_DIR . 'css/jquery.selectize.css', array(), self::$version );

	}

    /**
     * @return array
     * localize js file for language
     */

    public static function get_language_strings() {

		$strings = array(
			'string_prefix'  => __( 'You Have', 'cbratingsystem' ),
			'string_postfix' => __( 'characters', 'cbratingsystem' ),

		);

		return $strings;
	}

    /**
     * load_scripts_and_styles
     */
    public static function load_scripts_and_styles() {

		wp_enqueue_script( 'jquery-raty-min', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/jquery.raty.min.js', array( 'jquery' ) );
		//wp_enqueue_script('jquery-progressbar', RTP_PLUGIN_DIR_JS . 'external/jquery.ui.progressbar.min.js', array('jquery', 'jquery-ui-widget', 'jquery-ui-core'));
		wp_enqueue_script( 'cbrp-front-js', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/cbratingsystem.front.js', array( 'jquery' ), self::$version, true );
		wp_enqueue_script( 'cbrp-front-review-js', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/cbratingsystem.front.review.js', array( 'jquery' ), self::$version, true );
		wp_enqueue_script( 'cbrp-ajax-request', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/cbratingsystem.front.ajax.js', array( 'jquery' ), self::$version, true );
		wp_enqueue_style( 'cbrp-basic-style', CB_RATINGSYSTEM_PLUGIN_DIR . 'css/basic.style.css', array(), self::$version );
		wp_enqueue_style( 'cbrp-basic-review-style', CB_RATINGSYSTEM_PLUGIN_DIR . 'css/basic.review.style.css', array(), self::$version );

		$theme_key = get_option( 'cbratingsystem_theme_key' );
		if ( is_string( $theme_key ) and ! empty( $theme_key ) ) {
			wp_enqueue_style( 'cbrp-extra-theme-style', CB_RATINGSYSTEM_PLUGIN_DIR . 'css/extra.theme.style.css', array(), self::$version );
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
			array( 'rating' => __( 'Average Rating', cbratingsystem ) )
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
			$log_average = __( 'No avegare rating', 'cbratingsystem' );

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
				'theme_key' 	=> get_option( 'cbratingsystem_theme_key' ), // set the default theme
				'showreview' 	=> 1
			), $atts
		);


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
				$showreview                   = intval($options['showreview']);

				$post_id = $ratingFormArray['post_id'];
				//var_dump($post_id);
				if ( class_exists( 'CBRatingSystemFront' ) && ( $ratingFormArray['is_active'] == 1 ) && ( ( $ratingFormArray['enable_shorttag'] == 1 ) ) ) {

					//get the rating form
					$form = CBRatingSystemFront::add_ratingForm_to_content( $ratingFormArray );

					//get the review list
					if ( class_exists( 'CBRatingSystemFrontReiview' ) && ( $ratingFormArray['review']['review_enabled'] == 1 ) && $showreview ) {
						if ( is_singular() ) {
							$review .= CBRatingSystemFrontReiview::rating_reviews_shorttag( $ratingFormArray, $post_id );

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
		$form_id = apply_filters( 'rating_form_array', $defaultFormId );
		$post_id = get_the_ID();

		$form   = '';
		$review = '';

		//echo '<pre>'; print_r( $form_id ); echo '</pre>'; //die();

		if ( is_int( $form_id ) || is_numeric( $form_id ) ) {
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

			if ( class_exists( 'CBRatingSystemFrontReiview' ) and ( $ratingFormArray['review']['review_enabled'] == 1 ) and ( ! ( $ratingFormArray['position'] == 'none' ) ) ) {
				if ( is_single() or is_page() ) {
					$review .= CBRatingSystemFrontReiview::rating_reviews( $ratingFormArray, $post_id );

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

		//echo '<pre>'; print_r( $form_id ); echo '</pre>'; //die();
		if ( is_int( $form_id ) || is_numeric( $form_id ) ) {
			$ratingFormArray = CBRatingSystemData::get_ratingForm( $form_id );

			$ratingFormArray['form_id'] = $form_id;
			$ratingFormArray['post_id'] = $post_id;

			if ( class_exists( 'CBRatingSystemFront' ) and ( $ratingFormArray['is_active'] == 1 ) ) {
				//$theme_key = get_option('cbratingsystem_theme_key');

				$ratingFormArray['theme_key'] = $theme_key;


				$form .= CBRatingSystemFront::add_ratingForm_to_content( $ratingFormArray );


				CBRatingSystemTheme::build_custom_theme_css();
				CBRatingSystem::load_scripts_and_styles();

				$output = $form;

			}

			if ( class_exists( 'CBRatingSystemFrontReiview' ) && ( $ratingFormArray['review']['review_enabled'] == 1 ) && $showreview ) {
				//if(is_single() || is_page()){
				if ( is_singular() ) {
					$review .= CBRatingSystemFrontReiview::rating_reviews_shorttag( $ratingFormArray, $post_id, 0 );

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

    /**
     * @return array
     */
    public static function post_types() {
		$args      = array(
			'builtin' => array(
				'options' => array(
					'public'   => true,
					'_builtin' => true,
					'show_ui'  => true,
				),
				'label'   => __( 'Built in post types', 'cbratingsystem' ),
			),
			'custom'  => array(
				'options' => array(
					'public'   => true,
					'_builtin' => false,
				),
				'label'   => __( 'Custom post types', 'cbratingsystem' ),
			),
		);
		$output    = 'objects'; // names or objects, note names is the default
		$operator  = 'and'; // 'and' or 'or'
		$postTypes = array();

		foreach ( $args as $postArgType => $postArgTypeArr ) {
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
		//echo '<pre>'; print_r($userRoles); echo '</pre>'; die();
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

		//echo '<pre>'; print_r($userRoles); echo '</pre>'; die();

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
				// echo "<pre>"; print_r($user->roles); echo "</pre>";
				// echo "<pre>"; print_r(explode('',$roles)); echo "</pre>";
				$intersectedRoles = array_intersect( $roles, $user->roles );
				//var_dump($intersectedRoles); //exit;
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
				// echo "<pre>"; print_r($user->roles); echo "</pre>";
				// echo "<pre>"; print_r(explode('',$roles)); echo "</pre>";
				$intersectedRoles = array_intersect( $roles, $user->roles );
				//var_dump($intersectedRoles); //exit;
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
     * @param $formId
     * @return bool
     */
    public function can_automatically_make_deafult_form( $formId ) {
		global $wpdb;
		$table_name = CBRatingSystemData::get_ratingForm_settings_table_name();
		$sql        =  $wpdb->prepare( "SELECT COUNT(id) AS count FROM $table_name", null );
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
	function get_default_ratingFormId() {
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
			//var_dump(sizeof($average_rating));

//			echo '<pre>';
//			print_r($average_rating);
//			echo '</pre>';

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
						$('#cbrp-alone-rated<?php echo $average_rating['post_id']; ?>').raty({
							half    : true,
							path    : '<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>',
							score   : <?php echo ( ($average_rating['per_post_rating_summary']/100)*5); ?>,
							readOnly: true,
							hintList: ['', '', '', '', '']
						});
					});
				</script>
				<?php
				$show .= '  <div id="cbrp-alone-rated' . $average_rating['post_id'] . '" style="margin: 0;"></div>';
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

					//var_dump($average_rating);
					//var_dump($whrOpt);
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
								$('#cbrp-alone-rated<?php echo $post_id; ?>').raty({
									half    : true,
									path    : '<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>',
									score   : <?php echo (($average_rating['per_post_rating_summary']/100)*5); ?>,
									readOnly: true,
									hintList: ['', '', '', '', '']
								});
							});
						</script>
						<?php
						$show .= '  <div id="cbrp-alone-rated' . $post_id . '" style="margin: 0;"></div>';
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
	//var_dump($form_id);
	//var_dump($post_id);


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