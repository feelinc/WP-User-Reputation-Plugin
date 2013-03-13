<?php

/*
Plugin Name: User Reputation
Plugin URI: -
Description: -
Version: 1.0.0
Author: Sulaeman <me@sulaeman.com>
Author URI: http://www.sulaeman.com
License: A "Slug" license name e.g. GPL2
*/

include_once( __DIR__ . '/controllers/UserReputation.php' );

// Register the installation and uninstallation function
register_activation_hook( __FILE__, 'UserReputation::_install' );
register_deactivation_hook( __FILE__, 'UserReputation::_uninstall' );

// All the starting point of code start at bootstrap function
$userReputation = new UserReputation();
