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
      <h1><img src="view/image/mail.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a href="<?php echo $insert; ?>" class="button ripple"><?php echo $button_insert; ?></a>
        <a id="delete" class="button-delete ripple"><?php echo $button_delete; ?></a>
      </div>
    </div>
    <div class="content-body">
    <?php if ($navigation_hi) { ?>
      <div class="pagination" style="margin-bottom:10px;"><?php echo $pagination; ?></div>
    <?php } ?>
    <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="list">
        <thead>
          <tr>
            <td width="1" style="text-align:center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" id="check-all" class="checkbox" />
            <label for="check-all"><span></span></label></td>
            <td class="left"><?php if ($sort === 'name') { ?>
              <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php if ($sort === 'code') { ?>
              <a href="<?php echo $sort_code; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_code; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_code; ?>"><?php echo $column_code; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php if ($sort === 'type') { ?>
              <a href="<?php echo $sort_type; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_type; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_type; ?>"><?php echo $column_type; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="left"><?php echo $column_subject; ?></td>
            <td class="center"><?php echo $column_status; ?></td>
            <td class="center"><?php if ($sort === 'sort_order') { ?>
              <a href="<?php echo $sort_sort_order; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_sort_order; ?></a>
            <?php } else { ?>
              <a href="<?php echo $sort_sort_order; ?>"><?php echo $column_sort_order; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
            <?php } ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
          <tr class="filter">
            <td></td>
            <td><input type="text" name="filter_name" value="<?php echo $filter_name; ?>" /></td>
            <td><input type="text" name="filter_code" value="<?php echo $filter_code; ?>" /></td>
            <td class="left">
              <select name="filter_type">
                <option value=""><?php echo $text_all_types; ?></option>
                <?php foreach ($types as $value => $label) { ?>
                <option value="<?php echo $value; ?>"<?php if ($filter_type === $value) { ?> selected<?php } ?>><?php echo $label; ?></option>
                <?php } ?>
              </select>
            </td>
            <td></td>
            <td class="center">
              <select name="filter_status">
                <option value=""><?php echo $text_all_statuses; ?></option>
                <option value="1"<?php if ($filter_status === '1') { ?> selected<?php } ?>><?php echo $text_enabled; ?></option>
                <option value="0"<?php if ($filter_status === '0') { ?> selected<?php } ?>><?php echo $text_disabled; ?></option>
              </select>
            </td>
            <td></td>
            <td class="right"><a onclick="filter();" class="button-filter ripple"><?php echo $button_filter; ?></a></td>
          </tr>
        <?php if ($templates) { ?>
          <?php foreach ($templates as $template) { ?>
          <tr>
            <td style="text-align:center;"><?php if ($template['selected']) { ?>
              <input type="checkbox" name="selected[]" value="<?php echo $template['template_id']; ?>" id="<?php echo $template['template_id']; ?>" class="checkbox" checked />
              <label for="<?php echo $template['template_id']; ?>"><span></span></label>
            <?php } else { ?>
              <input type="checkbox" name="selected[]" value="<?php echo $template['template_id']; ?>" id="<?php echo $template['template_id']; ?>" class="checkbox" />
              <label for="<?php echo $template['template_id']; ?>"><span></span></label>
            <?php } ?></td>
            <td class="left"><?php echo $template['name']; ?></td>
            <td class="left"><code><?php echo $template['code']; ?></code></td>
            <td class="left"><?php echo isset($types[$template['type']]) ? $types[$template['type']] : $template['type']; ?></td>
            <td class="left"><?php echo $template['subject']; ?></td>
            <?php if ($template['status'] == 1) { ?>
            <td class="center"><span class="enabled"><?php echo $text_enabled; ?></span></td>
            <?php } else { ?>
            <td class="center"><span class="disabled"><?php echo $text_disabled; ?></span></td>
            <?php } ?>
            <td class="center"><?php echo $template['sort_order']; ?></td>
            <td class="right"><?php foreach ($template['action'] as $action) { ?>
              <a href="<?php echo $action['href']; ?>" class="button-form animated fadeIn ripple"><?php echo $action['text']; ?></a>
            <?php } ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </form>
    <?php if ($navigation_lo) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
    <?php } ?>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function filter() {
  var url = 'index.php?route=tool/mail_manager&token=<?php echo $token; ?>';

  var filter_name = $('input[name=\'filter_name\']').val();
  if (filter_name) {
    url += '&filter_name=' + encodeURIComponent(filter_name);
  }

  var filter_code = $('input[name=\'filter_code\']').val();
  if (filter_code) {
    url += '&filter_code=' + encodeURIComponent(filter_code);
  }

  var filter_type = $('select[name=\'filter_type\']').val();
  if (filter_type) {
    url += '&filter_type=' + encodeURIComponent(filter_type);
  }

  var filter_status = $('select[name=\'filter_status\']').val();
  if (filter_status !== '') {
    url += '&filter_status=' + encodeURIComponent(filter_status);
  }

  location = url;
}
//--></script>

<script type="text/javascript"><!--
window.addEventListener('keydown', function(event) {
  if (event.defaultPrevented) { return; }
  if (event.key === 'Enter') { filter(); }
  event.preventDefault();
}, true);
//--></script>

<script type="text/javascript"><!--
$('#delete').on('click', function() {
  $.confirm({
    title: '<?php echo $text_confirm_delete; ?>',
    content: '<?php echo $text_confirm; ?>',
    icon: 'fa fa-question-circle',
    theme: 'light',
    useBootstrap: false,
    boxWidth: 580,
    animation: 'zoom',
    closeAnimation: 'scale',
    opacity: 0.1,
    buttons: {
      confirm: function() {
        $('form').submit();
      },
      cancel: function() { }
    }
  });
});
//--></script>

<?php echo $footer; ?>