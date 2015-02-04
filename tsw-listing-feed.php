<?php
// Add Custom Post Type to a feed
function add_tsw_custom_listing_to_feed( $qv ) {
    if ( isset($qv['feed']) && !isset($qv['post_type']) )
        $qv['post_type'] = array('post', '<custom_listing>');
    return $qv;
}
add_filter( 'request', 'add_tsw_custom_listing_to_feed' );
?>