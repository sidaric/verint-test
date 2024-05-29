<?php

if ( ! class_exists( 'WP_Link_Search' ) ) {

    class WP_Link_Search {

        public static function init() {
            add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
            add_action( 'wp_ajax_wp_link_search', array( __CLASS__, 'handle_search' ) );
        }

        public static function admin_menu() {
            add_menu_page(
                'Link Search',
                'Link Search',
                'manage_options',
                'wp-link-search',
                array( __CLASS__, 'admin_page' ),
                'dashicons-search',
                20
            );
        }

        public static function enqueue_scripts() {
            wp_enqueue_style('wp-link-search-style', plugin_dir_url( dirname( __FILE__ ) ) . 'css/style.css');
            wp_enqueue_script( 'wp-link-search', plugin_dir_url( dirname( __FILE__ ) ) . 'js/wp-link-search.js', array( 'jquery' ), '1.0', true );
            wp_localize_script( 'wp-link-search', 'wpLinkSearch', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        }

        public static function admin_page() {
            ?>
            <div class="wrap">
                <h1>Link Search</h1>
                <div id="link-search">
                <input type="text" id="wp-link-search-url" placeholder="Enter URL to search" />
                <button id="wp-link-search-btn" class="button button-primary">Search</button>
                </div>
                <div id="wp-link-search-results"></div>
            </div>
            <?php
        }

        public static function handle_search() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( 'You do not have permission to perform this action.' );
            }

            $url = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '';

            if ( empty( $url ) ) {
                wp_send_json_error( 'URL is required.' );
            }

            $results = self::search_links( $url );

            wp_send_json_success( $results );
        }

        private static function search_links( $url ) {
            global $wpdb;

            $results = array();

            // Search post content.
            $posts = $wpdb->get_results( $wpdb->prepare( "
                SELECT ID, post_title 
                FROM {$wpdb->posts} 
                WHERE post_content LIKE %s 
                AND post_status = 'publish'", '%' . $wpdb->esc_like( $url ) . '%' ) );

            foreach ( $posts as $post ) {
                $results[] = array(
                    'title' => $post->post_title,
                    'link'  => get_permalink( $post->ID ),
                );
            }

            // Search ACF fields.
            if ( class_exists( 'ACF' ) ) {
                $acf_results = self::search_acf_fields( $url );
                $results = array_merge( $results, $acf_results );
            }

            return $results;
        }

        private static function search_acf_fields( $url ) {
            global $wpdb;

            $results = array();

            // Get all ACF fields that might contain URLs.
            $acf_fields = $wpdb->get_results( "
                SELECT post_id, meta_key, meta_value 
                FROM {$wpdb->postmeta} 
                WHERE meta_value LIKE '%" . $wpdb->esc_like( $url ) . "%'" );

            foreach ( $acf_fields as $field ) {
                $post_id = $field->post_id;
                $post_title = get_the_title( $post_id );

                $results[] = array(
                    'title' => $post_title,
                    'link'  => get_permalink( $post_id ),
                );
            }

            return $results;
        }
    }
}
