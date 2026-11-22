<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/review.png" alt="" /> <?php echo $heading_title; ?></h1>
    </div>
    <div class="content">
      <div class="attention"><?php echo $text_install_message; ?></div>
      <p><a href="<?php echo $install_url; ?>" class="button ripple"><?php echo $text_install_btn; ?></a></p>
    </div>
  </div>
</div>
<?php echo $footer; ?>