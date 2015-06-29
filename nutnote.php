<?php
/*
	Plugin Name: NutNote Plugin for wordpress
	Plugin URI: http://www.nutnote.com/wordpress-plugin
	Description: Nutnote plugin for wordpress
	Version: 1.1.2
	Author: nutnote.com
	Author URI: http://www.nutnote.com/
*/
define( "NUTNOTE_API_VERSION", "1.1.2" );

define( 'POSTS_PER_PAGE', 20 );
define('NUTNOTE_TOP_ITEM_NUM', 4);

$dir = json_api_dir();
@include_once "$dir/singletons/api.php";
@include_once "$dir/singletons/query.php";
@include_once "$dir/singletons/introspector.php";
@include_once "$dir/singletons/response.php";
@include_once "$dir/models/post.php";
@include_once "$dir/models/comment.php";
@include_once "$dir/models/category.php";
@include_once "$dir/models/tag.php";
@include_once "$dir/models/author.php";
@include_once "$dir/models/attachment.php";
@include_once "$dir/dfi/dynamic-featured-image.php";

function json_api_init() {
    global $json_api;
    if ( phpversion() < 5 ) {
        add_action( 'admin_notices', 'json_api_php_version_warning' );

        return;
    }
    if ( ! class_exists( 'JSON_API' ) ) {
        add_action( 'admin_notices', 'json_api_class_warning' );

        return;
    }
    // add Side Menu Location
    register_nav_menus(
        array(
            'nutnote_navigation' => __( 'NutNote Side Menu' )
        )
    );
    // add category and tag to page
    register_taxonomy_for_object_type( 'post_tag', 'page' );
    register_taxonomy_for_object_type( 'category', 'page' );

    add_filter( 'rewrite_rules_array', 'json_api_rewrites' );
    $json_api = new JSON_API();


    global $dynamic_featured_image;
    $dynamic_featured_image = new Dynamic_Featured_Image();
}

function json_api_php_version_warning() {
    echo "<div id=\"json-api-warning\" class=\"updated fade\"><p>Sorry, NutNote requires PHP version 5.0 or greater.</p></div>";
}

function json_api_class_warning() {
    echo "<div id=\"json-api-warning\" class=\"updated fade\"><p>Oops, NutNote class not found. If you've defined a JSON_API_DIR constant, double check that the path is correct.</p></div>";
}

function json_api_activation() {
    // Add the rewrite rule on activation
    global $wp_rewrite;
    add_filter( 'rewrite_rules_array', 'json_api_rewrites' );
    $wp_rewrite->flush_rules();
}

function json_api_deactivation() {
    // Remove the rewrite rule on deactivation
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

function json_api_rewrites( $wp_rules ) {
    $base = get_option( 'json_api_base', 'api' );
    if ( empty( $base ) ) {
        return $wp_rules;
    }
    $json_api_rules = array(
        "$base\$"      => 'index.php?json=info',
        "$base/(.+)\$" => 'index.php?json=$matches[1]'
    );

    return array_merge( $json_api_rules, $wp_rules );
}

function json_api_dir() {
    if ( defined( 'JSON_API_DIR' ) && file_exists( JSON_API_DIR ) ) {
        return JSON_API_DIR;
    } else {
        return dirname( __FILE__ );
    }
}

// Add initialization and activation hooks
add_action( 'init', 'json_api_init' );
register_activation_hook( "$dir/nutnote.php", 'json_api_activation' );
register_deactivation_hook( "$dir/nutnote.php", 'json_api_deactivation' );

?>
