<?php echo $header; ?>
<h1><?php echo $heading_prepare; ?></h1>
<div id="content">
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" name="prepare">
    <div class="terms">
      <?php echo $text_prepare; ?>
    </div>
    <div class="buttons">
      <div class="right">
        <input type="submit" value="<?php echo $button_continue; ?>" class="button animated fadeIn" />
      </div>
    </div>
  </form>
</div>
<?php echo $footer; ?>