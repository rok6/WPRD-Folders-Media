<?php

class WPRD_Folders_Media
{
	use WRFM_Utils,
		WRFM_Admin_Settings,
		WRFM_Admin_Fields,
		WRFM_Uploads;

	private static $plugin_version;
	private static $plugin_title;
	private static $plugin_name;
	private static $prefix;

	private static $setting_page_name;
	private static $setting_group;

	private static $plugin_menu_role;

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

}
