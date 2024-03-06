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
        'restaurant_cuisine', 
        'restaurant_location', 
        'restaurant_feature',
        'restaurant_cuisine_location',
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
                    Field::make('text', 'day', __('Day')),
                    Field::make('text', 'hours', __('Hours')),
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
 * Cuisine taxonomy
 */
(new Taxonomy)
    ->name('restaurant_cuisine')
    ->label(__('Сuisine'))
    ->labelPlular(__('Сuisines'))
    ->postTypes(['restaurant',])
    ->rewrite(['slug' => 'kuchnja',])
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
 * Location taxonomy
 */
(new Taxonomy)
    ->name('restaurant_location')
    ->label(__('Location'))
    ->labelPlular(__('Locations'))
    ->hierarchical(true)
    ->postTypes(['restaurant',])
    ->rewrite(['slug' => 'lokacija',])
    ->register();

/**
 * Cuisine-Location taxonomy
 */
(new Taxonomy)
    ->name('restaurant_cuisine_location')
    ->label(__('Cuisine-Location'))
    ->labelPlular(__('Cuisines-Locations'))
    ->postTypes(['restaurant',])
    ->rewrite(['slug' => 'kuchnja-lokacija',])
    ->register();

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
 * Generate Cuisine-Location terms
 */
function generateCuisineLocationTerms() {
    $cuisines = get_terms( [
        'taxonomy' => 'restaurant_cuisine',
        'hide_empty' => false,
    ] );
    $locations = get_terms( [
        'taxonomy' => 'restaurant_location',
        'hide_empty' => false,
    ] );
    foreach ($cuisines as $cuisine) {
        foreach ($locations as $location) {
            $name = $cuisine->name . '-' .$location->name;
            $slug = $cuisine->slug . '_' .$location->slug;

            if (get_term_by('slug', $slug)) {
                continue;
            }
           
            $term = wp_insert_term($name, 'restaurant_cuisine_location', [
                'slug' => $slug,
            ]);
           
            if ($term instanceof WP_Error) {
                continue;
            }
           
            $posIds = get_posts([
                'post_type' => 'restaurant',
                'fields' => 'ids',
                'posts_per_page'  => -1,
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'restaurant_cuisine',
                        'field' => 'id',
                        'terms' => $cuisine->term_id, 
                    ],
                    [
                        'taxonomy' => 'restaurant_location',
                        'field' => 'id',
                        'terms' => $location->term_id, 
                    ],
                ],
            ]);
            array_map(function ($postId) use($term) {
                wp_set_post_terms($postId, [$term['term_id']], 'restaurant_cuisine_location');
            }, $posIds);
        }
    }

    wp_send_json_success();
    wp_die();
}

add_action('wp_ajax_generate_cuisine_location_terms', 'generateCuisineLocationTerms');

/**
 * Generate Cuisine-Location terms button
 */
function addGenerateCuisineLocationTermsBtn() {
   ?>
    <button id="gen-terms" class="button button-primary">
        <?php _e('Generate terms') ?>
    </button>

    <script>
        const genTermsBtn = document.querySelector('#gen-terms')
        genTermsBtn.addEventListener('click', (e) => {
            genTermsBtn.setAttribute('disabled', 'disabled')
            fetch('/wp-admin/admin-ajax.php?action=generate_cuisine_location_terms', {
                method: 'POST',
            }).then(res => {
                alert('Successfully generated.')
            }).catch(err => {
                alert(err)
            }).finally(() => {
                genTermsBtn.removeAttribute('disabled')
            })
        })
    </script>
   <?php
}

add_action('restaurant_cuisine_location_add_form', 'addGenerateCuisineLocationTermsBtn');

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

    update_option('import_settings', $settings);

    wp_send_json_success();
    wp_die();
}

add_action('wp_ajax_update_settings', 'updateSettings');

/**
 * Generate description
 */
function generateDescription(): string {
    $settings = get_option('import_settings', []);
    $templates = $settings['description_templates'] ?? [];
    
    if (! $templates) {
        return '';
    }

    $template = $templates[rand(0, count($templates) - 1)];

    $description = preg_replace_callback('(\[.*?\])', function ($matches) {
        $words = explode('|', trim($matches[0], '[]'));
        $word = $words[rand(0, count($words) - 1)];
    
        return $word;
    }, $template);

    return $description;
}

/**
 * Generate terms descriptions
 */
function generateTermsDescriptions() {
    $terms = get_terms( [
        'taxonomy' => $_POST['tax'],
        'hide_empty' => false,
    ] );

    var_dump($terms);

    foreach ($terms as $term) {
        wp_update_term($term->term_id, $term->taxonomy, [
            'description' => generateDescription(),
        ]);
    }

    wp_send_json_success();
    wp_die();
}

add_action('wp_ajax_generate_terms_descs', 'generateTermsDescriptions');

/**
 * Add generate descriptions button
 */
function addGenerateDescriptionBtn() {
    ?>
    <button id="gen-descs" class="button button-primary">
        <?php _e('Generate descriptions') ?>
    </button>

    <script>
        const genDescsBtn = document.querySelector('#gen-descs')
        genDescsBtn.addEventListener('click', (e) => {
            genDescsBtn.setAttribute('disabled', 'disabled')
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=generate_terms_descs&tax=<?php echo $_GET['taxonomy'] ?>',
            }).then(res => {
                alert('Successfully generated.')
            }).catch(err => {
                alert(err)
            }).finally(() => {
                genDescsBtn.removeAttribute('disabled')
            })
        })
    </script>
   <?php
}

add_action('restaurant_cuisine_add_form', 'addGenerateDescriptionBtn');
add_action('restaurant_location_add_form', 'addGenerateDescriptionBtn');
add_action('restaurant_cuisine_location_add_form', 'addGenerateDescriptionBtn');
    
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