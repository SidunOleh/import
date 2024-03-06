<?php

namespace Import;

defined('ABSPATH') or die;

class Taxonomy
{
    private $name;

    private $postTypes;

    private $label;

    private $labelPlular;

    private $hierarchical;

    private $publiclyQueryable;

    private $rewrite;

    public function __construct()
    {
        $this->name = '';
        $this->postTypes = [];
        $this->label = '';
        $this->labelPlular = '';
        $this->hierarchical = false;
        $this->publiclyQueryable = true;
        $this->rewrite = [];        
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function postTypes(array $postTypes): self
    {
        $this->postTypes = $postTypes;

        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function labelPlular(string $labelPlular): self
    {
        $this->labelPlular = $labelPlular;

        return $this;
    }

    public function hierarchical(bool $hierarchical): self
    {
        $this->hierarchical = $hierarchical;

        return $this;
    }

    public function publiclyQueryable(bool $publiclyQueryable): self
    {
        $this->publiclyQueryable = $publiclyQueryable;

        return $this;
    }

    public function rewrite(array $rewrite): self
    {
        $this->rewrite = $rewrite;

        return $this;
    }

    public function register(): void
    {
        add_action('init', function () {
            register_taxonomy($this->name, $this->postTypes, [
                'label'  => $this->label,
                'labels' => [
                    'name'              => $this->labelPlular,
                    'singular_name'     => $this->label,
                    'search_items'      => __('Search ') . $this->label,
                    'all_items'         => __('All ')  . $this->labelPlular,
                    'view_item '        => __('View ') . $this->label,
                    'parent_item'       => __('Parent ') . $this->label,
                    'parent_item_colon' => __('Parent :') . $this->label,
                    'edit_item'         => __('Edit ') . $this->label,
                    'update_item'       => __('Update ') . $this->label,
                    'add_new_item'      => __('Add New ') . $this->label,
                    'new_item_name'     => __('New ') . $this->label,
                    'menu_name'         => $this->labelPlular,
                    'back_to_items'     => __('← Back to ') . $this->labelPlular,
                ],
                'hierarchical'          => $this->hierarchical,
                'publicly_queryable'    => $this->publiclyQueryable,
                'rewrite'               => $this->rewrite,
            ]);
        });
    }
}