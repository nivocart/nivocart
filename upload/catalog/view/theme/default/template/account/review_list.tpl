<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <p><?php echo $text_intro; ?></p>
  <?php if ($products) { ?>
    <table class="list review-list">
      <thead>
        <tr>
          <td class="left"><?php echo $column_product; ?></td>
          <td class="left"><?php echo $column_orders; ?></td>
          <td class="center"><?php echo $column_review; ?></td>
          <td class="center"><?php echo $column_approval; ?></td>
          <td class="right"><?php echo $column_action; ?></td>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($products as $product) { ?>
        <tr>
          <td class="left" data-label="<?php echo $column_product; ?>">
            <a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
          <?php if ($product['variant']) { ?>
            <br /><span class="help"><?php echo $product['variant']; ?></span>
          <?php } ?>
          </td>
          <td class="left" data-label="<?php echo $column_orders; ?>">
          <?php foreach ($product['orders'] as $order) { ?>
            <a href="<?php echo $order['href']; ?>" class="review-order">#<?php echo $order['order_id']; ?></a>
          <?php } ?>
          </td>
          <td class="center" data-label="<?php echo $column_review; ?>"><?php echo $product['review_text']; ?></td>
          <td class="center" data-label="<?php echo $column_approval; ?>">
            <span class="review-badge review-badge-<?php echo $product['approval_class']; ?>"><?php echo $product['approval_text']; ?></span>
          </td>
          <td class="right" data-label="<?php echo $column_action; ?>">
          <?php if ($product['action'] === 'locked') { ?>
            <span class="button-disabled" aria-disabled="true"><?php echo $product['button_text']; ?></span>
          <?php } else { ?>
            <a href="<?php echo $product['form']; ?>" class="button review-open"><?php echo $product['button_text']; ?></a>
          <?php } ?>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
    <div class="pagination"><?php echo $pagination; ?></div>
  <?php } else { ?>
    <div class="content"><?php echo $text_empty; ?></div>
  <?php } ?>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<script type="text/javascript"><!--
$(function() {
	var $trigger = null;

	function closeReviewModal() {
		$('.review-modal-overlay').remove();
		$('body').removeClass('review-modal-open');

		if ($trigger) {
			$trigger.trigger('focus');
		}
	}

	$(document).on('click', 'a.review-open', function(e) {
		e.preventDefault();

		$trigger = $(this);

		var url = $trigger.attr('href');

		url += (url.indexOf('?') === -1 ? '?' : '&') + 'modal=1';

		var $overlay = $('<div class="review-modal-overlay"><div class="review-modal" role="dialog" aria-modal="true" tabindex="-1"><a href="#" class="review-modal-close" aria-label="<?php echo $button_close; ?>" title="<?php echo $button_close; ?>">&times;</a><div class="review-modal-body"></div></div></div>');

		$('body').addClass('review-modal-open').append($overlay);

		$overlay.find('.review-modal').trigger('focus');

		$overlay.find('.review-modal-body').load(url, function(response, status) {
			if (status === 'error') {
				$(this).html('<div class="warning"><?php echo $error_load; ?></div>');
			}
		});
	});

	$(document).on('click', '.review-modal-close, .review-modal .review-cancel', function(e) {
		e.preventDefault();

		closeReviewModal();
	});

	$(document).on('click', '.review-modal-overlay', function(e) {
		if (e.target === this) {
			closeReviewModal();
		}
	});

	$(document).on('keydown', function(e) {
		if (e.key === 'Escape' && $('.review-modal-overlay').length) {
			closeReviewModal();
		}
	});
});
//--></script>
<?php echo $footer; ?>