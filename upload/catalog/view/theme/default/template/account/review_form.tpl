<?php if (!$is_modal) { ?>
<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
<?php } ?>
<div class="review-form" id="review-form-box">
<?php if ($is_modal) { ?>
  <h2><?php echo $heading_title; ?></h2>
<?php } else { ?>
  <h1><?php echo $heading_title; ?></h1>
<?php } ?>
<?php if ($form_error) { ?>
  <div class="warning"><?php echo $form_error; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $back; ?>" class="button<?php echo $back_close ? ' review-cancel' : ''; ?>"><?php echo $button_back; ?></a></div>
  </div>
<?php } else { ?>
  <div id="review-warning" aria-live="polite"></div>
  <form id="review-form" action="index.php?route=account/review/save" method="post" novalidate="novalidate">
    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
    <input type="hidden" name="token" value="<?php echo $token; ?>" />
    <div class="hidden">
      <label for="review-website">Website</label>
      <input type="text" name="website" id="review-website" value="" tabindex="-1" autocomplete="off" />
    </div>
    <table class="form">
      <tr>
        <td><?php echo $entry_product; ?></td>
        <td><b><?php echo $product_name; ?></b>
        <?php if ($variant) { ?>
          <br /><span class="help"><?php echo $variant; ?></span>
        <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $entry_orders; ?></td>
        <td><?php echo $orders; ?></td>
      </tr>
      <tr>
        <td><label for="review-author"><?php echo $entry_author; ?></label></td>
        <td><input type="text" id="review-author" value="<?php echo $author; ?>" size="20" readonly="readonly" />
          <br /><span class="help"><?php echo $help_author; ?></span>
        </td>
      </tr>
      <tr>
        <td><?php echo $entry_date; ?></td>
        <td><?php echo $date; ?></td>
      </tr>
      <tr>
        <td><span class="required">*</span> <?php echo $entry_rating; ?></td>
        <td>
          <div class="review-stars" role="radiogroup" aria-label="<?php echo $entry_rating; ?>">
          <?php foreach ($ratings as $star) { ?>
            <input type="radio" name="rating" value="<?php echo $star['value']; ?>" id="review-star-<?php echo $star['value']; ?>" class="sr-only"<?php echo ($star['value'] === $rating) ? ' checked="checked"' : ''; ?> />
            <label for="review-star-<?php echo $star['value']; ?>" title="<?php echo $star['title']; ?>"><span class="sr-only"><?php echo $star['title']; ?></span></label>
          <?php } ?>
          </div>
          <span class="error" id="error-rating"></span>
        </td>
      </tr>
      <tr>
        <td><span class="required">*</span> <label for="review-text"><?php echo $entry_text; ?></label></td>
        <td><textarea name="text" id="review-text" rows="6" maxlength="<?php echo $text_max; ?>"><?php echo $text; ?></textarea>
          <br /><span class="help"><?php echo $help_text; ?></span>
          <span class="help review-count"><span id="review-count">0</span> / <?php echo $text_max; ?></span>
          <br /><span class="error" id="error-text"></span>
        </td>
      </tr>
    </table>
    <div class="buttons">
      <div class="left"><a href="<?php echo $cancel; ?>" class="button-grey review-cancel"><?php echo $button_cancel; ?></a></div>
      <div class="right"><button type="submit" id="button-review" class="button"><?php echo $button_submit; ?></button></div>
    </div>
  </form>
<script type="text/javascript"><!--
$(function() {
	var $form = $('#review-form');

	function updateCount() {
		$('#review-count').text($('#review-text').val().length);
	}

	$('#review-text').off('input.review').on('input.review', updateCount);

	updateCount();

	function showWarning(message) {
		$('#review-warning').empty().append($('<div class="warning"></div>').text(message));
	}

	$form.off('submit.review').on('submit.review', function(e) {
		e.preventDefault();

		$.ajax({
			url: $form.attr('action'),
			type: 'post',
			data: $form.serialize(),
			dataType: 'json',
			beforeSend: function() {
				$form.find('.error').text('');
				$('#button-review').prop('disabled', true);
				$('#review-warning').html('<div class="attention"><img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
			},
			success: function(json) {
				$('#review-warning').empty();

				if (json['success']) {
					window.location = json['redirect'];

					return;
				}

				$('#button-review').prop('disabled', false);

				if (json['error']) {
					if (json['error']['warning']) {
						showWarning(json['error']['warning']);
					}

					if (json['error']['rating']) {
						$('#error-rating').text(json['error']['rating']);
					}

					if (json['error']['text']) {
						$('#error-text').text(json['error']['text']);
					}
				}

				if (json['redirect']) {
					window.location = json['redirect'];
				}
			},
			error: function() {
				$('#button-review').prop('disabled', false);

				showWarning('<?php echo $error_general; ?>');
			}
		});
	});
});
//--></script>
<?php } ?>
</div>
<?php if (!$is_modal) { ?>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?><?php } ?>