<?php
/*
Plugin Name: Analytics Connect - Google Analytics Ecommerce for Infusionsoft
Plugin URI: http://ecommerceanalyticsconnect.com/wordpress/
Description: The official Analytics Connect plugin for WordPress.
Version: 1.0.3
Author: Analytics Connect
Author URI: http://www.ecommerceanalyticsconnect.com/
*/

class WP_Analytics_Connect {                

        public function __construct() {       
            $this->plugin_basename = plugin_basename(__FILE__); 
            $this->dir = plugin_dir_path( __FILE__ );
            $this->admin_page_id = "Analytics-Connect"; 
            $this->keyoid = "analyticsconnect_key";          
            $this->key = get_option($this->keyoid);           
            $this->admin_init();
        }

        public function analytics_connect_snippet() {
        	$code = "|<!-- START Analytics Connect Integration Code -->|<script type='text/javascript' src='//www.ecommerceanalyticsconnect.com/request/request.js'></script>|<script type='text/javascript'>|function AnalyticsConnect(){new Connector('%KEY%');}|if (window.attachEvent) {window.attachEvent('onload', AnalyticsConnect());}|else if (window.addEventListener) {window.addEventListener('load', AnalyticsConnect(), false);}|else {document.addEventListener('load', AnalyticsConnect(), false);}|</script>|<!-- END Analytics Connect Integration Code -->|";
			$code = str_replace(array('%KEY%','|'), array($this->key, PHP_EOL), $code);
			echo $code;
        }

        /*******************
         * Admin Setup Functions *
         *******************/

        /**
         * Initialize the admin capabilities
         */
        private function admin_init() {            
            //set up the settings admin page            
            add_action('admin_menu', array(&$this, 'add_settings_page'));   
            //create the link to the settings page
            add_filter("plugin_action_links_".$this->plugin_basename, array(&$this, 'add_settings_action_link'));
        }

        public function add_settings_page() {
            $pageTitle = "Analytics Connect";
            $menuTitle = "Analytics Connect";
            $capability = 'manage_options'; 
            $callback =  array(&$this,"analytics_connect_plugin_settings");    
            add_options_page($pageTitle, $menuTitle, $capability, $this->admin_page_id, $callback);
        }

        /*
         * Modify the $links array to add the 'Settings' and 'Premium Support' links.
         */
        public function add_settings_action_link($links) {
            $links = isset($links) ? $links : array();
            $settings_link = '<a href="options-general.php?page='.$this->admin_page_id.'">Settings</a>'; 
            array_unshift($links, $settings_link);
            $premium_support_link = '<a href="http://www.ecommerceanalyticsconnect.com/premium-support.php?utm_source=ac-wordpress-plugin&utm_medium=text-link&utm_campaign=premium-support-link">Premium Support</a>'; 
            array_unshift($links, $premium_support_link);
            return $links;
        }

        /***************************
         * Settings Page Functions *
         ***************************/        
       
        /*
         * Render the settings page, where users can define their certify snippet
         * and claim ID
         */
        public function analytics_connect_plugin_settings() {
        	$current_key = $this->key;
            $errors = array();
            $updated = false;
            if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST["analyticsconnect_submit"])) {                
                //The user just sent us new data, fetch it, validate it and store it
                $form_key = isset($_POST["analyticsconnect_key"]) 
                    ? $this->_clean_input($_POST["analyticsconnect_key"]) : $current_key;

                if(strcmp($form_key, $current_key)) {
                    if($this->_validKey($form_key)) {
                        update_option($this->keyoid, $form_key);
                        $this->key = $form_key;
                        $updated["key"] = "Your key has been updated.";
                    } else {
                        $errors["key"] = "We could not validate your key. Please make sure you copy the key exactly as it appears on your dashboard.";    
                    }
                }
            
                if(empty($errors) && $updated===false) {
                    $updated["nochange"] = "No changes found.";                    
                }
            }

            //these values are needed in the admin script
            $this->updated = $updated;            
            $this->errors = $errors;
            $this->key = get_option($this->keyoid);   
            include('analyticsconnect_admin.php');
        }

        private function _validKey($key) {
            include_once('acapi.php');
			$acapi = new ACAPI($key, "");
			$response = $acapi->verifykey();
			if(isset($response->error)){
				return FALSE;
			}
			else{
				if(isset($response->success)){
					return TRUE;
				}
			}
			return FALSE;
        }

        private function _clean_input($value) {
            return trim(stripslashes($value));
        }
	}
$analytics_connect_activate = new WP_Analytics_Connect();

function analytics_connect_hook(){
	global $post;
    if(has_shortcode($post->post_content, 'analytics_connect')){
	   $analytics_connect_plugin = new WP_Analytics_Connect();
	   $analytics_connect_plugin->analytics_connect_snippet();
    }
}

function analytics_connect_short(){

}

add_shortcode('analytics_connect', 'analytics_connect_short');
add_action('wp_footer', 'analytics_connect_hook', 100);
?>