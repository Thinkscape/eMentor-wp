<?php

namespace EMT\Wordpress\Shortcode;

use EMT\Wordpress\Shortcode,
	EMT\Wordpress\Plugin
;

abstract class AbstractShortcode implements Shortcode
{
	/**
	 * @var \EMT\Wordpress\Plugin
	 */
	protected $plugin;

	public function __construct(Plugin $plugin){
		$this->plugin = $plugin;
	}
}