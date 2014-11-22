<?php

//namespace CBRatingSystem;
/**
 * Class CBRatingSystemWidget
 */
class CBRatingSystemWidget extends WP_Widget {

	function CBRatingSystemWidget() {
		parent::WP_Widget( 'cbrp_top_rated', __('Top Rated Posts','cbratingsystem'), array( 'description' => __('A widget to display top rated posts, pages or custom post types.','cbratingsystem') ) );
	}

    /**
     * @param array $args
     * @param array $instance
     */
    function widget( $args, $instance ) {
		global $wpdb;

		extract( $args );
		CBRatingSystem::load_scripts_and_styles();


		$type  		= $instance['type'];
		$date  		= $instance['day'];
		$limit 		= $instance['todisplay'];
		$form_id 	= $instance['form_id'];

		if ( $date != 0 ) {

			$date = self::get_calculated_date( $date );
			$whrOptn['post_date'] = $date;
		}

		$whrOptn['post_type'][] = $type;
		$whrOptn['form_id'][] 	= $form_id;  //added from v3.2.20

		$data = CBRatingSystemData::get_ratings_summary( $whrOptn, 'avg', 'DESC', true, $limit );


        $title = empty($options['widget_title']) ? __('Top Rated Posts','cbratingsystem') : $instance['widget_title'];
        $title 		= apply_filters( 'widget_title', $instance['title'] );
        echo $before_widget;
        echo $before_title . $title . $after_title;
		?>
		<ul id="cbrp-top-rated-wpanel" style="padding: 10px 0 10px 10px;">
			<?php if ( ! empty( $data ) ) : ?>
				<?php foreach ( $data as $newdata ) : ?>
					<li>
						<script>
							jQuery(document).ready(function ($) {
								$('#cbrp-top-rated<?php echo $newdata->post_id; ?>').raty({
									half    : true,
									path    : '<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG; ?>',
									score   : <?php echo ( ($newdata->per_post_rating_summary/100) * 5); ?>,
									readOnly: true,
									hintList: ['', '', '', '', '']
								});
							});
						</script>
						<div id="cbrp-top-rated<?php echo $newdata->post_id; ?>" style="margin: 0;"></div>
						<?php echo "<strong>" . ( ( $newdata->per_post_rating_summary / 100 ) * 5 ) . "/5</strong> "; ?>
						<a href="<?php echo get_permalink( $newdata->post_id ); ?>"><?php echo $newdata->post_title; ?></a>
					</li>
				<?php endforeach; ?>
			<?php else: ?>
				<li><?php _e('No Results found','cbratingsystem'); ?></li>
			<?php endif; ?>
		</ul>
		<?php
		echo $after_widget;
	}

    /**
     * @param array $instance
     *
     * @return string|void
     */
    function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
		} else {
			$title = __('Top Rated Posts', 'cbratingsystem');
		}
		//die();
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( "Title", 'cbratingsystem' ) ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'day' ); ?>"><?php _e( "Display Last", 'cbratingsystem' ) ?>:</label>
			<select id="<?php echo $this->get_field_id( 'day' ); ?>" name="<?php echo $this->get_field_name( 'day' ); ?>" class="widefat" style="width:50%">
				<?php
				$no_of_days = array( 1 => '24 hours', 7 => 'Week', 15 => '15 Days', 30 => 'Month', 0 => 'All' );
				foreach ( $no_of_days as $day => $day_label ) {
					echo "<option value='$day'";
					if ( $instance['day'] == $day ) {
						echo "selected='selected'";
					}
					echo ">$day_label</option>";
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'todisplay' ); ?>">No. To Display:</label>
			<select id="<?php echo $this->get_field_id( 'todisplay' ); ?>" name="<?php echo $this->get_field_name( 'todisplay' ); ?>" class="widefat" style="width: 25%">
				<?php
				$no_of_display = array( 10, 15, 20 );
				foreach ( $no_of_display as $nod ) {
					echo "<option value='$nod'";
					if ( $instance['todisplay'] == $nod ) {
						echo "selected='selected'";
					}
					echo ">$nod</option>";
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( "Post Type", 'cbratingsystem' ) ?>:</label>
			<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat" style="width: 55%">
				<?php

				foreach ( CBRatingSystem::post_types() as $argType => $postTypes ) {
					echo '<optgroup label="' . $postTypes['label'] . '">';
					foreach ( $postTypes['types'] as $type => $typeLabel ) {
						echo "<option value='$type'";
						if ( $instance['type'] == $type ) {
							echo "selected='selected'";
						}
						echo ">" . ucfirst( $typeLabel ) . "</option>";
					}
					echo '</optgroup>';
				}

				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'form_id' ); ?>"><?php _e( "Form", 'cbratingsystem' ) ?>:</label>
			<select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>" class="widefat" style="width: 55%">
				<?php
				$action      = array(
					'is_active' => true,
					'post_type' => $object->post_type,
				);
				$ratingForms 		= CBRatingSystemData::get_ratingForms( true, $action );
				$ratingFormToShow 	= intval($this->get_field_id( 'form_id' ));
				$default                  = CBRatingSystem::get_default_ratingFormId();

				if ( ! empty( $ratingForms ) ) {
					foreach ( $ratingForms as $ratingForm ) {

						if ( $default == $ratingForm->id ) {
							$txt = ' (' . __( 'Default Form', cbratingsystem ) . ')';
						} else {
							$txt = '';
						}

						if ( $ratingFormToShow == $ratingForm->id ) {
							echo  '<option selected value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
						} else {
							echo  '<option value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
						}
					}
				}


				?>
			</select>
		</p>
	<?php
	}

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['day']       = strip_tags( $new_instance['day'] );
		$instance['todisplay'] = strip_tags( $new_instance['todisplay'] );
		$instance['type']      = strip_tags( $new_instance['type'] );
		$instance['form_id']   = strip_tags( $new_instance['form_id'] );

		return $instance;
	}

    /**
     * @param $date
     *
     * @return bool|string
     */
    function get_calculated_date( $date ) {
		if ( is_numeric( $date ) ) {
			return date( 'Y-m-d H:i:s', strtotime( "-$date days" ) );
		}
	}
}
