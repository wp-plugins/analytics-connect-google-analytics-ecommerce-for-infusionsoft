<?php

/*
Plugin Name: AnalyticsConnect.io - Google Analytics Ecommerce for Infusionsoft
Plugin URI: http://analyticsconnect.io/kb/wordpress.php
Description: The official AnalyticsConnect.io plugin for WordPress.
Version: 2.0.2
Requires at least: 3.5.1
Author: AnalyticsConnect.io
Author URI: http://analyticsconnect.io
License: GPL v3

Copyright (C) 2011-2014, AnalyticsConnect.io - admin@analyticsconnect.io

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/



define('ANALYTICS_CONNECT_IO_SOFTWARE_VERSION', '2.0.2');  //  Use same as listed above
define('ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME', 'AnalyticsConnect.io');  //  Used for display to users
define('ANALYTICS_CONNECT_IO_POST_URL', 'https://analyticsconnect.io/app/request/index.php');  //  Main Servers: Processing URL
define('ANALYTICS_CONNECT_IO_CALLBACK_URL', 'https://analyticsconnect.io/app/callback/wordpress.php');  //  Main Servers: Callback URL



include( plugin_dir_path( __FILE__ ) . 'analyticsconnect-io-admin.php');



//  If the shortcode is on the page we've got some work to do
add_shortcode('analyticsconnect-io', 'analyticsconnectio_shortcode');
function analyticsconnectio_shortcode() {
	
	$orderId = FALSE;
	
	//  Let's see if we can pull an OrderID
	
	if (isset($_POST)) {  //  Look for OrderID as a POST var (used by developers of other plugins)
		foreach ($_POST as $var => $value) {
			if (strtolower($var)=='orderid') {
				$orderId = $value;
			}
		}
	}
	if (isset($_GET)) {  //  Look for OrderID as a GET var
		foreach ($_GET as $var => $value) {
			if (strtolower($var)=='orderid') {
				$orderId = $value;
			}
		}
	}
	if ($orderId == FALSE) {  //  No OrderID found
		return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - ERROR (local): No OrderID available! -->';  //  Just give up
	} else {  //  We have an OrderID
		
		$options = get_option('analyticsconnectio_options');  //  Pull info from WP database
		
		if (preg_match('/^[a-z0-9]{24}$/i', $options['secret_key'])) {  //  Only run if Secret Key has a valid format
		
			//  Get the user's Google Cookie ID, if not avalible generate a UUID we can use
			$cid = analyticsconnectio_get_user_ga_cookie_id();
			if ($cid == FALSE) { $cid = analyticsconnectio_gen_uuid(); }
			
			$curlPostData = array(
				secretkey => $options['secret_key'],
				orderid => $orderId,
				cid => $cid
			);
			$curlPostBody = http_build_query($curlPostData);
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => ANALYTICS_CONNECT_IO_POST_URL,
				CURLOPT_USERAGENT => ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' WordPress Plugin v' . ANALYTICS_CONNECT_IO_SOFTWARE_VERSION,
				CURLOPT_POST => 1,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_POSTFIELDS => $curlPostBody
			));
			$result = curl_exec($curl);
			curl_close($curl);
			$data = json_decode($result, true);
			
			//  Process the result data
			
			if ($data[error] == '') {  //  No errors reported back from the servers
				
				$htmlCode = '
				
	<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' WordPress Plugin v' . ANALYTICS_CONNECT_IO_SOFTWARE_VERSION . ' -->
	' . $data[googleanalytics] . '
	' . $data[adwords] . '
	' . $data[facebook] . '
	';
				return $htmlCode;
				
			} else {  //  Something went wrong
			
				return $data[error];
				
			}
			
		} else {  //  Invalid Secret Key format
		
			return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - ERROR (local): Your Secret Key is invalid! -->';
			
		}

	}

}



//  Get the user's Google Cookie ID
function analyticsconnectio_get_user_ga_cookie_id() {
	if (isset($_COOKIE["_ga"])){  //  Get the GA cookie
		list($version, $domainDepth, $cid1, $cid2) = split('[\.]', $_COOKIE["_ga"],4);
		$cid = $cid1.'.'.$cid2;
		return $cid;
	} else {
		return FALSE;
	}
}



//  Generate UUID v4 (If the user doesn't have a Google Cookie we need to create something to send with the data to GA Measurement Protocol)
function analyticsconnectio_gen_uuid() {

	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
		// 16 bits for "time_mid"
		mt_rand( 0, 0xffff ),
	
		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand( 0, 0x0fff ) | 0x4000,
	
		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand( 0, 0x3fff ) | 0x8000,
	
		// 48 bits for "node"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
	
}



//  If the user asked this plugin for the Google Analytics code to be loaded onto their site
add_action('wp_head', 'analyticsconnectio_add_to_header');
function analyticsconnectio_add_to_header() {
	$options = get_option('analyticsconnectio_options');
	if ($options['gacode'] == 'true') { ?>
<!-- The following has been added in by the <?php echo ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME; ?> WordPress Plugin v<?php echo ANALYTICS_CONNECT_IO_SOFTWARE_VERSION; ?> -->
<!-- START Google Universal Analytics Code -->
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', '<?php echo $options['gaua']; ?>', 'auto', {'allowLinker': true});
	ga('require', 'linker');
	ga('linker:autoLink', ['<?php echo $options['infappname']; ?>.infusionsoft.com'], false, true);
	ga('require', 'displayfeatures');
	ga('send', 'pageview');
</script>
<!-- END Google Universal Analytics Code -->
	<?php }
}



?>