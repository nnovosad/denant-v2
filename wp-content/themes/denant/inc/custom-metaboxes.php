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

function about_post_metabox() {
    global $mb_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id' => 'about_metabox_block',
        'title' => __('О нас', 'cmb2'),
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
        'name'       => __( 'Название раздела', 'cmb2' ),
        'id'         => $mb_prefix . 'about_title',
        'desc'       => __( 'По умолчанию: О нас', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Текст', 'cmb2' ),
        'id'         => $mb_prefix . 'about_text',
        'desc'       => __( 'Введите текст для раздела "О нас" ', 'cmb2' ),
        'type'       => 'textarea'
    ) );
}
add_action( 'cmb2_init', 'about_post_metabox' );

function services_post_metabox() {
    global $mb_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id' => 'services_metabox_block',
        'title' => __('Услуги', 'cmb2'),
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
        'name'       => __( 'Название раздела', 'cmb2' ),
        'id'         => $mb_prefix . 'services_title',
        'desc'       => __( 'По умолчанию: Услуги', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Название подраздела 1', 'cmb2' ),
        'id'         => $mb_prefix . 'services_subsection1_title',
        'desc'       => __( 'По умолчанию: Автомобильные перевозки грузов', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Заголовок: Комплектные', 'cmb2' ),
        'id'         => $mb_prefix . 'services_complete_title',
        'desc'       => __( 'По умолчанию: Комплектные', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Текст: Комплектные', 'cmb2' ),
        'id'         => $mb_prefix . 'services_complete_text',
        'desc'       => __( 'Введите текст для комплектных услуг', 'cmb2' ),
        'type'       => 'textarea'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Заголовок: Сборные', 'cmb2' ),
        'id'         => $mb_prefix . 'services_national_title',
        'desc'       => __( 'По умолчанию: Сборные', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Текст: Сборные', 'cmb2' ),
        'id'         => $mb_prefix . 'services_national_text',
        'desc'       => __( 'Введите текст для сборных услуг', 'cmb2' ),
        'type'       => 'textarea'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Заголовок: Негабаритные', 'cmb2' ),
        'id'         => $mb_prefix . 'services_oversized_title',
        'desc'       => __( 'По умолчанию: Негабаритные', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Текст: Негабаритные', 'cmb2' ),
        'id'         => $mb_prefix . 'services_oversized_text',
        'desc'       => __( 'Введите текст для негабаритных услуг', 'cmb2' ),
        'type'       => 'textarea'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Название подраздела 2', 'cmb2' ),
        'id'         => $mb_prefix . 'services_subsection2_title',
        'desc'       => __( 'По умолчанию: Складское хранение', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Текст подраздела 2', 'cmb2' ),
        'id'         => $mb_prefix . 'services_subsection2_text',
        'desc'       => __( 'Введите текст для подраздела 2', 'cmb2' ),
        'type'       => 'textarea'
    ) );
}
add_action( 'cmb2_init', 'services_post_metabox' );

function documents_post_metabox() {
    global $mb_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id' => 'documents_metabox_block',
        'title' => __('Документы', 'cmb2'),
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
        'name'       => __( 'Название раздела', 'cmb2' ),
        'id'         => $mb_prefix . 'document_title',
        'desc'       => __( 'По умолчанию: Документы', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Сертификат Текст', 'cmb2' ),
        'id'         => $mb_prefix . 'certificate_text',
        'desc'       => __( 'По умолчанию: Скачать Сертификат', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Сертификат Файл', 'cmb2' ),
        'id'         => $mb_prefix . 'certificate_file',
        'desc'       => __( 'Выбирете сертификат', 'cmb2' ),
        'type'       => 'file'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Реквизиты Текст', 'cmb2' ),
        'id'         => $mb_prefix . 'requisites_text',
        'desc'       => __( 'По умолчанию: Скачать Реквизиты', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Реквизиты Файл', 'cmb2' ),
        'id'         => $mb_prefix . 'requisites_file',
        'desc'       => __( 'Выбирете реквизиты', 'cmb2' ),
        'type'       => 'file'
    ) );


}
add_action( 'cmb2_init', 'documents_post_metabox' );

function company_post_metabox() {
    global $mb_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id' => 'company_metabox_block',
        'title' => __('Смежные компании', 'cmb2'),
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
        'name'       => __( 'Название раздела', 'cmb2' ),
        'id'         => $mb_prefix . 'company_title',
        'desc'       => __( 'По умолчанию: Смежные компании', 'cmb2' ),
        'type'       => 'text'
    ) );

    $group_company = $cmb->add_field( array(
        'id'          => 'company_group',
        'type'        => 'group',
        'description' => __( 'Смежные компании', 'cmb2' ),
        // 'repeatable'  => false, // use false if you want non-repeatable group
        'options'     => array(
            'group_title'   => __( 'Компания {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
            'add_button'    => __( 'Добавить', 'cmb2' ),
            'remove_button' => __( 'Удалить', 'cmb2' ),
            'sortable'      => true, // beta
            // 'closed'     => true, // true to have the groups closed by default
        ),
    ) );

    $cmb->add_group_field( $group_company, array(
        'name' => 'Название компании',
        'id'   => 'name_company',
        'type' => 'text',
    ) );

    $cmb->add_group_field( $group_company, array(
        'name'       => __( 'Реквизиты Текст', 'cmb2' ),
        'id'         => $mb_prefix . 'company_requisites_text',
        'desc'       => __( 'По умолчанию: Скачать Реквизиты', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_group_field( $group_company, array(
        'name'       => __( 'Реквизиты Файл', 'cmb2' ),
        'id'         => $mb_prefix . 'company_requisites_file',
        'desc'       => __( 'Выбирете реквизиты', 'cmb2' ),
        'type'       => 'file'
    ) );

    $cmb->add_group_field( $group_company, array(
        'name'       => __( 'Свидетельство Текст', 'cmb2' ),
        'id'         => $mb_prefix . 'company_diploma_text',
        'desc'       => __( 'По умолчанию: Скачать Свидетельство', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_group_field( $group_company, array(
        'name'       => __( 'Свидетельство Файл', 'cmb2' ),
        'id'         => $mb_prefix . 'company_diploma_file',
        'desc'       => __( 'Выбирете Свидетельство', 'cmb2' ),
        'type'       => 'file'
    ) );

}
add_action( 'cmb2_init', 'company_post_metabox' );

function partners_post_metabox() {
    global $mb_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id' => 'partners_metabox_block',
        'title' => __('Партнеры', 'cmb2'),
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
        'name'       => __( 'Название раздела', 'cmb2' ),
        'id'         => $mb_prefix . 'partners_title',
        'desc'       => __( 'По умолчанию: Партнеры', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Партнеры', 'cmb2' ),
        'id'         => $mb_prefix . 'partners_item',
        'desc'       => __( 'Выберите фото партнеров', 'cmb2' ),
        'type'       => 'file_list'
    ) );
}
add_action( 'cmb2_init', 'partners_post_metabox' );

function contact_post_metabox() {
    global $mb_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id' => 'contact_metabox_block',
        'title' => __('Партнеры', 'cmb2'),
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
        'name'       => __( 'Название раздела', 'cmb2' ),
        'id'         => $mb_prefix . 'contact_title',
        'desc'       => __( 'По умолчанию: Контакты', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Телефон', 'cmb2' ),
        'id'         => $mb_prefix . 'contact_phone',
        'desc'       => __( 'Введите телефон', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Email', 'cmb2' ),
        'id'         => $mb_prefix . 'contact_email',
        'desc'       => __( 'Введите email', 'cmb2' ),
        'type'       => 'text_email'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Скайп', 'cmb2' ),
        'id'         => $mb_prefix . 'contact_skype',
        'desc'       => __( 'Введите скайп', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Вконтакте текст', 'cmb2' ),
        'id'         => $mb_prefix . 'vk_text',
        'desc'       => __( 'Введите текст', 'cmb2' ),
        'type'       => 'text'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Вконтакте ссылка', 'cmb2' ),
        'id'         => $mb_prefix . 'vk_link',
        'desc'       => __( 'Введите ссылку', 'cmb2' ),
        'type'       => 'text_url'
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Адрес', 'cmb2' ),
        'id'         => $mb_prefix . 'address',
        'desc'       => __( 'Введите адрес', 'cmb2' ),
        'type'       => 'textarea'
    ) );
}
add_action( 'cmb2_init', 'contact_post_metabox' );