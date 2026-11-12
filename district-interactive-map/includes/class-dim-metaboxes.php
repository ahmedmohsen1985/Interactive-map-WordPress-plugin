<?php
/**
 * Meta boxes for district_map.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DIM_Metaboxes {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function add_meta_boxes() {
        add_meta_box(
            'dim_viewport_meta',
            __( 'Map Viewport Config', 'district-interactive-map' ),
            array( $this, 'render_viewport_meta' ),
            'district_map',
            'normal',
            'high'
        );

        add_meta_box(
            'dim_gps_links_meta',
            __( 'GPS Links', 'district-interactive-map' ),
            array( $this, 'render_gps_meta' ),
            'district_map',
            'normal',
            'high'
        );

        add_meta_box(
            'dim_perk_stops_meta',
            __( 'Perk Stops', 'district-interactive-map' ),
            array( $this, 'render_perk_meta' ),
            'district_map',
            'normal',
            'high'
        );
    }

    public function enqueue_assets( $hook ) {
        $screen = get_current_screen();

        $is_map_screen = is_object( $screen ) && 'district_map' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true );
        $is_settings   = 'settings_page_dim-settings' === $hook;

        if ( ! $is_map_screen && ! $is_settings ) {
            return;
        }

        wp_enqueue_style(
            'dim-admin',
            DIM_PLUGIN_URL . 'assets/css/dim-admin.css',
            array(),
            DIM_VERSION
        );

        wp_enqueue_script(
            'dim-admin',
            DIM_PLUGIN_URL . 'assets/js/dim-admin.js',
            array(),
            DIM_VERSION,
            true
        );
    }

    public function render_viewport_meta( $post ) {
        wp_nonce_field( 'dim_save_metabox_data', 'dim_metabox_nonce' );

        $center_lat   = get_post_meta( $post->ID, 'dim_center_lat', true );
        $center_lng   = get_post_meta( $post->ID, 'dim_center_lng', true );
        $default_zoom = get_post_meta( $post->ID, 'dim_default_zoom', true );

        if ( '' === $center_lat ) {
            $center_lat = '30.0889';
        }
        if ( '' === $center_lng ) {
            $center_lng = '31.3283';
        }
        if ( '' === $default_zoom ) {
            $default_zoom = '14';
        }
        ?>
        <table class="form-table dim-form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="dim_center_lat"><?php esc_html_e( 'Center Latitude', 'district-interactive-map' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="dim_center_lat" name="dim_center_lat" value="<?php echo esc_attr( $center_lat ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dim_center_lng"><?php esc_html_e( 'Center Longitude', 'district-interactive-map' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="dim_center_lng" name="dim_center_lng" value="<?php echo esc_attr( $center_lng ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dim_default_zoom"><?php esc_html_e( 'Default Zoom', 'district-interactive-map' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="dim_default_zoom" name="dim_default_zoom" value="<?php echo esc_attr( $default_zoom ); ?>" min="1" max="22" step="1" class="small-text">
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    public function render_gps_meta( $post ) {
        $items = get_post_meta( $post->ID, '_dim_gps_links', true );
        if ( ! is_array( $items ) ) {
            $items = array();
        }
        $this->render_repeater( 'gps', __( 'GPS Links', 'district-interactive-map' ), $items );
    }

    public function render_perk_meta( $post ) {
        $items = get_post_meta( $post->ID, '_dim_perk_stops', true );
        if ( ! is_array( $items ) ) {
            $items = array();
        }
        $this->render_repeater( 'perk', __( 'Perk Stops', 'district-interactive-map' ), $items );
    }

    private function render_repeater( $type, $label, $items ) {
        $template_id = 'dim-tmpl-' . $type;
        ?>
        <div class="dim-repeater-wrapper" data-type="<?php echo esc_attr( $type ); ?>">
            <table class="dim-repeater-table" data-type="<?php echo esc_attr( $type ); ?>">
                <thead>
                    <tr>
                        <th class="dim-col-handle"><?php esc_html_e( '#', 'district-interactive-map' ); ?></th>
                        <th><?php esc_html_e( 'Title', 'district-interactive-map' ); ?></th>
                        <th><?php esc_html_e( 'Latitude', 'district-interactive-map' ); ?></th>
                        <th><?php esc_html_e( 'Longitude', 'district-interactive-map' ); ?></th>
                        <th><?php esc_html_e( 'Google Maps URL', 'district-interactive-map' ); ?></th>
                        <th class="dim-col-actions"><?php esc_html_e( 'Actions', 'district-interactive-map' ); ?></th>
                    </tr>
                </thead>
                <tbody class="dim-repeater-body">
                    <?php foreach ( $items as $index => $item ) : ?>
                        <?php $this->render_repeater_row( $type, $index, $item ); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button dim-add-row" data-type="<?php echo esc_attr( $type ); ?>" data-template="<?php echo esc_attr( $template_id ); ?>">
                    <?php esc_html_e( 'Add Row', 'district-interactive-map' ); ?>
                </button>
            </p>
            <template id="<?php echo esc_attr( $template_id ); ?>">
                <?php $this->render_repeater_row( $type, 0, array( 'title' => '', 'lat' => '', 'lng' => '', 'url' => '' ) ); ?>
            </template>
        </div>
        <?php
    }

    private function render_repeater_row( $type, $index, $item ) {
        $prefix = 'dim_' . $type;
        $number = sprintf( '%02d', absint( $index ) + 1 );
        ?>
        <tr class="dim-repeater-row" draggable="true" data-type="<?php echo esc_attr( $type ); ?>">
            <td class="dim-col-handle">
                <span class="dim-row-number"><?php echo esc_html( $number ); ?></span>
                <span class="dim-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'district-interactive-map' ); ?>">⋮⋮</span>
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr( $prefix ); ?>_title[]" value="<?php echo esc_attr( isset( $item['title'] ) ? $item['title'] : '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Title', 'district-interactive-map' ); ?>">
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr( $prefix ); ?>_lat[]" value="<?php echo esc_attr( isset( $item['lat'] ) ? $item['lat'] : '' ); ?>" class="dim-coord" placeholder="<?php esc_attr_e( 'Latitude', 'district-interactive-map' ); ?>">
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr( $prefix ); ?>_lng[]" value="<?php echo esc_attr( isset( $item['lng'] ) ? $item['lng'] : '' ); ?>" class="dim-coord" placeholder="<?php esc_attr_e( 'Longitude', 'district-interactive-map' ); ?>">
            </td>
            <td>
                <input type="url" name="<?php echo esc_attr( $prefix ); ?>_url[]" value="<?php echo esc_attr( isset( $item['url'] ) ? $item['url'] : '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'https://...', 'district-interactive-map' ); ?>">
            </td>
            <td class="dim-col-actions">
                <button type="button" class="button dim-move-up"><?php esc_html_e( '↑', 'district-interactive-map' ); ?></button>
                <button type="button" class="button dim-move-down"><?php esc_html_e( '↓', 'district-interactive-map' ); ?></button>
                <button type="button" class="button dim-remove-row"><?php esc_html_e( 'Remove', 'district-interactive-map' ); ?></button>
            </td>
        </tr>
        <?php
    }

    public function save( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! isset( $_POST['dim_metabox_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dim_metabox_nonce'] ) ), 'dim_save_metabox_data' ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( 'district_map' !== get_post_type( $post_id ) ) {
            return;
        }

        $center_lat = isset( $_POST['dim_center_lat'] ) ? sanitize_text_field( wp_unslash( $_POST['dim_center_lat'] ) ) : '';
        $center_lng = isset( $_POST['dim_center_lng'] ) ? sanitize_text_field( wp_unslash( $_POST['dim_center_lng'] ) ) : '';
        $zoom       = isset( $_POST['dim_default_zoom'] ) ? sanitize_text_field( wp_unslash( $_POST['dim_default_zoom'] ) ) : '';

        update_post_meta( $post_id, 'dim_center_lat', ( '' !== $center_lat ? floatval( $center_lat ) : 30.0889 ) );
        update_post_meta( $post_id, 'dim_center_lng', ( '' !== $center_lng ? floatval( $center_lng ) : 31.3283 ) );
        update_post_meta( $post_id, 'dim_default_zoom', ( '' !== $zoom ? absint( $zoom ) : 14 ) );

        $gps = array();
        if ( isset( $_POST['dim_gps_title'] ) && is_array( $_POST['dim_gps_title'] ) ) {
            $titles = wp_unslash( $_POST['dim_gps_title'] );
            $lats   = isset( $_POST['dim_gps_lat'] ) ? wp_unslash( $_POST['dim_gps_lat'] ) : array();
            $lngs   = isset( $_POST['dim_gps_lng'] ) ? wp_unslash( $_POST['dim_gps_lng'] ) : array();
            $urls   = isset( $_POST['dim_gps_url'] ) ? wp_unslash( $_POST['dim_gps_url'] ) : array();

            foreach ( array_keys( $titles ) as $i ) {
                $title = isset( $titles[ $i ] ) ? sanitize_text_field( $titles[ $i ] ) : '';
                $lat   = isset( $lats[ $i ] ) ? sanitize_text_field( $lats[ $i ] ) : '';
                $lng   = isset( $lngs[ $i ] ) ? sanitize_text_field( $lngs[ $i ] ) : '';
                $url   = isset( $urls[ $i ] ) ? esc_url_raw( $urls[ $i ] ) : '';

                if ( '' === $title || '' === $lat || '' === $lng ) {
                    continue;
                }

                if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
                    continue;
                }

                $lat_f = floatval( $lat );
                $lng_f = floatval( $lng );

                if ( ! is_finite( $lat_f ) || ! is_finite( $lng_f ) ) {
                    continue;
                }

                $gps[] = array(
                    'title' => $title,
                    'lat'   => $lat_f,
                    'lng'   => $lng_f,
                    'url'   => $url,
                );
            }
        }

        if ( ! empty( $gps ) ) {
            update_post_meta( $post_id, '_dim_gps_links', $gps );
        } else {
            delete_post_meta( $post_id, '_dim_gps_links' );
        }

        $perks = array();
        if ( isset( $_POST['dim_perk_title'] ) && is_array( $_POST['dim_perk_title'] ) ) {
            $titles = wp_unslash( $_POST['dim_perk_title'] );
            $lats   = isset( $_POST['dim_perk_lat'] ) ? wp_unslash( $_POST['dim_perk_lat'] ) : array();
            $lngs   = isset( $_POST['dim_perk_lng'] ) ? wp_unslash( $_POST['dim_perk_lng'] ) : array();
            $urls   = isset( $_POST['dim_perk_url'] ) ? wp_unslash( $_POST['dim_perk_url'] ) : array();

            foreach ( array_keys( $titles ) as $i ) {
                $title = isset( $titles[ $i ] ) ? sanitize_text_field( $titles[ $i ] ) : '';
                $lat   = isset( $lats[ $i ] ) ? sanitize_text_field( $lats[ $i ] ) : '';
                $lng   = isset( $lngs[ $i ] ) ? sanitize_text_field( $lngs[ $i ] ) : '';
                $url   = isset( $urls[ $i ] ) ? esc_url_raw( $urls[ $i ] ) : '';

                if ( '' === $title || '' === $lat || '' === $lng ) {
                    continue;
                }

                if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
                    continue;
                }

                $lat_f = floatval( $lat );
                $lng_f = floatval( $lng );

                if ( ! is_finite( $lat_f ) || ! is_finite( $lng_f ) ) {
                    continue;
                }

                $perks[] = array(
                    'title' => $title,
                    'lat'   => $lat_f,
                    'lng'   => $lng_f,
                    'url'   => $url,
                );
            }
        }

        if ( ! empty( $perks ) ) {
            update_post_meta( $post_id, '_dim_perk_stops', $perks );
        } else {
            delete_post_meta( $post_id, '_dim_perk_stops' );
        }
    }
}
