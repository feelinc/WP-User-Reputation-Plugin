<?php if (!empty($histories)) : ?>
	<ul>
	<?php foreach($histories as $history) : ?>
		<li class="clear" style="margin:0 0 15px 0;">
			<div style="float:left; width:40px;"><?php echo $history->point; ?></div><a href="<?php echo $history->action_url; ?>" style="float:left;"><?php echo $history->action_title; ?></a>
		</li>
	<?php endforeach; ?>
	<ul>
<?php else : ?>
	NO HISTORY FOUND YET.
<?php endif; ?>