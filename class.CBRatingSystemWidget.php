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
        $order 	    = $instance['order'];

        $whrOptn['order'] = $order;
		if ( $date != 0 ) {

			$date = self::get_calculated_date( $date );
			$whrOptn['post_date'] = $date;
		}

		$whrOptn['post_type'][] = $type;
		$whrOptn['form_id'][] 	= $form_id;  //added from v3.2.20

		$data = CBRatingSystemData::get_ratings_summary( $whrOptn, 'avg', $order, true, $limit );


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

        if(array_key_exists( 'order' ,$instance)){
            $order = $instance['order'];
        }
        else{
            $order = 'DESC';
        }

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
        <!-- order by  type filter -->
        <p>
            <label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( "Order", 'cbratingsystem' ) ?>:</label>
            <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('ASC' => 'Ascending', 'DESC' => 'Descending' );

                foreach ( $no_of_filter as $key => $label ) {

                    echo "<option value = '$key'";

                    if ( $order == $key ) {
                        echo "selected='selected'";
                    }
                    echo "> $label </option>";
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
        $instance['order']           = strip_tags( $new_instance['order'] );

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


/*Codeboxr Rating System Top Rated User*/
/**
 * Class CBRatingSystemUserWidget
 */
class CBRatingSystemUserWidget extends WP_Widget {

    function CBRatingSystemUserWidget() {
        parent::WP_Widget( 'cbrp_top_rated_user', __('Top Rated User','cbratingsystem'), array( 'description' => __('A widget to display top rated user.','cbratingsystem') ) );
    }

    /**
     * @param array $args
     * @param array $instance
     */
    function widget( $args, $instance ) {

        global $wpdb;

        extract( $args );

        $widget_id = $args['widget_id'];
       // var_dump($widget_id);

        CBRatingSystem::load_scripts_and_styles();


        $posttype   = $instance['posttype'];
        $date  		= $instance['timelimit'];
        $limit 		= $instance['resultlimit'];
        $form_id 	= $instance['form_id'];
        $user_id 	= $instance['user_id']  ;
        $post_id 	= $instance['post_id'] ;
        $post_filter= $instance['post_filter'] ;
        $order      = $instance['order'] ;
        $firstorder = $instance['firstorder'] ;

        if ( $date != 0 ) {

            $date = self::get_calculated_date( $date );
            $whrOptn['post_date'] = $date;
        }

        $whrOptn['post_type']       = $posttype;
        $whrOptn['form_id']         = $form_id;  //added from v3.2.20
        $whrOptn['user_id']         = $user_id;
        $whrOptn['form_id']         = $form_id;  //added from v3.2.20
        $whrOptn['post_id']         = $post_id;
        $whrOptn['post_filter']     = $post_filter;  //added from v3.2.20
        $whrOptn['order']           = $order;  //added from v3.2.20
        $whrOptn['firstorder']      = $firstorder;

        $data = CBRatingSystemData::get_top_rated_post( $whrOptn, false, $limit );


        $title = empty($options['widget_title']) ? __('Top Rated Posts','cbratingsystem') : $instance['widget_title'];
        $title 		= apply_filters( 'widget_title', $instance['title'] );
        echo $before_widget;
        echo $before_title . $title . $after_title;
        ?>
        <ul class="cbrp-top-rated-wpanel" style="">
            <?php if ( ! empty( $data ) ) : ?>
                <?php //var_dump($data);?>

          <!--          <td style=""> <?php /*echo 'Name'; */?></td>
                    <td style=""> <?php /*echo 'Post '; */?></td>
                    <td style=""> <?php /*echo 'Rating'; */?></td>
-->


                <?php foreach ( $data as $newdata ) : ?>

                    <li>
                        <?php
                        $author_info = get_userdata( (int) $newdata['post_author']);
                        ?>
                        <div style=""> <a style="" href="<?php echo get_author_posts_url((int) $newdata['post_author']); ?>"><?php echo $author_info->display_name; ?></a></div>
                         <div style=""> <?php echo $newdata['post_count']; ?> Posts</div>
                        <?php
                        $rating = ( ( $newdata['rating']/ 100 ) *  5);
                           ?>
                        <script>
                            jQuery(document).ready(function ($) {
                                $('#cbrp-top-rated<?php echo $newdata['post_author'].'_'.$widget_id; ?>').raty({
                                    half    : true,
                                    path    : '<?php echo CB_RATINGSYSTEM_PLUGIN_DIR_IMG ; ?>',
                                    score   : <?php echo number_format($rating, 2, '.', ''); ?>,
                                    readOnly: true,
                                    hintList: ['', '', '', '', '']
                                });
                            });
                        </script>
                                   <?php echo "<strong>" . number_format($rating, 2, '.', '') . "/5</strong> "; ?>

                                    <div id ="cbrp-top-rated<?php echo $newdata['post_author'].'_'.$widget_id ?>" style=""></div>

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
            $title = __('Top Rated Users', 'cbratingsystem');
        }

       if(array_key_exists( 'timelimit' ,$instance)){
           $timelimit = $instance['timelimit'];
       }
        else{
            $timelimit = '0';
        }
        if(array_key_exists( 'user_id' ,$instance)){
            $user_id = $instance['user_id'];
        }
        else{
            $user_id = '';
        }
        if(array_key_exists( 'post_id' ,$instance)){
            $post_id = $instance['post_id'];
        }
        else{
            $post_id = '';
        }

        if(array_key_exists( 'resultlimit' ,$instance)){
            $limit = $instance['resultlimit'];
        }
        else{
            $limit = '10';
        }
        if(array_key_exists( 'posttype' ,$instance)){
            $posttype = $instance['posttype'];
        }
        else{
            $posttype = '0';
        }
        if(array_key_exists( 'post_filter' ,$instance)){
            $post_filter = $instance['post_filter'];
        }
        else{
            $post_filter = 'post_type';
        }
        if(array_key_exists( 'form_id' ,$instance)){
            $form_id = $instance['form_id'];
        }
        else{
            $form_id = 'form_id';
        }
//$order
        if(array_key_exists( 'order' ,$instance)){
            $order = $instance['order'];
        }
        else{
            $order = 'DESC';
        }
        if(array_key_exists( 'firstorder' ,$instance)){
            $firstorder = $instance['firstorder'];
        }
        else{
            $firstorder = 'post_count';
        }

        ?>
        <p>
            <label for = "<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( "Title", 'cbratingsystem' ) ?>:</label>
            <input class = "widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" />
        </p>
        <!--user limit  -->
        <p>
            <label for = "<?php echo $this->get_field_id( 'user_id' ); ?>"><?php _e( "User Ids ('Blank or comma separate ids ) ", 'cbratingsystem' ) ?>:</label>
            <input class = "widefat" id="<?php echo $this->get_field_id( 'user_id' ); ?>" type="text" name="<?php echo $this->get_field_name( 'user_id' ); ?>" value="<?php echo $user_id; ?>" />
        </p>
        <!--time limit  -->
        <p>
            <label for="<?php echo $this->get_field_id( 'timelimit' ); ?>"><?php _e( "Display Last", 'cbratingsystem' ) ?>:</label>
            <select id="<?php echo $this->get_field_id( 'timelimit' ); ?>" name="<?php echo $this->get_field_name( 'timelimit' ); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_days = array( 1 => '24 hours', 7 => 'Week', 15 => '15 Days', 30 => 'Month', 0 => 'All' );
                foreach ( $no_of_days as $day => $day_label ) {
                    echo "<option value='$day'";
                    if ( $timelimit == $day ) {
                        echo "selected='selected'";
                    }
                    echo ">$day_label</option>";
                }
                ?>
            </select>
        </p>
        <!--result limit  -->
        <p>
            <label for="<?php echo $this->get_field_id( 'resultlimit' ); ?>">No. To Display:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'resultlimit' ); ?>" type="text" name="<?php echo $this->get_field_name( 'resultlimit' ); ?>" value="<?php echo $limit; ?>" />
        </p>
        <!--post id -->
        <p>
            <label for="<?php echo $this->get_field_id( 'post_id' ); ?>"><?php _e( "Post Ids ('Blank or comma separate ids ) ", 'cbratingsystem' ) ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'post_id' ); ?>" type="text" name="<?php echo $this->get_field_name( 'post_id' ); ?>" value="<?php echo $post_id; ?>" />
        </p>
        <!--post type id -->
        <p>
            <label for="<?php echo $this->get_field_id( 'posttype' ); ?>"><?php _e( "Post Type", 'cbratingsystem' ) ?>:</label>
            <select id="<?php echo $this->get_field_id( 'posttype' ); ?>" name="<?php echo $this->get_field_name( 'posttype' ); ?>" class="widefat" style="width: 55%">
                <?php
                echo "<option value='0'";
                if ($posttype == 0 ) {
                    echo "selected='selected'";
                }
                echo ">" . ucfirst( 'All' ) . "</option>";
                //////////////

                foreach ( CBRatingSystem::post_types() as $argType => $postTypes ) {
                    echo '<optgroup label="' . $postTypes['label'] . '">';
                    foreach ( $postTypes['types'] as $type => $typeLabel ) {
                        echo "<option value='$type'";
                        if ($posttype == $type ) {
                            echo "selected='selected'";
                        }
                        echo ">" . ucfirst( $typeLabel ) . "</option>";
                    }
                    echo '</optgroup>';
                }

                ?>
            </select>
        </p>
        <!-- post type filter -->
        <p>
            <label for="<?php echo $this->get_field_id( 'post_filter' ); ?>"><?php _e( "Post Filter", 'cbratingsystem' ) ?>:</label>
            <select id="<?php echo $this->get_field_id( 'post_filter' ); ?>" name="<?php echo $this->get_field_name( 'post_filter' ); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('post_type' => 'Post Type', 'post_id' => 'Post ID' );

                foreach ( $no_of_filter as $key => $label ) {

                    echo "<option value = '$key'";

                    if ( $post_filter == $key ) {
                        echo "selected='selected'";
                    }
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>
        <!-- order by  type filter -->
        <p>
            <label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( "Order", 'cbratingsystem' ) ?>:</label>
            <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('ASC' => 'Ascending', 'DESC' => 'Descending' );

                foreach ( $no_of_filter as $key => $label ) {

                    echo "<option value = '$key'";

                    if ( $order == $key ) {
                        echo "selected='selected'";
                    }
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>
        <!-- order by  type filter -->
        <p>
            <label for="<?php echo $this->get_field_id( 'firstorder' ); ?>"><?php _e( "First Sort By", 'cbratingsystem' ) ?>:</label>
            <select id="<?php echo $this->get_field_id( 'firstorder' ); ?>" name="<?php echo $this->get_field_name( 'firstorder' ); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('rating' => 'Rating', 'post_count' => 'User post Number' );

                foreach ( $no_of_filter as $key => $label ) {

                    echo "<option value = '$key'";

                    if ( $firstorder == $key ) {
                        echo "selected='selected'";
                    }
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>


        <!--form id -->
        <p>
            <label for="<?php echo $this->get_field_id( 'form_id' ); ?>"><?php _e( "Form", 'cbratingsystem' ) ?>:</label>
            <select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>" class="widefat" style="width: 55%">
                <?php
                $action      = array(
                    'is_active' => true,

                );
                $ratingForms 		      = CBRatingSystemData::get_ratingForms( true, $action );
                $ratingFormToShow 	      = intval($form_id);

                $default                  = CBRatingSystem::get_default_ratingFormId();

                if ( ! empty( $ratingForms ) ) {

                    echo "<option value='0'";
                    if ($ratingFormToShow == 0 ) {
                        echo "selected='selected'";
                    }
                    echo ">" . ucfirst( 'All' ) . "</option>";

                    foreach ( $ratingForms as $ratingForm ) {

                        if ( $default == $ratingForm->id ) {
                            $txt = ' (' . __( 'Default Form', 'cbratingsystem' ) . ')';
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

        $instance                    = $old_instance;
        $instance['title']           = strip_tags( $new_instance['title'] );
        $instance['timelimit']       = strip_tags( $new_instance['timelimit'] );
        $instance['resultlimit']     = strip_tags( $new_instance['resultlimit'] );
        $instance['posttype']        = strip_tags( $new_instance['posttype'] );
        $instance['form_id']         = strip_tags( $new_instance['form_id'] );
        $instance['user_id']         = strip_tags( $new_instance['user_id'] );
        $instance['post_id']         = strip_tags( $new_instance['post_id'] );
        $instance['post_filter']     = strip_tags( $new_instance['post_filter'] );
        $instance['order']           = strip_tags( $new_instance['order'] );
        $instance['firstorder']      = strip_tags( $new_instance['firstorder'] );

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
}// end of user widget class
