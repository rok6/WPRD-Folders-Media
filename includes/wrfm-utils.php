<?php

trait WRFM_Utils
{
	private static $object;

	private static $allowed_post_types;

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

	/**
	 *
	 */
	private function get_vars()
	{
		yield 'params' => self::$wrfm_options;

		yield 'files' => $this->get_files();

		yield 'page' => [
			'name' => self::$plugin_name,
			'group' => self::$setting_group,
		];

		yield 'post_types' => self::$allowed_post_types;

		yield 'upload_dirname' => self::$upload_dirname;
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
	private function get_upload_dir_path()
	{
		$path = wp_upload_dir();

		return $path;
	}


	/**
	 *
	 */
	private function get_upload_dirname()
	{
		return str_replace( ['../', './'], '', ( $base_dir = get_option('upload_path') !== '' ? $base_dir : 'wp-content/uploads' ) );
	}


	/**=====================================================
	 *	WRFM Fields
	 *=====================================================*/
	private function input_name( $prop, $after = '' )
	{
		$prop = (string) $prop;
		return esc_attr(self::$plugin_name . '['. $prop .']' . $after);
	}

	private function selecter( $prop )
	{
		$prop = (string) $prop;
		return esc_attr(self::$prefix.'-'.$prop);
	}
}
