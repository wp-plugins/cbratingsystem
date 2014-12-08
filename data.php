<?php

/**
 * Class CBRatingSystemData
 */
class CBRatingSystemData {
    /**
     *
     */
    public static function install_table() {
        global $wpdb;

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        //need for database creation
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }

        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

        $table_name = self::get_ratingForm_settings_table_name();
        $sql        = "CREATE TABLE $table_name (
                        id mediumint(8) unsigned not null auto_increment,
                        name varchar(1000) not null,
                        is_active tinyint(1) not null,
                        post_types VARCHAR( 1000 ) NOT NULL,
                        show_on_single INT( 1 ) NOT NULL DEFAULT  '1',
                        show_on_home INT( 1 ) NOT NULL DEFAULT  '1',
                        position varchar(100) not null,
                        enable_shorttag tinyint(1) not null,
                        logging_method varchar(100) not null,
                        allowed_users varchar(200) not null,
                        editor_group varchar(50) not null,
                        custom_criteria longtext not null,
                        enable_comment tinyint(1) not null,
                        comment_limit INT( 10 ) NOT NULL DEFAULT  '0',
                        enable_question INT( 1 ) NOT NULL,
                        custom_question longtext not null,
                        review varchar(1000) NOT NULL,
                        show_on_arcv INT( 1 ) NOT NULL DEFAULT  '1',
                        email_verify_guest INT( 1 ) NOT NULL DEFAULT  '1',
                        view_allowed_users varchar(200)not null,
                        comment_view_allowed_users varchar(200) not null,
                        comment_moderation_users varchar(200) not null,
                        comment_required INT( 1 ) NOT NULL DEFAULT  '1',
                        buddypress_active INT( 1 ) NOT NULL DEFAULT  '1',
                        buddypress_post INT( 1 ) NOT NULL DEFAULT  '1',
                        show_chedit_to_codeboxr INT( 1 ) NOT NULL DEFAULT  '1',
                        show_user_avatar_in_review INT( 1 ) NOT NULL DEFAULT  '1',
                        show_user_link_in_review INT( 1 ) NOT NULL DEFAULT  '1',
                        show_editor_rating INT( 1 ) NOT NULL DEFAULT  '1',
                        PRIMARY KEY  (id)
            ) $charset_collate;";
        //here we nedd charset
        $wpdb->query( $sql );
        dbDelta( $sql ); //we take upgrade.php to get this method

        //echo '<pre>'; print_r($sql); die();

        $table_name = self::get_user_ratings_table_name();
        $sql        = "CREATE TABLE $table_name (
                      id mediumint(8) unsigned not null auto_increment,
                      form_id int(10) not null COMMENT 'Rating form id',
                      post_id int(10) not null,
                      post_type varchar(15),
                      rating longtext not null,
                      question LONGTEXT NOT NULL,
                      comment LONGTEXT NOT NULL,
                      comment_status LONGTEXT NOT NULL,
                      comment_hash LONGTEXT NOT NULL,
                      comment_limit INT( 10 ) NOT NULL DEFAULT '0',
                      average INT( 5 ) NOT NULL COMMENT  'value * 100',
                      user_id int(10),
                      user_name varchar(100) DEFAULT NULL,
                      user_email varchar(100) DEFAULT NULL,
                      user_session VARCHAR( 100 ),
                      user_ip VARCHAR( 16 ) NOT NULL,
                      created int(20) not null,
                      PRIMARY KEY  (id)
            ) $charset_collate;";
        $wpdb->query( $sql );
        dbDelta( $sql );

        //echo '<pre>'; print_r($sql); die();

        $table_name = self::get_user_ratings_summury_table_name();
        $sql        = "CREATE TABLE $table_name (
                        `id` int(10) NOT NULL AUTO_INCREMENT,
                        `post_id` int(10) NOT NULL,
                        `post_type` VARCHAR( 50 ) NOT NULL,
                        `form_id` int(10) NOT NULL,
                        `per_post_rating_count` int(100) NOT NULL DEFAULT '0',
                        `per_post_rating_summary` int(2) NOT NULL,
                        `custom_user_rating_summary` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
                        `per_criteria_rating_summary` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
                        PRIMARY KEY  (id)
                )$charset_collate;";
        $wpdb->query( $sql );
        dbDelta( $sql );

    }

    /**
     *
     */
    public static function update_table() {
        global $wpdb;

        $version = CBRatingSystem::$version; //notice how can we take value from other class.the var is static in nature

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }

        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        $method = 'install_table';
        if ( method_exists( 'CBRatingSystemData', $method ) ) //
        {
            self::$method();
        }


    }

    /**
     *  Update tables comparing version or sql table structure
     *
     */
    public  static  function  modify_tables(){
        global $wpdb;
        $setting_table = self::get_ratingForm_settings_table_name();

    }

    /**
     * Delete all tables created by this plugin
     *
     */
    public static function delete_tables() {
        //delete tables
        //var_dump('hi there'); exit();
        global $wpdb;
        $table_name[] = self::get_ratingForm_settings_table_name(); //look how to create an array
        $table_name[] = self::get_user_ratings_table_name();
        $table_name[] = self::get_user_ratings_summury_table_name();

        $sql = "DROP TABLE IF EXISTS " . implode( ', ', $table_name );
        $wpdb->query($sql);
        //require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        //dbDelta( $sql );

    }

    /**
     * Delete all options created by this plugin
     *
     */
    public static function delete_options(){
        //delete options
        delete_option( "cbratingsystem_defaultratingForm" );
        delete_option( "cbratingsystem_theme_key" );
        delete_option( "cbratingsystem_theme_settings" );
        delete_option( "cbratingsystem_deleteonuninstall" );

        //delete plugin version name
        //delete_site_option( 'cbratingsystem_plugin_version');
    }

    /**
     * Delete all meta keys created by this plugin
     *
     */
    public  static  function  delete_metakeys(){
        //delete meta keys
        $meta_keys['_enable_ratingForm']  = 'enable_ratingForm';
        $meta_keys['_listing_ratingForm'] = 'listing_ratingForm';
        foreach ( $meta_keys as $meta_key ) {
            delete_post_meta_by_key( $meta_key );
        }
    }

    /**
     * @param bool $is_object
     * @param array $action_option
     * @return array
     */
    public static function get_ratingForms( $is_object = false, array $action_option = array() ) {
        global $wpdb;
        $table_name = self::get_ratingForm_settings_table_name();
        $action = '';
        if ( ! empty( $action_option ) and is_array( $action_option ) ) {
            $action = "WHERE";
            $action .= ( isset( $action_option['is_actives'] ) ? ' is_active=1 AND' : '' );
            $action .= ( ! empty( $action_option['post_type'] ) and is_string( $action_option['post_type'] ) ? ' post_types LIKE  \'%' . $action_option['post_type'] . '%\'' : '' );

            if ( $action == 'WHERE' ) {
                $action = '';
            }
        }

        if ( substr( $action, - 3 ) == 'AND' ) {
            $action = substr( $action, 0, - 3 );
        }

        $sql = $wpdb->prepare( "SELECT * FROM $table_name $action ORDER BY name ASC", null );

        if ( ! $is_object ) { //how i want rating forms like an array or an object
            $results = $wpdb->get_results( $sql, ARRAY_A );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );

            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]["post_types"]                 = maybe_unserialize( $results[$i]["post_types"] );
                $results[$i]["position"]                   = maybe_unserialize( $results[$i]["position"] );
                $results[$i]["logging_method"]             = maybe_unserialize( $results[$i]["logging_method"] );
                $results[$i]["allowed_users"]              = maybe_unserialize( $results[$i]["allowed_users"] );
                $results[$i]["view_allowed_users"]         = maybe_unserialize( $results[$i]["view_allowed_users"] );
                $results[$i]["comment_view_allowed_users"] = maybe_unserialize( $results[$i]["comment_view_allowed_users"] );
                $results[$i]["comment_moderation_users"]   = maybe_unserialize( $results[$i]["comment_moderation_users"] );
                $results[$i]["custom_criteria"]            = maybe_unserialize( $results[$i]["custom_criteria"] );
                $results[$i]["custom_question"]            = maybe_unserialize( $results[$i]["custom_question"] );
                $results[$i]["review"]                     = maybe_unserialize( $results[$i]["review"] );
            }
        } else {
            $results = $wpdb->get_results( $sql, OBJECT );
            //echo '<pre>'; print_r($results); echo '</pre>'; die();
            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]->post_types                 = maybe_unserialize( $results[$i]->post_types );
                $results[$i]->position                   = maybe_unserialize( $results[$i]->position );
                $results[$i]->logging_method             = maybe_unserialize( $results[$i]->logging_method );
                $results[$i]->allowed_users              = maybe_unserialize( $results[$i]->allowed_users );
                $results[$i]->view_allowed_users         = maybe_unserialize( $results[$i]->view_allowed_users );
                $results[$i]->comment_view_allowed_users = maybe_unserialize( $results[$i]->comment_view_allowed_users );
                $results[$i]->comment_moderation_users   = maybe_unserialize( $results[$i]->comment_moderation_users );
                $results[$i]->custom_criteria            = maybe_unserialize( $results[$i]->custom_criteria );
                $results[$i]->custom_question            = maybe_unserialize( $results[$i]->custom_question );
                $results[$i]->review                     = maybe_unserialize( $results[$i]->review );
            }
        }

        return $results;
    }

    /**
     * @param $id
     * @param bool $is_object
     * @return array
     */
    public static function get_ratingForm( $id, $is_object = false ) {

        global $wpdb;
        $table_name = self::get_ratingForm_settings_table_name();
        $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE id=%d", $id );

        /*********************Editing This Part**************************/

        if ( ! $is_object ) {
            $results = $wpdb->get_results( $sql, ARRAY_A );

            if ( empty( $results ) ) {
                return array();
            }

            $result                               = $results[0];
            $result["post_types"]                 = maybe_unserialize( $result["post_types"] );
            $result["position"]                   = maybe_unserialize( $result["position"] );
            $result["logging_method"]             = maybe_unserialize( $result["logging_method"] );
            $result["allowed_users"]              = maybe_unserialize( $result["allowed_users"] );
            $result["view_allowed_users"]         = maybe_unserialize( $result["view_allowed_users"] );
            $result["comment_view_allowed_users"] = maybe_unserialize( $result["comment_view_allowed_users"] );
            $result["comment_moderation_users"]   = maybe_unserialize( $result["comment_moderation_users"] );
            $result["custom_criteria"]            = maybe_unserialize( $result["custom_criteria"] );
            $result["custom_question"]            = maybe_unserialize( $result["custom_question"] );
            $result["review"]                     = maybe_unserialize( $result["review"] );
            //echo "<pre>"; print_r($result); echo "</pre>";
        } else {
            $results = $wpdb->get_results( $sql, OBJECT );

            if ( empty( $results ) ) {
                return array();
            }

            $result                             = $results[0];
            $result->post_types                 = maybe_unserialize( $result->post_types );
            $result->position                   = maybe_unserialize( $result->position );
            $result->logging_method             = maybe_unserialize( $result->logging_method );
            $result->allowed_users              = maybe_unserialize( $result->allowed_users );
            $result->view_allowed_users         = maybe_unserialize( $result->view_allowed_users );
            $result->comment_view_allowed_users = maybe_unserialize( $result->comment_view_allowed_users );
            $result->comment_moderation_users   = maybe_unserialize( $result->comment_moderation_users );
            $result->custom_criteria            = maybe_unserialize( $result->custom_criteria );
            $result->custom_question            = maybe_unserialize( $result->custom_question );
            $result->review                     = maybe_unserialize( $result->review );
        }

        return $result;
    }

    /**
     * @param $form_id
     * @param string $post_id
     * @param string $user_id
     * @param bool $is_object
     * @return array
     */
    public static function get_ratings( $form_id, $post_id = '', $user_id = '', $is_object = false ) {

        global $wpdb;
        $table_name1 = self::get_user_ratings_table_name();
        $active_clause = ( ! empty( $post_id ) ) ? ( ( is_array( $user_id ) ? " AND ur.post_id IN(%s)" : " AND ur.post_id=%d" ) ) : "";
        $active_clause .= ( ! empty( $user_id ) ) ? ( ( is_array( $user_id ) ? " AND ur.user_id IN(%s)" : " AND ur.user_id=%d" ) ) : "";
        $post_id = ( ( is_array( $post_id ) ? implode( ',', $post_id ) : $post_id ) );
        $user_id = ( ( is_array( $user_id ) ? implode( ',', $user_id ) : $user_id ) );
        $sql = $wpdb->prepare( "SELECT ur.* FROM $table_name1 ur WHERE ur.form_id=%d $active_clause", $form_id, $post_id, $user_id );

        if ( ! $is_object ) {
            $results = $wpdb->get_results( $sql, ARRAY_A );
            //echo '<pre>'; print_r($sql); echo '</pre>'; die();
            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]["rating"]   = maybe_unserialize( $results[$i]["rating"] );
                $results[$i]["question"] = maybe_unserialize( $results[$i]["question"] );
            }
        } else {
            $results = $wpdb->get_results( $sql, OBJECT );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]->rating   = maybe_unserialize( $results[$i]->rating );
                $results[$i]->question = maybe_unserialize( $results[$i]->question );
            }
        }

        return $results;
    }

    /**
     * @param array $form_id
     * @param array $post_id
     * @param array $user_id
     * @param string $user_session
     * @param string $sort
     * @param string $sort_type
     * @param array $limit
     * @param bool $is_object
     * @return array
     */

    public static function get_user_ratings_with_ratingForm( array $form_id = array(), array $post_id = array(), array $user_id = array(), $user_session = '', $sort = 'time', $sort_type = 'DESC', array $limit = array(), $is_object = false ) {
        global $wpdb;

        $table_name1 = self::get_ratingForm_settings_table_name();
        $table_name2 = self::get_user_ratings_table_name();
        $table_name3 = $wpdb->posts;

        $form_id = array_filter( $form_id );
        $post_id = array_filter( $post_id );
        $user_id = array_filter( $user_id );

        $active_clause = ( ! empty( $form_id ) and is_array( $form_id ) ) ? " AND ur.form_id IN ('" . implode( ',', $form_id ) . "')" : "";
        $active_clause .= ( ! empty( $post_id ) and is_array( $post_id ) ) ? " AND ur.post_id IN ('" . implode( ',', $post_id ) . "')" : "";
        $active_clause .= ( ! empty( $user_id ) and is_array( $user_id ) ) ? " AND ur.user_id IN ('" . implode( ',', $user_id ) . "')" : "";

        $active_clause .= ( ( $user_session != '' ) ) ? " AND ur.user_session=%s" : "";

        if ( ! empty( $sort ) and ! empty( $sort_type ) ) {
            if ( $sort == 'time' ) {
                $sortingOrder = 'ORDER BY ur.created ' . $sort_type;
            } elseif ( $sort == 'post_id' ) {
                $sortingOrder = 'ORDER BY p.ID ' . $sort_type;
            } elseif ( $sort == 'post_title' ) {
                $sortingOrder = 'ORDER BY p.post_title ' . $sort_type;
            } elseif ( $sort == 'form_id' ) {
                $sortingOrder = 'ORDER BY ur.form_id ' . $sort_type;
            } elseif ( $sort === 'avg' ) {
                $sortingOrder = 'ORDER BY ur.average ' . $sort_type;
            }
        } else {
            $sortingOrder = '';
        }

        if ( ! empty( $limit ) ) {
            $limitAction = "LIMIT";
            $limitAction .= ( ( isset( $limit['start'] ) and is_numeric( $limit['start'] ) ) ? ' ' . $limit['start'] . ',' : '' );
            $limitAction .= ( ( isset( $limit['offset'] ) and is_numeric( $limit['offset'] ) ) ? ' ' . $limit['offset'] : '' );

            if ( $limitAction == 'LIMIT' ) {
                $limitAction = '';
            }
        }

        $sql = $wpdb->prepare(
            "SELECT ur.*, p.post_title, p.post_type, rs.name, rs.custom_criteria, rs.custom_question FROM $table_name1 rs
                INNER JOIN $table_name2 ur
                INNER JOIN $table_name3 p ON p.ID = ur.post_id
                WHERE rs.id = ur.form_id $active_clause $sortingOrder $limitAction", $form_id, $post_id, $user_id, $user_session
        );

        //echo '<pre>'; print_r($sql); echo '</pre>'; //die();

        if ( ! $is_object ) {
            $results = $wpdb->get_results( $sql, ARRAY_A );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]["rating"]          = maybe_unserialize( $results[$i]["rating"] );
                $results[$i]["custom_criteria"] = maybe_unserialize( $results[$i]["custom_criteria"] );
                $results[$i]["question"]        = maybe_unserialize( $results[$i]["question"] );
                $results[$i]["custom_question"] = maybe_unserialize( $results[$i]["custom_question"] );
            }
        } else {
            $results = $wpdb->get_results( $sql, OBJECT );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]->rating          = maybe_unserialize( $results[$i]->rating );
                $results[$i]->custom_criteria = maybe_unserialize( $results[$i]->custom_criteria );
                $results[$i]->question        = maybe_unserialize( $results[$i]->question );
                $results[$i]->custom_question = maybe_unserialize( $results[$i]->custom_question );
            }
        }

        return $results;
    }

    /**
     * Getting the last review/rating using last review/rating id
     *
     * @param int  $lastCommentID
     * @param bool $is_object
     *
     * @return array/object
     */

    /*
     * @param array  $form_id
     * @param array  $post_id
     * @param array  $user_id
     * @param string $user_session
     * @param string $sort
     * @param string $sort_type
     * @param array  $limit
     *
     * */

    //public static function get_user_ratings_with_ratingForm_lastID(array $form_id=array(), array $post_id=array(), array $user_id=array(), $user_session='', $sort='time', $sort_type='DESC', array $limit=array(), $lastCommentID, $is_object = false) {
    public static function get_user_ratings_with_ratingForm_lastID( $lastCommentID, $is_object = false ) {
        global $wpdb;

        $table_name1 = self::get_ratingForm_settings_table_name();
        $table_name2 = self::get_user_ratings_table_name();
        $table_name3 = $wpdb->posts;


        $sql = $wpdb->prepare(
            "SELECT ur.*, p.post_title, p.post_type, rs.name, rs.custom_criteria, rs.custom_question FROM $table_name1 rs
                INNER JOIN $table_name2 ur
                INNER JOIN $table_name3 p ON p.ID = ur.post_id
                WHERE rs.id = ur.form_id AND ur.id=%d ", $lastCommentID
        );


        if ( $is_object ) {
            $results = $wpdb->get_results( $sql, OBJECT );

            if ( empty( $results ) ) {
                return new object();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]->rating          = maybe_unserialize( $results[$i]->rating );
                $results[$i]->custom_criteria = maybe_unserialize( $results[$i]->custom_criteria );
                $results[$i]->question        = maybe_unserialize( $results[$i]->question );
                $results[$i]->custom_question = maybe_unserialize( $results[$i]->custom_question );
            }

        } else {
            $results = $wpdb->get_results( $sql, ARRAY_A );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]["rating"]          = maybe_unserialize( $results[$i]["rating"] );
                $results[$i]["custom_criteria"] = maybe_unserialize( $results[$i]["custom_criteria"] );
                $results[$i]["question"]        = maybe_unserialize( $results[$i]["question"] );
                $results[$i]["custom_question"] = maybe_unserialize( $results[$i]["custom_question"] );
            }
        }

        // echo '<pre>';
        //print_r($results);
        //echo '</pre>'; die();//

        return $results;
    }

    /*
     *
     */
    public static function get_ratings_summary( array $whereOptions = array(), $sort = 'form_id', $sort_type = 'ASC', $is_object = false, $limit = '' ) {
        global $wpdb;
        //var_dump($whereOptions);
        $table_name1 = self::get_user_ratings_summury_table_name();
        //var_dump($wpdb->posts);
        $table_name2 = $wpdb->posts;
        //var_dump($table_name2);
        $table_name3 = self::get_ratingForm_settings_table_name();

        $userRoleSQL = '';

        if ( ! empty( $whereOptions ) ) {
            $active_clause = ( ! empty( $whereOptions['form_id'] ) and is_array( $whereOptions['form_id'] ) ) ? " AND rs.form_id IN ('" . implode( ',', $whereOptions['form_id'] ) . "')" : "";
            $active_clause .= ( ! empty( $whereOptions['post_id'] ) and is_array( $whereOptions['post_id'] ) ) ? " AND rs.post_id IN ('" . implode( ',', $whereOptions['post_id'] ) . "')" : "";
            $active_clause .= ( ! empty( $whereOptions['post_type'] ) and is_array( $whereOptions['post_type'] ) ) ? " AND rs.post_type IN ('" . implode( ',', $whereOptions['post_type'] ) . "')" : "";
            $active_clause .= ( ! empty( $whereOptions['post_date'] ) and ! is_array( $whereOptions['post_date'] ) ) ? " AND p.post_date > '{$whereOptions['post_date']}'" : "";
        }


        if ( ! empty( $sort ) and ! empty( $sort_type ) ) {
            if ( $sort == 'post_id' ) {
                $sortingOrder = 'ORDER BY p.ID ' . $sort_type;
            } elseif ( $sort == 'post_title' ) {
                $sortingOrder = 'ORDER BY p.post_title ' . $sort_type;
            } elseif ( $sort == 'form_id' ) {
                $sortingOrder = 'ORDER BY rs.form_id ' . $sort_type;
            } elseif ( $sort === 'avg' ) {
                $sortingOrder = 'ORDER BY rs.per_post_rating_summary ' . $sort_type;
            }
        } else {
            $sortingOrder = '';
        }

        if ( ! empty( $limit ) ) {
            $limit = 'LIMIT ' . $limit;
        }
        $form_id = '';
        $post_id = '';
        $sql = $wpdb->prepare(
            "SELECT rs.*, p.post_title, p.post_type, r.name FROM $table_name1 rs
             INNER JOIN $table_name2 p
             INNER JOIN $table_name3 r
             $userRoleSQL
             WHERE p.ID=rs.post_id AND r.id=rs.form_id $active_clause $sortingOrder $limit", $form_id, $post_id
        );

        //echo '<pre>'; print_r($sql); echo '</pre>'; die();

        if ( ! $is_object ) {
            $results = $wpdb->get_results( $sql, ARRAY_A );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]["per_criteria_rating_summary"] = maybe_unserialize( $results[$i]["per_criteria_rating_summary"] );
                $results[$i]["custom_user_rating_summary"]  = maybe_unserialize( $results[$i]["custom_user_rating_summary"] );
                //$rows[ $results[$i]['post_id'] ]["per_criteria_rating_summary"] = maybe_unserialize($results[$i]["per_criteria_rating_summary"]);
            }
        } else {
            $results = $wpdb->get_results( $sql, OBJECT );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]->per_criteria_rating_summary = maybe_unserialize( $results[$i]->per_criteria_rating_summary );
                $results[$i]->custom_user_rating_summary  = maybe_unserialize( $results[$i]->custom_user_rating_summary );
                //$rows[ $results[$i]->post_id ] = new stdClass();
                //$rows[ $results[$i]->post_id ]->per_criteria_rating_summary = maybe_unserialize($results[$i]->per_criteria_rating_summary);
            }
        }

        //var_dump($results);
        return $results;
    }

    /**
     * @param bool $is_object
     * @return array
     */
    public static function get_ratings_summary_with_ratingForms( $is_object = false ) {
        global $wpdb;

        $table_name1 = self::get_user_ratings_summury_table_name();
        $table_name2 = self::get_ratingForm_settings_table_name();

        $sql = $wpdb->prepare(
            "SELECT SUM(rs.per_post_rating_count) AS count, rs.*, r.* FROM $table_name2 r
            LEFT JOIN $table_name1 rs ON r.id=rs.form_id
            GROUP BY r.id
            ORDER BY rs.per_post_rating_count DESC", null
        );

        //echo '<pre>'; print_r($sql); echo '</pre>'; //die();

        if ( ! $is_object ) {
            $results = $wpdb->get_results( $sql, ARRAY_A );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );
            for ( $i = 0; $i < $count; $i ++ ) {
                $results[$i]["per_criteria_rating_summary"] = maybe_unserialize( $results[$i]["per_criteria_rating_summary"] );
                $results[$i]["rating"]                      = maybe_unserialize( $results[$i]["rating"] );
                $results[$i]["custom_criteria"]             = maybe_unserialize( $results[$i]["custom_criteria"] );
                $results[$i]["question"]                    = maybe_unserialize( $results[$i]["question"] );
                $results[$i]["custom_question"]             = maybe_unserialize( $results[$i]["custom_question"] );
            }
        } else {
            $results = $wpdb->get_results( $sql, OBJECT );

            if ( empty( $results ) ) {
                return array();
            }

            $count = sizeof( $results );

            //var_dump($results);
            //echo '<pre>'
            for ( $i = 0; $i < $count; $i ++ ) {

                $results[$i]->per_criteria_rating_summary = maybe_unserialize( $results[$i]->per_criteria_rating_summary );
               // added 26-10
                if(property_exists($results[$i],'rating')){
                   $results[$i]->rating                      = maybe_unserialize( $results[$i]->rating );
               }
                if(property_exists($results[$i],'question')){
                    $results[$i]->question                    = maybe_unserialize( $results[$i]->question );
                }

                $results[$i]->custom_criteria             = maybe_unserialize( $results[$i]->custom_criteria );

                $results[$i]->custom_question             = maybe_unserialize( $results[$i]->custom_question );
            }
        }

        return $results;
    }

    /**
     * @param $ratingForm
     * @return bool
     */
    public static function update_ratingForm( $ratingForm ) {
        global $wpdb;
        //echo '<pre>'; print_r($ratingForm); echo '</pre>'; //die();
        if ( ! empty( $ratingForm ) ) {
            $table_name = self::get_ratingForm_settings_table_name();

            $id = $ratingForm['id'];
            unset( $ratingForm['id'] );

            $fieldTypes = self::check_array_element_value_type( $ratingForm );

            if ( $id == 0 ) {
                $rating_forms = self:: get_ratingForms();
                if(is_array($rating_forms) && count($rating_forms) >= 1){
                    $_can_add_cbratingform = apply_filters('cbraing_add_more_forms' , false);
                }
                else{
                    $_can_add_cbratingform =  true;
                }
                if($_can_add_cbratingform){
                    $success = $wpdb->insert( $table_name, $ratingForm, $fieldTypes );
                    $id      = $wpdb->insert_id;
                }
                else{
                    $success = false;
                    add_filter('cbrating_error' ,array('CBRatingSystemData' , 'cbrating_no_more_forms_error'));
                }

            } else {

                $success = $wpdb->update( $table_name, $ratingForm, array( "id" => $id ) );

            }
        }

        return ( $success !== false ) ? $id : false;
    }
    // edit error msg
    public function cbrating_no_more_forms_error($error){
        return __('No more forms for free version' , 'cbratingsystem');
    }

    /**
     * @param $rating
     * @return bool
     */
    public static function update_rating( $rating ) {
        global $wpdb;
        //echo '<pre>'; print_r($ratingForm); echo '</pre>'; //die();
        if ( ! empty( $rating ) ) {
            $table_name = self::get_user_ratings_table_name();

            $fieldTypes = self::check_array_element_value_type( $rating );

            $success = $wpdb->insert( $table_name, $rating, $fieldTypes );
        }

        return ( $success ) ? $wpdb->insert_id : false;
    }
    /**
     * @param $rating
     * @return bool
     */
    public static function update_rating_comment( $rating ) {
        global $wpdb;
        //echo '<pre>'; print_r($ratingForm); echo '</pre>'; //die();
        if ( ! empty( $rating ) ) {
            $table_name = self::get_user_ratings_table_name();

            $fieldTypes = self::check_array_element_value_type( $rating );

            $success = $wpdb->update( $table_name, $rating, array('id' => $rating['id'], 'post_id' => $rating['post_id'], 'form_id' => $rating['form_id'] ), $fieldTypes, array( '%d', '%d' ) );
        }

        return ( $success ) ? $wpdb->insert_id : false;
    }

    /**
     * @param $rating
     * @return bool
     */
    public static function update_rating_hash( $rating ) {
        global $wpdb;
        //echo '<pre>'; print_r($ratingForm); echo '</pre>'; //die();
        if ( ! empty( $rating ) ) {
            $table_name = self::get_user_ratings_table_name();

            $fieldTypes = self::check_array_element_value_type( $rating );
            $rating_data ['comment_hash'] ='';
            $rating_data ['comment_status'] ='unapproved';
            $success = $wpdb->update( $table_name, $rating_data, array('comment_hash' =>$rating['comment_status'] ), $fieldTypes, array( '%d', '%d' ) );
        }

        return ( $success ) ? $wpdb->insert_id : false;
    }

    /**
     * @param $rating
     * @return bool
     */
    public static function update_rating_summary( $rating ) {
        global $wpdb;
        //echo '<pre>'; print_r($rating); echo '</pre>'; //die();
        if ( ! empty( $rating ) ) {
            $table_name = self::get_user_ratings_summury_table_name();
            $fieldTypes = self::check_array_element_value_type( $rating );

            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $table_name WHERE post_id=%d AND form_id=%d", $rating['post_id'], $rating['form_id'] ) );
            //echo '<pre>Count:'; print_r($count); echo '</pre>'; die();
            if ( $count > 0 ) {
                $success = $wpdb->update( $table_name, $rating, array( 'post_id' => $rating['post_id'], 'form_id' => $rating['form_id'] ), $fieldTypes, array( '%d', '%d' ) );
            } else {
                $success = $wpdb->insert( $table_name, $rating, $fieldTypes );
            }
        }
        //var_dump($success);
        return ( $success ) ? true : false;
    }

    /**
     * @param $id
     * @param $form_id
     * @param $is_active
     * @param $setting
     * @return mixed
     */
    public static function update_feed( $id, $form_id, $is_active, $setting ) {

        global $wpdb;
        $table_name = self::get_paymill_table_name();
        $setting    = maybe_serialize( $setting );
        if ( $id == 0 ) {
            //insert
            $wpdb->insert( $table_name, array( "form_id" => $form_id, "is_active" => $is_active, "meta" => $setting ), array( "%d", "%d", "%s" ) );
            $id = $wpdb->get_var( "SELECT LAST_INSERT_ID()" );
        } else {
            //update
            $wpdb->update( $table_name, array( "form_id" => $form_id, "is_active" => $is_active, "meta" => $setting ), array( "id" => $id ), array( "%d", "%d", "%s" ), array( "%d" ) );
        }

        return $id;
    }

    /**
     * @param array $id
     */
    public static function delete_ratingForm( array $id ) {

        global $wpdb;
        $table_name = self::get_ratingForm_settings_table_name();
        $sql = $wpdb->prepare( "DELETE FROM $table_name WHERE id IN (" . implode( ',', $id ) . ")", null );
        //echo '<pre>'; print_r($sql); echo '</pre>'; die();
        $wpdb->query( $sql );
    }

    /**
     * @param array $ids
     */
    public static function delete_user_rating( array $ids ) {

        global $wpdb;
        $table_name1 = self::get_user_ratings_summury_table_name();
        $table_name = self::get_user_ratings_table_name();
        foreach($ids as $id){
            $sql = $wpdb->prepare( "SELECT post_id ,form_id FROM $table_name1 WHERE id=%d ", $id );
            $results = $wpdb->get_results( $sql,ARRAY_A);
            //echo '<pre>'; print_r($results[0]['form_id']); echo '</pre>';
            $sql = $wpdb->prepare( "DELETE FROM $table_name WHERE post_id=%d AND form_id=%d", $results[0]['post_id'] ,$results[0]['form_id']);
            $results = $wpdb->get_results( $sql,ARRAY_A);
            //echo '<pre>'; print_r($results); echo '</pre>';
        }
       // return 0;
    }

    /**
     * @param array $id
     */
    public static function delete_ratingForm_symmary( array $id ) {

        global $wpdb;
        $table_name = self::get_user_ratings_summury_table_name();
        $sql = $wpdb->prepare( "DELETE FROM $table_name WHERE id IN (" . implode( ',', $id ) . ")", null );
        $wpdb->query( $sql );
       // return 0;
    }

    /**
     * @param array $id
     */
    public static function delete_ratingForm_with_all_ratings( array $id ) {

        global $wpdb;
        $table_name = self::get_ratingForm_settings_table_name();

        $sql = $wpdb->prepare( "DELETE FROM $table_name WHERE id IN (" . implode( ',', $id ) . ")", null );

        $wpdb->query( $sql );

        self::delete_ratingSummary( $id );
        self::delete_ratings( array(), array(), $id );
    }

    /**
     * @param array $form_id
     * @param array $post_id
     * @return mixed
     */
    public static function delete_ratingSummary( array $form_id = array(), array $post_id = array() ) {
        global $wpdb;
        $table_name = self::get_user_ratings_summury_table_name();

        $action = ( ! empty( $form_id ) || ! empty( $post_id ) ) ? 'WHERE' : '';
        $action .= ( ! empty( $form_id ) ) ? ' form_id IN (\'' . implode( ',', $form_id ) . '\') AND' : '';
        $action .= ( ! empty( $post_id ) ) ? ' post_id IN (' . implode( ',', $post_id ) . ') AND' : '';

        if ( substr( $action, - 3 ) == 'AND' ) {
            $action = substr( $action, 0, - 3 );
        }

        $return = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name $action", null ) );

        return $return;
    }

    /**
     * @param array $id
     * @param array $post_id
     * @param array $form_id
     * @param array $ip
     * @return mixed
     */

    public static function delete_ratings( array $id, array $post_id = array(), array $form_id = array(), array $ip = array() ) {

        global $wpdb;
        $table_name = self::get_user_ratings_table_name();

        $action  = ( ! empty( $id ) || ! empty( $post_id ) || ! empty( $form_id ) || ! empty( $ip ) ) ? 'WHERE' : '';
        $action .= ( ! empty( $id ) ) ? ' id IN (' . implode( ',', $id ) . ') AND' : '';
        $action .= ( ! empty( $post_id ) ) ? ' post_id IN (\'' . implode( ',', $post_id ) . '\') AND' : '';
        $action .= ( ! empty( $form_id ) ) ? ' form_id IN (\'' . implode( ',', $form_id ) . '\') AND' : '';
        $action .= ( ! empty( $ip ) ) ? ' user_ip IN (\'' . implode( ',', $ip ) . '\') AND' : '';

        if ( substr( $action, - 3 ) == 'AND' ) {

            $action = substr( $action, 0, - 3 );
        }

        $sql = $wpdb->prepare( "DELETE FROM $table_name $action", null );


        $return = $wpdb->query( $sql );
        // echo '<pre>'; print_r($return); echo '</pre>'; die();
        return $return;
    }

    /**
     * @param array $post_id
     * @param array $form_id
     */
    public static function delete_ratings_log(  array $post_id = array(), array $form_id = array() ) {
        self::delete_ratings(array(),$post_id,$form_id,array());
        self::delete_ratingSummary($form_id,$post_id);

    }

    /**
     *
     */
    public static function drop_tables() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS " . self::get_paymill_table_name() );
    }

    // get forms that are not assigned to feeds
    /**
     * @param string $active_form
     * @return array
     */
    public static function get_available_forms( $active_form = '' ) {

        $forms           = RGFormsModel::get_forms();
        $available_forms = array();

        foreach ( $forms as $form ) {
            $available_forms[] = $form;
        }

        return $available_forms;
    }

    /**
     * @param $array
     * @return array
     */
    public static function check_array_element_value_type( $array ) {
        $ret = array();

        if ( ! empty( $array ) ) {
            foreach ( $array as $val ) {
                $ret[] = self::check_value_type( $val );
            }
        }

        return $ret;
    }

    /**
     * @param $string
     * @return string
     */
    public static function check_value_type( $string ) {
        $t   = gettype( $string );
        $ret = '';

        switch ( $t ) {
            case 'string' :
                $ret = '\'%s\'';
                break;

            case 'integer':
                //$ret = '\'%d\'';
                $ret = '%d';
                break;
        }

        return $ret;
    }

    /**
     * @return string
     */
    public static function get_ratingForm_settings_table_name() {
        global $wpdb;

        return $wpdb->prefix . "cbratingsystem_ratingform_settings";
    }

    /**
     * @return string
     */
    public static function get_user_ratings_table_name() {
        global $wpdb;

        return $wpdb->prefix . "cbratingsystem_user_ratings";
    }

    /**
     * @return string
     */
    public static function get_user_ratings_summury_table_name() {
        global $wpdb;

        return $wpdb->prefix . "cbratingsystem_ratings_summary";
    }
    // get top rated post function added 27-11-14
    /*
   *
   */
    public static function get_top_rated_post( array $whereOptions = array(), $is_object = false, $limit = '' ) {

        global $wpdb;
        //making the options
        $active_clause = '';

        if($whereOptions ['post_filter'] == 'post_id'){
            if($whereOptions ['post_id'] != '' && $whereOptions ['post_id'] != '0'){
                $active_clause .= 'AND post.ID IN ('.($whereOptions['post_id']).') ';
                //var_dump(explode( ',' ,$whereOptions['post_id']));
            }
        }
        else if ($whereOptions ['post_filter'] == 'post_type'){
            //var_dump('i am here');
            if($whereOptions ['post_type'] != '' && $whereOptions ['post_type'] != '0'){
                $active_clause .= 'AND post.post_type ="'.$whereOptions['post_type'].'"';
            }

        }
        if($whereOptions ['user_id'] != ''){
            if($whereOptions ['user_id'] != '' && $whereOptions ['user_id'] != '0'){
                $active_clause .= 'AND post.post_author IN ('.($whereOptions['user_id']).') ';
                //var_dump(explode( ',' ,$whereOptions['user_id']));
            }
        }
       // var_dump($whereOptions ['form_id']);
        if($whereOptions ['form_id'] != '' && $whereOptions ['form_id'] != '0'){
            $active_clause .= 'AND summary.form_id ="'.(int)$whereOptions ['form_id'].'"';
        }
        if(array_key_exists('post_date' , $whereOptions) ){
            $active_clause .= 'AND post.post_date >"'.$whereOptions['post_date'].'"';
        }

        if($whereOptions ['order'] != ''){
            $order_by = $whereOptions ['order'];
        }
        else{
            $order_by = 'DESC';
        }
        if (  $limit != ''  ) {
            $limit = (int) (preg_replace("/[^0-9]/","",$limit) ) ;
            //var_dump($limit);
            $limit = 'LIMIT ' . $limit;
        }

        $posttable     =  $wpdb->prefix . "posts";
        $summarytable  =  self::get_user_ratings_summury_table_name();
        $usertable     =  $wpdb->prefix . "users";
        //$table_name1 =
        $formtable     = self::get_ratingForm_settings_table_name();

        $sql     =   $wpdb->prepare( "SELECT SUM(summary.per_post_rating_summary)/count(summary.post_id) as rating, count(summary.post_id) as post_count,post.post_author  FROM $posttable as post  LEFT JOIN $summarytable as summary ON summary.post_id = post.ID  WHERE  post.post_status = 'publish' $active_clause GROUP BY post.post_author ORDER BY rating $order_by ,post_count  $order_by $limit" , $limit) ;
       // $results = $wpdb->get_results( $sql,ARRAY_A);


        if ( ! $is_object ) {
            $results = $wpdb->get_results( $sql, ARRAY_A );

            if ( empty( $results ) ) {
                return array();
            }


        } else {
            $results = $wpdb->get_results( $sql, OBJECT );

            if ( empty( $results ) ) {
                return array();
            }

        }

        //var_dump($results);
        return $results;
    }

}// end of class