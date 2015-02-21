<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CBRatingSystemTool {

	private static $message = '';

	public static function admin_ratingForm_tool_settings() {

        CBRatingSystem::add_common_styles_scripts();


		?>
		<div class="wrap columns-2">
			<div class="icon32 icon32_cbrp_admin icon32-cbrp-dashboard" id="icon32-cbrp-dashboard"><br></div>
			<h2><?php _e( "Codeboxr Rating System Tools", 'cbratingsystem' ) ?></h2>

			<div class="metabox-holder has-right-sidebar" id="poststuff">
				<div id="post-body" class="post-body">
                    <?php echo self::rating_system_tools_settings();?>
				</div>
				<?php
				define( 'CB_RATINGSYSTEM_SUPPORT_VIDEO_DISPLAY', true );
				require( CB_RATINGSYSTEM_PATH . '/cb-sidebar.php' );
				?>
			</div>
		</div>

		<?php


		?>

	<?php


	}


    public static function rating_system_tools_settings(){

        if(!class_exists('cbratingsystemaddon')){
            echo __('<p>This option is for premium version</p>','cbratingsystem');
        }
         do_action('cbrating_tools_settings_wrapper','<p>This option is for premium version</p>');

    }// end of tool settings function

}

?>