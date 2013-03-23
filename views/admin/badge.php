<div class="wrap nosubsub">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php _e('Badges', $this->getConfig('txt_domain')); ?></h2>
	
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
				<table class="wp-list-table widefat fixed tags" cellspacing="0">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb">&nbsp;</th>
							<th scope="col" id="title" class="manage-column column-name desc" style=""><?php _e('Title', $this->getConfig('txt_domain')); ?></th>
							<th scope="col" id="type" class="manage-column column-description desc" style=""><?php _e('Type', $this->getConfig('txt_domain')); ?></th>
							<th scope="col" id="desc" class="manage-column column-description desc" style=""><?php _e('Description', $this->getConfig('txt_domain')); ?></th>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<td scope="col" id="cb" class="manage-column column-cb">&nbsp;</th>
							<th scope="col" id="title" class="manage-column column-name desc" style=""><?php _e('Title', $this->getConfig('txt_domain')); ?></th>
							<th scope="col" id="type" class="manage-column column-type desc" style=""><?php _e('Type', $this->getConfig('txt_domain')); ?></th>
							<th scope="col" id="desc" class="manage-column column-description desc" style=""><?php _e('Description', $this->getConfig('txt_domain')); ?></th>
						</tr>
					</tfoot>

					<tbody id="the-list" data-wp-lists="list:tag">
						<?php if (!empty($badges)) : ?>
						<?php foreach($badges as $badge) : ?>
						<tr id="badge-<?php echo $badge->id; ?>" class="alternate">
							<th class="icon column-icon"><img src="<?php echo $badge->icon; ?>" /></th>
							<td class="name column-name">
								<strong><a class="row-title" href=""><?php echo $badge->title; ?></a></strong><br>
								<div class="row-actions">
									<span class="edit"><a href="<?php echo add_query_arg('edit', $badge->id, \UserReputation\Lib\Utility::currentUrl()); ?>"><?php _e('Edit'); ?></a> | </span>
									<span class="delete"><a href="<?php echo add_query_arg(array('delete' => $badge->id, 'nonce' => wp_create_nonce($this->getConfig('prefix').'delete-badge')), \UserReputation\Lib\Utility::currentUrl()); ?>"><?php _e('Delete', $this->getConfig('txt_domain')); ?></a></span>
								</div>
							</td>
							<td class="type column-type"><?php echo $badge_types[$badge->type]; ?></td>
							<td class="type column-type"><?php echo $badge->description; ?></td>
						</tr>
						<?php endforeach; ?>
						<?php else: ?>
						<tr class="alternate">
							<td colspan="3"><?php _e('Please add a badge first', $this->getConfig('txt_domain')); ?></td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<?php if (empty($current_badge->id)) : ?>
					<h3><?php _e('Add New Badge', $this->getConfig('txt_domain')); ?></h3>
					<?php else : ?>
					<h3><?php _e('Edit Badge', $this->getConfig('txt_domain')); ?></h3>
					<?php endif; ?>
					<form id="addbadge" method="post" action="" class="validate">
						<?php wp_nonce_field( $this->getConfig('prefix').'edit-badge' ); ?>
						<div class="form-field form-required">
							<label for="title"><?php _e('Title', $this->getConfig('txt_domain')); ?></label>
							<input name="title" id="title" type="text" value="<?php echo $current_badge->title; ?>" size="40" aria-required="true" />
						</div>
						<div class="form-field form-required">
							<label for="type"><?php _e('Type', $this->getConfig('txt_domain')); ?></label>
							<?php if (!empty($badge_types)) : ?>
							<select id="type" name="type" style="width: 95%;">
								<?php foreach($badge_types as $type_key => $type) : ?>
								<option value="<?php echo $type_key; ?>"<?php echo ($current_badge->type == $type_key) ? ' selected="selected"' : ''; ?>><?php _e($type, $this->getConfig('txt_domain')); ?></option>
								<?php endforeach; ?>
							</select>
							<?php endif; ?>
						</div>
						<div class="form-field form-required uploader">
							<label for="icon"><?php _e('Icon', $this->getConfig('txt_domain')); ?></label>
							<input name="icon" id="icon" type="text" value="<?php echo $current_badge->icon; ?>" aria-required="true" />
							<input type="button" id="upload-icon" class="button" value="<?php _e('Upload icon', $this->getConfig('txt_domain')); ?>" />
						</div>
						<div class="form-field form-required">
							<label for="type"><?php _e('Description', $this->getConfig('txt_domain')); ?></label>
							<textarea name="description" rows="10"><?php echo $current_badge->description; ?></textarea>
						</div>
						<p class="submit">
							<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e(((empty($current_badge->id)) ? 'Add New Badge' : 'Save Changes'), $this->getConfig('txt_domain')); ?>" />
							<?php if (!empty($current_badge->id)) : ?>
							<a class="submitdelete deletion" href="<?php echo remove_query_arg('edit', \UserReputation\Lib\Utility::currentUrl()); ?>"><?php _e('Cancel', $this->getConfig('txt_domain')); ?></a>
							<?php endif; ?>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){
		var file_frame;

		jQuery('#upload-icon').live('click', function( event ){
 
			event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
			  file_frame.open();
			  return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
			  title: jQuery( this ).data( 'uploader_title' ),
			  button: {
			    text: jQuery( this ).data( 'uploader_button_text' ),
			  },
			  multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
			  // We set multiple to false so only get one image from the uploader
			  attachment = file_frame.state().get('selection').first().toJSON();

			  jQuery('#icon').val(attachment.url);
			});

			// Finally, open the modal
			file_frame.open();
		});
	});
</script>