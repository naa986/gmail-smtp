=== Gmail SMTP ===
Contributors: naa986
Donate link: https://wphowto.net/
Tags: smtp, gmail, mail, email, oauth
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 5.6
Stable tag: 1.2.3.14
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect to Gmail SMTP server to automatically send email from your WordPress site. Configure wp_mail() to use SMTP with OAuth 2.0 authentication.

== Description ==

[Gmail SMTP](https://wphowto.net/gmail-smtp-plugin-for-wordpress-1341) plugin allows you to authenticate with your Gmail account to send email via Gmail SMTP server.

https://www.youtube.com/watch?v=_MCxcvfoGj4&rel=0

Most shared hosting servers have restrictions when it comes to email. Usually email will get blocked or missing for no reason. Sometimes it will get blocked when your website reaches the daily limit of outgoing email. This plugin can bypass this issue by routing the email through Gmail's SMTP server.

=== Gmail SMTP Add-ons ===

* [Reply-To](https://wphowto.net/how-to-add-a-reply-to-address-in-the-gmail-smtp-wordpress-plugin-6756)
* [Cc](https://wphowto.net/gmail-smtp-plugin-cc-configuration-6770)

=== Gmail SMTP Benefits ===

* Gmail SMTP plugin is not like most SMTP plugins. It uses the OAuth 2.0 protocol to authorize access to the Gmail API - which means a more secure login system and users won't have to enter any username or password.
* Gmail SMTP plugin uses PHPMailer - a very popular library used for sending email through PHP's mail function. This libary is also used in the core WordPress to send email.
* Gmail SMTP plugin utilizes "wp_mail" (A function used by WordPress to send email) instead of completely overriding it. This way you still get all the benefits of the default mail function. 
* You no longer need to enable **Allow less secure apps** on your gmail account to fix SMTP connection issue. This issue became prominent from December 2014, when Google started imposing XOAUTH2 authentication (based on OAuth2) to access their apps. This issue still affects almost all the SMTP plugins because they authenticate via username and password.

=== How OAuth 2.0 Authorization Works ===

* You register an application in the Google Developers Console.
* The application is launched and it requests that you give it access to data in your Google account.
* If you consent, the application receives credentials to access the Gmail API.

=== Gmail SMTP Features ===

* Configure your website to send email using Gmail SMTP server
* Authenticate using OAuth 2.0 protocol
* Authenticate with encryption when sending an email (TLS/SSL)

=== Gmail SMTP Basic Setup ===

* Create a new project in Google Developers Console.
* Enable Gmail API in it.
* Create credentials (OAuth client ID) to access this API.
* Configure the consent screen for the web application.
* Enter a **Product Name** and a **Privacy policy URL**.
* Once the consent screen is configured, create a web application.
* Go to the plugin settings (`Settings->Gmail SMTP`).
* Set the **Authorized Redirect URL** of the application as the one shown in the settings.
* Finish creating the web app.
* Copy the newly created **Client ID** and **Client secret** and paste into the settings area.
* Enter your OAuth Email, From Email and From name.
* Select an encryption.
* Enter a port number.
* Save the settings.
* Now you can authorize your application to access the Gmail API by clicking on the **Grant Permission** button.
* Once the application has been authorized Gmail SMTP plugin will be able to take control of all outgoing email.

=== Gmail SMTP Settings ===
 
* **Authorized Redirect URI**: Authorized redirect URL for your website. You need to copy this URL into your web application.
* **Client ID**: The client ID of your web application.
* **Client secret**: The client secret of your web application.
* **OAuth Email Address**: The email address that you will use for SMTP authentication. This should be the same email used in the Google Developers Console.
* **From Email Address**: The email address which will be used as the From Address when sending an email.
* **From Name**: The name which will be used as the From Name when sending an email.
* **Type of Encryption**: The encryption which will be used when sending an email (TLS/SSL. TLS is recommended).
* **SMTP Port**: The port which will be used when sending an email. If you choose TLS it should be set to 587. For SSL use port 465 instead.
* **Disable SSL Certificate Verification**: As of PHP 5.6 a warning/error will be displayed if the SSL certificate on the server is not properly configured. You can check this option to disable that default behaviour.

=== Gmail SMTP Test Email ===

Once you have configured the settings you can send a test email to check the functionality of the plugin.
 
* **To**: Email address of the recipient.
* **Subject**: Subject of the email.
* **Message**: Email body.

For detailed setup instructions please visit the [Gmail SMTP](https://wphowto.net/gmail-smtp-plugin-for-wordpress-1341) plugin page.

== Installation ==

1. Go to the Add New plugins screen in your WordPress Dashboard
1. Click the upload tab
1. Browse for the plugin file (gmail-smtp.zip) on your computer
1. Click "Install Now" and then hit the activate button

== Frequently Asked Questions ==

= Can I send email from my Gmail account using this plugin? =

Yes.

= Can this plugin use OAuth 2.0 to send email? =

Yes.

= Can I use this plugin for Gmail signup? =

No.

= Can I use this plugin on Gmail app? =

No.

= Can I use this plugin to access my Gmail inbox? =

No.

= Can I use this plugin to sign out of my Gmail account? =

No.

== Screenshots ==

For screenshots please visit the [Gmail SMTP](https://wphowto.net/gmail-smtp-plugin-for-wordpress-1341) plugin page

== Upgrade Notice ==
none

== Changelog ==

= 1.2.3.14 =
* Compatibility update for WordPress 6.8.

= 1.2.3.13 =
* Compatibility update for WordPress 6.7.

= 1.2.3.12 =
* Compatibility update for WordPress 6.5.

= 1.2.3.11 =
* Compatibility update for WordPress 6.4.

= 1.2.3.10 =
* Additional check for the settings link.

= 1.2.3.9 =
* Compatibility update for WordPress 6.3.

= 1.2.3.8 =
* Proper sanitization of the test email body.

= 1.2.3.7 =
* Removed slashes from test email subject and body.

= 1.2.3.6 =
* Compatibility update for WordPress 6.2.

= 1.2.3.5 =
* Added support for the Cc add-on.

= 1.2.3.4 =
* Added support for the Reply-To add-on.

= 1.2.3.3 =
* Compatibility update for WordPress 6.1.

= 1.2.3.2 =
* Updated the mail function for WordPress 6.0.

= 1.2.3.1 =
* Fixed an issue that could cause an Undefined variable error if $attachments was not set.

= 1.2.3 =
* Updated the mail function for WordPress 5.9.

= 1.2.2 =
* Updated for WordPress 5.8.
* Updated the help text for the From Email Address field.

= 1.2.1 =
* WordPress 5.7 compatibility update.

= 1.2.0 =
* WordPress 5.5 compatibility update.

= 1.1.9 =
* Gmail SMTP is compatible with WordPress 5.3.

= 1.1.8 =
* Fixed this warning that is shown on servers running on PHP 7.2 or higher: PHP Deprecated: __autoload() is deprecated, use spl_autoload_register() instead in /wp-content/plugins/gmail-smtp/PHPMailer/PHPMailerAutoload.php on line 45

= 1.1.7 =
* Added some error handling code after the authorized redirection step. This is to make sure that the plugin doesn't save the error if an error occurs and no access token is returned.

= 1.1.6 =
* Updated the PHPMailer library to version 5.2.26. This release fixes a potential XSS vulnerability in the default debug output.
* Gmail SMTP is now compatible with WordPress 4.9.

= 1.1.5 =
* Updated the minimum requirements for PHP to 5.6 since it was causing a curl_reset() error in the Google API Client library.

= 1.1.4 =
* Made some changes to only include the Google API Client library and PHPMailer when needed.

= 1.1.3 =
* Updated the Google API Client library to version 2.2.0.
* Updated the PHPMailer library to version 5.2.24. This release contains a security fix for XSS vulnerability CVE-2017-11503.

= 1.1.2 =
* Removed the "Basic Setup Instructions" section from the settings page. Full setup instructions are now available on the documentation page.

= 1.1.1 =
* Updated the PHPMailer library to version 5.2.22. This release contains a critical security fix for CVE-2017-5223.

= 1.1.0 =
* Updated the PHPMailer library to version 5.2.21. This release contains a critical security update for CVE-2016-10045 that was fixed in PHPMailer 5.2.20.

= 1.0.9 =
* Updated the PHPMailer library to the latest version (5.2.19). This release also contains a critical security update for CVE-2016-10033 that was fixed in PHPMailer 5.2.18.

= 1.0.8 =
* Gmail SMTP now supports the "wp_mail_failed" hook which fires after a phpmailerException is caught.

= 1.0.7 =
* Added more requirements to the Server Info tab to help with troubleshooting.
* Compatible with WooCommerce email in HTML format.

= 1.0.6 =
* Added a new option to bypass this error on some servers where the SSL certificate is not properly configured - Warning: stream_socket_enable_crypto(): SSL operation failed with code 1.
OpenSSL Error messages: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed

= 1.0.5 =
* Fixed a bug that was causing this error - "Strict Standards: Non-static method GmailXOAuth2::getClient()"

= 1.0.4 =
* google-api-php-client's autoloader path now points to the src directory as the current autoloader has been deprecated
* Gmail SMTP plugin is now compatible with WordPress 4.4

= 1.0.3 =
* Added a new tab to show some server related information
* More debug data will be shown when sending a test email

= 1.0.2 =
* Fixed a bug where the oauth URL was pointing to localhost

= 1.0.1 =
* First commit
