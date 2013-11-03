<?php
/*
Plugin Name: Mailgun Email Validator
Plugin URI: http://jesin.tk/wordpress-plugins/mailgun-email-validator/
Description: Kick spam with an highly advanced email validation in comment forms, user registration forms and contact forms using <a href="http://blog.mailgun.com/post/free-email-validation-api-for-web-forms/" target="_blank">Mailgun's Email validation</a> service.
Author: Jesin
Version: 1.0
Author URI: http://jesin.tk/
*/

if( !function_exists( 'json_decode' ) )
{
	function json_decode( $string, $assoc = FALSE )
	{
		require_once 'JSON.php';
		$json = new Services_JSON();

		if($assoc)
			return (array) $json->decode($string);
		else
			return $json->decode($string);
	}
}

if( !class_exists( 'Email_Validation_Mailgun' ) )
{
	class Email_Validation_Mailgun
	{
		private $options = NULL;
		var $slug;
		var $basename;

		public function __construct()
		{
			$this->options = get_option( 'jesin_mailgun_email_validator' );
			$this->basename = plugin_basename( __FILE__ );
			$this->slug = str_replace( array( basename( __FILE__ ), '/' ), '', $this->basename );

			add_action( 'init', array( &$this, 'plugin_init' ) );
		}

		public function plugin_init()
		{
			load_plugin_textdomain( $this->slug, FALSE, $this->slug . '/languages' );
			add_filter( 'is_email', array( &$this, 'validate_email' ) );
		}

		//Function which sends the email to Mailgun to check it
		public function validate_email( $emailID )
		{
			//If the format of the email itself is wrong return false without further checking
			if( !filter_var( $emailID, FILTER_VALIDATE_EMAIL ) )
				return FALSE;

			//If no API was entered don't do anything
			if( !isset( $this->options['mailgun_pubkey_api'] ) || empty( $this->options['mailgun_pubkey_api'] ) )
				return TRUE;

			$args = array(
				'sslverify' => FALSE,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( "api:".$this->options['mailgun_pubkey_api'] )
				)
			);
			//Send the email to Mailgun's email validation service
			$response = wp_remote_request( "https://api.mailgun.net/v2/address/validate?address=".urlencode($emailID), $args );

			//If there was a HTTP or connection error pass the validation so that the website visitor doesn't know anything
			if( is_wp_error( $response ) || isset( $response['error'] ) || '200' != $response['response']['code'] )
				return TRUE;

			//Extract the JSON response and return the result
			$result = json_decode( $response['body'], true );
			return $result['is_valid'];
		}
	}

	$email_validation_mailgun = new Email_Validation_Mailgun();
}

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin_options.php';