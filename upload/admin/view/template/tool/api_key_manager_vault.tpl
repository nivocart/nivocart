<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/api.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_submit; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <div class="attention"><?php echo $text_info; ?></div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" autocomplete="off">
        <table class="form">
        <?php if ($mode === 'change') { ?>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_passphrase_current; ?></td>
            <td><input type="password" name="passphrase_current" value="" size="40" autocomplete="off" /></td>
          </tr>
        <?php } ?>
          <tr>
            <td><span class="required">*</span> <?php echo ($mode === 'change') ? $entry_passphrase_new : $entry_passphrase; ?>
            <?php if ($mode !== 'unlock') { ?>
              <span class="help"><?php echo $help_passphrase; ?></span>
            <?php } ?></td>
            <td><?php if ($error_passphrase) { ?>
              <input type="password" name="passphrase" value="" size="40" class="input-error" autocomplete="off" autofocus />
              <span class="error"><?php echo $error_passphrase; ?></span>
            <?php } else { ?>
              <input type="password" name="passphrase" value="" size="40" autocomplete="off" autofocus />
            <?php } ?></td>
          </tr>
        <?php if ($mode !== 'unlock') { ?>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_passphrase_confirm; ?></td>
            <td><?php if ($error_confirm) { ?>
              <input type="password" name="passphrase_confirm" value="" size="40" class="input-error" autocomplete="off" />
              <span class="error"><?php echo $error_confirm; ?></span>
            <?php } else { ?>
              <input type="password" name="passphrase_confirm" value="" size="40" autocomplete="off" />
            <?php } ?></td>
          </tr>
        <?php } ?>
        </table>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('#form input').on('keydown', function(e) {
	if (e.which === 13) {
		e.preventDefault();

		$('#form').submit();
	}
});
//--></script>

<?php echo $footer; ?>