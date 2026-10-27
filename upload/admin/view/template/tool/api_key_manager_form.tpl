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
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" autocomplete="off">
      <table class="form">
        <tr>
          <td><span class="required">*</span> <?php echo $entry_name; ?></td>
          <td><?php if ($error_name) { ?>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" size="30" class="input-error" autocomplete="off" />
            <span class="error"><?php echo $error_name; ?></span>
          <?php } else { ?>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" size="30" autocomplete="off" />
          <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_code; ?>
            <span class="help"><?php echo $help_code; ?></span></td>
          <td><?php if ($error_code) { ?>
            <input type="text" name="code" value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" size="60" class="input-error" autocomplete="off" spellcheck="false" />
            <span class="error"><?php echo $error_code; ?></span>
          <?php } else { ?>
            <input type="text" name="code" value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" size="60" autocomplete="off" spellcheck="false" />
          <?php } ?></td>
        </tr>
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="status">
            <?php if ($status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
            <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
            <?php } ?>
          </select>
          <?php if ($error_status) { ?>
            <span class="error"><?php echo $error_status; ?></span>
          <?php } ?></td>
        </tr>
      </table>
    </form>
    </div>
    <div style="height:24px; margin-top:10px;">
      <a href="https://console.developers.google.com/apis/library" target="_blank" rel="noopener noreferrer" class="button"><?php echo $link_google; ?></a>
      <a href="https://developers.facebook.com/" target="_blank" rel="noopener noreferrer" class="button"><?php echo $link_facebook; ?></a>
      <a href="https://dev.twitter.com/products/web" target="_blank" rel="noopener noreferrer" class="button"><?php echo $link_twitter; ?></a>
    </div>
  </div>
</div>
<?php echo $footer; ?>