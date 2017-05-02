<?php

class WPRD_Folders_Media
{
	use WRFM_Utils,
		WRFM_Admin,
		WRFM_Uploads;

	private static $plugin_version;
	private static $plugin_title;
	private static $plugin_name;
	private static $prefix;

	private static $setting_page_name;
	private static $setting_group;

	private static $plugin_menu_role;

	private static $upload_dir;
	private static $upload_dirname;

	private static $wrfm_options;

	/**=====================================================
	 *	WRFM Setup
	 *=====================================================*/
	public function initialize()
	{
		self::$plugin_version = 0.1;

		self::$plugin_title	 = 'WPRD Folders Media';
		self::$plugin_name	 = 'wprd-folders-media';
		self::$prefix		 = 'wrfm';

		self::$setting_group = 'wprd-folders-media-group';

		self::$plugin_menu_role = 'administrator';

		self::$upload_dir = $this->get_upload_dir_path();
		self::$upload_dirname = $this->get_upload_dirname();
		self::$wrfm_options = get_option(self::$plugin_name);


		load_plugin_textdomain(self::$plugin_name, false, dirname( plugin_basename(__FILE__ ) ) . '/languages' );

		// add_filter('wp_handle_upload_prefilter', [$this, 'pre_upload_functions']);
		// add_filter('wp_handle_upload', [$this, 'upload_functions']);

		add_action('admin_menu', [$this, 'register_admin_menu']);
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
		$before = get_option(self::$plugin_name);

		if( is_array($before) ) {
			$defaults = $before += $defaults;
		}

		update_option(self::$plugin_name, $defaults);
	}

	private function remove_settings()
	{
		delete_option(self::$plugin_name);
	}

	/**=====================================================
	 *	Replace Tags
	 *=====================================================*/
	private static $safe_tags = [
		'%post_type%'	=> '',
		'%file_ext%'	=> '',
		'%file_type%'	=> '',
		'%author%'		=> '',
		'%author_id%'	=> '',
	];

	private function tags()
	{
		global $post_id, $current_user;

		$get_data = null;

		/**
		 * Post_type
		 */
		$post_id = !empty($post_id) ? $post_id
				 : !empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : '';
		$get_data = get_post_type($post_id);
		yield '%post_type%' => $get_data;

		/**
		 * File_type
		 */
		$mime = wp_check_filetype( $_REQUEST['name'] );
		$get_data = $mime['ext'];
		yield '%file_ext%' => $get_data;

		/**
		 * File_ext
		 */
		$get_data = strpos($mime['type'], 'image') !== false ? 'image'
				  : strpos($mime['type'], 'pdf') !== false ? 'pdf'
				  : strpos($mime['type'], 'text') !== false ? 'txt'
				  : preg_match('/mp3|wav|wma|ogg|aif|mid|/', $mime['ext']) !== false ? 'sound'
				  : preg_match('/mp4|m4a|mov|mpeg|mpg|wmv|avi|webm|ogm/', $mime['ext']) !== false ? 'video'
				  : $mime['ext'];
		yield '%file_type%' => $get_data;

		/**
		 * Author_login_name
		 */
		$get_data = $current_user->data->user_login;
		yield '%author%' => $get_data;

		/**
		 * Author_ID
		 */
		$get_data = $current_user->data->ID;
		yield '%author_id%' => $get_data;
	}

}
