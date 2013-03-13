<?php namespace UserReputation\Model;

include_once( dirname(__DIR__) . '/libraries/Exceptions.php' );

use \UserReputation\Exception\DBException;

class Reputation
{
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
			'id'           => 0,
			'user_id'      => 0,
			'action_id'    => 0,
			'action_type'  => '',
			'event_name'   => '',
			'page'         => 0,
			'limit'        => 0,
			'order_by'     => 'id',
			'order'        => 'ASC'
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
			'user_id'      => 0,
			'point'        => 0,
			'action_id'    => 0,
			'action_type'  => '',
			'event_name'   => '',
			'created_at'   => time()
		), $data);

		if (empty($data['user_id'])
		 || empty($data['action_id'])
		 || empty($data['action_type']))
		{
			throw new DBException('Invalid data', DBException::EC_INVALID_DATA);
		}

		if (!$wpdb->insert($wpdb->prefix.'reputation_histories', $data))
		{
			throw new DBException(sprintf('Query error: %s', $wpdb->last_error), DBException::EC_QUERY);
		}

		$reputation_history_id = $wpdb->insert_id;

		switch($data['action_type'])
		{
			case 'post':
				$user_id = $wpdb->get_var('SELECT `post_author` FROM `'.$wpdb->posts.'` WHERE `ID` = '.$data['action_id']);
			break;
			case 'comment':
				$user_id = $wpdb->get_var('SELECT `user_id` FROM `'.$wpdb->comments.'` WHERE `comment_ID` = '.$data['action_id']);
			break;
		}

		if (empty($user_id))
		{
			$wpdb->delete($wpdb->prefix.'reputation_histories', array(
				'id' => $reputation_history_id
			));

			throw new DBException(sprintf('Owner of action ID does not found.'), DBException::EC_INVALID_DATA);
		}

		$sql = 'SELECT `id` FROM ';
		$sql .= '`'.$wpdb->prefix.'reputations'.'` ';
		$sql .= 'WHERE ';
		$sql .= '`user_id` = '.$user_id;

		$reputation_id = $wpdb->get_var($sql);

		if (empty($reputation_id))
		{
			if (!$wpdb->insert($wpdb->prefix.'reputations', array(
				'user_id'       => $user_id,
				'total'         => $data['point'],
				'action_number' => 1,
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
}
