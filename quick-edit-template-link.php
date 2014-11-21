<?php
/**
 * Plugin Name: Quick Edit Template Link
 * Plugin URI: http://www.chubbyninja.co.uk
 * Description: Displays the current template in the admin bar with a link directly to the template in theme editor (if the current user has access to it)
 * Version: 1.0.0
 * Author: Danny Hearnah - ChubbyNinja
 * Author URI: http://danny.hearnah.com
 * License: GPL2

    Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : dan.hearnah@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

defined('ABSPATH') or die("No direct access");

function chubby_ninja_admin_bar_init()
{
	if (!is_super_admin() || !is_admin_bar_showing() )
	{
		return;
	}
	add_action('admin_bar_menu', 'chubby_ninja_admin_bar_link', 500);
}

add_action('admin_bar_init', 'chubby_ninja_admin_bar_init');


function chubby_ninja_admin_bar_link()
{
	global $wp_admin_bar, $template;
	

	$href = '#';

	$name = end( explode( '/wp-content/', $template ) );
	
	if( current_user_can( 'edit_themes' ) )
	{
		$n = end(explode( '/', $name, 2 ) );
		$parts = explode('/',$n, 2 );

		$url = get_bloginfo('wpurl') . '/wp-admin/theme-editor.php?file=%s&theme=%s';
		$href = sprintf($url, $parts[1], $parts[0] );
	}

	// Add as a parent menu
	$wp_admin_bar->add_node( array(
		'title' => $name,
		'href' => $href,
		'id' => 'edit-tpl'
	));
}