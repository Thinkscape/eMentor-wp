<?php
namespace EMT\Wordpress;

use EMT\Client\Client,
	EMT\Client\Exception as ClientException
;

class Plugin
{
	protected $shortCodes = array(
		'ementor_player' => '\EMT\Wordpress\Shortcode\Player',
	);

	protected $options = array(
		'group1' => array(
			'group' => 'wp-ementor-general',
			'name' => null,
			'type' => 'heading',
			'label' => 'Dane dostępowe',
			'default' => false
		),
		'emt-api-server' => array(
			'group' => 'wp-ementor-general',
			'name' => 'Adres serwera API',
			'label' => 'Pełny adres serwera API, np. https://www.ementor.pl/api/v2',
			'default' => 'https://www.ementor.pl/api/v2'
		),
		'wp-ementor-adminUserEmail' => array(
			'group' => 'wp-ementor-general',
			'name' => 'Adres email konta autora',
			'label' => 'Adres którym logujesz się do Panelu Autora',
			'default' => ''
		),
		'emt-api-keyId' => array(
			'group' => 'wp-ementor-general',
			'name' => 'ID klucza API',
			'label' => 'Wpisz ID twojego klucza API. Jeśli nie posiadasz klucza, skontaktuj się z Obsługą Klienta ',
			'default' => ''
		),
		'emt-api-keySecret' => array(
			'group' => 'wp-ementor-general',
			'name' => 'Secret klucza API',
			'type' => 'password',
			'label' => 'Wpisz lub wklej wartość secret klucza. Wielkości liter mają znaczenie i nie ma żadnych spacji.',
			'default' => ''
		),

		// ------------------------
		'group2' => array(
			'group' => 'wp-ementor-general',
			'name' => null,
			'type' => 'heading',
			'label' => 'Ustawienia wyświetlania',
			'default' => false
		),
		'wp-ementor-defaultPlayerPreset' => array(
			'group' => 'wp-ementor-general',
			'name' => 'Domyślny format odtwarzacza',
			'type' => 'select',
			'options' => array(
				'basic' => 'basic (mały embed z czarną ramką, przycisk play i tytuł)',
				'clearWidescreen' => 'clearWidescreen (duży embed 16:9 bez ramki, przycisk play i tytuł)',
			),
			'label' => 'Wygląd odtwarzacza, gdy wstawiasz media z ustawionym formatem "domyślny"',
			'default' => 'clearWidescreen'
		),
		'wp-ementor-maxMediaItems' => array(
			'group' => 'wp-ementor-general',
			'name' => 'Ilość media na ekranie',
			'label' => 'W okienku wstawiania media do wpisu, ile pozycji wyświetlać na każdym ekranie (max 100)',
			'default' => '50'
		),
		'wp-ementor-showAdminBarStats' => array(
			'group' => 'wp-ementor-general',
			'name' => 'Statystyki',
			'type' => 'checkbox',
			'label' => 'Wyświetlaj statystyki w górnym pasku narzędziowym',
			'default' => true
		),

		// ------------------------
		'group3' => array(
			'group' => 'wp-ementor-general',
			'name' => null,
			'type' => 'heading',
			'label' => 'Program Partnerski',
		),
		'wp-ementor-affEnabled' => array(
			'group' => 'wp-ementor-general',
			'name' => 'Śledzenie linków',
			'type' => 'checkbox',
			'label' => 'Włącz obsługę śledzenia linków partnerskich dla tej witryny',
			'default' => 0
		),
		'wp-ementor-affDomain' => array(
			'group' => 'wp-ementor-general',
			'name' => 'Domena serwisu (PP)',
			'label' => 'Główna domena serwisu, dla której tworzone będą linki partnerskie. Musi ona zawierać domenę
			obecnego serwisu Wordpress, może wskazywać na domenę nadrzędną lub na dowolną subdomenę.',
			'default' => array('\EMT\Wordpress\Util','getDefaultAffDomain'),
			'sanitize' => array('\EMT\Wordpress\Util','sanitizeAffDomain'),
		),
		'wp-ementor-affLinkId' => array(
			'group' => 'wp-ementor-general',
			'name' => null,
			'type' => 'hidden',
			'label' => '(ustawiane automatycznie, nie modyfikować!)',
			'default' => null,

		),
	);

	protected $optionGroups = array(
		'wp-ementor-general' => '',
//		'wp-ementor-interface' => 'Ustawienia wyświetlania',
	);

	protected $affIdParams = array('EMTAFF','emtaff','aff','a');

	protected $affTracker = array(
		'js'  => '<script type="text/javascript" src="http://pp.ementor.pl/track/:linkId/:affId/js?ref=:ref"></script>',
		'img' => '<img src="http://pp.ementor.pl/track/:linkId/:affId/img?ref=:ref" alt="" />',
	);

	/**
	 * @var Renderer
	 */
	protected $renderer;

	/**
	 * @var Client
	 */
	protected $client;

	public function __construct(){
		$this->getRenderer();
		add_action( 'init', array($this, 'init') );
		register_activation_hook( EMT_ABSPLUGIN, array($this, 'activate') );
		register_deactivation_hook(EMT_ABSPLUGIN, array($this, 'deactivate') );

	}

	public function init() {
		/**
		 * Register shortcodes
		 */
		$plugin = &$this;
		foreach($this->shortCodes as $code => $class){
			add_shortcode( $code, function($attributes) use ($class, &$plugin){
				$shortcode = new $class($plugin);
				return $shortcode->render($attributes);
			});
		}

		/**
		 * Register AFF links tracker
		 */
		if(get_option('wp-ementor-affEnabled',false)){
			add_action('wp_footer', array($this,'handleAffFooter'));
		}

		/**
		 * Run admin hooks
		 */
		if (is_admin()) {
			add_action( 'admin_init', array($this, 'initAdmin') );
			add_action( 'admin_menu', array($this, 'initAdminMenu'), 0 );
		}

		if(session_id() === ""){
			session_start();
		}
	}

	public function initUploadTab($tabs){
		$tabs['ementor'] = 'eMentor';
		return $tabs;
	}

	public function initAdmin(){
		/**
		 * Handle aff domain change by the user
		 */
		add_filter('pre_update_option_wp-ementor-affDomain',array($this,'handleAffDomainChange'),10,2);
		add_filter('pre_update_option_wp-ementor-affEnabled',array($this,'handleAffEnable'),10,2);


		/**
		 * Register plugin settings
		 */
		foreach($this->options as $id => $option){
			if(!$option['name']) continue;

			register_setting( $option['group'], $id, isset($option['sanitize']) ? $option['sanitize'] : '' );

			// set default value
			if($option['default'] && get_option($id,'_THIS_IS_UNDEFINED_') === '_THIS_IS_UNDEFINED_'){
				if(is_array($option['default']) && is_callable($option['default'])){
					$value = call_user_func($option['default']);
					update_option($id,$value);
				}else{
					update_option($id,$option['default']);
				}
			}
		}

		/**
		 * Register setting sections (groups)
		 */
		foreach($this->optionGroups as $id=>$name){
			add_settings_section(
				$id,
				$name,
				array($this->renderer,'adminOptionsSection'),
				'wp-ementor-settings'
			);
		}

		/**
		 * Add settings fields
		 */
		foreach($this->options as $id=>$option){
			if(isset($option['hidden']) && $option['hidden']) continue;

			add_settings_field(
				$id,
				$option['name'],
				array($this->renderer,'adminOptionsEntry'),
				'wp-ementor-settings',
				$option['group'],
				array_merge(
					$option,
					array(
						'id' => $id,
						'value' => get_option($id),
					)
				)
			);
		}

		/**
		 * Register upload tab
		 */
		add_filter('media_upload_tabs', array($this,'initUploadTab'));
		add_action('media_upload_ementor',array($this,'insertMedia'));


		/**
		 * Use stylesheet on admin page
		 */
		wp_register_style( 'wp-ementor-admin', plugins_url( 'css/wp-ementor-admin.css', EMT_ABSPLUGIN ) );
		wp_enqueue_style( 'wp-ementor-admin' );

		/**
		 * Register scripts
		 */
		wp_register_script(
			'wp-ementor-admin-media-tab',
			plugins_url('js/wp-ementor-admin-media-tab.js',EMT_ABSPLUGIN)
		);

		/**
		 * Add admin-bar menu
		 */
		if(get_option('wp-ementor-showAdminBarStats')){
			add_action( 'admin_bar_menu', array($this->renderer,'adminBarStats'),100);
		}
	}

	public function initAdminMenu() {
		add_options_page(
			'Integracja z platformą eMentor.pl',
			'eMentor',
			'manage_options',
			'wp-ementor-settings',
			array($this->renderer, 'optionsPage')
		);

		add_menu_page(
			'Integracja z platformą eMentor.pl',
			'eMentor',
			'manage_options',
			'wp-ementor-products',
			'',
			plugins_url('img/menu.png',EMT_ABSPLUGIN),
			Util::findHighestAdminMenuPosition(20)
		);

		add_submenu_page(
			'wp-ementor-products',
			'Lista sprzedawanych produktów',
			'Produkty',
			'manage_options',
			'wp-ementor-products',
			array($this->renderer, 'productsPage')
		);

		add_submenu_page(
			'wp-ementor-products',
			'Biblioteka mediów eMentor',
			'Media',
			'manage_options',
			'wp-ementor-media',
			array($this->renderer, 'mediaPage')
		);

		// fix wordpress menu (?)
		ksort($GLOBALS['menu']);


	}

	public function insertMedia(){
//		if(!empty($_POST)){
//			check_admin_referer('media-form');
//			if ( !current_user_can( $post_type_object->cap->edit_post, $attachment_id ) )
//				continue;
//		}else{
			return wp_iframe(array($this->renderer,'insertMediaTab'));
//		}
	}

	public function activate(){
		$this->testConnection();
		//trigger_error('FOOOOOOOOOOOOOOOOO', E_USER_ERROR);
//		trigger_error('', E_USER_ERROR);
//		echo "ERRRRRRRRRORRRRRRRRRRRR!!!!!!!!!";
//		throw new \Exception('fuuuuuuuuuuuuuuuuuuuuuuu');
	}

	public function deactivate(){

	}

	/**
	 * @return bool|string
	 */
	public function getCurrentUser(){

//		$_SESSION['EMTUser']['email'] = 'acmeclient@ementor.pl';

		if(
			isset($_SESSION['EMTUser']) &&
			isset($_SESSION['EMTUser']['email'])
		){
			return $_SESSION['EMTUser']['email'];
		}elseif(
			is_user_logged_in() &&
			current_user_can('manage_options') &&
			($adminEmail = get_option('wp-ementor-adminUserEmail'))
		){
			return $adminEmail;
		}elseif(
			is_user_logged_in() &&
			($user = wp_get_current_user()) &&
			($user instanceof \WP_User) &&
			$user->user_email
		){
			return $user->user_email;
		}

		return false;
	}

	/**
	 * Try to ping Api server. Return true on success, or false on error.
	 * An error message will be also be shown on the screen.
	 *
	 * @return bool		True on success
	 */
	public function testConnection(){
		try{
			$this->getClient()->ping();
		}catch(ClientException $e){
	//		Util::showAdminNotice($this->clientExceptionToErrorMsg($e,'Próba połączenia z serwerem API'));
			return false;
		}

		return true; // all went well
	}

	/**
	 * Transform a EMT\Client exception into a human-readable error message.
	 *
	 * @param \EMT\Client\Exception $e				The exception that has been caught
	 * @param string                $operation		(optional) The operation that was performed
	 * @return string								Error message
	 */
	public function clientExceptionToErrorMsg(ClientException $e, $operation = ''){
		/** @var \Exception $e */
		if($e instanceof ClientException\Unauthorized){
			return
				($operation ? '<strong>'.$operation.'</strong><br/>' : '').
				'Serwer API odmówił dostępu. Sprawdź czy wpisane w ustawieniach ID i Secret klucza są poprawne ('.
				$e->getMessage().
				')'
			;
		}elseif($e instanceof ClientException\Conflict){
			return
				($operation ? '<strong>'.$operation.'</strong><br/>' : '').
				'Nie można wykonać operacji, ponieważ wystąpił konflikt ('.$e->getMessage().')'
			;
		}elseif($e instanceof ClientException\ConnectionFailed){
			return
				($operation ? '<strong>'.$operation.'</strong><br/>' : '').
				'Nie można połączyć się z serwerem API. Sprawdź czy wpisany w ustawieniach adres serwera API jest'.
				'poprawny i czy twój serwer może wykonywać zewnętrzne połączenia HTTPS ('.$e->getMessage().')'
			;
		}elseif($e instanceof ClientException\NotFound){
			return
				($operation ? '<strong>'.$operation.'</strong><br/>' : '').
				'Nie odnaleziono elementu ('.$e->getMessage().')'
			;
		}elseif($e instanceof ClientException\ServerError){
			return
				($operation ? '<strong>'.$operation.'</strong><br/>' : '').
				'Wystąpił błąd serwera. Spróbuj ponownie za chwilę lub skontaktuj się z Obsługą Klienta eMentor '.
				'('.$e->getMessage().')'
			;
		}elseif($e instanceof ClientException\NotImplemented){
			return
				($operation ? '<strong>'.$operation.'</strong><br/>' : '').
				'Ta funkcja nie jest obsługiwana przez serwer API. Jeśli uważasz, że wystąpił błąd, skontaktuj się '.
				'z Obsługą Klienta eMentor ('.$e->getMessage().')'
			;
		}elseif($e instanceof ClientException\BadQuery){
			return
				($operation ? '<strong>'.$operation.'</strong><br/>' : '').
				'Nie można było wykonać operacji - sprawdź poprawność wpisanych danych i spróbuj ponownie. '.
				'Jeśli uważasz, że wystąpił błąd, skontaktuj się z Obsługą Klienta eMentor ('.$e->getMessage().')'
			;
		}else{
			return
				($operation ? '<strong>'.$operation.'</strong><br/>' : '').
				'Wystąpił nieoczekiwany błąd, sprawdź poprawność wpisanych danych i spróbuj ponownie '.
				'('.get_class($e).': '.$e->getMessage().')'
			;
		}
	}

	public function handleAffDomainChange($newDomain, $oldDomain){
		if($newDomain == $oldDomain)
			return $newDomain; // nothing to do, nothing changes

		/**
		 * Validate domain
		 */
		if(strlen($newDomain) > 100){
			add_settings_error(
				'wp-ementor-affSiteDomain',
				'',
				'Długość domeny nie może przekraczać 100 znaków.'
			);
			return '';
		}

		/**
		 * Try to connect to the API and retrieve aff link
		 */
		try{
			$client = $this->getClient();
			$links = $client->findAll('Affsitelink',array(
				'domain' => $newDomain
			));
			if(!count($links)){
				throw new ClientException\ServerError('Cannot retrieve site link');
			}
			$link = $links[0];

			/**
			 * Store aff link id
			 */
			update_option('wp-ementor-affLinkId',$link->id);
			return $newDomain;

		}catch(ClientException $e){
			add_settings_error(
				'wp-ementor-affSiteDomain',
				'',
				$this->clientExceptionToErrorMsg($e,'Włączenie obsługi Programu Partnerskiego')
			);
			return '';
		}
	}

	public function handleAffEnable($newVal, $oldVal){
		if($newVal && !$oldVal){
			/**
			 * Check if aff domain is set
			 */
			if(!get_option('wp-ementor-affDomain')){
				add_settings_error(
					'wp-ementor-affEnabled',
					'',
					'Przed włączeniem obsługi Programu Partnerskiego wpisz poprawną nazwę domeny serwisu.'
				);
				return false;
			}else{
				/**
				 * Check if aff link id is set
				 */
				if(!get_option('wp-ementor-affLinkId')){
					// force reload aff link id
					$this->handleAffDomainChange(get_option('wp-ementor-affDomain'),false);

					if(!get_option('wp-ementor-affLinkId')){
						/**
						 * Cannot enable at this point
						 */
						return false;
					}
				}
			}
		}

		return (bool)$newVal;
	}

	/**
	 * Render aff-related widgets at the bottom of the page
	 */
	public function handleAffFooter(){
		$method = 'js';
		foreach($this->affIdParams as $p){
			if(isset($_GET[$p]) && strlen($_GET[$p]) > 0){
				/**
				 * Determine referrer url
				 */
				if($ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null){
					$ref = htmlentities(urlencode($ref));
				}

				/**
				 * Extract and sanitize affId
				 */
				$affId = substr(preg_replace('/[^a-zA-Z0-9\-\_\=]/','',$_GET[$p]),0,25);

				/**
				 * Get link id
				 */
				$linkId = get_option('wp-ementor-affLinkId');
				if(!$linkId) return;

				/**
				 * Build html fragment
				 */
				$frag = str_replace(
					array(
						':affId',
						':linkId',
						':ref'
					),array(
						$affId,
						$linkId,
						$ref
					),
					$this->affTracker[$method]
				);

				/**
				 * Echo it out
				 */
				echo $frag;
			}
		}
	}

	/**
	 * @param \EMT\Wordpress\Renderer $renderer
	 */
	public function setRenderer($renderer) {
		$this->renderer = $renderer;
	}

	/**
	 * @return \EMT\Wordpress\Renderer
	 */
	public function getRenderer() {
		if(!$this->renderer){
			$this->renderer = new Renderer($this, EMT_ABSPATH . '/templates');
		}
		return $this->renderer;
	}

	/**
	 * @param \EMT\Client\Client $client
	 */
	public function setClient($client) {
		$this->client = $client;
	}

	/**
	 * @return \EMT\Client\Client
	 */
	public function getClient() {
		if(!$this->client){
			$this->client = new Client(
				get_option('emt-api-keyId'),
				get_option('emt-api-keySecret'),
				get_option('emt-api-server')
			);
		}

		return $this->client;
	}

	public function getOptionGroups() {
		return $this->optionGroups;
	}

	public function getOptions() {
		return $this->options;
	}
}
