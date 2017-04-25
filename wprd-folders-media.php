<?php
/*
Plugin Name: WPRD Folders Media
Plugin URI:
Description: メディアライブラリをフォルダごとに管理できるようにします。
Version: 0.1
Author: mzkr6
Author URI: http://example.com/
License: GPL2
*/

/*  Copyright 2017 mazkr6 (email : プラグイン作者のメールアドレス)

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

/**
 * WPRD_FoldersMedia
 *
 * PHP5.4-
 */

if( !defined('ABSPATH') ) {
	exit;
}
if( !is_admin() ) {
	return;
}

$WRFM = WPRD_FoldersMedia::get_object();

add_action( 'plugins_loaded', [ $WRFM, 'initialize' ] );
register_activation_hook( __FILE__, [ $WRFM, 'activate' ] );
register_deactivation_hook( __FILE__, [ $WRFM, 'deactivate' ]  );


/**
 * class
 * WPRD_FoldersMedia
 */
class WPRD_FoldersMedia
{
	private static $object;
	private static $version = 0.1;

	private static $prefix		= 'wrfm';
	private static $plugin_hook	= 'wprd-folders-media';
	private static $plugin_name = 'WPRD Folders Media';

	private $vars = [];

	private $upload_dir;
	private $hook;

	public function __construct()
	{
		//
	}


	/**
	 *
	 */
	public function initialize()
	{
		load_plugin_textdomain(self::$plugin_hook, false, dirname( plugin_basename(__FILE__ ) ) . '/languages' );

		define( 'WRFM_BASENAME', plugin_basename(__FILE__) ); // WPRD_Folders_Media/wprd-folders-media.php

		$this->pre_upload_functions();
		$this->upload_functions();
		$this->xmlrpc_call();
		$this->admin_menu();
	}


	/**
	 *
	 */
	private function pre_upload_functions()
	{
		add_filter('wp_handle_upload_prefilter', function( $f ) {
			echo 'wp_handle_upload_prefilter','::'; _d($f);
		});
	}

	/**
	 *
	 */
	private function upload_functions()
	{
		add_filter('wp_handle_upload', function( $f ) {
			echo 'wp_handle_upload','::'; _d($f);
		});
	}

	/**
	 *
	 */
	private function xmlrpc_call()
	{
		add_action('xmlrpc_call', function( $f ) {
			echo 'xmlrpc_call','::'; _d($f);
		});
	}

	/**
	 *
	 */
	private function register_settings()
	{
		register_setting(self::$prefix.'-settings', self::$plugin_hook, [&$this, 'valid_settings']);
	}

	/**
	 *
	 */
	private function admin_menu()
	{
		add_action('admin_menu', function() {

			$this->hook = add_submenu_page('upload.php',
				self::$plugin_name,
				self::$plugin_name,
				'administrator',
				self::$plugin_hook,
				[&$this, 'admin_page']
			);
			$this->admin_scripts();
		});
	}

	/**
	 *
	 */
	public function admin_page()
	{
		$this->vars['files'] = $this->get_files();
		$this->vars['post_types'] = $this->get_post_types();

		extract($this->vars);
		include('includes/settings.php');
	}


	/**
	 *
	 */
	public function admin_scripts()
	{
		add_action('admin_print_scripts-'.$this->hook, function() {
			wp_enqueue_media();
			wp_enqueue_script(self::$prefix.'-scripts',
				plugins_url('/assets/js/media-uploader.js', __FILE__),
				['jquery'],
				filemtime( dirname(__FILE__) . '/assets/js/media-uploader.js' ),
				false
			);
		});
	}


	/**=====================================================
	 *	WRFM Methods
	 *=====================================================*/
	/**
	 *
	 */
	private function get_files( array $args = [] )
	{
		$args = $args += [
			'post_type'		=> 'attachment',
			'numberposts'	=> -1,
			// 'post_mime_type' => 'image'
		];
		return get_posts($args);
	}


	/**
	 *
	 */
	private function get_post_types( array $args = [] )
	{
		$args = $args += [
			'public'	=> true,
			'_builtin'	=> false
		];

		$types = [];
		$_types = array_merge(['post' => 'post', 'page' => 'page'], get_post_types($args));

		foreach( $_types as $key => $post_type ) {
			$types[$key] = get_post_type_object($post_type)->label;
		}

		return $types;
	}




	/**=====================================================
	 *	Make a self
	 *=====================================================*/
	public static function get_object()
	{
		if( !isset( self::$object ) ) {
			$class = __CLASS__;
			self::$object = new $class();
		}

		return self::$object;
	}

	/**=====================================================
	 *	Before Settings, After Care
	 *=====================================================*/
	public function activate()
	{
		$this->default_settings();
	}

	public function deactivate()
	{
		$this->remove_settings();
	}

	private function default_settings()
	{
		_d('default_settings');
	}

	private function remove_settings()
	{
		_d('remove_settings');
	}

}


/**=====================================================
 *	utils
 *=====================================================*/
/**
 * dump ex
 *
 * @since 0.1
 * @param $str
 */
function _d( $str ){
	echo PHP_EOL,'<pre>';
	var_dump($str);
	echo '</pre>',PHP_EOL;
}
