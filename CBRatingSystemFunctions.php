<?php

/**
 * Class CBRatingSystemFunctions
 */
class CBRatingSystemFunctions{
    /**
     * @param $text
     * @param $path
     * @param array $options
     * @return string
     */
    public static function cb_l( $text, $path, array $options = array() ) {
        // Merge in defaults.
        $options += array(
            'attributes' => array(),
            'html'       => false,
        );
        if ( isset( $options['attributes']['title'] ) && strpos( $options['attributes']['title'], '<' ) !== false ) {

            $options['attributes']['title'] = strip_tags( $options['attributes']['title'] );
        }
            return '<a href="' . self::check_plain( $path ) . '"' . self::prepare_attributes( $options['attributes'] ) . '>' . ( $options['html'] ? $text : self::check_plain( $text ) ) . '</a>';
    }

    /**
     * @param array $attributes
     * @return string
     */

    public static function prepare_attributes( array $attributes = array() ) {

        foreach ( $attributes as $attribute => &$data ) {

            $data = implode( ' ', (array) $data );
            $data = $attribute . '="' . self::check_plain( $data ) . '"';
        }

        return $attributes ? ' ' . implode( ' ', $attributes ) : '';
    }

    /**
     * @param $text
     * @return string
     */
    public static  function check_plain( $text ) {

        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }

    /**
     * @param $value
     * @return array|string
     */
    public static  function stripslashes_multiarray( $value ) {

        $value = is_array( $value ) ?
            array_map( array('CBRatingSystemFunctions','stripslashes_multiarray'), $value ) :
            stripslashes( $value );

        return $value;
    }

    /**
     * @param $string
     * @param int $size
     * @param string $readMoreText
     * @param string $wrapperClass
     * @return array
     */

    public static function text_summary_mapper( $string, $size = 200, $readMoreText = '...More', $wrapperClass = '' ) {

        $stringSize = strlen( $string );
        $summury    = array();

        if ( $stringSize <= ( $size + 20 ) ) {
            return array( 'summury' => $string );
	    }

        $summury['summury'] = substr( $string, 0, $size );
        $summury['rest']    = substr( $string, - $size );

        return $summury;
    }

    /**
     * @param $time
     * @return string
     */
    public static function codeboxr_time_elapsed_string( $time ) {

        if ( ! is_numeric( $time ) ) {

            $time = strtotime( $time );
        }

        $periods = array( "second", "minute", "hour", "day", "week", "month", "year", "decade", "age" );
        $lengths = array( "60", "60", "24", "7", "4.35", "12", "10", "100" );

        $now = time();

        $difference = $now - $time;

        if ( $difference <= 10 && $difference >= 0 ) {

            return $tense = 'just now';

        } elseif ( $difference > 0 ) {

            $tense = 'ago';

        } elseif ( $difference < 0 ) {

            $tense = 'later';
        }

        for ( $j = 0; $difference >= $lengths[$j] && $j < count( $lengths ) - 1; $j ++ ) {
            $difference /= $lengths[$j];
        }

        $difference = round( $difference );

        $period     = $periods[$j] . ( $difference > 1 ? 's' : '' );

        return "{$difference} {$period} {$tense} ";
    } //end of codeboxr_time_elapsed_string


}// end of class


