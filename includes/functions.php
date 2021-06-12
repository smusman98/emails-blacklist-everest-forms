<?php

/**
 * Save and Update Blacklisted forms
 * @param $value array
 * @since 1.0
 * @version 1.0
 */
if( !function_exists( 'efeb_update_blacklist' ) ):
function efeb_update_blacklist( $value )
{
    $blacklist = array();

    if( !empty( $value ) )
    {
        if ( get_option( 'efeb_blacklist' ) )
        {
            $blacklist = get_option( 'efeb_blacklist' );

            $blacklist[$value['form_id']] = $value;
        }
        else
            $blacklist[$value['form_id']] = $value;

        update_option( 'efeb_blacklist', $blacklist );
    }
}
endif;

/**
 * Returns Blacklisted forms
 * @return bool|mixed|void
 * @since 1.0
 * @version 1.0
 */
if ( !function_exists( 'efeb_get_blacklist' ) ):
function efeb_get_blacklist()
{
    if ( get_option( 'efeb_blacklist' ) )
        return get_option( 'efeb_blacklist' );

    return false;
}
endif;

/**
 * Remove form from blacklist
 * @param $form_id
 * @return bool
 * @since 1.0
 * @version 1.0
 */
if ( !function_exists( 'efeb_delete_blacklisted_form' ) ):
    function efeb_delete_blacklisted_form( $form_id )
{
    if ( get_option( 'efeb_blacklist' ) )
    {
        $blacklist = get_option( 'efeb_blacklist' );

        unset( $blacklist[$form_id] );

        update_option( 'efeb_blacklist', $blacklist );
    }

    return false;
}
endif;

