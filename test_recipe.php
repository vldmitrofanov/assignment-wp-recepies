<?php

defined('ABSPATH') or exit;
/**
 * @package Test Recipe
 */
/*
Plugin Name: Test Recipe
Plugin URI:
Description: Description not yet available. But comming soon!
Version: 0.0.1
Author: Shirker
Author URI: N/A
License: GPLv2 or later
*/

function create_test_recipe_cpt()
{
    global $wpdb;
    $labels = array(
        'name' => _x('Test Recipe', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Test Recipe', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => _x('Test Recipe', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('Test Recipe', 'Add New on Toolbar', 'textdomain'),
        'archives' => __('Test Recipe Archives', 'textdomain'),
        'attributes' => __('Test Recipe Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Test Recipe:', 'textdomain'),
        'all_items' => __('All Test Recipe', 'textdomain'),
        'add_new_item' => __('Add New Test Recipe', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Test Recipe', 'textdomain'),
        'edit_item' => __('Edit Test Recipe', 'textdomain'),
        'update_item' => __('Update Test Recipe', 'textdomain'),
        'view_item' => __('View Test Recipe', 'textdomain'),
        'view_items' => __('View Test Recipe', 'textdomain'),
        'search_items' => __('Search Test Recipe', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into Test Recipe', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this Test Recipe', 'textdomain'),
        'items_list' => __('Test Recipe list', 'textdomain'),
        'items_list_navigation' => __('Test Recipe list navigation', 'textdomain'),
        'filter_items_list' => __('Filter Test Recipe list', 'textdomain'),
    );
    $args = array(
        'label' => __('Test Recipe', 'textdomain'),
        'description' => __('', 'textdomain'),
        'labels' => $labels,
        'menu_icon' => '',
        'supports' => array('title', 'excerpt', 'editor', 'thumbnail'),
        'taxonomies' => array('category', 'post_tag'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'exclude_from_search' => false,
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'register_meta_box_cb' => 'cooking_time_meta_box'
    );
    register_post_type('testrecipe', $args);

    $sql = array();
    $sql[0] = "ALTER TABLE $wpdb->comments ADD rating INT(1) DEFAULT NULL;";
    foreach ($sql as $query) {
        @$wpdb->query($query);
    }
}

add_action('init', 'create_test_recipe_cpt', 0);

function cooking_time_meta_box()
{

    $screens = get_post_types();

    foreach ($screens as $screen) {
        add_meta_box(
            'cooking-time',
            __('Cooking Time (min)', 'textdomain'),
            'cooking_time_meta_box_callback',
            $screen
        );
        add_meta_box(
            'servings',
            __('Servings (for how many people)', 'textdomain'),
            'servings_meta_box_callback',
            $screen
        );
    }
}

function cooking_time_meta_box_callback($post)
{
    $value = get_post_meta($post->ID, '_cooking_time', true);

    echo '<input type="number" style="width:100%" id="cooking_time" name="cooking_time" value="' . esc_attr($value) . '" required />';
}

function servings_meta_box_callback($post)
{
    $value = get_post_meta($post->ID, '_servings', true);

    echo '<input type="number" style="width:100%" id="servings" name="servings" value="' . esc_attr($value) . '" required />';
}

function save_cooking_time_meta_box_data($post_id)
{
    if (!isset($_POST['cooking_time'])) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {

        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Sanitize user input.
    $my_data = sanitize_text_field($_POST['cooking_time']);

    // Update the meta field in the database.
    update_post_meta($post_id, '_cooking_time', $my_data);
}

add_action('save_post', 'save_cooking_time_meta_box_data');

function save_servings_meta_box_data($post_id)
{

    // Check if our nonce is set.
    if (!isset($_POST['servings'])) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {

        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Sanitize user input.
    $my_data = sanitize_text_field($_POST['servings']);

    // Update the meta field in the database.
    update_post_meta($post_id, '_servings', $my_data);
}

add_action('save_post', 'save_servings_meta_box_data');

function get_test_recipies_query($limit_string = 10, $order_by = null, $extra_query = '', $collection = true)
{
    global $wpdb;
    $order_by = empty($order_by) ? 'p.post_modified_gmt DESC' : $order_by;
    $if_content = $collection ? '' : 'p.post_content,';
    return "SELECT p.ID as id, p.post_title, p.post_author as user_id, p.post_excerpt, p.post_date_gmt, p.post_excerpt, " .
        "$if_content p.post_name as slug, p.post_modified_gmt, p.comment_count, " .
        "u.display_name as user_display_name, " .
        "MAX(CASE WHEN pm1.meta_key = '_cooking_time' then pm1.meta_value ELSE NULL END) as cooking_time, " .
        "MAX(CASE WHEN pm1.meta_key = '_servings' then pm1.meta_value ELSE NULL END) as servings, " .
        "MAX(wt.slug) as tag, " .
        "MAX(CASE WHEN wp.post_mime_type like 'image%' then wp.guid ELSE NULL END) as image, " .
        "(select avg(rating) as rating from " . $wpdb->comments . " where comment_post_ID = p.id and comment_approved>0) as rating " .
        "FROM $wpdb->posts AS p " .
        "INNER JOIN $wpdb->users AS u ON p.post_author = u.ID " .
        "LEFT JOIN $wpdb->postmeta AS pm1 ON ( pm1.post_id = p.ID) " .
        "LEFT JOIN $wpdb->posts wp ON p.id = wp.post_parent " .
        "LEFT JOIN $wpdb->term_relationships wtr ON p.ID = wtr.object_ID ".
        "LEFT JOIN $wpdb->term_taxonomy wtt ON wtr.term_taxonomy_id = wtt.term_taxonomy_id ".
        "LEFT JOIN $wpdb->terms wt ON wt.term_id = wtt.term_id ".
        "LEFT JOIN $wpdb->comments AS wpc ON ( wpc.comment_post_ID = p.ID) " .
        "WHERE p.post_type = 'testrecipe' AND p.post_status='publish' " .
        $extra_query . " " .
        "GROUP BY p.ID,p.post_title " .
        "ORDER BY " . $order_by . " LIMIT " . $limit_string;
}

function get_all_test_recipies($request)
{
    global $wpdb;

    $number_of_result = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts AS p WHERE p.post_type = 'testrecipe' AND p.post_status='publish'");

    if ($request->get_param('results_per_page') == null) {
        $results_per_page = 3;
    } else {
        $results_per_page = (int) $request->get_param('results_per_page');
    }

    $number_of_pages = ceil($number_of_result / $results_per_page);

    if ($request->get_param('page') == null) {
        $page = 1;
    } else {
        $page = (int) $request->get_param('page');
    }

    if ($request->get_param('order_by') == null) {
        $order_by = 'p.post_modified_gmt DESC';
    } else {
        $order_by = sanitize_text_field($request->get_param('order_by')); //"rating DESC"
    }

    $page_first_result = ($page - 1) * $results_per_page;

    $query = get_test_recipies_query($page_first_result . ',' . $results_per_page, $order_by);

    $posts_data = $wpdb->get_results($query);
    //return $query;

    return  array('data' => $posts_data, 'page' => $page, 'pages' => $number_of_pages, 'results_per_page' => $results_per_page);
}

function get_featured_test_recipies($request) {
    global $wpdb;
    $order_by = "p.comment_count DESC";
    $extra_query = "AND wpc.rating >= 4 ";
    $query = get_test_recipies_query(5, $order_by, $extra_query);
    $posts_data = $wpdb->get_results($query);
    return  array('data' => $posts_data);
}

function search_test_recipies($request)
{
    global $wpdb;

    $results_per_page = 10;

    $order_by = 'p.post_title DESC';

    if ($request->get_param('q') == null) {
        return array();
    } else {
        $st = trim(sanitize_text_field($request->get_param('q')));
        $st = preg_replace('!\s+!', '%', $st);
        $extra_query = " AND p.post_title LIKE '%$st%' "; //"rating DESC"
    }

    $query = get_test_recipies_query($results_per_page, $order_by, $extra_query);

    $posts_data = $wpdb->get_results($query);

    return $posts_data;
}

function get_test_recipe_by_id($request)
{
    global $wpdb;
    $id = (int) $request['id'];
    $query = get_test_recipies_query(1, null, " AND p.ID = $id ", false);

    $posts_data = $wpdb->get_results($query);

    if (empty($posts_data)) {
        return new WP_Error('no_posts', __('No post found'), array('status' => 404));
    }
    $post = $posts_data[0];

    $comments = get_comments([
        'post_id' => $id,
        'number' => '3'
    ]);
    $post->comments = $comments;
    return $post;
}

function comment_test_recipe($request)
{
    global $wpdb;
    $id = (int) $request['id'];
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts  WHERE ID = $id AND post_type = 'testrecipe' AND post_status='publish'");

    if (empty($count)) {
        return new WP_Error('no_posts', __('No post found'), array('status' => 404));
    }
    $comment_author_email = trim(sanitize_text_field($request->get_param('email')));
    $comment_author = trim(sanitize_text_field($request->get_param('name')));
    $comment_content = trim(sanitize_text_field($request->get_param('comment')));
    $rating = trim(sanitize_text_field($request->get_param('rating')));
    $comment = array(
        'comment_author' => $comment_author,
        'comment_post_ID' => $id,
        'comment_type' => 'comment',
        'comment_content' => $comment_content,
        'comment_author_email' => $comment_author_email
    );
    $comment_id =  wp_insert_comment($comment);
    if (!empty($comment_id)) {
        $query = "UPDATE $wpdb->comments SET rating=$rating WHERE comment_ID=$comment_id";
        $wpdb->query($query);
    }
    return ['status' => 'success'];
}


add_action('rest_api_init', function () {
    register_rest_route('test_recipe', '/all', array(
        'methods' => 'GET',
        'callback' => 'get_all_test_recipies',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('test_recipe', '/featured', array(
        'methods' => 'GET',
        'callback' => 'get_featured_test_recipies',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('test_recipe', '/search', array(
        'methods' => 'POST',
        'callback' => 'search_test_recipies',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('test_recipe', '/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_test_recipe_by_id',
        'args' => array(
            'id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('test_recipe', '/comment/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'comment_test_recipe',
        'args' => array(
            'id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
            'name' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'The commentator\'s name',
            ),
            'email' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'The commentator\'s name',
            ),
            'comment' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'The commentator\'s name',
            ),
            'rating' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param) && ($param > 0 && $param <= 5);
                }
            ),
        ),
    ));
});
