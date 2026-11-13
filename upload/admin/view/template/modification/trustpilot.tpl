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
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs">
        <a href="#tab-invitations"><?php echo $tab_invitations; ?></a>
        <a href="#tab-settings"><?php echo $tab_settings; ?></a>
      </div>
      <div id="tab-invitations">
        <p><?php echo $text_month_count; ?></p>
        <?php if ($limit_reached) { ?>
          <div class="attention"><?php echo $text_limit_reached; ?></div>
        <?php } ?>
        <table class="list">
          <thead>
            <tr>
              <td class="left"><?php echo $column_date; ?></td>
              <td class="left"><?php echo $column_order; ?></td>
              <td class="left"><?php echo $column_customer; ?></td>
              <td class="center"><?php echo $column_status; ?></td>
              <td class="left"><?php echo $column_details; ?></td>
            </tr>
          </thead>
          <tbody>
          <?php if ($invitations) { ?>
            <?php foreach ($invitations as $invitation) { ?>
            <tr>
              <td class="left"><?php echo $invitation['date']; ?></td>
              <td class="left"><?php if ($invitation['order']) { ?>
                <a href="<?php echo $invitation['order']; ?>">#<?php echo $invitation['order_id']; ?></a>
              <?php } else { ?>
                <?php echo $invitation['reference']; ?>
              <?php } ?></td>
              <td class="left"><?php echo $invitation['customer']; ?></td>
              <td class="center"><?php if ($invitation['status'] === 'sent') { ?>
                <span class="enabled"><?php echo $invitation['status_text']; ?></span>
              <?php } elseif ($invitation['status'] === 'failed') { ?>
                <span class="disabled"><?php echo $invitation['status_text']; ?></span>
              <?php } else { ?>
                <?php echo $invitation['status_text']; ?>
              <?php } ?></td>
              <td class="left"><?php echo $invitation['message']; ?></td>
            </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td class="center" colspan="5"><?php echo $text_no_results; ?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
      <div id="tab-settings">
        <p><?php echo $text_info; ?></p>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
          <table class="form">
            <tr>
              <td><?php echo $entry_status; ?><span class="help"><?php echo $help_status; ?></span></td>
              <td><?php if ($trustpilot_status) { ?>
                <input type="radio" name="trustpilot_status" value="1" id="trustpilot-status-on" class="radio" checked />
                <label for="trustpilot-status-on"><span><span></span></span><?php echo $text_yes; ?></label>
                <input type="radio" name="trustpilot_status" value="0" id="trustpilot-status-off" class="radio" />
                <label for="trustpilot-status-off"><span><span></span></span><?php echo $text_no; ?></label>
              <?php } else { ?>
                <input type="radio" name="trustpilot_status" value="1" id="trustpilot-status-on" class="radio" />
                <label for="trustpilot-status-on"><span><span></span></span><?php echo $text_yes; ?></label>
                <input type="radio" name="trustpilot_status" value="0" id="trustpilot-status-off" class="radio" checked />
                <label for="trustpilot-status-off"><span><span></span></span><?php echo $text_no; ?></label>
              <?php } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_afs_email; ?><span class="help"><?php echo $help_afs_email; ?></span></td>
              <td><?php if ($error_afs_email) { ?>
                <input type="text" name="trustpilot_afs_email" value="<?php echo $trustpilot_afs_email; ?>" size="50" class="input-error" />
                <span class="error"><?php echo $error_afs_email; ?></span>
              <?php } else { ?>
                <input type="text" name="trustpilot_afs_email" value="<?php echo $trustpilot_afs_email; ?>" size="50" />
              <?php } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_sender_email; ?><span class="help"><?php echo $help_sender_email; ?></span></td>
              <td><?php if ($error_sender_email) { ?>
                <input type="text" name="trustpilot_sender_email" value="<?php echo $trustpilot_sender_email; ?>" size="50" class="input-error" />
                <span class="error"><?php echo $error_sender_email; ?></span>
              <?php } else { ?>
                <input type="text" name="trustpilot_sender_email" value="<?php echo $trustpilot_sender_email; ?>" size="50" />
              <?php } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_monthly_limit; ?><span class="help"><?php echo $help_monthly_limit; ?></span></td>
              <td><?php if ($error_monthly_limit) { ?>
                <input type="text" name="trustpilot_monthly_limit" value="<?php echo $trustpilot_monthly_limit; ?>" size="6" class="input-error" />
                <span class="error"><?php echo $error_monthly_limit; ?></span>
              <?php } else { ?>
                <input type="text" name="trustpilot_monthly_limit" value="<?php echo $trustpilot_monthly_limit; ?>" size="6" />
              <?php } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_trigger_status; ?><span class="help"><?php echo $help_trigger_status; ?></span></td>
              <td><?php echo $trigger_status; ?></td>
            </tr>
          </table>
        </form>
        <table class="form">
          <tr>
            <td><?php echo $entry_test; ?><span class="help"><?php echo $help_test; ?></span></td>
            <td><input type="text" name="test_email" id="test-email" value="" size="40" /> &nbsp;
              <a id="button-trustpilot-test" class="button button-align ripple"><i class="fa fa-envelope"></i> &nbsp; <?php echo $button_test; ?></a>
              <div id="trustpilot-test-result"></div></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script>

<script type="text/javascript"><!--
$('#button-trustpilot-test').on('click', function() {
  $.confirm({
    title: '<?php echo $button_test_title; ?>',
    content: '<?php echo $text_confirm_test; ?>',
    icon: 'fa fa-question-circle',
    theme: 'light',
    useBootstrap: false,
    boxWidth: 580,
    animation: 'zoom',
    closeAnimation: 'scale',
    opacity: 0.1,
    buttons: {
      confirm: function() {
        sendTest();
      },
      cancel: function() {}
    }
  });
});

function sendTest() {
  $.ajax({
    url: 'index.php?route=modification/trustpilot/test&token=<?php echo $token; ?>',
    type: 'post',
    dataType: 'json',
    data: {test_email: $('#test-email').val()},
    beforeSend: function() {
      $('#button-trustpilot-test').after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
      $('#trustpilot-test-result').empty();
    },
    complete: function() {
      $('.wait').remove();
    },
    success: function(json) {
      if (json['error']) {
        $('#trustpilot-test-result').append($('<div class="warning"></div>').text(json['error']));
      }

      if (json['success']) {
        $('#trustpilot-test-result').append($('<div class="success"></div>').text(json['success']));
      }
    },
    error: function(xhr, ajaxOptions, thrownError) {
      $('#trustpilot-test-result').append($('<div class="warning"></div>').text(thrownError + ' ' + xhr.statusText));
    }
  });
}
//--></script>

<?php echo $footer; ?>