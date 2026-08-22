<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/report.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="location='<?php echo $close; ?>';" class="button-cancel ripple"><?php echo $button_close; ?></a>
      </div>
    </div>
    <div class="content-body">
      <?php if (!isset($has_results)) { ?>
        <!-- DS tables not installed -->
        <div style="padding:20px; background:#fff8e1; border:1px solid #ffe082; border-radius:4px; color:#7a5f00; margin-bottom:16px;">
          <?php echo $text_no_ds_tables; ?>
        </div>
      <?php } else { ?>
      <?php if (!$has_cost_config) { ?>
      <div style="padding:10px 14px; background:#eaf3fb; border:1px solid #b8d9f0; border-radius:4px; color:#2e5c8a; margin-bottom:12px; font-size:13px;">
        <?php echo $text_no_cost_config; ?>
      </div>
      <?php } ?>
      <?php if ($navigation_hi) { ?>
      <div class="pagination" style="margin-bottom:10px;"><?php echo $pagination; ?></div>
      <?php } ?>
      <!-- Filter bar -->
      <div class="report">
        <div class="left"><i class="fa fa-search" style="font-size:19px;"></i></div>
        <div class="left"><em><?php echo $entry_date_start; ?></em> <input type="text" name="filter_date_start" value="<?php echo $filter_date_start; ?>" id="date-start" size="12" /> <img src="view/image/calendar.png" alt="" /></div>
        <div class="left"><em><?php echo $entry_date_end; ?></em> <input type="text" name="filter_date_end" value="<?php echo $filter_date_end; ?>" id="date-end" size="12" /> <img src="view/image/calendar.png" alt="" /></div>
        <div class="left"><em><?php echo $entry_group; ?></em>
          <select name="filter_group">
          <?php foreach ($groups as $group) { ?>
            <option value="<?php echo $group['value']; ?>"<?php echo ($group['value'] === $filter_group) ? ' selected="selected"' : ''; ?>><?php echo $group['text']; ?></option>
          <?php } ?>
          </select>
        </div>
        <div class="left"><em><?php echo $entry_channel; ?></em>
          <select name="filter_channel_id">
            <option value="0"><?php echo $text_all_channels; ?></option>
          <?php foreach ($channels as $channel) { ?>
            <option value="<?php echo $channel['channel_id']; ?>"<?php echo ((int)$channel['channel_id'] === (int)$filter_channel_id) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($channel['name']); ?></option>
          <?php } ?>
          </select>
        </div>
        <div class="left"><em><?php echo $entry_status; ?></em>
          <select name="filter_order_status_id">
            <option value="0"><?php echo $text_all_status; ?></option>
          <?php foreach ($order_statuses as $order_status) { ?>
            <option value="<?php echo $order_status['order_status_id']; ?>"<?php echo ((int)$order_status['order_status_id'] === (int)$filter_order_status_id) ? ' selected="selected"' : ''; ?>><?php echo $order_status['name']; ?></option>
          <?php } ?>
          </select>
        </div>
        <div class="right"><a onclick="filter();" class="button-filter ripple"><?php echo $button_filter; ?></a></div>
      </div>
      <!-- Main results table -->
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_date_start; ?></td>
            <td class="left"><?php echo $column_date_end; ?></td>
            <td class="left"><?php echo $column_channel; ?></td>
            <td class="right"><?php echo $column_orders; ?></td>
            <td class="right"><?php echo $column_revenue; ?></td>
            <td class="right"><?php echo $column_product_cost; ?></td>
            <td class="right"><?php echo $column_gateway_fees; ?></td>
            <td class="right"><?php echo $column_returns_provision; ?></td>
            <td class="right"><?php echo $column_gross_profit; ?></td>
            <td class="right"><?php echo $column_gross_margin; ?></td>
          </tr>
        </thead>
        <tbody>
        <?php if ($has_results) { ?>
          <?php foreach ($rows as $row) { ?>
          <tr>
            <td class="left"><?php echo $row['date_start']; ?></td>
            <td class="left"><?php echo $row['date_end']; ?></td>
            <td class="left"><?php echo htmlspecialchars($row['channel_name']); ?></td>
            <td class="right"><?php echo $row['orders']; ?></td>
            <td class="right"><?php echo $row['revenue']; ?></td>
            <td class="right"><?php echo $row['product_cost']; ?></td>
            <td class="right"><?php echo $row['gateway_fees']; ?></td>
            <td class="right"><?php echo $row['returns_provision']; ?></td>
            <td class="right"<?php echo ($row['gross_profit_raw'] < 0) ? ' style="color:#c0392b;"' : ''; ?>><?php echo $row['gross_profit']; ?></td>
            <td class="right"><?php echo $row['gross_margin_pct']; ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="10"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td class="left" colspan="3"><strong><?php echo $text_total; ?></strong></td>
            <td class="right"></td>
            <td class="right"><strong><?php echo $total_revenue; ?></strong></td>
            <td class="right"><strong><?php echo $total_product_cost; ?></strong></td>
            <td class="right"><strong><?php echo $total_gateway_fees; ?></strong></td>
            <td class="right"><strong><?php echo $total_returns; ?></strong></td>
            <td class="right"><strong><?php echo $total_gross_profit; ?></strong></td>
            <td class="right"><strong><?php echo $total_gross_margin; ?></strong></td>
          </tr>
        </tfoot>
      </table>
      <?php if ($navigation_lo) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
      <?php } ?>
      <!-- Overhead summary panel -->
      <div style="margin-top:24px; border:1px solid #d9dde4; border-radius:4px; overflow:hidden;">
        <div style="background:#f0f3f7; border-bottom:1px solid #d9dde4; padding:9px 16px;">
          <strong style="font-size:13px; color:#2e4a6e;"><?php echo $text_overhead_summary; ?></strong>
        </div>
        <table class="list" style="margin:0;">
          <tbody>
            <tr>
              <td class="left" style="width:60%;"><?php echo $text_hosting_share; ?></td>
              <td class="right"><?php echo $overhead['hosting']; ?></td>
            </tr>
            <tr>
              <td class="left"><?php echo $text_domain_share; ?></td>
              <td class="right"><?php echo $overhead['domain']; ?></td>
            </tr>
            <tr>
              <td class="left"><?php echo $text_tools_share; ?></td>
              <td class="right"><?php echo $overhead['tools']; ?></td>
            </tr>
            <tr>
              <td class="left"><?php echo $text_platform_share; ?></td>
              <td class="right"><?php echo $overhead['platform']; ?></td>
            </tr>
            <tr>
              <td class="left"><?php echo $text_advertising_share; ?></td>
              <td class="right"><?php echo $overhead['advertising']; ?></td>
            </tr>
            <tr>
              <td class="left"><?php echo $text_chargeback_share; ?></td>
              <td class="right"><?php echo $overhead['chargeback']; ?></td>
            </tr>
            <tr>
              <td class="left"><?php echo $text_other_share; ?></td>
              <td class="right"><?php echo $overhead['other']; ?></td>
            </tr>
          </tbody>
          <tfoot>
            <tr style="background:#f5f6f8;">
              <td class="left"><strong><?php echo $text_total_overhead; ?></strong></td>
              <td class="right"><strong><?php echo $overhead['total']; ?></strong></td>
            </tr>
            <tr style="background:#e8f0fa;">
              <td class="left"><strong style="font-size:14px; color:#1a3d6b;"><?php echo $text_net_profit; ?></strong></td>
              <td class="right"><strong style="font-size:14px; color:<?php echo ($net_profit_raw < 0) ? '#c0392b' : '#1d6a3c'; ?>;"><?php echo $net_profit; ?></strong></td>
            </tr>
            <tr style="background:#e8f0fa;">
              <td class="left"><strong style="color:#1a3d6b;"><?php echo $text_net_margin; ?></strong></td>
              <td class="right"><strong style="color:<?php echo ($net_profit_raw < 0) ? '#c0392b' : '#1d6a3c'; ?>;"><?php echo $net_margin_pct; ?></strong></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <?php } ?>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function filter() {
    var url = 'index.php?route=report/sale_dropship_profit&token=<?php echo $token; ?>';

    var s = $('input[name="filter_date_start"]').val();
    if (s) url += '&filter_date_start=' + encodeURIComponent(s);

    var e = $('input[name="filter_date_end"]').val();
    if (e) url += '&filter_date_end=' + encodeURIComponent(e);

    var g = $('select[name="filter_group"]').val();
    if (g) url += '&filter_group=' + encodeURIComponent(g);

    var ch = $('select[name="filter_channel_id"]').val();
    if (ch && ch !== '0') url += '&filter_channel_id=' + encodeURIComponent(ch);

    var st = $('select[name="filter_order_status_id"]').val();
    if (st && st !== '0') url += '&filter_order_status_id=' + encodeURIComponent(st);

    location = url;
}
//--></script>

<script type="text/javascript"><!--
window.addEventListener("keydown", function(event) {
    if (event.defaultPrevented) {
		return;
	}

    if (event.key === "Enter") {
		filter(); event.preventDefault();
	}
}, true);
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
    $('#date-start').datepicker({dateFormat: 'yy-mm-dd'});
    $('#date-end').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script>

<?php echo $footer; ?>