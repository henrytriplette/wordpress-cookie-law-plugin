<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Triplette_Cookie_Law_Settings {

	/**
	 * The single instance of Triplette_Cookie_Law_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wpt_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Cookie Settings', 'triplette-cookie-law' ) , __( 'Cookie Settings', 'triplette-cookie-law' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'triplette-cookie-law' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['standard'] = array(
			'title'					=> __( 'Base Settings', 'triplette-cookie-law' ),
			'description'			=> __( 'Cookie Plugin Basic Settings.', 'triplette-cookie-law' ),
			'fields'				=> array(
				array(
					'id' 			=> 'triplette_cookies_enable',
					'label'			=> __( 'Enable Plugin', 'triplette-cookie-law' ),
					'description'	=> __( 'Enable plugin after configuration in complete', 'triplette-cookie-law' ),
					'type'			=> 'select',
					'options'		=> array( 'enable' => 'Enable Plugin', 'disable' => 'Disable Plugin' ),
					'default'		=> 'disable'
				),
				array(
					'id' 			=> 'triplette_cookies_type',
					'label'			=> __( 'Cookies Types', 'triplette-cookie-law' ),
					'description'	=> __( 'Which types of cookies are in use on your site?', 'triplette-cookie-law' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'social' => 'Social Media', 'analytics' => 'Analytics', 'advertising' => 'Advertising', 'necessary' => 'Strictly necessary' ),
					'default'		=> array( 'necessary', 'analytics' )
				),
				array(
					'id' 			=> 'triplette_cookies_consent',
					'label'			=> __( 'Consent Mode', 'triplette-cookie-law' ),
					'description'	=> __( 'Note: Cookie Consent will always use explicit mode when a browser\'s "do not track" setting is enabled (unless overridden in feature options below). The do not track setting is enabled by default in the latest versions of Internet Explorer.', 'triplette-cookie-law' ),
					'type'			=> 'select',
					'options'		=> array( 'explicit' => 'Explicit - no cookies will be set until a visitor consents', 'implicit' => 'Implied - set cookies and allow visitors to opt out' ),
					'default'		=> 'explicit'
				),
				array(
					'id' 			=> 'triplette_cookies_banner_display',
					'label'			=> __( 'Banner display mode', 'triplette-cookie-law' ),
					'description'	=> __( 'Should the banner be shown on every page until consent is gained, or should the banner only show once.', 'triplette-cookie-law' ),
					'type'			=> 'select',
					'options'		=> array( 'false' => 'Show the banner on every page until consent is gained', 'true' => 'Only show the banner on the first page a visitor looks at' ),
					'default'		=> 'false'
				),
				array(
					'id' 			=> 'triplette_cookies_banner_refresh',
					'label'			=> __( 'Do you use server-side scripts which need access to the approved cookies?', 'triplette-cookie-law' ),
					'description'	=> __( 'If you use have a server side application that needs to be aware of the consent to cookies, setting this option to yes will cause the page to be reloaded after consent has been gained.', 'triplette-cookie-law' ),
					'type'			=> 'select',
					'options'		=> array( 'false' => 'No, don\'t refresh the page after gaining consent', 'true' => 'Yes, refresh the page after gaining consent' ),
					'default'		=> 'true'
				),
				array(
					'id' 			=> 'triplette_cookies_do_not_track',
					'label'			=> __( 'Ignore "do not track"', 'triplette-cookie-law' ),
					'description'	=> __( 'Enabling this setting will mean Cookie Consent ignores any do not track headers from the visitor\'s browser.', 'triplette-cookie-law' ),
					'type'			=> 'checkbox',
					'default'		=> 'on'
				),
				array(
					'id' 			=> 'triplette_cookies_privacy_tab',
					'label'			=> __( 'Hide the privacy settings tab', 'triplette-cookie-law' ),
					'description'	=> __( 'If you plan to use your own privacy settings link in-line, you may wish to disable the standard privacy settings tab.', 'triplette-cookie-law' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				),
			)
		);

		$settings['text'] = array(
			'title'					=> __( 'Text', 'triplette-cookie-law' ),
			'description'			=> __( 'Change the text of Cookie Consent.', 'triplette-cookie-law' ),
			'fields'				=> array(
				array(
					'id' 			=> 'tct_general_social_media_title',
					'label'			=> __( 'General - Social media title' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Social media',
					'placeholder'	=> __( 'Social media', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_social_media_description',
					'label'			=> __( 'General - Social media description' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Facebook, Twitter e altri social necessitano di identificare l\'utente per funzionare correttamente.',
					'placeholder'	=> __( 'Facebook, Twitter e altri social necessitano di identificare l\'utente per funzionare correttamente.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_analytics_title',
					'label'			=> __( 'General - Analytics title' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Analytics',
					'placeholder'	=> __( 'Analytics', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_analytics_description',
					'label'			=> __( 'General - Analytics description' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Misuriamo in modo anonimo l\'utilizzo del sito per migliorare l\'esperienza.',
					'placeholder'	=> __( 'Misuriamo in modo anonimo l\'utilizzo del sito per migliorare l\'esperienza.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_advertising_title',
					'label'			=> __( 'General - Advertising title' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Advertising',
					'placeholder'	=> __( 'Advertising', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_advertising_description',
					'label'			=> __( 'General - Advertising description' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Gli annunci saranno scelti automaticamente in base a interessi e azioni passate.',
					'placeholder'	=> __( 'Gli annunci saranno scelti automaticamente in base a interessi e azioni passate.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_necessary_title',
					'label'			=> __( 'General - Necessary title' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Fondamentali',
					'placeholder'	=> __( 'Fondamentali', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_necessary_description',
					'label'			=> __( 'General - Necessary description' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Alcuni cookies sono fondamentali per l\'utilizzo del sito e non possno esser disabilitati.',
					'placeholder'	=> __( 'Alcuni cookies sono fondamentali per l\'utilizzo del sito e non possno esser disabilitati.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_default_cookie_title',
					'label'			=> __( 'General - Default cookie title' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Default cookie title.',
					'placeholder'	=> __( 'Default cookie title.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_default_cookie_description',
					'label'			=> __( 'General - Default cookie description' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Default cookie description.',
					'placeholder'	=> __( 'Default cookie description.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_cookie_help',
					'label'			=> __( 'General - Cookie help link text' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Maggiori informazioni.',
					'placeholder'	=> __( 'Maggiori informazioni.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_general_close_window',
					'label'			=> __( 'General - Close window text' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Chiudi.',
					'placeholder'	=> __( 'Chiudi.', 'triplette-cookie-law' )
				),
				
				// Slide Down
				
				array(
					'id' 			=> 'tct_slide_title',
					'label'			=> __( 'Slide Down - Notification title text' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Questo sito o gli strumenti terzi da questo utilizzati si avvalgono di cookie necessari al funzionamento ed utili alle finalità illustrate nella cookie policy. Se vuoi saperne di più o negare il consenso a tutti o ad alcuni cookie, consulta cookie settings. Chiudendo questo banner, scorrendo questa pagina, cliccando su un link o proseguendo la navigazione in altra maniera, acconsenti all’uso dei cookie.',
					'placeholder'	=> __( 'Questo sito o gli strumenti terzi da questo utilizzati si avvalgono di cookie necessari al funzionamento ed utili alle finalità illustrate nella cookie policy. Se vuoi saperne di più o negare il consenso a tutti o ad alcuni cookie, consulta cookie settings. Chiudendo questo banner, scorrendo questa pagina, cliccando su un link o proseguendo la navigazione in altra maniera, acconsenti all’uso dei cookie.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_slide_title_implicit',
					'label'			=> __( 'Slide Down - Notification title text for implicit consent' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Questo sito o gli strumenti terzi da questo utilizzati si avvalgono di cookie necessari al funzionamento ed utili alle finalità illustrate nella cookie policy. Se vuoi saperne di più o negare il consenso a tutti o ad alcuni cookie, consulta cookie settings. Chiudendo questo banner, scorrendo questa pagina, cliccando su un link o proseguendo la navigazione in altra maniera, acconsenti all’uso dei cookie.',
					'placeholder'	=> __( 'Questo sito o gli strumenti terzi da questo utilizzati si avvalgono di cookie necessari al funzionamento ed utili alle finalità illustrate nella cookie policy. Se vuoi saperne di più o negare il consenso a tutti o ad alcuni cookie, consulta cookie settings. Chiudendo questo banner, scorrendo questa pagina, cliccando su un link o proseguendo la navigazione in altra maniera, acconsenti all’uso dei cookie.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_slide_custom_cookie',
					'label'			=> __( 'Slide Down - Custom cookie title text' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Questo sito utilizza cookies personalizzati che necessita approvazione specifica.',
					'placeholder'	=> __( 'Questo sito utilizza cookies personalizzati che necessita approvazione specifica.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_slide_see_details',
					'label'			=> __( 'Slide Down - See details link' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Dettagli.',
					'placeholder'	=> __( 'Dettagli.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_slide_see_details_implicit',
					'label'			=> __( 'Slide Down - See details link for implicit consent' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'cambia impostazioni',
					'placeholder'	=> __( 'cambia impostazioni', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_slide_hide_details_link',
					'label'			=> __( 'Slide Down - Hide details link' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Nascondi dettagli',
					'placeholder'	=> __( 'Nascondi dettagli', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_slide_allow_cookies_button',
					'label'			=> __( 'Slide Down - Allow cookies button' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Consenti cookies',
					'placeholder'	=> __( 'Consenti cookies', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_slide_allow_cookies_global_button',
					'label'			=> __( 'Slide Down - Allow global cookies button' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Consenti i cookies',
					'placeholder'	=> __( 'Consenti cookies', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_slide_allow_cookies_button_implicit',
					'label'			=> __( 'Slide Down - Allow cookies button for implicit consent' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Chiudi',
					'placeholder'	=> __( 'Chiudi', 'triplette-cookie-law' )
				),
				
				// Privacy Settings
				
				array(
					'id' 			=> 'tct_privacy_settings',
					'label'			=> __( 'Privacy Settings - Tag text' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Impostazioni Privacy',
					'placeholder'	=> __( 'Impostazioni Privacy ', 'triplette-cookie-law' )
				),
				
				// Privacy Settings Dialog
				
				array(
					'id' 			=> 'tct_privacy_settings_dialog',
					'label'			=> __( 'Privacy Settings - Privacy dialog title large part' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Impostazioni Privacy',
					'placeholder'	=> __( 'Impostazioni Privacy', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_dialog_small',
					'label'			=> __( 'Privacy Settings - Privacy dialog title small part' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'per questo sito',
					'placeholder'	=> __( 'per questo sito', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_dialog_subtitle',
					'label'			=> __( 'Privacy Settings - Privacy dialog subtitle' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Alcune funzionalità necessitano la tua approvazione per poter esser attivate.',
					'placeholder'	=> __( 'Alcune funzionalità necessitano la tua approvazione per poter esser attivate.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_policy_link',
					'label'			=> __( 'Privacy Settings - Privacy Policy Link' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> get_site_url().'/privacy-policy',
					'placeholder'	=> __( get_site_url().'/privacy-policy', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_policy_title',
					'label'			=> __( 'Privacy Settings - Privacy Policy Title' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Privacy Policy.',
					'placeholder'	=> __( 'Privacy Policy.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_cookie_policy_link',
					'label'			=> __( 'Privacy Settings - Cookie Policy Link' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> get_site_url().'/cookie-policy',
					'placeholder'	=> __( get_site_url().'/cookie-policy', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_cookie_policy_title',
					'label'			=> __( 'Privacy Settings - Cookie Policy Title' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Cookie Policy.',
					'placeholder'	=> __( 'Cookie Policy.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_dialog_all_websites',
					'label'			=> __( 'Privacy Settings - Change settings for all websites link' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Cambia impostazioni globali',
					'placeholder'	=> __( 'Cambia impostazioni globali', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_dialog_consent',
					'label'			=> __( 'Privacy Settings - Cookie option - Use global setting' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Usa impostazioni globali',
					'placeholder'	=> __( 'Usa impostazioni globali', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_dialog_i_consent',
					'label'			=> __( 'Privacy Settings - Cookie option - I consent' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Acconsento',
					'placeholder'	=> __( 'Acconsento', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_dialog_i_decline',
					'label'			=> __( 'Privacy Settings - Cookie option - I decline' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Rifiuta',
					'placeholder'	=> __( 'Rifiuta', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_privacy_settings_dialog_no_cookies',
					'label'			=> __( 'Privacy Settings - No cookies warning' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Questo sito web non utilizza cookies.',
					'placeholder'	=> __( 'Questo sito web non utilizza cookies.', 'triplette-cookie-law' )
				),
				
				// Global Dialog
				
				array(
					'id' 			=> 'tct_global_settings_dialog_title',
					'label'			=> __( 'Global settings dialog - Global dialog title large part' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Impostazioni Privacy',
					'placeholder'	=> __( 'Impostazioni Privacy', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_global_settings_dialog_title_small',
					'label'			=> __( 'Global settings dialog - Global dialog title small part' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'per tutti i siti',
					'placeholder'	=> __( 'per tutti i siti', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_global_settings_dialog_subtitle',
					'label'			=> __( 'Global settings dialog - Global dialog subtitle' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Puoi accettare i cookies per tutti i siti dotati di questo plugin.',
					'placeholder'	=> __( 'Puoi accettare i cookies per tutti i siti dotati di questo plugin.', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_global_settings_back_to',
					'label'			=> __( 'Global settings dialog - Back to website settings link' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Ritorna alle impostazioni',
					'placeholder'	=> __( 'Ritorna alle impostazioni', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_global_settings_ask',
					'label'			=> __( 'Global settings dialog - Cookie option - Ask me each time' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Chiedi ad ogni accesso',
					'placeholder'	=> __( 'Chiedi ad ogni accesso', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_global_always_allow',
					'label'			=> __( 'Global settings dialog - Cookie option - Always allow' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Consenti Sempre',
					'placeholder'	=> __( 'Consenti Sempre', 'triplette-cookie-law' )
				),
				array(
					'id' 			=> 'tct_global_never_allow',
					'label'			=> __( 'Global settings dialog - Cookie option - Never allow' , 'triplette-cookie-law' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> 'Blocca Sempre',
					'placeholder'	=> __( 'Blocca Sempre', 'triplette-cookie-law' )
				),
			)
		);

		$settings['style'] = array(
			'title'					=> __( 'Style', 'triplette-cookie-law' ),
			'description'			=> __( 'Change the look of Cookie Consent.', 'triplette-cookie-law' ),
			'fields'				=> array(
				array(
					'id' 			=> 'triplette_cookies_style',
					'label'			=> __( 'Base Style', 'triplette-cookie-law' ),
					'description'	=> __( 'Change the look of Cookie Consent.', 'triplette-cookie-law' ),
					'type'			=> 'select',
					'options'		=> array( 'light' => 'Light', 'dark' => 'Dark', 'monochrome' => 'Mono' ),
					'default'		=> 'light'
				),
				array(
					'id' 			=> 'triplette_cookies_position_banner',
					'label'			=> __( 'Banner Position', 'triplette-cookie-law' ),
					'description'	=> __( 'Change the position of Cookie Consent Bar.', 'triplette-cookie-law' ),
					'type'			=> 'select',
					'options'		=> array( 'top' => 'Top', 'bottom' => 'Bottom', 'push' => 'Push from top (experimental)' ),
					'default'		=> 'top'
				),
				array(
					'id' 			=> 'triplette_cookies_position_tag',
					'label'			=> __( 'Tag Position', 'triplette-cookie-law' ),
					'description'	=> __( 'Choose where the privacy settings tag appears.', 'triplette-cookie-law' ),
					'type'			=> 'select',
					'options'		=> array( 'bottom-right' => 'Bottom right', 'bottom-left' => 'Bottom left', 'vertical-left' => 'Left side', 'vertical-right' => 'Right side' ),
					'default'		=> 'bottom-right'
				),
				array(
					'id' 			=> 'triplette_cookies_custom_style',
					'label'			=> __( 'Custom CSS Style' , 'triplette-cookie-law' ),
					'description'	=> __( 'Add here your custom CSS Style.', 'triplette-cookie-law' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( '', 'triplette-cookie-law' )
				),
			),
		$settings['info'] = array(
			'title'					=> __( 'Useful Info', 'triplette-cookie-law' ),
			'description'			=> __( 'Cookie Plugin Setup info. More instructions <a target="_blank" href="http://sitebeam.net/cookieconsent/documentation/code-examples/">HERE</a>', 'triplette-cookie-law' ),
			'fields'				=> array(
				array(
					'id' 			=> 'triplette_cookies_codegen',
					'label'			=> __( 'Generate Code', 'triplette-cookie-law' ),
					'description'	=> __( 'Note: Paste following code into your template ', 'triplette-cookie-law' ),
					'type'			=> 'select',
					'options'		=> array( 'analytics' => 'Analytics tracking code', 'advertising' => 'Advertising code', 'social' => 'Social plugins and widgets' ),
					'default'		=> 'analytics'
				),
				array(
					'id' 			=> 'triplette_cookie_output',
					'label'			=> __( 'Code Output' , 'triplette-cookie-law' ),
					'description'	=> __( 'Generated Code to paste.', 'triplette-cookie-law' ),
					'type'			=> 'textarea',
					'default'		=> '<script type="text/plain" class="cc-onconsent-analytics">',
					'placeholder'	=> __( '<script type="text/plain" class="cc-onconsent-analytics">', 'wordpress-plugin-template' )
				),
			),
		),
	);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Cookie Settings' , 'triplette-cookie-law' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'triplette-cookie-law' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main Triplette_Cookie_Law_Settings Instance
	 *
	 * Ensures only one instance of Triplette_Cookie_Law_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Triplette_Cookie_Law()
	 * @return Main Triplette_Cookie_Law_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}