<div id="user-reputation-history">
	<?php $pagination->labels('prev', 'next'); ?>

	<script type="text/javascript">
		/* required for ajax pagination */
		var urCurrentPageUserID = <?php echo $user_id; ?>;
	</script>

	<?php if (!empty($histories)) : ?>
		
		<ul>
		<?php foreach($histories as $history) : ?>
			<li class="clear" style="margin:0 0 15px 0;">
				<div style="float:left; width:40px;"><?php echo $history->point; ?></div>
				<label style="float:left; margin-right:20px;"><?php echo $history->event_name; ?></label>
				<a style="float:left; margin-right:20px;" href="<?php echo $history->action_url; ?>"><?php echo $history->action_title; ?></a>
				<label style="float:left;"><?php echo date('F d, Y', $history->created_at); ?></label>
			</li>
		<?php endforeach; ?>
		<ul>

		<div class="ur-pagination-wrapper clear">
			<?php $pagination->render(false, 'ur-pagination ur-history-pagination'); ?>
		</div>
	<?php else : ?>
		NO HISTORY FOUND YET.
	<?php endif; ?>
</div>