<?php echo $header; ?>
<h1>4<span style="font-size:16px;">/4</span> - <?php echo $heading_step_4; ?></h1>
<div id="content">
  <div class="success"><?php echo $text_congratulation; ?></div>
  <h2><?php echo $heading_next; ?></h2>
  <fieldset>
    <p><b><?php echo $heading_setting; ?></b></p>
    <p><?php echo $help_setting; ?></p>
    <br />
    <p><b><?php echo $heading_security; ?></b></p>
    <p><?php echo $help_security; ?></p>
    <br />
    <p><b><?php echo $heading_server; ?></b></p>
    <p><?php echo $help_server; ?></p>
  </fieldset>
  <div><a href="../admin/" class="button-go animated fadeIn"><i class="fa fa-lock"></i> &nbsp; <?php echo $text_login; ?></a></div>
</div>
<?php echo $footer; ?>