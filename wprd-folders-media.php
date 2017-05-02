<?php
/*
Plugin Name: WPRD Folders Media
Plugin URI:
Description: メディアライブラリをフォルダごとに管理できるようにします。
Version: 0.1
Author: mzkr6
Author URI: http://example.com/
License: GPL2

v PHP 5.6 -

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

require_once(__DIR__.'/includes/wrfm-utils.php');
require_once(__DIR__.'/includes/wrfm-admin.php');
require_once(__DIR__.'/includes/wrfm-uploads.php');
require_once(__DIR__.'/includes/wrfm.php');

$WRFM = WPRD_Folders_Media::get_object();

/**
 * Start Actions
 */
register_activation_hook( __FILE__, [ $WRFM, 'activate' ] );
register_deactivation_hook( __FILE__, [ $WRFM, 'deactivate' ]  );

add_action( 'plugins_loaded', [ $WRFM, 'initialize' ] );

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
