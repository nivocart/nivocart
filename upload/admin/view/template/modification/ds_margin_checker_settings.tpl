<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/offer.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a href="<?php echo $dashboard_url; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <?php if ($error_warning) { ?>
      <div class="warning"><?php echo $error_warning; ?></div>
      <?php } ?>
      <h2><?php echo $tab_settings; ?></h2>
      <form action="<?php echo $save_url; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_threshold; ?><span class="help"><?php echo $help_threshold; ?></span></td>
            <td><input type="number" name="threshold" value="<?php echo number_format((float)$threshold, 2, '.', ''); ?>" min="0" max="99" step="0.5" size="5" /> %</td>
          </tr>
          <tr>
            <td><?php echo $entry_breakpoint; ?><span class="help"><?php echo $help_breakpoint; ?></span></td>
            <td>
              <select name="breakpoint">
                <?php foreach ($breakpoint_options as $option) { ?>
                <option value="<?php echo $option['value']; ?>"<?php echo $option['selected'] ? ' selected="selected"' : ''; ?>><?php echo $option['label']; ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_margin_high; ?><span class="help"><?php echo $help_margin_high; ?></span></td>
            <td>
              <select name="margin_high">
                <?php foreach ($margin_high_options as $option) { ?>
                <option value="<?php echo $option['value']; ?>"<?php echo $option['selected'] ? ' selected="selected"' : ''; ?>><?php echo $option['label']; ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_min_profit; ?><span class="help"><?php echo $help_min_profit; ?></span></td>
            <td>
              <select name="min_profit">
                <?php foreach ($min_profit_options as $option) { ?>
                <option value="<?php echo $option['value']; ?>"<?php echo $option['selected'] ? ' selected="selected"' : ''; ?>><?php echo $option['label']; ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_rounding; ?><span class="help"><?php echo $help_rounding; ?></span></td>
            <td>
              <select name="price_rounding">
                <?php foreach ($rounding_options as $value => $label) { ?>
                <option value="<?php echo $value; ?>"<?php echo ($value === $price_rounding) ? ' selected="selected"' : ''; ?>><?php echo $label; ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_samples; ?><span class="help"><?php echo $help_samples; ?></span></td>
            <td>
              <table class="list">
                <thead>
                  <tr>
                    <td class="right"><?php echo $column_total_cost; ?></td>
                    <td class="right"><?php echo $column_sale_price; ?></td>
                    <td class="right"><?php echo $column_profit; ?></td>
                    <td class="right"><?php echo $column_margin_pct; ?></td>
                  </tr>
                </thead>
                <tbody id="mc-sample-body"></tbody>
              </table>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<script>
var mcSampleUrl = <?php echo json_encode($sample_url); ?>;
var mcSampleError = <?php echo json_encode($error_server); ?>;
var mcSampleTimer = null;

function mcLoadSamples() {
  $.ajax({
    url: mcSampleUrl,
    type: 'POST',
    dataType: 'json',
    data: $('#form').serialize(),
    success: function(res) {
      var $body = $('#mc-sample-body').empty();

      $.each(res.rows || [], function(i, row) {
        $('<tr></tr>')
          .append($('<td class="right"></td>').text(row.cost))
          .append($('<td class="right"></td>').text(row.price))
          .append($('<td class="right"></td>').text(row.profit))
          .append($('<td class="right"></td>').text(row.margin))
          .appendTo($body);
      });
    },
    error: function(xhr) {
      $('#mc-sample-body').empty().append($('<tr></tr>').append($('<td colspan="4" class="center"></td>').text(mcSampleError + ' ' + xhr.status)));
    }
  });
}

$('#form select, #form input[name="threshold"]').on('change keyup', function() {
  clearTimeout(mcSampleTimer);

  mcSampleTimer = setTimeout(mcLoadSamples, 250);
});

mcLoadSamples();
</script>
<?php echo $footer; ?>