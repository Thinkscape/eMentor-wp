<?php
namespace EMT\Wordpress;


use EMT\Client\Client,
	EMT\Client\Exception as ClientException
;

class Renderer
{
	/**
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * @var string
	 */
	protected $templatePath = '';

	public function __construct(Plugin $plugin, $templatePath){
		$this->plugin = $plugin;
		$this->templatePath = $templatePath;
	}

	public function optionsPage() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		require $this->templatePath .'/options.php';
	}

	public function notImplementedPage() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		require $this->templatePath .'/notImplemented.php';
	}

	public function editPage() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		/** @var \wpdb $wpdb */
		global $wpdb;

		$entries = $wpdb->get_results('SELECT * FROM '.$this->dbTable.' ORDER BY dateCreated DESC LIMIT 10',ARRAY_A);
		foreach(array_keys($entries) as $key){
			$entries[$key]['url'] = $this->getUri($entries[$key]['key']);
		}

		wp_register_script( 'wp-ementor-webcam-admin', plugins_url( 'js/webcam-admin.js', __FILE__ ) );
		wp_register_script( 'ext-core-3.0.0', plugins_url( 'js/ext/ext-core-debug.js', __FILE__ ) );
		wp_register_script( 'JSON', plugins_url( 'js/JSON.min.js', __FILE__ ) );

		wp_enqueue_script( 'ext-core-3.0.0' );
		wp_enqueue_script( 'JSON' );
		wp_enqueue_script( 'wp-ementor-webcam-admin' );


		$oldLocale = get_locale();
		setlocale('LC_ALL','pl_PL.utf8');
		include EMT_ABSPATH .  '/templates/edit.php';
		setlocale('LC_ALL',$oldLocale);

	}

	public function productsPage(){
		$client = $this->plugin->getClient();
		try{
			$products = $client->findAll('product',array(),'dateCreated','DESC');
		}catch(ClientException $e){
			$error = $this->plugin->clientExceptionToErrorMsg($e,'Wczytywanie listy produktów');
			require $this->templatePath . '/clientException.php';
			return;
		}

		require $this->templatePath . '/products.php';
	}

	public function mediaPage(){
		$client = $this->plugin->getClient();
		try{
			$media = $client->findAll('media',array(),'dateCreated','DESC');
		}catch(ClientException $e){
			$error = $this->plugin->clientExceptionToErrorMsg($e,'Wczytywanie listy mediów');
			require $this->templatePath . '/clientException.php';
			return;
		}

		require $this->templatePath . '/media.php';
	}

	public function insertMediaTab(){
		wp_enqueue_style( 'media' );
		wp_enqueue_script( 'wp-ementor-admin-media-tab' );

		$params = array();
		$page = 1;
		$search = $productId = $offset = $limit = null;

		/**
		 * Parse query params
		 */
		if(!empty($_GET['s'])){
			$search = trim($_GET['s']);
			$params[] = array('name','like',$search);
		}

		if(!empty($_GET['productId'])){
			$productId = trim(preg_replace('/[^a-zA-Z0-9\_\-]/','',$_GET['productId']));
			$params[] = array('productId',$productId);
		}


		/**
		 * Paging
		 */
		$limit = max(min(get_option('wp-ementor-maxMediaItems',10),100),1);
		$page = max((int)$_GET['wp-ementor-page'],1);
		if($page > 1){
			$offset = ($page - 1 ) * $limit;
		}

		$client = $this->plugin->getClient();
		try{
			$products = $client->findAll('product',array(
				'type' => array(1,4)
			),'name','ASC');
			$media = $client->findAll('media',$params,'name','ASC',$limit,$offset);
		}catch(ClientException $e){
			$error = $this->plugin->clientExceptionToErrorMsg($e,'Wczytywanie listy mediów');
			require $this->templatePath . '/clientException.php';
			return;
		}

		require $this->templatePath . '/mediaTab.php';
	}

	public function adminOptionsEntry($params){
		if(!$params['section']) $params['section'] = 'wp-ementor-webcam';

		switch($params['type']){
			case 'heading':
				echo '</td></tr><tr valign="top"><td colspan="2"><h4>' . $params['label'] . '</h4>';
				break;

			case 'checkbox':
				echo
					'<input class="checkbox' . (isset($params['class']) ? $params['class'] : '') .
					'" type="checkbox" id="' . htmlentities( $params['id'] ) . '" name="' .
					htmlentities( $params['id'] ) . '" value="1" ' . checked( $params['value'], 1, false ) .
					' /> <label for="' . esc_attr($params['id']) . '">' . $params['label'] . '</label>'
				;
				break;

			case 'select':
				echo '<select class="select' . (isset($params['class'])?$params['class']:'') . '" name="'.htmlentities($params['id']).'">';
				foreach ( $params['options'] as $value => $label ){
					echo '<option value="' . esc_attr( $value ) . '"' .
						selected( $value,  $params['value'], false ) .
						 '>' . $label . '</option>';
				}
				echo '</select>';
				if ( $params['label'] != '' )
					echo '<br /><span class="description">' . $params['label'] . '</span>';

				break;

			case 'radio':
				$i = 0;
				foreach ( $params['options'] as $value => $label ) {
					echo
						'<input class="radio' . (isset($params['class'])?$params['class']:'') .
						'" type="radio" name="'.htmlentities($params['id']).'" id="' . esc_attr($params['id']. $i ).
						'" value="' . esc_attr( $params['value'] ) . '" ' .
						checked( $value, $params['value'], false ) .
						'> <label for="' . esc_attr($params['id']. $i ). '">' . $label . '</label>'
					;
					if ( $i < count( $params['options'] ) - 1 ){
						echo '<br />';
					}

					$i++;
				}

				if ( $params['label'] != '' )
					echo '<br /><span class="description">' . $params['label'] . '</span>';

				break;

			case 'textarea':
				echo
					'<textarea class="' . (isset($params['class'])?$params['class']:'') . '" id="'.
					htmlentities($params['id']) .'" name="'.htmlentities($params['id']).'"
					placeholder="' . $params['placeholder'] . '" rows="5" cols="30">' .
					wp_htmledit_pre( $params['id'] ) . '</textarea>';

				if ( $params['label'] != '' )
					echo '<br /><span class="description">' . $params['label'] . '</span>';

				break;

			case 'password':
				echo '<input class="regular-text' . (isset($params['class'])?$params['class']:'') .
				'" type="password" id="'. htmlentities($params['id']) .
				'" name="'.htmlentities($params['id']).'" value="' .
				esc_attr( $params['value'] ) . '" />';

				if ( $params['label'] != '' )
					echo '<br /><span class="description">' . $params['label'] . '</span>';

				break;

			case 'text':
			default:
				echo
					'<input name="'.htmlentities($params['id']).'" id="'.htmlentities($params['id']).
					'" type="text" value="' . htmlentities($params['value']) .
					'" class="regular-text" />'
				;
				if ( $params['label'] != '' )
					echo '<br /><span class="description">' . $params['label'] . '</span>';
				break;
		}
	}


	public function adminOptionsSection() {
		return;
	}

		public function getImagePath(){
		return ABSPATH.'/'.get_option('ts_bmcars_webcam_path');
	}

	public function getUri($key, $absolute = true){
		if($absolute)
			return home_url('/') . 'kamerka/' . $key;
		else
			return 'kamerka/'.$key;
	}






}