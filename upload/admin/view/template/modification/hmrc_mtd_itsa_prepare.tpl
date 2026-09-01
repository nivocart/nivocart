<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/modification.png" alt="" /> <?php echo $heading_itsa_prepare; ?></h1>
      <div class="buttons">
        <a href="<?php echo $back; ?>" class="button-cancel ripple"><?php echo $button_back; ?></a>
      </div>
    </div>
    <div class="content">
      <?php if ($error_warning) { ?>
      <div class="warning"><?php echo $error_warning; ?></div>
      <?php } ?>
      <p style="padding:10px 10px 6px;"><?php echo $text_itsa_prepare_intro; ?></p>
      <p style="padding:0 10px 10px;"><strong><?php echo $period_label; ?></strong></p>
      <form action="<?php echo $action_submit; ?>" method="post" id="form-itsa-return">
        <input type="hidden" name="business_id" value="<?php echo htmlspecialchars($business_id); ?>" />
        <input type="hidden" name="period_start" value="<?php echo htmlspecialchars($period_start); ?>" />
        <input type="hidden" name="period_end" value="<?php echo htmlspecialchars($period_end); ?>" />
        <input type="hidden" name="tax_year" value="<?php echo htmlspecialchars($tax_year); ?>" />
        <!-- Income -->
        <h2><?php echo $text_itsa_income_section; ?></h2>
        <table class="form">
          <tr>
            <td><?php echo $entry_turnover; ?><br /><small><?php echo $text_turnover_help; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="turnover" id="turnover" value="<?php echo $turnover; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_other_income; ?><br /><small><?php echo $text_other_income_help; ?></small></td>
            <td>
              &pound;&nbsp;<input type="number" name="other_income" id="other_income" value="<?php echo $other_income; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
        </table>
        <!-- Allowable Expenses -->
        <h2><?php echo $text_itsa_expenses_section; ?></h2>
        <table class="form">
          <tr>
            <td><?php echo $entry_cost_of_goods; ?></td>
            <td>
              &pound;&nbsp;<input type="number" name="cost_of_goods" value="<?php echo $cost_of_goods; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_admin_costs; ?></td>
            <td>
              &pound;&nbsp;<input type="number" name="admin_costs" value="<?php echo $admin_costs; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_travel_costs; ?></td>
            <td>
              &pound;&nbsp;<input type="number" name="travel_costs" value="<?php echo $travel_costs; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_staff_costs; ?></td>
            <td>
              &pound;&nbsp;<input type="number" name="staff_costs" value="<?php echo $staff_costs; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_advertising_costs; ?></td>
            <td>
              &pound;&nbsp;<input type="number" name="advertising_costs" value="<?php echo $advertising_costs; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_premises_costs; ?></td>
            <td>
              &pound;&nbsp;<input type="number" name="premises_costs" value="<?php echo $premises_costs; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_other_expenses; ?></td>
            <td>
              &pound;&nbsp;<input type="number" name="other_expenses" value="<?php echo $other_expenses; ?>" step="0.01" min="0" style="width:160px;" />
            </td>
          </tr>
        </table>
        <!-- Declaration -->
        <h2><?php echo $text_finalised_label; ?></h2>
        <table class="form">
          <tr>
            <td>
              <input type="checkbox" name="finalised" value="1" id="finalised" class="checkbox" />
              <label for="finalised"><span><span></span></span><?php echo $text_itsa_finalised_confirm; ?></label>
            </td>
          </tr>
          <tr class="highlighted">
            <td>
              <a onclick="$('#form-itsa-return').submit();" class="button-save ripple">
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

<?php echo $footer; ?>