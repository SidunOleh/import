<?php

namespace Import\Descriptions;

use WP_Term;

defined('ABSPATH') or die;

class CuisineGenerator extends Generator
{
    protected function rules(WP_Term $term): array
    {
        return [ 
            [
                'cuisine' => $term->slug,
            ], 
            [
                'cuisine' => '*',
            ], 
        ];
    }

    protected function replaceVars(string $template, WP_Term $term): string
    {        
        $template = preg_replace(
            '/{cuisine}/',
            $term->name, 
            $template
        );

        return $template;
    }
}