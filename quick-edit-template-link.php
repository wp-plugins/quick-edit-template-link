<?php
/**
 * Plugin Name: Template Debugger
 * Plugin URI: http://www.chubbyninja.co.uk
 * Description: A template debugger that helps you identify what template files are being used on the page you're viewing
 * Version: 2.0.1
 * Author: Danny Hearnah - ChubbyNinjaa
 * Author URI: http://danny.hearnah.com
 * License: GPL2
 *
 * Copyright 2014  DANNY HEARNAH  (email : dan.hearnah@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'ABSPATH' ) or die( "No direct access" );

$qetl_current_template = '';

/**
 *
 */
function chubby_ninja_admin_bar_style() {
	wp_register_style( 'quick-edit-template-link', plugin_dir_url( __FILE__ ) . 'css/quick_edit_template_link.min.css' );
	wp_enqueue_style( 'quick-edit-template-link' );
}

/**
 *
 */
function chubby_ninja_admin_bar_init() {
	if ( ! is_super_admin() || ! is_admin_bar_showing() || is_admin() ) {
		return;
	}
	add_action( 'wp_enqueue_scripts', 'chubby_ninja_admin_bar_style' );
	add_action( 'admin_bar_menu', 'chubby_ninja_admin_bar_link', 500 );
}

/**
 *
 */
function chubby_ninja_admin_bar_link() {
	global $wp_admin_bar, $template;

	$href = $url = '#';

	$explode_on = 'themes';
	if ( strstr( $template, '/wp-content/plugins/' ) ) {
		$explode_on = 'plugins';
	}
	$name = str_replace( '/', ' &rarr; ', end( explode( '/wp-content/' . $explode_on . '/', $template ) ) );


	if ( current_user_can( 'edit_themes' ) ) {
		$n     = end( explode( '/', $name, 2 ) );
		$parts = explode( '/', $n, 2 );

		$url  = get_bloginfo( 'wpurl' ) . '/wp-admin/theme-editor.php?file=%s&theme=%s';
		$href = sprintf( $url, $parts[1], $parts[0] );
	}

	// Add as a parent menu
	$wp_admin_bar->add_node( array(
		'title' => '<span class="ab-icon"></span>' . $name,
		'href'  => $href,
		'id'    => 'edit-tpl'
	) );


	$options = get_option( 'qetl_settings' );

	if ( empty( $options['qetl_max_recursive'] ) ) {
		$options['qetl_max_recursive'] = 99;
	}

	addPart( parse_includes(), 'edit-tpl', $url, 0, $options['qetl_max_recursive'] );
}


/**
 * @param $parts
 * @param $class
 * @param $url
 * @param int $depth
 * @param int $max_depth
 * @param string $prepend_path
 * @param string $type
 */
function addPart( $parts, $class, $url, $depth = 0, $max_depth = 99, $prepend_path = '', $type = '' ) {

	global $wp_admin_bar, $qetl_current_template;

	if ( ! is_array( $parts ) ) {
		return;
	}
	if ( $depth > $max_depth ) {
		return;
	}

	foreach ( $parts as $key => $part ) {

		if ( $depth == 0 ) {
			$type = $key;
		}
		if ( $depth == 1 ) {
			$qetl_current_template = $key;
		}

		$id = $class . '-' . $key;

		if ( is_array( $part ) ) {

			if ( $depth >= 2 ) {
				$prepend_path .= $key . '/';
			}

			$wp_admin_bar->add_node( array(
				'parent' => $class,
				'title'  => $key,
				'href'   => '#',
				'id'     => $id
			) );

			addPart( $part, $id, $url, ( $depth + 1 ), $max_depth, $prepend_path, $type );
		} else {
			$href = '#';

			if ( current_user_can( 'edit_themes' ) && $type == 'themes' ) {
				$_part = $prepend_path . $part;
				$href  = sprintf( $url, $_part, $qetl_current_template );
			}

			// Add as a parent menu
			$wp_admin_bar->add_node( array(
				'parent' => $class,
				'title'  => $part,
				'href'   => $href,
				'id'     => $id
			) );
		}

	}
}

/**
 * @param $path
 * @param string $separator
 *
 * @return array
 */
function pathToArray( $path, $separator = '/' ) {
	if ( ( $pos = strpos( $path, $separator ) ) === FALSE ) {
		return array( $path );
	}

	return array( substr( $path, 0, $pos ) => pathToArray( substr( $path, $pos + 1 ) ) );
}

/**
 * @return array
 */
function parse_includes() {

	$options = get_option( 'qetl_settings' );

	$files = get_included_files();

	$incs = array();

	foreach ( $files as $f => $file ) {
		if ( ! strstr( $file, '/wp-content/themes/' ) && ! strstr( $file, '/wp-content/plugins/' ) ) {
			continue;
		}

		if ( empty( $options['qetl_checkbox_plugins'] ) && strstr( $file, '/wp-content/plugins/' ) ) {
			continue;
		}

		$file = end( explode( '/wp-content/', $file ) );

		if ( strstr( $file, 'plugins' ) ) {
			$hash = md5( current( explode( '/', str_replace( 'plugins/', '', $file ) ) ) );
			if ( $options[ 'qetl_exclude_plugin_' . $hash ] ) {
				continue;
			}
		}

		$incs = array_merge_recursive( $incs, pathToArray( $file ) );
	}

	return $incs;
}

/**
 *
 */
function qetl_add_admin_menu() {

	add_options_page( 'Template Debugger', 'Template Debugger', 'manage_options', 'quick_edit_template_link', 'quick_edit_template_link_options_page' );

}

/**
 *
 */
function qetl_settings_init() {

	register_setting( 'pluginPage', 'qetl_settings' );


	add_settings_section( 'qetl_pluginPage_section', __( 'General', 'wordpress' ), 'qetl_settings_section_callback', 'pluginPage' );

	add_settings_section( 'qetl_pluginPage_section2', __( 'Plugins', 'wordpress' ), 'qetl_settings_section2_callback', 'pluginPage' );

	add_settings_field( 'qetl_checkbox_plugins', __( 'Show Plugins in Dropdown', 'wordpress' ), 'qetl_checkbox_field_0_render', 'pluginPage', 'qetl_pluginPage_section2' );

	add_settings_field( 'qetl_exclude_plugins', __( 'Exclude From Dropdown', 'wordpress' ), 'qetl_checkbox_field_1_render', 'pluginPage', 'qetl_pluginPage_section2' );

	add_settings_field( 'qetl_exclude_plugins', __( 'Maximum recursive depth', 'wordpress' ), 'qetl_textarea_field_0_render', 'pluginPage', 'qetl_pluginPage_section' );


}

/**
 *
 */
function qetl_checkbox_field_0_render() {

	$options = get_option( 'qetl_settings' );
	?>
	<input type='checkbox' name='qetl_settings[qetl_checkbox_plugins]' <?php checked( $options['qetl_checkbox_plugins'], 1 ); ?> value='1'>
	<?php
}

/**
 *
 */
function qetl_textarea_field_0_render() {

	$options = get_option( 'qetl_settings' );
	?>
	<input type='text' name='qetl_settings[qetl_max_recursive]' value='<?= $options['qetl_max_recursive'] ?>'> (0 = unlimited)
	<?php
}

/**
 *
 */
function qetl_checkbox_field_1_render() {

	$options = get_option( 'qetl_settings' );
	$plugins = get_plugins();

	foreach ( $plugins as $key => $val ) {
		$hash = md5( current( explode( '/', $key ) ) );
		?>
		<input type='checkbox' name='qetl_settings[qetl_exclude_plugin_<?= $hash ?>]' <?php checked( $options[ 'qetl_exclude_plugin_' . $hash ], 1 ); ?> value='1'>
		<?= $val['Name'] ?><br>
		<?php
	}

}

/**
 *
 */
function qetl_settings_section_callback() {

	echo __( 'This plugin appends the admin bar with a dropdown showing you what files are being included on that specific page', 'wordpress' );

}

/**
 *
 */
function qetl_settings_section2_callback() {

	echo __( 'If you want to exclude specific plugins from the dropdown, select them here', 'wordpress' );

}

/**
 *
 */
function quick_edit_template_link_options_page() {

	?>
	<form action='options.php' method='post'>
		<div class="wrap">
			<h1>Template Debugger</h1>

			<p>If you find this plugin useful or would like to see something added, please take a minute to
				<a href="https://wordpress.org/support/view/plugin-reviews/quick-edit-template-link" target="_blank">Rate &amp;
				                                                                                     Review</a> the
			   plugin.</p>

			<div style="width:60%;float:left;">
				<?php
				settings_fields( 'pluginPage' );
				do_settings_sections( 'pluginPage' );
				submit_button();
				?>
			</div>
			<div style="width:35%;float:right;">
				<h3>Advert</h3>

				<p>This advert is to help contribute to development costs.</p>
				<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- Quick Edit Template Link -->
				<ins class="adsbygoogle"
				     style="display:inline-block;width:300px;height:600px"
				     data-ad-client="ca-pub-4524739862142506"
				     data-ad-slot="7190258734"></ins>
				<script>
					(adsbygoogle = window.adsbygoogle || []).push({});
				</script>
			</div>
			<div class="clear:both;"></div>
		</div>
	</form>
	<?php

}

add_action( 'admin_bar_init', 'chubby_ninja_admin_bar_init' );
add_action( 'admin_menu', 'qetl_add_admin_menu' );
add_action( 'admin_init', 'qetl_settings_init' );
