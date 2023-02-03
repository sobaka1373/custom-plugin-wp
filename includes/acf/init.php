<?php

if( function_exists('acf_add_options_page') ) {
// Register options page.
    $option_page = acf_add_options_page(array(
        'page_title' => __('Import file'),
        'menu_title' => __('Import file'),
        'menu_slug' => 'import-plugin-page',
        'capability' => 'edit_posts',
        'redirect' => false
    ));
}

if( function_exists('acf_add_local_field_group') ) {
    acf_add_local_field_group(array(
        'key' => 'group_63c66d30016e4',
        'title' => 'Table indexes',
        'fields' => array(
            array(
                'key' => 'field_63c66d30f236c',
                'label' => 'file_city',
                'name' => 'file_city',
                'type' => 'file',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'array',
                'library' => 'all',
                'min_size' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'import-plugin-page',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));
}