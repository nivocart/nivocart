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
  <?php if ($attention) { ?>
    <div class="attention"><?php echo $attention; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/stock-status.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-delete ripple"><?php echo $button_delete; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content-body">
    <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form" name="images">
      <table class="list">
        <thead>
          <tr>
            <td width="1" style="text-align:center;">
              <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" id="check-all" class="checkbox" />
              <label for="check-all"><span></span></label>
            </td>
            <td class="left"><?php echo $column_name; ?></td>
            <td class="left"><?php echo $column_size; ?></td>
            <td class="left"><?php echo $column_modified; ?></td>
          </tr>
        </thead>
        <tbody>
        <?php if ($cache_images) { ?>
          <?php foreach ($cache_images as $cache_image) { ?>
          <tr>
            <td style="text-align:center;">
              <input type="checkbox" name="selected[]" value="<?php echo $cache_image['name']; ?>" id="<?php echo $cache_image['name']; ?>" class="checkbox"<?php echo $cache_image['selected'] ? ' checked' : ''; ?> />
              <label for="<?php echo $cache_image['name']; ?>"><span></span></label>
            </td>
            <td class="left"><?php echo $cache_image['name']; ?></td>
            <td class="left"><?php echo $cache_image['size']; ?></td>
            <td class="left"><?php echo $cache_image['modified']; ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="4"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
        <?php if ($cache_images) { ?>
        <tfoot>
          <tr>
            <td colspan="2" class="left"><strong><?php echo $text_total_size; ?></strong></td>
            <td colspan="2" class="left"><strong><?php echo $cache_total_size; ?></strong></td>
          </tr>
        </tfoot>
        <?php } ?>
      </table>
    </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>