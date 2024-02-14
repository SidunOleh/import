<?php

/**
 * Plugin Name: Import
 * Description: Import data
 * Author: Sidun Oleh
 */

use Import\PostType;
use Import\Taxonomy;
use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Import\Importer\ImporterFactory;

defined('ABSPATH') or die;

/**
 * Plugin root
 */
const IMPORT_ROOT = __DIR__;

/**
 * Composer autoloader
 */
require_once IMPORT_ROOT . '/vendor/autoload.php';

/**
 * Restaurant post type
 */
(new PostType)
    ->name('restaurant')
    ->label(__('Restaurant'))
    ->labelPlular(__('Restaurants'))
    ->menuIcon('dashicons-coffee')
    ->supports(['title', 'editor', 'thumbnail', 'comments',])
    ->rewrite(['slug' => 'ресторан',])
    ->taxonomies([
        'restaurant_category', 
        'restaurant_location', 
        'restaurant_feature',
    ])->register();

function restaurantPostTypeMetafields() {
    Container::make('post_meta', __('Restaurant metafields'))
        ->where('post_type', '=', 'restaurant')
        ->add_tab(__('Gallery'), [
            Field::make('media_gallery', 'gallery', __('Gallery')),
        ])
        ->add_tab(__('Address'), [
            Field::make('text', 'address_country', __('Country')),
            Field::make('text', 'address_locality', __('Locality')),
            Field::make('text', 'address_region', __('Region')),
            Field::make('text', 'address_street', __('Street')),
            Field::make('text', 'address_full', __('Full')),
        ])
        ->add_tab(__('Geo'), [
            Field::make('text', 'geo_latitude', __('Latitude')),
            Field::make('text', 'geo_longitude', __('Longitude')),
        ])
        ->add_tab(__( 'Opening hours'), [
            Field::make('complex', 'opening_hours', __('Opening hours'))
                ->add_fields( [
                    Field::make( 'text', 'day', __('Day') ),
                    Field::make( 'text', 'hours', __('Hours') ),
                ] ),
        ])
        ->add_tab(__('Contact info'), [
            Field::make('text', 'contact_telephone', __('Telephone')),
            Field::make('text', 'contact_website', __('Website')),
            Field::make('text', 'contact_instagram', __('Instagram')),
        ])
        ->add_tab(__('Others'), [
            Field::make('text', 'others_source', __('Source')),
        ]);
}

add_action('carbon_fields_register_fields', 'restaurantPostTypeMetafields');

/**
 * Category taxonomy
 */
(new Taxonomy)
    ->name('restaurant_cuisine')
    ->label(__('Сuisine'))
    ->labelPlular(__('Сuisines'))
    ->postTypes(['restaurant',])
    ->publiclyQueryable(false)
    ->register();

/**
 * Сuisine taxonomy metafields
 */
function сuisineTaxonomyMetafields() {
    Container::make('term_meta', 'Сuisine metafields')
        ->show_on_taxonomy('restaurant_cuisine')
        ->add_fields([
            Field::make('image', 'icon', __('Icon')),
            Field::make('checkbox', 'show_in_sidebar', __('Show in sidebar')),
        ]);
}

add_action('carbon_fields_register_fields', 'сuisineTaxonomyMetafields');

/**
 * Feature taxonomy
 */
(new Taxonomy)
    ->name('restaurant_feature')
    ->label(__('Feature'))
    ->labelPlular(__('Features'))
    ->postTypes(['restaurant',])
    ->publiclyQueryable(false)
    ->register();

/**
 * Feature taxonomy metafields
 */   
function featureTaxonomyMetafields() {
    Container::make('term_meta', 'Feature metafields')
        ->show_on_taxonomy('restaurant_feature')
        ->add_fields([
            Field::make('image', 'icon', __('Icon')),
            Field::make('checkbox', 'show_in_sidebar', __('Show in sidebar')),
        ]);
}

add_action('carbon_fields_register_fields', 'featureTaxonomyMetafields');

/**
 * Location taxonomy
 */
(new Taxonomy)
    ->name('restaurant_location')
    ->label(__('Location'))
    ->labelPlular(__('Locations'))
    ->hierarchical(true)
    ->postTypes(['restaurant',])
    ->publiclyQueryable(false)
    ->register();

/**
 * Dish taxonomy
 */
(new Taxonomy)
    ->name('restaurant_dish')
    ->label(__('Dish'))
    ->labelPlular(__('Dishes'))
    ->postTypes(['restaurant',])
    ->publiclyQueryable(false)
    ->register();
    
/**
 * Comments metafields
 */
function commentsMetafields() {
    Container::make('comment_meta', 'Comment metafields')
        ->add_fields([
            Field::make('text', 'stars', __('Stars')),
            Field::make('text', 'author_img_url', __('Author image url')),
        ]);
}

add_action('carbon_fields_register_fields', 'commentsMetafields');

/**
 * Add import page
 */
function addImportPage() {
    add_submenu_page(
        'edit.php?post_type=restaurant',
        __('Import'),
        __('Import'),
        'manage_options',
        'import',
        function () {
            require_once IMPORT_ROOT . '/templates/import-page.php';
        }
    );
}

add_action('admin_menu', 'addImportPage');

/**
 * Add settings page
 */
function addSettingsPage() {
    add_submenu_page(
        'edit.php?post_type=restaurant',
        __('Settings'),
        __('Settings'),
        'manage_options',
        'settings',
        function () {
            require_once IMPORT_ROOT . '/templates/settings-page.php';
        }
    );
}

add_action('admin_menu', 'addSettingsPage');

/**
 * Update settings
 */
function updateSettings() {
    $settings = $_POST['settings'] ?? [];

    $result = update_option('import_settings', $settings);

    wp_send_json(['status' => $result,]);
    wp_die();
}

add_action('wp_ajax_update_settings', 'updateSettings');

    
/**
 * Import items
 */
function importItems() {
    set_time_limit(0);
    ini_set('memory_limit', -1);

    $urls = $_POST['urls'] ?? [];
    $config = $_POST['config'] ?? [];

    $settings = get_option('import_settings', []);
    $config['twocaptcha_key'] = $settings['twocaptcha_key'] ?? '';
    
    $progress = [
        'total' => count($urls),
        'success' => 0,
        'fail' => 0,
        'failed_imports' => [],
    ]; 
    foreach ($urls as $url) {
        try {
            $importer = ImporterFactory::create($url, $config);
            $importer->import($url);
            
            $progress['success']++;
        } catch (Exception $e) {
            $progress['fail']++;
            $progress['failed_imports'][] = $url;

            error_log(json_encode([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'url' => $url,
                'time' => date('Y-m-d H:i:s'),
                'trace' => $e->getTraceAsString(),
            ]) . PHP_EOL, 3, IMPORT_ROOT . '/logs/error_log');
        }

        sleep(rand(1, 5));
    }

    wp_send_json($progress);
    wp_die();
}   

add_action('wp_ajax_import_items', 'importItems');