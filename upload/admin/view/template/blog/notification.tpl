<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php if ($text_install_message) { ?>
  <div class="warning"><?php echo $text_install_message; ?> <a href="<?php echo $install_blog; ?>" class="button"><?php echo $text_upgrade; ?></a></div>
<?php } ?>
</div>
<?php echo $footer; ?>