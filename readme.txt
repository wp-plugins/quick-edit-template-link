=== Plugin Name ===
Contributors: ChubbyNinjaa
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=A66YW2BYEESFW
Tags: admin, theme editor, templates, admin bar, debug, debugger, child theme, generator
Requires at least: 3.0.1
Tested up to: 4.3
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Intended for Developers: This template debugger helps you identify what template files are being used on the page you're viewing.



== Description ==

Do you get frustrated trying to work out what the template file you're editing is? Ever had to echo/print some text to see if WordPress is picking up the file you **expect** it to?

If yes, then give this plugin a try, it simply adds a handy dropdown at the top of the admin bar giving you the ability to quickly see what theme template file(s) is being used, as well as what other files are being included, from themes and plugins.

Never get frustrated with creating child themes again, using Template Debugger you can generate a new child theme based on one of the installed parent themes, and Template Debugger does all the leg work! Just fill out the input fields and Template Debugger will create the theme directory, add in the style.css and functions.php


== Installation ==

The simplest way to install

1. In your Wordpress Admin click on 'Plugins' then 'Add New'
2. Type 'Template Debugger' in the search field.
3. Navigate to the front end of your website, you'll see a new part to the admin bar showing you the current template and included files.

Alternatively,

1. Upload the plugin to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

If you want to show the plugins, and/or exclude certain plugins from showing in this dropdown, go to your WP Dashboard, Settings > Template Debugger


== Frequently Asked Questions ==

= Can Editors and Subscribers view the template? =
They can see the name of the template in the admin bar, but they cannot click the link into the theme editor unless you have given them capabilities

== Screenshots ==

1. screenshot-2.png
2. screenshot-3.png
3. screenshot-4.png
4. screenshot-5.png

== Changelog ==

= 2.1.1 =
* New: Current template file highlighted with an asterisks (*)
* Fixed: Link to theme editor broken

= 2.1.0 =
* New: You can now generate a child theme inside Template Debugger
* Fixed: Missing minified CSS file

= 2.0.1 =

* Updated: Name changed from Template Quick Edit Link (a bit of a mouthful!) to Template Debugger
* Updated: Add minified version of CSS
* Updated: Reformatted code
* Updated: Updated settings page

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
Upgrade to fix broken theme editor link