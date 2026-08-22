<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
    </div>
    <div class="content-body">
      <div class="dscc-alert dscc-alert-warning" style="margin:20px;">
        <strong><?php echo $text_install_message; ?></strong><br /><br />
        <a href="<?php echo $install_url; ?>" class="button ripple"><?php echo $text_install_btn; ?></a>
      </div>
    </div>
  </div>
</div>
<link rel="stylesheet" href="view/stylesheet/ds_cost_calculator_dashboard.css" />
<?php echo $footer; ?>