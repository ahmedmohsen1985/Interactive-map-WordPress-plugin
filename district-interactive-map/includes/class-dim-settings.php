<?php
/**
 * Settings page for District Interactive Map.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DIM_Settings {

    private $option_name = 'dim_settings';

    private $defaults = array();

    public function __construct() {
        $this->defaults = array(
            'google_maps_api_key'     => '',
            'custom_map_style_json'   => $this->default_map_style_json(),
        );

        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_settings_page() {
        add_options_page(
            __( 'District Map', 'district-interactive-map' ),
            __( 'District Map', 'district-interactive-map' ),
            'manage_options',
            'dim-settings',
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'dim_settings_group',
            $this->option_name,
            array( $this, 'sanitize_settings' )
        );

        add_settings_section(
            'dim_main_section',
            __( 'General Settings', 'district-interactive-map' ),
            null,
            'dim-settings'
        );

        add_settings_field(
            'dim_google_maps_api_key',
            __( 'Google Maps API Key', 'district-interactive-map' ),
            array( $this, 'render_api_key_field' ),
            'dim-settings',
            'dim_main_section'
        );

        add_settings_field(
            'dim_custom_map_style_json',
            __( 'Custom Map Style JSON', 'district-interactive-map' ),
            array( $this, 'render_style_field' ),
            'dim-settings',
            'dim_main_section'
        );
    }

    public function sanitize_settings( $input ) {
        $output = array();

        $output['google_maps_api_key'] = isset( $input['google_maps_api_key'] )
            ? sanitize_text_field( wp_unslash( $input['google_maps_api_key'] ) )
            : '';

        $output['custom_map_style_json'] = isset( $input['custom_map_style_json'] )
            ? sanitize_textarea_field( wp_unslash( $input['custom_map_style_json'] ) )
            : '';

        return $output;
    }

    public function render_api_key_field() {
        $settings = $this->get_settings();
        printf(
            '<input type="text" name="%s[google_maps_api_key]" value="%s" class="regular-text">',
            esc_attr( $this->option_name ),
            esc_attr( $settings['google_maps_api_key'] )
        );
    }

    public function render_style_field() {
        $settings = $this->get_settings();
        printf(
            '<textarea name="%s[custom_map_style_json]" rows="10" cols="50" class="large-text code">%s</textarea>',
            esc_attr( $this->option_name ),
            esc_textarea( $settings['custom_map_style_json'] )
        );
        echo '<p class="description">' . esc_html__( 'Paste a Google Maps JSON style array. Leave empty to use the built-in Silver / Grayscale style.', 'district-interactive-map' ) . '</p>';
    }

    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'dim_settings_group' );
                do_settings_sections( 'dim-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function get_settings() {
        return wp_parse_args( get_option( $this->option_name, array() ), $this->defaults );
    }

    public function get_api_key() {
        $settings = $this->get_settings();
        return isset( $settings['google_maps_api_key'] ) ? $settings['google_maps_api_key'] : '';
    }

    public function get_map_style_json() {
        $settings   = $this->get_settings();
        $style_json = isset( $settings['custom_map_style_json'] ) ? trim( $settings['custom_map_style_json'] ) : '';

        if ( '' === $style_json ) {
            return $this->default_map_style_json();
        }

        return $style_json;
    }

    private function default_map_style_json() {
        return wp_json_encode( array(
            array(
                'featureType' => 'all',
                'elementType' => 'geometry',
                'stylers'     => array(
                    array( 'color' => '#f5f5f5' ),
                ),
            ),
            array(
                'featureType' => 'all',
                'elementType' => 'labels.text.stroke',
                'stylers'     => array(
                    array( 'visibility' => 'off' ),
                ),
            ),
            array(
                'featureType' => 'all',
                'elementType' => 'labels.text.fill',
                'stylers'     => array(
                    array( 'color' => '#9e9e9e' ),
                ),
            ),
            array(
                'featureType' => 'road',
                'elementType' => 'geometry',
                'stylers'     => array(
                    array( 'color' => '#ffffff' ),
                ),
            ),
            array(
                'featureType' => 'road',
                'elementType' => 'geometry.stroke',
                'stylers'     => array(
                    array( 'color' => '#ffffff' ),
                ),
            ),
            array(
                'featureType' => 'road',
                'elementType' => 'labels.text.fill',
                'stylers'     => array(
                    array( 'color' => '#9e9e9e' ),
                ),
            ),
            array(
                'featureType' => 'water',
                'elementType' => 'geometry',
                'stylers'     => array(
                    array( 'color' => '#e9e9e9' ),
                ),
            ),
            array(
                'featureType' => 'poi',
                'elementType' => 'geometry',
                'stylers'     => array(
                    array( 'color' => '#f5f5f5' ),
                ),
            ),
            array(
                'featureType' => 'transit',
                'elementType' => 'geometry',
                'stylers'     => array(
                    array( 'color' => '#f5f5f5' ),
                ),
            ),
        ) );
    }
}
