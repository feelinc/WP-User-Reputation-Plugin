<?php
/**
 * The template for displaying user my reputation
 */

$reputation = UserReputation::get();

get_header(); ?>

	<?php if (!is_user_logged_in()) : ?>
	<h1>Please login to see your reputations</h1><hr/>
	<?php endif; ?>

	<h2>TOTAL REPUTATION: <?php echo $reputation->total; ?></h2>
	<?php if (!empty($reputation->total_per_badge_types)) : ?>
		<ul>
		<?php foreach($reputation->total_per_badge_types as $type) : ?>
			<li><?php echo $type->title; ?>: <?php echo $type->total; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<hr/>

	<h2>REPUTATION HISTORY</h2>
	<br/>
	<br/>
	<div id="reputation-history-wrapper">
		<?php UserReputation::getHistoryView(); ?>
	</div>

	<hr/>

	<h2>BADGE HISTORY</h2>
	<br/>
	<br/>
	<strong><?php echo $reputation->badge_number; ?> Badges</strong><br/><br/>
	<div id="badge-history-wrapper">
		<?php UserReputation::getBadgesView(); ?>
	</div>

	<hr/>

<?php get_footer(); ?>