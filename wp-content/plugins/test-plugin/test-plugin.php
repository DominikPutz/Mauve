<?php
/**
* @package Test-Plugin
*/
/*
Plugin Name: Test-Plugin
Plugin URI:
Description: Just a simple Plugin for practising
Version: 1.0
Author: Dominik Putz
Author URI:
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/

// ensure that only WP is using this plugin - if this file is called directly - abort!
defined ('ABSPATH') or die('Hey, you don\'t have access to this! What are you doing here?');

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/*
* PURPOSE : The code that runs during plugin activation
*/
function activate_test_plugin()
{
	Inc\Base\Activate::activate();
}
register_activation_hook(__FILE__, 'activate_test_plugin' );

/*
* PURPOSE : The code that runs during plugin deactivation
*/
function deactivate_test_plugin()
{
	Inc\Base\Deactivate::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_test_plugin' );

/*
* PURPOSE : Initialize all the core classes of the plugin
*  PARAMS :
*/
if ( class_exists( 'Inc\\Init' ) ) {
    Inc\Init::register_services();
}
