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
  <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <?php if ($vault_ready && !$vault_unlocked) { ?>
    <div class="attention"><?php echo $text_locked; ?></div>
  <?php } ?>
  <?php if ($vault_ready && $legacy_total) { ?>
    <div class="warning"><?php echo $vault_unlocked ? $text_legacy : $text_legacy_locked; ?>
    <?php if ($vault_unlocked) { ?>
      <form action="<?php echo $migrate; ?>" method="post" enctype="multipart/form-data" id="form-migrate">
        <a onclick="$('#form-migrate').submit();" class="button ripple"><?php echo $button_migrate; ?></a>
      </form>
    <?php } ?>
    </div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/api.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
      <?php if ($vault_ready) { ?>
        <?php if ($vault_unlocked) { ?>
          <a href="<?php echo $lock; ?>" class="button ripple"><?php echo $button_lock; ?></a>
        <?php } else { ?>
          <a href="<?php echo $unlock; ?>" class="button ripple"><?php echo $button_unlock; ?></a>
        <?php } ?>
        <a href="<?php echo $passphrase; ?>" class="button ripple"><?php echo $button_passphrase; ?></a>
      <?php } ?>
        <a href="<?php echo $insert; ?>" class="button ripple"><?php echo $button_insert; ?></a>
        <a id="delete" class="button-delete ripple"><?php echo $button_delete; ?></a>
      </div>
    </div>
    <div class="content-body">
    <?php if ($navigation_hi) { ?>
      <div class="pagination" style="margin-bottom:10px;"><?php echo $pagination; ?></div>
    <?php } ?>
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
      <div class="toggler" style="padding:0 0 10px 0;">
        <fieldset><p><?php echo $text_api_info; ?></p></fieldset>
      </div>
      <table class="list">
        <thead>
          <tr>
            <td width="1" style="text-align:center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" id="check-all" class="checkbox" />
            <label for="check-all"><span></span></label></td>
            <td class="left"><?php if ($sort === 'name') { ?>
              <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php echo $column_code; ?></td>
            <td class="left"><?php echo $column_status; ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
        <?php if ($api_keys) { ?>
          <?php foreach ($api_keys as $api_key) { ?>
          <tr>
            <td style="text-align:center;"><?php if ($api_key['selected']) { ?>
              <input type="checkbox" name="selected[]" value="<?php echo $api_key['api_key_id']; ?>" id="<?php echo $api_key['api_key_id']; ?>" class="checkbox" checked />
              <label for="<?php echo $api_key['api_key_id']; ?>"><span></span></label>
            <?php } else { ?>
              <input type="checkbox" name="selected[]" value="<?php echo $api_key['api_key_id']; ?>" id="<?php echo $api_key['api_key_id']; ?>" class="checkbox" />
              <label for="<?php echo $api_key['api_key_id']; ?>"><span></span></label>
            <?php } ?></td>
            <td class="left"><?php echo htmlspecialchars($api_key['name'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="left api-key-cell"><span class="api-key-mask">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span><span class="api-key-value"></span></td>
          <?php if ($api_key['status'] === '1') { ?>
            <td class="center"><span class="enabled"><?php echo $text_enabled; ?></span></td>
          <?php } else { ?>
            <td class="center"><span class="disabled"><?php echo $text_disabled; ?></span></td>
          <?php } ?>
            <td class="right">
            <?php if ($vault_unlocked) { ?>
              <a class="button-form reveal ripple" data-id="<?php echo $api_key['api_key_id']; ?>"><?php echo $button_reveal; ?></a>
            <?php } ?>
            <?php foreach ($api_key['action'] as $action) { ?>
              <a href="<?php echo $action['href']; ?>" class="button-form animated fadeIn ripple"><?php echo $action['text']; ?></a>
            <?php } ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="5"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      </form>
    <?php if ($navigation_lo) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
    <?php } ?>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$(document).ready(function() {
	$('.toggler').hide().before('<a id="<?php echo 'toggler'; ?>" class="button ripple" style="margin:10px auto;"><i class="fa fa-info-circle"></i> &nbsp; <?php echo $button_info; ?></a>');
	$('#<?php echo 'toggler'; ?>').click(function() {
		$('.toggler').slideToggle(50);
		return false;
	});
});
//--></script>

<script type="text/javascript"><!--
$('#delete').on('click', function() {
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
				$('#form').submit();
			},
			cancel: function() {}
		}
	});
});
//--></script>

<?php if ($vault_unlocked) { ?>
<script type="text/javascript"><!--
// url->link() returns &amp;-encoded URLs suitable for HTML attributes, so decode it for JavaScript.
var revealUrl = '<?php echo str_replace('&amp;', '&', $reveal); ?>';
var textReveal = <?php echo json_encode($button_reveal); ?>;
var textHide = <?php echo json_encode($button_hide); ?>;
var textError = <?php echo json_encode($error_reveal); ?>;

function hideKey($button) {
	var $cell = $button.closest('tr').find('.api-key-cell');

	clearTimeout($button.data('timer'));

	$cell.find('.api-key-value').text('').hide();
	$cell.find('.api-key-mask').show();

	$button.data('shown', false).text(textReveal);
}

function showError(message) {
	$.alert({
		title: false,
		content: $('<div>').text(message).html(),
		icon: 'fa fa-exclamation-triangle',
		theme: 'light',
		useBootstrap: false,
		boxWidth: 480,
		animation: 'zoom',
		closeAnimation: 'scale',
		opacity: 0.1
	});
}

$(document).ready(function() {
	$('.api-key-value').hide();

	$('.reveal').on('click', function() {
		var $button = $(this);

		if ($button.data('shown')) {
			hideKey($button);

			return false;
		}

		$.ajax({
			url: revealUrl,
			type: 'POST',
			dataType: 'json',
			data: {api_key_id: $button.data('id')},
			success: function(json) {
				if (json.error) {
					showError(json.error);

					return;
				}

				var $cell = $button.closest('tr').find('.api-key-cell');

				$cell.find('.api-key-mask').hide();
				$cell.find('.api-key-value').text(json.code).show();

				$button.data('shown', true).text(textHide);
				$button.data('timer', setTimeout(function() {
					hideKey($button);
				}, 30000));
			},
			error: function() {
				showError(textError);
			}
		});

		return false;
	});
});
//--></script>
<?php } ?>

<?php echo $footer; ?>