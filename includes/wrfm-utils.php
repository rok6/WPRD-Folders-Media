<?php

trait WRFM_Utils
{
	private static $object;

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
		yield 'params' => get_option(self::$plugin_name);

		yield 'files' => $this->get_files();

		yield 'page' => [
			'name' => self::$plugin_name,
			'group' => self::$setting_group,
		];

		yield 'upload_dirname' => $this->get_upload_dirname();
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
	private function get_upload_dirname()
	{
		return str_replace(['../', './'], '', get_option('upload_path'));
	}


	/**=====================================================
	 *	WRFM Fields
	 *=====================================================*/
	private function input_name( $prop, $after = '' )
	{
		$prop = (string) $prop;
		return esc_attr(self::$plugin_name . '['. $prop .']' . $after);
	}

	private function input_selecter( $prop )
	{
		$prop = (string) $prop;
		return esc_attr(self::$prefix.'-'.$prop);
	}
}
