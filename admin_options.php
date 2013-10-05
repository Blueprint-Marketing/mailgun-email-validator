<?php
if(!class_exists('Email_Validation_Mailgun_Admin'))
{
	class Email_Validation_Mailgun_Admin
	{
		private $options = NULL;

		public function __construct()
		{
			$this->options = get_option('jesin_mailgun_email_validator');

			add_action( 'admin_menu' , array( &$this, 'plugin_menu' ) );
			add_action( 'admin_init' , array( &$this, 'plugin_settings' ) );
			add_action( 'admin_notices' , array( &$this, 'admin_messages' ) );
		}

		//Display admin notices
		public function admin_messages()
		{
			global $email_validation_mailgun;
			//Displayed if no API key is entered
			if( !isset($this->options['mailgun_pubkey_api']) || empty($this->options['mailgun_pubkey_api']) )
				echo '<div class="updated"><p>The <a href="'.admin_url( 'options-general.php?page=' . $email_validation_mailgun->slug ).'">Mailgun Email Validator plugin</a> will not work until a Mailgun Public API key is entered.</p></div>';
		}
		
		public function settings_link($links)
		{
			global $email_validation_mailgun;
			array_unshift($links, '<a href="'.admin_url( 'options-general.php?page=' . $email_validation_mailgun->slug ).'">Settings</a>');
			return $links;
		}

		//Hook in and create a menu
		public function plugin_menu()
		{
			global $email_validation_mailgun;
			add_filter( 'plugin_action_links_' . $email_validation_mailgun->basename, array( &$this, 'settings_link' ) );
			$plugin_page = add_options_page( 'Email Validation Settings', 'Email Validation', 'manage_options', $email_validation_mailgun->slug, array(&$this,'plugin_options') );
			add_action( 'admin_head-' . $plugin_page, array( &$this, 'plugin_panel_styles' ) );
			add_action( 'admin_footer-' . $plugin_page, array( &$this, 'plugin_panel_scripts' ) ); //Add AJAX to the footer of the options page
		}

		//Create the options page
		public function plugin_settings()
		{
			add_action( 'wp_ajax_mailgun_api', array( &$this, 'mailgun_api_ajax_callback') ); //AJAX to verify the API key
			add_action( 'wp_ajax_test_email', array( &$this, 'test_email_ajax_callback') ); //AJAX for demo email validation

			global $email_validation_mailgun;
			register_setting( $email_validation_mailgun->slug.'_options', 'jesin_mailgun_email_validator', array( &$this, 'sanitize_input' ) );
			add_settings_section($email_validation_mailgun->slug.'_settings', '', array( &$this, 'dummy_cb'), $email_validation_mailgun->slug);
			add_settings_field('mailgun_pubkey_api','<label for="mailgun_pubkey_api">Mailgun Public API</label>', array( &$this, 'api_field' ), $email_validation_mailgun->slug, $email_validation_mailgun->slug.'_settings'); //Public API key field
		}

		public function plugin_panel_styles()
		{
			global $email_validation_mailgun;
			echo '<style type="text/css">#icon-'.$email_validation_mailgun->slug.'{background:transparent url(\'' . plugin_dir_url( __FILE__ ) . 'screen-icon.png\') no-repeat;}</style>';
		}

		//Add AJAX to the footer
		public function plugin_panel_scripts()
		{
?>
<script type="text/javascript">
jQuery(document).ready(
	jQuery('#mailgun_api_verify').click (function($) 
	{
		if (jQuery.trim(jQuery('#mailgun_pubkey_api').val()).length == 0) {
			jQuery('#api_output').html('This field cannot be empty');
			return;
		}

		var data = {
			action: 'mailgun_api',
			api: jQuery('#mailgun_pubkey_api').val()
		};

		jQuery('#api_output').html('Checking...');
		jQuery('#api_output').css("cursor","wait");
		jQuery('#mailgun_api_verify').attr("disabled","disabled");
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#api_output').html(response);
			jQuery('#api_output').css("cursor","default");
			jQuery('#mailgun_api_verify').removeAttr("disabled");
		}
		);
	}
));

jQuery(document).ready(
	jQuery('#validate_email').click (function($)
	{
		if (jQuery.trim(jQuery('#sample_email').val()).length == 0) {
			jQuery('#email_output').html('Please enter an email address to validate');
			return;
		}

		var data = {
			action: 'test_email',
			email_id: jQuery('#sample_email').val()
		};
		jQuery('#email_output').html('Checking...');
		jQuery('#email_output').css("cursor","wait");
		jQuery('#validate_email').attr("disabled","disabled");
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#email_output').html(response);
			jQuery('#email_output').css("cursor","default");
			jQuery('#validate_email').removeAttr("disabled");
		}
		);
	}
));
</script>
<?php	}

		//AJAX Callback function for validating the Public API key
		public function mailgun_api_ajax_callback()
		{
			$args = array(
				'sslverify' => false,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( "api:".$_POST['api'] )
				)
			);

			//We are using a static email here as only the API is validated
			$response = wp_remote_head( "https://api.mailgun.net/v2/address/validate?address=foo%40mailgun.net", $args );

			//A Network error has occurred
			if( is_wp_error($response) )
				echo '<span style="color:red">' . $response->get_error_message() . '</span>';
			
			elseif( isset($response->errors['http_request_failed']) )
			{
				echo '<span style="color:red">The following error occured when validating the key<br />';
				foreach($response->errors['http_request_failed'] as $http_errors)
					echo $http_errors;
				echo '</span>';
			}

			elseif( '200' == $response['response']['code'] )
				echo '<span style="color:green">API Key is valid</span>';

			//Invalid API as Mailgun returned 401 Unauthorized
			elseif( '401' == $response['response']['code'] )
				echo '<span style="color:red">Invalid API Key. Error code: '.$response['response']['code'].' '.$response['response']['message'].'</span>';

			//A HTTP error other than 401 has occurred
			else
				echo '<span style="color:red">A HTTP error occured when validating the API.
					Error code: '.$response['response']['code'].' '.$response['response']['message'].'</span>';

			die();
		}

		//AJAX Callback function for demo email validation
		public function test_email_ajax_callback()
		{
			if(!filter_var($_POST['email_id'], FILTER_VALIDATE_EMAIL))
			{
				echo '<span style="color:red">The format of the email address is invalid.</span>';
				die();
			}

			//Someone tries validating without entering the Public API key
			if(!isset($this->options['mailgun_pubkey_api']) || empty($this->options['mailgun_pubkey_api']))
			{
				echo '<span style="color:red">Please enter a Mailgun Public API and click Save Settings.</span>';
				die();
			}

			$args = array(
				'sslverify' => false,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( "api:".$this->options['mailgun_pubkey_api'] )
				)
			);
			$response = wp_remote_request( "https://api.mailgun.net/v2/address/validate?address=".urlencode($_POST['email_id']), $args );

			if( is_wp_error($response) )
			{
				echo '<span style="color:red">' . $response->get_error_message() . '</span>';
				die();
			}
			$result = json_decode($response['body'],true);

			//A Network error has occurred
			if( isset($response->errors['http_request_failed']) )
			{
				echo '<span style="color:red">The following error occured<br />';
				foreach($response->errors['http_request_failed'] as $http_errors)
					echo $http_errors;
				echo '</span>';
			}

			elseif( '200' == $response['response']['code'] )
			{
				if($result['is_valid'])
					echo '<span style="color:green">Address is valid</span>';
				else
					echo '<span style="color:red">Address is invalid</span>';
			}

			//API key is invalid so email couldn't be verified
			elseif( '401' == $response['response']['code'] )
				echo '<span style="color:red">Invalid API Key.<br />Error code: '.$response['response']['code'].' '.$response['response']['message'].'</span>';

			die();
		}

		//Validate user input in the admin panel
		public function sanitize_input($input)
		{
			$input['mailgun_pubkey_api'] = trim($input['mailgun_pubkey_api']);
			if(!empty($input['mailgun_pubkey_api']))
			{
				preg_match_all( '/[0-9a-z-]/', $input['mailgun_pubkey_api'], $matches );
				$input['mailgun_pubkey_api'] = implode( $matches[0] );
			}

			return $input;
		}

		//Create the Public API field
		public function api_field()
		{
			$api_key = ( (isset($this->options['mailgun_pubkey_api']) && !empty($this->options['mailgun_pubkey_api'])) ? $this->options['mailgun_pubkey_api'] : '' );
			echo '<input class="regular_text code" id="mailgun_pubkey_api" name="jesin_mailgun_email_validator[mailgun_pubkey_api]" size="40" type="text" value="'.$api_key.'" required />
				<input id="mailgun_api_verify" type="button" value="Verify API Key" /><br />
				<div id="api_output"></div>
				<p class="description">Enter your Mailgun Public API key which is shown at the left under <strong>Account Information</strong> after you <a href="https://mailgun.com/sessions/new">login</a>.</p>';
		}

		//HTML of the plugin options page
		public function plugin_options()
		{
			global $email_validation_mailgun;
		?>
			<div class="wrap">
			<?php screen_icon( $email_validation_mailgun->slug ); ?>
			<h2>Email Validation Settings</h2>
			<p>This plugin requires a Mailgun account which is totally free. <a href="https://mailgun.com/signup" target="_blank">Signup for a free account</a></p>
			<form method="post" action="options.php">
			<?php settings_fields( $email_validation_mailgun->slug . '_options' );
			do_settings_sections( $email_validation_mailgun->slug );
			submit_button(); ?>
			</form>
			<?php if( isset( $this->options['mailgun_pubkey_api'] ) && !empty( $this->options['mailgun_pubkey_api'] ) ): ?>
			<h2 class="title">Email Validation Demo</h2>
			<p>You can use this form to see how mailgun validates email addresses.</p>
			<label for="sample_email">Email:</label><input style="margin-left: 20px" class="regular_text code" type="text" id="sample_email" size="40"/>
			<input type="button" id="validate_email" value="Validate Email" />
			<div id="email_output" style="font-size:20px;padding:10px 0 0 50px"></div>
			<?php endif; ?>
			</div>
		<?php
		}

		public function dummy_cb() {} //Empty callback for the add_settings_section() function
	}
	
	$email_validation_mailgun_admin = new Email_Validation_Mailgun_Admin();
}