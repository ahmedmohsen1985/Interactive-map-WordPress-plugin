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
        wp_nonce_field( 'dim_save_map_meta', 'dim_map_meta_nonce' );

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
        $items = get_post_meta( $post->ID, 'dim_gps_links', true );
        if ( ! is_array( $items ) ) {
            $items = array();
        }
        $this->render_repeater( 'dim_gps_links', __( 'GPS Links', 'district-interactive-map' ), $items );
    }

    public function render_perk_meta( $post ) {
        $items = get_post_meta( $post->ID, 'dim_perk_stops', true );
        if ( ! is_array( $items ) ) {
            $items = array();
        }
        $this->render_repeater( 'dim_perk_stops', __( 'Perk Stops', 'district-interactive-map' ), $items );
    }

    private function render_repeater( $key, $label, $items ) {
        $template_id = 'dim-tmpl-' . str_replace( '_', '-', $key );
        ?>
        <div class="dim-repeater-wrapper" data-key="<?php echo esc_attr( $key ); ?>">
            <table class="dim-repeater-table" data-key="<?php echo esc_attr( $key ); ?>">
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
                        <?php $this->render_repeater_row( $key, $index, $item ); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button dim-add-row" data-key="<?php echo esc_attr( $key ); ?>" data-template="<?php echo esc_attr( $template_id ); ?>">
                    <?php esc_html_e( 'Add Row', 'district-interactive-map' ); ?>
                </button>
            </p>
            <template id="<?php echo esc_attr( $template_id ); ?>">
                <?php $this->render_repeater_row( $key, '__INDEX__', array( 'title' => '', 'lat' => '', 'lng' => '', 'url' => '' ) ); ?>
            </template>
        </div>
        <?php
    }

    private function render_repeater_row( $key, $index, $item ) {
        $name_base = $key . '[' . esc_attr( $index ) . ']';
        $number    = is_numeric( $index ) ? absint( $index ) + 1 : 1;
        ?>
        <tr class="dim-repeater-row" draggable="true" data-key="<?php echo esc_attr( $key ); ?>">
            <td class="dim-col-handle">
                <span class="dim-row-number"><?php echo esc_html( sprintf( '%02d', $number ) ); ?></span>
                <span class="dim-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'district-interactive-map' ); ?>">⋮⋮</span>
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr( $name_base ); ?>[title]" value="<?php echo esc_attr( isset( $item['title'] ) ? $item['title'] : '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Title', 'district-interactive-map' ); ?>" required>
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr( $name_base ); ?>[lat]" value="<?php echo esc_attr( isset( $item['lat'] ) ? $item['lat'] : '' ); ?>" class="dim-coord" placeholder="<?php esc_attr_e( 'Latitude', 'district-interactive-map' ); ?>" required>
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr( $name_base ); ?>[lng]" value="<?php echo esc_attr( isset( $item['lng'] ) ? $item['lng'] : '' ); ?>" class="dim-coord" placeholder="<?php esc_attr_e( 'Longitude', 'district-interactive-map' ); ?>" required>
            </td>
            <td>
                <input type="url" name="<?php echo esc_attr( $name_base ); ?>[url]" value="<?php echo esc_attr( isset( $item['url'] ) ? $item['url'] : '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'https://...', 'district-interactive-map' ); ?>">
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

        if ( ! isset( $_POST['dim_map_meta_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dim_map_meta_nonce'] ) ), 'dim_save_map_meta' ) ) {
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

        $gps   = isset( $_POST['dim_gps_links'] ) ? $this->sanitize_repeater( $_POST['dim_gps_links'] ) : array();
        $perks = isset( $_POST['dim_perk_stops'] ) ? $this->sanitize_repeater( $_POST['dim_perk_stops'] ) : array();

        update_post_meta( $post_id, 'dim_gps_links', $gps );
        update_post_meta( $post_id, 'dim_perk_stops', $perks );
    }

    private function sanitize_repeater( $input ) {
        $clean = array();

        if ( ! is_array( $input ) ) {
            return $clean;
        }

        foreach ( $input as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = isset( $row['title'] ) ? sanitize_text_field( wp_unslash( $row['title'] ) ) : '';
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

            $clean[] = array(
                'title' => $title,
                'lat'   => $lat_f,
                'lng'   => $lng_f,
                'url'   => $url,
            );
        }

        return $clean;
    }
}
