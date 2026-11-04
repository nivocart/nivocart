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
    <?php if ($navigation_hi) { ?>
      <div class="pagination" style="margin-bottom:10px;"><?php echo $pagination; ?></div>
    <?php } ?>
      <div class="report">
        <div class="left"><i class="fa fa-search" style="font-size:19px;"></i></div>
        <div class="left"><em><?php echo $entry_date_start; ?></em> <input type="text" name="filter_date_start" value="<?php echo $filter_date_start; ?>" id="date-start" size="12" /> <img src="view/image/calendar.png" alt="" /></div>
        <div class="left"><em><?php echo $entry_date_end; ?></em> <input type="text" name="filter_date_end" value="<?php echo $filter_date_end; ?>" id="date-end" size="12" /> <img src="view/image/calendar.png" alt="" /></div>
        <div class="left"><em><?php echo $entry_state; ?></em> <select name="filter_state">
          <option value=""><?php echo $text_all_states; ?></option>
          <?php foreach ($states as $state) { ?>
            <?php if ($state['value'] === $filter_state) { ?>
              <option value="<?php echo $state['value']; ?>" selected="selected"><?php echo $state['name']; ?></option>
            <?php } else { ?>
              <option value="<?php echo $state['value']; ?>"><?php echo $state['name']; ?></option>
            <?php } ?>
          <?php } ?>
        </select></div>
        <div class="right"><a onclick="filter();" class="button-filter ripple"><?php echo $button_filter; ?></a></div>
      </div>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_code; ?></td>
            <td class="left"><?php echo $column_recipient; ?></td>
            <td class="right"><?php echo $column_value; ?></td>
            <td class="right"><?php echo $column_balance; ?></td>
            <td class="left"><?php echo $column_purchased; ?></td>
            <td class="left"><?php echo $column_sent; ?></td>
            <td class="left"><?php echo $column_valid; ?></td>
            <td class="left"><?php echo $column_expiry; ?></td>
            <td class="left"><?php echo $column_redeemed; ?></td>
            <td class="left"><?php echo $column_state; ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
        <?php if ($vouchers) { ?>
          <?php foreach ($vouchers as $voucher) { ?>
            <tr>
              <td class="left"><?php echo $voucher['code']; ?></td>
              <td class="left"><?php echo $voucher['recipient']; ?></td>
              <td class="right"><?php echo $voucher['value']; ?></td>
              <td class="right"><?php echo $voucher['balance']; ?></td>
              <td class="left"><?php echo $voucher['purchased']; ?></td>
              <td class="left"><?php echo $voucher['sent']; ?></td>
              <td class="left"><?php echo $voucher['valid']; ?></td>
              <td class="left"><?php echo $voucher['expiry']; ?></td>
              <td class="left"><?php echo $voucher['redeemed']; ?></td>
              <td class="left"><?php echo $voucher['state']; ?></td>
              <td class="right"><?php foreach ($voucher['action'] as $action) { ?>
                <a href="<?php echo $action['href']; ?>" class="button-form ripple"><?php echo $action['text']; ?></a>
              <?php } ?></td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="11"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    <?php if ($navigation_lo) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
    <?php } ?>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function filter() {
	url = 'index.php?route=report/sale_voucher&token=<?php echo $token; ?>';

	var filter_date_start = $('input[name=\'filter_date_start\']').prop('value');

	if (filter_date_start) {
		url += '&filter_date_start=' + encodeURIComponent(filter_date_start);
	}

	var filter_date_end = $('input[name=\'filter_date_end\']').prop('value');

	if (filter_date_end) {
		url += '&filter_date_end=' + encodeURIComponent(filter_date_end);
	}

	var filter_state = $('select[name=\'filter_state\']').prop('value');

	if (filter_state) {
		url += '&filter_state=' + encodeURIComponent(filter_state);
	}

	location = url;
}
//--></script>

<script type="text/javascript"><!--
window.addEventListener("keydown", function(event) {
	if (event.defaultPrevented) {
		return;
	}

	switch (event.key) {
	case "Enter": filter();
		break;
	default:
		return;
	}

	event.preventDefault();
}, true);
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
	$('#date-start').datepicker({dateFormat: 'yy-mm-dd'});
	$('#date-end').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script>

<?php echo $footer; ?>