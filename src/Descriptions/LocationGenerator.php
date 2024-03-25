<?php

namespace Import\Descriptions;

use WP_Term;

defined('ABSPATH') or die;

class LocationGenerator extends Generator
{
    protected function rules(WP_Term $term): array
    {
        return [
            [
                'location' => $term->slug,
            ],
            [
                'location' => '*',
            ],
        ];
    }

    protected function replaceVars(string $template, WP_Term $term): string
    {        
        $template = preg_replace(
            '/{location}/',
            $term->name, 
            $template
        );

        return $template;
    }
}