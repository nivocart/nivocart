<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="box-content">
<?php } ?>
<?php if ($consent) { ?>
    <?php if ($load_script) { ?>
    <script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
    <?php } ?>
    <div class="trustpilot-widget" data-locale="<?php echo htmlspecialchars($locale, ENT_QUOTES, 'UTF-8'); ?>" data-template-id="<?php echo htmlspecialchars($template_id, ENT_QUOTES, 'UTF-8'); ?>" data-businessunit-id="<?php echo htmlspecialchars($business_id, ENT_QUOTES, 'UTF-8'); ?>" data-style-height="<?php echo htmlspecialchars($height, ENT_QUOTES, 'UTF-8'); ?>" data-style-width="<?php echo htmlspecialchars($width, ENT_QUOTES, 'UTF-8'); ?>">
      <a href="<?php echo htmlspecialchars($review_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Trustpilot</a>
    </div>
<?php } else { ?>
    <a href="<?php echo htmlspecialchars($review_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo $text_review; ?></a>
<?php } ?>
<?php if ($theme) { ?>
  </div>
</div>
<?php } ?>