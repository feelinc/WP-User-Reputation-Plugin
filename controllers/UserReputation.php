<?php

include_once( dirname(__DIR__) . '/libraries/Base.php' );
include_once( dirname(__DIR__) . '/models/Reputation.php' );
include_once( dirname(__DIR__) . '/models/User.php' );

use \UserReputation\Lib\Base;
use \UserReputation\Exception\SysException;
use \UserReputation\Exception\DBException;
use \UserReputation\Model\Reputation as ReputationModel;
use \UserReputation\Model\User as UserModel;

class UserReputation extends Base
{
	private $_hook = null;
	private static $_allowed_action_types = array('post', 'comment');

	public function __construct()
	{
		parent::__construct();

		include_once( $this->getPath('controller') . 'Hook.php' );

		$this->_hook = new \UserReputation\Ctrl\Hook();

		include_once( $this->getPath('config') . 'hooks.php' );
	}

	/*
	* Get reputation data
	*
	* param array (id, user_id):
	*	id: the data reputation ID in table (optional)
	*	user_id: user ID who's having the reputation data (optional)
	*
	* If not param assigned, automatically get current logged in user ID
	* and assign to user_id param.
	*
	* return object
	*/
	public static function get($args = array())
	{
		$args = array_merge(array(
			'id' => 0,
			'user_id' => 0
		), $args);

		if (empty($args['id']) && empty($args['user_id']))
		{
			if (false === ($args['user_id'] = self::getCurrentUserId()))
			{
				return false;
			}
		}

		try {
			$reputation = ReputationModel::get($args);
		} catch (DBException $e) {
			error_log($e->getMessage());
			return false;
		}

		try {
			$total_badges = ReputationModel::getTotalPerUserBadgeType($reputation->user_id);
		} catch (DBException $e) {
			error_log($e->getMessage());
			return false;
		}

		if ($reputation)
		{
			$reputation->total_per_badge_types = array();

			if (!empty($total_badges))
			{
				$base = Base::app();
				$badge_types = $base->getConfig('badge_types');
				unset($base);

				foreach($total_badges as $badge)
				{
					$reputation->total_per_badge_types[$badge->type] = new stdClass();
					$reputation->total_per_badge_types[$badge->type]->title = $badge_types[$badge->type];
					$reputation->total_per_badge_types[$badge->type]->total = $badge->total;
				}
			}
		}

		return $reputation;
	}

	/*
	* Get reputation history data
	*
	* param integer user_id (optional)     : automatically get current logged in user ID if not defined
	* param integer limit (optional)       : number items per page, don't use it at any view, only for specific usage (ajax)
	* param integer page (optional)        : current page number, don't use it at any view, only for specific usage (ajax)
	* param integer total_rows (optional)  : total rows returned from query, don't use it at any view, only for specific usage (ajax)
	* param integer number_rows (optional) : number rows returned from query, don't use it at any view, only for specific usage (ajax)
	*
	* return array
	*/
	public static function getHistory(&$user_id, &$limit = 0, $page = 1, &$total_rows = 0, &$number_rows = 0)
	{
		$histories = array();

		if (empty($user_id))
		{
			if (false === ($user_id = self::getCurrentUserId()))
			{
				return $histories;
			}
		}

		$base = Base::app();
		if (empty($limit))
			$limit = $base->getOption('num_items_per_page');
		unset($base);

		try {
			$histories = ReputationModel::getHistory(array(
				'receiver_user_id' => (int) $user_id,
				'page'             => $page,
				'limit'            => $limit,
				'order_by'         => 'created_at'
			), $total_rows, $number_rows);
		} catch (DBException $e) {
			error_log($e->getMessage());
			return $histories;
		}

		if (!empty($histories))
		{
			foreach($histories as $index => $history)
			{
				$histories[$index]->point = ( (int)$history->point == $history->point && (int)$history->point > 0 ) ? '+'.$history->point : $history->point;
				$histories[$index]->action_title = '';
				$histories[$index]->action_url = '';

				$action_data = null;

				try {
					$action_data = ReputationModel::getActionData($history->action_type, $history->action_id);
				} catch (DBException $e) {}
				
				if ($action_data !== null)
				{
					$histories[$index]->action_title = $action_data['title'];
					$histories[$index]->action_url = $action_data['url'];
				}
				
				unset($action_data);
			}
		}

		return $histories;
	}

	/*
	* Show reputation history view
	* also used by ajax
	*
	* param integer user_id (optional)     : automatically get current logged in user ID if not defined
	* param integer limit (optional)       : number items per page, don't use it at any view, only for specific usage (ajax)
	* param integer page (optional)        : current page number, don't use it at any view, only for specific usage (ajax)
	*
	* return void, ouput html
	*/
	public static function getHistoryView($user_id = 0, $limit = 0, $page = 1)
	{
		$page = (isset($_GET['page'])) ? $_GET['page'] : $page;
		
		$histories = self::getHistory($user_id, $limit, $page, $total_rows, $number_rows);

		$base = Base::app();

		$plugin_view_path = $base->getPath('view');
		$history_template = $base->getConfig('reputation_history_template');

		if (!class_exists('Zebra_Pagination'))
			include_once($base->getPath('library').'Zebra_Pagination.php');

		unset($base);

		$pagination = new Zebra_Pagination();
		$pagination->records($total_rows);
		$pagination->records_per_page($limit);
		$pagination->set_page($page);

		$theme_template_path = rtrim(get_template_directory(), '/').'/'.$history_template.'.php';
		if (file_exists($theme_template_path))
		{
			include_once($theme_template_path);
		}
		else
		{
			include_once($plugin_view_path.$history_template.'.php');
		}
	}

	/*
	* Assign badge to a user
	*
	* param integer badge_id           : badge ID from database
	* param integer user_id (optional) : automatically get current logged in user ID if not defined
	*
	* return boolean|integer, false on failure, user_badge_id on success
	*
	* see apache / php log message for any error
	*/
	public static function setUserBadge($badge_id, $user_id = 0)
	{
		if (empty($user_id))
		{
			if (false === ($user_id = self::getCurrentUserId()))
			{
				return false;
			}
		}

		$user_badge_id = 0;

		try {
			$user_badge_id = ReputationModel::setUserBadge(array(
				'user_id'  => $user_id,
				'badge_id' => $badge_id
			));
		} catch (DBException $e) {
			error_log($e->getMessage());
			return false;
		}

		return $user_badge_id;
	}

	/*
	* Remove badge from a user by user badge ID
	*
	* param integer id : user badge ID from database
	*
	* return boolean
	*
	* see apache / php log message for any error
	*/
	public static function removeUserBadgeById($id)
	{
		if (empty($id))
			return false;

		try {
			ReputationModel::removeUserBadgeById($id);
		} catch (DBException $e) {
			error_log($e->getMessage());
			return false;
		}

		return true;
	}

	/*
	* Remove badge from a user by badge ID and user_id
	*
	* param integer badge_id           : badge ID from database
	* param integer user_id (optional) : automatically get current logged in user ID if not defined
	*
	* return boolean
	*
	* see apache / php log message for any error
	*/
	public static function removeUserBadge($badge_id, $user_id = 0)
	{
		if (empty($user_id))
		{
			if (false === ($user_id = self::getCurrentUserId()))
			{
				return false;
			}
		}

		try {
			ReputationModel::removeUserBadge($user_id, $badge_id);
		} catch (DBException $e) {
			error_log($e->getMessage());
			return false;
		}

		return true;
	}

	/*
	* Set a reputation point to a user by their action
	*
	* param integer point              : point number, in example use -2 for negative point
	* param integer action_id          : in example a Post ID
	* param integer action_type        : in example 'post'
	* param integer event_name         : event description / text when point added
	* param integer user_id (optional) : point given by a user ID, automatically get current logged in user ID if not defined
	*
	* return boolean
	*
	* see apache / php log message for any error
	*/
	public static function add($point, $action_id, $action_type, $event_name, $user_id = 0)
	{
		if (!in_array(strtolower($action_type), self::$_allowed_action_types))
		{
			echo 'Allowed action types are: '.implode(',', self::$_allowed_action_types);
			exit();
		}

		if (!is_user_logged_in())
			return false;

		if (empty($user_id))
		{
			if (false === ($user_id = self::getCurrentUserId()))
			{
				return false;
			}
		}

		if (self::isAlreadyAdding($user_id, $action_id, $action_type, $event_name))
			return false;
		
		try {
			ReputationModel::add(array(
				'user_id'      => (int) $user_id,
				'point'        => (int) $point,
				'action_id'    => (int) $action_id,
				'action_type'  => strtolower($action_type),
				'event_name'   => $event_name
			));
		} catch (DBException $e) {
			error_log($e->getMessage());
			return false;
		}

		return true;
	}

	/*
	* Check if a user already give point to a action and event
	*
	* param integer action_id          : in example a Post ID
	* param integer action_type        : in example 'post'
	* param integer event_name         : event description / text when point added
	* param integer user_id (optional) : point given by a user ID, automatically get current logged in user ID if not defined
	*
	* return boolean
	*
	* see apache / php log message for any error
	*/
	public static function isAlreadyAdding($action_id, $action_type, $event_name, $user_id = 0)
	{
		if (!is_user_logged_in())
			return true;

		if (empty($user_id))
		{
			if (false === ($user_id = self::getCurrentUserId()))
			{
				return true;
			}
		}
		
		try {
			$history = ReputationModel::getHistory(array(
				'user_id'     => (int) $user_id,
				'action_id'   => (int) $action_id,
				'action_type' => $action_type,
				'event_name'  => $event_name,
				'limit'       => 1
			));

			if (is_object($history))
				return true;
		} catch (DBException $e) {
			error_log($e->getMessage());
			return false;
		}

		return false;
	}

	/*
	* Check if the action is created by a user him self
	*
	* param integer action_id          : in example a Post ID
	* param integer action_type        : in example 'post'
	* param integer user_id (optional) : automatically get current logged in user ID if not defined
	*
	* return boolean
	*
	* see apache / php log message for any error
	*/
	public static function isUserAction($action_id, $action_type, $user_id = 0)
	{
		if (!is_user_logged_in())
			return true;

		if (empty($user_id))
		{
			if (false === ($user_id = self::getCurrentUserId()))
			{
				return true;
			}
		}

		$creator_id = ReputationModel::getActionCreator($action_type, $action_id);

		if (empty($creator_id))
			return true;

		return ($user_id == $creator_id) ? true : false;
	}

	/*
	* Get badge list data for a user
	*
	* param integer user_id (optional) : automatically get current logged in user ID if not defined
	*
	* return array
	*/
	public static function getBadges(&$user_id = 0)
	{
		$badges = array();

		if (empty($user_id))
		{
			if (false === ($user_id = self::getCurrentUserId()))
			{
				return $badges;
			}
		}

		$badges = ReputationModel::getBadge(array(
			'user_id' => $user_id
		));

		return $badges;
	}

	/*
	* Show badge history view
	*
	* param integer user_id (optional) : automatically get current logged in user ID if not defined
	*
	* return void, ouput html
	*/
	public static function getBadgesView($user_id = 0)
	{
		$badges = self::getBadges($user_id);
		
		$base = Base::app();

		$plugin_view_path = $base->getPath('view');
		$history_template = $base->getConfig('badge_history_template');

		unset($base);

		$theme_template_path = rtrim(get_template_directory(), '/').'/'.$history_template.'.php';
		if (file_exists($theme_template_path))
		{
			include_once($theme_template_path);
		}
		else
		{
			include_once($plugin_view_path.$history_template.'.php');
		}
	}

	/*
	* Get a user URL page for showing their reputation
	*
	* param integer user_id (optional) : automatically get current logged in user ID if not defined
	*
	* return string
	*/
	public static function getUserUrl($user_id = 0)
	{
		$nickname = '';

		if (empty($user_id))
		{
			if (function_exists('wp_get_current_user'))
			{
				$current_user = wp_get_current_user();
				if ( !empty($current_user->ID) )
				{
					$nickname = $current_user->nickname;
				}
				unset($current_user);
			}
		}

		if (!empty($user_id))
		{
			$user = get_userdata($user_id);
			if ( is_object($user) )
			{
				$nickname = $user->nickname;
			}
			unset($user);
		}

		if (!empty($nickname))
			return rtrim(get_bloginfo('siteurl'), '/').'/'.$this->getOption('page_slug').'/'.$nickname;

		return '';
	}

	/*
	* Get current logged in user ID
	*
	* return integer
	*/
	public static function getCurrentUserId()
	{
		$user_id = 0;
		if (function_exists('get_current_user_id'))
		{
			$user_id = get_current_user_id();
		}

		return (!empty($user_id)) ? $user_id : false;
	}

	/*
	* Get current page owner user data
	*
	* return object
	*/
	public static function getCurrentPageOwner()
	{
		$base = Base::app();
		$nickname = get_query_var($base->getConfig('prefix').'page');
		unset($base);

		if (empty($nickname))
			return false;

		return UserModel::getByNickname($nickname);
	}

	/*
	* Get current URL
	*
	* return string
	*/
	public static function getCurrentUri()
	{
		return \UserReputation\Lib\Utility::currentUrl();
	}

	public function _bootstrap()
	{}

	public static function _install()
	{
		global $wpdb;

		update_option( 'ur_db_version', '1.0' );

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = $wpdb->prefix . 'reputations';
		$sql = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` bigint(20) unsigned NOT NULL,
		  `total` int(11) NOT NULL DEFAULT '0',
		  `action_number` int(11) NOT NULL DEFAULT '0',
		  `badge_number` int(11) NOT NULL,
		  `updated_at` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'reputation_badges';
		$sql = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `title` varchar(50) NOT NULL DEFAULT '',
		  `type` varchar(50) NOT NULL DEFAULT '',
		  `icon` varchar(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'reputation_histories';
		$sql = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` bigint(20) unsigned NOT NULL,
		  `receiver_user_id` bigint(20) unsigned NOT NULL,
		  `point` int(11) NOT NULL DEFAULT '0',
		  `action_id` bigint(20) unsigned NOT NULL,
		  `action_type` varchar(10) NOT NULL DEFAULT '',
		  `event_name` varchar(255) NOT NULL DEFAULT '',
		  `created_at` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'reputation_histories';
		$sql = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` bigint(20) unsigned NOT NULL,
		  `badge_id` int(11) unsigned NOT NULL,
		  `created_at` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		dbDelta( $sql );
	}

	public static function _uninstall()
	{
		
	}
}
