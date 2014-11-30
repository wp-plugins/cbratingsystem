<?php

//namespace CBRatingSystem;
/**
 * Class CBRatingSystemTheme
 */
class CBRatingSystemTheme {
    /**
     * @return string
     */
    public static function admin_ratingForm_theme_settings() {
		$output = '';
		$output .= self::build_admin_ratingForm_theme_settings();

		return $output;
	}

    /**
     * build_admin_ratingForm_theme_settings
     */
    public static function build_admin_ratingForm_theme_settings() {

		$formPath = admin_url( 'admin.php?page=ratingformedit' );

		if ( ! empty( $_POST ) && check_admin_referer( 'cbrp_theme_settings_form_nonce_action', 'cbrp_theme_settings_form_nonce_field' ) ) {

			$post     = $_POST['ratingFormTheme'];
			$error    = false;
			$settings = array();
			$message  = array();

			if ( is_string( $post['theme'] ) and ( ! empty( $post['theme'] ) ) ) {
				update_option( 'cbratingsystem_theme_key', $post['theme'] );
			} else {
				update_option( 'cbratingsystem_theme_key', 'basic' );
			}

			if ( ! empty( $post['theme_settings'] ) ) {
				foreach ( $post['theme_settings'] as $theme_key_name => $theme_settings ) {
					if ( ! empty( $theme_settings ) ) {
						//echo '<pre>'; print_r($theme_settings); echo '</pre>'; die();
						foreach ( $theme_settings as $settings_key => $settings_val ) {
							//echo '<pre>'; var_dump(substr($settings_key, -6)); echo '</pre>'; //die();
							if ( substr( $settings_key, - 6 ) == '_color' ) {
								if ( ( strlen( $settings_val ) == 7 ) and ( $settings_val[0] == '#' ) ) {
									$settings['theme_settings'][$theme_key_name][$settings_key] = $settings_val;
								} else {
									$error     = true;
									$message[] = sprintf( __( 'Enter Color Code', 'cbratingsystem' ) . ' <a href="#cbrp' . $theme_key_name . str_replace( '_', '', $settings_key ) . '">' . __( 'ChooseBackgroundColor', CB_RATINGSYSTEM_PLUGIN_SLUG_NAME ) . '</a>' );
								}
							}
						}
						//die();
					} else {
						$error     = true;
						$message[] = __( "Theme Submission Error!!", 'cbratingsystem' ); //__('Something went wrong with the form CB_RATINGSYSTEM_PLUGIN_SLUG_NAMEGIN_SLUG_NAME);
					}
				}
			} else {
				$error     = true;
				$message[] = __( "Theme Submission Error!!", 'cbratingsystem' ); // __('Something went wrong wCB_RATINGSYSTEM_PLUGIN_SLUG_NAMEsion', CB_PLUGIN_SLUG_NAME);
			}

			if ( ( $error === false ) and ! empty( $settings ) ) {
				update_option( 'cbratingsystem_theme_settings', maybe_serialize( $settings ) );

				$message[] = __( "Theme Submitted successfully", 'successfully Saved', cbratingsystem);
			} elseif ( ( $error === true ) and empty( $message ) ) {
				$message[] = __( "Theme Submission Error!!", 'cbratingsystem' ); //CB_RATINGSYSTEM_PLUGIN_SLUG_NAMEwrong with the form submission', CB_PLUGIN_SLUG_NAME);
			}

			//echo '<pre>'; print_r(implode('<br />', $message)); echo '</pre>'; die();
		}
		?>


		<div class="wrap columns-2">
			<div class="icon32 icon32_cbrp_admin icon32-cbrp-theme-settings" id="icon32-cbrp-theme-settings"><br></div>
			<h2><?php _e( "Codeboxr Rating System Theme Settings", 'cbratingsystem' ); ?></h2>

			<div class="metabox-holder has-right-sidebar" id="poststuff">

				<div class="messages <?php echo $class . ( ( $error === false ) ? ' status' : ' error' ); ?>"
					<?php
					if ( empty( $message ) ) {
						echo "style=\"display:none\"";
					}
					?>>
					<?php echo implode( '<br />', $message ); ?>
				</div>
				<div id="post-body" class="post-body">
					<div id="post-body-content">
						<div class="cbratingsystem-theme-settings cbratingsystem-admin-theme-settings" id="cbratingsystem-admin-theme-settings">
							<!-- End of main Dashboard Div -->
							<div class="postbox-container" id="postbox-container-1">
								<div class="meta-box-sortables ui-sortable" id="normal-sortables">
									<div class="postbox cbrarting_theme_settings" id="cbrarting_theme_settings">
										<div title="Click to toggle" class="handlediv"><br></div>
										<h3 class="hndle">
											<span><?php _e( "Setting option", 'cbratingsystem' ); ?></span></h3>

										<div class="inside">
											<div class="table table_content">
												<div class="cbrp_theme_settings_form_wrapper" id="cbrp_theme_settings_form_wrapper">
													<form name="cbrp_theme_settings_form" method="post" id="cbrp_theme_settings_form_wrapper">
														<div class="">
															<table>
																<tr>
																	<td style="width:20%;">
																		<label for="ratingformthemeselection"><?php _e( "Choose Theme", 'cbratingsystem' ); ?></label>
																	</td>
																	<td>
																		<div class="">
																			<?php
																			$themes = array(
																				'0'        => __( "Choose Theme", 'cbratingsystem' ),
																				'basic'    => __( "Basic Theme", 'cbratingsystem' ),

																			);
                                                                            $themes = apply_filters('cbratingsystem_theme_options',$themes);

																			$saved_theme_key = get_option( 'cbratingsystem_theme_key' );
																			$saved_theme_settings = maybe_unserialize( get_option( 'cbratingsystem_theme_settings' ) );

																			if ( ! is_string( $saved_theme_key ) ) {
																				$saved_theme_key = 'basic';
																			}

																			//echo '<pre>'; print_r($saved_theme_settings); echo '</pre>'; die();

																			?>
																			<select id="ratingformthemeselection" name="ratingFormTheme[theme]" class="">
																				<?php
																				foreach ( $themes as $theme_key => $theme_name ) :
																					$selected = ( $theme_key == $saved_theme_key ) ? 'selected ' : '';
																					?>
																					<option <?php echo $selected; ?>value="<?php echo $theme_key; ?>"><?php echo $theme_name; ?></option>
																				<?php
																				endforeach;
																				?>
																			</select>
																		</div>
																	</td>
																</tr>
																<tr>
																	<td style="width:20%;">

																	</td>
																	<td>
                                                                        <?php
                                                                            // added 29-9-14 for premium version codeboxr
                                                                            $cb_post_theme_color =  isset($_POST['ratingFormTheme']) ?$post['theme_settings']['custom']['bg_color'] :'';
                                                                            $cb_post_text_color  =  isset($_POST['ratingFormTheme']) ?$post['theme_settings']['custom']['text_color'] :'' ;
                                                                            echo self::build_custom_themeoptions($cbthemesettings = array('saved_theme_settings' => $saved_theme_settings,'cb_post_theme_color' => $cb_post_theme_color,'cb_post_text_color' => $cb_post_text_color));
                                                                            // no html here
                                                                        ?>

																	</td>
																</tr>
															</table>

															<div class="description add_left_margin">If "<?php echo __( 'Choose Theme', 'cbratingsystem' ); ?>" is chosen, then automatically the "<?php echo __( 'Basic Theme', 'cbratingsystem' ); ?>" will be loaded.</div>
														</div>

														<div class="cbrp_theme_settings_submit">
															<input class="button-primary" name="cbrp_theme_settings" type="submit" value="Save Theme Settings" />
															<?php wp_nonce_field( 'cbrp_theme_settings_form_nonce_action', 'cbrp_theme_settings_form_nonce_field' ); ?>
														</div>
													</form>
												</div>

											</div>
											<br class="clear">
										</div>
									</div>

								</div>
							</div>

							<div class="postbox-container" id="postbox-container-2">

							</div>
							<!-- End of main Dashboard Div -->
						</div>
					</div>
				</div>

				<?php
				define( 'CB_RATINGSYSTEM_SUPPORT_VIDEO_DISPLAY', true );
				require( CB_RATINGSYSTEM_PATH . '/cb-sidebar.php' );
				?>
			</div>
		</div>

	<?php
	}

    /**
     * added 29-9-14 by codeboxr .custom theme option available in premium version
     * codeboxr
     * return a html field to add custom theme
     */
    public static function build_custom_themeoptions($cbthemesettings) {

            $cbrating_custom_theme                  = '<p>'.__('Custom only available in premium version','cbratingsystem').'</p>';
            $cbthemesettings['custom_wrapper']      = $cbrating_custom_theme;
            $cbthemesettings                        = apply_filters('cbratingsystem_theme_custom_option_wrapper',$cbthemesettings);
            //$cbthemesettings['custom_wrapper'] = $cbrating_custom_theme;
        return $cbthemesettings['custom_wrapper'];
    }

    /**
     *build_custom_theme_css
     */
    public static function build_custom_theme_css() {
		$saved_theme_key      = get_option( 'cbratingsystem_theme_key' );
		$saved_theme_settings = maybe_unserialize( get_option( 'cbratingsystem_theme_settings' ) );
		$output               = '';

		//echo '<pre>'; var_dump($saved_theme_key == 'custom'); echo '</pre>'; //die();
		//echo '<pre>'; var_dump(!empty($saved_theme_settings['theme_settings']['custom'])); echo '</pre>'; //die();

		if ( $saved_theme_key == 'custom' ) {
			//echo '<pre>'; var_dump(!empty($saved_theme_settings['theme_settings']['custom'])); echo '</pre>'; die();
			if ( ! empty( $saved_theme_settings['theme_settings']['custom'] ) ) {
				//echo '<pre>'; var_dump(!empty($saved_theme_settings['theme_settings']['custom'])); echo '</pre>'; die();
				$output .= '<style type="text/css">';
				$output .= '
                            .cbrp_container_' . $saved_theme_key . '_theme {
                                background: ' . $saved_theme_settings['theme_settings']['custom']['bg_color'] . ';
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .readonly_criteria_wrapper_' . $saved_theme_key . '_theme, .criteria_star_hint_' . $saved_theme_key . '_theme, .criteria_label_wrapper_' . $saved_theme_key . '_theme {
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .cbrp_container_' . $saved_theme_key . '_theme .switch-tab {
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .cbrp_container_' . $saved_theme_key . '_theme .switch-tab:hover {
                                color: #000000;
                            }
                            .cbrp_container_' . $saved_theme_key . '_theme .form-required {
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .reviews_wrapper_' . $saved_theme_key . '_theme {
                                background: ' . $saved_theme_settings['theme_settings']['custom']['bg_color'] . ';
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .reviews_wrapper_' . $saved_theme_key . '_theme .reviews_rating_' . $saved_theme_key . '_theme:hover {
                                background: ' . $saved_theme_settings['theme_settings']['custom']['bg_color'] . ';
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .reviews_wrapper_' . $saved_theme_key . '_theme a, .reviews_wrapper_' . $saved_theme_key . '_theme .cbrating_user_name a, .reviews_wrapper_' . $saved_theme_key . '_theme .cbrating_user_name a .reviews_wrapper_' . $saved_theme_key . '_theme .user_rate_time a {
                                font-weight: bold;
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .reviews_wrapper_' . $saved_theme_key . '_theme a:hover, .reviews_wrapper_' . $saved_theme_key . '_theme .cbrating_user_name a:hover, .reviews_wrapper_' . $saved_theme_key . '_theme .user_rate_time a:hover {
                                font-weight: bold;
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                                text-decoration: underline;
                            }
                            .cbrp_container_' . $saved_theme_key . '_theme .ratingFormStatus.error_message {
                                border-color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            ';
                    $output .= '</style>';

				//echo '<pre>'; print_r($output); echo '</pre>'; die();
			}
		}

		//echo '<pre>'; print_r($output); echo '</pre>'; die();

		echo $output;
	}// end of build custom css

}// end of class