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
  <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/feed.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <h2><?php echo $heading_title; ?></h2>
      <table class="form">
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="pinterest_feed_status">
            <?php if ($pinterest_feed_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
            <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
            <?php } ?>
          </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_data_feed; ?></td>
          <td><textarea cols="40" rows="3" readonly="readonly"><?php echo $data_feed; ?></textarea></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_currency; ?><span class="help"><?php echo $help_currency; ?></span></td>
          <td><input type="text" name="pinterest_feed_currency" value="<?php echo $pinterest_feed_currency; ?>" size="5" maxlength="3" />
            <?php if ($error_currency) { ?>
              <span class="error"><?php echo $error_currency; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_cache_hours; ?><span class="help"><?php echo $help_cache_hours; ?></span></td>
          <td><input type="text" name="pinterest_feed_cache_hours" value="<?php echo $pinterest_feed_cache_hours; ?>" size="5" />
            <?php if ($error_cache_hours) { ?>
              <span class="error"><?php echo $error_cache_hours; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_min_image; ?><span class="help"><?php echo $help_min_image; ?></span></td>
          <td><input type="text" name="pinterest_feed_min_image" value="<?php echo $pinterest_feed_min_image; ?>" size="5" />
            <?php if ($error_min_image) { ?>
              <span class="error"><?php echo $error_min_image; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><?php echo $entry_category_default; ?><span class="help"><?php echo $help_category; ?></span></td>
          <td><input type="text" name="pinterest_feed_category_default" value="<?php echo $pinterest_feed_category_default; ?>" size="10" />
            <?php if ($error_category) { ?>
              <span class="error"><?php echo $error_category; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><?php echo $entry_category_map; ?><span class="help"><?php echo $help_category_map; ?></span></td>
          <td><table class="form">
            <?php foreach ($categories as $category) { ?>
              <tr>
                <td><?php echo $category['indent']; ?><?php echo $category['name']; ?></td>
                <td><input type="text" name="pinterest_feed_category[<?php echo $category['category_id']; ?>]" value="<?php echo $category['value']; ?>" size="10" />
                  <?php if ($category['error']) { ?>
                    <span class="error"><?php echo $category['error']; ?></span>
                  <?php } ?></td>
              </tr>
            <?php } ?>
          </table></td>
        </tr>
        <tr>
          <td><?php echo $entry_cache; ?></td>
          <td><?php echo $cache_info; ?>
            <?php if ($cache_skipped) { ?>
              <br /><?php echo $cache_skipped; ?>
            <?php } ?>
            <?php if ($variants_info) { ?>
              <br /><?php echo $variants_info; ?>
            <?php } ?>
            <br /><a href="<?php echo $clear; ?>" class="button ripple"><?php echo $text_clear_cache; ?></a></td>
        </tr>
      </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>