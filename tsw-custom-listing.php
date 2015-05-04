<?php
/*
Plugin Name: TSW Custom Listing
Plugin URI: https://themes.tradesouthwest.com/plugins/TSW-Custom-Listing/
Description: Custom post type plugin for posting classifieds like articles to theme LarrysList. Add-ons available at http://themes.tradesouthwest.com
Author: Larry Judd Oliver
Author URI: http://tradesouthwest.com/
Version: 1.1.0
License: GNU General Public License v3.0
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/*  Copyright 2014  Tradesouthwest  (email : larry@tradesouthwest.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * tsw custom profile post type function.
 * 
 * This function creates a new post type for WordPress theme Jacqui and is specific to this theme.
 *
 * @since 1.0.0
 *
 */

// create custom post type for menu Custom Listing

add_action( 'init', 'create_post_type' );

function create_post_type() {
register_post_type( 'listing',
    array( 'labels' => array( 
        'name' => 'Custom Listings',
        'singular_name' => 'Custom Listing',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Listing - Excerpt is first 50 words (about 3 lines)',
        'edit_item' => 'Edit Listing',
        'new_item' => 'New Listing',
        'view_item' => 'View Listing',
        'search_items' => 'Search Listing',
        'not_found' => 'No custom listings found',
        'not_found_in_trash' => 'No custom listings found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Custom Listings'
        ),
        'hierarchical' => true,
        'description' => 'Custom post listing will only work on this theme.',
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'taxonomies' => array( 'post_tag', 'tsw-taxonomy' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 45,
        'menu_icon' => plugins_url('tsw-custom-listing/icon_pin24.png'),
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'map_meta_cap' => true,
	'hierarchical' => true,
        'rewrite' => false,
        'query_var' => true,
	'delete_with_user' => true,
        'can_export' => true,
        'capability_type' => 'post',
        )
    );

}

add_filter( 'map_meta_cap', 'listing_map_meta_cap', 10, 4 );

function listing_map_meta_cap($caps, $cap, $user_id, $args)
{

    if ( 'edit_listing' == $cap || 'delete_listing' == $cap || 'read_listing' == $cap ) {
        $post = get_post( 'listing' );
        $post_type = get_post_type_object( $post->post_type );
        $caps = array();
    }

    if ( 'edit_listing' == $cap ) {
        if ( $user_id == $post->post_author )
            $caps[] = $post_type->cap->edit_post;
        else
            $caps[] = $post_type->cap->edit_others_post;
    }

    elseif ( 'delete_listing' == $cap ) {
        if ( $user_id == $post->post_author )
            $caps[] = $post_type->cap->delete_post;
        else
            $caps[] = $post_type->cap->delete_others_post;
    }

    elseif ( 'read_listing' == $cap ) {
        if ( 'private' != $post->post_status )
            $caps[] = 'read';
        elseif ( $user_id == $post->post_author )
            $caps[] = 'read';
        else
            $caps[] = $post_type->cap->read_private_posts;
    }

    return $caps;
}

/**
 * User Manages their Media Only
 * @WP_User
 */
add_action('pre_get_posts','tsw_users_own_attachments');
function tsw_users_own_attachments( $wp_query_obj ) {

    global $current_user, $pagenow;

    if( !is_a( $current_user, 'WP_User->ID') )
        return;

    if( (   'edit.php' != $pagenow ) &&
    (   'upload.php' != $pagenow ) &&
    ( ( 'admin-ajax.php' != $pagenow ) || ( $_REQUEST['action'] != 'query-attachments' ) ) )
    return;

    if( !current_user_can('delete_pages') )
        $wp_query_obj->set('author', $current_user->id );

    return;
}
/**
 * sets only user posts as available to edit in admin
 */
function tsw_posts_for_current_author($query) {
	global $user_level;

	if($query->is_admin && $user_level < 5) {
		global $user_ID;
		$query->set('author',  $user_ID);
		unset($user_ID);
	}
	unset($user_level);

	return $query;
}
add_filter('pre_get_posts', 'tsw_posts_for_current_author');

function tsw_custom_tax_init(){

  //set some options for our new custom taxonomy
  $args = array(
    'label' => __( 'Custom Listing Category' ),
    'hierarchical' => true,
    'capabilities' => array(
      // allow anyone editing posts to assign terms
      'assign_terms' => 'edit_posts',
      /* 
      * but you probably don't want anyone except 
      * admins messing with what gets auto-generated! 
      */
      'edit_terms' => 'administrator'
    )
  );

  /* 
  * create the custom taxonomy and attach it to
  * custom post type A 
  */
  register_taxonomy( 'tsw-taxonomy', 'listing', $args);
}

add_action( 'init', 'tsw_custom_tax_init' );

/**
 * adding styles to users admin panel
 */
function custom_colors() {
    if ( !current_user_can('update_plugins')) {
        echo '<style type="text/css">
           #adminmenuback, #adminmenuwrap{background:#d8d8d8}
           #adminmenu li{background:#a94242}
           #dashboard_right_now{display:none}
           span.ab-icon {display:none}
           #footer-upgrade{display:none}
           #wp-version-message{display:none}
           .inside a:first-child{font-size:19px}
         </style>';
}
}

add_action('admin_head', 'custom_colors');

// custom admin login logo
function custom_login_logo() {
if ( !current_user_can('update_plugins')) {
$url = plugins_url('tsw-custom-listing/custom-login-logo.png');
	echo '<style type="text/css">
	h1 a { background-image: url($url) }
	</style>';
  }
}
add_action('login_head', 'custom_login_logo');

/**
 * remove admin widgets from normal users dashboard
 * add custom widgets to display user's posts
 */
add_action('wp_dashboard_setup', 'tsw_custom_dashboard_widgets');

function tsw_custom_dashboard_widgets() {
if ( !current_user_can('update_plugins')) {
    global $wp_meta_boxes;
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_right_now']);
}
    wp_add_dashboard_widget('custom_help_widget', 'Currently Listed', 'tsw_custom_dashboard_help');
}

function tsw_custom_dashboard_help() {
global $current_user;
      get_currentuserinfo();
        $author_query = array(
        'author' => $current_user->ID,
        'post_type'=>'listing'
        );
$author_posts = new WP_Query($author_query); ?>
<h3> <?php echo $current_user->user_login ?> </h3>
<?php
if ( $author_posts->have_posts() ) : while ( $author_posts->have_posts() ) : $author_posts->the_post(); ?>
<li><a href="<?php the_permalink() ?>" rel="bookmark" title=" <?php the_title_attribute(); ?>"><?php the_title(); ?></a></li>                       
<?php endwhile; ?>
<?php endif; wp_reset_postdata(); 
}

/**
 * custom widget to display custom post stats on dashboard
 */ 
    // wp_dashboard_setup is the action hook
add_action('wp_dashboard_setup', 'tsw_custom_dashboard_stats');

// add dashboard widget
function tsw_custom_dashboard_stats() {
    wp_add_dashboard_widget('listing_stat_widget', 'Listing Stats','tsw_dashboard_custom_post_types');
}
function tsw_dashboard_custom_post_types() {
 $authorid = get_current_user_id();
        query_posts(array( 
            'post_type' => 'listing',
            'author' => $authorid,
        ) ); 
            $count = 0;
            while (have_posts()) : the_post(); 
                $count++; 
            endwhile;
            echo '<h3>' . $count ;
            echo ' Listings</h3>';
        wp_reset_query();
 
} 
require_once dirname( __FILE__ ) . '/tsw-listing-feed.php';
?>
