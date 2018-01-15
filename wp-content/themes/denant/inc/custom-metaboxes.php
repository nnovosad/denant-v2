<?php
/**
 * Adding metaboxes for page templates
 */

// Start with an underscore to hide fields from custom fields list
$mb_prefix = '_dat_';

function subheading_post_metabox() {
    global $mb_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id' => 'hero_text_metabox_block',
        'title' => __('Верхний текст', 'cmb2'),
        'object_types' => array('page'), // Post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left,
        'show_on' => array(
            'key' => 'page-template',
            'value' => 'template-homepage.php'
        )
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Текст #1', 'cmb2' ),
        'id'         => $mb_prefix . 'hero_text_1',
        'desc'       => __( 'Введите текст на первом слайде', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Текст #2', 'cmb2' ),
        'id'         => $mb_prefix . 'hero_text_2',
        'desc'       => __( 'Введите текст на втором слайде', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Название компании', 'cmb2' ),
        'id'         => $mb_prefix . 'hero_company_text',
        'desc'       => __( 'По умолчанию: ДентАнтТранс', 'cmb2' ),
        'type'       => 'text'
    ) );
}
add_action( 'cmb2_init', 'subheading_post_metabox' );