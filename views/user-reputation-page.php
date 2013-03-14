<?php
/**
 * The template for displaying user reputation
 */

$nonce_key = 'add-a-reputation';
$error     = '';
$success   = '';

if ($_POST)
{
	$nonce = (isset($_POST['nonce'])) ? $_POST['nonce'] : '';

	if (wp_verify_nonce( $nonce, $nonce_key ))
	{
		$point       = (isset($_POST['point'])) ? $_POST['point'] : '';
		$action_id   = (isset($_POST['action_id'])) ? $_POST['action_id'] : '';
		$action_type = (isset($_POST['action_type'])) ? $_POST['action_type'] : '';
		$event_name  = (isset($_POST['event_name'])) ? $_POST['event_name'] : '';

		if (UserReputation::isUserAction($action_id, $action_type))
		{
			$error = 'You cannot do that for your own action';
		}
		else
		{
			if (UserReputation::isAlreadyAdding($action_id, $action_type, $event_name))
			{
				$error = 'You was voted / accepted this action';
			}
			else
			{
				if (! UserReputation::add($point, $action_id, $action_type, $event_name))
				{
					$error = 'There was a problem while adding the reputation';
				}
				else
				{
					$success = 'Reputation added';
				}
			}
		}
	}
}

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php if (!is_user_logged_in()) : ?>
			<h3>Please login to add any reputation</h3><br/><br/>
			<?php endif; ?>

			<?php if (!empty($error)) : ?>
			<h3><?php echo $error; ?></h3><br/><br/>
			<?php endif; ?>

			<?php if (!empty($success)) : ?>
			<h3><?php echo $success; ?></h3><br/><br/>
			<?php endif; ?>

			<form name="reputation" method="POST" action="">
				Point: <input type="text" name="point" value="10" /><br/>
				Action ID: <input type="text" name="action_id" value="1" /><br/>
				Action Type: <input type="text" name="action_type" value="post" /><br/>
				Event Name: <input type="text" name="event_name" value="voteup" /><br/>
				Nonce: <input type="text" name="nonce" value="<?php echo wp_create_nonce($nonce_key); ?>" readonly="readonly" />
				<input type="submit" name="add_reputation" value="Add a reputation" />
			</form>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>