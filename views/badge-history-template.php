<div id="user-badge-history">
	<?php if (!empty($badges)) : ?>
		<ul>
		<?php foreach($badges as $badge) : ?>
			<li class="clear" style="margin:0 0 15px 0;">
				<img src="<?php echo $badge->icon; ?>" width="30" />&nbsp;<strong><?php echo $badge->title; ?></strong>,&nbsp;at:&nbsp;<?php echo date('F d, Y', $badge->created_at); ?>
			</li>
		<?php endforeach; ?>
		<ul>
	<?php else : ?>
		NO BADGE FOUND YET.
	<?php endif; ?>
</div>