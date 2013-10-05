<?php
// if we're not uninstalling..
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

// clean up..
delete_option( 'jesin_mailgun_email_validator' );