=== Plugin Name ===
Tags: Pocket, widget
Requires at least: 3.0.1
Tested up to: 3.9
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Wordpress widget to show your Pocket collection.

== Description ==

A [WordPress](http://wordpress.org/) widget to show your [Pocket](http://getpocket.com/) collection.
The Pocket widget is a Wordpress plugin that connects to your Pocket account and displays your Pocket articles in a widget. It is almost a direct translation of the Pocket API to a Wordpress widget.

== Installation ==

Either use Wordpress' built-in plugin installer.
Or install manually: download and unzip (or clone) the plugin to the /wp-content/plugins/ directory. Then activate the plugin through the Plugins menu in WordPress.

== Setup ==

Before you start you need to authenticate the plugin to access your Pocket account.
Go to the Pocket Widget settings and click **authenticate**. This wil take you to Pocket which will tell you what this app is authorised. If you **authorize** it you'll be taken back to the settings page.
The newly added 'Access token' together with the 'Consumer key' is your access to Pocket. Save the settings.

By default Pocket Widget has it's own Pocket App for API access.
You can easily setup your own by going to http://getpocket.com/developer/apps/ and clicking **Create an Application**.
Then give it a name, allow it only to **retreive**, and set the platform to **web**. Then accept terms of service and click **create application**.
Copy the **consumer key** to the Pocket Widget settings and click **Save changes**.

== Versioning and issues ==

The main CVS repo for this plugin is on Github. The version up on Wordpress is a distilled build of the major tags.
If you have any issues or suggestions please put them on [Github](https://github.com/Sjeiti/Pocket-Widget/issues).

