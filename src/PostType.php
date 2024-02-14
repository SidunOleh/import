<?php

namespace Import;

defined('ABSPATH') or die;

class PostType
{
    private $name;

    private $label;

    private $labelPlular;

    private $taxonomies;

    private $public;

    private $menuPosition;

    private $menuIcon;

    private $supports;

    private $hierarchical;

    private $hasArchive;

    private $rewrite;

    public function __construct()
    {
        $this->name = '';
        $this->label = '';
        $this->labelPlular = '';
        $this->taxonomies = [];
        $this->public = true;
        $this->menuPosition = 20;
        $this->menuIcon = 'dashicons-menu';
        $this->supports = ['title', 'editor', 'thumbnail',];
        $this->hierarchical = false;      
        $this->hasArchive = true;  
        $this->rewrite = [];  
    }

    public function name(string $name): self
    {
        $this->name = $name;

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

    public function taxonomies(array $taxonomies): self
    {
        $this->taxonomies = $taxonomies;

        return $this;
    }

    public function public(bool $public): self
    {
        $this->public  = $public;

        return $this;
    } 

    public function menuPosition(int $menuPosition): self
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    public function menuIcon(string $menuIcon): self
    {
        $this->menuIcon = $menuIcon;

        return $this;
    }

    public function supports(array $supports): self
    {
        $this->supports = $supports;

        return $this;
    }

    public function hierarchical(bool $hierarchical): self
    {
        $this->hierarchical = $hierarchical;

        return $this;
    }

    public function hasArchive(array $hasArchive): self
    {
        $this->hasArchive = $hasArchive;

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
            register_post_type($this->name, [
                'label'  => $this->label,
                'labels' => [
                    'name'               => $this->labelPlular,
                    'singular_name'      => $this->label,
                    'add_new'            => __('Add ') . $this->label,
                    'add_new_item'       => __('Add ') . $this->label,
                    'edit_item'          => __('Edit ') . $this->label,
                    'new_item'           => __('New ') . $this->label,
                    'view_item'          => __('View ') . $this->label,
                    'search_items'       => __('Search ') . $this->label,
                    'not_found'          => __('Not Found'),
                    'not_found_in_trash' => __('Not Found in Trash'),
                    'menu_name'          => $this->labelPlular,
                ],
                'public'        => $this->public,
                'menu_position' => $this->menuPosition,
                'menu_icon'     => $this->menuIcon,
                'hierarchical'  => $this->hierarchical,
                'supports'      => $this->supports,
                'taxonomies'    => $this->taxonomies,
                'has_archive'   => $this->hasArchive,
                'rewrite'       => $this->rewrite,
            ]);
        });
    }
}
