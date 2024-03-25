<?php

namespace Import\Descriptions;

use WP_Term;

defined('ABSPATH') or die;

class CuisineLocationGenerator extends Generator
{
    protected function rules(WP_Term $term): array
    {
        $slug = explode('_', $term->slug);

        return [
            [
                'cuisine' => $slug[0],
                'location' => $slug[1],
            ],
            [
                'cuisine' => $slug[0],
                'location' => '*',
            ],
            [
                'cuisine' => '*',
                'location' => $slug[1],
            ],
            [
                'cuisine' => '*',
                'location' => '*',
            ],
        ];
    }

    protected function replaceVars(string $template, WP_Term $term): string
    {
        $name = explode('-', $term->name);
        
        $template = preg_replace(
            '/{cuisine}/', 
            $name[0], 
            $template
        );
        $template = preg_replace(
            '/{location}/',
            $name[1], 
            $template
        );

        return $template;
    }
}