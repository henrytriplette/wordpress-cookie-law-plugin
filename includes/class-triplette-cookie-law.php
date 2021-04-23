<?php

if (! defined('ABSPATH') ) { exit;
}

class Triplette_Cookie_Law
{

    /**
     * The single instance of Triplette_Cookie_Law.
     *
     * @var    object
     * @access private
     * @since  1.0.0
     */
    private static $_instance = null;

    /**
     * Settings class object
     *
     * @var    object
     * @access public
     * @since  1.0.0
     */
    public $settings = null;

    /**
     * The version number.
     *
     * @var    string
     * @access public
     * @since  1.0.0
     */
    public $_version;

    /**
     * The token.
     *
     * @var    string
     * @access public
     * @since  1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     *
     * @var    string
     * @access public
     * @since  1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     *
     * @var    string
     * @access public
     * @since  1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     *
     * @var    string
     * @access public
     * @since  1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     *
     * @var    string
     * @access public
     * @since  1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     *
     * @var    string
     * @access public
     * @since  1.0.0
     */
    public $script_suffix;

    /**
     * Constructor function.
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function __construct( $file = '', $version = '1.0.0' )
    {
        $this->_version = $version;
        $this->_token = 'triplette_cookie_law';

        // Load plugin environment variables
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        register_activation_hook($this->file, array( $this, 'install' ));

        // Add admin Notice
        add_action('admin_notices', array( $this, 'install_admin_notice' ));
        add_action('admin_init', array( $this, 'install_nag_ignore' ));

        // Load frontend JS & CSS
        add_action('wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 5);
        add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5);

        // Load admin JS & CSS
        add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1);
        add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1);    

        // Load API for generic admin functions
        if (is_admin() ) {
            $this->admin = new Triplette_Cookie_Law_Admin_API();
        }

        // Handle localisation
        $this->load_plugin_textdomain();
        add_action('init', array( $this, 'load_localisation' ), 0);
    } // End __construct ()

    /**
     * Wrapper function to register a new post type
     *
     * @param  string $post_type   Post type name
     * @param  string $plural      Post type item plural name
     * @param  string $single      Post type item single name
     * @param  string $description Description of post type
     * @return object              Post type class object
     */
    public function register_post_type( $post_type = '', $plural = '', $single = '', $description = '' )
    {

        if (! $post_type || ! $plural || ! $single ) { return;
        }

        $post_type = new Triplette_Cookie_Law_Post_Type($post_type, $plural, $single, $description);

        return $post_type;
    }

    /**
     * Wrapper function to register a new taxonomy
     *
     * @param  string $taxonomy   Taxonomy name
     * @param  string $plural     Taxonomy single name
     * @param  string $single     Taxonomy plural name
     * @param  array  $post_types Post types to which this taxonomy applies
     * @return object             Taxonomy class object
     */
    public function register_taxonomy( $taxonomy = '', $plural = '', $single = '', $post_types = array() )
    {

        if (! $taxonomy || ! $plural || ! $single ) { return;
        }

        $taxonomy = new Triplette_Cookie_Law_Taxonomy($taxonomy, $plural, $single, $post_types);

        return $taxonomy;
    }

    /**
     * Load frontend CSS.
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function enqueue_styles()
    {
        wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-frontend');
    } // End enqueue_styles ()

    /**
     * Load frontend Javascript.
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function enqueue_scripts()
    {
        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version);            
        wp_register_script($this->_token . '-frontend-params', esc_url($this->assets_url) . 'js/frontend-params.js', array( 'jquery' ), $this->_version);
        
        if (get_option('wpt_triplette_cookies_enable') == 'enable' ) {

            wp_enqueue_script($this->_token . '-frontend');
            wp_enqueue_script($this->_token . '-frontend-params');

            // Prepare Params
            $cookie_type = get_option('wpt_triplette_cookies_type');
            $cookie_type_format = '';
            foreach ($cookie_type as $type) {
                $cookie_type_format[$type] = Array();
            }
            
            $hideprivacysettingstab = false;
            if (get_option('wpt_triplette_cookies_privacy_tab') == 'on' ) { $hideprivacysettingstab = true; 
            }
            
            $ignoreDoNotTrack = false;
            if (get_option('wpt_triplette_cookies_do_not_track') == 'on' ) { $ignoreDoNotTrack = true; 
            }
            
            // Create Params
            $params = array(
            'triplette_cookies_type' => json_encode($cookie_type_format),
            'triplette_cookies_consent' => get_option('wpt_triplette_cookies_consent'),
            'triplette_cookies_banner_refresh' => get_option('wpt_triplette_cookies_banner_refresh'),
            'triplette_cookies_style' => str_replace('"', '', get_option('wpt_triplette_cookies_style')),
            'triplette_cookies_position_banner' =>  str_replace('"', '', get_option('wpt_triplette_cookies_position_banner')),
            'triplette_cookies_position_tag' =>  str_replace('"', '', get_option('wpt_triplette_cookies_position_tag')),
            'triplette_cookies_server' => get_site_url(),
            'triplette_cookies_privacy_tab' => $hideprivacysettingstab,
            'triplette_cookies_banner_display' => get_option('wpt_triplette_cookies_banner_display'),
            'triplette_cookies_do_not_track' => $ignoreDoNotTrack,
              
            'tct_general_social_media_title' => get_option('wpt_tct_general_social_media_title'),
            'tct_general_social_media_description' => get_option('wpt_tct_general_social_media_description'),
            'tct_general_analytics_title' => get_option('wpt_tct_general_analytics_title'),
            'tct_general_analytics_description' => get_option('wpt_tct_general_analytics_description'),
            'tct_general_advertising_title' => get_option('wpt_tct_general_advertising_title'),
            'tct_general_advertising_description' => get_option('wpt_tct_general_advertising_description'),
            'tct_general_necessary_title' => get_option('wpt_tct_general_necessary_title'),
            'tct_general_necessary_description' => get_option('wpt_tct_general_necessary_description'),
            'tct_general_default_cookie_title' => get_option('wpt_tct_general_default_cookie_title'),
            'tct_general_default_cookie_description' => get_option('wpt_tct_general_default_cookie_description'),
            'tct_slide_title' => get_option('wpt_tct_slide_title'),
            'tct_slide_title_implicit' => get_option('wpt_tct_slide_title_implicit'),
            'tct_slide_custom_cookie' => get_option('wpt_tct_slide_custom_cookie'),
            'tct_slide_see_details' => get_option('wpt_tct_slide_see_details'),
            'tct_slide_see_details_implicit' => get_option('wpt_tct_slide_see_details_implicit'),
            'tct_slide_hide_details_link' => get_option('wpt_tct_slide_hide_details_link'),
            'tct_slide_allow_cookies_button' => get_option('wpt_tct_slide_allow_cookies_button'),
            'tct_slide_allow_cookies_button_implicit' => get_option('wpt_tct_slide_allow_cookies_button_implicit'),
            'tct_privacy_settings' => get_option('wpt_tct_privacy_settings'),
            'tct_privacy_settings_dialog' => get_option('wpt_tct_privacy_settings_dialog'),
            'tct_privacy_settings_dialog_small' => get_option('wpt_tct_privacy_settings_dialog_small'),
            'tct_privacy_settings_dialog_subtitle' => get_option('wpt_tct_privacy_settings_dialog_subtitle'),
            'tct_privacy_settings_policy_link' => get_option('wpt_tct_privacy_settings_policy_link'),
            'tct_privacy_settings_policy_title' => get_option('wpt_tct_privacy_settings_policy_title'),
            'tct_privacy_settings_cookie_policy_link' => get_option('wpt_tct_privacy_settings_cookie_policy_link'),
            'tct_privacy_settings_cookie_policy_title' => get_option('wpt_tct_privacy_settings_cookie_policy_title'),
            'tct_privacy_settings_dialog_all_websites' => get_option('wpt_tct_privacy_settings_dialog_all_websites'),
            'tct_privacy_settings_dialog_consent' => get_option('wpt_tct_privacy_settings_dialog_consent'),
            'tct_privacy_settings_dialog_i_consent' => get_option('wpt_tct_privacy_settings_dialog_i_consent'),
            'tct_privacy_settings_dialog_i_decline' => get_option('wpt_tct_privacy_settings_dialog_i_decline'),
            'tct_privacy_settings_dialog_no_cookies' => get_option('wpt_tct_privacy_settings_dialog_no_cookies'),
            'tct_global_settings_dialog_title' => get_option('wpt_tct_global_settings_dialog_title'),
            'tct_global_settings_dialog_title_small' => get_option('wpt_tct_global_settings_dialog_title_small'),
            'tct_global_settings_dialog_subtitle' => get_option('wpt_tct_global_settings_dialog_subtitle'),
            'tct_global_settings_back_to' => get_option('wpt_tct_global_settings_back_to'),
            'tct_global_settings_ask' => get_option('wpt_tct_global_settings_ask'),
            'tct_global_always_allow' => get_option('wpt_tct_global_always_allow'),
            'tct_global_never_allow' => get_option('wpt_tct_global_never_allow'),
            'tct_general_close_window' => get_option('wpt_tct_general_close_window'),
              
            );        
            wp_localize_script($this->_token . '-frontend-params', 'tC', $params);
        }
    } // End enqueue_scripts ()

    /**
     * Load admin CSS.
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function admin_enqueue_styles( $hook = '' )
    {
        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');
    } // End admin_enqueue_styles ()

    /**
     * Load admin Javascript.
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function admin_enqueue_scripts( $hook = '' )
    {
        wp_register_script($this->_token . '-admin', esc_url($this->assets_url) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version);
        wp_enqueue_script($this->_token . '-admin');
    } // End admin_enqueue_scripts ()

    /**
     * Load plugin localisation
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function load_localisation()
    {
        load_plugin_textdomain('triplette-cookie-law', false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_localisation ()

    /**
     * Load plugin textdomain
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function load_plugin_textdomain()
    {
        $domain = 'triplette-cookie-law';

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_plugin_textdomain ()

    /**
     * Add Custom Pages
     *
     * @access public
     * @since  1.0.0
     * @return null
     */
    public function create_pages()
    {


        // get contents of a file into a string
        /*
        $filename = plugin_dir_url( __FILE__ )."resources/cookie_policy.txt";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        */

        $contents = 'Please insert your policy here';

        $cookie_policy = array(
        'comment_status' => 'closed', // 'closed' means no comments.
        'post_content' => $contents, //The full text of the post.
        'post_date' => date('Y-m-d H:i:s'), //The time post was made.
        'post_name' => 'cookie-policy', // The name (slug) for your post
        'post_status' => 'publish', //Set the status of the new post.
        'post_title' => 'Cookie Policy', //The title of your post.
        'post_type' => 'page', //Sometimes you want to post a page.
        'tags_input' => 'cookie policy, cookies', //For tags.
        );  
        
        // Insert the post into the database
        // $page = get_page_by_title( 'Cookie Policy' );
        // if ( is_null($page) ) {
        wp_insert_post($cookie_policy);
        // }
            
        // get contents of a file into a string
        /*
        $filename = plugin_dir_url( __FILE__ )."resources/privacy_policy.txt";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        */

        $privacy_policy = array(
        'comment_status' => 'closed', // 'closed' means no comments.
        'post_content' => $contents, //The full text of the post.
        'post_date' => date('Y-m-d H:i:s'), //The time post was made.
        'post_name' => 'privacy-policy', // The name (slug) for your post
        'post_status' => 'publish', //Set the status of the new post.
        'post_title' => 'Privacy Policy', //The title of your post.
        'post_type' => 'page', //Sometimes you want to post a page.
        'tags_input' => 'privacy policy, privacy', //For tags.
        );  
        
        // Insert the post into the database
        // $page = get_page_by_title( 'Privacy Policy' );
        // if ( is_null($page) ) {
        wp_insert_post($privacy_policy);
        // }

    } // End create_pages ()

    /**
     * Add Notice on first install
     *
     * @access public
     * @since  1.0.0
     * @return string
     */
    public function install_admin_notice()
    {
        global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
        if (! get_user_meta($user_id, 'triplette_install_nag_ignore') ) {
            echo '<div class="update-nag"><p>'; 
            printf(__('<b>Triplette Cookie Law Plugin</b> installed. Fill out Cookie Policy and Privacy Policy pages, then the activate plugin in Settings > Cookie Settings | <a href="%1$s">Hide Notice</a>'), '?triplette_install_nag_ignore=0');
            echo "</p></div>";
        }
    }

    public function install_nag_ignore()
    {
        global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if (isset($_GET['triplette_install_nag_ignore']) && '0' == $_GET['triplette_install_nag_ignore'] ) {
            add_user_meta($user_id, 'triplette_install_nag_ignore', 'true', true);
        }
    }

    /**
     * Main Triplette_Cookie_Law Instance
     *
     * Ensures only one instance of Triplette_Cookie_Law is loaded or can be loaded.
     *
     * @since  1.0.0
     * @static
     * @see    Triplette_Cookie_Law()
     * @return Main Triplette_Cookie_Law instance
     */
    public static function instance( $file = '', $version = '1.0.0' )
    {
        if (is_null(self::$_instance) ) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    } // End instance ()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __clone ()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __wakeup ()

    /**
     * Installation. Runs on activation.
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function install()
    {
        $this->_log_version_number();
        
        // Create Privacy Policy e Cookie Policy Pages
        $this->create_pages();        
    } // End install ()

    /**
     * Log the plugin version number.
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    } // End _log_version_number ()

}
