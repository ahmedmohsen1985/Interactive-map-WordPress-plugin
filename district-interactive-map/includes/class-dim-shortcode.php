<?php
/**
 * Shortcode handler for [district_map].
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DIM_Shortcode {

    private static $instance_count = 0;

    public function __construct() {
        add_shortcode( 'district_map', array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 1 );
    }

    public function register_scripts() {
        wp_register_script(
            'dim-frontend',
            DIM_PLUGIN_URL . 'assets/js/dim-frontend.js',
            array(),
            DIM_VERSION,
            true
        );

        $settings = new DIM_Settings();
        $api_key  = $settings->get_api_key();

        if ( '' !== $api_key ) {
            $maps_url = 'https://maps.googleapis.com/maps/api/js?key=' . urlencode( $api_key ) . '&callback=dimInitMap';
            wp_register_script(
                'dim-google-maps',
                $maps_url,
                array( 'dim-frontend' ),
                null,
                true
            );
        }
    }

    public function render( $atts ) {
        self::$instance_count++;
        $atts = shortcode_atts( array( 'id' => '', 'slug' => '' ), $atts, 'district_map' );
        $map_id = 0;

        if ( ! empty( $atts['id'] ) ) {
            $map_id = absint( $atts['id'] );
        } elseif ( ! empty( $atts['slug'] ) ) {
            $query = new WP_Query( array(
                'post_type'              => 'district_map',
                'name'                   => sanitize_title( $atts['slug'] ),
                'posts_per_page'         => 1,
                'post_status'            => 'publish',
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ) );

            if ( $query->have_posts() ) {
                $map_id = $query->posts[0]->ID;
            }
        }

        if ( ! $map_id ) {
            return '<p class="dim-error">' . esc_html__( 'District map not found.', 'district-interactive-map' ) . '</p>';
        }

        $post = get_post( $map_id );
        if ( ! $post || 'district_map' !== $post->post_type ) {
            return '<p class="dim-error">' . esc_html__( 'District map not found.', 'district-interactive-map' ) . '</p>';
        }

        if ( ! is_preview() && 'publish' !== get_post_status( $map_id ) ) {
            return '';
        }

        $settings = new DIM_Settings();
        $api_key  = $settings->get_api_key();

        if ( '' === $api_key ) {
            return '<p class="dim-error">' . esc_html__( 'Google Maps API key is missing. Please add it under Settings > District Map.', 'district-interactive-map' ) . '</p>';
        }

        if ( ! wp_script_is( 'dim-frontend', 'registered' ) ) {
            $this->register_scripts();
        }

        wp_enqueue_style(
            'dim-frontend',
            DIM_PLUGIN_URL . 'assets/css/dim-frontend.css',
            array(),
            DIM_VERSION
        );

        $uid = 'dim-map-' . self::$instance_count . '-' . $map_id;

        $raw_center_lat = get_post_meta( $map_id, 'dim_center_lat', true );
        $center_lat     = is_numeric( $raw_center_lat ) ? floatval( $raw_center_lat ) : 30.0889;

        $raw_center_lng = get_post_meta( $map_id, 'dim_center_lng', true );
        $center_lng     = is_numeric( $raw_center_lng ) ? floatval( $raw_center_lng ) : 31.3283;

        $raw_zoom = get_post_meta( $map_id, 'dim_default_zoom', true );
        $zoom     = is_numeric( $raw_zoom ) ? absint( $raw_zoom ) : 14;

        $gps_raw  = get_post_meta( $map_id, 'dim_gps_links', true );
        $perk_raw = get_post_meta( $map_id, 'dim_perk_stops', true );

        if ( ! is_array( $gps_raw ) ) {
            $gps_raw = array();
        }
        if ( ! is_array( $perk_raw ) ) {
            $perk_raw = array();
        }

        $gps   = $this->prepare_items( $gps_raw, 'gps' );
        $perks = $this->prepare_items( $perk_raw, 'perk' );

        $config = array(
            'uid'       => $uid,
            'center'    => array( 'lat' => $center_lat, 'lng' => $center_lng ),
            'zoom'      => $zoom,
            'gps'       => $gps,
            'perks'     => $perks,
            'styleJson' => $settings->get_map_style_json(),
        );

        wp_enqueue_script( 'dim-frontend' );
        wp_add_inline_script(
            'dim-frontend',
            'window.dimMapData=window.dimMapData||[];window.dimMapData.push(' . wp_json_encode( $config ) . ');',
            'before'
        );
        wp_enqueue_script( 'dim-google-maps' );

        ob_start();
        ?>
        <div class="dim-container" id="<?php echo esc_attr( $uid ); ?>">
            <div class="dim-lists-col">
                <h2 class="dim-section-heading"><?php esc_html_e( 'GPS Links', 'district-interactive-map' ); ?></h2>
                <div class="dim-items-grid">
                    <?php foreach ( $gps as $item ) : ?>
                        <a class="dim-list-item dim-list-item-gps" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer" data-type="gps" data-index="<?php echo esc_attr( $item['num'] ); ?>" data-map-uid="<?php echo esc_attr( $uid ); ?>">
                            <span class="dim-item-num"><?php echo esc_html( $item['num'] ); ?>.</span>
                            <span class="dim-item-title"><?php echo esc_html( $item['title'] ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <h2 class="dim-section-heading dim-perks-heading"><?php esc_html_e( 'Perk Stops', 'district-interactive-map' ); ?></h2>
                <div class="dim-items-grid">
                    <?php foreach ( $perks as $item ) : ?>
                        <a class="dim-list-item dim-list-item-perk" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer" data-type="perk" data-index="<?php echo esc_attr( $item['num'] ); ?>" data-map-uid="<?php echo esc_attr( $uid ); ?>">
                            <span class="dim-item-num"><?php echo esc_html( $item['num'] ); ?>.</span>
                            <span class="dim-item-title"><?php echo esc_html( $item['title'] ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dim-map-col">
                <div class="dim-legend-bar">
                    <div class="dim-legend-item">
                        <span class="dim-legend-badge dim-legend-badge-gps">01</span>
                        <span class="dim-legend-label"><?php esc_html_e( 'GPS Links', 'district-interactive-map' ); ?></span>
                    </div>
                    <div class="dim-legend-item">
                        <span class="dim-legend-badge dim-legend-badge-perk">01</span>
                        <span class="dim-legend-label"><?php esc_html_e( 'Perk Stops', 'district-interactive-map' ); ?></span>
                    </div>
                </div>
                <div class="dim-map-canvas" id="<?php echo esc_attr( $uid ); ?>-canvas"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function prepare_items( $raw, $type ) {
        $clean = array();

        if ( ! is_array( $raw ) ) {
            return $clean;
        }

        $counter = 1;
        foreach ( $raw as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row['title'] ) ? trim( $row['title'] ) : '';
            $lat   = isset( $row['lat'] ) ? sanitize_text_field( wp_unslash( $row['lat'] ) ) : '';
            $lng   = isset( $row['lng'] ) ? sanitize_text_field( wp_unslash( $row['lng'] ) ) : '';
            $url   = isset( $row['url'] ) ? esc_url_raw( wp_unslash( $row['url'] ) ) : '';

            if ( '' === $title || '' === $lat || '' === $lng ) {
                continue;
            }

            $lat_f = floatval( $lat );
            $lng_f = floatval( $lng );

            if ( ! is_finite( $lat_f ) || ! is_finite( $lng_f ) ) {
                continue;
            }

            if ( '' === $url ) {
                $url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $lat_f . ',' . $lng_f );
            }

            $clean[] = array(
                'title' => sanitize_text_field( $title ),
                'lat'   => $lat_f,
                'lng'   => $lng_f,
                'url'   => $url,
                'type'  => $type,
                'num'   => sprintf( '%02d', $counter ),
            );

            $counter++;
        }

        return $clean;
    }
}
