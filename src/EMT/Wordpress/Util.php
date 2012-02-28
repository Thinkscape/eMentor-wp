<?php
namespace EMT\Wordpress;

class Util
{
	/**
	 * Find next, highest, free admin menu position.
	 * Used together with add_menu_page()
	 *
	 * @see add_menu_page()
	 * @static
	 * @param int $min		Minimum (lowest) desired position
	 * @return int
	 */
	public static function findHighestAdminMenuPosition($min = 5){
		$result = 0;
		$positions = array_keys($GLOBALS['menu']);
		sort($positions);
		for ($x = 0; $x < (count( $positions ) - 1); $x++) {
			if (
				($positions[$x] >= $min) &&
				!array_key_exists($positions[$x]+1,$GLOBALS['menu'])
			) {
				return $positions[$x]+1;
			}
		}
		return $positions[count($positions)-1]+1;
	}

	/**
	 * Find a page by its name (slug) and return absolute URL (permalink) pointing to it.
	 *
	 * @param string $pageName	Page name (slug)
	 * @return bool|string		Full page URL or false if page not found
	 */
	public static function getPageUrl($pageName = ''){
		$query = new WP_Query( 'pagename='.$pageName );
		$posts = $query->get_posts();
		if(count($posts))
			return get_permalink($posts[0]->ID);
		else
			return false;
	}

	public static function getMediaThumbUrl($productId, $mediaId, $format = 'small'){
		return 'http://www.ementor.pl/img/thumbs/'.$format.'/'.$productId.'-'.$mediaId.'.jpg';
	}

	public static function getProductThumbUrl($productId, $format = 'small'){
		return 'http://www.ementor.pl/img/thumbs/'.$format.'/'.$productId.'.jpg';
	}

	public static function getDottedTime($time = 0,$miliseconds = false){
		$h = 0;
		$m = 0;
		$s = 0;
		$ms = 0;

		if($time >= 3600){
			$h = floor($time / 3600);
			$m = floor(($time - $h*3600) / 60);
			$s = floor(($time - $h*3600 - $m*60) % 60) ;
			$ms = round(( $time - (($h*3600) + ($m * 60) + $s) ) * 10000);
		}elseif($time >= 60){
			$h = 0;
			$m = floor($time / 60);
			$s = floor(($time - $m*60) % 60) ;
			$ms = round(( $time - (($m * 60) + $s) ) * 10000);
		}else{
			$h = 0;
			$m = 0;
			$s = floor($time);
			$ms = round(( $time - $s ) * 10000);
		}


		if($miliseconds)
			return sprintf("%02d:%02d:%02d.%04d",$h,$m,$s,$ms);
		else
			return sprintf("%02d:%02d:%02d",$h,$m,$s);
	}

	/**
	 * Show a notice (error message) inside WP Admin.
	 *
	 * @static
	 * @param string $notice		Text of the warning message
	 * @param string $class			Notice class to use (defaults to "error")
	 * @param bool   $adminOnly		Show this notice only
	 */
	public static function showAdminNotice($notice, $class = 'error', $adminOnly = false){
		add_action( 'admin_notices', function() use ($notice, $class, $adminOnly){
			if(!$adminOnly || user_can( 'manage_options' )){
				echo
					'<div id="message" class="'.$class.'"><p><strong>' .
					$notice.
					'</strong></p></div>';
			}
		});
	}

	public static function sanitizeAffDomain($domain){
		$domain = trim($domain);
		$domain = trim($domain,',');
		if(!$domain || !stristr($domain,'.')){
			/**
			 * Implicitly disable aff support
			 */
			update_option('wp-ementor-affEnabled',false);

			return '';
		}

		return $domain;
	}

	public static function getDefaultAffDomain(){
		if(!$domain = parse_url(get_option('home'),PHP_URL_HOST))
			return;

		if(!stristr($domain,'.'))
			return;

		// return only last 2 fragments from the domain
		$frags = explode('.',$domain);
		return implode('.',array_splice($frags,-2));
	}
}
