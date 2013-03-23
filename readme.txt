Templates that can created in your active theme directory:
1. user-reputation-page.php
2. my-reputation-page.php
3. reputation-history-template.php
4. badge-history-template.php

You can copy the default html codes for above templates in plugin view directory: "view/"

API list:
Located in file: controllers/UserReputation.php
Read the method comment for further info

1. Get user reputation

	$reputation = UserReputation::get(array(
		'user_id' => $user_id
	));

2. Get user reputation history data

	$histories = UserReputation::getHistory($user_id);

3. Show user reputation history view

	UserReputation::getHistoryView($user_id);

4. Assign badge to a user

	UserReputation::setUserBadge($badge_id, $user_id);

5. Remove badge from a user by user badge ID

	UserReputation::removeUserBadgeById($id);

6. Remove badge from a user by badge ID and user_id

	UserReputation::removeUserBadge($badge_id, $user_id);

7. Set a reputation point to a user by their action

	UserReputation::add($point, $action_id, $action_type, $event_name, $receiver_user_id, $user_id);

8. Check if a user already give point to a action and event

	UserReputation::isAlreadyAdding($action_id, $action_type, $event_name, $user_id);

9. Check if a user already give point to a action and event

	UserReputation::isUserAction($action_id, $action_type, $user_id);

10. Get badge list data

	$badges = UserReputation::getAvailableBadges();

11. Get badge list data for a user

	$user_badges = UserReputation::getBadges($user_id);

12. Show user badge history view

	UserReputation::getBadgesView($user_id);

13. Get a user URL page for showing their reputation

	$user_url = UserReputation::getUserUrl($user_id);

14. Get current logged in user ID

	$user_id = UserReputation::getCurrentUserId();

15. Get current page owner user data

	$user_data = UserReputation::getCurrentPageOwner();

16. Get current URL

	$url = UserReputation::getCurrentUri();
