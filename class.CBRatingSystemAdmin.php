<?php

/**
 * Class CBRatingSystemAdmin
 */
class CBRatingSystemAdmin {

    /**
     * initialize when plugin installed
     */
    public static function init() {

		add_action( 'admin_enqueue_scripts', array( 'CBRatingSystemAdmin', 'admin_load_scripts_and_styles' ) );
		add_action( 'admin_menu', array( 'CBRatingSystemAdmin', 'admin_rating_mainmenu' ) );

		add_filter( "plugin_action_links_" . CB_RATINGSYSTEM_PLUGIN_BASE_NAME, array( 'CBRatingSystemAdmin', 'admin_cbratingsystem_settings_link' ) );

		//widget for linkedinresume
		// include_once 'class.CBRatingSystemWidget.php';
		// add_action( 'widgets_init', array($this,'cb_rating_load_widget' ));
	}

    /**
     * add menu pages
     */
    public static function admin_rating_mainmenu() {

        //var_dump(CB_RATINGSYSTEM_PLUGIN_NAME);
        //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		$ratingactiontop     = add_menu_page( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main', CB_RATINGSYSTEM_PLUGIN_NAME, 'administrator', 'rating', array( 'CBRatingSystemAdminDashboard', 'display_admin_dashboard' ), plugins_url( 'images/admin_icon/rating-home-16.png', __FILE__ ) );
        //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		$ratingactionform    = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Rating Forms', 'cbratingsystem' ), 'administrator', 'ratingform', array( 'CBRatingSystemAdmin', 'admin_ratingForm_listing_page' ) );
		$ratingactionformnew = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Add Rating Form', 'cbratingsystem' ), 'administrator', 'ratingformedit', array( 'CBRatingSystemAdmin', 'admin_ratingForm_listing_page' ) );
		$ratingactionuserlog = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'User Rating Logs', 'cbratingsystem' ), 'administrator', 'rating_reports', array( 'CBRatingSystemAdmin', 'admin_rating_reports_page' ) );
		$ratingactionavglog  = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Rating Average', 'cbratingsystem' ), 'administrator', 'rating_avg_reports', array( 'CBRatingSystemAdmin', 'admin_rating_average_reports_page' ) );
		$ratingactiontheme   = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Theme Settings', 'cbratingsystem' ), 'administrator', 'ratingform_theme', array( 'CBRatingSystemTheme', 'admin_ratingForm_theme_settings' ) );
		$ratingtool          = add_submenu_page( 'rating', __( CB_RATINGSYSTEM_PLUGIN_NAME . ' Main' ), __( 'Tools', 'cbratingsystem' ), 'administrator', 'rating_tool', array( 'CBRatingSystemTool', 'admin_ratingForm_tool_settings' ) );

		add_action( "load-{$ratingactiontop}", array( 'CBRatingSystemAdmin', 'admin_dashboard_page_css_js' ) );
		add_action( "load-{$ratingactiontheme}", array( 'CBRatingSystemAdmin', 'admin_theme_page_css_js' ) );
		add_action( "load-{$ratingactionformnew}", array( 'CBRatingSystemAdmin', 'admin_load_scripts_and_styles' ) );
	}

    /**
     * @param $links
     *
     * @return mixed
     * return rating setting link in backed dashboard
     */
    public static function admin_cbratingsystem_settings_link( $links ) {

		$settings_link = '<a href="admin.php?page=rating">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

    /**
     * admin_load_scripts_and_styles
     */
    public static function admin_load_scripts_and_styles() {

		wp_enqueue_style( 'cbratingsystem_admin_style', CB_RATINGSYSTEM_PLUGIN_DIR . 'css/admin.style.css', array(), CBRatingSystem::$version );
		wp_enqueue_script( 'cbratingsystem_admin_edit_script', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/cbratingsystem.admin.js', array( 'jquery' ), CBRatingSystem::$version );
        wp_enqueue_script( 'cbratingsystem_admin_inline_edit_script', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/jquery.inline-edit.js', array( 'jquery' ), CBRatingSystem::$version );
		wp_enqueue_script( 'jquery-tablesorter-min', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/jquery.tablesorter.min.js', array( 'jquery' ), CBRatingSystem::$version );
		wp_enqueue_script( 'jquery-tablesorter-pager', CB_RATINGSYSTEM_PLUGIN_DIR . 'js/jquery.tablesorter.pager.js', array( 'jquery' ), CBRatingSystem::$version );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
        wp_localize_script(
            'cbratingsystem_admin_edit_script',
            'commentAjax',
            array('ajaxUrl' =>admin_url('admin-ajax.php')) // inside class
        );

		//CBRatingSystemAdmin::admin_dashboard_page_css_js();
		//CBRatingSystemAdmin::admin_theme_page_css_js();
		CBRatingSystem::add_common_styles_scripts();

	}

    /**
     * admin_dashboard_page_css_js
     */
    public static function admin_dashboard_page_css_js() {
		wp_register_style( 'cbratingsystem_admin_dashboard_style', plugins_url( '/css/admin.dashboard.css', __FILE__ ), array(), CBRatingSystem::$version );
		wp_register_script( 'cbratingsystem_admin_dashboard_script', plugins_url( '/js/cbratingsystem.admin.dashboard.js', __FILE__ ), array( 'jquery' ), CBRatingSystem::$version );

		wp_enqueue_style( 'cbratingsystem_admin_dashboard_style' );
		wp_enqueue_script( 'cbratingsystem_admin_dashboard_script' );

		CBRatingSystem::add_common_styles_scripts();
	}

	public static function admin_theme_page_css_js() {
        // this is available in premium version-codeboxr
		//wp_register_script( 'cbratingsystem_admin_theme_script', plugins_url( '/js/cbratingsystem.admin.theme.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), CBRatingSystem::$version );

		wp_enqueue_style( 'wp-color-picker' );
        // this is available in premium version-codeboxr
		//wp_enqueue_script( 'cbratingsystem_admin_theme_script' );

		CBRatingSystem::add_common_styles_scripts();
	}

	/*
     * The landing page for rating system plugin. Here, the page/menu
     * listings and plugin uninstallation process can be found.
     */

	public static function admin_ratingForm_listing_page() {
		$output = '';

		if ( isset( $_POST['form_id'] ) and ( $_POST['form_id'] == 'cb_ratingForm_edit_form' ) ) {

            $ratingForm = self::admin_save_ratingForm( $_POST );

			if ( ( $ratingForm !== false ) and ( ! is_array( $ratingForm ) ) ) {
				if ( $ratingForm != 0 ) {
					$ratingForm  = CBRatingSystemData::get_ratingForm( $ratingForm, true );
				} else {
					//$ratingForm = $ratingForm_id;
				}

				// echo '<pre>'; print_r($ratingForm); echo '</pre>'; //die();

				$output .= '<div class="icon32 icon32_cbrp_admin icon32-cbrp-edit" id="icon32-cbrp-edit"><br></div>
                            <h2>' . __( 'Rating Form Edit /Add', 'cbratingsystem' ) . '</h2>';
				$output .= '<div class="admin_ratingForm_wrapper metabox-holder has-right-sidebar" id="poststuff">';

				$output .= '<div class="messages status">' . __( 'Rating form has been saved successfully', 'cbratingsystem' ) . '</div>';

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
		}

		if ( isset( $_GET['page'] ) and ( $_GET['page'] == 'ratingformedit' ) and ! isset( $_POST['form_id'] ) ) {
			$ratingForm_id = ( isset( $_GET['id'] ) and is_numeric( $_GET['id'] ) ) ? $_GET['id'] : ( ( ( $_GET['id'] == 'add' ) ) ? 0 : 0 );

			if ( ! is_null( $ratingForm_id ) ) {
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
			}
		} else {
			//$ratingForms = CBRatingSystemData::get_ratingForms(true);
			$output .= self::admin_ratingForm_listing_html();
		}

		echo $output;
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
	 * Save a rating form
	 *
	 * @param $post
	 *
	 * @return array|bool
	 */
	private static function admin_save_ratingForm( $post ) {

		global $wpdb;

		if ( ! empty( $post ) && check_admin_referer( 'cb_ratingForm_edit_form_nonce_action', 'cb_ratingForm_edit_form_nonce_field' ) ) {
			//echo '<pre>'; print_r($post['ratingForm']['editor_group']); echo '</pre>'; die();

			if ( isset( $post['ratingForm'] ) and ! empty( $post['ratingForm'] ) ) {
				$path            = admin_url( 'admin.php?page=ratingformedit' );
				$formSavableData = array();
				$ratingFormData  = $post['ratingForm'];

//				echo '<pre>';
//				print_r($ratingFormData);
//				echo '</pre>';


				//echo "<pre>"; print_r($ratingFormData['view_allowed_users']); echo "</pre>";
				$errorHappened = false;
				//$errorMessage = array();
				$affectedFields = array();
				$wp_error       = new WP_Error( 'input_error' );

				//echo '<pre>'; var_dump(  !empty($ratingFormData['allowed_users']) and ($formSavableData['is_active'] != 0) ); echo '</pre>'; die();

				$formSavableData['is_active']           = ( isset( $ratingFormData['is_active'] ) and ( $ratingFormData['is_active'] == 1 ) ) ? 1 : 0;
				$formSavableData['enable_shorttag']     = ( isset( $ratingFormData['enable_shorttag'] ) and ( $ratingFormData['enable_shorttag'] == 1 ) ) ? 1 : 0;
				$formSavableData['show_on_single']      = ( isset( $ratingFormData['show_on_single'] ) and ( $ratingFormData['show_on_single'] == 1 ) ) ? 1 : 0;
				$formSavableData['show_on_home']        = ( isset( $ratingFormData['show_on_home'] ) and ( $ratingFormData['show_on_home'] == 1 ) ) ? 1 : 0;
                $formSavableData['show_on_arcv']        = ( isset( $ratingFormData['show_on_arcv'] ) and ( $ratingFormData['show_on_arcv'] == 1 ) ) ? 1 : 0;
                $formSavableData['email_verify_guest']  = ( isset( $ratingFormData['email_verify_guest'] ) and ( $ratingFormData['email_verify_guest'] == 1 ) ) ? 1 : 0;
				$formSavableData['id']                  = ( isset( $ratingFormData['id'] ) and ( is_numeric( $ratingFormData['id'] ) ) ) ? $ratingFormData['id'] : 0;
				$formSavableData['enable_comment']      = ( isset( $ratingFormData['enable_comment'] ) and ( ( $ratingFormData['enable_comment'] == 1 ) ) ) ? 1 : 0;
				$formSavableData['enable_question']     = ( isset( $ratingFormData['enable_question'] ) and ( ( $ratingFormData['enable_question'] == 1 ) ) ) ? 1 : 0;
                $formSavableData['comment_required']    = ( isset( $ratingFormData['comment_required'] ) and ( ( $ratingFormData['comment_required'] == 1 ) ) ) ? 1 : 0;
                $formSavableData['buddypress_active']   = ( isset( $ratingFormData['buddypress_active'] ) and ( ( $ratingFormData['buddypress_active'] == 1 ) ) ) ? 1 : 0;
                $formSavableData['buddypress_post']     = ( isset( $ratingFormData['buddypress_post'] ) and ( ( $ratingFormData['buddypress_post'] == 1 ) ) ) ? 1 : 0;
				//show_chedit_to_codeboxr
                $formSavableData['show_chedit_to_codeboxr']     = ( isset( $ratingFormData['show_chedit_to_codeboxr'] ) and ( ( $ratingFormData['show_chedit_to_codeboxr'] == 1 ) ) ) ? 1 : 0;
                if ( isset( $ratingFormData['name'] ) and ! empty( $ratingFormData['name'] ) ) {
					$formSavableData['name'] = sanitize_text_field( $ratingFormData['name'] );
				} else {
					$errorHappened  = true;
					$errorText      = '<i>' . sprintf( '<a href="#name">%s</a>', __( 'Title/Name', 'cbratingsystem' ) ) . '</i>' . __( ' field can\'t be left empty.', 'cbratingsystem' );
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'name';
				}

				if ( isset( $ratingFormData['post_types'] ) and ! empty( $ratingFormData['post_types'] ) ) {
					$formSavableData['post_types'] = maybe_serialize( array_values( $ratingFormData['post_types'] ) );
				} else {
					$errorHappened  = true;
					$errorText      = __( "You must select at least one ", 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#post-types">%s</a>', __( 'Post Type', 'cbratingsystem' ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'post_types';
				}

				if ( isset( $ratingFormData['position'] ) and ! empty( $ratingFormData['position'] ) ) {
					$formSavableData['position'] = $ratingFormData['position'];
				} else {
					$errorHappened  = true;
					$errorText      = __( "You must select at least one", 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#position">%s</a>', __( 'Position', 'cbratingsystem' ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'position';
				}

				/*
                 * We won't show error if looging method is left empty. We won't track user.
                 */
				if ( isset( $ratingFormData['logging_method'] ) and ! empty( $ratingFormData['logging_method'] ) ) {
					$formSavableData['logging_method'] = maybe_serialize( array_keys( $ratingFormData['logging_method'] ) );
				} else {
					$errorHappened  = true;
					$errorText      = __( "You must select at least one", 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#logging-method">%s</a>', __( 'Logging Method', cbratingsystem ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'allowed_users';
				}

				if ( isset( $ratingFormData['allowed_users'] ) and ! empty( $ratingFormData['allowed_users'] ) and ( $formSavableData['is_active'] != 0 ) ) {
					$formSavableData['allowed_users'] = maybe_serialize( ( $ratingFormData['allowed_users'] ) );
					//echo "<pre>"; print_r($formSavableData['allowed_users']); echo "</pre>";
				} else {
					$errorHappened  = true;
					$errorText      = __( "You must give access to at least one", 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#allowed-users">%s</a>', __( 'Allowed User Group', 'cbratingsystem' ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'allowed_users';
				}


				if ( isset( $ratingFormData['editor_group'] ) and ! empty( $ratingFormData['editor_group'] ) and ( $formSavableData['is_active'] != 0 ) ) {
					$formSavableData['editor_group'] = $ratingFormData['editor_group'];
				} else {
					$errorHappened  = true;
					$errorText      = __( "You must give access to at least one", 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#editor-group">%s</a>', __( 'Editor User Group', 'cbratingsystem' ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'editor_group';
				}

				if ( isset( $ratingFormData['custom_criteria'] ) and ! empty( $ratingFormData['custom_criteria'] ) ) {
					$labelCOunt    = 0;
					$selectedStars = 0;

					foreach ( $ratingFormData['custom_criteria'] as $firstLevel => $firstLevelArray ) {
						if ( ! empty( $firstLevelArray['label'] ) and ( isset( $firstLevelArray['enabled'] ) and ( $firstLevelArray['enabled'] == 1 ) ) ) {

							$starCount = 0;
							foreach ( $firstLevelArray['stars'] as $secondLevel => $secondLevelArray ) {
								if ( isset( $secondLevelArray['enabled'] ) ) {
									$formSavableData['custom_criteria'][$firstLevel]['label']   = sanitize_text_field( $firstLevelArray['label'] );
									$formSavableData['custom_criteria'][$firstLevel]['enabled'] = 1;

									if ( isset( $secondLevelArray['titleHidden'] ) and ! empty( $secondLevelArray['titleHidden'] ) ) {
										$formSavableData['custom_criteria'][$firstLevel]['stars'][$secondLevel] = sanitize_text_field( $secondLevelArray['titleHidden'] );
									} elseif ( ! empty( $secondLevelArray['title'] ) ) {
										$formSavableData['custom_criteria'][$firstLevel]['stars'][$secondLevel] = sanitize_text_field( $secondLevelArray['title'] );
									}

									$starCount ++;
									$selectedStars ++;
								}
							}
							$labelCOunt ++;
						}
					}

					//echo '<pre>'; print_r($formSavableData['custom_criteria']); echo '</pre>'; die();
					$formSavableData['custom_criteria'] = maybe_serialize( $formSavableData['custom_criteria'] );

					if ( ( $labelCOunt < 1 ) ) {
						$errorHappened  = true;
						$errorText      = __( 'You must enable and name at least one criteria and have to choose at least one star from ', 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#custom-criteria">%s</a>', __( 'Custom Criteria', 'cbratingsystem' ) ) . '</i>';
						$errorMessage[] = $errorText;
						$wp_error->add( 'input_error', $errorText );
						$affectedFields[] = 'custom_criteria';
					}
					if ( ( $selectedStars < 1 ) ) {
						$errorHappened  = true;
						$errorText      = __( 'You must choose at least one STAR from ' ) . '<i>' . sprintf( '<a href="#custom-criteria">%s</a>', __( 'Custom Criteria', 'cbratingsystem' ) ) . '</i> label';
						$errorMessage[] = $errorText;
						$wp_error->add( 'input_error', $errorText );
						$affectedFields[] = 'custom_criteria';
					}

				} else {
					$errorHappened  = true;
					$errorText      = __( 'You must type at least one criteria name/label and have to choose at least one star from ', 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#custom_criteria">%s</a>', __( 'Custom Criteria', 'cbratingsystem' ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'custom_criteria';
				}

				if ( isset( $ratingFormData['comment_limit'] ) and ! empty( $ratingFormData['comment_limit'] ) and is_numeric( $ratingFormData['comment_limit'] ) ) {
					$formSavableData['comment_limit'] = sanitize_text_field( $ratingFormData['comment_limit'] );
				} else {
					$errorHappened  = true;
					$errorText      = '<i>' . __( 'Character limit input', 'cbratingsystem' ) . '</i>' . __( ' field can\'t be left empty at ', 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#enable-comment">%s</a>', __( '"Comment Box Enabling".', 'cbratingsystem' ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'comment_limit';
				}

				if ( isset( $ratingFormData['custom_question'] ) and ! empty( $ratingFormData['custom_question'] ) ) {
					$emptyTitle = 0;
					foreach ( $ratingFormData['custom_question'] as $firstLevel => $firstLevelArray ) {
						if ( ! empty( $firstLevelArray['title'] ) ) {

							if ( isset( $firstLevelArray['enabled'] ) and ( $firstLevelArray['enabled'] == 1 ) ) {
								if ( ! empty( $firstLevelArray['field'] ) and ! empty( $firstLevelArray['field']['type'] ) ) {
									$formSavableData['custom_question']['enabled'][$firstLevel]['title']   = sanitize_text_field( $firstLevelArray['title'] );
									$formSavableData['custom_question']['enabled'][$firstLevel]['enabled'] = 1;
									$formSavableData['custom_question']['enabled'][$firstLevel]['field']   = array();

									//$formSavableData['custom_question']['all'][$firstLevel]['title'] = sanitize_text_field($firstLevelArray['title']);
									//$formSavableData['custom_question']['all'][$firstLevel]['title'] = sanitize_text_field($firstLevelArray['title']);

									if ( isset( $firstLevelArray['required'] ) ) {
										$formSavableData['custom_question']['required'][$firstLevel]            = $firstLevel;
										$formSavableData['custom_question']['enabled'][$firstLevel]['required'] = 1;
									}

									$fieldWithArr = array( 'checkbox', 'radio' );
									$type         = $firstLevelArray['field']['type'];
									if ( in_array( $type, $fieldWithArr ) ) {
										$formSavableData['custom_question']['enabled'][$firstLevel]['field']['type'] = $type;

										foreach ( $firstLevelArray['field'][$type] as $fieldId => $field ) {
											if ( is_numeric( $fieldId ) and ( $firstLevelArray['field'][$type]['count'] > $fieldId ) ) {
												if ( ! empty( $field['text'] ) ) {
													$formSavableData['custom_question']['enabled'][$firstLevel]['field'][$type][$fieldId]['text'] = sanitize_text_field( $field['text'] );
												}

											} elseif ( $fieldId == 'seperated' ) {
												if ( $firstLevelArray['field']['type'] != 'radio' ) {

													if ( ( $field == 1 ) ) {
														$formSavableData['custom_question']['enabled'][$firstLevel]['field'][$type]['seperated'] = 1;
													} else {
														$formSavableData['custom_question']['enabled'][$firstLevel]['field'][$type]['seperated'] = 0;
													}
												}
											} elseif ( $fieldId == 'count' ) {
												$formSavableData['custom_question']['enabled'][$firstLevel]['field'][$type]['count'] = ( $firstLevelArray['field'][$type]['count'] > 0 ) ? $firstLevelArray['field'][$type]['count'] : 1;
											}
										}
									} elseif ( $firstLevelArray['field']['type'] == 'text' ) {
										$formSavableData['custom_question']['enabled'][$firstLevel]['field']['type'] = 'text';
									}
								}
							} else {
								$formSavableData['custom_question']['all'][$firstLevel] = sanitize_text_field( $firstLevelArray['title'] );
							}
						} else {
							$emptyTitle ++;
						}
					}

					//echo '<pre>'; print_r($formSavableData['custom_question']); echo '</pre>'; die();
					$formSavableData['custom_question'] = maybe_serialize( $formSavableData['custom_question'] );

					if ( ( $emptyTitle > 0 ) ) {
						$errorHappened  = true;
						$errorText      = __( 'You can\'t left empty the question field at ', cbratingsystem ) . '<i>' . sprintf( '<a href="#custom-question">%s</a>', __( 'Custom Question', cbratingsystem ) ) . '</i>';
						$errorMessage[] = $errorText;
						$wp_error->add( 'input_error', $errorText );
						$affectedFields[] = 'custom_question';
					}

				}

				if ( isset( $ratingFormData['review'] ) and ! empty( $ratingFormData['review'] ) ) {
					$formSavableData['review']['review_enabled'] = ( isset( $ratingFormData['review']['review_enabled'] ) and ( ( $ratingFormData['review']['review_enabled'] == 1 ) ) ) ? 1 : 0;

					if ( isset( $ratingFormData['review']['review_limit'] ) and ( ! empty( $ratingFormData['review']['review_limit'] ) ) and ( is_numeric( $ratingFormData['review']['review_limit'] ) ) ) {
						$formSavableData['review']['review_limit'] = $ratingFormData['review']['review_limit'];

						$formSavableData['review'] = maybe_serialize( $formSavableData['review'] );
					} else {
						if ( $formSavableData['review']['review_enabled'] == 1 ) {
							$errorHappened  = true;
							$errorText      = __( 'You have entered wrong character at ', cbratingsystem ) . '<i>' . sprintf( '<a href="#review">%s</a>', __( 'Review', cbratingsystem ) ) . '</i>';
							$errorMessage[] = $errorText;
							$wp_error->add( 'input_error', $errorText );
							$affectedFields[] = 'review';
						} else {
							$formSavableData['review'] = maybe_serialize( $formSavableData['review'] );
						}
					}
				}

				$formSavableData['show_on_arcv']          = ( isset( $ratingFormData['show_on_arcv'] ) and ( $ratingFormData['show_on_arcv'] == 1 ) ) ? 1 : 0;
                $formSavableData['email_verify_guest']    = ( isset( $ratingFormData['email_verify_guest'] ) and ( $ratingFormData['email_verify_guest'] == 1 ) ) ? 1 : 0;

				if ( isset( $ratingFormData['view_allowed_users'] ) and ! empty( $ratingFormData['view_allowed_users'] ) and ( $formSavableData['is_active'] != 0 ) ) {
					$formSavableData['view_allowed_users'] = maybe_serialize( ( $ratingFormData['view_allowed_users'] ) );
					//echo "<pre>"; print_r($formSavableData['allowed_users']); echo "</pre>";
				} else {
					$errorHappened  = true;
					$errorText      = __( "You must give access to at least one", 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#view-allowed-users">%s</a>', __( 'View Allowed User Group', cbratingsystem ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'view_allowed_users';
				}
				if ( isset( $ratingFormData['comment_view_allowed_users'] ) and ! empty( $ratingFormData['comment_view_allowed_users'] ) and ( $formSavableData['is_active'] != 0 ) ) {
					$formSavableData['comment_view_allowed_users'] = maybe_serialize( ( $ratingFormData['comment_view_allowed_users'] ) );
					//echo "<pre>"; print_r($formSavableData['allowed_users']); echo "</pre>";
				} else {
					$errorHappened  = true;
					$errorText      = __( "You must give access to at least one", 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#comment_view-allowed-users">%s</a>', __( 'Comment View Allowed User Group', cbratingsystem ) ) . '</i>';
					$errorMessage[] = $errorText;
					$wp_error->add( 'input_error', $errorText );
					$affectedFields[] = 'comment_view_allowed_users';
				}
                if ( isset( $ratingFormData['comment_moderation_users'] ) and ! empty( $ratingFormData['comment_moderation_users'] ) and ( $formSavableData['is_active'] != 0 ) ) {
                    $formSavableData['comment_moderation_users'] = maybe_serialize( ( $ratingFormData['comment_moderation_users'] ) );
                    //echo "<pre>"; print_r($formSavableData['allowed_users']); echo "</pre>";
                } else {
                    $errorHappened  = true;
                    $errorText      = __( "You must give access to at least one", 'cbratingsystem' ) . '<i>' . sprintf( '<a href="#comment_moderation_users">%s</a>', __( 'Comment Moderation User Group', cbratingsystem ) ) . '</i>';
                    $errorMessage[] = $errorText;
                    $wp_error->add( 'input_error', $errorText );
                    $affectedFields[] = 'comment_moderation_users';
                }


				//echo '<pre>'; var_dump($formSavableData); echo '</pre>'; die();
				if ( ! $errorHappened and empty( $errorMessage ) ) {
					if ( class_exists( 'CBRatingSystemData' ) ) {
						$formId = CBRatingSystemData::update_ratingForm( $formSavableData );
						//echo '<pre>'; var_dump($formId); echo '</pre>'; //die();
						if ( $formId !== false ) {

							if ( $post['op'] == __( 'Save configuration and make default', 'cbratingsystem') ) {
								update_option( 'cbratingsystem_defaultratingForm', $formId );
								//var_dump($formId);
								$return = $formId;
							} else {
								if ( CBRatingSystem::can_automatically_make_deafult_form( $formId ) === true ) {
									update_option( 'cbratingsystem_defaultratingForm', $formId );
								}
								//echo 'hello';
								$return = $formId;

							}

							if ( $return !== false ) {

								$table_name = CBRatingSystemData::get_user_ratings_summury_table_name();
								$count      = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $table_name WHERE post_id=%d AND form_id=%d", $rating['post_id'], $formId ) );
							}

							return $return;
						} else {

							$errorHappened  = true;
							$errorText      = apply_filters('cbrating_error', __( 'An error occurred while storing the submitted data. Please ensure that all data are resonable. if problem persist please contact the administrator.', 'cbratingsystem' ));
							$errorMessage[] = $errorText;
							$wp_error->add( 'input_error', $errorText );
							$affectedFields[] = 'overall';
						}
					}
				}

				//echo '<pre>'; var_dump($errorMessage); echo '</pre>'; //die();

				if ( $errorHappened and ! empty( $errorMessage ) ) {
					//$errorMessageHtml = '<ul>' . implode("<li></li>", $errorMessage);
					$trimmedErrorMessage = array_map( 'trim', $errorMessage );
					$errorMessage        = array_filter( $trimmedErrorMessage );

					if ( ! empty( $errorMessage ) ) {
						$errorMessageHtml = '<ul><li>' . implode( "</li><li>", $errorMessage ) . '</li></ul>';

						$errorMessageHtml = '<div class="messages error">' . $errorMessageHtml . '</div>';

						//echo $output;
					}

					return array( 'affectedFields' => $affectedFields, 'formSavableData' => $formSavableData, 'errorMessageText' => $errorMessageHtml, 'errorMessage' => $errorMessage );
				}

			}

		}
	}//end save rating function


	public static function admin_ratingForm_edit_page( $ratingForm ) {
		global $wp_roles;
        $ratingForms            = count(CBRatingSystemData::get_ratingForms(true));
        $addmore_cbratingforms  = ( $ratingForms < 1 ) ? true : apply_filters( 'cbraing_add_more_forms' , false);
		if($addmore_cbratingforms == false && empty($ratingForm)){
            return __('Unlimited Forms in Premium Version' , 'cbratingsystem');
        }
        //echo "<pre>"; echo "Rating Form: ";var_dump($ratingForm); echo "</pre>";
		//if(!empty($ratingForm)) {
		$post 					= $_POST['ratingForm'];

		//var_dump($post['id'] );
		//echo '<pre>'; print_r($post); echo '</pre>'; //die();
		//echo '<pre>'; var_dump(($post)? (($post['allowed_users']['registered']==1) ? '1checked ' : '') : ((isset($ratingForm->allowed_users['registered'])) ? (in_array('registered', $ratingForm->allowed_users)? '2checked ' : '') : '3checked ')); echo '</pre>'; die();
		$output = '';

		$postTypes = CBRatingSystem::post_types();

		$userRoles = CBRatingSystem::user_roles();

		$editorUserRoles = CBRatingSystem::editor_user_roles();
		//echo '<pre>'; print_r($userRoles); echo '</pre>'; die();
		$output .= '

            <div id="post-body">
            <div id="post-body-content">
            <div class="cb-ratingForm-edit postbox">
                <h3>'.__(" Edit/Add rating form","cbratingsystem").'</h3>
                <div class="inside">
                <form action="" method="post" id="cb-ratingForm-edit-form" accept-charset="UTF-8">
                    <div>
                        <fieldset class="collapsible form-wrapper collapse-processed">
                            <legend>
                                <span class="fieldset-legend">
                                    Configuration
                                </span>
                            </legend>
                            <div class="fieldset-wrapper">
                                <div class="form-item form-type-checkbox form-item-enable" id="is-active">
                                    <input type="checkbox" id="edit-enable" name="ratingForm[is_active]" value="1" ' . ( ( $post['is_active'] ) ? ( ( $post['is_active'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->is_active ) ? 'checked ' : ( ( $ratingForm->is_active == '' || $ratingForm->is_active == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-enable">Enable this rating form.</label>
                                    <div class="description add_left_margin">Enabled by default.</div>
                                </div>
                                <div class="form-item form-type-checkbox form-item-enable-shorttag" id="enable-shorttag">
                                    <input type="checkbox" id="edit-enable-shorttag" name="ratingForm[enable_shorttag]" value="1" ' . ( ( $post['enable_shorttag'] ) ? ( ( $post['enable_shorttag'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->enable_shorttag ) ? 'checked ' : ( ( $ratingForm->enable_shorttag == '' || $ratingForm->enable_shorttag == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox add_left_margin">
                                    <label class="option" for="edit-enable-shorttag">Enable Shortcode for this form</label>
                                    <div class="description add_left_margin"><i>Shortcode Example</i>: <code>[cbratingsystem form_id=' . ( ! empty( $post['id'] ) ? $post['id'] : ( isset( $ratingForm->id ) ? $ratingForm->id : 'ID-OF-THIS-RATING-FORM' ) ) . ' post_id=POST_ID theme_key=THEME_KEY]</code></div>
                               		<div class="description add_left_margin">POST_ID is the id of the post where you are adding this shortcode /the post id from where you want to grab the form and reviews .</div>
							    </div>

                                <div class="form-item form-type-textfield form-item-name" id="name">
                                    <label for="edit-name">Title/Name <span class="form-required" title="This field is required.">*</span></label>
                                    <input type="text" id="edit-name" name="ratingForm[name]" value="' . ( ( $post['name'] ) ? ( sanitize_text_field( $post['name'] ) ? sanitize_text_field( $post['name'] ) : __( 'Sample rating form', cbratingsystem ) ) : ( sanitize_text_field( $ratingForm->name ) ? sanitize_text_field( $ratingForm->name ) : __( 'Sample rating form', cbratingsystem ) ) ) . '" size="60" maxlength="128" class="form-text required add_left_margin">
                                    <div class="description add_left_margin">Title/Name for this Rating Form</div>
                                </div>

                                <div class="form-item form-type-select form-item-post-type" id="post-types">
                                    <div><label for="edit-post-type">Post Type Selection<span class="form-required" title="This field is required.">*</span></label></div>
                                    <select id="edit-post-type" multiple="yes" data-placeholder="Choose post type(s)..." name="ratingForm[post_types][]" class="form-select form-item-select-post-type required add_left_margin">

            ';

		if ( ! empty( $postTypes ) ) {

			foreach ( $postTypes as $postType ) {
				if ( ! empty( $postType['types'] ) ) {

					$output .= '<optgroup label="' . $postType['label'] . '">';

					foreach ( $postType['types'] as $type => $typeLabel ) {
						if ( is_array( $post['post_types'] ) and ! empty( $post['post_types'] ) ) {
							if ( in_array( $type, $post['post_types'] ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel . '</option>';
							} else {
								if ( is_array( $ratingForm->post_types ) and ! empty( $ratingForm->post_types ) ) {
									if ( in_array( $type, $ratingForm->post_types ) ) {
										$output .= '<option selected value="' . $type . '">' . $typeLabel . '</option>';
									} else {
										$output .= '<option value="' . $type . '">' . $typeLabel . '</option>';
									}
								} else {
									$output .= '<option value="' . $type . '">' . $typeLabel . '</option>';
								}
							}
						} else {
							if ( ! empty( $ratingForm->post_types ) ) {
								$ratingForm->post_types = maybe_unserialize( $ratingForm->post_types );
							}

							if ( is_array( $ratingForm->post_types ) and ! empty( $ratingForm->post_types ) ) {
								if ( in_array( $type, $ratingForm->post_types ) ) {
									$output .= '<option selected value="' . $type . '">' . $typeLabel . '</option>';
								} else {
									$output .= '<option value="' . $type . '">' . $typeLabel . '</option>';
								}
							} else {
								$output .= '<option value="' . $type . '">' . $typeLabel . '</option>';
							}
						}
					}

					$output .= '</optgroup>';
				}
			}
		}

		$output .= '
                                    </select>
                                    <div class="description add_left_margin">Select post types where you want to show this rating form. Hold down <i>Ctrl</i> key to select multiple.</div>
                                </div>

                                <div class="form-item form-type-checkbox form-item-single" id="show-on-single">
                                    <input type="checkbox" id="edit-single" name="ratingForm[show_on_single]" value="1" ' . ( ( $post['show_on_single'] ) ? ( ( $post['show_on_single'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->show_on_single ) ? 'checked ' : ( ( $ratingForm->show_on_single == '' || $ratingForm->show_on_single == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-single">Show on single post/page.</label>
                                    <div class="description add_left_margin">Enabled by default.</div>
                                </div>
                                <div class="form-item form-type-checkbox form-item-home" id="show-on-home">
                                    <input type="checkbox" id="edit-home" name="ratingForm[show_on_home]" value="1" ' . ( ( $post['show_on_home'] ) ? ( ( $post['show_on_home'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->show_on_home ) ? 'checked ' : ( ( $ratingForm->show_on_home == '' || $ratingForm->show_on_home == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-home">Show on home page.</label>
                                    <div class="description add_left_margin">If you are listing or displaying any post/page at your site home page then for every teaser view of the posts/pages a simple view of the form will be displayed, otherwise the normal view of the form. Enabled by default.</div>
                                </div>
								<div class="form-item form-type-checkbox form-item-arcv" id="show-on-arcv">
                                    <input type="checkbox" id="edit-arcv" name="ratingForm[show_on_arcv]" value="1" ' . ( ( $post['show_on_arcv'] ) ? ( ( $post['show_on_arcv'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->show_on_arcv ) ? 'checked ' : ( ( $ratingForm->show_on_arcv == '' || $ratingForm->show_on_arcv == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-arcv">Show on archive page.</label>
                                    <div class="description add_left_margin">If you are listing or displaying any post/page at your site archive page then for every teaser view of the posts/pages a simple view of the form will be displayed, otherwise the normal view of the form. Enabled by default.</div>
                                </div>
                                <div class="form-item form-type-checkbox form-item-email_verify_guest" id="email_verify_guest">
                                    <input type="checkbox" id="edit-email_verify_guest" name="ratingForm[email_verify_guest]" value="1" ' . ( ( $post['email_verify_guest'] ) ? ( ( $post['email_verify_guest'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->email_verify_guest ) ? 'checked ' : ( ( $ratingForm->email_verify_guest == '' || $ratingForm->email_verify_guest == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-email_verify_guest">Guest Email Verify</label>
                                    <div class="description add_left_margin">Enable/disable email verification for guest.
                                    '.apply_filters("cbratingsystem_buddypress_description"," [This is for premium version only] ").'</div>
                                </div>
                                 <div class="form-item form-type-checkbox form-item-buddypress_active" id="buddypress_active">
                                    <input type="checkbox" id="edit-buddypress_active" name="ratingForm[buddypress_active]" value="1" ' . ( ( $post['buddypress_active'] ) ? ( ( $post['buddypress_active'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->buddypress_active ) ? 'checked ' : ( ( $ratingForm->buddypress_active == '' || $ratingForm->buddypress_active == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-buddypress_active">Buddypress author profile integration</label>
                                    <div class="description add_left_margin">This will set comment author link to buddy press author link if buddy press on
                                    '.apply_filters("cbratingsystem_buddypress_description"," [This is for premium version only] ").'</div>
                                </div>


                                <div class="form-item form-type-checkbox form-item-buddypress_post" id="buddypress_post">
                                    <input type="checkbox" id="edit-buddypress_post" name="ratingForm[buddypress_post]" value="1" ' . ( ( $post['buddypress_post'] ) ? ( ( $post['buddypress_post'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->buddypress_post ) ? 'checked ' : ( ( $ratingForm->buddypress_post == '' || $ratingForm->buddypress_post == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-buddypress_post">Buddypress post after comment</label>
                                    <div class="description add_left_margin">This will post comment in your buddypress profile if buddypress on
                                     '.apply_filters("cbratingsystem_buddypress_description"," [This is for premium version only] ").'</div>
                                </div>
                                 <div class="form-item form-type-checkbox form-item-show_chedit_to_codeboxr" id="show_chedit_to_codeboxr">
                                    <input type="checkbox" id="edit-show_chedit_to_codeboxr" name="ratingForm[show_chedit_to_codeboxr]" value="1" ' . ( ( $post['show_chedit_to_codeboxr'] ) ? ( ( $post['show_chedit_to_codeboxr'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->show_chedit_to_codeboxr ) ? 'checked ' : ( ( $ratingForm->show_chedit_to_codeboxr == '' || $ratingForm->show_chedit_to_codeboxr == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-show_chedit_to_codeboxr">Show Credit to codeboxr</label>
                                    <div class="description add_left_margin">This will show credit to codeboxr</div>
                                </div>
                                <div class="form-item form-type-radios form-item-position" id="position">
                                    <label for="edit-position">Position <span class="form-required" title="This field is required.">*</span></label>
                                    <div id="edit-position" class="form-radios">
                                        <div class="form-item form-type-radio form-item-position-top add_left_margin">
                                            <input type="radio" id="edit-position-top" name="ratingForm[position]" value="top" ' . ( ( $post['position'] ) ? ( ( $post['position'] == 'top' ) ? 'checked ' : '' ) : ( ( $ratingForm->position == 'top' ) ? 'checked ' : '' ) ) . 'class="form-checkbox">
                                            <label class="option" for="edit-position-top">Top</label>
                                        </div>
                                        <div class="form-item form-type-radio form-item-position-bottom add_left_margin">
                                            <input type="radio" id="edit-position-bottom" name="ratingForm[position]" value="bottom" ' . ( isset( $post['position'] ) ? ( ( $post['position'] == 'bottom' ) ? 'checked ' : '' ) : ( isset( $ratingForm->position ) ? ( ( $ratingForm->position == 'bottom' ) ? 'checked ' : '' ) : 'checked ' ) ) . 'class="form-checkbox">
                                            <label class="option" for="edit-position-bottom">Bottom</label>
                                        </div>
                                        <div class="form-item form-type-radio form-item-position-none add_left_margin">
                                            <input type="radio" id="edit-position-none" name="ratingForm[position]" value="none" ' . ( isset( $post['position'] ) ? ( ( $post['position'] == 'none' ) ? 'checked ' : '' ) : ( isset( $ratingForm->position ) ? ( ( $ratingForm->position == 'none' ) ? 'checked ' : '' ) : '' ) ) . 'class="form-checkbox">
                                            <label class="option" for="edit-position-none">None</label>
                                        </div>
                                    </div>
                                    <div class="description add_left_margin">Position to show this form. <i>Top</i>: at Content top, <i>Bottom</i>: at Content bottom. If <i>None</i> is chosen, then this form will only be available/displayed through <i><a href="#enable-shorttag">Shorttag</a></i>.</div>
                                </div>
                                <div class="form-item form-type-checkboxes form-item-logging-method" id="logging-method">
                                    <label for="edit-logging-method">Logging Method <span class="form-required" title="This field is required.">*</span></label>
                                    <div id="edit-logging-method" class="form-checkboxes">
                                        <div class="form-item form-type-checkbox form-item-logging-method-top add_left_margin">
                                            <input type="checkbox" id="edit-logging-method-cookie" name="ratingForm[logging_method][cookie]" value="1" ' . ( ( $post ) ? ( ( $post['logging_method']['cookie'] == 1 ) ? 'checked ' : '' ) : ( ( isset( $ratingForm->logging_method ) ) ? ( in_array( 'cookie', $ratingForm->logging_method ) ? 'checked ' : '' ) : 'checked ' ) ) . 'class="form-checkbox">
                                            <label class="option" for="edit-logging-method-cookie">Cookie</label>
                                        </div>
                                        <div class="form-item form-type-checkbox form-item-logging-method-bottom add_left_margin">
                                            <input type="checkbox" id="edit-logging-method-ip" name="ratingForm[logging_method][ip]" value="1" ' . ( ( $post ) ? ( ( $post['logging_method']['ip'] == 1 ) ? 'checked ' : '' ) : ( ( isset( $ratingForm->logging_method ) ) ? ( in_array( 'ip', $ratingForm->logging_method ) ? 'checked ' : '' ) : 'checked ' ) ) . 'class="form-checkbox">
                                            <label class="option" for="edit-logging-method-ip">IP</label>
                                        </div>
                                    </div>
                                    <div class="description add_left_margin">What types of user\'s information <i>"Rating Form"</i> will store.</div>
                                </div>
	                                <div class="form-item form-type-checkboxes form-item-allowed-users" id="allowed-users">
	                                    <label for="edit-allowed-users">Allowed User Roles Who Can Rate<span class="form-required" title="This field is required.">*</span></label>
	                                    <select id="edit-allowed-users" multiple="yes"  name="ratingForm[allowed_users][]" class="form-select form-item-select-post-type required add_left_margin">
									
									            ';
		//echo '<pre>'; var_dump($ratingForm->allowed_users); echo '</pre>'; //die();
		if ( ! empty( $userRoles ) ) {

			foreach ( $userRoles as $optGrp => $options ) {
				if ( ! empty( $options ) ) {

					$output .= '<optgroup label="' . $optGrp . '">';

					foreach ( $options as $type => $typeLabel ) {
						if ( is_array( $post['allowed_users'] ) and ! empty( $post['allowed_users'] ) ) {

							if ( in_array( $type, $post['allowed_users'] ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
							} else {
								$output .= '<option value="' . $type . '">' . $typeLabel['name'] . '</option>';
							}
						} else {

							if ( ! empty( $ratingForm->allowed_users ) && in_array( $type, $ratingForm->allowed_users ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
							} else {
								//var_dump($type);
								if ( $type == 'administrator' || $type == 'editor' ) {
									$select = 'selected';
									//var_dump($select);
								} else {
									$select = '';
								}
								$output .= '<option ' . $select . ' value="' . $type . '">' . $typeLabel['name'] . '</option>';
							}
						}
					}

					$output .= '</optgroup>';
				}
			}
		}

		$output .= '
	                                    </select>
	                                    <div class="description add_left_margin">What user group can rate your article with this <i>"Rating Form"</i>.</div>
	                                </div>
	                               
	                               <div class="form-item form-type-checkboxes form-item-view-allowed-users" id="view-allowed-users">
	                                    <label for="edit-view-allowed-users">Allowed User Roles Who Can View Rating <span class="form-required" title="This field is required.">*</span></label>
	                                    <select id="edit-view-allowed-users" multiple="yes" data-placeholder="Choose user role(s)..." name="ratingForm[view_allowed_users][]" class="form-select form-item-select-post-type required add_left_margin">
									
									            ';
		//echo '<pre>'; var_dump($ratingForm->allowed_users); echo '</pre>'; //die();
		if ( ! empty( $userRoles ) ) {

			foreach ( $userRoles as $optGrp => $options ) {
				if ( ! empty( $options ) ) {

					$output .= '<optgroup label="' . $optGrp . '">';

					foreach ( $options as $type => $typeLabel ) {
						if ( is_array( $post['view_allowed_users'] ) and ! empty( $post['view_allowed_users'] ) ) {
							if ( in_array( $type, $post['view_allowed_users'] ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
							} else {
								$output .= '<option value="' . $type . '">' . $typeLabel['name'] . '</option>';
							}
						} else {
							if ( ! empty( $ratingForm->view_allowed_users ) && in_array( $type, $ratingForm->view_allowed_users ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
							} else {
								if ( $type == 'administrator' || $type == 'editor' || $type == 'guest' ) {
									$select = 'selected';
									//var_dump($select);
								} else {
									$select = '';
								}
								$output .= '<option ' . $select . ' value="' . $type . '">' . $typeLabel['name'] . '</option>';
							}
						}
					}

					$output .= '</optgroup>';
				}
			}
		}

		$output .= '
	                                    </select>
	                                    <div class="description add_left_margin">What user group can view rating.</div>
	                                </div> 
								   
								   
								    
								 <div class="form-item form-type-checkboxes form-item-comment-view-allowed-users" id="comment-view-allowed-users">
	                                    <label for="edit-comment-view-allowed-users">Allowed User Roles Who Can View Rating Review<span class="form-required" title="This field is required.">*</span></label>
	                                    <select id="edit-comment-view-allowed-users" multiple="yes" data-placeholder="Choose user role(s)..." name="ratingForm[comment_view_allowed_users][]" class="form-select form-item-select-post-type required add_left_margin">
									
									            ';
		//echo '<pre>'; var_dump($ratingForm->allowed_users); echo '</pre>'; //die();
		if ( ! empty( $userRoles ) ) {

			foreach ( $userRoles as $optGrp => $options ) {
				if ( ! empty( $options ) ) {

					$output .= '<optgroup label="' . $optGrp . '">';

					foreach ( $options as $type => $typeLabel ) {
						if ( is_array( $post['comment_view_allowed_users'] ) and ! empty( $post['comment_view_allowed_users'] ) ) {
							if ( in_array( $type, $post['comment_view_allowed_users'] ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
							} else {
								$output .= '<option value="' . $type . '">' . $typeLabel['name'] . '</option>';
							}
						} else {
							if ( ! empty( $ratingForm->comment_view_allowed_users ) && in_array( $type, $ratingForm->comment_view_allowed_users ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
							} else {
								if ( $type == 'administrator' || $type == 'editor' || $type == 'guest') {
									$select = 'selected';
									//var_dump($select);
								} else {
									$select = '';
								}
								$output .= '<option ' . $select . ' value="' . $type . '">' . $typeLabel['name'] . '</option>';
							}
						}
					}

					$output .= '</optgroup>';
				}
			}
		}

		$output .= '
	                                    </select>
	                                    <div class="description add_left_margin">What user group can view rating reviews.</div>
	                                </div> 
								   
								   
								   <!--- view moderation group -->
								   	 <div class="form-item form-type-checkboxes form-item-comment-moderation-users" id="comment-moderation-users">
	                                    <label for="edit-comment-moderation-users">Enable rating moderation for group<span class="form-required" title="This field is required.">*</span></label>
	                                    <select id="edit-comment-moderation-users" multiple="yes" data-placeholder="Choose user role(s)..." name="ratingForm[comment_moderation_users][]" class="form-select form-item-select-post-type required add_left_margin">

									            ';
                                                         //echo '<pre>'; var_dump($ratingForm->allowed_users); echo '</pre>'; //die();
                                                        if ( ! empty( $userRoles ) ) {

                                                            foreach ( $userRoles as $optGrp => $options ) {
                                                                if ( ! empty( $options ) ) {

                                                                    $output .= '<optgroup label="' . $optGrp . '">';

                                                                    foreach ( $options as $type => $typeLabel ) {
                                                                        if ( is_array( $post['comment_moderation_users'] ) and ! empty( $post['comment_moderation_users'] ) ) {
                                                                            if ( in_array( $type, $post['comment_moderation_users'] ) ) {
                                                                                $output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
                                                                            } else {
                                                                                $output .= '<option value="' . $type . '">' . $typeLabel['name'] . '</option>';
                                                                            }
                                                                        } else {
                                                                            if ( ! empty( $ratingForm->comment_moderation_users ) && in_array( $type, $ratingForm->comment_moderation_users ) ) {
                                                                                $output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
                                                                            } else {
                                                                                if (  $type == 'guest') {
                                                                                    $select = 'selected';
                                                                                    //var_dump($select);
                                                                                } else {
                                                                                    $select = '';
                                                                                }
                                                                                $output .= '<option ' . $select . ' value="' . $type . '">' . $typeLabel['name'] . '</option>';
                                                                            }
                                                                        }
                                                                    }

                                                                    $output .= '</optgroup>';
                                                                }
                                                            }
                                                        }

                                                        $output .= '
                                                    </select>
	                                    <div class="description add_left_margin">What user groups comments will be riviewed.</div>
	                                </div>
								    <!--comment moderation group  -->
                                
								
                                <div class="form-item form-type-checkboxes form-item-editor-group" id="editor-group">
                                    <label for="edit-editor-group">Rating Editor User Group <span class="form-required" title="This field is required.">*</span></label>
                                    <select id="edit-editor-group" data-placeholder="Choose user role(s)..." name="ratingForm[editor_group]" class="form-select form-item-select-post-type required add_left_margin">

            ';
		//echo '<pre>'; var_dump($post['editor_group']); echo '</pre>'; //die();
		//echo '<pre>'; var_dump($ratingForm->editor_group); echo '</pre>'; die();
		if ( ! empty( $editorUserRoles ) ) {

			foreach ( $editorUserRoles as $optGrp => $options ) {
				if ( ! empty( $options ) ) {

					$output .= '<optgroup label="' . $optGrp . '">';

					foreach ( $options as $type => $typeLabel ) {
						if ( ! empty( $post['editor_group'] ) ) {
							if ( ( $type == $post['editor_group'] ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
							} else {
								$output .= '<option value="' . $type . '">' . $typeLabel['name'] . '</option>';
							}
						} else {
							if ( ! empty( $ratingForm->editor_group ) && ( $type == $ratingForm->editor_group ) ) {
								$output .= '<option selected value="' . $type . '">' . $typeLabel['name'] . '</option>';
							} else {
								$output .= '<option value="' . $type . '">' . $typeLabel['name'] . '</option>';
							}
						}
					}

					$output .= '</optgroup>';
				}
			}
		}

		$output .= '
                                    </select>
                                    <!--div id="edit-allowed-users" class="form-checkboxes">
                                        <div class="form-item form-type-checkbox form-item-allowed-users-top add_left_margin">
                                            <input type="checkbox" id="edit-allowed-users-registered" name="ratingForm[allowed_users][registered]" value="1" ' . ( ( $post ) ? ( ( $post['allowed_users']['registered'] == 1 ) ? 'checked ' : '' ) : ( ( isset( $ratingForm->allowed_users['registered'] ) ) ? ( in_array( 'registered', $ratingForm->allowed_users ) ? 'checked ' : '' ) : 'checked ' ) ) . 'class="form-checkbox">
                                            <label class="option" for="edit-allowed-users-registered">Registered user</label>
                                        </div>
                                        <div class="form-item form-type-checkbox form-item-allowed-users-bottom add_left_margin">
                                            <input type="checkbox" id="edit-allowed-users-guest" name="ratingForm[allowed_users][guest]" value="1" ' . ( ( $post ) ? ( ( $post['allowed_users']['guest'] == 1 ) ? 'checked ' : '' ) : ( ( isset( $ratingForm->allowed_users['guest'] ) ) ? ( in_array( 'guest', $ratingForm->allowed_users ) ? 'checked ' : '' ) : 'checked ' ) ) . 'class="form-checkbox">
                                            <label class="option" for="edit-allowed-users-guest">Guest</label>
                                        </div>
                                    </div-->
                                    <div class="description add_left_margin">What user group\'s rate will be count as editor rating.</div>
                                </div>

                                ';
		$output .= self::admin_ratingForm_edit_page_custom_label( $ratingForm );
		$output .=
			'
                                <div class="form-item form-type-checkbox form-item-comment-enabled" id="enable-comment">
                                    <input type="checkbox" id="edit-comment-enabled" name="ratingForm[enable_comment]" value="1" ' . ( ( $post ) ? ( ( $post['enable_comment'] == 1 ) ? 'checked ' : '' ) : ( ( isset( $ratingForm->enable_comment ) ) ? ( ( $ratingForm->enable_comment == 1 ) ? 'checked ' : '' ) : 'checked ' ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-comment-enabled">Enable comment box with character limit: </label>
                                    <input type="text" size="10" name="ratingForm[comment_limit]" class="form-text" value="' . ( ( $post['comment_limit'] ) ? ( is_numeric( $post['comment_limit'] ) ? $post['comment_limit'] : 0 ) : ( ( isset( $ratingForm->comment_limit ) ) ? ( is_numeric( $ratingForm->comment_limit ) ? $ratingForm->comment_limit : 0 ) : 200 ) ) . '" />
                                </div>
                                <div class="form-item form-type-checkbox form-comment-required" id="comment-required-div">
                                    <input type="checkbox" id="comment-required" name="ratingForm[comment_required]" value="1" ' . ( ( $post['comment_required'] ) ? ( ( $post['comment_required'] == 1 ) ? 'checked ' : '' ) : ( ( $ratingForm->comment_required ) ? 'checked ' : ( ( $ratingForm->comment_required == '' || $ratingForm->comment_required == '0' ) ? '' : 'checked ' ) ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-home">Comment required</label>
                                    <div class="description add_left_margin">If comment is required</div>
                                </div>

                                ' . ( CBRatingSystemAdminFormParts::admin_ratingForm_edit_page_custom_question( $ratingForm ) ) . '

                                <div class="form-item form-type-checkbox form-item-review-enabled" id="review">
                                    <input type="checkbox" id="edit-review-enabled" name="ratingForm[review][review_enabled]" value="1" ' . ( ( $post['review'] ) ? ( ( $post['review']['review_enabled'] == 1 ) ? 'checked ' : '' ) : ( ( isset( $ratingForm->review['review_enabled'] ) ) ? ( ( $ratingForm->review['review_enabled'] == 1 ) ? 'checked ' : '' ) : 'checked ' ) ) . 'class="form-checkbox">
                                    <label class="option" for="edit-review-enabled">Display user reviews at front-end with "first load display" limit: </label>
                                    <input type="text" size="10" name="ratingForm[review][review_limit]" class="form-text" value="' . ( ( $post['review']['review_limit'] ) ? ( is_numeric( $post['review']['review_limit'] ) ? $post['review']['review_limit'] : 10 ) : ( ( isset( $ratingForm->review['review_limit'] ) ) ? ( is_numeric( $ratingForm->review['review_limit'] ) ? $ratingForm->review['review_limit'] : 10 ) : 10 ) ) . '" />
                                </div>
                            </div>
                        </fieldset>

                        <input type="hidden" name="form_id" value="cb_ratingForm_edit_form">
                        <input type="hidden" name="ratingForm[id]" value="' . ( ( isset( $post['id'] ) && $post['id'] != 0 ) ? $post['id'] : ( ( $ratingForm->id ) ? $ratingForm->id : 0 ) ) . '">
                        ' . wp_nonce_field( 'cb_ratingForm_edit_form_nonce_action', 'cb_ratingForm_edit_form_nonce_field' ) . '
                        <div class="form-actions form-wrapper" id="edit-actions">
                            <input type="submit" id="edit-submit" class="button button-primary button-large" name="op" value="Save configuration" class="form-submit">
                            <input type="submit" id="edit-submit-default" name="op" class="button button-primary button-large" value="Save configuration and make default" class="form-submit">
                        </div>
                    </div>
                </form>
                </div>
            </div>
            </div>
            </div>
            ';

		return $output;
		//}
	}

	public function admin_ratingForm_edit_page_custom_label( $RFSettings = array() ) {

		$customCriteria = ( $RFSettings->custom_criteria ) ? $RFSettings->custom_criteria : array();
		//$customCriteria = !empty($_POST['ratingForm']['custom_criteria']) ? $_POST['ratingForm']['custom_criteria'] : $customCriteria;
		$postCustomCriteria = $_POST['ratingForm']['custom_criteria'];
		$displayItemCount   = 0;
		$output = '';
		//$output .= '
		$output .=
			'<div class="cb-ratingForm-edit-custom-criteria-container form-item" id="custom-criteria">
            <label class="" for="edit-custom-criteria">Custom Criteria <span class="form-required" title="This field is required.">*</span></label>
            <div class="edit-custom-criteria-fields-wrapper">
            ';

		if ( ! empty( $postCustomCriteria ) ) {
			foreach ( $postCustomCriteria as $label_id => $labelArr ) {
				if ( ! empty( $labelArr['label'] ) and ( $labelArr['enabled'] == 1 ) ) {
					$displayItemCount ++;
				}
			}
		} else {
			foreach ( $customCriteria as $label_id => $labelArr ) {
				if ( ! empty( $labelArr['label'] ) and ( $labelArr['enabled'] == 1 ) ) {
					$displayItemCount ++;
				}
			}
		}

		$displayItemCount = ( $displayItemCount - 2 );
        $label_indexes = array();

        if(is_array($customCriteria) && empty($customCriteria)){
            $count_label = 3;
        }
        else{
            foreach($customCriteria as $index=>$customCriteria_one){
                array_push($label_indexes,$index);

            }
            $count_label = max($label_indexes)+1;
            $count_label = $count_label <3?  3:$count_label;
        }
		for ( $label_id = 0; $label_id <$count_label; $label_id ++ ) {
			if ( $label_id == 0 ) {
				$class = 'first';
			} elseif ( $label_id == $count_label -1) {
				$class = 'last';
			} else {
				$class = 'other';
			}

			//$displayItem = ( isset($postCustomCriteria[$label_id]['label'])? ( !empty($postCustomCriteria[$label_id]['label'])? ' display_item':'') : (!empty($customCriteria[$label_id]['label']) ? ' display_item' : '') );
			$displayItem = ' display_item';

			$output .=
				'<div class="custom-criteria-wrapper custom-criteris-wrapper-' . $class . ' custom-criteria-wrapper-label-id-' . $label_id . $displayItem . '">
                    <input type="checkbox" name="ratingForm[custom_criteria][' . $label_id . '][enabled]" value="1" class="custom-criteria-enable-checkbox edit-custom-criteria-enable-checkbox-label-' . $label_id . '"
                         ' . ( ( isset( $postCustomCriteria[$label_id]['enabled'] ) and ( ! empty( $postCustomCriteria[$label_id]['label'] ) ) ) ? ( ( $postCustomCriteria[$label_id]['enabled'] == 1 ) ? 'checked ' : '' ) : ( isset( $customCriteria[$label_id]['enabled'] ) ? 'checked ' : '' ) ) . ' />
                    <div data-label-id="' . $label_id . '" class="add_left_margin form-type-textfield form-item-custom-criteria-label label-id-' . $label_id . ' ' . $class . $displayItem . '">
                        <input type="text" id="edit-custom-criteria-label-' . $label_id . '"
                            name="ratingForm[custom_criteria][' . $label_id . '][label]" ' . ( isset( $postCustomCriteria[$label_id]['label'] ) ? ( ! empty( $postCustomCriteria[$label_id]['label'] ) ? 'value="' . stripslashes( $postCustomCriteria[$label_id]['label'] ) . '" class="form-text display_item" ' : '' ) : ( isset( $customCriteria[$label_id]['label'] ) ? 'value="' . stripslashes( $customCriteria[$label_id]['label'] ) . '" class="form-text display_item" ' : 'value="Criteria ' . ( $label_id + 1 ) . '" class="form-text"' ) ) . '>
                    </div>
                    <div class="add_left_margin form-type-checkboxes form-item-custom-criteria-stars label-star-id-' . $label_id . $displayItem . '">
                        <div id="edit-custom-criteria-checkboxes" class="form-checkboxes">
            ';
          //
            if($customCriteria[$label_id] != NULL){
                $star_count  = max(array_keys($customCriteria[$label_id]['stars']));
                $star_count = $star_count>=5 ? $star_count+1 : 5;
               // echo '<pre>';  var_dump($star_count);echo '</pre>'; die();
            }
            else{
                $star_count  = 5;
            }

			for ( $star_id = 0; $star_id <$star_count; $star_id ++ ) {
				$star_title = array( 'Worst', 'Bad', 'Not Bad', 'Good', 'Best' );
				//echo '<pre>'; var_dump(( isset($postCustomCriteria[$label_id]['stars'][$star_id]['enabled'])? ( ($postCustomCriteria[$label_id]['stars'][$star_id]['enabled']==1)?'1checked ':'yes') : (isset($customCriteria[$label_id]['stars'][$star_id]) ? '2checked ' : 'Not') )); echo '</pre>'; //die();
				$output .=
					'<div class="form-item form-type-checkbox form-item-custom-criteria-star star-id-' . $star_id . $displayItem . '">
                                <input type="checkbox" id="edit-custom-criteria-enable-label-' . $label_id . '-star-' . $star_id . '"
                                    name="ratingForm[custom_criteria][' . $label_id . '][stars][' . $star_id . '][enabled]"
                                    value="1" ' . ( isset( $postCustomCriteria[$label_id]['stars'][$star_id]['enabled'] ) ? ( ( $postCustomCriteria[$label_id]['stars'][$star_id]['enabled'] == 1 ) ? 'checked ' : '' ) : ( isset( $customCriteria[$label_id]['stars'][$star_id] ) ? 'checked ' : '' ) ) . 'class="form-checkbox">
                                <label data-star-id="' . $star_id . '" title="Click to edit"
                                    class="label-' . $label_id . '-option-star-' . $star_id . ' label-' . $label_id . '-option-star option-star-id-' . $star_id . ' option mouse_normal"
                                        >' . ( ( $postCustomCriteria[$label_id]['stars'][$star_id]['title'] ) ? stripslashes( $postCustomCriteria[$label_id]['stars'][$star_id]['title'] ) : ( isset( $customCriteria[$label_id]['stars'][$star_id] ) ? stripslashes( $customCriteria[$label_id]['stars'][$star_id] ) : $star_title[$star_id] ) ) . '</label>

                                <input data-label-id="' . $label_id . '" data-star-id="' . $star_id . '" type="text" id="edit-custom-criteria-text-star-' . $star_id . '"
                                    name="ratingForm[custom_criteria][' . $label_id . '][stars][' . $star_id . '][title]"
                                    value="' . ( ( $postCustomCriteria[$label_id]['stars'][$star_id]['title'] ) ? stripslashes( $postCustomCriteria[$label_id]['stars'][$star_id]['title'] ) : ( isset( $customCriteria[$label_id]['stars'][$star_id] ) ? stripslashes( $customCriteria[$label_id]['stars'][$star_id] ) : $star_title[$star_id] ) ) . '"
                                    class="form-text disable_field edit-custom-criteria-label-text-' . $label_id . '-star edit-custom-criteria-label-text-' . $label_id . '-star-' . $star_id . '">
                                <!--input data-label-id="' . $label_id . '" data-star-id="' . $star_id . '" type="hidden" id="edit-custom-criteria-hidden-star-' . $star_id . '" name="ratingForm[custom_criteria][stars][star-' . $star_id . '][titleHidden]"
                                    value="' . $star_title[$star_id] . '"
                                    class="form-text disable_field edit-custom-criteria-label-hidden-' . $label_id . '-star edit-custom-criteria-label-hidden-' . $label_id . '-star-' . $star_id . '" -->
                            </div>
                    ';

			}
			$output .=
				'           </div>
                    </div>';
            $add_star_msg           = '<p style ="margin-left:10px;">'.__('Unlimited star in premium version','cbratingsystem').'</p>';
            $start_count_addon      = 5;
            $custom_rating_star_msg = apply_filters('ratingsystem_add_new_star', array($add_star_msg , $label_id , $start_count_addon));
            $output                 .= $custom_rating_star_msg[0];
            $output .= '<div class="clear" style="clear:both;"></div><div class="add_more_criteria_custom add_left_margin"></div>
                 ';
			if ( ( $class != 'last' ) ) {
				$output .=
					'<div class="add_more_criteria add_left_margin">
                    ';
				if ( ( $displayItemCount < $label_id ) ) {
					//$output .= '<a href="javascript:void(0);" class="add_more_criteria_link" data-showing-label-id="'.($label_id+1).'">Add more criteria</a>';
				}
				$output .= '
                 </div>
            </div>
            <!--div class="clear" style="clear:both"></div-->
                ';
			}
		}
		$output .=
			'       </div>
            </div>
            ';
        $cbcriteria_msg = '<p style ="margin-left:10px;">'.__('Unlimited criteria in premium version','cbratingsystem').'</p>';
        $cbcriteria_btn = apply_filters('ratingsystem_add_new_crateria',array($cbcriteria_msg , $label_id));
        $output .= $cbcriteria_btn[0];
        $output .= ' <div class="description add_left_margin">Rating Criteria(s) for this <i>"Rating Form"</i>. You can change the Criteria Labal and the Star Label/Hint. To edit Star Label/Hint, click on the default Label/Hint and after chaning click on outside of the field. You must enable/check at least one STAR for every enabled Criteria.</div>
        </div>';

		return $output;
	}

    /**
     * @param array $RFSettings
     *
     * @return string
     */
    private function admin_ratingForm_edit_page_custom_question( $RFSettings = array() ) {
		//echo '<pre>'; print_r($RFSettings); echo '</pre>'; die();

		//$form_id = (!empty($RFSettings) and isset($RFSettings['rfid'])) ? $RFSettings['rfid'] : self::get_next_ratingForm_id();

		$customQuestion = ( $RFSettings->custom_question ) ? $RFSettings->custom_question : array();
		//$customCriteria = !empty($_POST['ratingForm']['custom_criteria']) ? $_POST['ratingForm']['custom_criteria'] : $customCriteria;
		$postCustomQuestion = $_POST['ratingForm']['custom_question'];
		//echo '<pre>$customCriteria'; print_r($customCriteria); echo '</pre>'; //die();
		//echo '<pre>$postCustomCriteria'; print_r($postCustomCriteria); echo '</pre>'; //die();


		$output = '';
		//$output .= '

		$output .=
			'<div class="cb-ratingForm-edit-custom-question-container form-item">
            <label class="" for="edit-custom-question">Custom Questions</label>
            <div class="edit-custom-criteria-fields-wrapper">
            ';

		for ( $q_id = 0; $q_id < 10; $q_id ++ ) {
			//echo '<pre>'; var_dump(( isset($postCustomCriteria[$label_id]['stars'][$star_id]['enabled'])? ( ($postCustomCriteria[$label_id]['stars'][$star_id]['enabled']==1)?'1checked ':'yes') : (isset($customCriteria[$label_id]['stars'][$star_id]) ? '2checked ' : 'Not') )); echo '</pre>'; //die();
			$output .=
				'<div data-q-id="' . $q_id . '" class="form-item form-type-checkbox form-item-custom-question q-id-' . $q_id . ' add_left_margin">
                    <input type="checkbox" id="edit-custom-question-enable-q-' . $q_id . '"
                        name="ratingForm[custom_question][' . $q_id . '][enabled]"
                        value="1" ' . ( isset( $postCustomQuestion[$q_id]['enabled'] ) ? ( ( $postCustomQuestion[$q_id]['enabled'] == 1 ) ? 'checked ' : '' ) : ( isset( $customQuestion['enabled'][$q_id] ) ? 'checked ' : '' ) ) . 'class="form-checkbox">
                    <label data-q-id="' . $q_id . '" title="Click to edit"
                        class="label-q-' . $q_id . ' option mouse_normal"
                            >' . ( ( $postCustomQuestion[$q_id]['title'] ) ? stripslashes( $postCustomQuestion[$q_id]['title'] ) : ( isset( $customQuestion['all'][$q_id] ) ? stripslashes( $customQuestion['all'][$q_id] ) : 'Sample question ' . ( $q_id + 1 ) . '?' ) ) . '</label>

                    <input data-q-id="' . $q_id . '" type="text" id="edit-custom-question-text-q-' . $q_id . '"
                        name="ratingForm[custom_question][' . $q_id . '][title]"
                        value="' . ( ( $postCustomQuestion[$q_id]['title'] ) ? stripslashes( $postCustomQuestion[$q_id]['title'] ) : ( isset( $customQuestion['all'][$q_id] ) ? stripslashes( $customQuestion['all'][$q_id] ) : 'Sample question ' . ( $q_id + 1 ) . '?' ) ) . '"
                        class="form-text disable_field edit-custom-question-label-text-q-' . $q_id . '">
                </div>

                <div data-q-id="' . $q_id . '" class="form-item form-type-checkbox form-item-custom-question-required-q-id-' . $q_id . ' add_left_margin_double add_bottom_margin disable_field">
                    <label data-q-id="' . $q_id . '" title="Is this question required for front-ned users?"
                        class="label-q-required-' . $q_id . ' option mouse_normal"
                            >Required (User must have to check this box)?</label>
                    <input type="checkbox" id="edit-custom-question-require-q-' . $q_id . '"
                        name="ratingForm[custom_question][' . $q_id . '][required]"
                        value="1" ' . ( isset( $postCustomQuestion[$q_id]['required'] ) ? ( ( $postCustomQuestion[$q_id]['required'] == 1 ) ? 'checked ' : '' ) : ( isset( $customQuestion['required'][$q_id] ) ? 'checked ' : '' ) ) . 'class="form-checkbox">
                </div>
            ';

		}

		$output .=
			'
            </div>
            <div class="description add_left_margin">Custom Question</div>
        </div>';

		return $output;
	}

    /**
     *
     */
    public static function admin_ratingForm_listing_html() {
		//echo '<pre>'; print_r($ratingForms); echo '</pre>'; die();
        $ratingForms        = CBRatingSystemData::get_ratingForms( true );
        $total_Rating_forms = count($ratingForms);
        //var_dump($total_Rating_forms);
        if ($total_Rating_forms < 1){
            $add_more_cbform = true;
        }
        else{
            $add_more_cbform = apply_filters('cbraing_add_more_forms' , false);
        }
		$adminUrl = admin_url( 'admin.php?page=ratingformedit&id=' );

		if ( ! empty( $_POST ) && check_admin_referer( 'cb_form_delete_nonce_action', 'cb_form_delete_nonce_action' ) ) {
			$post = $_POST;
			//echo '<pre>'; print_r($post); echo '</pre>'; die();
			if ( ( $post['cbrp-form-delete-by'] == 'selected' ) and ( ! empty( $post['delete_forms'] ) ) ) { // Delete by Selected Items.
				//echo '<pre>'; print_r($post); echo '</pre>'; die();
				//$success = CBRatingSystemData::delete_ratingSummary($post['average_log_ids']);
				asort( $post['delete_forms'] );
				$success = CBRatingSystemData::delete_ratingForm_with_all_ratings( $post['delete_forms'] );
				if ( $success ) {
					$message = __( 'Deleted', 'cbratingsystem' );
				} else {
					$message = __( 'An error occurred while deleting the form. Please ensure that you haven\'t chosen any illegal option. If problem persist please contact the administrator.', 'cbratingsystem' );
				}
			}

			if ( isset( $post['make_form_default'] ) and ( is_numeric( $post['make_form_default'] ) ) ) { // Delete by Selected Items.
				//echo '<pre>'; print_r($post); echo '</pre>'; die();
				$success = update_option( 'cbratingsystem_defaultratingForm', $post['make_form_default'] );
				if ( $success ) {
					$message = __( 'Action completed', 'cbratingsystem' );
				} else {
					$message = __( 'An error occurred while making the form default. Please ensure that you haven\'t chosen any illegal option. If problem persist please contact the administrator.', 'cbratingsystem' );
				}
			}
		}



		?>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				console.log("hello@");

				var options = {};
				options['ajaxurl'] = '<?php echo admin_url('admin-ajax.php'); ?>';
				/*

				 /** Table Sorter and Pagination for Log Reports **/

				jQuery("#log-results")
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

				jQuery("#report-result")
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
                            '1_hover' => __('Disable This', 'cbratingsystem'),
                            '1_normal' => __('Enabled', 'cbratingsystem'),
                            '0_hover' => __('Enable This', 'cbratingsystem'),
                            '0_normal' => __('Disabled', 'cbratingsystem'),
                        )
                    ).';
                ';
            ?>
		</script>
		<div class="wrap columns-2">
			<div class="icon32 icon32_cbrp_admin icon32-cbrp-form-listing" id="icon32-cbrp-form-listing"><br></div>
			<h2><?php _e( 'Codeboxr Rating System Form Listing', 'cbratingsystem' ); echo '</h2>';
                if($add_more_cbform){
                   ?>
                <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=ratingformedit'); ?>"><?php _e('Add New Form','cbratingsystem'); ?></a>
                    <?php
                }
                else {
                    echo __('Unlimited Forms in premium version' , 'cbratingsystem');
                }
                ?>

			<div class="metabox-holder has-right-sidebar" id="poststuff">

				<div id="message" class="<?php echo $class; ?>"
					<?php
					if ( empty( $message ) ) {
						echo "style=\"display:none\"";
					}
					?>>
					<p><strong><?php _e( $message, 'cbratingsystem' ); ?></strong></p>
				</div>
				<div id="post-body" class="post-body">
					<div id="stuff-box">
						<!-- Starting of Single rating Listing -->
						<div class="cbrp-form-listing">
							<form method="post" action="<?php echo $base_page; ?>">
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
												$status        = ( $ratingForm->is_active == 1 ) ? "Enabled" : "Disabled";
												$ebaleShortTag = ( $ratingForm->enable_shorttag == 1 ) ? "Enabled    [cbratingsystem form_id=" . $ratingForm->id . "  post_id=POST_ID theme_key=THEME_KEY]" : "Disabled";
                                                $criteriaHtml  = '';
                                                $criteriaHtml  = ( $criteriaHtml ) ? $criteriaHtml : "No Criteria";

												if ( ! empty( $ratingForm->custom_criteria ) ) {
													$criteriaHtml = '<ul>';
													foreach ( $ratingForm->custom_criteria as $criteriaId => $criteria ) {
														if ( $criteria['label'] ) {
															$criteriaHtml .= '<li><strong>' . $criteria['label'] . '</strong> [Stars::  ' . implode( ', ', CBRatingSystemFunctions :: stripslashes_multiarray( $criteria['stars'] ) ) . '  ]' . '</li>';
														}
													}
													$criteriaHtml .= '</ul>';
												}  ?>
												<tr class="<?php echo $oddEvenClass; ?> status-publish format-standard hentry category-uncategorized alternate iedit author-self">
													<th scope="row" class="check-column">
														<label class="screen-reader-text" for="cb-select-<?php echo $ratingForm->id; ?>">Select Sample</label>
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
										<?php else : echo "<td colspan='7' align='center'>No Results Found</td>"; ?>
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
					</div>
				</div>
				<?php /*require(CB_RATINGSYSTEM_PATH . '/cb-sidebar.php');*/ ?>
			</div>
		</div>
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