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

    protected function handleTemplate(string $template, WP_Term $term): string
    {
        $description = preg_replace_callback('(\[.*?\])', function ($matches) {
            $words = explode('|', trim($matches[0], '[]'));
            $word = $words[rand(0, count($words) - 1)];
        
            return $word;
        }, $template);

        $name = explode('-', $term->name);
        $description = preg_replace(
            '/{cuisine-name}/', 
            $name[0], 
            $description
        );
        $description = preg_replace(
            '/{location-name}/',
            $name[1], 
            $description
        );

        return $description;
    }
}