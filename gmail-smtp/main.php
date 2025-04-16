<?php
/*
Plugin Name: Gmail SMTP
Version: 1.2.3.14
Plugin URI: https://wphowto.net/gmail-smtp-plugin-for-wordpress-1341
Author: naa986
Author URI: https://wphowto.net/
Description: Easily send email from your WordPress site via Gmail SMTP server
Text Domain: gmail-smtp
Domain Path: /languages 
 */

if (!defined('ABSPATH')){
    exit;
}

class GMAIL_SMTP {
    
    var $plugin_version = '1.2.3.14';
    var $phpmailer_version = '6.9.3';
    var $google_api_client_version = '2.2.0';
    var $plugin_url;
    var $plugin_path;
    
    function __construct() {
        define('GMAIL_SMTP_VERSION', $this->plugin_version);
        define('GMAIL_SMTP_SITE_URL', site_url());
        define('GMAIL_SMTP_HOME_URL', home_url());
        define('GMAIL_SMTP_URL', $this->plugin_url());
        define('GMAIL_SMTP_PATH', $this->plugin_path());
        $this->plugin_includes();
        $this->loader_operations();
    }

    function plugin_includes() {
        //Google API client library was too big to push to wordpress.org. I had to make it lightweight by keeping only the essential files
        //Open src/Google/Client.php and check which classes are in use from the /vendor directory.
        //In addition to the ones in use I had to keep composer and phpseclib as well to avoid a fatal error (and remove everything else).
        //vendor/google/apiclient-services/src/Google/Service was taking too much space. The plugin only needed to keep the "Gmail.php" file in it.
        //do a cleanup in both the PHPMailer & Google API Client folders to remove all the git related unnecessary files.
        /* Only include these scripts when needed to avoid conflicts with other plugins that are using Google API Client
        include_once('google-api-php-client/vendor/autoload.php');
        include_once('PHPMailer/PHPMailerAutoload.php');
        include_once('class.phpmaileroauthgoogle.php');
        include_once('class.phpmaileroauth.php');
         */
    }

    function loader_operations() {
        if (is_admin()) {
            include_once('addons/gmail-smtp-addons.php');
        }
        add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
        add_action('admin_menu', array($this, 'options_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('init', array($this, 'plugin_init'));
        add_action('admin_notices', 'gmail_smtp_admin_notice');
        if(is_gmail_smtp_configured()){
            add_filter('pre_wp_mail', 'gmail_smtp_pre_wp_mail', 10, 2);
        }
    }
    
    function enqueue_admin_scripts($hook) {
        if('settings_page_gmail-smtp-settings' != $hook) {
            return;
        }
        wp_register_style('gmail-smtp-addons-menu', GMAIL_SMTP_URL.'/addons/gmail-smtp-addons.css');
        wp_enqueue_style('gmail-smtp-addons-menu');
    }
    
    function plugins_loaded_handler()
    {
        if(is_admin() && current_user_can('manage_options')){
            add_filter('plugin_action_links', array($this, 'add_plugin_action_links'), 10, 2);
        }
        load_plugin_textdomain('gmail-smtp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
    }

    function plugin_url() {
        if ($this->plugin_url)
            return $this->plugin_url;
        return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
    }

    function plugin_path() {
        if ($this->plugin_path)
            return $this->plugin_path;
        return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
    }

    function add_plugin_action_links($links, $file) {
        if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
            $links[] = '<a href="options-general.php?page=gmail-smtp-settings">'.__('Settings', 'gmail-smtp').'</a>';
        }
        return $links;
    }
    
    function options_menu() {
        add_options_page(__('Gmail SMTP', 'gmail-smtp'), __('Gmail SMTP', 'gmail-smtp'), 'manage_options', 'gmail-smtp-settings', array($this, 'options_page'));
    }

    function options_page() {
        $plugin_tabs = array(
            'gmail-smtp-settings' => __('General', 'gmail-smtp'),
            'gmail-smtp-settings&action=test-email' => __('Test Email', 'gmail-smtp'),
            'gmail-smtp-settings&action=revoke-access' => __('Revoke Access', 'gmail-smtp'),
            'gmail-smtp-settings&action=server-info' => __('Server Info', 'gmail-smtp'),
            'gmail-smtp-settings&action=addons' => __('Add-ons', 'gmail-smtp'),
            'gmail-smtp-settings&action=advanced' => __('Advanced', 'gmail-smtp'),
        );
        $url = "https://wphowto.net/gmail-smtp-plugin-for-wordpress-1341";
        $link_text = sprintf(__('Please visit the <a target="_blank" href="%s">Gmail SMTP</a> documentation page for usage instructions.', 'gmail-smtp'), esc_url($url));
        $allowed_html_tags = array(
            'a' => array(
                'href' => array(),
                'target' => array()
            )
        );
        echo '<div class="wrap"><h2>Gmail SMTP v' . GMAIL_SMTP_VERSION . '</h2>';
        echo '<div class="update-nag">'.wp_kses($link_text, $allowed_html_tags).'</div>';
        $current = '';
        $action = '';
        if (isset($_GET['page'])) {
            $current = sanitize_text_field($_GET['page']);
            if (isset($_GET['action'])) {
                $action = sanitize_text_field($_GET['action']);
                $current .= "&action=" . $action;
            }
        }
        $content = '';
        $content .= '<h2 class="nav-tab-wrapper">';
        foreach ($plugin_tabs as $location => $tabname) {
            if ($current == $location) {
                $class = ' nav-tab-active';
            } else {
                $class = '';
            }
            $content .= '<a class="nav-tab' . $class . '" href="?page=' . $location . '">' . $tabname . '</a>';
        }
        $content .= '</h2>';
        $allowed_html_tags = array(
            'a' => array(
                'href' => array(),
                'class' => array()
            ),
            'h2' => array(
                'href' => array(),
                'class' => array()
            )
        );
        echo wp_kses($content, $allowed_html_tags);
        if(!empty($action))
        { 
            switch($action)
            {
               case 'test-email':
                   $this->test_email_settings();
                   break;
               case 'revoke-access':
                   $this->revoke_access_settings();
                   break;
               case 'server-info':
                   $this->server_info_settings();
                   break;
               case 'addons':
                   gmail_smtp_display_addons();
                   break;
               case 'advanced':
                   $this->advanced_settings();
                   break;
            }
        }
        else
        {
            $this->general_settings();
        }        
        echo '</div>';
    }
    
    function plugin_init(){
        if(is_admin()){
            if(isset($_GET['action']) && $_GET['action'] == "oauth_grant"){
                
                include_once('google-api-php-client/vendor/autoload.php');
                include_once('class.phpmaileroauthgoogle.php');
                
                if (isset($_GET['code'])) {
                    $authCode = sanitize_text_field($_GET['code']);
                    $accessToken = GmailXOAuth2::resetCredentials($authCode);
                    if(isset($accessToken) && !empty($accessToken)){                       
                        //echo __('Access Granted Successfully!', 'gmail-smtp');
                        $_GET['gmail_smtp_access_granted'] = "yes";
                    }
                    else{
                        $_GET['gmail_smtp_access_granted'] = "no";
                    }
                }
                else {
                    // If we don't have an authorization code then get one
                    $authUrl_array = GmailXOAuth2::authenticate();
                    if(isset($authUrl_array['authorization_uri'])){
                        $authUrl= $authUrl_array['authorization_uri'];
                        wp_redirect(esc_url_raw($authUrl));
                        exit;
                    }
                }
                // Unix timestamp of when the token will expire, and need refreshing
                //    echo $token->expires;
            }
        }
    }
    
    function test_email_settings(){
        if(isset($_POST['gmail_smtp_send_test_email'])){
            $to = '';
            if(isset($_POST['gmail_smtp_to_email']) && !empty($_POST['gmail_smtp_to_email'])){
                $to = sanitize_email($_POST['gmail_smtp_to_email']);
            }
            $subject = '';
            if(isset($_POST['gmail_smtp_email_subject']) && !empty($_POST['gmail_smtp_email_subject'])){
                $subject = sanitize_text_field(stripslashes($_POST['gmail_smtp_email_subject']));
            }
            $message = '';
            if(isset($_POST['gmail_smtp_email_body']) && !empty($_POST['gmail_smtp_email_body'])){
                $message = sanitize_textarea_field(stripslashes($_POST['gmail_smtp_email_body']));
            }
            
            wp_mail($to, $subject, $message);
        }
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('gmail_smtp_test_email'); ?>

            <table class="form-table">

                <tbody>

                    <tr valign="top">
                        <th scope="row"><label for="gmail_smtp_to_email"><?php _e('To', 'gmail-smtp');?></label></th>
                        <td><input name="gmail_smtp_to_email" type="text" id="gmail_smtp_to_email" value="" class="regular-text">
                            <p class="description"><?php _e('Email address of the recipient', 'gmail-smtp');?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="gmail_smtp_email_subject"><?php _e('Subject', 'gmail-smtp');?></label></th>
                        <td><input name="gmail_smtp_email_subject" type="text" id="gmail_smtp_email_subject" value="" class="regular-text">
                            <p class="description"><?php _e('Subject of the email', 'gmail-smtp');?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="gmail_smtp_email_body"><?php _e('Message', 'gmail-smtp');?></label></th>
                        <td><textarea name="gmail_smtp_email_body" id="gmail_smtp_email_body" rows="6"></textarea>
                            <p class="description"><?php _e('Email body', 'gmail-smtp');?></p></td>
                    </tr>

                </tbody>

            </table>

            <p class="submit"><input type="submit" name="gmail_smtp_send_test_email" id="gmail_smtp_send_test_email" class="button button-primary" value="<?php _e('Send Email', 'gmail-smtp');?>"></p>
        </form>
        
        <?php
    }
    
    function revoke_access_settings()
    {
        if (isset($_POST['gmail_smtp_delete_access_key'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'gmail_smtp_delete_accesskey')) {
                wp_die('Error! Nonce Security Check Failed! please click on the button again.');
            }
            $options = array();
            $options['oauth_access_token'] = '';
            gmail_smtp_update_option($options);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Access Key Successfully Deleted!', 'gmail-smtp');
            echo '</strong></p></div>';
        }
        $url = "https://security.google.com/settings/security/permissions";
        $link_text = sprintf(wp_kses(__('Revoke access by visiting <a target="_blank" href="%s">account settings</a>.', 'gmail-smtp'), array('a' => array('href' => array(), 'target' => array()))), esc_url($url));
        ?>

        <div class="update-nag">
            <?php _e('Generally you do not need to do anything on this page. However, for some reason if you wish to revoke access from your application please follow these steps:', 'gmail-smtp');?>
            <ol>
                <li><?php echo $link_text;?></li>
                <li><?php _e('Delete your existing access key by clicking on the "Delete Access Key" button.', 'gmail-smtp');?></li>
            </ol>    
        </div>

        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('gmail_smtp_delete_accesskey'); ?>           

            <p class="submit"><input type="submit" name="gmail_smtp_delete_access_key" id="gmail_smtp_delete_access_key" class="button button-primary" value="Delete Access Key"></p>
        </form>            

        <?php
    }
    
    function server_info_settings()
    {
        $server_info = '';
        $server_info .= sprintf('OS: %s%s', php_uname(), PHP_EOL);
        $version = '';
        if(version_compare(PHP_VERSION, '5.4', '<')) {
            $version = ' (PHPMailer requires PHP 5.4 or later in order to send email)';
        }
        $server_info .= sprintf('PHP version: %s%s%s', PHP_VERSION, $version, PHP_EOL);
        $server_info .= sprintf('WordPress version: %s%s', get_bloginfo('version'), PHP_EOL);
        $server_info .= sprintf('WordPress multisite: %s%s', (is_multisite() ? 'Yes' : 'No'), PHP_EOL);
        $openssl_status = 'Available';
        $openssl_text = '';
        if(!extension_loaded('openssl') && !defined('OPENSSL_ALGO_SHA1')){
            $openssl_status = 'Not available';
            $openssl_text = ' (openssl extension is required in order to use any kind of encryption like TLS or SSL. Gmail SMTP server does not allow you to send email without an encrypted connection)';
        }      
        $server_info .= sprintf('openssl: %s%s%s', $openssl_status, $openssl_text, PHP_EOL);
        $server_info .= sprintf('allow_url_fopen: %s%s', (ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled'), PHP_EOL);
        $stream_socket_client_status = 'Not Available';
        $fsockopen_status = 'Not Available';
        $socket_enabled = false;
        if(function_exists('stream_socket_client')){
            $stream_socket_client_status = 'Available';
            $socket_enabled = true;
        }
        if(function_exists('fsockopen')){
            $fsockopen_status = 'Available';
            $socket_enabled = true;
        }
        $socket_text = '';
        if(!$socket_enabled){
            $socket_text = ' (In order to make a SMTP connection your server needs to have either stream_socket_client or fsockopen)';
        }
        $server_info .= sprintf('stream_socket_client: %s%s', $stream_socket_client_status, PHP_EOL);
        $server_info .= sprintf('fsockopen: %s%s%s', $fsockopen_status, $socket_text, PHP_EOL);
        $cURL_status = 'Not Available. In order to make a SMTP connection your server needs to have cURL enabled';
        if(function_exists('curl_init')){
            $cURL_status = 'Available';           
        }
        $server_info .= sprintf('cURL: %s%s', $cURL_status, PHP_EOL);
        if(function_exists('curl_version')){
            $curl_version = curl_version();
            $server_info .= sprintf('cURL Version: %s, %s%s', $curl_version['version'], $curl_version['ssl_version'], PHP_EOL);
        }
        ?>
        <textarea rows="10" cols="50" class="large-text code"><?php echo esc_textarea($server_info);?></textarea>
        <?php
    }

    function general_settings() {
        
        if (isset($_POST['gmail_smtp_update_settings'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'gmail_smtp_general_settings')) {
                wp_die('Error! Nonce Security Check Failed! please save the settings again.');
            }
            $client_id = '';
            if(isset($_POST['oauth_client_id']) && !empty($_POST['oauth_client_id'])){
                $client_id = sanitize_text_field($_POST['oauth_client_id']);
            }
            $client_secret = '';
            if(isset($_POST['oauth_client_secret']) && !empty($_POST['oauth_client_secret'])){
                $client_secret = sanitize_text_field($_POST['oauth_client_secret']);
            }
            $oauth_user_email = '';
            if(isset($_POST['oauth_user_email']) && !empty($_POST['oauth_user_email'])){
                $oauth_user_email = sanitize_email($_POST['oauth_user_email']);
            }
            $from_email = '';
            if(isset($_POST['from_email']) && !empty($_POST['from_email'])){
                $from_email = sanitize_email($_POST['from_email']);
            }
            $from_name = '';
            if(isset($_POST['from_name']) && !empty($_POST['from_name'])){
                $from_name = sanitize_text_field(stripslashes($_POST['from_name']));
            }
            $type_of_encryption = '';
            if(isset($_POST['type_of_encryption']) && !empty($_POST['type_of_encryption'])){
                $type_of_encryption = sanitize_text_field($_POST['type_of_encryption']);
            }
            $smtp_port = '';
            if(isset($_POST['smtp_port']) && !empty($_POST['smtp_port'])){
                $smtp_port = sanitize_text_field($_POST['smtp_port']);
            }
            $disable_ssl_verification = '';
            if(isset($_POST['disable_ssl_verification']) && !empty($_POST['disable_ssl_verification'])){
                $disable_ssl_verification = sanitize_text_field($_POST['disable_ssl_verification']);
            }
            $options = array();
            $options['oauth_client_id'] = $client_id;
            $options['oauth_client_secret'] = $client_secret;
            $options['oauth_user_email'] = $oauth_user_email;
            $options['from_email'] = $from_email;
            $options['from_name'] = $from_name;
            $options['type_of_encryption'] = $type_of_encryption;
            $options['smtp_port'] = $smtp_port;
            $options['disable_ssl_verification'] = $disable_ssl_verification;
            gmail_smtp_update_option($options);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Settings Saved!', 'gmail-smtp');
            echo '</strong></p></div>';
        }
        
        if (isset($_GET['gmail_smtp_access_granted'])) {
            if($_GET['gmail_smtp_access_granted']=="yes"){
                echo '<div id="message" class="updated fade"><p><strong>';
                echo __('Access Granted Successfully!', 'gmail-smtp');
                echo '</strong></p></div>';
            }
            else{
                echo '<div id="message" class="error"><p><strong>';
                echo __('Access could not be granted', 'gmail-smtp');
                echo '</strong></p></div>';
            }
        }
        
        $options = gmail_smtp_get_option();
        if(!is_array($options)){
            $options = array();
            $options['oauth_client_id'] = '';
            $options['oauth_client_secret'] = '';
            $options['oauth_user_email'] = '';
            $options['from_email'] = '';
            $options['from_name'] = '';
            $options['type_of_encryption'] = '';
            $options['smtp_port'] = '';
            $options['disable_ssl_verification'] = '';
        }
        // Avoid warning notice since this option was added later
        if(!isset($options['disable_ssl_verification'])){
            $options['disable_ssl_verification'] = '';
        }
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('gmail_smtp_general_settings'); ?>

            <table class="form-table">

                <tbody>
                    
                    <tr valign="top">
                        <th scope="row"><label><?php _e('SMTP Status', 'gmail-smtp');?></label></th>
                        <?php if(isset($options['oauth_access_token']) && !empty($options['oauth_access_token'])){ ?>
                        <td><img src="<?php echo esc_attr(GMAIL_SMTP_URL.'/images/connected.png');?>" style="height: 30px;">
                            <p class="description"><?php _e('Connected', 'gmail-smtp');?></p></td>
                        <?php }
                        else{ ?>
                        <td><img src="<?php echo esc_attr(GMAIL_SMTP_URL.'/images/not-connected.png');?>" style="height: 30px;">
                            <p class="description"><?php _e('Not Connected', 'gmail-smtp');?></p></td>    
                        <?php } ?>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="oauth_redirect_uri"><?php _e('Authorized Redirect URI', 'gmail-smtp');?></label></th>
                        <td><input name="oauth_redirect_uri" type="text" id="oauth_redirect_uri" value="<?php echo esc_url(admin_url("options-general.php?page=gmail-smtp-settings&action=oauth_grant")); ?>" readonly class="regular-text code">
                            <p class="description"><?php _e('Copy this URL into your web application', 'gmail-smtp');?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="oauth_client_id"><?php _e('Client ID', 'gmail-smtp');?></label></th>
                        <td><input name="oauth_client_id" type="text" id="oauth_client_id" value="<?php echo esc_attr($options['oauth_client_id']); ?>" class="regular-text code">
                            <p class="description"><?php _e('The client ID of your web application', 'gmail-smtp');?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="oauth_client_secret"><?php _e('Client Secret', 'gmail-smtp');?></label></th>
                        <td><input name="oauth_client_secret" type="text" id="oauth_client_secret" value="<?php echo esc_attr($options['oauth_client_secret']); ?>" class="regular-text code">
                            <p class="description"><?php _e('The client secret of your web application', 'gmail-smtp');?></p></td>
                    </tr>                   
                    
                    <tr valign="top">
                        <th scope="row"><label for="oauth_user_email"><?php _e('OAuth Email Address', 'gmail-smtp');?></label></th>
                        <td><input name="oauth_user_email" type="text" id="oauth_user_email" value="<?php echo esc_attr($options['oauth_user_email']); ?>" class="regular-text code">
                            <p class="description"><?php _e('The email address that you will use for SMTP authentication. This should be the same email used in the Google Developers Console.', 'gmail-smtp');?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="from_email"><?php _e('From Email Address', 'gmail-smtp');?></label></th>
                        <td><input name="from_email" type="text" id="from_email" value="<?php echo esc_attr($options['from_email']); ?>" class="regular-text code">
                            <p class="description"><?php _e('The email address which will be used as the From Address when sending an email. Gmail currently ignores this value if it is different from the OAuth Email Address.', 'gmail-smtp');?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="from_name"><?php _e('From Name', 'gmail-smtp');?></label></th>
                        <td><input name="from_name" type="text" id="from_name" value="<?php echo esc_attr($options['from_name']); ?>" class="regular-text code">
                            <p class="description"><?php _e('The name which will be used as the From Name when sending an email.', 'gmail-smtp');?></p></td>
                    </tr>
                    
                    <tr>
                    <th scope="row"><label for="type_of_encryption"><?php _e('Type of Encryption', 'gmail-smtp');?></label></th>
                    <td>
                        <select name="type_of_encryption" id="type_of_encryption">
                            <option value="tls" <?php echo selected( $options['type_of_encryption'], 'tls', false );?>><?php _e('TLS', 'gmail-smtp');?></option>
                            <option value="ssl" <?php echo selected( $options['type_of_encryption'], 'ssl', false );?>><?php _e('SSL', 'gmail-smtp');?></option>
                        </select>
                        <p class="description"><?php _e('The encryption which will be used when sending an email (TLS is recommended).', 'gmail-smtp');?></p>
                    </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="smtp_port"><?php _e('SMTP Port', 'gmail-smtp');?></label></th>
                        <td><input name="smtp_port" type="text" id="smtp_port" value="<?php echo esc_attr($options['smtp_port']); ?>" class="regular-text code">
                            <p class="description"><?php _e('The port which will be used when sending an email. If you choose TLS it should be set to 587. For SSL use port 465 instead.', 'gmail-smtp');?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="disable_ssl_verification"><?php _e('Disable SSL Certificate Verification', 'gmail-smtp');?></label></th>
                        <td><input name="disable_ssl_verification" type="checkbox" id="disable_ssl_verification" <?php checked($options['disable_ssl_verification'], 1); ?> value="1">
                            <p class="description"><?php _e('As of PHP 5.6 you will get a warning/error if the SSL certificate on the server is not properly configured. You can check this option to disable that default behaviour. Please note that PHP 5.6 made this change for a good reason. So you should get your host to fix the SSL configurations instead of bypassing it', 'gmail-smtp');?></p></td>
                    </tr>

                </tbody>

            </table>

            <p class="submit"><input type="submit" name="gmail_smtp_update_settings" id="gmail_smtp_update_settings" class="button button-primary" value="<?php _e('Save Changes', 'gmail-smtp');?>"></p>
        </form>
        
        <?php
        if($this->can_grant_permission()){
        ?>
        <a class="button button-primary" href="<?php echo $_SERVER["REQUEST_URI"].'&action=oauth_grant'; ?>"><?php _e('Grant Permission', 'gmail-smtp');?></a>                             
        <?php
        }        
    }
    
    function advanced_settings() {
        ?>
        <div class="update-nag"><?php _e('Settings from add-ons will appear here.', 'gmail-smtp');?></div>
        <?php        
        if (isset($_POST['gmail_smtp_update_advanced_settings'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'gmail_smtp_advanced_settings')) {
                wp_die('Error! Nonce Security Check Failed! please save the settings again.');
            }
            $post = $_POST;
            do_action('gmail_smtp_advanced_settings_submitted', $post);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Settings Saved!', 'gmail-smtp');
            echo '</strong></p></div>';
        }
        $settings_fields = '';
        $settings_fields = apply_filters('gmail_smtp_advanced_settings_fields', $settings_fields);
        if(empty($settings_fields)){
            return;
        }
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('gmail_smtp_advanced_settings'); ?>

            <table class="form-table">
                <tbody>                                    
                    <?php
                    if(!empty($settings_fields)){
                        echo $settings_fields;
                    }
                    ?>
                </tbody>

            </table>

            <p class="submit"><input type="submit" name="gmail_smtp_update_advanced_settings" id="gmail_smtp_update_advanced_settings" class="button button-primary" value="<?php _e('Save Changes', 'gmail-smtp');?>"></p>
        </form>
        <?php
    }
    
    function can_grant_permission(){
        $options = gmail_smtp_get_option();    
        $grant_permission = true;
        if(!isset($options['oauth_client_id']) || empty($options['oauth_client_id'])){
            $grant_permission = false;
        }
        if(!isset($options['oauth_client_secret']) || empty($options['oauth_client_secret'])){
            $grant_permission = false;
        }
        if(isset($options['oauth_access_token']) && !empty($options['oauth_access_token'])){
            $grant_permission = false;
        }
        return $grant_permission;
    }

}

function gmail_smtp_get_option(){
    $options = get_option('gmail_smtp_options');
    return $options;
}

function gmail_smtp_update_option($new_options){
    $empty_options = gmail_smtp_get_empty_options_array();
    $options = gmail_smtp_get_option();
    if(is_array($options)){
        $current_options = array_merge($empty_options, $options);
        $updated_options = array_merge($current_options, $new_options);
        update_option('gmail_smtp_options', $updated_options);
    }
    else{
        $updated_options = array_merge($empty_options, $new_options);
        update_option('gmail_smtp_options', $updated_options);
    }
}

function gmail_smtp_get_empty_options_array(){
    $options = array();
    $options['oauth_client_id'] = '';
    $options['oauth_client_secret'] = '';
    $options['oauth_access_token'] = '';
    $options['oauth_user_email'] = '';
    $options['from_email'] = '';
    $options['from_name'] = '';
    $options['type_of_encryption'] = '';
    $options['smtp_port'] = '';
    $options['disable_ssl_verification'] = '';
    return $options;
}

function gmail_smtp_admin_notice() {        
    if(!is_gmail_smtp_configured()){
        ?>
        <div class="error">
            <p><?php _e('Gmail SMTP plugin cannot send email until you enter your credentials in the settings and grant access to your web application.', 'gmail-smtp'); ?></p>
        </div>
        <?php
    }
    if(version_compare(PHP_VERSION, '5.6', '<')){
        ?>
        <div class="error">
            <p><?php _e('Gmail SMTP plugin requires PHP 5.6 or higher. Please contact your web host to update your PHP version.', 'gmail-smtp'); ?></p>
        </div>
        <?php
    }
}

function is_gmail_smtp_configured() {
    $options = gmail_smtp_get_option();
    $smtp_configured = true;
    if(!isset($options['oauth_client_id']) || empty($options['oauth_client_id'])){
        $smtp_configured = false;
    }
    if(!isset($options['oauth_client_secret']) || empty($options['oauth_client_secret'])){
        $smtp_configured = false;
    }
    if(!isset($options['oauth_access_token']) || empty($options['oauth_access_token'])){
        $smtp_configured = false;
    }
    if(!isset($options['oauth_user_email']) || empty($options['oauth_user_email'])){
        $smtp_configured = false;
    }
    if(!isset($options['from_email']) || empty($options['from_email'])){
        $smtp_configured = false;
    }
    if(!isset($options['from_name']) || empty($options['from_name'])){
        $smtp_configured = false;
    }
    if(!isset($options['type_of_encryption']) || empty($options['type_of_encryption'])){
        $smtp_configured = false;
    }
    if(!isset($options['smtp_port']) || empty($options['smtp_port'])){
        $smtp_configured = false;
    }
    return $smtp_configured;
}

$GLOBALS['gmail-smtp'] = new GMAIL_SMTP();

function gmail_smtp_pre_wp_mail($null, $atts)
{
    if ( isset( $atts['to'] ) ) {
            $to = $atts['to'];
    }

    if ( ! is_array( $to ) ) {
            $to = explode( ',', $to );
    }

    if ( isset( $atts['subject'] ) ) {
            $subject = $atts['subject'];
    }

    if ( isset( $atts['message'] ) ) {
            $message = $atts['message'];
    }

    if ( isset( $atts['headers'] ) ) {
            $headers = $atts['headers'];
    }

    if ( isset( $atts['attachments'] ) ) {
            $attachments = $atts['attachments'];           
            if ( ! is_array( $attachments ) ) {
                    $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
            }
    }
    
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
    include_once('google-api-php-client/vendor/autoload.php');
    include_once('class.phpmaileroauthgoogle.php');
    include_once('class.phpmaileroauth.php');

    $options = gmail_smtp_get_option();

    $phpmailer = new PHPMailerOAuth; /* this must be the custom class we created */

    // Tell PHPMailer to use SMTP
    $phpmailer->isSMTP();

    // Set AuthType
    $phpmailer->AuthType = 'XOAUTH2';

    // Whether to use SMTP authentication
    $phpmailer->SMTPAuth = true;

    // Set the encryption system to use - ssl (deprecated) or tls
    $phpmailer->SMTPSecure = $options['type_of_encryption'];

    // Set the hostname of the mail server
    $phpmailer->Host = 'smtp.gmail.com';

    // Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $phpmailer->Port = $options['smtp_port'];

    $phpmailer->SMTPAutoTLS = false;

    //enable debug when sending a test mail
    if(isset($_POST['gmail_smtp_send_test_email'])){
        $phpmailer->SMTPDebug = 4;
        // Ask for HTML-friendly debug output
        $phpmailer->Debugoutput = 'html';
    }

    //disable ssl certificate verification if checked
    if(isset($options['disable_ssl_verification']) && !empty($options['disable_ssl_verification'])){
        $phpmailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    }
    // User Email to use for SMTP authentication - Use the same Email used in Google Developer Console
    $phpmailer->oauthUserEmail = $options['oauth_user_email'];

    //Obtained From Google Developer Console
    $phpmailer->oauthClientId = $options['oauth_client_id'];

    //Obtained From Google Developer Console
    $phpmailer->oauthClientSecret = $options['oauth_client_secret'];

    $gmail_token = json_decode($options['oauth_access_token'], true);

    //Obtained By running get_oauth_token.php after setting up APP in Google Developer Console.
    //Set Redirect URI in Developer Console as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
    // eg: http://localhost/phpmail/get_oauth_token.php
    $phpmailer->oauthRefreshToken = $gmail_token['refresh_token'];    

    // Headers.
    $cc       = array();
    $bcc      = array();
    $reply_to = array();

    if ( empty( $headers ) ) {
            $headers = array();
    } else {
            if ( ! is_array( $headers ) ) {
                    /*
                     * Explode the headers out, so this function can take
                     * both string headers and an array of headers.
                     */
                    $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
            } else {
                    $tempheaders = $headers;
            }
            $headers = array();

            // If it's actually got contents.
            if ( ! empty( $tempheaders ) ) {
                    // Iterate through the raw headers.
                    foreach ( (array) $tempheaders as $header ) {
                            if ( ! str_contains( $header, ':' ) ) {
                                    if ( false !== stripos( $header, 'boundary=' ) ) {
                                            $parts    = preg_split( '/boundary=/i', trim( $header ) );
                                            $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                                    }
                                    continue;
                            }
                            // Explode them out.
                            list( $name, $content ) = explode( ':', trim( $header ), 2 );

                            // Cleanup crew.
                            $name    = trim( $name );
                            $content = trim( $content );

                            switch ( strtolower( $name ) ) {
                                    // Mainly for legacy -- process a "From:" header if it's there.
                                    case 'from':
                                            $bracket_pos = strpos( $content, '<' );
                                            if ( false !== $bracket_pos ) {
                                                    // Text before the bracketed email is the "From" name.
                                                    if ( $bracket_pos > 0 ) {
                                                            $from_name = substr( $content, 0, $bracket_pos );
                                                            $from_name = str_replace( '"', '', $from_name );
                                                            $from_name = trim( $from_name );
                                                    }

                                                    $from_email = substr( $content, $bracket_pos + 1 );
                                                    $from_email = str_replace( '>', '', $from_email );
                                                    $from_email = trim( $from_email );

                                                    // Avoid setting an empty $from_email.
                                            } elseif ( '' !== trim( $content ) ) {
                                                    $from_email = trim( $content );
                                            }
                                            break;
                                    case 'content-type':
                                            if ( str_contains( $content, ';' ) ) {
                                                    list( $type, $charset_content ) = explode( ';', $content );
                                                    $content_type                   = trim( $type );
                                                    if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                                            $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                                                    } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                                            $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                                            $charset  = '';
                                                    }

                                                    // Avoid setting an empty $content_type.
                                            } elseif ( '' !== trim( $content ) ) {
                                                    $content_type = trim( $content );
                                            }
                                            break;
                                    case 'cc':
                                            $cc = array_merge( (array) $cc, explode( ',', $content ) );
                                            break;
                                    case 'bcc':
                                            $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                                            break;
                                    case 'reply-to':
                                            $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                                            break;
                                    default:
                                            // Add it to our grand headers array.
                                            $headers[ trim( $name ) ] = trim( $content );
                                            break;
                            }
                    }
            }
    }

    // Empty out the values that may be set.
    $phpmailer->clearAllRecipients();
    $phpmailer->clearAttachments();
    $phpmailer->clearCustomHeaders();
    $phpmailer->clearReplyTos();
    $phpmailer->Body    = '';
    $phpmailer->AltBody = '';

    // Set "From" name and email.

    // If we don't have a name from the input headers.
    if ( ! isset( $from_name ) ) {
            $from_name = $options['from_name'];//'WordPress';
    }

    /*
     * If we don't have an email from the input headers, default to wordpress@$sitename
     * Some hosts will block outgoing mail from this address if it doesn't exist,
     * but there's no easy alternative. Defaulting to admin_email might appear to be
     * another option, but some hosts may refuse to relay mail from an unknown domain.
     * See https://core.trac.wordpress.org/ticket/5007.
     */
    if ( ! isset( $from_email ) ) {
            // Get the site domain and get rid of www.
            $sitename   = wp_parse_url( network_home_url(), PHP_URL_HOST );
            $from_email = 'wordpress@';

            if ( null !== $sitename ) {
                    if ( str_starts_with( $sitename, 'www.' ) ) {
                            $sitename = substr( $sitename, 4 );
                    }

                    $from_email .= $sitename;
            }
            $from_email = $options['from_email'];//'wordpress@' . $sitename;
    }

    /**
     * Filters the email address to send from.
     *
     * @since 2.2.0
     *
     * @param string $from_email Email address to send from.
     */
    $from_email = apply_filters( 'wp_mail_from', $from_email );

    /**
     * Filters the name to associate with the "from" email address.
     *
     * @since 2.3.0
     *
     * @param string $from_name Name associated with the "from" email address.
     */
    $from_name = apply_filters( 'wp_mail_from_name', $from_name );

    try {
            $phpmailer->setFrom( $from_email, $from_name, false );
    } catch ( PHPMailer\PHPMailer\Exception $e ) {
            $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /** This filter is documented in wp-includes/pluggable.php */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

            return false;
    }
    /*reply_to code */
    $gmailsmtp_reply_to = '';
    $gmailsmtp_reply_to = apply_filters('gmailsmtp_reply_to', $gmailsmtp_reply_to);
    if(isset($gmailsmtp_reply_to) && !empty($gmailsmtp_reply_to)){
        $temp_reply_to_addresses = explode(",", $gmailsmtp_reply_to);
        $reply_to = array();
        foreach($temp_reply_to_addresses as $temp_reply_to_address){
            $reply_to_address = trim($temp_reply_to_address);
            $reply_to[] = $reply_to_address;
        }
    }
    /*end of reply_to code */
    /*cc code */
    $gmailsmtp_cc = '';
    $gmailsmtp_cc = apply_filters('gmailsmtp_cc', $gmailsmtp_cc);
    if(isset($gmailsmtp_cc) && !empty($gmailsmtp_cc)){
        $temp_cc_recipients = explode(",", $gmailsmtp_cc);
        $cc = array();
        foreach($temp_cc_recipients as $temp_cc_recipient){
            $cc_recipient = trim($temp_cc_recipient);
            $cc[] = $cc_recipient;
        }        
    }
    /*end of cc code */
    // Set mail's subject and body.
    $phpmailer->Subject = $subject;
    $phpmailer->Body    = $message;

    // Set destination addresses, using appropriate methods for handling addresses.
    $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );

    foreach ( $address_headers as $address_header => $addresses ) {
            if ( empty( $addresses ) ) {
                    continue;
            }

            foreach ( (array) $addresses as $address ) {
                    try {
                            // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
                            $recipient_name = '';

                            if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
                                    if ( count( $matches ) === 3 ) {
                                            $recipient_name = $matches[1];
                                            $address        = $matches[2];
                                    }
                            }

                            switch ( $address_header ) {
                                    case 'to':
                                            $phpmailer->addAddress( $address, $recipient_name );
                                            break;
                                    case 'cc':
                                            $phpmailer->addCc( $address, $recipient_name );
                                            break;
                                    case 'bcc':
                                            $phpmailer->addBcc( $address, $recipient_name );
                                            break;
                                    case 'reply_to':
                                            $phpmailer->addReplyTo( $address, $recipient_name );
                                            break;
                            }
                    } catch ( PHPMailer\PHPMailer\Exception $e ) {
                            continue;
                    }
            }
    }

    // Set Content-Type and charset.

    // If we don't have a Content-Type from the input headers.
    if ( ! isset( $content_type ) ) {
            $content_type = 'text/plain';
    }

    /**
     * Filters the wp_mail() content type.
     *
     * @since 2.3.0
     *
     * @param string $content_type Default wp_mail() content type.
     */
    $content_type = apply_filters( 'wp_mail_content_type', $content_type );

    $phpmailer->ContentType = $content_type;

    // Set whether it's plaintext, depending on $content_type.
    if ( 'text/html' === $content_type ) {
            $phpmailer->isHTML( true );
    }

    // If we don't have a charset from the input headers.
    if ( ! isset( $charset ) ) {
            $charset = get_bloginfo( 'charset' );
    }

    /**
     * Filters the default wp_mail() charset.
     *
     * @since 2.3.0
     *
     * @param string $charset Default email charset.
     */
    $phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

    // Set custom headers.
    if ( ! empty( $headers ) ) {
            foreach ( (array) $headers as $name => $content ) {
                    // Only add custom headers not added automatically by PHPMailer.
                    if ( ! in_array( $name, array( 'MIME-Version', 'X-Mailer' ), true ) ) {
                            try {
                                    $phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
                            } catch ( PHPMailer\PHPMailer\Exception $e ) {
                                    continue;
                            }
                    }
            }

            if ( false !== stripos( $content_type, 'multipart' ) && ! empty( $boundary ) ) {
                    $phpmailer->addCustomHeader( sprintf( 'Content-Type: %s; boundary="%s"', $content_type, $boundary ) );
            }
    }

    if ( isset( $attachments ) && ! empty( $attachments ) ) {
            foreach ( $attachments as $filename => $attachment ) {
                    $filename = is_string( $filename ) ? $filename : '';

                    try {
                            $phpmailer->addAttachment( $attachment, $filename );
                    } catch ( PHPMailer\PHPMailer\Exception $e ) {
                            continue;
                    }
            }
    }

    /**
     * Fires after PHPMailer is initialized.
     *
     * @since 2.2.0
     *
     * @param PHPMailer $phpmailer The PHPMailer instance (passed by reference).
     */
    do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

    $mail_data = compact( 'to', 'subject', 'message', 'headers', 'attachments' );

    // Send!
    try {
            $send = $phpmailer->send();

            /**
             * Fires after PHPMailer has successfully sent an email.
             *
             * The firing of this action does not necessarily mean that the recipient(s) received the
             * email successfully. It only means that the `send` method above was able to
             * process the request without any errors.
             *
             * @since 5.9.0
             *
             * @param array $mail_data {
             *     An array containing the email recipient(s), subject, message, headers, and attachments.
             *
             *     @type string[] $to          Email addresses to send message.
             *     @type string   $subject     Email subject.
             *     @type string   $message     Message contents.
             *     @type string[] $headers     Additional headers.
             *     @type string[] $attachments Paths to files to attach.
             * }
             */
            do_action( 'wp_mail_succeeded', $mail_data );

            return $send;
    } catch ( PHPMailer\PHPMailer\Exception $e ) {
            $mail_data['phpmailer_exception_code'] = $e->getCode();

            /**
             * Fires after a PHPMailer\PHPMailer\Exception is caught.
             *
             * @since 4.4.0
             *
             * @param WP_Error $error A WP_Error object with the PHPMailer\PHPMailer\Exception message, and an array
             *                        containing the mail recipient, subject, message, headers, and attachments.
             */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_data ) );

            return false;
    }
    
}