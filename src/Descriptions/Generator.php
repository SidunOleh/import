<?php

namespace Import\Descriptions;

use WP_Term;

defined('ABSPATH') or die;

abstract class Generator
{
    public function generate(WP_Term $term): string
    {
        $templates = $this->findTemplates($this->rules($term));

        if (! $templates) {
            return '';
        }

        $template = $templates[rand(0, count($templates) - 1)];

        $description = $this->handleTemplate($template['text'], $term);

        return $description;
    }

    protected function findTemplates(array $rules): array
    {
        $templates = [];
        foreach ($rules as $rule) { 
            if (
                $templates = $this->findTemplatesByRule($rule)
            ) {
                break;
            }
        }

        return $templates;
    }

    protected function findTemplatesByRule(array $rule): array
    {
        $templates = $this->getTemplates();
        $findTemplates = [];
        foreach ($templates as $template) {
            if (
                $this->matchRules($rule, $template['rule'] ?? [])
            ) {
                $findTemplates[] = $template;
            }
        }

        return $findTemplates;
    }

    protected function getTemplates(): array
    {
        $settings = get_option('import_settings', []);

        $templates = $settings['templates'] ?? [];

        return $templates;
    }

    protected function matchRules(array $rule1, array $rule2): bool
    {
        if (count($rule1) != count($rule2)) {
            return false;
        }

        $matched = true;
        foreach ($rule1 as $key => $val) {
            if (
                ! isset($rule2[$key]) or
                urldecode($val) != urldecode($rule2[$key])
            ) {
                $matched = false;
            }
        }

        return $matched;
    }

    protected function handleTemplate(string $template, WP_Term $term): string
    {
        $template = $this->chooseVariants($template);

        $template = $this->replaceVars($template, $term);

        return $template;
    }

    protected function chooseVariants(string $template): string
    {
        $template = preg_replace_callback('(\[.*?\])', function ($matches) {
            $words = explode('|', trim($matches[0], '[]'));
            $word = $words[rand(0, count($words) - 1)];
        
            return $word;
        }, $template);

        return $template;
    }

    protected function replaceVars(string $template, WP_Term $term): string
    {
        $template = preg_replace('/{name}/', $term->name, $template);

        return $template;
    }

    abstract protected function rules(WP_Term $term): array;

    public static function create(string $taxonomy): ?Generator
    {
        switch ($taxonomy) {
            case 'restaurant_cuisine':
                return new CuisineGenerator;
            case 'restaurant_location':
                return new LocationGenerator;
            case 'restaurant_cuisine_location':
                return new CuisineLocationGenerator;
            default:
                return null;
        }
    }
}