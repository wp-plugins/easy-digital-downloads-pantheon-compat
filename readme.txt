=== Easy Digital Downloads - Pantheon Compat ===
Contributors: section214
Tags: easy digital downloads, edd, pantheon
Requires at least: 3.6
Tested up to: 4.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Compatibility plugin for Easy Digital Downloads on Pantheon

== Description ==

By default, activating EDD on a site hosted on Pantheon will result in a notice being displayed on the Dashboard informing you that your files aren't protected. This is due to Pantheon using a pure NGINX server, which ignores the .htaccess file EDD uses to protect its upload directory. EDD Pantheon Compat provides a simple compatibility layer which converts the standard EDD archive structure to work with the Pantheon protected directory structure, allowing you to maintain file security, and still use EDD on Pantheon!

Requires [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/).

== Installation ==

1. Upload `edd-pantheon-compat` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enjoy!

== Changelog ==

= Version 1.0.0 =
* Initial release
