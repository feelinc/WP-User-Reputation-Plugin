<?php

include_once( dirname(__DIR__) . '/libraries/Base.php' );
include_once( dirname(__DIR__) . '/models/Reputation.php' );

use \UserReputation\Exception\SysException;
use \UserReputation\Exception\DBException;
use \UserReputation\Model\Reputation as ReputationModel;

class UserReputation extends \UserReputation\Lib\Base
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

		if (self::isAlreadyAdding($user_id, $action_id, $action_type))
		{
			return false;
		}
		
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

	public static function isAlreadyAdding($user_id, $action_id, $action_type)
	{
		try {
			$history = ReputationModel::getHistory(array(
				'user_id'     => (int) $user_id,
				'action_id'   => (int) $action_id,
				'action_type' => $action_type,
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

	private static function getCurrentUserId()
	{
		$user_id = 0;
		if (function_exists('get_current_user_id'))
		{
			$user_id = get_current_user_id();
		}

		return (!empty($user_id)) ? $user_id : false;
	}

	public function _bootstrap()
	{
		
	}

	public static function _install()
	{
		
	}

	public static function _uninstall()
	{
		
	}
}
