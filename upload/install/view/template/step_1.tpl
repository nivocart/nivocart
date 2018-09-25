<?php echo $header; ?>
<h1>1<span style="font-size:16px;">/4</span> - <?php echo $heading_step_1; ?></h1>
<div id="content">
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" name="terms">
    <div class="terms">
      <?php echo $text_terms; ?>
    </div>
    <div class="buttons">
      <div class="right"><input type="submit" value="<?php echo $button_continue; ?>" class="button animated fadeIn" /></div>
    </div>
  </form>
</div>
<?php echo $footer; ?>