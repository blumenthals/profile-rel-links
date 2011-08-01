<?php
/**
 * @package profile-rel-links
 * @version 1.0
 * @requires php >= 5.3
 */
/*
Plugin Name: Profile rel= links
Plugin URI: http://github.com/blumenthals/profile-rel-links
Description: Adds rel= links to profiles for matching up with Twitter and Google profiles
Author: Aria Stewart <aria@blumenthals.com>
Version: 1.0
Author URI: http://blumenthals.com/
*/


if(is_admin()) {
	add_filter( 'user_contactmethods', function($contactmethods) {
		$contactmethods['gprofile'] = 'Google Profile or Google+';
		$contactmethods['twitter'] = 'Twitter';
		return $contactmethods;
	});

	add_action('admin_init', function() {
		add_option('prl_intercept_links', true, null, true);
		add_settings_section('prl_main', 'Main Settings', function() { }, 'profile-rel-links');
		register_setting( 'profile-rel-links', 'prl_intercept_links' );
		add_settings_field('prl_intercept_links', 'Intercept Links and add "rel="?', function() {
			$intercept = get_option('prl_intercept_links');
			?> <input id='prl_intercept_links' name='prl_intercept_links' type='checkbox' <?php checked(1, $intercept); ?> value='1' /> <?php
		}, 'profile-rel-links', 'prl_main');
	});
	add_action('admin_menu', function() {
		add_options_page('rel= Links', 'Profile rel= Links', 'manage_options', 'profile-rel-links', function() {
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			?>
			<div class="wrap">
			<h2>Profile rel= links</h2>
			<form method="post" action="options.php"> 
			<?php 

				settings_fields('profile-rel-links');
				do_settings_sections('profile-rel-links');
			?>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			</form>
			</div>
			<?php
		});
	});
}

add_action('wp', function() {
	if(!get_option('prl_intercept_links')) return;

	global $wp_rewrite;
	$rules = array();
	foreach($wp_rewrite->rules as $rule => $val) {
		if(preg_match('/^author/', $rule)) $rules[] = str_replace(array('(', '$'), array('(?:', ''), $rule);
	}
	ob_start(function($content) use ($rules) {
		foreach($rules as $rule) {
			//$content = preg_replace('#(<a[^>]*)(href=[\'"]'.$rule.')([\'"][^>]*>)#', '\1 rel="author" \2\3', $content);
			$content = preg_replace('#(<a[^>]*)(href=[^>]*'.$rule.')#', '\1 rel="author" \2\3', $content);
$content .= $rule . "\n";
		}
		return $content;
	});
});

?>
