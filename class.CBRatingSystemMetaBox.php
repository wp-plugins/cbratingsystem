<?php

class CBRatingSystemMetaBox extends CBRatingSystemAdmin {

	public function add_post_meta_boxes() {

		foreach ( CBRatingSystem::post_types() as $argType => $postTypes ) {
			foreach ( $postTypes['types'] as $type => $typeLabel ) {
				add_meta_box(
					"cbrp-$type-meta-box", // Unique ID
					esc_html__( "Rating Form Options", 'cbratingsystem' ), // Title
					array( 'CBRatingSystemMetaBox', 'post_meta_box_form' ), // Callback function
					"$type", // Admin page (or post type)
					"advanced", // Context
					"core" // Priority
				);
			}
		}
	}

	public function save_post_meta_data( $post_id, $post ) {
		/* Verify the nonce before proceeding. */
		if ( ! isset( $_POST['rating_form_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['rating_form_meta_box_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		//echo '<pre>'; print_r($_POST); echo '</pre>'; die();

		/* Get the posted data and sanitize it for use as an HTML class. */
		$new_meta_enable_value             = ( ( isset( $_POST['enable_ratingForm'] ) and ( $_POST['enable_ratingForm'] == 1 ) ) ? ( $_POST['enable_ratingForm'] ) : '0' );
		$new_meta_default_ratingForm_value = ( ( isset( $_POST['is_default_ratingForm'] ) and ( $_POST['is_default_ratingForm'] == 1 ) ) ? ( $_POST['is_default_ratingForm'] ) : '0' );
		$new_meta_listing_value            = ( ( isset( $_POST['listing_ratingForm'] ) and ( $_POST['listing_ratingForm'] != '#NONE#' ) ) ? sanitize_html_class( $_POST['listing_ratingForm'] ) : '' );

		/* Get the meta key. */
		$meta_keys['_enable_ratingForm']     = $new_meta_enable_value;
		$meta_keys['_is_default_ratingForm'] = $new_meta_default_ratingForm_value;
		$meta_keys['_listing_ratingForm']    = $new_meta_listing_value;

		//echo '<pre>'; var_dump($meta_keys); echo '</pre>'; //die();

		foreach ( $meta_keys as $meta_key => $new_meta_value ) {
			$success = update_post_meta( $post_id, $meta_key, $new_meta_value );

			//echo '<pre>'; var_dump($success); echo '</pre>'; //die();
		}
		//echo '<pre>'; var_dump($ratingFormEnabled = get_post_meta($post_id, 'enable_ratingForm', true)); echo '</pre>'; //die();
		//echo '<pre>'; var_dump($ratingFormEnabled = get_post_meta($post_id, 'listing_ratingForm', true)); echo '</pre>'; die();
	}

	public function post_meta_box_form( $object, $box ) {
		//echo '<pre>'; print_r($object); echo '</pre>'; die();
		$postId = get_the_ID();

		$ratingFormEnabled        = get_post_meta( $postId, '_enable_ratingForm', true );
		$defaultRatingFormEnabled = get_post_meta( $postId, '_is_default_ratingForm', true );
		$ratingFormToShow         = get_post_meta( $postId, '_listing_ratingForm', true );
		$default                  = get_option( 'cbratingsystem_defaultratingForm' );
		$default                  = CBRatingSystem::get_default_ratingFormId();

		//echo '<pre>'; var_dump(( (($ratingFormEnabled != '')) ? (($ratingFormEnabled==1)? '1checked' : '') : ( '2checked') )); echo '</pre>'; //die();
		//echo '<pre>'; var_dump($defaultRatingFormEnabled); echo '</pre>'; die();
		//echo '<pre>'; echo ( (!empty($ratingFormEnabled) and ($ratingFormEnabled != '')) ? (($ratingFormEnabled==1)? 'checked' : '') : (($ratingFormEnabled==0)? '' : 'checked') ); echo '</pre>'; die();

		$ratingFormToShow = ( ! empty( $ratingFormToShow ) and is_numeric( $ratingFormToShow ) ) ? $ratingFormToShow : ( ( is_numeric( $default ) and ! empty( $default ) ) ? $default : '#NONE#' );

		$action      = array(
			'is_active' => true,
			'post_type' => $object->post_type,
		);
		$ratingForms = CBRatingSystemData::get_ratingForms( true, $action );
		$adminUrl    = admin_url( '?page=rating' );

		$option = '<select id="listing_ratingForm" name="listing_ratingForm" class="listing_ratingForm_select" style="width:50%">';
		if ( $ratingFormToShow == '#NONE#' ) {
			$option .= '<option selected value="#NONE#">--Select rating form--</option>';
		} else {
			$option .= '<option value="#NONE#">--Select rating form--</option>';
		}

		if ( ! empty( $ratingForms ) ) {
			foreach ( $ratingForms as $ratingForm ) {
				if ( $default == $ratingForm->id ) {
					$txt = ' (' . __( 'Default Form', cbratingsystem ) . ')';
				} else {
					$txt = '';
				}
				if ( $ratingFormToShow == $ratingForm->id ) {
					$option .= '<option selected value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
				} else {
					$option .= '<option value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
				}
			}
		}
		$option .= '</select>';

		?>
		<?php wp_nonce_field( basename( __FILE__ ), 'rating_form_meta_box_nonce' ); ?>


		<p>
			<label for="enable_ratingForm"><?php _e( sprintf( "Display Form" ), 'cbratingsystem' ); ?></label>
			<input type="checkbox" id="enable_ratingForm" name="enable_ratingForm" value="1" <?php echo( ( ( $ratingFormEnabled != '' ) ) ? ( ( $ratingFormEnabled == 1 ) ? 'checked' : '' ) : ( 'checked' ) ); ?>/>
		</p>
		<p>
			<label for="is_default_ratingForm"><?php _e( sprintf( "Use Default" ), 'cbratingsystem' ); ?></label>
			<input type="checkbox" id="is_default_ratingForm" name="is_default_ratingForm" value="1" <?php echo( ( ( $defaultRatingFormEnabled != '' ) ) ? ( ( $defaultRatingFormEnabled == 1 ) ? 'checked' : '' ) : ( 'checked' ) ); ?>/>
		</p>
		<p class="cb_listing_ratingForm">
			<label for="listing_ratingForm"><?php _e( sprintf( "Select which form should work for this %s:", $object->post_type ), cbratingsystem ); ?></label>
			<br />
			<?php echo $option; ?>
			<span class="description"><?php _e( sprintf( "Select which form should work for this %s. You can checkout rating forms at %s page.", $object->post_type, '<a target="_blank" href="' . $adminUrl . '">this</a>' ), cbratingsystem ); ?></span>
		</p>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('input#is_default_ratingForm').change(function () {
					if ($(this).is(':checked')) {
						$('.cb_listing_ratingForm').hide();
					} else {
						$('.cb_listing_ratingForm').show();
					}
				});
				if ($('input#is_default_ratingForm').is(':checked')) {
					$('.cb_listing_ratingForm').hide();
				} else {
					$('.cb_listing_ratingForm').show();
				}
			});
		</script>
	<?php
	}

	/* Filter the post class hook with our custom post class function. */

	//add_filter( 'rating_form_array', array('CBRatingSystemMetaBox', 'ratingForm_add_meta_data_filter') );

	public static function ratingForm_add_meta_data_filter( $ratingFormID ) {

		/* Get the current post ID. */
		$postId = get_the_ID();

		/* If we have a post ID, proceed. */
		if ( ! empty( $postId ) ) {

			/* Get the custom post class. */
			$ratingFormEnabled        = get_post_meta( $postId, '_enable_ratingForm', true );
			$defaultRatingFormEnabled = get_post_meta( $postId, '_is_default_ratingForm', true );
			$ratingFormToShow         = get_post_meta( $postId, '_listing_ratingForm', true );

			if ( ! empty( $ratingFormToShow ) and is_numeric( $ratingFormToShow ) ) {
				if ( ( $ratingFormEnabled == 1 ) ) {
					if ( $ratingFormID == $ratingFormToShow ) {
						$ratingFormID = $ratingFormID;
					} else {
						$ratingFormID = $ratingFormToShow;
					}

					if ( $defaultRatingFormEnabled == 1 ) {
						$ratingFormID = get_option( 'cbratingsystem_defaultratingForm' );
					}
				} else {
					$ratingFormID = null;
				}
			}
		}

		return $ratingFormID;
	}

}