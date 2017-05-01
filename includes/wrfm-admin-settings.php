<?php

trait WRFM_Admin_Settings
{
	private static $plugin_name;
	private static $plugin_menu_role;
	private static $prefix;

	private static $admin_menu_hook;

	/**
	 * 管理メニュー項目の追加
	 */
	public function register_admin_menu()
	{
		self::$admin_menu_hook = add_submenu_page('upload.php',
			self::$plugin_name,
			self::$plugin_name,
			self::$plugin_menu_role,
			self::$plugin_name,
			[$this, 'admin_page']
		);
		// CSS, JS Load
		add_action('admin_print_scripts-'.self::$admin_menu_hook, [$this, 'add_admin_scripts']);
	}

	/**
	 *
	 */
	public function admin_page()
	{
		// $this->vars['params'] = $this->options;
		// $this->vars['files'] = $this->get_files();
		// $this->vars['post_types'] = $this->post_types = $this->get_post_types();
		// $this->vars['plugin_name'] = self::$plugin_hook;
		// $this->vars['setting_group'] = self::$setting_group;
		// $this->vars['page_name'] = self::$setting_page_name;
		// $this->vars['upload_base_dir'] = self::$upload_dirname;

		foreach( $this->get_vars() as $k => $v ) {
			$$k = $v;
		}

		include('settings.php');
	}

	/**
	 *
	 */
	public function add_admin_scripts()
	{
		wp_enqueue_media();

		wp_enqueue_style(self::$prefix.'-styles',
			plugins_url('/assets/css/style.css', __FILE__),
			false,
			filemtime( dirname(__FILE__) . '/../assets/css/style.css' )
		);

		wp_enqueue_script(self::$prefix.'-scripts',
			plugins_url('/assets/js/settings_scripts.js', __FILE__),
			['jquery'],
			filemtime( dirname(__FILE__) . '/../assets/js/settings_scripts.js' ),
			true
		);
	}
}
