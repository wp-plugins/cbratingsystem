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


		$formPath       = admin_url( 'admin.php?page=ratingformedit' );
		$reviewPath     = admin_url( 'admin.php?page=rating_reports' );

		$ratingForms     = CBRatingSystemData::get_ratings_summary_with_ratingForms( true );
		$totalRatingForm = count( $ratingForms );

		$defaultFormId  = get_option( 'cbratingsystem_defaultratingForm' );
		?>
		We are here
		<div class="wrap">

			<div id="icon-options-general" class="icon32"></div>
			<h2><?php _e( "CBX Multi Criteria Rating System Dashboard", 'cbratingsystem' ) ?></h2>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable">

							<div class="postbox">

								<h3><span><?php _e('Overview','cbratingsystem'); ?></span></h3>
								<div class="inside">

									<?php
									$url = get_option( 'siteurl' );
									$url .= '/wp-admin/admin.php?page=ratingformedit';
									?>
									<table class="widefat">
										<thead>
											<tr>
												<th class="row-title">
													<?php _e('Default','cbratingsystem'); ?>
												</th>
												<th >
													<?php _e('Form','cbratingsystem'); ?>
												</th>
												<th><?php _e('Reviews','cbratingsystem'); ?></th>
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
															<a href="<?php echo $link; ?>"><?php echo $title; ?> </a> - <?php _e('ID','cbratingsystem'); ?>: <?php echo $ratingForm->id; ?>
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
															<a href="<?php echo $link; ?>"><?php echo $title; ?></a> - <?php _e('ID','cbratingsystem'); ?>: <?php echo $ratingForm->id; ?>
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
								</div> <!-- .inside -->

							</div> <!-- .postbox -->

						</div> <!-- .meta-box-sortables .ui-sortable -->

					</div> <!-- post-body-content -->

					<!-- sidebar -->
					<div id="postbox-container-1" class="postbox-container">

						<div class="meta-box-sortables">

							<div class="postbox">

								<h3><span><?php  _e('Plugin Information','cbratingsystem'); ?></span></h3>
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
}