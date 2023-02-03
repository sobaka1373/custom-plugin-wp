<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Custom_Plugin_Admin
{

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/main-admin.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/main-admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }

    public function register_admin_page() {
        add_menu_page(
            'custom plugin', 'Custom plugin title', 'manage_options',
            'custom-plugin-admin', 'admin_page_open', '', 6
        );
//        add_filter( 'option_page_capability_'.'custom-plugin-admin', 'my_page_capability' );
    }

    public function start_parse_file(): void
    {
        $data_file = get_field('file_city',  'option');
        $file = get_attached_file($data_file['ID']);
        $data_array = parse_city_file($file);
        unset($data_array[1]);
        foreach ($data_array as $item) {
            $id_region = generate_region($item[2]);
            $id_district = generate_district($item[3], $id_region);
            $id_townname = generate_townname($item[5], $id_district);
            if (empty(get_post_meta($id_townname, 'towm_type'))) {
                add_post_meta( $id_townname, 'towm_type', $item[4]);
            }
            $id_streetname = generate_streetname($item[7], $id_townname);
            if (empty(get_post_meta($id_townname, 'street_type'))) {
                add_post_meta( $id_streetname, 'street_type', $item[6]);
            }

            if (empty(get_post_meta($id_townname, 'post_code'))) {
                add_post_meta( $id_streetname, 'post_code', $item[1]);
            }

            if (empty(get_post_meta($id_townname, 'buildings'))) {
                add_post_meta( $id_streetname, 'buildings', $item[8]);
            }
        }
        wp_die();
    }

    public function create_posttype_region() {
        register_post_type( 'region',
            array(
                'labels' => array(
                    'name' => __( 'Region' ),
                    'singular_name' => __( 'Region' )
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'region'),
                'show_in_rest' => true,
            )
        );
    }

    public function create_posttype_district() {
        register_post_type( 'district',
            array(
                'labels' => array(
                    'name' => __( 'District' ),
                    'singular_name' => __( 'District' )
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'district'),
                'show_in_rest' => true,
            )
        );
    }

    public function create_posttype_townname() {
        register_post_type( 'townname',
            array(
                'labels' => array(
                    'name' => __( 'TownName' ),
                    'singular_name' => __( 'TownName' )
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'townname'),
                'show_in_rest' => true,
            )
        );
    }

    public function create_posttype_streetname() {
        register_post_type( 'streetname',
            array(
                'labels' => array(
                    'name' => __( 'StreetName' ),
                    'singular_name' => __( 'StreetName' )
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'streetname'),
                'show_in_rest' => true,
            )
        );
    }
}