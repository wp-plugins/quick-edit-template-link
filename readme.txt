=== Plugin Name ===
Contributors: ChubbyNinjaa
Tags: admin, theme editor, templates, admin bar
Requires at least: 3.0.1
Tested up to: 4.3
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Quickly find out what theme file(s) is currently being used (archive.php single.php..) with this handy Admin Bar dropdown



== Description ==

This plugin adds a handy link at the top of the admin bar giving you the ability to quickly see what theme template file(s) is being used, as well as what other files are being included, from themes and plugins.

If the admin user has access to edit templates, they are provided with a link to the theme editor.


== Installation ==

Just install and navigate to the front end of your website, you'll see a new part to the admin bar showing you the current template and included files.

If you want to show the plugins, and/or exclude certain plugins from showing in this dropdown, go to your WP Dashboard, Settings > Quick Edit Template Link

== Frequently Asked Questions ==

= Can Editors and Subscribers view the template? =
They can see the name of the template in the admin bar, but they cannot click the link into the theme editor unless you have given them capabilities

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png

== Changelog ==

= 2.0 =
* New: Ability to see what plugin files are being included
* New: Ability to navigate recursively through directories
* New: Settings Page to exclude specific plugins from showing
* New: Specify level of recursive in settings
* Fixed: Plugin running while in the dashboard
* Fixed: Overflow issues

= 1.1 =
* Fixed: Dropdown items missing when list too long for viewport

= 1.0.1 =
* Now shows a dropdown of all template includes, if you are using a parent and child theme combo it will nest them nicely by theme location

= 1.0.0 =
* Initial Release

== Upgrade Notice ==

Upgrade to get the ability to customize the plugin, plus bug fixes