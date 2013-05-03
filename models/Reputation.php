<?php namespace UserReputation\Model;

/*
 * (author) Sulaeman <me@sulaeman.com>
 */

include_once( dirname(__DIR__) . '/libraries/Exceptions.php' );

use \UserReputation\Exception\DBException;

class Reputation
{
	public static function get($param, &$total_rows = 0, &$number_rows = 0)
	{
		global $wpdb;

		// Check if param is a numeric, treat as the ID
		if (is_numeric($param))
		{
			$options['id'] = $param;
		}
		else
		{
			$options = $param;
		}
		unset($param);

		$options = array_merge(array(
			'id'           => 0,
			'user_id'      => 0,
			'page'         => 0,
			'limit'        => 0,
			'order_by'     => 'id',
			'order'        => 'ASC'
		), $options);

		$need_a_row = FALSE;
	    $use_where = FALSE;
	    $has_where = FALSE;

	    if (!empty($options['id'])  
	      || !empty($options['user_id'])  
	      || $options['limit'] == 1)
	    {
	    	$need_a_row = TRUE;
	    	$use_where = TRUE;
	    }

	    if (!empty($options['user_id']))
	    {
	    	$use_where = TRUE;
	    }

	    $sql = 'SELECT ';

		if (!$need_a_row)
		{
			$sql .= 'SQL_CALC_FOUND_ROWS ';
		}

		$sql .= '`'.$wpdb->prefix.'reputations`.* FROM `'.$wpdb->prefix.'reputations` ';

		if ($use_where)
		{
			$sql .= 'WHERE ';
		}

		if (!empty($options['id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputations`.`id` = '.$options['id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['user_id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputations`.`user_id` = '.$options['user_id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['order_by']) && !empty($options['order']))
	    {
	    	$sql .= 'ORDER BY `'.$wpdb->prefix.'reputations`.'.$options['order_by'].' '.$options['order'].' ';
	    }

	    if (!empty($options['page']) && !empty($options['limit']))
	    {
	    	$sql .= 'LIMIT '.($options['limit'] * ($options['page'] - 1)).', '.$options['limit'].' ';
	    }
	    elseif (!empty($options['limit']))
	    {
	    	$sql .= 'LIMIT '.$options['limit'].' ';
	    }

	    if (!$need_a_row)
      	{
	    	$results = $wpdb->get_results($sql);

	    	if ($results)
	    		$number_rows = count($results);

	    	// Get total rows found in previous query
	        $total_rows = $wpdb->get_var('SELECT FOUND_ROWS() as rows');
	    }
	    else
	    {
	    	$results = $wpdb->get_row($sql);

	    	if ($results)
	    		$number_rows = 1;
	    }

	    return $results;
	}

	public static function getHistory($param, &$total_rows = 0, &$number_rows = 0)
	{
		global $wpdb;

		// Check if param is a numeric, treat as the ID
		if (is_numeric($param))
		{
			$options['id'] = $param;
		}
		else
		{
			$options = $param;
		}
		unset($param);

		$options = array_merge(array(
			'id'               => 0,
			'user_id'          => 0,
			'receiver_user_id' => 0,
			'action_id'        => 0,
			'action_type'      => '',
			'event_name'       => '',
			'page'             => 0,
			'limit'            => 0,
			'order_by'         => 'id',
			'order'            => 'DESC'
		), $options);

		$need_a_row = FALSE;
	    $use_where = FALSE;
	    $has_where = FALSE;

	    if (!empty($options['id'])  
	      || $options['limit'] == 1)
	    {
	    	$need_a_row = TRUE;
	    	$use_where = TRUE;
	    }

	    if (!empty($options['user_id']) 
	      || !empty($options['receiver_user_id']) 
	      || !empty($options['action_id']) 
	      || !empty($options['action_type']) 
	      || !empty($options['event_name']))
	    {
	    	$use_where = TRUE;
	    }

	    $sql = 'SELECT ';

		if (!$need_a_row)
		{
			$sql .= 'SQL_CALC_FOUND_ROWS ';
		}

		$sql .= '`'.$wpdb->prefix.'reputation_histories`.* FROM `'.$wpdb->prefix.'reputation_histories` ';

		if ($use_where)
		{
			$sql .= 'WHERE ';
		}

		if (!empty($options['id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_histories`.`id` = '.$options['id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['user_id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_histories`.`user_id` = '.$options['user_id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['receiver_user_id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_histories`.`receiver_user_id` = '.$options['receiver_user_id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['action_id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_histories`.`action_id` = '.$options['action_id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['action_type']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_histories`.`action_type` = "'.$options['action_type'].'" ';

			$has_where = TRUE;
		}

		if (!empty($options['event_name']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_histories`.`event_name` = "'.$options['event_name'].'" ';

			$has_where = TRUE;
		}

		if (!empty($options['order_by']) && !empty($options['order']))
	    {
	    	$sql .= 'ORDER BY `'.$wpdb->prefix.'reputation_histories`.'.$options['order_by'].' '.$options['order'].' ';
	    }

	    if (!empty($options['page']) && !empty($options['limit']))
	    {
	    	$sql .= 'LIMIT '.($options['limit'] * ($options['page'] - 1)).', '.$options['limit'].' ';
	    }
	    elseif (!empty($options['limit']))
	    {
	    	$sql .= 'LIMIT '.$options['limit'].' ';
	    }
	    
	    if (!$need_a_row)
      	{
	    	$results = $wpdb->get_results($sql);

	    	if ($results)
	    		$number_rows = count($results);

	    	// Get total rows found in previous query
	        $total_rows = $wpdb->get_var('SELECT FOUND_ROWS() as rows');
	    }
	    else
	    {
	    	$results = $wpdb->get_row($sql);

	    	if ($results)
	    		$number_rows = 1;
	    }

	    return $results;
	}

	public static function add($data)
	{
		global $wpdb;

		$data = array_merge(array(
			'user_id'      		=> 0,
			'receiver_user_id' 	=> 0,
			'point'        		=> 0,
			'action_id'    		=> 0,
			'action_type'  		=> '',
			'event_name'   		=> '',
			'created_at'   		=> time()
		), $data);

		if (empty($data['user_id'])
		 || empty($data['action_id'])
		 || empty($data['action_type']))
		{
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);
		}

		if (empty($data['receiver_user_id']))
			$data['receiver_user_id'] = self::getActionCreator($data['action_type'], $data['action_id']);

		if (!$wpdb->insert($wpdb->prefix.'reputation_histories', $data))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		$reputation_history_id = $wpdb->insert_id;

		if (empty($data['receiver_user_id']))
		{
			$wpdb->delete($wpdb->prefix.'reputation_histories', array(
				'id' => $reputation_history_id
			));

			throw new DBException(sprintf('Owner of action ID does not found.'), DBException::EC_INVALID_DATA);
		}

		$sql = 'SELECT `id` FROM ';
		$sql .= '`'.$wpdb->prefix.'reputations'.'` ';
		$sql .= 'WHERE ';
		$sql .= '`user_id` = '.$data['receiver_user_id'];

		$reputation_id = $wpdb->get_var($sql);

		if (empty($reputation_id))
		{
			if (!$wpdb->insert($wpdb->prefix.'reputations', array(
				'user_id'       => $data['receiver_user_id'],
				'total'         => $data['point'],
				'action_number' => 1,
				'badge_number'  => 0,
				'updated_at'    => time()
			)))
			{
				throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
			}

			$reputation_id = $wpdb->insert_id;
		}
		else
		{
			$sql = 'UPDATE `'.$wpdb->prefix.'reputations'.'`';
			$sql .= 'SET ';
			$sql .= '`total` = `total` + '.$data['point'].', ';
			$sql .= '`action_number` = `action_number` + 1, ';
			$sql .= '`updated_at` = '.time().' ';
			$sql .= 'WHERE ';
			$sql .= '`id` = '.$reputation_id;

			if (!$wpdb->query($sql))
			{
				throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
			}
		}

		unset($sql);

		return $reputation_id;
	}

	public static function getActionData($type, $action_id)
	{
		$data = null;

		if (empty($type) || empty($action_id))
			return $data;

		global $wpdb;

		$data = array(
			'title' => '',
			'url' => ''
		);

		switch(strtolower($type))
		{
			case 'post':
				$post = get_post($action_id);

				if ($post)
				{
					$data = array(
						'title' => $post->post_title,
						'url' => get_permalink($action_id)
					);
				}

				unset($post);
			break;
			case 'comment':
				$comment = get_comment($action_id);

				if ($comment)
				{
					$post = get_post($comment->comment_post_ID);

					$data = array(
						'title' => $post->post_title,
						'url' => get_comment_link($comment)
					);

					unset($post);
				}

				unset($comment);
			break;
		}

		return $data;
	}

	public static function getActionCreator($type, $action_id)
	{
		$user_id = 0;

		if (empty($type) || empty($action_id))
			return $user_id;

		global $wpdb;

		switch(strtolower($type))
		{
			case 'post':
				$user_id = $wpdb->get_var('SELECT `post_author` FROM `'.$wpdb->posts.'` WHERE `ID` = '.$action_id.' AND `post_type` = "post" AND `post_status` = "publish"');
			break;
			case 'comment':
				$user_id = $wpdb->get_var('SELECT `user_id` FROM `'.$wpdb->comments.'` WHERE `comment_ID` = '.$action_id);
			break;
		}

		return $user_id;
	}

	public static function setUserBadge($data)
	{
		global $wpdb;

		$data = array_merge(array(
			'user_id'    => 0,
			'badge_id'   => 0,
			'created_at' => time()
		), $data);

		if (empty($data['user_id'])
		 || empty($data['badge_id']))
		{
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);
		}

		if (!$wpdb->insert($wpdb->prefix.'reputation_user_badges', $data))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		$sql = 'SELECT `id` FROM ';
		$sql .= '`'.$wpdb->prefix.'reputations'.'` ';
		$sql .= 'WHERE ';
		$sql .= '`user_id` = '.$data['user_id'];

		$reputation_id = $wpdb->get_var($sql);

		if (empty($reputation_id))
		{
			if (!$wpdb->insert($wpdb->prefix.'reputations', array(
				'user_id'       => $data['user_id'],
				'total'         => 0,
				'action_number' => 0,
				'badge_number'  => 1,
				'updated_at'    => time()
			)))
			{
				throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
			}

			$reputation_id = $wpdb->insert_id;
		}
		else
		{
			$sql = 'UPDATE `'.$wpdb->prefix.'reputations'.'`';
			$sql .= 'SET ';
			$sql .= '`badge_number` = `badge_number` + 1, ';
			$sql .= '`updated_at` = '.time().' ';
			$sql .= 'WHERE ';
			$sql .= '`id` = '.$reputation_id;

			if (!$wpdb->query($sql))
			{
				throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
			}
		}

		unset($sql);

		return $wpdb->insert_id;
	}

	public static function removeUserBadgeById($id)
	{
		global $wpdb;
		
		if (empty($id))
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);

		$user_badge = self::getUserBadge($id);

		if (!is_object($user_badge))
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);

		if (!$wpdb->delete($wpdb->prefix.'reputation_user_badges', array(
			'id'  => $id
		)))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		$sql = 'UPDATE `'.$wpdb->prefix.'reputations'.'`';
		$sql .= 'SET ';
		$sql .= '`badge_number` = `badge_number` - 1, ';
		$sql .= '`updated_at` = '.time().' ';
		$sql .= 'WHERE ';
		$sql .= '`user_id` = '.$user_badge->user_id;

		unset($user_badge);

		if (!$wpdb->query($sql))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		return true;
	}

	public static function removeUserBadge($user_id, $badge_id)
	{
		global $wpdb;

		if (empty($badge_id)
		 || empty($user_id))
		{
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);
		}

		if (!$wpdb->delete($wpdb->prefix.'reputation_user_badges', array(
			'user_id'  => $user_id,
			'badge_id' => $badge_id
		)))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		$sql = 'UPDATE `'.$wpdb->prefix.'reputations'.'`';
		$sql .= 'SET ';
		$sql .= '`badge_number` = `badge_number` - 1, ';
		$sql .= '`updated_at` = '.time().' ';
		$sql .= 'WHERE ';
		$sql .= '`user_id` = '.$user_id;

		if (!$wpdb->query($sql))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		return true;
	}

	public static function getUserBadge($param = array(), &$total_rows = 0, &$number_rows = 0)
	{
		global $wpdb;

		// Check if param is a numeric, treat as the ID
		if (is_numeric($param))
		{
			$options['id'] = $param;
		}
		else
		{
			$options = $param;
		}
		unset($param);

		$options = array_merge(array(
			'id'       => 0,
			'user_id'  => 0,
			'badge_id' => 0,
			'page'     => 0,
			'limit'    => 0,
			'order_by' => 'id',
			'order'    => 'ASC'
		), $options);

		$need_a_row = FALSE;
	    $use_where = FALSE;
	    $has_where = FALSE;

	    if (!empty($options['id'])  
	      || $options['limit'] == 1)
	    {
	    	$need_a_row = TRUE;
	    	$use_where = TRUE;
	    }

	    if (!empty($options['user_id'])
	     || !empty($options['badge_id']))
	    {
	    	$use_where = TRUE;
	    }

	    $sql = 'SELECT ';

		if (!$need_a_row)
		{
			$sql .= 'SQL_CALC_FOUND_ROWS ';
		}

		$sql .= '`'.$wpdb->prefix.'reputation_user_badges`.* ';
		$sql .= 'FROM `'.$wpdb->prefix.'reputation_user_badges` ';

		if ($use_where)
		{
			$sql .= 'WHERE ';
		}

		if (!empty($options['id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_user_badges`.`id` = '.$options['id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['user_id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_user_badges`.`user_id` = '.$options['user_id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['badge_id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_user_badges`.`badge_id` = '.$options['badge_id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['order_by']) && !empty($options['order']))
	    {
	    	$sql .= 'ORDER BY `'.$wpdb->prefix.'reputation_user_badges`.'.$options['order_by'].' '.$options['order'].' ';
	    }

	    if (!empty($options['page']) && !empty($options['limit']))
	    {
	    	$sql .= 'LIMIT '.($options['limit'] * ($options['page'] - 1)).', '.$options['limit'].' ';
	    }
	    elseif (!empty($options['limit']))
	    {
	    	$sql .= 'LIMIT '.$options['limit'].' ';
	    }
	    
	    if (!$need_a_row)
      	{
	    	$results = $wpdb->get_results($sql);

	    	if ($results)
	    		$number_rows = count($results);

	    	// Get total rows found in previous query
	        $total_rows = $wpdb->get_var('SELECT FOUND_ROWS() as rows');
	    }
	    else
	    {
	    	$results = $wpdb->get_row($sql);

	    	if ($results)
	    		$number_rows = 1;
	    }

	    return $results;
	}

	public static function getTotalPerUserBadgeType($user_id)
	{
		if (empty($user_id))
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);

		global $wpdb;

		$sql = 'SELECT ';
		$sql .= 'COUNT(`wp_reputation_user_badges`.`id`) as total, ';
		$sql .= '`wp_reputation_badges`.`type` ';
		$sql .= 'FROM `wp_reputation_user_badges` ';
		$sql .= 'LEFT JOIN `wp_reputation_badges` ON (`wp_reputation_badges`.`id` = `wp_reputation_user_badges`.`badge_id`) ';
		$sql .= 'WHERE ';
		$sql .= '`wp_reputation_user_badges`.`user_id` = '.$user_id.' ';
		$sql .= 'GROUP BY `wp_reputation_badges`.`type`';

		return $wpdb->get_results($sql);
	}

	public static function getBadge($param = array(), &$total_rows = 0, &$number_rows = 0)
	{
		global $wpdb;

		// Check if param is a numeric, treat as the ID
		if (is_numeric($param))
		{
			$options['id'] = $param;
		}
		else
		{
			$options = $param;
		}
		unset($param);

		$options = array_merge(array(
			'id'       => 0,
			'user_id'  => 0,
			'type'     => '',
			'page'     => 0,
			'limit'    => 0,
			'order_by' => 'id',
			'order'    => 'ASC'
		), $options);

		$need_a_row = FALSE;
	    $use_where = FALSE;
	    $has_where = FALSE;

	    if (!empty($options['id'])  
	      || $options['limit'] == 1)
	    {
	    	$need_a_row = TRUE;
	    	$use_where = TRUE;
	    }

	    if (!empty($options['user_id'])
	     || !empty($options['type']))
	    {
	    	$use_where = TRUE;
	    }

	    $sql = 'SELECT ';

		if (!$need_a_row)
		{
			$sql .= 'SQL_CALC_FOUND_ROWS ';
		}

		$sql .= '`'.$wpdb->prefix.'reputation_badges`.* ';
		if (!empty($options['user_id']))
		{
			$sql .= ', `'.$wpdb->prefix.'reputation_user_badges`.id as user_badge_id ';
			$sql .= ', `'.$wpdb->prefix.'reputation_user_badges`.created_at ';
		}

		$sql .= 'FROM `'.$wpdb->prefix.'reputation_badges` ';

		if (!empty($options['user_id']))
		{
			$sql .= 'LEFT JOIN `'.$wpdb->prefix.'reputation_user_badges` ON (`'.$wpdb->prefix.'reputation_badges`.`id` = `'.$wpdb->prefix.'reputation_user_badges`.`badge_id`) ';
		}

		if ($use_where)
		{
			$sql .= 'WHERE ';
		}

		if (!empty($options['id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_badges`.`id` = '.$options['id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['type']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_badges`.`type` = "'.$options['action_type'].'" ';

			$has_where = TRUE;
		}

		if (!empty($options['user_id']))
		{
			if ($has_where)
			{
				$sql .= 'AND ';
			}

			$sql .= '`'.$wpdb->prefix.'reputation_user_badges`.`user_id` = '.$options['user_id'].' ';

			$has_where = TRUE;
		}

		if (!empty($options['order_by']) && !empty($options['order']))
	    {
	    	$sql .= 'ORDER BY `'.$wpdb->prefix.'reputation_badges`.'.$options['order_by'].' '.$options['order'].' ';
	    }

	    if (!empty($options['user_id']))
		{
			$sql .= 'ORDER BY `'.$wpdb->prefix.'reputation_user_badges`.`created_at` '.$options['order'].' ';
		}

	    if (!empty($options['page']) && !empty($options['limit']))
	    {
	    	$sql .= 'LIMIT '.($options['limit'] * ($options['page'] - 1)).', '.$options['limit'].' ';
	    }
	    elseif (!empty($options['limit']))
	    {
	    	$sql .= 'LIMIT '.$options['limit'].' ';
	    }
	    
	    if (!$need_a_row)
      	{
	    	$results = $wpdb->get_results($sql);

	    	if ($results)
	    		$number_rows = count($results);

	    	// Get total rows found in previous query
	        $total_rows = $wpdb->get_var('SELECT FOUND_ROWS() as rows');
	    }
	    else
	    {
	    	$results = $wpdb->get_row($sql);

	    	if ($results)
	    		$number_rows = 1;
	    }

	    return $results;
	}

	public function addBadge($data)
	{
		global $wpdb;

		$data = array_merge(array(
			'title' => '',
			'type'  => '',
			'icon'  => ''
		), $data);

		if (empty($data['title'])
		 || empty($data['type']))
		{
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);
		}

		if (!$wpdb->insert($wpdb->prefix.'reputation_badges', $data))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		return $wpdb->insert_id;
	}

	public function updateBadge($id, $data)
	{
		global $wpdb;

		if (empty($id))
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);

		$data = array_merge(array(
			'title' => '',
			'type'  => '',
			'icon'  => ''
		), $data);

		if (empty($data['title'])
		 || empty($data['type']))
		{
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);
		}

		if (!$wpdb->update($wpdb->prefix.'reputation_badges', $data, array(
			'id' => $id
		)))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		return $id;
	}

	public function deleteBadge($id)
	{
		global $wpdb;

		if (empty($id))
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);

		if (!$wpdb->delete($wpdb->prefix.'reputation_badges', array(
			'id' => $id
		)))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		// TO DO: remove user badges also

		return true;
	}
}
