<?php namespace UserReputation\Model;

include_once( dirname(__DIR__) . '/libraries/Exceptions.php' );

use \UserReputation\Exception\DBException;

class User
{
	public static function getByNickname($nickname)
	{
		global $wpdb;

		$sql = 'SELECT `'.$wpdb->users.'`.* ';
		$sql .= 'FROM `'.$wpdb->users.'` ';
		$sql .= 'LEFT JOIN `'.$wpdb->usermeta.'` ON (`'.$wpdb->usermeta.'`.`user_id` = `'.$wpdb->users.'`.`ID`) ';
		$sql .= 'WHERE ';
		$sql .= '`'.$wpdb->usermeta.'`.`meta_key` = "nickname" ';
		$sql .= 'AND `'.$wpdb->usermeta.'`.`meta_value` = "'.$nickname.'"';
		
		return $wpdb->get_row($sql);
	}
}
