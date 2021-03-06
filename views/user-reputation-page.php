<?php
/**
 * The template for displaying user reputation
 */

/* START: EXAMPLE FOR SIMULATING USER REPUTATION DATA */
/* YOU CAN REMOVE IT IF WANT TO */
$error     = '';
$success   = '';

if ($_POST)
{
	// process adding user badge
	if (isset($_POST['set_badge']))
	{
		$user_id  = (isset($_POST['user'])) ? $_POST['user'] : 0;
		$badge_id = (isset($_POST['badge'])) ? $_POST['badge'] : 0;

		if (!UserReputation::setUserBadge($badge_id, $user_id))
		{
			$error = 'There was a problem while adding the badge';
		}
		else
		{
			$success = 'Badge added';
		}
	}

	// process remove user badge
	if (isset($_POST['remove_badge']))
	{
		$user_id  = (isset($_POST['user'])) ? $_POST['user'] : 0;
		$badge_id = (isset($_POST['badge'])) ? $_POST['badge'] : 0;

		if (!UserReputation::removeUserBadge($badge_id, $user_id))
		{
			$error = 'There was a problem while removing the badge';
		}
		else
		{
			$success = 'Badge removed';
		}
	}

	// process adding user reputation
	if (isset($_POST['add_reputation']))
	{
		$point            = (isset($_POST['point'])) ? $_POST['point'] : 0;
		$receiver_user_id = (isset($_POST['user'])) ? $_POST['user'] : 0;
		$action_id        = (isset($_POST['action_id'])) ? $_POST['action_id'] : 0;
		$action_type      = (isset($_POST['action_type'])) ? $_POST['action_type'] : '';
		$event_name       = (isset($_POST['event_name'])) ? $_POST['event_name'] : '';

		if (! UserReputation::add($point, $action_id, $action_type, $event_name, $receiver_user_id))
		{
			$error = 'There was a problem while adding the reputation';
		}
		else
		{
			$success = 'Reputation added';
		}
	}
}
/* END: FOR SIMULATING USER REPUTATION DATA */


// get current page owner user data
$user_data = UserReputation::getCurrentPageOwner();

if ($user_data !== null)
{
	$user_data = get_userdata($user_data->ID);
}

// get current page user reputation
$reputation = null;
if ($user_data)
{
	$reputation = UserReputation::get(array(
		'user_id' => $user_data->ID
	));
}

// get badge list
$badges = UserReputation::getAvailableBadges();
$badges = UserReputation::getBadges();

// get current user badges
$user_badges = array();
if ($user_data)
{
	$user_badges = UserReputation::getBadges($user_data->ID);
}

get_header(); ?>

	<!-- START: Message handler -->
	<?php if (!empty($error)) : ?>
	<h3><?php echo $error; ?></h3><br/><br/>
	<?php endif; ?>

	<?php if (!empty($success)) : ?>
	<h3><?php echo $success; ?></h3><br/><br/>
	<?php endif; ?>
	<!-- END: Message handler -->

	<?php if ($user_data) : ?>

	<h1><?php echo $user_data->display_name; ?></h1>
	<h2>TOTAL REPUTATION: <?php echo ($reputation) ? $reputation->total : 0; ?></h2>
	<?php if (!empty($reputation->total_per_badge_types)) : ?>
		<ul>
		<?php foreach($reputation->total_per_badge_types as $type) : ?>
			<li><?php echo $type->title; ?>: <?php echo $type->total; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php else: ?>
	<h1>NO USER FOUND FOR THIS PAGE</h1>
	<?php endif; ?>

	<hr/>

	<h2>REPUTATION HISTORY</h2>
	<br/>
	<br/>
	<?php if ($user_data) : ?>
	<div id="reputation-history-wrapper">
		<?php UserReputation::getHistoryView($user_data->ID); ?>
	</div>
	<?php endif; ?>

	<hr/>

	<h2>BADGE HISTORY</h2>
	<br/>
	<br/>
	<?php if ($user_data) : ?>
	<strong><?php echo ($reputation) ? $reputation->badge_number : 0; ?> Badges</strong><br/><br/>
	<div id="badge-history-wrapper">
		<?php UserReputation::getBadgesView($user_data->ID); ?>
	</div>
	<?php endif; ?>

	<hr/>

	<h2>EXAMPLE OF POINT & BADGE ASSIGNING</h2>
	<br/>
	<br/>

	<div class="clear">
		<div style="width:48%; margin-right:20px; float:left;">
			<?php if (!is_user_logged_in()) : ?>
			<h3>Please login to add any reputation</h3><br/><br/>
			<?php endif; ?>

			<?php if ($user_data !== null && is_user_logged_in()) : ?>
			<p style="color:red;">*Remember that the point will be given to the Action ID creator/author, not to the current page owner</p>
			<br/><br/>
			<form name="reputation" method="POST" action="">
				Point: <input type="text" name="point" value="10" /><br/>
				For User ID: <input type="text" name="user" value="<?php echo $user_data->ID; ?>" /><br/>
				Action ID: <input type="text" name="action_id" value="1" /><br/>
				Action Type: <input type="text" name="action_type" value="post" /><br/>
				Event Name: <input type="text" name="event_name" value="voteup" /><br/>
				<input type="submit" name="add_reputation" value="Add a reputation" />
			</form>
			<?php endif; ?>
		</div>

		<div style="width:48%; float:left;">
			<h4>Current user badges:</h4><br/>
			<?php if (!empty($user_badges)) : ?>
			<strong>TOTAL BADGE: <?php echo $reputation->badge_number; ?></strong><br/><br/>
			<ul>
				<?php foreach($user_badges as $badge) : ?>
				<li>
					<img src="<?php echo $badge->icon; ?>" width="30" />&nbsp;<?php echo $badge->title; ?>

					<form name="badge" method="POST" action="">
						<input type="hidden" name="user" value="<?php echo $user_data->ID; ?>" />
						<input type="hidden" name="badge" value="<?php echo $badge->id; ?>" />
						<input type="submit" name="remove_badge" value="Remove badge" />
					</form>

				</li>
				<?php endforeach; ?>
			</ul>
			<?php else: ?>
			<p>NO BADGES ASSIGNED</p>
			<?php endif; ?>
			
			<br/><br/><br/>

			<h4>Set badges for this user.</h4><br/>

			<?php if (!empty($badges) && $user_data) : ?>
			<form name="badge" method="POST" action="">
				User ID: <input type="text" name="user" value="<?php echo $user_data->ID; ?>" /><br/>
				<select name="badge">
					<?php foreach($badges as $badge) : ?>
					<option value="<?php echo $badge->id; ?>"><?php echo $badge->title; ?></option>
					<?php endforeach; ?>
				</select>
				<br/>
				<input type="submit" name="set_badge" value="Set badge" />
			</form>
			<?php endif; ?>
		</div>
	</div>

	<div style="clear:both"></div>

<?php get_footer(); ?>