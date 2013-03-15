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

	public static function getHistoryView($user_id, $limit = 0, $page = 1)
	{
		$histories = array();

		if (empty($user_id))
			return $histories;

		$base = Base::app();

		if (empty($limit))
		{
			$limit = $base->getOption('num_items_per_page');
		}

		$plugin_view_path = $base->getPath('view');
		$history_template = $base->getConfig('reputation_history_template');

		unset($base);

		try {
			$histories = ReputationModel::getHistory(array(
				'receiver_user_id' => (int) $user_id,
				'page'             => $page,
				'limit'            => $limit,
				'order_by'         => 'created_at'
			));
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

	public function getUserTotalBadge($user_id = 0)
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

	public static function getBadges($args = array())
	{
		return ReputationModel::getBadge($args);
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

	public static function getCurrentUserId()
	{
		$user_id = 0;
		if (function_exists('get_current_user_id'))
		{
			$user_id = get_current_user_id();
		}

		return (!empty($user_id)) ? $user_id : false;
	}

	public static function getCurrentPageOwner()
	{
		$base = Base::app();
		$nickname = get_query_var($base->getConfig('prefix').'page');
		unset($base);

		if (empty($nickname))
			return false;

		return UserModel::getByNickname($nickname);
	}

	public static function getCurrentUri()
	{
		return \UserReputation\Lib\Utility::currentUrl();
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
