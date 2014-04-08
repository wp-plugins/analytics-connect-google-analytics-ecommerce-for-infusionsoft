<?php
//must check that the user has the required capability 
if (!current_user_can('manage_options')) {
    wp_die( __('You do not have sufficient permissions to access this page.') );
}
$formAction = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
if(file_exists($this->dir.'/key.txt')){
    $this->new_key_from_file($this->dir.'/key.txt');
}
?>

<?php if (!empty($this->updated)): ?>
    <div class="updated"><?= implode("<br />\n", $this->updated) ?></div>
<?php endif;?>   

<?php if (!empty($this->errors)): ?>
    <div class="error"><?= implode("<br />\n", $this->errors) ?></div>
<?php endif; ?>

<div class="wrap">
	<br>
    <h2>Analytics Connect - Google Analytics Ecommerce for Infusionsoft</h2>

	<form name="analyticsconnect_form" method="post" action="<?php echo $formAction ?>">
        <input type="hidden" name="analyticsconnect_submit" value="Y"/>
        <p>
            To connect the plugin to Analytics Connect, enter your key below.
            <br/>
            If you need a key, please visit our <a href="http://www.ecommerceanalyticsconnect.com" target="_blank">website</a> to sign up.
        </p>
        <br>
	    <p>
	        <h3>Analytics Connect Key:</h3>
	        <input type="text" name="analyticsconnect_key" value="<?= htmlspecialchars($this->key) ?>" size="40">
            <br/>
        </p>
        <p class="submit">  
           <input type="submit" name="Submit" value="Check Key" />  
        </p>
    </form>
        <p>
        	<h3>Setup Instructions</h3>
        	<br>
        	Place the following shortcode on all thank-you pages.  These are the pages a customer arrives at after making a perchase.
        	<br>
        	<h4><strong>Shortcode:</strong> [analytics_connect]</h4>
        	Make sure that the OrderId variable is being passed to the thank-you page containing your shortcode.
        	<br>This may require some extra setup inside Infusionsoft.  See our <a href="http://www.ecommerceanalyticsconnect.com/installation-troubleshooting.php">Installation and Troubleshooting Guide</a> for more information.
        	<br>
            <br>This plugin requires Google Analytics to already be installed.
            <br>For Google Classic Analytics, we recommend the <a href="http://wordpress.org/plugins/google-analytics-for-wordpress/">Google Analytics for WordPress</a> plugin.
            <br>This plugin also works with Google Universal Analytics.
        </p>
    <hr>
</div>
