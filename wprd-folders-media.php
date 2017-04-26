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

	private static $plugin_name = 'WPRD Folders Media';
	private static $plugin_hook	= 'wprd-folders-media';
	private static $prefix		= 'wrfm';
	private static $admin_menu_hook;

	private static $setting_page_name = 'wprd-folders-media';
	private static $setting_group = 'wprd-folders-media-group';

	private static $plugin_menu_role = 'administrator';
	private static $upload_dir_base;
	private static $upload_dir_url;

	private $get_params;
	private $vars = [];
	private $options = [];

	public function __construct()
	{
		//
	}

	/**=====================================================
	 *	WRFM Setup
	 *=====================================================*/
	public function initialize()
	{
		load_plugin_textdomain(self::$plugin_hook, false, dirname( plugin_basename(__FILE__ ) ) . '/languages' );

		define( 'WRFM_BASENAME', plugin_basename(__FILE__) ); // wprd-folders-media/wprd-folders-media.php
		define( 'WRFM_UPLOAD_DIR', '' );

		$path = wp_upload_dir();
		$path['basedir'] = str_replace('\\', '/', $path['basedir']);
		$path['basedir'] = preg_replace('/\/[^\/]+\/[\.]{2}/u', '', $path['basedir']);
		$path['basedir'] = str_replace('/', '\\', $path['basedir']);

		self::$upload_dir_base = $path['basedir'];
		self::$upload_dir_url = $path['baseurl'];

		$this->pre_upload_functions();	// アップロード前の処理
		$this->uploaded_functions();	// アップロード後の処理
		$this->admin_menu();
	}


	/**
	 *
	 */
	private function pre_upload_functions()
	{
		add_filter('wp_handle_upload_prefilter', function( $file ) {
			add_filter('upload_dir', [&$this, 'wprd_upload_dir']);
			return $file;
		});
	}


	/**
	 *
	 */
	private function uploaded_functions()
	{
		add_filter('wp_handle_upload', function( $file ) {
			remove_filter('upload_dir', [&$this, 'wprd_upload_dir']);
			return $file;
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

			self::$admin_menu_hook = add_submenu_page('upload.php',
				self::$plugin_name,
				self::$plugin_name,
				self::$plugin_menu_role,
				self::$setting_page_name,
				[&$this, 'admin_page']
			);
			$this->admin_scripts();
		});

		add_action('admin_init', [&$this, 'register_setting_fields']);

		$this->get_params = get_option(self::$plugin_hook);
	}

	/**
	 *
	 */
	public function admin_page()
	{
		$this->vars['params'] = $this->get_params;
		$this->vars['files'] = $this->get_files();
		$this->vars['post_types'] = $this->get_post_types();
		$this->vars['plugin_name'] = self::$plugin_hook;
		$this->vars['setting_group'] = self::$setting_group;
		$this->vars['page_name'] = self::$setting_page_name;

		extract($this->vars);
		include('includes/settings.php');
	}


	/**
	 *
	 */
	public function admin_scripts()
	{
		add_action('admin_print_scripts-'.self::$admin_menu_hook, function() {
			wp_enqueue_media();
			wp_enqueue_style(self::$prefix.'-styles',
				plugins_url('/assets/css/style.css', __FILE__),
				false,
				filemtime( dirname(__FILE__) . '/assets/css/style.css' )
			);
			wp_enqueue_script(self::$prefix.'-scripts',
				plugins_url('/assets/js/settings_scripts.js', __FILE__),
				['jquery'],
				filemtime( dirname(__FILE__) . '/assets/js/settings_scripts.js' ),
				true
			);
		});
	}


	/**=====================================================
	 *	WRFM Methods
	 *=====================================================*/
	/**
	 *
	 */
	private function wprd_upload_dir( $param )
	{
		$this->parara = $param;
		return $param;
	}

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

	/**
	 * @since 0.1
	 */
	private function add_settings_field( $id, $title, array $args = [] )
	{
		$_id = ($id !== 'uploads_use_yearmonth_folders') ? self::$plugin_hook : $id;

		add_settings_field($_id, __($title),
			[$this, 'build_setting_fields'],
			self::$setting_page_name,
			'default',
			$args += ['id' => $id, 'title' => $title, 'label_for' => $id]
		);
	}

	public function register_setting_fields() {

		add_settings_section(
			'default',
			null,
			null,
			self::$setting_page_name
		);

		$this->add_settings_field( 'uploads_use_yearmonth_folders', 'フォルダを年月ベースにする' );
		$this->add_settings_field( 'settings', '基本設定' );

		register_setting(self::$setting_group, 'uploads_use_yearmonth_folders');
		register_setting(self::$setting_group, self::$plugin_hook, [&$this, 'sanitize']);
	}

	public function build_setting_fields( $args )
	{
		$id = esc_attr($args['id']);

		switch( $id )
		{
			case 'settings':
				$this->options = get_option(self::$plugin_hook);

				$value = $this->get_param('default_dir');

				echo '<input name="'.self::$plugin_hook.'['.$id.']" type="text" id="'.$id.'" value="'. esc_attr($value) .'" class="regular-text" />',
					 '<p class="description">',
					 	'下記、投稿タイプごとの指定がない場合のフォルダ作成の基本設定です。<br />',
					 	'空欄の場合はフォルダを作成しません。「/」で階層を分けることができ、空白があった場合は自動的に削除され詰められます。',
					 '</p>';



				echo '<div class="wrfm-container">',
						'<ul id="wrfm-filters">';

						if( !empty($this->options['dirs']) ) {

		 					foreach( $this->options['dirs'] as $dir_set ) {
		 						echo '<li>',
										'<select name="'. self::$plugin_hook .'[dirs][]">',
											'<option></option>',
										'</select>',
									 '</li>';
		 					}

		 				}

				echo	'</ul>',
						'<div id="wrfm-new_filter">',
							'<p class="wrfm-new_filter-info">'. __('投稿タイプの毎のフォルダ条件を追加', self::$plugin_hook) .'</p>',
						'</div>',
					 '</div>';
				break;

			case 'uploads_use_yearmonth_folders':
				echo '<input name="'.$id.'" type="hidden" value="0" />',
			 		 '<label for="'.$id.'"><input name="'.$id.'" type="checkbox" id="'.$id.'" value="1" '. checked(1, get_option($id), false) .' /></label>';
				break;
		}
	}

	public function get_param($id)
	{
		return isset($this->options[$id]) ? (string) $this->options[$id] : '';
	}

	public function sanitize( $input )
	{
		_d($input);
		add_settings_error( self::$setting_page_name, self::$plugin_hook, 'メッセージを入力して下さい。' );

		return $input;
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
		$defaults = [
			'dirs' => [],
			'default_dir' => '',
		];

		$before = get_option(self::$plugin_hook);

		if( is_array($before) ) {
			$defaults = $before += $defaults;
		}

		update_option(self::$plugin_hook, $defaults);
	}

	private function remove_settings()
	{
		delete_option(self::$plugin_hook);
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
function _d( $str ) {
	echo PHP_EOL,'<pre>';
	var_dump($str);
	echo '</pre>',PHP_EOL;
}

function wrfm_js_encode($data) {
	return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
