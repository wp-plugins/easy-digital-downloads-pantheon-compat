<?php
/**
 * Plugin Name:     Easy Digital Downloads - Pantheon Compat
 * Plugin URI:      https://section214.com
 * Description:     Compatibility plugin for Easy Digital Downloads on Pantheon
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-pantheon-compat
 *
 * @package         EDD\PantheonCompat
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


if( ! class_exists( 'EDD_Pantheon_Compat' ) ) {


    /**
     * Main EDD_Pantheon_Compat class
     *
     * @since       1.0.0
     */
    class EDD_Pantheon_Compat {


        /**
         * @var         EDD_Pantheon_Compat $instance The one true EDD_Pantheon_Compat
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true EDD_Pantheon_Compat
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new EDD_Pantheon_Compat();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_PANTHEON_COMPAT_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_PANTHEON_COMPAT_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_PANTHEON_COMPAT_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Rehook the EDD upload dir handler
            remove_action( 'admin_init', 'edd_change_downloads_upload_dir', 999 );
            add_action( 'admin_init', array( $this, 'change_edd_upload_dir' ), 999 );

            add_action( 'admin_init', array( $this, 'disable_notice' ) );
        }


        /**
         * Change the EDD default upload directory
         *
         * @access      public
         * @since       1.0.0
         * @global      string $pagenow The page we are currently viewing
         * @return      void
         */
        public function change_edd_upload_dir() {
            global $pagenow;

            if( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
                if( 'download' == get_post_type( $_REQUEST['post_id'] ) ) {
                    add_filter( 'upload_dir', array( $this, 'set_upload_dir' ) );
                }
            }
        }


        /**
         * Set our custom upload directory when necessary
         *
         * @access      public
         * @since       1.0.0
         * @param       array $upload The current upload directory information
         * @return      array $upload The updated upload directory information
         */
        public function set_upload_dir( $upload ) {
            if( get_option( 'uploads_use_yearmonth_folders' ) ) {
                // Generate the year and month dirs
                $time = current_time( 'mysql' );
                $y = substr( $time, 0, 4 );
                $m = substr( $time, 5, 2 );
                $upload['subdir'] = "/$y/$m";
            }

            $upload['subdir']   = '/private/edd' . $upload['subdir'];
            $upload['path']     = $upload['basedir'] . $upload['subdir'];
            $upload['url']      = $upload['baseurl'] . $upload['subdir'];

            return $upload;
        }


        /**
         * Disable the EDD NGINX warning
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function disable_notice() {
            if( ! get_user_meta( get_current_user_id(), '_edd_nginx_redirect_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
                update_user_meta( get_current_user_id(), '_edd_nginx_redirect_dismissed', 1 );
            }    
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'edd_pantheon_compat_language_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-pantheon-compat', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-pantheon-compat/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-pantheon-compat/ folder
                load_textdomain( 'edd-pantheon-compat', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-pantheon-compat/ folder
                load_textdomain( 'edd-pantheon-compat', $mofile_local );
            } else {
                // Load the traditional language files
                load_plugin_textdomain( 'edd-pantheon-compat', false, $lang_dir );
            }
        }
    }
}


/**
 * The main function responsible for returning the one true
 * EDD_Pantheon_Compat instance to functions everywhere
 *
 * @since       1.0.0
 * @return      EDD_Pantheon_Compat The one true EDD_Pantheon_Compat
 */
function edd_pantheon_compat() {
    if( ! array_key_exists( 'PANTHEON_SITE', $_ENV ) ) {
        echo '<div class="error"><p>' . __( 'This plugin is intended for use only on EDD sites hosted on Pantheon. Use on other hosts will cause system instability.', 'edd-pantheon-compat' ) . '</p></div>';

        // We need plugin.php!
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        deactivate_plugins( __FILE__ );
    }

    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'S214_EDD_Activation' ) ) {
            require_once 'includes/class.s214-edd-activation.php';
        }

        $activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();

        return EDD_Pantheon_Compat::instance();
    } else {
        return EDD_Pantheon_Compat::instance();
    }
}
add_action( 'plugins_loaded', 'edd_pantheon_compat' );
