<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <p><?php echo $text_description; ?></p>
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="voucher-form">
    <div class="voucher-page">
      <div class="voucher-fields">
        <h2><?php echo $text_step_amount; ?></h2>
        <div class="voucher-presets">
        <?php foreach ($presets as $preset_row) { ?>
          <input type="radio" name="preset" value="<?php echo $preset_row['value']; ?>" id="preset-<?php echo $preset_row['value']; ?>" data-text="<?php echo $preset_row['text']; ?>"<?php echo ((float)$preset === (float)$preset_row['value']) ? ' checked="checked"' : ''; ?> />
          <label for="preset-<?php echo $preset_row['value']; ?>"><?php echo $preset_row['text']; ?></label>
        <?php } ?>
        </div>
        <p class="voucher-custom"><?php echo $text_custom_amount; ?>
          <span class="voucher-currency"><?php echo $symbol_left; ?><input type="text" name="amount" value="<?php echo $amount; ?>" id="voucher-amount" size="8" inputmode="decimal" autocomplete="off" /><?php echo $symbol_right; ?></span>
        </p>
        <?php if ($error_amount) { ?>
          <span class="error"><?php echo $error_amount; ?></span>
        <?php } ?>

        <h2><?php echo $text_step_occasion; ?></h2>
        <div class="voucher-designs">
        <?php foreach ($designs as $design_option) { ?>
          <input type="radio" name="design" value="<?php echo $design_option['value']; ?>" id="design-<?php echo $design_option['value']; ?>" data-text="<?php echo $design_option['name']; ?>"<?php echo ($design === $design_option['value']) ? ' checked="checked"' : ''; ?> />
          <label for="design-<?php echo $design_option['value']; ?>" class="voucher-design-<?php echo $design_option['value']; ?>"><i class="fa voucher-icon-<?php echo $design_option['value']; ?>"></i><span><?php echo $design_option['name']; ?></span></label>
        <?php } ?>
        </div>
        <?php if ($error_design) { ?>
          <span class="error"><?php echo $error_design; ?></span>
        <?php } ?>

        <h2><?php echo $text_step_details; ?></h2>
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_to_name; ?></td>
            <td><input type="text" name="to_name" value="<?php echo $to_name; ?>" id="voucher-to-name" size="30" maxlength="64" />
            <?php if ($error_to_name) { ?>
              <span class="error"><?php echo $error_to_name; ?></span>
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_to_email; ?></td>
            <td><input type="email" name="to_email" value="<?php echo $to_email; ?>" size="30" maxlength="96" />
            <?php if ($error_to_email) { ?>
              <span class="error"><?php echo $error_to_email; ?></span>
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_from_name; ?></td>
            <td><input type="text" name="from_name" value="<?php echo $from_name; ?>" id="voucher-from-name" size="30" maxlength="64" />
            <?php if ($error_from_name) { ?>
              <span class="error"><?php echo $error_from_name; ?></span>
            <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_from_email; ?></td>
            <td><input type="email" name="from_email" value="<?php echo $from_email; ?>" size="30" maxlength="96" />
            <?php if ($error_from_email) { ?>
              <span class="error"><?php echo $error_from_email; ?></span>
            <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_message; ?></td>
            <td><textarea name="message" cols="40" rows="4" id="voucher-message" maxlength="<?php echo $message_max; ?>"><?php echo $message; ?></textarea>
              <span class="voucher-counter"><span id="voucher-left"><?php echo $message_max; ?></span> <?php echo $text_characters; ?></span>
            <?php if ($error_message) { ?>
              <span class="error"><?php echo $error_message; ?></span>
            <?php } ?></td>
          </tr>
        </table>

        <h2><?php echo $text_step_agree; ?></h2>
        <p class="voucher-validity"><i class="fa fa-clock-o"></i> <?php echo $text_validity; ?></p>
        <p class="voucher-agree">
          <input type="checkbox" name="agree" value="1" id="voucher-agree"<?php echo $agree ? ' checked="checked"' : ''; ?> />
          <label for="voucher-agree"><?php echo $text_agree; ?></label>
        </p>
        <p class="voucher-agree">
          <input type="checkbox" name="age" value="1" id="voucher-age"<?php echo $age ? ' checked="checked"' : ''; ?> />
          <label for="voucher-age"><?php echo $text_age; ?></label>
        </p>
        <?php if ($terms) { ?>
          <p class="voucher-terms"><a href="<?php echo $terms; ?>" target="_blank" rel="noopener"><?php echo $text_terms; ?></a></p>
        <?php } ?>
        <div class="buttons">
          <div class="right"><input type="submit" value="<?php echo $button_add; ?>" class="button" /></div>
        </div>
      </div>
      <div class="voucher-side">
        <h2><?php echo $text_preview; ?></h2>
        <div class="voucher-preview voucher-design-<?php echo $design; ?>" id="voucher-preview">
          <i class="fa voucher-preview-icon voucher-icon-<?php echo $design; ?>" id="voucher-preview-icon"></i>
          <div class="voucher-preview-title" id="voucher-preview-title"></div>
          <div class="voucher-preview-amount" id="voucher-preview-amount"></div>
          <div class="voucher-preview-names">
            <span id="voucher-preview-to"></span><br />
            <span id="voucher-preview-from"></span>
          </div>
          <div class="voucher-preview-message" id="voucher-preview-message"></div>
          <div class="voucher-preview-code"><?php echo $text_preview_code; ?></div>
        </div>
      </div>
    </div>
  </form>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
$(document).ready(function() {
	var texts = {
		to: '<?php echo addslashes($preview_to); ?>',
		from: '<?php echo addslashes($preview_from); ?>',
		name: '<?php echo addslashes($preview_name); ?>',
		you: '<?php echo addslashes($preview_you); ?>',
		symbolLeft: '<?php echo addslashes($symbol_left); ?>',
		symbolRight: '<?php echo addslashes($symbol_right); ?>'
	};

	var designs = [<?php echo "'" . implode("','", array_column($designs, 'value')) . "'"; ?>];

	function updatePreview() {
		var design = $('input[name=\'design\']:checked').val() || 'general';
		var custom = $.trim($('#voucher-amount').val());
		var amount = '';

		if (custom !== '') {
			amount = texts.symbolLeft + custom + texts.symbolRight;
		} else {
			amount = $('input[name=\'preset\']:checked').attr('data-text') || '';
		}

		var preview = $('#voucher-preview');

		$.each(designs, function(i, name) {
			preview.removeClass('voucher-design-' + name);
			$('#voucher-preview-icon').removeClass('voucher-icon-' + name);
		});

		preview.addClass('voucher-design-' + design);
		$('#voucher-preview-icon').addClass('voucher-icon-' + design);

		$('#voucher-preview-title').text($('input[name=\'design\']:checked').attr('data-text') || '');
		$('#voucher-preview-amount').text(amount);
		$('#voucher-preview-to').text(texts.to.replace('%s', $.trim($('#voucher-to-name').val()) || texts.name));
		$('#voucher-preview-from').text(texts.from.replace('%s', $.trim($('#voucher-from-name').val()) || texts.you));
		$('#voucher-preview-message').text($('#voucher-message').val());

		$('#voucher-left').text(<?php echo (int)$message_max; ?> - $('#voucher-message').val().length);
	}

	// Typing an amount clears the preset buttons, choosing a preset clears the typed amount
	$('#voucher-amount').on('input', function() {
		if ($.trim($(this).val()) !== '') {
			$('input[name=\'preset\']').prop('checked', false);
		}

		updatePreview();
	});

	$('input[name=\'preset\']').on('change', function() {
		$('#voucher-amount').val('');

		updatePreview();
	});

	$('#voucher-form').on('input change', 'input, textarea', updatePreview);

	updatePreview();
});
//--></script>
<?php echo $footer; ?>