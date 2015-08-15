<?php
/**
 * Plugin Name: Template Debugger
 * Plugin URI: http://www.chubbyninja.co.uk
 * Description: A template debugger that helps you identify what template files are being used on the page you're viewing
 * Version: 2.1.0
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

	add_menu_page( 'Template Debugger', 'Template Debugger', 'manage_options', 'quick_edit_template_link', 'quick_edit_template_link_options_page' );
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

	add_settings_section( 'qetl_pluginPage_child', __( '', 'wordpress' ), 'qetl_pluginPage_child_callback', 'childTheme' );
	add_settings_field( 'qetl_select_theme', __( 'Select Parent Theme', 'wordpress' ), 'qetl_select_render', 'childTheme', 'qetl_pluginPage_child' );
	add_settings_field( 'qetl_theme_name1', __( 'Child Theme Name', 'wordpress' ), 'qetl_text_child_name_render', 'childTheme', 'qetl_pluginPage_child' );
	add_settings_field( 'qetl_theme_name2', __( 'Author Name', 'wordpress' ), 'qetl_text_child_author_render', 'childTheme', 'qetl_pluginPage_child' );
	add_settings_field( 'qetl_theme_name3', __( 'Author URI', 'wordpress' ), 'qetl_text_child_author_uri_render', 'childTheme', 'qetl_pluginPage_child' );
	add_settings_field( 'qetl_theme_name4', __( 'Child Theme Version', 'wordpress' ), 'qetl_text_child_version_render', 'childTheme', 'qetl_pluginPage_child' );

	qetl_check_form_action();
}

$qetl_error = $qetl_success = false;
function qetl_check_form_action()
{
    global $qetl_error,$qetl_success;
	if( !isset($_POST['action']) ){
		return;
	}
	if( $_POST['action'] != 'qetl_generate_child'){
		return;
	}

	// lets create the child theme
	$parent = $_POST['qetl_parent_theme'];
	$name = $_POST['qetl_child_name'];
	$slug = sanitize_title( $name );
	$author = $_POST['qetl_child_author'];
	$author_uri = $_POST['qetl_child_author_uri'];
	$version = $_POST['qetl_child_version'];

	if(
	    empty( $parent ) ||
	    empty( $name ) ||
	    empty( $author ) ||
	    empty( $version )
	    ) {
            $qetl_error = new WP_Error( 'broke', __( "The parent theme, name, author and version must not be blank", "my_textdomain" ) );
            return;
	    }

	$ph = array(
        '$name',
        '$uri',
        '$author_uri',
        '$author',
        '$parent',
        '$version'
	);
	$live = array(
        $name,
        '',
        $author_uri,
        $author,
        $parent,
        $version
	);

	$root = get_theme_root();
	$path = $root . '/' . $slug;


	if( is_dir( $path ) )
	{
	    $path .= '_' .wp_generate_password(5, false);
	}

	$ok = wp_mkdir_p( $path );
	if( !$ok )
	{
	    $qetl_error = new WP_Error( 'broke', __( "I could not create your theme directory, please make sure " . $root . " is writable", "my_textdomain" ) );
		return;
	}


    // create default files
	$default_functions = file_get_contents(__DIR__ . '/admin/functions.php');
	$functions = str_replace( $ph, $live, $default_functions );
	$fp = fopen( $path . '/functions.php', 'w');
	fwrite($fp, $functions);
	fclose($fp);

	$default_css = file_get_contents(__DIR__ . '/admin/style.css');
	$css = str_replace( $ph, $live, $default_css );
	$fp = fopen( $path . '/style.css', 'w');
	fwrite($fp, $css);
	fclose($fp);

	$qetl_success = true;

}

function qetl_pluginPage_child_callback() {}

/**
 *
 */
function qetl_select_render() {
	$theme_list = wp_get_themes();
	?>
	<select name="qetl_parent_theme" id="">
		<option value="">Select Theme</option>
		<?php
		foreach( $theme_list as $theme_slug=>$theme__ )
		{
			$theme = wp_get_theme( $theme_slug );
			if( $theme->get('Template') ) { continue; }
			?>
			<option value="<?=$theme_slug?>" <?=((isset($_POST['qetl_parent_theme'])) ? ' selected=selected ' : NULL)?> ><?=$theme->get( 'Name' )?></option>
			<?php
		}
		?>
	</select>
	<?php
}

function qetl_text_child_name_render() {
	?>
	<input type='text' name='qetl_child_name' value="<?=((isset($_POST['qetl_child_name'])) ? $_POST['qetl_child_name'] : NULL)?>">
	<?php
}

function qetl_text_child_author_render() {

	$current_user = wp_get_current_user();
	?>
	<input type='text' name='qetl_child_author' value="<?=((isset($_POST['qetl_child_name'])) ? $_POST['qetl_child_author'] : $current_user->display_name)?>">
	<?php
}

function qetl_text_child_author_uri_render() {
	?>
	<input type='text' name='qetl_child_author_uri' value="<?=((isset($_POST['qetl_child_name'])) ? $_POST['qetl_child_author_uri'] : NULL)?>">
	<?php
}

function qetl_text_child_version_render() {
	?>
	<input type='text' name='qetl_child_version' value="<?=((isset($_POST['qetl_child_name'])) ? $_POST['qetl_child_version'] : '1.0.0')?>">
	<?php
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

	$active_tab = 'general';

	if( isset($_GET['tab'] ) )
	{
		$active_tab = $_GET['tab'];
	}
	?>
		<div class="wrap">
			<h1>Template Debugger</h1>

			<p>If you find this plugin useful or would like to see something added, please take a minute to
				<a href="https://wordpress.org/support/view/plugin-reviews/quick-edit-template-link" target="_blank">Rate &amp;
				                                                                                     Review</a> the
			   plugin.</p>

			<div style="width:60%;float:left;">

				<h2 class="nav-tab-wrapper">
					<a class="nav-tab <?=(($active_tab == 'general') ? 'nav-tab-active' : NULL)?>" href="admin.php?page=quick_edit_template_link"><?php esc_attr_e('General'); ?></a>
					<a class="nav-tab <?=(($active_tab == 'child_theme') ? 'nav-tab-active' : NULL)?>" href="admin.php?page=quick_edit_template_link&tab=child_theme"><?php esc_attr_e('Create a Child Theme'); ?></a>
				</h2>

				<?php
				switch($active_tab)
				{
					case 'general':
					?>
					<form action='options.php' method='post'>
					<?php
						settings_fields( 'pluginPage' );
						do_settings_sections( 'pluginPage' );
						submit_button();
						?>
					</form>
						<?php
						break;

					case 'child_theme':
						doThemeCreateForm();
						break;

					default:
						settings_fields( 'pluginPage' );
						do_settings_sections( 'pluginPage' );
						submit_button();
				}

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


function doThemeCreateForm()
{
	?>
	<p>When you download a theme and want to modify it, you should always do so in a child theme, this ensures your changes are not lost when the theme is updated by the author.</p>

	<p>Using the form below, you can easily create the child theme</p>

    <?php
    global $qetl_error, $qetl_success;
    if( is_wp_error( $qetl_error ) ) {
        ?>
        <div id="" class="error">
            <p><?=$qetl_error->get_error_message()?></p>
        </div>
        <div style="border-left:solid 4px #f90000; background-color:#fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
            <p style="margin: 0;padding: 13px 17px;"><?=$qetl_error->get_error_message()?></p>
        </div>
        <?php
    }

    if( $qetl_success )
    {
    ?>
        <div id="" class="updated">
            <p>Your Child theme has been created, head over to <a href="<?=admin_url('themes.php')?>">Themes</a> to activate it</p>
        </div>
        <div id="" style="border-left:solid 4px #7ad03a; background-color:#fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
            <p style="margin: 0;padding: 13px 17px;">Your Child theme has been created, head over to <a href="<?=admin_url('themes.php')?>">Themes</a> to activate it</p>
        </div>
        <p style="font-weight: bold;">If this has made your life easier, please spare me a minute to <a href="https://wordpress.org/support/view/plugin-reviews/quick-edit-template-link" target="_blank">Rate &amp;
				                                                                                     Review</a> the
			   plugin.</p>
    <?php
    } else {
    ?>
        <form action="" method="post">
        <input type="hidden" name="action" value="qetl_generate_child">
        <?php
            do_settings_sections( 'childTheme' );
            submit_button('Create Child Theme');
        ?>
        </form>
        <?php
	}
}


add_action( 'admin_bar_init', 'chubby_ninja_admin_bar_init' );
add_action( 'admin_menu', 'qetl_add_admin_menu' );
add_action( 'admin_init', 'qetl_settings_init' );
