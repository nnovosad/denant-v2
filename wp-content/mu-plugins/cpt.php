<?php
/*
Author: Darwinapps
Author URI: http://darwinapps.com
Description: This plugin is used to add custom post types
Plugin Name: Custom post types by Darwinapps
Plugin URI: http://darwinapps.com
Version: 1.0
*/

if (!class_exists('custom_pt')) {

    class custom_pt {

        public $name;
        public $singular_name;
        public $plural;
        public $args = array(
            'labels' => array(),
            'public' => true,
            'has_archive' => true,
            'publicly_queryable' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'editor'
            ),
            'taxonomies' => array(''), // add default post categories and tags
            'menu_position' => 25,
            'exclude_from_search' => false
        );

        public function __construct($cpt, $args_overrides) {
            if (!is_array($cpt) || !isset($cpt[0], $cpt[1]))
                wp_die(__('Slug and singular name are required'));

            $this->name = isset($cpt[0]) ? $cpt[0] : '';
            $this->singular_name = isset($cpt[1]) ? $cpt[1] : '';
            $this->plural = isset($cpt[2]) ? $cpt[2] : $cpt[1].'s' ;

            if (!empty($args_overrides)) {
                foreach($args_overrides as $arg_key => $arg_value) {
                    $this->args[$arg_key] = $arg_value;
                }
            }

            if (!empty($this->name) && !empty($this->singular_name) && !empty($this->plural)) {
                add_action('init', array($this, 'register_post_type'));
            }
        }

        public function getLabels () {
            return array(
                'name'              => $this->plural,
                'singular_name'     => $this->singular_name,
                'add_new'           => sprintf(__('Добавить %s'), $this->singular_name),
                'add_new_item'      => sprintf(__('Добавить %s'), $this->singular_name),
                'edit_item'         => sprintf(__('Редактировать %s'), $this->singular_name),
                'new_item'          => sprintf(__('Добавить %s'), $this->singular_name),
                'all_items'         => sprintf(__('Все %s'), $this->plural),
                'view_item'         => sprintf(__('Просмотр %s'), $this->singular_name),
                'search_items'      => sprintf(__('Поиск %s'), $this->plural),
                'not_found'         => sprintf(__('Нет %s результата'), $this->plural),
                'not_found_in_trash' => sprintf(__('Нет %s результат в удаленных'), $this->plural),
                'parent_item_colon' => $this->args['hierarchical'] ? sprintf(__('Parent %s'), $this->singular_name) : null,
                'menu_name'         => $this->plural
            );
        }

        public function register_post_type() {
            $this->args['labels'] = $this->getLabels();
            register_post_type($this->name, $this->args);
        }

    }

}

// staff post type
new custom_pt(array('staff', 'Сотрудника', 'Сотрудники'), array('supports' => array('title', 'thumbnail', 'author')));