<?php
/*
Plugin Name: WPRD Folders Media
Plugin URI:
Description: メディアライブラリをフォルダごとに管理できるようにします。
Version: 0.1
Author: mzkr6
Author URI: http://example.com/
License: GPL2

v PHP 5.4 -

Copyright 2017 mazkr6 (email : プラグイン作者のメールアドレス)

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

	// テキストドメインや option の設定名
	private static $plugin_hook	= 'wprd-folders-media';
	// 管理ページのスラッグ
	private static $setting_page_name = 'wprd-folders-media';
	// 設定フィールドグループ名
	private static $setting_group = 'wprd-folders-media-group';

	private static $prefix = 'wrfm';
	private static $admin_menu_hook;


	private static $safe_list = [
		'%post_type%',
		'%file_type%',
		'%file_ext%',
		'%author%',
		'%author_id%',
		'%post_id%',
	];

	private static $plugin_menu_role = 'administrator';
	private static $upload_dir_base;
	private static $upload_dir_url;
	private static $upload_dirname;

	private $post_types;
	private $vars = [];
	private $options = [];

	// ファイルデータの一時保存用
	private static $_data = [];

	/**=====================================================
	 *	WRFM Setup
	 *=====================================================*/
	public function initialize()
	{
		load_plugin_textdomain(self::$plugin_hook, false, dirname( plugin_basename(__FILE__ ) ) . '/languages' );

		$path = wp_upload_dir();
		$path['basedir'] = str_replace('\\', '/', $path['basedir']);
		$path['basedir'] = preg_replace('/\/[^\/]+\/[\.]{2}/u', '', $path['basedir']);
		$path['basedir'] = str_replace('/', '\\', $path['basedir']);

		self::$upload_dir_base = $path['basedir'];
		self::$upload_dir_url = $path['baseurl'];
		self::$upload_dirname = $this->get_upload_dirname();

		// $this->pre_upload_functions();	// アップロード前の処理
		// $this->uploaded_functions();	// アップロード後の処理

		add_filter('wp_handle_upload_prefilter', [$this, 'pre_upload_functions']);
		add_filter('wp_handle_upload', [$this, 'upload_functions']);

		$this->admin_menu();

	}


	/**
	 *
	 */
	public function pre_upload_functions( $_file )
	{
		$file = $_file['name'];
		$mime = wp_check_filetype( $file );
		self::$_data['file_type'] = $mime['type'];
		self::$_data['file_ext'] = $mime['ext'];
		add_filter('upload_dir', [$this, 'wprd_upload_dir']);
		return $_file;
	}


	/**
	 *
	 */
	public function upload_functions( $up_filedata )
	{
		remove_filter('upload_dir', [$this, 'wprd_upload_dir']);
		return $up_filedata;
	}


	/**
	 *
	 */
	public function wprd_upload_dir( $path )
	{
		_d('wprd_upload_dir----------');

		$use_yearmonth = (int) get_option('uploads_use_yearmonth_folders');
		if( !empty($path['error']) || $use_yearmonth ) {
			return $path;
		}
		$path['basedir'] = $path['path'] = self::$upload_dir_base;
		$path['baseurl'] = $path['url'] = self::$upload_dir_url;
		$path = $this->wprd_dir_paths($path);

		_dump($path);
		// wp-admin/includes/file.php 372
		// _dump('-------------------------');
		// _dump($uploads);
		// _dump($file);
		// exit;

		return $path;
	}


	public function wprd_dir_paths( $path )
	{
		global $post, $post_id, $current_user;

		self::$_data['post_type'] = get_post_type($post_id);
		self::$_data['user_id'] = $current_user->data->ID;
		self::$_data['user_login'] = $current_user->data->user_login;

		$subdir = '/';
		$default = $this->options['default_dir'];
		// test
		// $default = '%post_type%/%file_type%';
		$subdir .= $default;

		foreach( $this->tags() as $key => $value ) {
			$subdir = str_replace($key, $value, $subdir);
		}

		$path['subdir'] = $subdir;
		$path['path'] .= $subdir;
		$path['url'] .= $subdir;


		$filename = dirname(__FILE__) . '/log.txt';

		// $_REQUEST['post_id']

		// ファイルに書き込む
		file_put_contents( $filename, json_encode($path, JSON_PRETTY_PRINT) );
		// ファイルを出力する
		// readfile($filename);

		return $path;
	}

	private function tags()
	{
		global $post_id, $current_user;

		/**
		 * Post_type
		 */
		$post_id = !empty($post_id) ? $post_id
				 : !empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : '';
		$post_type = get_post_type($post_id);
		yield '%post_type%' => $post_type;

		/**
		 * File_type
		 */
		$mime = wp_check_filetype( $_REQUEST['name'] );
		yield '%file_ext%' => $mime['ext'];

		/**
		 * File_ext
		 */
		$mime['type'] = strpos($mime['type'], 'image') !== false ? 'image'
						: $mime['type'];
		yield '%file_type%' => $mime['type'];

		/**
		 * Author_login_name
		 */
		yield '%author%' => $current_user->data->user_login;

		/**
		 * Author_ID
		 */
		yield '%author_id%' => $current_user->data->ID;
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

		$this->options = get_option(self::$plugin_hook);
	}

	/**
	 *
	 */
	public function admin_page()
	{
		$this->vars['params'] = $this->options;
		$this->vars['files'] = $this->get_files();
		$this->vars['post_types'] = $this->post_types = $this->get_post_types();
		$this->vars['plugin_name'] = self::$plugin_hook;
		$this->vars['setting_group'] = self::$setting_group;
		$this->vars['page_name'] = self::$setting_page_name;
		$this->vars['upload_base_dir'] = self::$upload_dirname;

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
	private function get_upload_dirname()
	{
		return str_replace(['../', './'], '', get_option('upload_path'));
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
				$prop = 'default_dir';
				$value = $this->get_param($prop);

				echo '<span class="show_directory"><code>'. self::$upload_dirname .'/</code></span><input name="'.self::$plugin_hook.'['. $prop .']" type="text" id="'. self::$prefix.'-'.$prop .'" value="'. esc_attr($value) .'" class="regular-text" />',
					 '<p class="description">',
					 	'下記、投稿タイプごとの指定がない場合のフォルダ作成の基本設定です。<br />',
					 	'空欄の場合はフォルダを作成しません。「/」で階層を分けることができ、空白があった場合は自動的に削除され詰められます。',
					 '</p>';



				echo '<div class="'. self::$prefix .'-container">',
						'<ul id="'. self::$prefix .'-filters">';

						if( !empty($this->options['dirs']) ) {

								foreach( $this->options['dirs'] as $post_type => $dir_set ) {

									$_namebase = self::$plugin_hook . '[dirs]['.$post_type.']';
									$_type	= !empty($dir_set['post_type']) ? $dir_set['post_type'] : '';
									$_id	= !empty($dir_set['id']) ? $dir_set['id'] : '';
									$_filter = !empty($dir_set['filter']) ? $dir_set['filter'] : '';

		 						echo '<li id="'. esc_attr($_id) .'">',
										'<div class="wrfm-filter-header">',
											'<select name="'. $_namebase .'">';
											foreach( $this->post_types as $type => $label ) {
												echo '<option value="'. esc_attr($type) .'" '. selected($type, $_type, false) .'>'. esc_html($label) .'</option>';
											}
								echo		'</select>',
											'<span class="remove">この項目を削除</span>',
										'</div>',
										'<div class="wrfm-filter-input">',
											'<label><span class="show_directory"></span><input name="" type="text" data-name="filter" value="'. esc_attr($_filter) .'" /></label>',
										'</div>',
										'<div class="wrfm-filter-hidden">',
											'<input name="" type="hidden" data-name="id" value="'. esc_attr($_id) .'" />',
										'</div>',
									 '</li>';
									 ;
								}

							}

				echo	'</ul>',
						'<div id="'. self::$prefix .'-new_filter">',
							'<p class="'. self::$prefix .'-new_filter-info">'. __('投稿タイプの毎のフォルダ条件を追加', self::$plugin_hook) .'</p>',
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
		/**
		 * 必要なデータのみ抜き出す
		 */
		$new_input = [];
		$new_input['default_dir'] = $input['default_dir'];
		$new_input['dirs'] = isset($input['dirs']) ? $input['dirs'] : [];

		//基本設定
		if( !$this->valid_input($new_input['default_dir']) ) {
			$new_input = get_option(self::$plugin_hook);
		}


		foreach( $new_input['dirs'] as $post_type => $data ) {

			// 存在しない投稿タイプがあった場合のエラー処理
			if( !array_key_exists($post_type, $this->get_post_types()) ) {
				add_settings_error( self::$plugin_hook, 'invalid_type', '不正なデータです' );
				$new_input = get_option(self::$plugin_hook);
				break;
			}

			// filter に許可されていない文字列があった場合のエラー処理
			if( !$this->valid_input($data['filter']) ) {
				$new_input = get_option(self::$plugin_hook);
				break;
			}

		}
		return $new_input;
	}

	private function valid_input( $input_value )
	{
		$return = true;

		if( empty($input_value) ) {
			$return = false;
		}
		else {

			$tags = explode( '/', preg_replace('/[\/]{2,}/', '', $input_value) );

			foreach( $tags as $tag ) {
				if( !in_array($tag, self::$safe_list, true) ) {
					add_settings_error( self::$plugin_hook, 'unavailable_characters', '利用できない文字列が含まれています。' );
					$return = false;
					break;
				}
			}
		}

		return $return;
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
			'default_dir' => '',
			'dirs' => [],
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
