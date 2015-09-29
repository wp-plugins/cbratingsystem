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

		<!--div class="wrap columns-2">
			<div class="icon32 icon32_cbrp_admin icon32-cbrp-dashboard" id="icon32-cbrp-dashboard"><br></div>
			<h2><?php _e( "Codeboxr Rating System Tools", 'cbratingsystem' ) ?></h2>

			<div class="metabox-holder has-right-sidebar" id="poststuff">
				<div id="post-body" class="post-body">
                    <?php echo self::rating_system_tools_settings();?>
				</div>

			</div>
		</div-->
		<div class="wrap">

			<div id="icon-options-general" class="icon32"></div>
			<h2><?php _e( "Codeboxr Rating System Tools", 'cbratingsystem' ) ?></h2>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable">

							<div class="postbox">
								<div class="inside">
									<?php echo self::rating_system_tools_settings();?>
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


		?>

	<?php


	}


    public static function rating_system_tools_settings(){

        self::rating_tool_settings();


    }// end of tool settings function

    /**
     * Tools Options Pabels
     *
     * admin tool settings
     */
    public static  function  rating_tool_settings(){

        $mooddisplay = "one";

        if (isset ( $_POST['savebutton'] ) &&  $_POST['savebutton']  == __( 'Save Option', 'cbratingsystem' )) {

            if ( isset( $_POST['wdcheckboxalldelete'] ) ) {
                update_option( "cbratingsystem_deleteonuninstall", 1 );
            } else {
                update_option( "cbratingsystem_deleteonuninstall", 0 );
            }
            self::$message = __('Uninstall setting saved!','cbratingsystem' );


        }//end switch
        if (isset ( $_POST['resetbutton'] ) &&  $_POST['resetbutton']  ==  __( 'Reset All', 'cbratingsystem' ) ) {


            self::delete_all();

            self::create_all();

            //self::build_admin_ratingForm_tool_settings();

            self::$message = __('Reset done!','cbratingsystem' );

        }

        ?>

            <?php if(self::$message != ''): ?>
                <div id="message" class="updated"><?php echo self::$message; ?></div>
            <?php endif; ?>
            <p style="color: red;">
                <strong><?php _e( "Options and Tables", 'cbratingsystem' ); //_e('The Following WordPress Options/Tables will be DELETED:', 'wp-downloadmanager'); ?></strong><br />
            </p>

            <table>
                <thead>
                <tr>
                    <th class="alternate"><?php _e( "Rating Options", 'cbratingsystem' ); //_e('Rating  Options', 'wp-ratingsystem'); ?></th>
                    <th class="alternate"><?php _e( "Rating Tables", 'cbratingsystem' ); //_e('Rating  Tables', 'wp-ratingsystem'); ?></th>
                </tr>
                </thead>
                <tr>
                    <td class="alternate">
                        <ol>
                            <li>cbratingsystem_defaultratingForm</li>
                            <li>cbratingsystem_theme_key</li>
                            <li>cbratingsystem_theme_settings</li>
                            <li>cbratingsystem_deleteonuninstall</li>
                        </ol>
                    </td>
                    <td class="alternate">
                        <?php

                        $tables[__('Rating Summary Table', 'cbratingsystem')] 		= CBRatingSystemData::get_ratingForm_settings_table_name(); //look how to create an array
                        $tables[__('User Rating Table', 'cbratingsystem')] 	 	    = CBRatingSystemData::get_user_ratings_table_name();
                        $tables[__('Rating Form Table', 'cbratingsystem')] 	 	    = CBRatingSystemData::get_user_ratings_summury_table_name();

                        ?>
                        <ol>
                            <?php foreach ($tables as $key => $val) {
                                echo '<li>'.$key.'('.$val.')</li>';
                            } ?>
                        </ol>
                    </td>
                </tr>
            </table>

            <form method=post id="dwm_options" action="" enctype="multipart/form-data">
                <p><?php echo __('Delete All options and tables related with this plugin on uninstall/delete','cbratingsystem') ?></p>
                <input class="form-checkbox" type="checkbox" name="wdcheckboxalldelete" value="cbdeleteall" <?php get_option( 'cbratingsystem_deleteonuninstall' ) == 1 ? print_r( "checked='checked'" ) : '' ?>> Yes

                <input class="button button-primary button-large"  type=submit name="savebutton" value="<?php _e( 'Save Option', 'cbratingsystem' ); ?>" tabindex="8"><br>

            </form>
            <br />
            <hr/>
            <h2><?php _e( "Reset Setting", 'cbratingsystem' ); ?></h2>
            <form method=post id="rat_options" action="" enctype="multipart/form-data">
                <input class="button button-primary button-large"  type=submit name="resetbutton" value="<?php _e( 'Reset All', 'cbratingsystem' ); ?>" tabindex="8" onclick="return confirm('<?php _e( 'You are about to reset all previous settings of this plugin .\nThis action is not reversible.\n\n Choose [Cancel] to stop, [OK] to reset.', 'cbratingsystem' ); ?>')">

                <p>
                    <span><?php echo __('This will delete all tables and options created by this plugin. You will get a fresh Rating system to use as first time install. This process can not be undone.','cbratingsystem') ?></span>
                </p>
            </form>
        <?php
        ?>
    <?php
    }// end of tools function

    /**
     * [delete_all description]
     * @return [type] [description]
     */
    public static function delete_all() {

        //at first delete all
        CBRatingSystemData::delete_tables();
        CBRatingSystemData::delete_options();
        CBRatingSystemData::delete_metakeys();

        //now build all again
        //self::build_admin_ratingForm_tool_settings();
    }


    /**
     *  Create All tables, options for this plugin.
     */
    public static function create_all() {

        //create tables again
        CBRatingSystemData::install_table();

        $settings = array();

        //adding options
        update_option( "cbratingsystem_defaultratingForm", 0 );
        update_option( "cbratingsystem_theme_key", 'basic' );
        update_option( "cbratingsystem_theme_settings", $settings );
        update_option( "cbratingsystem_deleteonuninstall", 0 );

        //CBRatingSystemAdmin::admin_ratingForm_edit_page_custom_label( $RFSettings = array() );
    }


    public static function build_admin_ratingForm_tool_settings() {

        //create tables again
        CBRatingSystemData::install_table();

        $settings = array();

        //adding options
        update_option( "cbratingsystem_defaultratingForm", 0 );
        update_option( "cbratingsystem_theme_key", 'basic' );
        update_option( "cbratingsystem_theme_settings", $settings );
        update_option( "cbratingsystem_deleteonuninstall", 0 );

        //CBRatingSystemAdmin::admin_ratingForm_edit_page_custom_label( $RFSettings = array() );
    }

}

?>