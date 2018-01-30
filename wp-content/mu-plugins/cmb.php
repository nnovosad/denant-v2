<?php
/*
Author: Darwinapps
Author URI: http://darwinapps.com
Description: This plugin is used to add custom metaboxes, based on CMB2 plugin
Plugin Name: Custom metaboxes by Darwinapps
Plugin URI: http://darwinapps.com
Version: 1.0
*/

/*
 * Use underscores to name fields
 * */

require_once("cmb2/init.php");

add_filter('cmb2_meta_boxes', 'denant_cpt_metaboxes');

function denant_cpt_metaboxes(array $meta_boxes)
{

    //start with an underscore to hide from custom fields list
    $prefix = '_dat_';

    $meta_boxes['staff-block'] = array(
        'id' => 'staff-block',
        'title' => __('Информация о сотруднике', 'cmb2'),
        'object_types' => array('staff'), // Post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
        'fields' => array(
            array(
                'name' => __('Должность', 'cmb2'),
                'desc' => __('Напишите должность', 'cmb2'),
                'id' => $prefix . 'staff_position',
                'type' => 'text'
            ),
            array(
                'name' => __('Email', 'cmb2'),
                'desc' => __('Напишите E-mail', 'cmb2'),
                'id' => $prefix . 'staff_email',
                'type' => 'text_email'
            ),
            array(
                'name' => __('Skype', 'cmb2'),
                'desc' => __('Напишите Skype', 'cmb2'),
                'id' => $prefix . 'staff_skype',
                'type' => 'text'
            ),
            array(
                'name' => __('Телефон', 'cmb2'),
                'id' => $prefix . 'staff_phone',
                'type' => 'group',
                'options' => array(
                    'group_title' => __('Телефон {#}', 'cmb2'), // since version 1.1.4, {#} gets replaced by row number
                    'add_button' => __('Добавить телефон', 'cmb2'),
                    'remove_button' => __('Удалить телефон', 'cmb2'),
                    'sortable' => true, // beta
                ),
                'fields' => array(
                    array(
                        'name' => __('Номер телефона', 'cmb2'),
                        'id' => $prefix . 'staff_phone_item',
                        'type' => 'text',
                    ),
                ),
            ),
        )
    );

    return $meta_boxes;
}
