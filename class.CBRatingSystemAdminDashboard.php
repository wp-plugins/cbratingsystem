<?php

/**
 * Class CBRatingSystemAdminDashboard
 */
class CBRatingSystemAdminDashboard extends CBRatingSystemAdmin {

	public static function display_admin_dashboard() {
		self::build_admin_dashboard();
	}

	/**
	 * [build_admin_dashboard description]
	 * @return [type]
	 */
	public static function build_admin_dashboard() {
		//$data = CBRatingSystemData::get_ratings_summary();

		//echo '<pre>'; print_r($summaryData); echo '</pre>'; die();
		$formPath       = admin_url( 'admin.php?page=ratingformedit' );
		$reviewPath     = admin_url( 'admin.php?page=rating_reports' );

		$ratingForms     = CBRatingSystemData::get_ratings_summary_with_ratingForms( true );
		$totalRatingForm = count( $ratingForms );

		$defaultFormId  = get_option( 'cbratingsystem_defaultratingForm' );
		?>


		<div class="wrap columns-2">
			<div class="icon32 icon32_cbrp_admin icon32-cbrp-dashboard" id="icon32-cbrp-dashboard"><br></div>
			<h2><?php _e( "Codeboxr Rating System Dashboard", 'cbratingsystem' ) ?></h2>

			<div class="metabox-holder has-right-sidebar" id="poststuff">

				<div id="message" class="<?php echo $class; ?>"
					<?php
					if ( empty( $message ) ) {
						echo "style=\"display:none\"";
					}
					?>>
					<p><strong><?php _e( $message, cbratingsystem ); ?></strong></p>
				</div>
				<div id="post-body" class="post-body">
					<div id="post-body-content">
						<?php $url = get_option( 'siteurl' );
						$url .= '/wp-admin/admin.php?page=ratingformedit'; ?>
						<div class="cbratingsystem-dashboard cbratingsystem-admin-dashborad" id="cbrartingsystem-admin-dashboard">
							<!-- End of main Dashboard Div -->
							<div class="postbox-container" id="postbox-container-1">
								<div class="meta-box-sortables ui-sortable" id="normal-sortables">
									<div class="postbox cbrarting_dashboard_overview" id="cbrarting_dashboard_overview">
										<div title="Click to toggle" class="handlediv"><br></div>
										<h3 class="hndle"><span>Overview</span></h3>

										<div class="inside">
											<!--
											<h2 class=""><span><a href="edit.php">4</a><a href="edit.php">Forms</a></span></h2>
											<div title="Click to toggle" class="handlediv"><br></div>
											-->

											<div class="table table_content">
												<p class="sub">
                                                    <?php
                                                    //var_dump($totalRatingForm);
                                                       if( $totalRatingForm >= 1){
                                                           $add_more_cbrating_form = apply_filters('cbraing_add_more_forms',false);
                                                       }
                                                        else{
                                                            $add_more_cbrating_form = true;
                                                        }
                                                    if($add_more_cbrating_form){
                                                        ?>
                                                        <a class="add_button button button-primary button-large button-addrating" href="<?php echo $url; ?>">Add New Form</a>
                                                        <?php

                                                    }
                                                    else{
                                                        echo __('Unlimited Forms in premium version' , 'cbratingsystem');
                                                    }
                                                    ?>

												</p>

												<p class="sub"><?php echo $totalRatingForm; ?> <?php echo( $totalRatingForm > 1 ? __( 'Forms', 'cbratingsystem' ) : __( 'Form', 'cbratingsystem' ) ); ?></p>
												<table>
													<thead>
													<tr class="first">
														<td style="width: 20%; text-align: center;">
															<p class="table_header_sub">Default</p>
														</td>
														<td style="width: 50%;">
															<p class="table_header_sub">Form</p>
														</td>
														<td style="width: 30%;">
															<p class="table_header_sub">Reviews</p>
														</td>
													</tr>
													</thead>
													<tbody>
													<?php
													if ( ! empty( $ratingForms ) ):
														foreach ( $ratingForms as $k => $ratingForm ) :
															$oddEvenClass = ( $k % 2 ) ? 'odd' : 'even';
															$title        = ( $ratingForm->name ) ? $ratingForm->name : "";
															$link         = $formPath . '&id=' . $ratingForm->id;
															$reviewLink   = $reviewPath . '&form=' . $ratingForm->id;
															$reviewsCount = ( $ratingForm->count ) ? $ratingForm->count : 0;
															$reviewsText  = ( ( ( $reviewsCount ) > 1 ) ? __( 'Reviews', 'cbratingsystem' ) : __( 'Review', 'cbratingsystem' ) );

															if ( $defaultFormId != $ratingForm->id ) :

																?>
																<tr class="<?php echo $oddEvenClass; ?>">
																	<td class="first b b_pages">
																		<span><img title="Not Default Form" alt="Not Default Form" src="<?php echo plugins_url( '/images/star-off-big.png', __FILE__ ); ?>" /></span>
																	</td>
																	<td class="t pages">
																		<a href="<?php echo $link; ?>"><?php echo $title; ?></a>
																	</td>
																	<td class="t pages">
																		<?php
																		if ( ( $reviewsCount ) > 1 ) {
																			$class = 'approved';
																		} else {
																			$class = 'waiting';
																		}
																		?>
																		<a href="<?php echo $reviewLink; ?>"><span class="count"><?php echo $reviewsCount; ?></span></a>
																		<a href="<?php echo $reviewLink; ?>" class="<?php echo $class; ?>"><?php echo $reviewsText; ?></a>
																	</td>
																</tr>
															<?php
															else :
																?>
																<tr class="default <?php echo $oddEvenClass; ?>">
																	<td class="first b b_pages">
																		<span><img title="Default Form" alt="Default Form" src="<?php echo plugins_url( '/images/star-on-big.png', __FILE__ ); ?>" /></span>
																	</td>
																	<td class="t pages">
																		<a href="<?php echo $link; ?>"><?php echo $title; ?></a>
																	</td>
																	<td class="t pages">
																		<?php
																		if ( ( $reviewsCount ) > 1 ) {
																			$class = 'approved';
																		} else {
																			$class = 'waiting';
																		}
																		?>
																		<a href="<?php echo $reviewLink; ?>"><span class="count"><?php echo $reviewsCount; ?></span></a>
																		<a href="<?php echo $reviewLink; ?>" class="<?php echo $class; ?>"><?php echo $reviewsText; ?></a>
																	</td>
																</tr>
															<?php
															endif;
														endforeach;
													endif;
													?>
													</tbody>
												</table>
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
}