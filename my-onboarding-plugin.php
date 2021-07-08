<?php
/**
 * Plugin Name: My Onboarding Plugin
 * Description: A very nice onboarding plugin to filter content
 * Author: Kristian Vassilev
 * Version: 1.0.0
 */


// First line of defense
if(!defined('ABSPATH')) {
    exit;
}




// Adds a menu element to the admin_menu for the plugin

add_action('admin_menu', 'add_plugin_menu');

function add_plugin_menu(){

    //title, name in menu, capability, title of page inside, name of function, dash-icon
    add_menu_page('My Onboarding', 'My Onboarding', 'administrator', 'ob-filter', 'ob_add_filter', 'dashicons-palmtree');
};


// Adds checkbox and checks whether the plugin is enabled

$is_plugin_enabled = get_option('onboarding_enabled');

function ob_add_filter(){

    //get the current option value from db

    $is_plugin_enabled = get_option('onboarding_enabled');
    $form_check = "";

    if($is_plugin_enabled == 'true'){
    $form_check = "checked";
    }   else{
    $form_check = "";
    };

    $ob_check = '
        <h2>Enable/Disable filter</h2>
        <div>
            <input type="checkbox" id="ob-filter" name="ob-filter" '.$form_check;'>
            <label for="ob-filter">Filters Enabled</label>
        </div>';
    echo $ob_check;
};


//Makes sure the plugin runs only if enabled

if($is_plugin_enabled == 'true'){
    

    // Adds a string to the wp_head element

    add_action("wp_head", "onboarding_plugin");

    function onboarding_plugin() {
        if(get_post_type() == 'student'){
            echo 'Onboarding Filter by: kvasilev';
        };
    };
    
    
    // Adds a hidden div after the first </p>

    add_filter('the_content', 'add_hidden_div');
    
    function add_hidden_div($content) { 
        if(get_post_type() == 'student'){
            $content = str_replace('</p>', '</p><div style="display:none;">hidden div</div>', $content);
            echo $content;
        }else {
            echo $content;
        };
    };


    // Adds a paragrah and a hidden div

    add_filter('the_content', 'add_new_paragraph');

    function add_new_paragraph($content) {
        if(get_post_type() == 'student'){
            $content= '<p>Generic paragraph</p>';
            $content = str_replace('<p>Generic paragraph</p>', '<p>Generic paragraph <div style="display:none;">hidden div</div></p>', $content);
            echo $content;
        }else{
            echo $content;
        }
    };


    // Emailing functionality upon profile update

    add_action('profile_update', 'update_profile_email');

    function update_profile_email() {
        $myUser = get_currentuserinfo();
        //wp_mail(email-to, email-subject, email-message)
        wp_mail('kvasilev@devrix.com', 'New profile update', 'Profile with user name '.$myUser->user_login.' has been updated.');
    };


    // Adds a nav element (profile page) to the current main menu if user is logged

    add_filter('wp_nav_menu_items','add_profile_page');

    function add_profile_page($items){
        if(is_user_logged_in() && get_post_type() == 'student'){
            $profilePageLink = admin_url('profile.php');
            return $items .= '<li><a href="'.$profilePageLink.'" class = "menu-item" >Profile Settings</a></li>';
        }else{
            return $items;
        }
    };

};

// Imports and starts the addOption.js
function add_onboarding_script() {
    wp_enqueue_script('addOptions', plugins_url('addOptions.js', __FILE__), array('jquery'),false, true);
    wp_localize_script( 'addOptions', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
};
    
add_action( 'admin_enqueue_scripts', 'add_onboarding_script' );  
add_action( 'wp_ajax_add_options', 'add_options' );


//Adds options to save the plugin enable/disable state into the db

function add_options() {
    $currentValue =  $_POST['ob_filter'];
    $savedOption = '';
    
   if ($currentValue == 'true') {
       $savedOption = 'true';
   } else {
    $savedOption = 'false';
   };

    update_option('onboarding_enabled',$savedOption);
    wp_send_json_success();
    wp_die(); 
};
?>