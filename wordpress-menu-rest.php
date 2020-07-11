<?php
/*
Plugin Name: Wordpress Menu Rest
Plugin URI:
Description:
Author: Hampus Backman
Version: 1.0
Author URI: http://hbackman.se
*/

define('WORDPRESS_MENU_REST_VERSION', '1.0');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getMenu(WP_REST_Request $request) {
    // Attempt to get all menus matching the
    // given slug in the request
    $menu = wp_get_nav_menus([
        'slug' => $request['slug'],
    ]);

    // If there's no menu items matching the slug,
    // then we need to return a 404
    if (count($menu) <= 0) {
        return new WP_Error(
            'rest_menu_invalid_slug',
            'Menu could not be found',
            [ 'status' => 404 ]
        );
    }

    // Grab the first menu
    $menu = $menu[0];

    // Get the items from the given menu
    $itemCollection = wp_get_nav_menu_items($request['slug']);
    $itemMapFn =
        function (WP_Post $item) {
            return [
                'id'                => $item->ID,
                'title'             => $item->title,
                'menu_order'        => $item->menu_order,
                'menu_item_parent'  => $item->menu_item_parent,
                'object_id'         => $item->object_id,
                'object_type'       => $item->object,
                'url'               => $item->url,
            ];
        };

    return [
        'id'            => $menu->slug,
        'name'          => $menu->name,
        'slug'          => $menu->slug,
        'description'   => $menu->description,
        'items'         => array_map($itemMapFn, $itemCollection),
        '_links'        => [
            'self' => [
                [ 'href' => site_url().'/wp-json/wp/v2/menus/'.$menu->slug ],
            ],
            'collection' => [
                [ 'href' => site_url().'/wp-json/wp/v2/menus' ],
            ],
        ],
    ];
}

function getMenus()
{
    $menuCollection = wp_get_nav_menus();
    $menuMapFn =
        function (WP_Term $menu) {
            return [
                'id'            => $menu->slug,
                'name'          => $menu->name,
                'slug'          => $menu->slug,
                'description'   => $menu->description,
                '_links'        => [
                    'self' => [
                        [ 'href' => site_url().'/wp-json/wp/v2/menus/'.$menu->slug ],
                    ],
                    'collection' => [
                        [ 'href' => site_url().'/wp-json/wp/v2/menus' ],
                    ],
                ],
            ];
        };

    return array_map($menuMapFn, $menuCollection);
}

add_action(
    'rest_api_init',
    function ()
    {
        register_rest_route('wp/v2', 'menus', array(
            'methods' => 'GET',
            'callback' => 'getMenus',
        ));
        register_rest_route('wp/v2', 'menus/(?P<slug>[a-zA-Z0-9-]+)', array (
            'methods' => 'GET',
            'callback' => 'getMenu',
            'args' => [
                'slug',
            ],
        ));
    }
);