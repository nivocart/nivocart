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
      <h1><img src="view/image/mail.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a onclick="location='<?php echo $cancel; ?>';" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <!-- Two-column layout: form left, placeholder sidebar right -->
      <div style="display:flex; gap:20px; align-items:flex-start;">
        <!-- ── Main form ── -->
        <div style="flex:1; min-width:0;">
          <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
            <table class="form">
              <tr class="highlighted">
                <td><?php echo $entry_type; ?></td>
                <td>
                  <select name="type" id="field-type">
                    <?php foreach ($types as $value => $label) { ?>
                    <option value="<?php echo $value; ?>"<?php if ($type === $value) { ?> selected<?php } ?>><?php echo $label; ?></option>
                    <?php } ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo $entry_code; ?> <span class="required">*</span><span class="help"><?php echo $help_code; ?></span></td>
                <td>
                  <input type="text" name="code" value="<?php echo $code; ?>" placeholder="e.g. newsletter, order_confirm" style="width:260px;" />
                  <?php if ($error_code) { ?>
                  <span class="error"><?php echo $error_code; ?></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $entry_name; ?> <span class="required">*</span></td>
                <td>
                  <input type="text" name="name" value="<?php echo $name; ?>" style="width:320px;" />
                  <?php if ($error_name) { ?>
                  <span class="error"><?php echo $error_name; ?></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $entry_subject; ?> <span class="required">*</span></td>
                <td>
                  <input type="text" name="subject" id="field-subject" value="<?php echo $subject; ?>" style="width:420px;" />
                  <?php if ($error_subject) { ?>
                  <span class="error"><?php echo $error_subject; ?></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $entry_store; ?><span class="help"><?php echo $help_store; ?></span></td>
                <td>
                  <div id="store_ids" class="scrollbox-store">
                    <?php $class = 'even'; ?>
                    <div class="<?php echo $class; ?>">
                      <input type="checkbox" name="mail_store[]" value="0"<?php if (in_array(0, $mail_store)) { ?> checked="checked"<?php } ?> />
                      <?php echo $text_default; ?>
                    </div>
                    <?php foreach ($stores as $store) { ?>
                    <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                    <div class="<?php echo $class; ?>">
                      <input type="checkbox" name="mail_store[]" value="<?php echo $store['store_id']; ?>"<?php if (in_array($store['store_id'], $mail_store)) { ?> checked="checked"<?php } ?> />
                      <?php echo $store['name']; ?>
                    </div>
                    <?php } ?>
                  </div>
                  <a onclick="$(this).parent().find(':checkbox').prop('checked', true);" class="button-select"></a><a onclick="$(this).parent().find(':checkbox').prop('checked', false);" class="button-unselect"></a>
                </td>
              </tr>
              <tr>
                <td><?php echo $entry_language; ?></td>
                <td>
                  <select name="language_id">
                    <?php foreach ($languages as $language) { ?>
                    <option value="<?php echo $language['language_id']; ?>"<?php if ((int)$language_id === (int)$language['language_id']) { ?> selected<?php } ?>><?php echo $language['name']; ?></option>
                    <?php } ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo $entry_sort_order; ?></td>
                <td>
                  <input type="text" name="sort_order" value="<?php echo $sort_order; ?>" style="width:60px;" />
                </td>
              </tr>
              <tr class="highlighted">
                <td><?php echo $entry_status; ?></td>
                <td>
                  <select name="status">
                    <option value="1"<?php if ((int)$status === 1) { ?> selected<?php } ?>><?php echo $text_enabled; ?></option>
                    <option value="0"<?php if ((int)$status === 0) { ?> selected<?php } ?>><?php echo $text_disabled; ?></option>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo $entry_body; ?> <span class="required">*</span></td>
                <td>
                  <textarea name="body" id="field-body" style="width:100%; height:420px;"><?php echo $body; ?></textarea>
                  <?php if ($error_body) { ?>
                  <span class="error"><?php echo $error_body; ?></span>
                  <?php } ?>
                </td>
              </tr>
            </table>
          </form>
        </div>
        <!-- ── Placeholder sidebar ── -->
        <div id="placeholder-sidebar">
          <h3><?php echo $text_placeholders; ?></h3>
          <p><?php echo $text_placeholder_hint; ?></p>
          <?php foreach ($placeholders as $group => $tokens) { ?>
          <div class="placeholder-group">
            <strong><?php echo $group; ?></strong>
            <?php foreach ($tokens as $token => $description) { ?>
            <a href="#" class="placeholder-token" data-token="<?php echo $token; ?>" title="<?php echo $description; ?>"><?php echo $token; ?>
              <span><?php echo $description; ?></span>
            </a>
            <?php } ?>
          </div>
          <?php } ?>
          <hr />
          <p><?php echo $text_placeholder_token; ?></p>
        </div>
      </div><!-- /flex wrapper -->
    </div>
  </div>
</div>

<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>

<script type="text/javascript"><!--
// ── CKEditor instance ──────────────────────────────────────────────
CKEDITOR.replace('field-body', {
  filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});

// ── Sync CKEditor → textarea on any form submission (Save or Apply)
$('#form').on('submit', function() {
  if (CKEDITOR.instances['field-body']) {
    $('textarea[name="body"]').val(CKEDITOR.instances['field-body'].getData());
  }
});

// ── Placeholder sidebar — insert token at CKEditor cursor ──────────
$(document).on('click', '.placeholder-token', function(e) {
  e.preventDefault();
  var token = $(this).data('token');
  var editor = CKEDITOR.instances['field-body'];
  if (editor) {
    editor.insertText(token);
  } else {
    var ta = document.getElementById('field-body');
    ta.value += token;
  }
});
//--></script>

<?php echo $footer; ?>