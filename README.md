# Gmail SMTP

[Gmail SMTP](https://wordpress.org/plugins/gmail-smtp/) is a WordPress plugin that allows you to authenticate with your Gmail account to send email via Gmail SMTP server. It was developed by [naa986](https://wphowto.net/) and is currently being used on over 20,000 websites.

Most shared hosting servers have restrictions when it comes to email. Usually email will get blocked or missing for no reason. Sometimes it will get blocked when your website reaches the daily limit of outgoing email. This plugin can bypass this issue by routing the email through Gmail's SMTP server.

## Requirements

* PHP 5.4 or later
* A Gmail Account
* A self-hosted WordPress site

## Gmail SMTP Benefits

* Gmail SMTP plugin is not like most SMTP plugins. It uses the OAuth 2.0 protocol to authorize access to the Gmail API - which means a more secure login system and users won't have to enter any username or password.
* Gmail SMTP plugin uses PHPMailer - a very popular library used for sending email through PHP's mail function. This libary is also used in the core WordPress to send email.
* Gmail SMTP plugin utilizes "wp_mail" (A function used by WordPress to send email) instead of completely overriding it. This way you still get all the benefits of the default mail function. 
* You no longer need to enable **Allow less secure apps** on your gmail account to fix SMTP connection issue. This issue became prominent from December 2014, when Google started imposing XOAUTH2 authentication (based on OAuth2) to access their apps. This issue still affects almost all the SMTP plugins because they authenticate via username and password.

## How OAuth 2.0 Authorization Works

* You register an application in the Google Developers Console.
* The application is launched and it requests that you give it access to data in your Google account.
* If you consent, the application receives credentials to access the Gmail API.

## Gmail SMTP Features

* Configure your website to send email using Gmail SMTP server
* Authenticate using OAuth 2.0 protocol
* Authenticate with encryption when sending an email (TLS/SSL)

## Gmail SMTP Basic Setup

* Create a new project in Google Developers Console.
* Enable Gmail API in it.
* Create credentials (OAuth client ID) to access this API.
* Configure the consent screen for the web application.
* Enter a **Product Name** and a **Privacy policy URL**.
* Once the consent screen is configured, create a web application.
* Go to the plugin settings (**Settings->Gmail SMTP**).
* Set the **Authorized Redirect URL** of the application as the one shown in the settings.
* Finish creating the web app.
* Copy the newly created **Client ID** and **Client secret** and paste into the settings area.
* Enter your OAuth Email, From Email and From name.
* Select an encryption.
* Enter a port number.
* Save the settings.
* Now you can authorize your application to access the Gmail API by clicking on the **Grant Permission** button.
* Once the application has been authorized Gmail SMTP plugin will be able to take control of all outgoing email.

## Gmail SMTP Settings
 
* **Authorized Redirect URI**: Authorized redirect URL for your website. You need to copy this URL into your web application.
* **Client ID**: The client ID of your web application.
* **Client secret**: The client secret of your web application.
* **OAuth Email Address**: The email address that you will use for SMTP authentication. This should be the same email used in the Google Developers Console.
* **From Email Address**: The email address which will be used as the From Address when sending an email.
* **From Name**: The name which will be used as the From Name when sending an email.
* **Type of Encryption**: The encryption which will be used when sending an email (TLS/SSL. TLS is recommended).
* **SMTP Port**: The port which will be used when sending an email. If you choose TLS it should be set to 587. For SSL use port 465 instead.
* **Disable SSL Certificate Verification**: As of PHP 5.6 a warning/error will be displayed if the SSL certificate on the server is not properly configured. You can check this option to disable that default behaviour.

## Gmail SMTP Test Email

Once you have configured the settings you can send a test email to check the functionality of the plugin.
 
* **To**: Email address of the recipient.
* **Subject**: Subject of the email.
* **Message**: Email body.

For detailed setup instructions/troubleshooting please visit the [Gmail SMTP](https://wphowto.net/gmail-smtp-plugin-for-wordpress-1341) plugin page.
