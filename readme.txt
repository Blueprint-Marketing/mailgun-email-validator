=== Mailgun Email Validator ===
Contributors: jesin
Tags: email validation, comments, spam, validation, anti-spam, contact form 7, jetpack, grunion, contact form
Requires at least: 3.1.0
Tested up to: 3.6.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Kick spam with a highly advanced email validation in comment forms, user registration and contact forms using Mailgun's Email validation service.

== Description ==

Most email validators look for an `@` and a `.`(dot) some go further and blacklist certain domain names. But Mailgun's Advanced email validation service goes deeper and looks for the existence of the domain name, presence of a [MX record](http://en.wikipedia.org/wiki/MX_record) and the custom ESP(Email Service Provider) grammar.
The grammar here is the rules defined by each email provider. For example, Yahoo Mail addresses can only contain letters, numbers, underscores, and one period. 
So `user.name.abc@yahoo.com` perfectly passes the [is_email()](http://codex.wordpress.org/Function_Reference/is_email) function but can never exist as it contains more than one period. Such addresses can't escape Mailgun's Email validation.

= Why use Mailgun's email validation service? =

* Performs the usual email syntax check.
* Checks the existence of the email domain. So `user@some-random-characters.com` can't escape.
* Checks if the email domain has a MX record. So `anything@example.com` is caught.
* Checks if the username complies with the grammar of its ESP (Email Service provider). Eg Gmail doesn't allow usernames less than 6 characters and hyphens so `small@gmail.com` and `hyphen-user@gmail.com` can't get away.

= Why use this plugin? =

* Integrates with the [is_email()](http://codex.wordpress.org/Function_Reference/is_email) function of WordPress. So it works seamlessly with Contact Form 7, Jetpack/Grunion contact forms, WordPress registration form and any form which uses the `is_email()` function.
* Kicks spam before it is inserted into the database
* Ensures that the commenting process is uninterrupted even if Mailgun suffers a [downtime](http://status.mailgun.com)
* Works completely transparent, nothing changes in the frontend

This plugin requires a Mailgun Public API Key which can be obtained through a free [sign-up at Mailgun](https://mailgun.com/signup)(No credit card required).

If you're trying out this plugin on a local WAMP/LAMP/MAMP installation make sure your system is connected to the Internet for this plugin to contact Mailgun.

= Further Reading =
Read about Mailgun's email validation service.

* <http://blog.mailgun.com/post/free-email-validation-api-for-web-forms/>
* <http://blog.mailgun.com/post/weekly-product-update-improvements-to-email-validation-api/>
* [Mailgun Address Validator demo](http://mailgun.github.io/validator-demo/) and its [source code](https://github.com/mailgun/validator-demo/tree/gh-pages)
* The [Mailgun Email Validator Plugin](http://jesin.tk/wordpress-plugins/mailgun-email-validator/) official homepage.

== Installation ==

1. Unzip and upload the `mailgun-email-validator` folder to the `/wp-content/plugins/` directory.
2. Activate the <strong>Mailgun Email Validator</strong> plugin through the 'Plugins' menu in WordPress.
3. Configure the plugin by going to `General > Email Validation` page.
4. [Signup](https://mailgun.com/signup) for a Mailgun account (it is completely free no credit card required).
5. [Login](https://mailgun.com/sessions/new), copy and paste your public API key to `General > Email Validation` page.

== Frequently Asked Questions ==

= Why did you create this plugin? =
I hate comment spam because it bloats my database. I also don't like bugging my visitors with CAPTCHAs in the form of scribbled text, 
counting the puppies and answering questions like *What is 3 + 2?*
So when Mailgun released their email validation service I tried validating the email addresses of comments in the spam queue of [my blog](http://jesin.tk).
I found that nearly 50% of these email addresses were identified incorrect by Mailgun. 
Thus this plugin was born. Though not as effective as CAPTCHAs this plugin can prevent a decent amount of spam while maintaining user experience.

= Is this plugin a product of Mailgun? =
No. It only makes use of Mailgun's email validation service API. Nothing in the code belongs to Mailgun.

= I get a *401 Unauthorized* error when I verify the API key but I'm very sure that it is correct =
You could be using the normal API key, for this plugin you need to enter the **Public API Key** this is slightly 
longer than the normal API key and is found just below it.

= This plugin is active and I have entered a Valid Public API Key but it doesn't work =
Try the email validation demo from the plugin's option page. It could a connectivity issue.

= What happens if Mailgun's service is down? =
In such cases emails are passed on untouched (as though this plugin is nonexistent) and on the front-end users won't notice anything.

= What contact form plugins are supported? =
Any form which uses the `is_email()` function are supported this means the most popular ones like Contact Form 7, Jetpack by WordPress.com, Grunion Contact Form
and a lot of others are supported.

= Does Mailgun support this plugin? =
No, because Mailgun didn't create it. If you need support create a thread 
by choosing the **Support** tab of this plugin. If you directly create a thread in the forum I'll never know of its existence.

= I want to see a demo of emails validating =
Mailgun has created a [jquery demo](http://mailgun.github.io/validator-demo/) at this page. The code for this demo is [available here](https://github.com/mailgun/validator-demo/tree/gh-pages).

== Changelog ==

= 1.0 =
* Initial version

== Screenshots ==
1. Enter your public API key to begin validation.
2. A comment with an incorrect email address.
3. This plugin in action on the user registration page.
4. Email validation in a contact form.