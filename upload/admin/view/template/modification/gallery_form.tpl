<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/review.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><?php if ($status === 1) { ?>
              <span class="enabled"><?php echo $text_approved; ?></span>
            <?php } elseif ($status === 2) { ?>
              <span class="missed"><?php echo $text_refused; ?></span>
            <?php } else { ?>
              <span class="disabled"><?php echo $text_pending; ?></span>
            <?php } ?>
              &nbsp;
              <?php if ($status !== 1 && $has_image) { ?>
                <a onclick="gallery('<?php echo $approve; ?>');" class="button button-align ripple"><i class="fa fa-check"></i> &nbsp; <?php echo $button_approve; ?></a>
              <?php } ?>
              <?php if ($status === 1) { ?>
                <a onclick="gallery('<?php echo $pending; ?>');" class="button button-align ripple"><i class="fa fa-clock-o"></i> &nbsp; <?php echo $button_pending; ?></a>
              <?php } ?>
              <?php if ($status !== 2) { ?>
                <a onclick="gallery('<?php echo $refuse; ?>');" class="button button-align ripple"><i class="fa fa-ban"></i> &nbsp; <?php echo $button_refuse; ?></a>
              <?php } ?>
              <a id="gallery-delete" class="button-delete button-align ripple"><?php echo $button_delete; ?></a></td>
          </tr>
          <tr>
            <td></td>
            <td><?php if ($has_image) { ?>
              <img src="<?php echo $image; ?>" alt="" width="450" height="450" style="max-width:100%;height:auto;border-radius:6px;" />
            <?php } else { ?>
              <div class="attention"><?php echo $text_image_gone; ?></div>
            <?php } ?></td>
          </tr>
          <?php if ($page) { ?>
          <tr>
            <td><?php echo $entry_page; ?></td>
            <td><a href="<?php echo $page; ?>" target="_blank"><?php echo $page; ?></a></td>
          </tr>
          <?php } ?>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_pet_name; ?></td>
            <td><?php if ($error_pet_name) { ?>
              <input type="text" name="pet_name" value="<?php echo $pet_name; ?>" size="40" class="input-error" />
              <span class="error"><?php echo $error_pet_name; ?></span>
            <?php } else { ?>
              <input type="text" name="pet_name" value="<?php echo $pet_name; ?>" size="40" />
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_pet_type; ?></td>
            <td><select name="filter_id">
              <?php foreach ($filters as $value => $name) { ?>
                <option value="<?php echo $value; ?>"<?php echo ($filter_id === $value) ? ' selected="selected"' : ''; ?>><?php echo $name; ?></option>
              <?php } ?>
            </select>
            <?php if ($error_filter) { ?>
              <span class="error"><?php echo $error_filter; ?></span>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_description; ?><span class="help"><?php echo $help_description_edit; ?></span></td>
            <td><textarea name="description" cols="60" rows="4"><?php echo $description; ?></textarea>
            <?php if ($error_description) { ?>
              <br /><span class="error"><?php echo $error_description; ?></span>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_reference; ?></td>
            <td><?php echo $reference; ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_submitter; ?><span class="help"><?php echo $help_submitter; ?></span></td>
            <td><?php echo $customer_name; ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_email; ?></td>
            <td><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></td>
          </tr>
          <tr>
            <td><?php echo $entry_optin; ?></td>
            <td><?php echo $optin ? $text_yes : $text_no; ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_ip; ?></td>
            <td><?php echo $ip; ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_date_added; ?></td>
            <td><?php echo $date_added; ?></td>
          </tr>
        </table>
      </form>
      <form action="" method="post" enctype="multipart/form-data" id="form-action">
        <input type="hidden" name="selected[]" value="<?php echo $gallery_id; ?>" />
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function gallery(action) {
	$('#form-action').attr('action', action);
	$('#form-action').submit();
}

$('#gallery-delete').on('click', function() {
	$.confirm({
		title: '<?php echo $text_confirm_delete; ?>',
		content: '<?php echo $text_confirm; ?>',
		icon: 'fa fa-question-circle',
		theme: 'light',
		useBootstrap: false,
		boxWidth: 580,
		animation: 'zoom',
		closeAnimation: 'scale',
		opacity: 0.1,
		buttons: {
			confirm: function() {
				gallery('<?php echo $delete; ?>');
			},
			cancel: function() { }
		}
	});
});
//--></script>

<?php echo $footer; ?>