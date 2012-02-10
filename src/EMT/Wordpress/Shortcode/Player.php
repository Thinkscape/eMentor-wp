<?php

namespace EMT\Wordpress\Shortcode;

use EMT\Client\Client,
	EMT\Client\Exception as ClientException
;

class Player extends AbstractShortcode
{
	protected $presets = array(
		'basic',
		'clearWidescreen',
	);

	public function render(array $o = array()){
		$client = $this->plugin->getClient();

		if(!isset($o['preset']) || !in_array($o['preset'],$this->presets)){
			$o['preset'] = get_option('wp-ementor-defaultPlayerPreset');
		}

		if(!isset($o['mediaid'])){
			return $this->throwError("Nie podano mediaId");
		}

		/**
		 * Try to determin logged user id
		 */
		$user = $this->plugin->getCurrentUser();
		if(!$user){
			return $this->throwError("Nie jesteś zalogowany. Zaloguj się i wróć na tą stronę.");
		}

		/**
		 * Retrieve embed code
		 */
		try{
			$embeds = $client->getAssociation(
				'media',
				$o['mediaid'],
				'embed',
				array(
					'userId' => $user,
					'template' => $o['preset']
				)
			);
		}catch(ClientException\Unauthorized $e){
			return $this->throwError("Nie masz dostępu do tego materiału. Skontaktuj się z Obsługą Klienta");
		}catch(ClientException $e){
			$error = $this->plugin->clientExceptionToErrorMsg($e);
			return $this->throwError($error);
		}

		/**
		 * Check if embed has been returned
		 */
		if(!count($embeds)){
			return '';
		}

		return $embeds[0]['html'];
	}

	protected function throwError($error){
		return '<div class="error">'.$error.'</div>';
	}
}