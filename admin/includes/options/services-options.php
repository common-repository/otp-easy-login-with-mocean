<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$option_name = 'oelm-services-options';

$settings = array(

	array(
		'type' 			=> 'section',
		'callback' 		=> 'section',
		'id' 			=> 'twilio-section',
		'title' 		=> 'Mocean Settings',
	),

	array(
		'type' 			=> 'setting',
		'callback' 		=> 'text',
		'section' 		=> 'twilio-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'mocean-account-sid',
		'title' 		=> 'API key',
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'text',
		'section' 		=> 'twilio-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'mocean-auth-token',
		'title' 		=> 'API Secret',
	),

	array(
		'type' 			=> 'setting',
		'callback' 		=> 'text',
		'section' 		=> 'twilio-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'mocean-sender-number',
		'title' 		=> 'Sent from',
	),

);

return $settings;

?>
