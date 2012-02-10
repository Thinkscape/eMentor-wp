<?php

namespace EMT\Wordpress;

interface Shortcode
{
	/**
	 * Render the shortcode
	 *
	 * @static
	 * @abstract
	 * @param array $attributes
	 * @return string
	 */
	public function render(array $attributes = array());
}