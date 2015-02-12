<?php

/**
 * Class CBRatingSystemAdmin
 */
class CBRatingSystemAdmin {

    /**
     * initialize when plugin installed
     */
    public static function init() {

        add_action( 'admin_menu', array( 'CBRatingSystemAdmin', 'admin_rating_mainmenu' ) );
        add_action( 'admin_enqueue_scripts', array( 'CBRatingSystemAdmin', 'admin_load_scripts_and_styles' ) );


		add_filter( "plugin_action_links_" . CB_RATINGSYSTEM_PLUGIN_BASE_NAME, array( 'CBRatingSystemAdmin', 'admin_cbratingsystem_settings_link' ) );

		//widget for linkedinresume
		// include_once 'class.CBRatingSystemWidget.php';
		// add_action( 'widgets_init', array($this,'cb_rating_load_widget' ));
	}

    /**
     * add menu pages
     */
    public static function admin_rating_mainmenu() {



		$ratingactiontop     = add_menu_page( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main', CB_RATINGSYSTEM_PLUGIN_NAME, 'administrator', 'rating', array( 'CBRatingSystemAdminDashboard', 'display_admin_dashboard' ), plugins_url( 'images/admin_icon/rating-home-16.png', __FILE__ ) );
		$ratingactionform    = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Rating Forms', 'cbratingsystem' ), 'administrator', 'ratingform', array( 'CBRatingSystemAdmin', 'admin_ratingForm_listing_page' ) );
		$ratingactionformnew = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Add New Form', 'cbratingsystem' ), 'administrator', 'ratingformedit', array( 'CBRatingSystemAdmin', 'admin_ratingForm_listing_page_add' ) );
		$ratingactionuserlog = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'User Rating Logs', 'cbratingsystem' ), 'administrator', 'rating_reports', array( 'CBRatingSystemAdmin', 'admin_rating_reports_page' ) );
		$ratingactionavglog  = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Rating Average', 'cbratingsystem' ), 'administrator', 'rating_avg_reports', array( 'CBRatingSystemAdmin', 'admin_rating_average_reports_page' ) );
		$ratingactiontheme   = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Themes', 'cbratingsystem' ), 'administrator', 'ratingform_theme', array( 'CBRatingSystemTheme', 'admin_ratingForm_theme_settings' ) );
		$ratingtool          = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Tools', 'cbratingsystem' ), 'administrator', 'rating_tool', array( 'CBRatingSystemTool', 'admin_ratingForm_tool_settings' ) );

		add_action( "load-{$ratingactiontop}", array( 'CBRatingSystemAdmin', 'admin_dashboard_page_css_js' ) );
		add_action( "load-{$ratingactiontheme}", array( 'CBRatingSystemAdmin', 'admin_theme_page_css_js' ) );

		//add_action( "load-{$ratingactionformnew}", array( 'CBRatingSystemAdmin', 'admin_load_scripts_and_styles' ) );
	}

    /**
     * @param $links
     *
     * @return mixed
     * return rating setting link in backed dashboard
     */
    public static function admin_cbratingsystem_settings_link( $links ) {

		$settings_link = '<a href="admin.php?page=rating">'.__('Settings','cbratingsystem').'</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

    /**
     * admin_load_scripts_and_styles
     */
    public static function admin_load_scripts_and_styles($hook) {

        //var_dump($hook);

        $plugin_page_slugs = array(
            'toplevel_page_rating',
            'rating-system_page_ratingform',
            'rating-system_page_ratingformedit',
            'rating-system_page_rating_reports',
            'rating-system_page_rating_avg_reports',
            'rating-system_page_ratingform_theme',
            'rating-system_page_rating_tool'
        );

        if(!in_array($hook, $plugin_page_slugs))  return;
        //toplevel_page_rating
        //rating-system_page_ratingform
        //rating-system_page_ratingformedit
        //rating-system_page_rating_reports
        //rating-system_page_rating_avg_reports
        //rating-system_page_ratingform_theme
        //rating-system_page_rating_tool

		wp_enqueue_style( 'cbratingsystem_admin_style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/cbratingsystem.admin.css', array(), CBRatingSystem::$version );

        wp_enqueue_script('jquery');

        wp_enqueue_script( 'cbratingsystem_admin_inline_edit_script', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/jquery.inline-edit.js', array( 'jquery' ), CBRatingSystem::$version );
		wp_enqueue_script( 'jquery-tablesorter-min', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/jquery.tablesorter.min.js', array( 'jquery' ), CBRatingSystem::$version );
		wp_enqueue_script( 'jquery-tablesorter-pager', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/jquery.tablesorter.pager.js', array( 'jquery' ), CBRatingSystem::$version );

		//wp_enqueue_script( 'jquery-ui-core' );
		//wp_enqueue_script( 'jquery-ui-tabs' );
        wp_localize_script(
            'cbratingsystem_admin_edit_script',
            'commentAjax',
            array('ajaxUrl' =>admin_url('admin-ajax.php')) // inside class
        );
        wp_enqueue_script( 'cbratingsystem_admin_edit_script', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/cbratingsystem.admin.js', array( 'jquery' ), CBRatingSystem::$version );

		//CBRatingSystemAdmin::admin_dashboard_page_css_js();
		//CBRatingSystemAdmin::admin_theme_page_css_js();
		CBRatingSystem::add_common_styles_scripts();

	}

    /**
     * admin_dashboard_page_css_js
     */
    public static function admin_dashboard_page_css_js() {
		//wp_register_style( 'cbratingsystem_admin_dashboard_style', plugins_url( '/css/admin.dashboard.css', __FILE__ ), array(), CBRatingSystem::$version );
		//wp_register_script( 'cbratingsystem_admin_dashboard_script', plugins_url( '/js/cbratingsystem.admin.dashboard.js', __FILE__ ), array( 'jquery' ), CBRatingSystem::$version );

		//wp_enqueue_style( 'cbratingsystem_admin_dashboard_style' );
		//wp_enqueue_script( 'cbratingsystem_admin_dashboard_script' );

		CBRatingSystem::add_common_styles_scripts();
	}

	public static function admin_theme_page_css_js() {

        //var_dump($hook);
        // this is available in premium version-codeboxr
		//wp_register_script( 'cbratingsystem_admin_theme_script', plugins_url( '/js/cbratingsystem.admin.theme.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), CBRatingSystem::$version );

		wp_enqueue_style( 'wp-color-picker' );
        // this is available in premium version-codeboxr
		//wp_enqueue_script( 'cbratingsystem_admin_theme_script' );

		CBRatingSystem::add_common_styles_scripts();
	}

	/**
	 * Shows a list of rating forms
	 */
	public  static  function admin_ratingForm_listing_page(){

		//listing rating forms
		//$output = self::admin_ratingForm_listing_html();
		?>
		<div class="wrap">

			<div id="icon-options-general" class="icon32"></div>
			<h2><?php _e( 'Codeboxr Rating System Form Listing', 'cbratingsystem' ) ; ?></h2>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable">

							<div class="postbox">

								<h3><span><?php _e( 'Edit/Add', 'cbratingsystem' ) ; ?></span></h3>
								<div class="inside">
									<?php
									self::admin_ratingForm_listing_html();
									?>
								</div> <!-- .inside -->

							</div> <!-- .postbox -->

						</div> <!-- .meta-box-sortables .ui-sortable -->

					</div> <!-- post-body-content -->

					<!-- sidebar -->
					<div id="postbox-container-1" class="postbox-container">
						<div class="meta-box-sortables">
							<div class="postbox">
								<h3><span><?php _e('Plugin Information','cbratingsystem'); ?></span></h3>
								<div class="inside">
									<?php
									define( 'CB_RATINGSYSTEM_SUPPORT_VIDEO_DISPLAY', true );
									require( CB_RATINGSYSTEM_PATH . '/cb-sidebar.php' );
									?>
								</div> <!-- .inside -->

							</div> <!-- .postbox -->

						</div> <!-- .meta-box-sortables -->

					</div> <!-- #postbox-container-1 .postbox-container -->

				</div> <!-- #post-body .metabox-holder .columns-2 -->

				<br class="clear">
			</div> <!-- #poststuff -->

		</div> <!-- .wrap -->
		<?php
	}

	/*
     * The landing page for rating system plugin. Here, the page/menu
     * listings and plugin uninstallation process can be found.
     */

	public static function admin_ratingForm_listing_page_add() {
		global $wp_roles;

		$form_editurl       = admin_url( 'admin.php?page=ratingformedit' );


		$postTypes          = CBRatingSystem::post_types();
		$userRoles          = CBRatingSystem::user_roles();
		$editorUserRoles    = CBRatingSystem::editor_user_roles();

		$form_default       = CBRatingSystem::form_default_fields();
		$form_question      = CBRatingSystem::form_default_question();
		$form_criteria      = CBRatingSystem::form_default_criteria();

		$savedform          = false;

		//check the post methods and pass the values
		$post = $_POST;
		//wp_safe_redirect('http://localhost/wordpress/wp-admin');
		//if saved then redirect from here
		$errorMessageHtml = '';
		if ( isset( $post['cbratingsubmit'] ) && isset($post['ratingForm']) && is_array($post['ratingForm']) ) {

			$return = self::admin_save_ratingForm_new( $post['ratingForm'], $form_default, $form_question, $form_criteria, $postTypes, $userRoles, $editorUserRoles);




			if($return != false && is_numeric($return)){
				//post saved successfully
				//echo $return;
				$redirect_url = $form_editurl.'&id='.$return.'&updated=1';
				CBRatingSystem::redirect($redirect_url);


			}

			if(isset($return['errorMessage'])){
				$trimmedErrorMessage = array_map( 'trim', $return['errorMessage'] );
				$errorMessage        = array_filter( $trimmedErrorMessage );

				if ( ! empty( $errorMessage ) ) {
					$errorMessageHtml = '<p>' . implode( "</p><p>", $errorMessage ) . '</p>';
					$errorMessageHtml = '<div id="messages" class="updated error">' . $errorMessageHtml . '</div>';
					//echo $output;
				}
			}
		}//if submitted
		else if(isset( $_GET['id']) && intval($_GET['id']) > 0){
			$ratingForm_id = isset( $_GET['id'] )? $_GET['id'] : 0;
			$ratingForm_id = intval($ratingForm_id);
			//if ( ! is_null( $ratingForm_id ) ) {
			if ( $ratingForm_id != 0 ) {
				$savedform = CBRatingSystemData::get_ratingForm( $ratingForm_id, false );
//				echo '<pre>';
//				print_r($savedform);
//				echo  '</pre>';
				//exit();
			}
		}


		///var_dump($post);



		$output = self::admin_ratingForm_edit_page_new($form_default, $form_question, $form_criteria, $post, $savedform );



		?>
		<div class="wrap">

			<div id="icon-options-general" class="icon32"></div>
			<h2><?php _e( 'Rating Form Edit /Add', 'cbratingsystem' ) ; ?></h2>
			<?php echo $errorMessageHtml; ?>
			<?php
				if(isset($_GET['updated']) && intval($_GET['updated']) == 1 && intval($_GET['id']) > 0){
					echo '<div id="messages" class="updated success"><p>'.sprintf(__('Form created/updated successfully. Form Id: %d','cbratingsystem'),intval($_GET['id'])).'</p></div>';
				}
			?>

			<div id="poststuff">

				<!--div id="post-body" class="metabox-holder columns-2"-->
				<div id="post-body" class="metabox-holder columns-1">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">

								<h3><span><?php _e( 'Edit/Add', 'cbratingsystem' ) ; ?></span></h3>
								<div class="inside">
									<?php
										echo $output;
									?>
								</div> <!-- .inside -->

							</div> <!-- .postbox -->

						</div> <!-- .meta-box-sortables .ui-sortable -->

					</div> <!-- post-body-content -->

					<!-- sidebar -->
					<!--div id="postbox-container-1" class="postbox-container">
						<div class="meta-box-sortables">
							<div class="postbox">
								<h3><span><?php _e('Plugin Information','cbratingsystem'); ?></span></h3>
								<div class="inside">
									<?php
									define( 'CB_RATINGSYSTEM_SUPPORT_VIDEO_DISPLAY', true );
									//require( CB_RATINGSYSTEM_PATH . '/cb-sidebar.php' );
									?>
								</div>

							</div>

						</div>

					</div--> <!-- #postbox-container-1 .postbox-container -->

				</div> <!-- #post-body .metabox-holder .columns-2 -->

				<br class="clear">
			</div> <!-- #poststuff -->

		</div> <!-- .wrap -->
		<?php
		/*
		if ( isset( $_POST['form_id'] ) && ( $_POST['form_id'] == 'cb_ratingForm_edit_form' ) ) {

            $ratingForm = self::admin_save_ratingForm( $_POST );

			if ( ( $ratingForm !== false ) && ( ! is_array( $ratingForm ) ) ) {
				if ( $ratingForm != 0 ) {
					$ratingForm  = CBRatingSystemData::get_ratingForm( $ratingForm, true );
				} else {
					//$ratingForm = $ratingForm_id;
				}

				// echo '<pre>'; print_r($ratingForm); echo '</pre>'; //die();

				$output .= '<div class="icon32 icon32_cbrp_admin icon32-cbrp-edit" id="icon32-cbrp-edit"><br></div>
                            <h2>' . __( 'Rating Form Edit /Add', 'cbratingsystem' ) . '</h2>';
				$output .= '<div class="admin_ratingForm_wrapper metabox-holder has-right-sidebar" id="poststuff">';

				$output .= '<div id="messages" class="updated below-h2"><p>' . __( 'Rating form has been saved successfully', 'cbratingsystem' ) . '</p></div>';

                if ( ! empty( $ratingForm ) ) {

					$output .= self::admin_ratingForm_edit_page( $ratingForm );
				} else {

					$output .= self::admin_ratingForm_edit_page( array() );
				}
				$output .= file_get_contents( CB_RATINGSYSTEM_PATH . '/cb-sidebar.php' );

				$output .= '</div>';


				echo $output;
				//die;

			} elseif ( is_array( $ratingForm ) ) {


				$output .= '<div class="icon32 icon32_cbrp_admin icon32-cbrp-edit" id="icon32-cbrp-edit"><br></div>
                            <h2>' . __( 'Rating Form Edit /Add', 'cbratingsystem' ) . '</h2>';
				$output .= '<div class="admin_ratingForm_wrapper metabox-holder has-right-sidebar" id="poststuff">';

				if ( ! empty( $ratingForm['errorMessageText'] ) ) {
					$output .= $ratingForm['errorMessageText'];
				}

				$output .= self::admin_ratingForm_edit_page( array() );
				$output .= file_get_contents( CB_RATINGSYSTEM_PATH . '/cb-sidebar.php' );

				$output .= '</div>';

				echo $output;
				//die;
			}
		}//end submit post method

		if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'ratingformedit' ) && ! isset( $_POST['form_id'] ) ) {
			$ratingForm_id = isset( $_GET['id'] )? $_GET['id'] : 0;

            $ratingForm_id = intval($ratingForm_id);

			//if ( ! is_null( $ratingForm_id ) ) {
				if ( $ratingForm_id != 0 ) {
					$ratingForm = CBRatingSystemData::get_ratingForm( $ratingForm_id, true );
				} else {
					$ratingForm = $ratingForm_id;
				}
				//echo '<pre>'; print_r($ratingForm); echo '</pre>'; //die();


				$output .= '<div class="icon32 icon32_cbrp_admin icon32-cbrp-edit" id="icon32-cbrp-edit"><br></div>
                            <h2>' . __( 'Codeboxr Rating System Rating Form Edit /Add', 'cbratingsystem' ) . '</h2>';
				$output .= '<div class="admin_ratingForm_wrapper metabox-holder has-right-sidebar" id="poststuff">';

				if ( isset( $_SESSION['success_message'] ) and ! empty( $_SESSION['success_message'] ) ) {
					$output .= $_SESSION['success_message'];
				}
				if ( ! empty( $ratingForm ) ) {
					$output .= self::admin_ratingForm_edit_page( $ratingForm );
				} else {
					$output .= self::admin_ratingForm_edit_page( array() );
				}

				$output .= file_get_contents( CB_RATINGSYSTEM_PATH . '/cb-sidebar.php' );

				$output .= '</div>';
			//}
		} else {
			//listing rating forms
			$output .= self::admin_ratingForm_listing_html();
		}

		echo $output;
		*/
	}

    /**
     * admin_rating_reports_page
     */
    public static function admin_rating_reports_page() {
		$output = CBRatingSystemAdminReport::logReportPageOutput();

		echo $output;
	}

    /**
     * admin_rating_average_reports_page
     */
    public static function admin_rating_average_reports_page() {
		$output = CBratinglogreportoutput::averageReportPageOutput();

		echo $output;
	}

	/**
	 * Backend Rating form validation and Save
	 *
	 * @param $ratingFormData
	 * @param $form_default
	 * @param $form_question
	 * @param $form_criteria
	 * @param $postTypes
	 * @param $userRoles
	 * @param $editorUserRoles
	 *
	 * @return array|bool
	 */
	private static function admin_save_ratingForm_new( $ratingFormData, $form_default, $form_question, $form_criteria, $postTypes, $userRoles, $editorUserRoles ) {

		global $wpdb;

		if ( check_admin_referer( 'cb_ratingForm_edit_form_nonce_action', 'cb_ratingForm_edit_form_nonce_field' ) ) {



				$path               = admin_url( 'admin.php?page=ratingformedit' );

				//$ratingFormData     = $post['ratingForm'];

				/*echo '<pre>';
				print_r($ratingFormData);
				echo '</pre>';*/
				//exit();

				$errorHappened      = false;
				$errorMessage       = array();
				$affectedFields     = array();
				$wp_error           = new WP_Error( 'input_error' );
				$formSavableData    = array();


				//special care for id
				$formSavableData['id']           = ( isset( $ratingFormData['id'] ) && ( intval($ratingFormData['id']) > 0 ) ) ? intval($ratingFormData['id']) : 0;

				//let's merge the extrafield fields with the main array to make it go it easy

				$ratingFormData = array_merge($ratingFormData, $ratingFormData['extrafields']);

				foreach($form_default as $key => $field){
					$singlefield_error = false;
					$extrafield = (isset($field['extrafield']) && $field['extrafield']) ? true : false;

					if(!isset($ratingFormData[$key])) continue;

					//$value      = ($extrafield) ? $ratingFormData['extrafield'][$key] : $ratingFormData[$key];
					$value      = $ratingFormData[$key];  //we merged the extrafields with defaults for easy access

					if(isset($field['required']) && $field['required']){
						//text type field
						if($field['type'] == 'text'){
							$value = sanitize_text_field($value);
							if(isset($field['numeric']) && $field['numeric'] ){
								$value = intval($value);
							}


							if(strlen($value) == ''){
								$errorHappened      = true;
								$errorText          = $field['errormsg'];
								$errorMessage[]     = $errorText;
								$wp_error->add( 'input_error', $errorText );
								$affectedFields[]   = $key;
								$singlefield_error  = true;
							}
							else if(isset($field['numeric']) && $field['numeric'] && $value == 0){
								$errorHappened      = true;
								$errorText          = sprintf(__( ' Field "%s" need to be numeric and can not be 0', 'cbratingsystem' ), $field['label']);
								$errorMessage[]     = $errorText;
								$wp_error->add( 'input_error', $errorText );
								$affectedFields[]   = $key;
								$singlefield_error  = true;
							}
							else if(isset($field['min']) && strlen($value) < $field['min'] ){
								$errorHappened      = true;
								$errorText          = sprintf(__( ' Field "%s"  needs minimum length %d', 'cbratingsystem' ), $field['label'], $field['min']);
								$errorMessage[]     = $errorText;
								$wp_error->add( 'input_error', $errorText );
								$affectedFields[]   = $key;
								$singlefield_error  = true;
							}
							else if(isset($field['max']) && strlen($value) > $field['max'] ){
								$errorHappened  = true;
								$errorText      = sprintf(__( ' Field "%s" maximum length allowed %d', 'cbratingsystem' ), $field['label'], $field['min']);
								$errorMessage[] = $errorText;
								$wp_error->add( 'input_error', $errorText );
								$affectedFields[] = $key;
								$singlefield_error  = true;
							}

						}
						else if($field['type'] == 'radio'){ //radio type field

						}
						else if($field['type'] == 'multiselect' && isset($field['multiple']) && $field['multiple']){ //multi select field

							//multi checkbox used for multiple select
							if(empty($value) || !is_array($value)){
								$errorHappened      = true;
								$errorText          = $field['errormsg'];
								$errorMessage[]     = $errorText;
								$wp_error->add( 'input_error', $errorText );
								$affectedFields[]   = $key;
								$singlefield_error  = true;
							}

						}
						else if($field['type'] == 'multiselect' && isset($field['multiple']) && ($field['multiple'] == false)){  ////multi select field with multiple false, we used this type field to use the jqury choosen plugin
							//multi checkbox used for single selet
							if(empty($value) || !is_array($value)){
								$errorHappened      = true;
								$errorText          = $field['errormsg'];
								$errorMessage[]     = $errorText;
								$wp_error->add( 'input_error', $errorText );
								$affectedFields[]   = $key;
								$singlefield_error  = true;
							}
						}


						if($field['type'] == 'multiselect'){
							$value = maybe_serialize($value);
						}

						if($extrafield){
							$formSavableData['extrafields'][$key] = $value;
						}
						else{
							$formSavableData[$key] = $value;
						}





					}
					else {
						//not required fields
						//$extrafield = (isset($field['extrafield']) && $field['extrafield']) ? true : false;
						//$value = ($extrafield) ? $ratingFormData['extrafield'][$key] : $ratingFormData[$key];

						if($field['type'] == 'text'){
							$value = sanitize_text_field($value);
							if(isset($field['numeric']) && $field['numeric'] ){
								$value = intval($value);
							}
						}

						//no check for radio for now
						//

						if($field['type'] == 'multiselect'){
							$value = maybe_serialize($value);
						}
						//field is ok, let move save for db entry
						if($extrafield){
							$formSavableData['extrafields'][$key] = $value;
						}
						else{
							$formSavableData[$key] = $value;
						}

					}//end not required

				}//end foreach


				//validating custom criteria
				if ( isset( $ratingFormData['custom_criteria'] ) && ! empty( $ratingFormData['custom_criteria'] ) ) {
					$labelCount    = 0;
					$selectedStars = 0;

					$labelCount_index = 0;

					//for each criteria
					foreach ( $ratingFormData['custom_criteria'] as $cindex => $criteria ) {
						$labelCount_index ++;

						$label      = (isset($criteria['label']) && $criteria['label'] != '') ? $criteria['label'] : sprintf(__('Criteria %d', 'cbratingsystem'), $labelCount_index);
						$enabled    = (isset( $criteria['enabled'] ) && ( $criteria['enabled'] == 1 ))? 1: 0;

						$formSavableData['custom_criteria'][$cindex]['label']   = sanitize_text_field( $label );
						$formSavableData['custom_criteria'][$cindex]['enabled'] = $enabled;

						$starCount = 0;
						//for stars of each criteria
						foreach ( $criteria['stars'] as $sindex => $stars ) {
							$seabled = (isset( $stars['enabled']) && ($stars['enabled'] == 1)) ? 1 : 0;  //single star enabled status
							$stitle  = (isset( $stars['title']) && ($stars['title'] != '')) ? sanitize_text_field( $stars['title'] ) : __('Star Title', 'cbratingsystem');  //single star title

							$formSavableData['custom_criteria'][$cindex]['stars'][$sindex]['enabled']   = $seabled;
							$formSavableData['custom_criteria'][$cindex]['stars'][$sindex]['title']     = $stitle;
							$starCount ++;
							if($seabled) $selectedStars ++;
						}
						if($enabled ) $labelCount ++;

					}//end loop

					//echo '<pre>'; print_r($formSavableData['custom_criteria']); echo '</pre>'; die();
					$formSavableData['custom_criteria'] = maybe_serialize( $formSavableData['custom_criteria'] );
					//$formSavableData['custom_criteria'] =  $formSavableData['custom_criteria'];

					if ( ( $labelCount < 1 ) ) {
						$errorHappened  = true;
						$errorText      = __( 'You must enable and name at least one criteria and have to choose at least one star from ', 'cbratingsystem' ) . __( 'Custom Criteria', 'cbratingsystem' ) . '</i>';
						$errorMessage[] = $errorText;
						$wp_error->add( 'input_error', $errorText );
						$affectedFields[] = 'custom_criteria';
					}
					if ( ( $selectedStars < 1 ) ) {
						$errorHappened  = true;
						$errorText      = __( 'You must choose at least one STAR from ' ) . __( 'Custom Criteria', 'cbratingsystem' )  . '</i> label';
						$errorMessage[] = $errorText;
						$wp_error->add( 'input_error', $errorText );
						$affectedFields[] = 'custom_criteria';
					}

				} else {
					$errorHappened  = true;
					$errorText      = __( 'You must type at least one criteria name/label and have to choose at least one star from ', 'cbratingsystem' ) . __( 'Custom Criteria', 'cbratingsystem' ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'custom_criteria';
				}
				//end validating custom criteria

//				echo '<pre>';
//				print_r($ratingFormData['custom_question']);
//				echo '</pre>';

				//validating custom questions
				if ( isset( $ratingFormData['custom_question'] ) && ! empty( $ratingFormData['custom_question'] ) ) {
					$emptyTitle = 0;
					foreach ( $ratingFormData['custom_question'] as $index => $firstLevelArray ) {
						if ( ! empty( $firstLevelArray['title'] ) ) {

							//if ( isset( $firstLevelArray['enabled'] ) && ( $firstLevelArray['enabled'] == 1 ) ) {
								if ( ! empty( $firstLevelArray['field'] ) && ! empty( $firstLevelArray['field']['type'] ) ) {
									$formSavableData['custom_question'][$index]['title']   = sanitize_text_field( $firstLevelArray['title'] );

									//var_dump($firstLevelArray['required']);

									$formSavableData['custom_question'][$index]['required'] = isset( $firstLevelArray['required'] ) ? intval($firstLevelArray['required']) : 0;
									$formSavableData['custom_question'][$index]['enabled'] = isset( $firstLevelArray['enabled'] ) ? intval($firstLevelArray['enabled']) : 0;

									//var_dump($formSavableData['custom_question'][$index]['required']);
									//$formSavableData['custom_question'][$firstLevel]['enabled'] = 1;
									$formSavableData['custom_question'][$index]['field']   = array();



									/*if ( isset( $firstLevelArray['required'] ) ) {
										//$formSavableData['custom_question']['required'][$index]            = $index;
										$formSavableData['custom_question'][$index]['required'] = 1;
									}*/


									$fieldWithArr = array( 'checkbox', 'radio' );
									$type         = $firstLevelArray['field']['type'];

									if ( in_array( $type, $fieldWithArr ) ) {
										$formSavableData['custom_question'][$index]['field']['type'] = $type;

										foreach ( $firstLevelArray['field'][$type] as $fieldId => $field ) {

											if ( is_numeric( $fieldId ) && ( $firstLevelArray['field'][$type]['count'] > $fieldId ) ) {
												if ( ! empty( $field['text'] ) ) {
													$formSavableData['custom_question'][$index]['field'][$type][$fieldId]['text'] = sanitize_text_field( $field['text'] );
												}

											} elseif ( $fieldId == 'seperated' ) {
												if ( $firstLevelArray['field']['type'] != 'radio' ) {

													if ( ( $field == 1 ) ) {
														$formSavableData['custom_question'][$index]['field'][$type]['seperated'] = 1;
													} else {
														$formSavableData['custom_question'][$index]['field'][$type]['seperated'] = 0;
													}
												}
											} elseif ( $fieldId == 'count' ) {
												$formSavableData['custom_question'][$index]['field'][$type]['count'] = ( $firstLevelArray['field'][$type]['count'] > 0 ) ? $firstLevelArray['field'][$type]['count'] : 1;
											}
										}
									} elseif ( $firstLevelArray['field']['type'] == 'text' ) {
										$formSavableData['custom_question'][$index]['field']['type'] = 'text';
									}
								}
							//} else {
							//	$formSavableData['custom_question']['all'][$firstLevel] = sanitize_text_field( $firstLevelArray['title'] );
							//}
						} else {
							$emptyTitle ++;
						}
					}
					//end validating custom questions

					//echo '<pre>'; print_r($formSavableData['custom_question']); echo '</pre>'; exit();

					$formSavableData['custom_question'] = maybe_serialize( $formSavableData['custom_question'] );

					if ( ( $emptyTitle > 0 ) ) {
						$errorHappened  = true;
						$errorText      = __( 'One of your question title field is empty', 'cbratingsystem' );
						$errorMessage[] = $errorText;
						$wp_error->add( 'input_error', $errorText );
						$affectedFields[] = 'custom_question';
					}

				}

				//echo '<pre>'; var_dump($formSavableData); echo '</pre>'; die();
				if ( ! $errorHappened && empty( $errorMessage ) ) {
					if ( class_exists( 'CBRatingSystemData' ) ) {

//						echo '<pre>';
//						print_r($formSavableData);
//						echo '</pre>';

						$formSavableData['extrafields']  = maybe_serialize($formSavableData['extrafields']);
						//exit();

//						echo '<pre>';
//						print_r($formSavableData);
//						echo '</pre>';
//						exit();


						$formId = CBRatingSystemData::update_ratingForm( $formSavableData );

						if ( $formId !== false ) {
							$return = false;

							if ( CBRatingSystem::can_automatically_make_deafult_form( $formId ) === true ) {
								update_option( 'cbratingsystem_defaultratingForm', $formId );
							}

							$return = $formId;
							return $return;

						} else {

							$errorHappened  = true;
							$errorText      = apply_filters('cbrating_error', __( 'Form save failed. Please check all fields are filled properly.', 'cbratingsystem' ));
							$errorMessage[] = $errorText;
							$wp_error->add( 'input_error', $errorText );
							$affectedFields[] = 'overall';
						}
					}
				}

				if ( $errorHappened && ! empty( $errorMessage ) ) {
					return array( 'affectedFields' => $affectedFields, 'formSavableData' => $formSavableData, 'errorMessage' => $errorMessage );
				}

		}
	}//end of method


	/**
	 * @param $post  array
	 *
	 * @return string
	 */
	public static function admin_ratingForm_edit_page_new($form_default, $form_question, $form_criteria, $post, $savedform = false ){
		global $wp_roles;

		$cbratingsystem_tabs = array(
			'cbratingsystem_general_setting'        => __('Generation Setting','cbratingsystem'),
			'cbratingsystem_criteria_setting'       => __('Custom Criteria','cbratingsystem'),
			'cbratingsystem_questions_setting'      => __('Custom Questions','cbratingsystem')
		);

		$output = '<form action="" method="post" id="cb-ratingForm-edit-form" accept-charset="UTF-8">';
			//building tab interface
			$output .= '<h2 class="nav-tab-wrapper">';
			$tabcount = 0;
			foreach ( $cbratingsystem_tabs as $tab_key => $tab_title ) {
				$active_class = ($tabcount < 1)? ' nav-tab-active': '';
				//$active_class = '';

				$output .= sprintf( '<a href="#%1$s" class="nav-tab '.$active_class.'" id="%1$s-tab">%2$s</a>', $tab_key, $tab_title );
				$tabcount++;
			}
			$output .= '</h2>';
			//end tab interface



			$postdata = false;

			if(isset($post['ratingForm']) && is_array($post['ratingForm'])){
				$postdata = $post['ratingForm'];
				$postdata = array_merge($postdata, $postdata['extrafields']);
			}



			//if($savedform != false){
			//	$savedform = array_merge($savedform, $savedform['extrafields']);
			//}

//			echo '<pre>';
//			print_r($savedform);
//			echo '</pre>';

			//general tab content
			$output .= '<div id="cbratingsystem_general_setting" class="ratingtabgroup">';
				$output .= '<table class="form-table"><tbody>';
				//general regular fields
				foreach($form_default as $key => $item){
					if(!isset($item['type'])) continue;

					///ratingForm
					$extrafield = (isset($item['extrafield']) && $item['extrafield']) ? true : false;

					$name = ($extrafield)? 'ratingForm[extrafields]['.$key.']': 'ratingForm['.$key.']';


					//var_dump($postdata);

					if($postdata != false){
						//var_dump('post method');
						$item['default'] = $postdata[$key];
					}
					else if($savedform != false){

						$savedvalue  = isset($savedform[$key]) ? $savedform[$key]: $item['default'];
						if($item['type'] == 'multiselect'){

							$savedvalue = maybe_unserialize($savedvalue);
						}

						$item['default'] = $savedvalue;
					}


					if($item['type'] == 'text'){
						$required       = (isset($item['required']) && $item['required'])? ' required ': '';
						$placeholder    = (isset($item['placeholder']) && $item['placeholder'] != '' )?  ' placeholder="'.$item['placeholder'].' "': '';

						$output .= '<tr>
									<th scope="row"><label for="cbratingf_'.$key.'">'.$item['label'].'</label></th>
									<td><input '.$required.$placeholder.' name="'.$name.'" type="text" id="cbratingf_'.$key.'" value="'.$item['default'].'" class="regular-text"></td>
								</tr>';
					}
					else if($item['type'] == 'radio'){
						$options = $item['options'];
						//$name = ($extrafield)? 'ratingForm[extrafield]['.$key.']': 'ratingForm['.$key.']';


						$radio_html = '';
						$radio_html .= '<tr>
											<th scope="row">'.$item['label'].'</th>
											<td>
												<fieldset>
													<legend class="screen-reader-text"><span>input type="radio"</span></legend>';
												foreach($options as $k => $v ){
													$radio_html .= '<label title="'.$v.'"><input '.checked( $item['default'], $k, $echo = false ).' type="radio" name="'.$name.'" value="'.$k.'" /> <span>'.$v.'</span></label><br />';
												}
						$radio_html .='			</fieldset>
											</td>
										</tr>';
						$output .= $radio_html;
					}
					else if($item['type'] == "multiselect" && isset($item['post_types']) && $item['post_types']){
						$multiple       = $item['multiple'];
						$multiple_text  = ($multiple)? ' multiple ': '';
						//$name = isset($item['extrafield'])? 'ratingForm[extrafield]['.$key.']': 'ratingForm['.$key.']';
						$selectoutput = '<tr valign="top">
											<th scope="row"><label for="cbratingf_'.$key.'">'.$item['label'].'</label></th>
											<td>
												<select id="cbratingf_'.$key.'" '.$multiple_text.' data-placeholder="'.$item['placeholder'].'" name="'.$name.'[]" class="cbratingf_chosen required">';
												foreach($item['options'] as $postgroup){
													$selectoutput .= '<optgroup label="' . $postgroup['label'] . '">';
													foreach($postgroup['types'] as $type => $label){
														if($multiple){
															$selected = (in_array($type, $item['default'])) ? 'selected="selected"': '';
														}
														else{
															$selected = ($type == $item['default']) ? 'selected="selected"': '';
														}
														$selectoutput .= '<option '.$selected.' value="'.$type.'">'.$label.'</option>';
													}
												}

						$selectoutput .='		</select>
											</td>
										</tr>';
						$output .= $selectoutput;
					}
					else if($item['type'] == "multiselect" && isset($item['user_types']) && $item['user_types']){
						$multiple       = $item['multiple'];
						$multiple_text  = ($multiple)? ' multiple ': '';
						//$name = isset($item['extrafield'])? 'ratingForm[extrafield]['.$key.']': 'ratingForm['.$key.']';

						$selectoutput = '<tr valign="top">
											<th scope="row"><label for="cbratingf_'.$key.'">'.$item['label'].'</label></th>
											<td>
												<select id="cbratingf_'.$key.'" '.$multiple_text.' data-placeholder="'.$item['placeholder'].'" name="'.$name.'[]" class="cbratingf_chosen required">';

						if ( ! empty( $item['options'] ) ) {

							foreach ( $item['options'] as $optGrp => $options ) {
								if ( ! empty( $options ) ) {

									$selectoutput .= '<optgroup label="' . $optGrp . '">';
									foreach ( $options as $type => $typeLabel ) {
										if($multiple){
											$selected = (in_array($type, $item['default'])) ? 'selected="selected"': '';
										}
										else{
											$selected = ($type == $item['default']) ? 'selected="selected"': '';
										}


										$selectoutput .= '<option '.$selected.' value="' . $type . '">' . $typeLabel['name'] . '</option>';
									}
									$selectoutput .= '</optgroup>';
								}
							}
						}

						$selectoutput .='		</select>
											</td>
										</tr>';
						$output .= $selectoutput;
					}
					else if($item['type'] == "multiselect"){
						//a  generic multi select which can be used by any 3rd party plugin, we will define the array structure for options and default values
						$multiple       = $item['multiple'];
						$multiple_text  = ($multiple)? ' multiple ': '';

						//$name = isset($item['extrafield'])? 'ratingForm[extrafield]['.$key.']': 'ratingForm['.$key.']';

						$selectoutput = '<tr valign="top">
											<th scope="row"><label for="cbratingf_'.$key.'">'.$item['label'].'</label></th>
											<td>
												<select id="cbratingf_'.$key.'" '.$multiple_text.' data-placeholder="'.$item['placeholder'].'" name="'.$name.'[]" class="cbratingf_chosen required">';
														foreach($item['options'] as $k => $v){
															if($multiple){
																$selected = (in_array($type, $item['default'])) ? 'selected="selected"': '';
															}
															else{
																$selected = ($type == $item['default']) ? 'selected="selected"': '';
															}

																$selected = (in_array($k, $item['default'])) ? 'selected="selected"': '';
															$selectoutput .= '<option '.$selected.' value="'.$k.'">'.$v.'</option>';
														}
						$selectoutput .='		</select>
											</td>
										</tr>';
						$output .= $selectoutput;
					}

					else if($item['type'] == 'hidden'){
						$name = isset($item['extrafield'])? 'ratingForm[extrafield]['.$key.']': 'ratingForm['.$key.']';
						$output .= '<input type="hidden" name="'.$name.'" value="'.$item['default'].'" />';
					}

				}//end foreach

				$output .= '</tbody></table>';
			$output .= '</div>';
			//end tab general setting


			//criteria tab content
			$output .= '<div id="cbratingsystem_criteria_setting" class="ratingtabgroup">';
				//$output .= '<table class="form-table">';

					$customCriteria  = $form_criteria['custom_criteria'];

					if($postdata != false){
						$customCriteria = maybe_unserialize($postdata['custom_criteria']);
					}

					if($savedform != false){
						$customCriteria     = maybe_unserialize($savedform['custom_criteria']);

//						echo '<pre>';
//						print_r($customCriteria);
//						echo '</pre>';
						//exit(1);
					}
					$output .= self::admin_ratingForm_edit_page_custom_criteria( $customCriteria, $form_default, $form_question, $form_criteria, $post, $savedform );

				//$output .= '</table>';
			$output .= '</div>';

			//question tab content
			$output .= '<div id="cbratingsystem_questions_setting" class="ratingtabgroup">';

				$customQuestion = $form_question['custom_question'];

				if($postdata != false){
					$customQuestion = maybe_unserialize($postdata['custom_question']);
				}

				if($savedform != false){
					$customQuestion     = maybe_unserialize($savedform['custom_question']);
				}

				$output .= ( CBRatingSystemAdminFormParts::admin_ratingForm_edit_page_custom_question( $customQuestion, $form_default, $form_question, $form_criteria, $post, $savedform ) ) ;

			$output .= '</div>';

			$output .=  wp_nonce_field( 'cb_ratingForm_edit_form_nonce_action', 'cb_ratingForm_edit_form_nonce_field' ) . '
						<div class="form-actions form-wrapper" id="edit-actions">
                            <input type="submit" id="edit-submit" class="button button-primary button-large" name="cbratingsubmit" value="'.__('Save','cbratingsystem').'" class="form-submit">
                        </div>';

		$output .= '</form>';
		return $output;

	}



	//public static function admin_ratingForm_edit_page_custom_label( $RFSettings = array(), $form_default = array() ) {
	//$customCriteria, $form_default, $form_question, $form_criteria, $post, $savedform
	public static function admin_ratingForm_edit_page_custom_criteria( $customCriteria, $form_default, $form_question, $form_criteria, $post, $savedform = false) {

//
//		echo '<pre>';
//		print_r($customCriteria);
//		echo '</pre>';

		$displayItemCount   = 0;
		$output = '';



		$output .=
			'<div class="cb-ratingForm-edit-custom-criteria-container form-item" id="custom-criteria">
	            <h3 class="" for="edit-custom-criteria">'.__('Custom Criteria','cbratingsystem').' <span class="form-required" title="'.__('This field is required','cbratingsystem').'">*</span></h3>
	            <div id="edit-custom-criteria-fields-wrapper" class="edit-custom-criteria-fields-wrapper">';

//
//					foreach ( $customCriteria as $label_id => $labelArr ) {
//						if ( ! empty( $labelArr['label'] ) && ( isset($labelArr['enabled']) && $labelArr['enabled'] == 1 ) ) {
//							$displayItemCount ++;
//						}
//					}

					//$displayItemCount = ( $displayItemCount - 2 );

					/*
			        $label_indexes = array();

			        if(is_array($customCriteria) && empty($customCriteria)){
				        //as we used a default array now it will not be true any more
			            $count_label = 3;
			        }
			        else{
			            foreach($customCriteria as $index => $customCriteria_one){
			                array_push($label_indexes, $index);

			            }
			            $count_label = max($label_indexes)+1;
			            $count_label = $count_label <3?  3:$count_label;
			        }
					*/

					$count_label = count($customCriteria);

					for ( $label_id = 0; $label_id < $count_label; $label_id ++ ) {
						/*
						if($label_id == 1){
							echo '<pre>';
							print_r($customCriteria[1]);
							echo '</pre>';

						}
						*/

						if ( $label_id == 0 ) {
							$class = 'first';
						} elseif ( $label_id == ($count_label -1)) {
							$class = 'last';
						} else {
							$class = 'other';
						}

						//$displayItem = ( isset($postCustomCriteria[$label_id]['label'])? ( !empty($postCustomCriteria[$label_id]['label'])? ' display_item':'') : (!empty($customCriteria[$label_id]['label']) ? ' display_item' : '') );
						$displayItem = ' display_item';

						$output .=
							'<div class="custom-criteria-wrapper custom-criteris-wrapper-' . $class . ' custom-criteria-wrapper-label-id-' . $label_id . $displayItem . '">
			                    <!--input type="hidden" name="ratingForm[custom_criteria][' . $label_id . '][enabled]" value="0" /-->
			                    <input type="checkbox" name="ratingForm[custom_criteria][' . $label_id . '][enabled]" value="1" class="custom-criteria-enable-checkbox edit-custom-criteria-enable-checkbox-label-' . $label_id . '"
			                         ' . checked( $customCriteria[$label_id]['enabled'] , '1', false ) . ' />
			                    <div data-label-id="' . $label_id . '" class="add_left_margin form-type-textfield form-item-custom-criteria-label label-id-' . $label_id . ' ' . $class . $displayItem . '">
			                        <input type="text" id="edit-custom-criteria-label-' . $label_id . '"
			                            name="ratingForm[custom_criteria][' . $label_id . '][label]" ' . ( isset( $customCriteria[$label_id]['label'] ) ? 'value="' . stripslashes( $customCriteria[$label_id]['label'] ) . '" class="form-text display_item" ' : 'value="Criteria ' . ( $label_id + 1 ) . '" class="form-text"' )  . '>
			                    </div>';

								//tag matching start
								$output .= '
				                    <div class="add_left_margin form-type-checkboxes form-item-custom-criteria-stars label-star-id-' . $label_id . $displayItem . '">
				                        <div id="edit-custom-criteria-checkboxes" class="form-checkboxes">';
										/*
							            if(isset($customCriteria[$label_id])&& $customCriteria[$label_id] != NULL){
							                $star_count  = max(array_keys($customCriteria[$label_id]['stars']));
							                $star_count = $star_count >= 5 ? $star_count+1 : 5;
							            }
							            else{
							                $star_count  = 5;
							            }
										*/

										$star_count = count($customCriteria[$label_id]['stars']);

										for ( $star_id = 0; $star_id < $star_count; $star_id ++ ) {
											$star_title = array( __('Worst', 'cbratingsystem'), __('Bad', 'cbratingsystem'), __('Not Bad','cbratingsystem'), __('Good', 'cbratingsystem'), __('Best','cbratingsystem') );
											$output .=
												'<div class="form-item form-type-checkbox form-item-custom-criteria-star star-id-' . $star_id . $displayItem . '">
											                <!--input type="hidden" name="ratingForm[custom_criteria][' . $label_id . '][stars][' . $star_id . '][enabled]" value="0" /-->
											                <input type="checkbox" id="edit-custom-criteria-enable-label-' . $label_id . '-star-' . $star_id . '"
											                    name="ratingForm[custom_criteria][' . $label_id . '][stars][' . $star_id . '][enabled]"
											                    value="1" ' . checked( $customCriteria[$label_id]['stars'][$star_id]['enabled'] , '1', false ) . 'class="form-checkbox">
											                <label data-label-id="' . $label_id . '" data-star-id="' . $star_id . '" title="Click to edit"
											                    class="custom-criteria-single-label label-' . $label_id . '-option-star-' . $star_id . ' label-' . $label_id . '-option-star option-star-id-' . $star_id . ' option mouse_normal"
											                        >' . ( isset( $customCriteria[$label_id]['stars'][$star_id]['title'] ) ?  $customCriteria[$label_id]['stars'][$star_id]['title'] : $star_title[$star_id] ) . '</label>
											                <input data-label-id="' . $label_id . '" data-star-id="' . $star_id . '" type="text" id="edit-custom-criteria-text-star-' . $star_id . '"
											                    name="ratingForm[custom_criteria][' . $label_id . '][stars][' . $star_id . '][title]"
											                    value="' . (isset( $customCriteria[$label_id]['stars'][$star_id]['title'] ) ?  $customCriteria[$label_id]['stars'][$star_id]['title']: $star_title[$star_id]) . '"
											                    class="form-text disable_field edit-custom-criteria-label-text-star edit-custom-criteria-label-text-' . $label_id . '-star edit-custom-criteria-label-text-' . $label_id . '-star-' . $star_id . '">
											    </div>';
										}

								$output .= '</div>
												</div>';
								//tag matching end


								//premium plugin features
								$custom_rating_star_msg           = '<p style ="margin-left:10px;">'.__('Unlimited star in pro version','cbratingsystem').'</p>';
					            $star_count_addon      = 5;
								$custom_rating_star_msg = apply_filters('cbratingsystem_add_new_star', $custom_rating_star_msg , $label_id , $star_count_addon);

					            $output                 .= $custom_rating_star_msg;

							$output .= '</div>';
					}//end for loop
				$output .= '</div>'; //end of #edit-custom-criteria-fields-wrapper .edit-custom-criteria-fields-wrapper

				//$output .= '</div></div>';


				//premium plugin feature
				//var_dump('label id='.$label_id);
		        $cbcriteria_msg = '<p style ="margin-left:10px;">'.__('Unlimited criteria in premium version','cbratingsystem').'</p>';
				$cbcriteria_msg = apply_filters('cbratingsystem_add_new_crateria', $cbcriteria_msg,  $label_id);

				$output .= $cbcriteria_msg;

				$output .= ' <div class="description add_left_margin">'.__('Rating Criteria(s) for this <strong>Rating Form</strog>. You can change the Criteria Labal and the Star Label/Hint. To edit Star Label/Hint, click on the default Label/Hint and after chaning click on outside of the field. You must enable/check at least one STAR for every enabled Criteria.', 'cbratingsystem').'</div>';

		$output .= '</div>';

		return $output;
	}


    /**
     *
     */
    public static function admin_ratingForm_listing_html() {
		//echo '<pre>'; print_r($ratingForms); echo '</pre>'; die();

		$adminUrl = admin_url( 'admin.php?page=ratingformedit&id=' );

		if ( ! empty( $_POST ) && check_admin_referer( 'cb_form_delete_nonce_action', 'cb_form_delete_nonce_action' ) ) {
			$post = $_POST;

			if ( ( $post['cbrp-form-delete-by'] == 'selected' ) && ( ! empty( $post['delete_forms'] ) ) ) { // Delete by Selected Items.
				//echo '<pre>'; print_r($post); echo '</pre>'; die();
				//$success = CBRatingSystemData::delete_ratingSummary($post['average_log_ids']);
				asort( $post['delete_forms'] );

				$success = CBRatingSystemData::delete_ratingForm_with_all_ratings( $post['delete_forms'] ); //returns array

				if ( $success ) {
					$message = __( 'Selected Form(s) Deleted', 'cbratingsystem' );
				} else {
					$message = __( 'Failed to Delete Form(s)', 'cbratingsystem' );
				}
			}

			if ( isset( $post['make_form_default'] ) and ( is_numeric( $post['make_form_default'] ) ) ) { // Delete by Selected Items.

				$success = update_option( 'cbratingsystem_defaultratingForm', $post['make_form_default'] );

                if ( $success ) {
					$message = __( 'Form set as default', 'cbratingsystem' );
				} else {
					$message = __( 'Failed to save the form as default', 'cbratingsystem' );
				}
			}
		}//end check if form submitted

        $ratingForms        = CBRatingSystemData::get_ratingForms( true );
        $total_Rating_forms = count($ratingForms);
        //var_dump($total_Rating_forms);
        if ($total_Rating_forms < 1){
            $add_more_cbform = true;
        }
        else{
            $add_more_cbform = apply_filters('cbraing_add_more_forms' , false);
        }



		?>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {


				var options = {};
				options['ajaxurl'] = '<?php echo admin_url('admin-ajax.php'); ?>';

				$("#log-results")
					.tablesorter({
						sortList: [
							[1, 1]
						],
						headers : { 0: { sorter: false } }
					})
					.tablesorterPager({
						container    : jQuery("#rtp-pager-logs"),
						positionFixed: false,
						removeRows   : false,
						output       : '{page} / {totalPages}'
					});

				/** Table Sorter and Pagination for Average Reports **/

				$("#report-result")
					.tablesorter({
						sortList: [
							[6, 1]
						],
						headers : {
							2: { sorter: false },
							3: { sorter: false },
							4: { sorter: false }
							//5: { sorter: false }
						}
					})
					.tablesorterPager({
						container    : jQuery("#rpt-pager-report"),
						positionFixed: false,
						removeRows   : false,
						output       : '{page} / {totalPages}'
					});

				var rtpSearchBy = jQuery('#rtp-search-by');

				rtpSearchBy.change(function () {
					if (rtpSearchBy.val() == 0) {
						jQuery('#search-by').hide(500);
					} else {
						jQuery('#search-by').show(500);
					}
				});

			});
		</script>
		<script type="text/javascript">
			<?php
                echo $js = '
                    var ratingFormEnableDisableStateText = '.json_encode(
                        array(
                            '1_hover'   => __('Disable This', 'cbratingsystem'),
                            '1_normal'  => __('Enabled', 'cbratingsystem'),
                            '0_hover'   => __('Enable This', 'cbratingsystem'),
                            '0_normal'  => __('Disabled', 'cbratingsystem'),
                        )
                    ).';
                ';
            ?>
		</script>


				<?php

                echo '<br/>';
                if($add_more_cbform){
                    echo '<a class="add-new-h2 button button-primary button-large button-addrating" href="'.admin_url('admin.php?page=ratingformedit').'&id=0">'. __('Add New Form','cbratingsystem').'</a>';
                }
                else {
                    //echo __('Unlimited Forms in premium version' , 'cbratingsystem');
                    echo __('Note: Upgrade to Premium Version to add more forms','cbratingsystem');
                }
                ?>



				<div id="message" class="updated"
					<?php
					if ( empty( $message ) ) {
						echo 'style="display:none"';
					}
					?>>
					<p><strong><?php _e( $message, 'cbratingsystem' ); ?></strong></p>
				</div>

						<!-- Starting of Single rating Listing -->
						<div class="cbrp-form-listing">
							<form method="post" action="">
								<div style="clear: both;"></div>
								<div class="stuffbox">
									<table id="log-results" class="widefat tablesorter">
										<thead>
										<tr>
											<th id="cb" class="manage-column column-cb check-column" style="">
												<label class="screen-reader-text" for="cb-select-all">Select All</label>
												<input id="cb-select-all" type="checkbox">
											</th>
											<th width="3%" class="manage-column column-id"><?php _e( 'ID', 'cbratingsystem' ); ?></th>
											<th width="20%" class="manage-column column-title sortable <?php echo ( $_GET['sortby'] == 'time' ) ? $sort : ''; ?>">
												<a href="<?php echo $adminUrl . '&sortby=title&sort=asc'; ?>"><?php _e( 'Title/Name', 'cbratingsystem' ); ?></a>
											</th>
											<th width="10%" class="manage-column column-status sortable <?php echo ( $_GET['sortby'] == 'time' ) ? $sort : ''; ?>">
												<a href="<?php echo $adminUrl . '&sortby=status&sort=asc'; ?>"><?php _e( 'Status', 'cbratingsystem' ); ?></a>
											</th>
											<th width="30%" class="manage-column column-shortatg sortable <?php echo ( $_GET['sortby'] == 'time' ) ? $sort : ''; ?>">
												<a href="<?php echo $adminUrl . '&sortby=shorttag&sort=asc'; ?>"><?php _e( 'Short Code Tag', 'cbratingsystem' ); ?></a>
											</th>
											<th width="30%" class="manage-column column-criteria"><?php _e( 'Criteria(s)', 'cbratingsystem' ); ?></th>
											<th width="12%" class="manage-column column-action"><?php _e( 'Action', 'cbratingsystem' ); ?></th>
										</tr>
										</thead>
										<tbody>
										<?php if ( $ratingForms ) : ?>
											<?php foreach ( $ratingForms as $k => $ratingForm ) :
												$oddEvenClass  = ( $k % 2 ) ? 'odd' : 'even';
												$title         = ( $ratingForm->name ) ? $ratingForm->name : "";
												$status        = ( $ratingForm->is_active == 1 ) ? __('Enabled','cbratingsystem') : __('Disabled','cbratingsystem');
												$ebaleShortTag = ( $ratingForm->enable_shorttag == 1 ) ? sprintf(__('Enabled    [cbratingsystem form_id="%d"]','cbratingsystem'),$ratingForm->id) : __('Disabled','cbratingsystem');
                                                $criteriaHtml  = '';
                                                $criteriaHtml  = ( $criteriaHtml ) ? $criteriaHtml : "No Criteria";

												if ( ! empty( $ratingForm->custom_criteria ) && sizeof($ratingForm->custom_criteria) > 0 ) {
													$criteriaHtml = '<ul>';
													foreach ( $ratingForm->custom_criteria as $criteriaId => $criteria ) {
														if ( $criteria['label'] != '' &&  $criteria['enabled']) {
															//please note that now we save all values, enabled or not, every thing
//															echo '<pre>';
//															print_r($criteria['stars']);
//															echo '</pre>';

															$labels = array();
															foreach($criteria['stars'] as $star){
																if($star['title'] == '' || $star['enabled'] != 1) continue;
																$labels[] = $star['title'];
															}

															//$criteriaHtml .= '<li><strong>' . $criteria['label'] . '</strong> [Stars::  ' . implode( ', ', CBRatingSystemFunctions :: stripslashes_multiarray( $criteria['stars'] ) ) . '  ]' . '</li>';
															$criteriaHtml .= '<li><strong>' . $criteria['label'] . '</strong> [Stars::  ' . implode( ', ', $labels ) . '  ]' . '</li>';
														}
													}
													$criteriaHtml .= '</ul>';
												}  ?>
												<tr class="<?php echo $oddEvenClass; ?> status-publish format-standard hentry category-uncategorized alternate iedit author-self">
													<th scope="row" class="check-column">
														<label class="screen-reader-text" for="cb-select-<?php echo $ratingForm->id; ?>"><?php echo __('Select Sample','cbratingsystem'); ?></label>
														<input id="cb-select-<?php echo $ratingForm->id; ?>" type="checkbox" value="<?php echo $ratingForm->id; ?>" name="delete_forms[]">
													</th>
													<td><?php echo $ratingForm->id; ?></td>
													<td><?php echo CBRatingSystemFunctions ::cb_l( $title, ( $adminUrl . $ratingForm->id ) ); ?></td>
													<td>
                                                        <span data-form-status="<?php echo $ratingForm->is_active; ?>" data-form-id="<?php echo $ratingForm->id; ?>"
															  class="enable_disable_click <?php echo( ( $ratingForm->is_active == 1 ) ? 'disable' : 'enable' ); ?> enable_disable_click_form_<?php echo $ratingForm->id; ?>">
                                                            <?php echo $status; ?>
                                                        </span>
													</td>
													<td><?php echo $ebaleShortTag; ?></td>
													<td><?php echo $criteriaHtml; ?></td>
													<td>
														<?php if ( get_option( 'cbratingsystem_defaultratingForm' ) != $ratingForm->id ): ?>
															<button data-form-id="<?php echo $ratingForm->id; ?>" type="submit" id="edit-submit" class="button button-primary button-large" name="make_form_default" value="<?php echo $ratingForm->id; ?>">Make Default</button>
														<?php else: ?>
															<button data-form-id="<?php echo $ratingForm->id; ?>" disabled id="" class="button-primary button-large button">Default</button>
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php else : echo "<td colspan='7' align='center'><?php _e('No Records Found','cbratingsystem'); ?></td>"; ?>
										<?php endif; ?>
										</tbody>
										<tfoot>
										<tr id="rtp-pager-logs" style="text-align: center;">
											<td colspan="10">
												<img class="first" title="Go To First" src="<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>arrow-stop-180.png" />
												<img class="prev" title="Previous" src="<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>arrow-180.png" />
												<input class="pagedisplay" type="text" readonly="true" />
												<img class="next" title="Next" src="<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>arrow.png" />
												<img class="last" title="Go To Last" src="<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>arrow-stop.png" />
												<input type="hidden" class="pagesize" value="10">
											</td>
										</tr>
										</tfoot>
									</table>
								</div>
								<div id="rtp-log-deletion">
									<h3><?php _e( 'Delete Logs Data' ); ?></h3>
									<table class="widefat">
										<tr>
											<td><strong><?php _e( 'Delete By:' ); ?></strong></td>
											<td>
												<select name="cbrp-form-delete-by">
													<option value="selected"><?php _e( 'Selected Items' ); ?></option>
												</select>
												<input class="button-secondary" name="cbrp-delete-form" type="submit" value="Delete Form" />
												<?php wp_nonce_field( 'cb_form_delete_nonce_action', 'cb_form_delete_nonce_action' ); ?>
											</td>
										</tr>
									</table>
								</div>
								<?php wp_nonce_field( 'cb_form_ajax_nonce_action', 'cb_form_ajax_nonce_action_name' ); ?>
							</form>
						</div>
						<!-- Ending of Single rating Listing -->



	<?php
	}

	/*
     * AJAX Callback
     */
	public static function cbAdminRatingFormListingAjaxFunction() {

		if ( isset( $_POST['cbRatingFormEnableData'] ) and ! empty( $_POST['cbRatingFormEnableData'] ) ) {
			$returnedData = $_POST['cbRatingFormEnableData'];
			//
			if ( wp_verify_nonce( $returnedData['nonce'], 'cb_form_ajax_nonce_action' ) ) {
				//echo '<pre>CBR:'; print_r($returnedData); echo '</pre>'; die();
				if ( ! empty( $returnedData['ratingFormId'] ) and is_numeric( $returnedData['ratingFormId'] ) ) {


					$insertArray['id']        = ( is_numeric( $returnedData['ratingFormId'] ) ) ? $returnedData['ratingFormId'] : 0;
					$insertArray['is_active'] = ( ( $returnedData['is_active'] == 1 ) ) ? 0 : 1;

					$success = CBRatingSystemData::update_ratingForm( $insertArray );

					if ( $success != false ) {
						echo json_encode(
							array(
								'is_active' => ( ( $returnedData['is_active'] == 1 ) ? 0 : 1 ),
								'text'      => ( ( $returnedData['is_active'] == 1 ) ? 'Disabled' : 'Enabled' ),
							)
						);
						die();
					}
				}
			}
			die();
		}
		die();
	}


	/*
     * Returns HTML for "multiple select box of posts"
     * Resource: http://codex.wordpress.org/The_Loop#Loop_Examples
     */
	private static function admin_site_posts_pages_to_selectbox_html() {
		if ( have_posts() ) {
			$post_select_box = $page_select_box = '';

			while ( have_posts() ) :
				/* Intialize the current post */
				the_post();
				$id = the_ID();

				if ( is_single( $id ) ) {
					$post_select_box .= '<option value="' . $id . '__" >' . the_title() . '</option>';
				}

				if ( is_page( $id ) ) {
					$page_select_box .= '<option value="' . $id . '__" >' . the_title() . '</option>';
				}

			endwhile;

			if ( ! empty( $post_select_box ) ) {
				$select_box['post_select_box'] = '<select name="showRatingFormOn[posts][]" >' . $post_select_box . '</select>';
			}

			if ( ! empty( $page_select_box ) ) {
				$select_box['page_select_box'] = '<select name="showRatingFormOn[pages][]" >' . $page_select_box . '</select>';
			}
		} else {
			$select_box = array();
		}

		return $select_box;
	}

	public static function get_next_ratingForm_id() {
		return 0;
	}

}