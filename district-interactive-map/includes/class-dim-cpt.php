<?php
/**
 * Custom Post Type: district_map.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DIM_CPT {

    public function __construct() {
        add_action( 'init', array( $this, 'register' ), 10 );
        add_filter( 'manage_district_map_posts_columns', array( $this, 'add_shortcode_column' ) );
        add_action( 'manage_district_map_posts_custom_column', array( $this, 'render_shortcode_column' ), 10, 2 );
    }

    public function register() {
        $labels = array(
            'name'                  => __( 'District Maps', 'district-interactive-map' ),
            'singular_name'         => __( 'District Map', 'district-interactive-map' ),
            'menu_name'             => __( 'District Maps', 'district-interactive-map' ),
            'name_admin_bar'        => __( 'District Map', 'district-interactive-map' ),
            'add_new'               => __( 'Add New', 'district-interactive-map' ),
            'add_new_item'          => __( 'Add New District Map', 'district-interactive-map' ),
            'new_item'              => __( 'New District Map', 'district-interactive-map' ),
            'edit_item'             => __( 'Edit District Map', 'district-interactive-map' ),
            'view_item'             => __( 'View District Map', 'district-interactive-map' ),
            'all_items'             => __( 'All District Maps', 'district-interactive-map' ),
            'search_items'          => __( 'Search District Maps', 'district-interactive-map' ),
            'parent_item_colon'     => __( 'Parent District Maps:', 'district-interactive-map' ),
            'not_found'             => __( 'No district maps found.', 'district-interactive-map' ),
            'not_found_in_trash'    => __( 'No district maps found in Trash.', 'district-interactive-map' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => array( 'title' ),
            'rewrite'            => false,
            'menu_icon'          => 'dashicons-location-alt',
        );

        register_post_type( 'district_map', $args );
    }

    public function add_shortcode_column( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $label ) {
            $new_columns[ $key ] = $label;

            if ( 'title' === $key ) {
                $new_columns['dim_shortcode'] = __( 'Shortcode', 'district-interactive-map' );
            }
        }

        if ( ! isset( $new_columns['dim_shortcode'] ) ) {
            $new_columns['dim_shortcode'] = __( 'Shortcode', 'district-interactive-map' );
        }

        return $new_columns;
    }

    public function render_shortcode_column( $column, $post_id ) {
        if ( 'dim_shortcode' !== $column ) {
            return;
        }

        $slug = get_post_field( 'post_name', $post_id );

        echo '<code>[district_map id="' . esc_attr( $post_id ) . '"]</code><br>';
        echo '<code>[district_map slug="' . esc_attr( $slug ) . '"]</code>';
    }
}
