<?php
/**
 * Plugin Name: My Onboarding Plugin
 * Description: A very nice onboarding plugin to filter content
 * Author: Kristian Vassilev
 * Version: 1.0.0
 *
 * @package   onboarding-plugin
 */

/**  First line of defense */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**  Adds an action to initiate adding menu element to the admin_menu for the plugin */
add_action( 'admin_menu', 'add_plugin_menu' );

/**
 * Adds the plugin to the menu
 *
 * @return void
 */
function add_plugin_menu() {
	add_menu_page( 'My Onboarding', 'My Onboarding', 'administrator', 'ob-filter', 'ob_enable_filter', 'dashicons-palmtree' );
};



/**  Adds checkbox and checks whether the plugin is enabled */
$ob_is_plugin_enabled = get_option( 'onboarding_enabled' );

/**
 * Adds an enable/disable filter to the plugin and checks its value
 *
 * @return void
 */
function ob_enable_filter() {
	// get the current option value from db.
	$ob_is_plugin_enabled = get_option( 'onboarding_enabled' );
	$ob_form_check        = '';

	if ( 'true' === $ob_is_plugin_enabled ) {
		$ob_form_check = 'checked';
	} else {
		$ob_form_check = '';
	};

	$ob_check = '
        <h2>Enable/Disable filter</h2>
        <div>
            <input type="checkbox" id="ob-filter" name="ob-filter" ' . $ob_form_check;'>
            <label for="ob-filter">Filters Enabled</label>
        </div>';
	echo ( $ob_check );
};


// Makes sure the plugin runs only if enabled.
if ( 'true' === $ob_is_plugin_enabled ) {


	// Adds a string to the wp_head element.
	add_action( 'wp_head', 'onboarding_plugin' );

	/**
	 * Adds plugin creator`s credentials on student post type
	 *
	 * @return void
	 */
	function onboarding_plugin() {
		if ( get_post_type() === 'student' ) {
			echo 'Onboarding Filter by: kvasilev';
		};
	};


	// Adds a hidden div after the first </p>.
	add_filter( 'the_content', 'ob_add_hidden_div', 50 );

	/**
	 * Adds a hidden div after the closing tag </p>
	 *
	 * @param string $content of the wp post.
	 * @return void
	 */
	function ob_add_hidden_div( $content ){
		if ( get_post_type() === 'student' ) {
			$content = str_replace( '</p>', '</p><div style="display:none;">hidden div</div>', $content );
			echo ( $content );
		} else {
			echo ( $content );
		};
	};


	// Adds a paragrah and a hidden div.
	add_filter( 'the_content', 'ob_add_new_paragraph', 60 );

	/**
	 * Adds new paragraph with hidden div after the content
	 *
	 * @param string $content of the wp post.
	 * @return void
	 */
	function ob_add_new_paragraph( $content ) {
		if ( get_post_type() === 'student' ) {
			$content = '<p>Generic paragraph</p>';
			$content = str_replace( '<p>Generic paragraph</p>', '<p>Generic paragraph <div style="display:none;">hidden div</div></p>', $content );
			echo ( $content );
		} else {
			echo ( $content );
		}
	};

	// Emailing functionality upon profile update.
	add_action( 'profile_update', 'ob_update_profile_email' );

	/**
	 * Sends and email upon profile update
	 *
	 * @return void
	 */
	function ob_update_profile_email() {
		$my_user = wp_get_current_user();

		// wp_mail(email-to, email-subject, email-message).
		wp_mail( 'kvasilev@devrix.com', 'New profile update', 'Profile with user name ' . $my_user->user_login . ' has been updated.' );
	};

	// Adds a new filter to the menu for showing (profile page).
	add_filter( 'wp_nav_menu_items', 'ob_add_profile_settings_page' );

	/**
	 * Adds profile page if user is logged in.
	 *
	 * @param string $items are the menu.
	 * @return void
	 */
	function ob_add_profile_settings_page( $items ) {
		if ( is_user_logged_in() && get_post_type() === 'student' ) {
			$profile_page_link = admin_url( 'profile.php' );
			return $items     .= '<li><a href="' . $profile_page_link . '" class = "menu-item" >Profile Settings</a></li>';
		} else {
			return $items;
		}
	};
};

/**  Imports and starts the addOption.js */
function ob_add_onboarding_script() {
	wp_enqueue_script( 'addOptions', plugins_url( 'addOptions.js', __FILE__ ), array( 'jquery' ), false, true );
	wp_localize_script( 'addOptions', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
};

add_action( 'admin_enqueue_scripts', 'ob_add_onboarding_script' );
add_action( 'wp_ajax_add_options', 'ob_add_options' );


/** Adds options to save the plugin enable/disable state into the db */
function ob_add_options() {
	$current_value = wp_unslash( sanitize_text_field( $_POST['ob_filter'] ) );
	$saved_option  = '';

	if ( 'true' === $current_value ) {
		$saved_option = 'true';
	};

	update_option( 'onboarding_enabled', $saved_option );
	wp_send_json_success();
	wp_die();
};
