<?php
/*
Plugin Name: Email not required
Plugin URI: https://ircf.fr
Description: Disable email requirement for users
Version: 0.3
Author: IRCF
Author URI: https://ircf.fr/
License: GPL2
*/

// Disable email address requirement for wp_insert_user()
if ( !function_exists('get_user_by_email') ) :
	function get_user_by_email($email) {
		if(strlen($email) == 0 || empty($email) || $email == "" || strpos($email, "@") == false) {
			return false;
		}
		else {
			return get_user_by('email', $email);
		}
	}
endif;

// Disable email address requirement for wp_update_user()
function disable_email_address_requirement( $errors ) {  
	$ignores = array('empty_email','invalid_email','email_exists');
	foreach($ignores as $ignore){
		unset($errors->errors[$ignore]);
		unset($errors->error_data[$ignore]);
	}
  return $errors;
}  
add_action( 'user_profile_update_errors', 'disable_email_address_requirement', 0, 3 );

// Disable email address javascript requirement (since WP 3.2)
function disable_email_address_javascript_requirement_begin () {
		ob_start();
}
function disable_email_address_javascript_requirement_end () {
	$user_form = ob_get_contents();
	$user_form = preg_replace(
		"#<label for=\"email\">(.*)".preg_quote(translate('(required)'))."(.*)</label>#",
		"<label for=\"email\">$1$2</label>",
		$user_form
	);
	// FIXME and remove str_replace
	/*$user_form = preg_replace(
		$test = "#<tr class=\"form-field form-required\">(.*?)<label for=\"email\">#m",
		"<tr class=\"form-field\">$1<label for=\"email\">",
		$user_form
	);*/
	$user_form = str_replace(
		"<tr class=\"form-field form-required\">\n\t\t<th scope=\"row\"><label for=\"email\">",
		"<tr class=\"form-field\">\n\t\t<th scope=\"row\"><label for=\"email\">",
		$user_form
	);
	ob_end_clean();
	echo $user_form;
}
if (in_array(basename($_SERVER['SCRIPT_NAME']), array('user-edit.php','user-new.php','profile.php'))){
	add_action('admin_footer', 'disable_email_address_javascript_requirement_end');
	add_action('admin_init', 'disable_email_address_javascript_requirement_begin');
}

// Disable email address requirement for wpmu_validate_user_signup (WordPress MU)
function disable_email_address_requirement_wpmu ($result) {
	if (!isset($result['user_email']) || empty($result['user_email'])){
		// Removes email error
		if (isset($result['errors']) && is_wp_error($result['errors']) && property_exists($result['errors'],'errors')) {
			unset($result['errors']->errors['user_email']);
		}
		// Skip notification
		$_POST['noconfirmation'] = 1;
	}
	return $result;
}
add_filter('wpmu_validate_user_signup', 'disable_email_address_requirement_wpmu');
