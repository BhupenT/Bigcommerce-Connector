<?php

/**
 * Collections of functions used by the other functions
 *
 *
 * @link       https://www.matterdesgin.com.au
 * @since      1.0.0
 *
 * @package    Bigcommerce_Connector
 * @subpackage Bigcommerce_Connector/includes
 */


function get_corrected_permalink($permalink) {
	if(!$permalink || empty($permalink)) 
		return '';
	//add dash if there is space to make a valid url
	$permalink = preg_replace('/\s+/', '-', $permalink);
	// Check if there is slash infront if not add
	$slash = substr($permalink, 0, 1);
	$permalink = ('/' == $slash) ? $permalink : '/' . $permalink;
	return strtolower($permalink);
}