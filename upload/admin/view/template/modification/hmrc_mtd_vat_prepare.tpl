<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/modification.png" alt="" /> <?php echo $heading_prepare; ?></h1>
      <div class="buttons">
        <a href="<?php echo $back; ?>" class="button-cancel ripple"><?php echo $button_back; ?></a>
      </div>
    </div>
    <div class="content">
      <?php if ($error_warning) { ?>
      <div class="warning"><?php echo $error_warning; ?></div>
      <?php } ?>
      <p style="padding:10px 10px 6px;"><?php echo $text_prepare_intro; ?></p>
      <p style="padding:0 10px 10px;"><strong><?php echo $period_label; ?></strong></p>
      <form action="<?php echo $action_submit; ?>" method="post" id="form-vat-return">
        <input type="hidden" name="period_key" value="<?php echo htmlspecialchars($obligation['period_key']); ?>" />
        <!-- VAT Return Boxes -->
        <h2><?php echo $text_vat_boxes; ?></h2>
        <table class="form">
          <tr>
            <td><?php echo $entry_vat_due_sales; ?><br /><small><?php echo $text_box_auto; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="vat_due_sales" id="box1" value="<?php echo $vat_due_sales; ?>" step="0.01" min="0" style="width:130px;" onchange="recalc();" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_vat_due_acquisitions; ?><br /><small><?php echo $text_box_manual; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="vat_due_acquisitions" id="box2" value="<?php echo $vat_due_acquisitions; ?>" step="0.01" min="0" style="width:130px;" onchange="recalc();" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_total_vat_due; ?><br /><small><?php echo $text_box_derived; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="total_vat_due" id="box3" value="<?php echo $total_vat_due; ?>" step="0.01" min="0" style="width:130px;" readonly="readonly" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_vat_reclaimed; ?><br /><small><?php echo $text_box_manual; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="vat_reclaimed" id="box4" value="<?php echo $vat_reclaimed; ?>" step="0.01" min="0" style="width:130px;" onchange="recalc();" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_net_vat_due; ?><br /><small><?php echo $text_box_derived_diff; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="net_vat_due" id="box5" value="<?php echo $net_vat_due; ?>" step="0.01" style="width:130px;" readonly="readonly" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_total_value_sales; ?><br /><small><?php echo $text_box_auto; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="total_value_sales" id="box6" value="<?php echo $total_value_sales; ?>" step="0.01" min="0" style="width:130px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_total_value_purchases; ?><br /><small><?php echo $text_box_manual; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="total_value_purchases" id="box7" value="<?php echo $total_value_purchases; ?>" step="0.01" min="0" style="width:130px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_total_goods_supplied; ?><br /><small><?php echo $text_box_manual; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="total_goods_supplied" id="box8" value="<?php echo $total_goods_supplied; ?>" step="0.01" min="0" style="width:130px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_total_acquisitions; ?><br /><small><?php echo $text_box_manual; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="total_acquisitions" id="box9" value="<?php echo $total_acquisitions; ?>" step="0.01" min="0" style="width:130px;" />
            </td>
          </tr>
        </table>
        <!-- Declaration -->
        <h2><?php echo $text_finalised_label; ?></h2>
        <table class="form">
          <tr>
            <td>
              <input type="checkbox" name="finalised" value="1" id="finalised" class="checkbox" />
              <label for="finalised"><span><span></span></span><?php echo $text_finalised_confirm; ?></label>
            </td>
          </tr>
          <tr class="highlighted">
            <td>
              <a onclick="$('#form-vat-return').submit();" class="button-save ripple">
                <i class="fa fa-paper-plane"></i> <?php echo $button_submit; ?>
              </a>
              &nbsp;
              <a href="<?php echo $back; ?>" class="button-cancel ripple"><?php echo $button_back; ?></a>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function recalc() {
    var box1 = parseFloat($('#box1').val()) || 0;
    var box2 = parseFloat($('#box2').val()) || 0;
    var box4 = parseFloat($('#box4').val()) || 0;
    var box3 = Math.round((box1 + box2) * 100) / 100;
    var box5 = Math.round((box3 - box4) * 100) / 100;
    $('#box3').val(box3.toFixed(2));
    $('#box5').val(box5.toFixed(2));
}
//--></script>

<?php echo $footer; ?>