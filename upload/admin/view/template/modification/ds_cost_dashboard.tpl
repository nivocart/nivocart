<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="location='<?php echo $close_url; ?>';" class="button-cancel ripple"><?php echo $button_close; ?></a>
      </div>
    </div>
    <div class="content">
      <?php if ($success) { ?>
      <div class="success"><?php echo $success; ?></div>
      <?php } ?>
      <?php if ($error) { ?>
      <div class="warning"><?php echo $error; ?></div>
      <?php } ?>
      <div id="tabs" class="htabs">
        <a href="#tab-global"><?php echo $tab_global; ?></a>
        <a href="#tab-channels"><?php echo $tab_channels; ?></a>
        <a href="#tab-help"><?php echo $tab_help; ?></a>
      </div>
      <!-- ================================================================
           TAB: Global Costs
           ================================================================ -->
      <div id="tab-global">
        <h2><?php echo $text_global_costs; ?></h2>
        <p style="padding: 0 10px 10px;"><?php echo $text_global_costs_desc; ?></p>
        <form action="<?php echo $save_global_url; ?>" method="post" id="form-global">
          <table class="form">
            <tr>
              <td><?php echo $entry_hosting_monthly; ?></td>
              <td><input type="number" name="hosting_monthly" value="<?php echo number_format((float)$global['hosting_monthly'], 2, '.', ''); ?>" min="0" step="0.01" style="width:110px;" /> &nbsp;/ month</td>
            </tr>
            <tr>
              <td><?php echo $entry_domain_annual; ?></td>
              <td><input type="number" name="domain_annual" value="<?php echo number_format((float)$global['domain_annual'], 2, '.', ''); ?>" min="0" step="0.01" style="width:110px;" /> &nbsp;/ year</td>
            </tr>
            <tr>
              <td><?php echo $entry_tools_annual; ?><br /><small><?php echo $entry_tools_annual_help; ?></small></td>
              <td><input type="number" name="tools_annual" value="<?php echo number_format((float)$global['tools_annual'], 2, '.', ''); ?>" min="0" step="0.01" style="width:110px;" /> &nbsp;/ year</td>
            </tr>
            <tr>
              <td><?php echo $entry_chargeback_pct; ?><br /><small><?php echo $entry_chargeback_help; ?></small></td>
              <td><input type="number" name="chargeback_pct" value="<?php echo number_format((float)$global['chargeback_pct'], 2, '.', ''); ?>" min="0" max="100" step="0.01" style="width:80px;" /> &nbsp;%</td>
            </tr>
            <tr>
              <td><?php echo $entry_vat_registered; ?></td>
              <td>
                <input type="radio" name="vat_registered" value="1" id="vat-yes" class="radio" <?php echo ($global['vat_registered'] ? 'checked="checked"' : ''); ?> />
                <label for="vat-yes"><span><span></span></span><?php echo $text_vat_yes; ?></label>
                &nbsp;&nbsp;
                <input type="radio" name="vat_registered" value="0" id="vat-no" class="radio" <?php echo (!$global['vat_registered'] ? 'checked="checked"' : ''); ?> />
                <label for="vat-no"><span><span></span></span><?php echo $text_vat_no; ?></label>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_other_monthly; ?></td>
              <td><input type="number" name="other_monthly" value="<?php echo number_format((float)$global['other_monthly'], 2, '.', ''); ?>" min="0" step="0.01" style="width:110px;" /> &nbsp;/ month</td>
            </tr>
            <tr>
              <td><?php echo $entry_other_description; ?></td>
              <td><input type="text" name="other_description" value="<?php echo htmlspecialchars($global['other_description']); ?>" style="width:300px;" placeholder="e.g. Phone, utilities, software" /></td>
            </tr>
            <tr class="highlighted">
              <td></td>
              <td><a onclick="$('#form-global').submit();" class="button-save ripple"><?php echo $button_save; ?></a></td>
            </tr>
          </table>
        </form>
      </div>
      <!-- ================================================================
           TAB: Platform / Channel Costs
           ================================================================ -->
      <div id="tab-channels">
        <h2><?php echo $text_channel_costs; ?></h2>
        <p style="padding: 0 10px 10px;"><?php echo $text_channel_costs_desc; ?></p>
        <?php if ($has_channels) { ?>
          <?php foreach ($channels as $ch) { ?>
          <h3 style="margin: 10px 0 0 10px;"><?php echo htmlspecialchars($ch['name']); ?> <small style="font-weight:normal; opacity:0.7;">(<?php echo htmlspecialchars($ch['provider']); ?>)</small></h3>
          <form action="<?php echo $save_channel_url; ?>" method="post" id="form-channel-<?php echo (int)$ch['channel_id']; ?>">
            <input type="hidden" name="channel_id" value="<?php echo (int)$ch['channel_id']; ?>" />
            <table class="form">
              <tr>
                <td><?php echo $entry_platform_monthly; ?></td>
                <td><input type="number" name="platform_monthly" value="<?php echo number_format((float)$ch['config']['platform_monthly'], 2, '.', ''); ?>" min="0" step="0.01" style="width:110px;" /> &nbsp;/ month</td>
              </tr>
              <tr>
                <td><?php echo $entry_advertising_monthly; ?></td>
                <td><input type="number" name="advertising_monthly" value="<?php echo number_format((float)$ch['config']['advertising_monthly'], 2, '.', ''); ?>" min="0" step="0.01" style="width:110px;" /> &nbsp;/ month</td>
              </tr>
              <tr>
                <td><?php echo $entry_gateway_fee_pct; ?></td>
                <td><input type="number" name="gateway_fee_pct" value="<?php echo number_format((float)$ch['config']['gateway_fee_pct'], 2, '.', ''); ?>" min="0" max="100" step="0.01" style="width:80px;" /> &nbsp;%</td>
              </tr>
              <tr>
                <td><?php echo $entry_gateway_fee_fixed; ?></td>
                <td><input type="number" name="gateway_fee_fixed" value="<?php echo number_format((float)$ch['config']['gateway_fee_fixed'], 2, '.', ''); ?>" min="0" step="0.01" style="width:80px;" /> &nbsp;per transaction</td>
              </tr>
              <tr>
                <td><?php echo $entry_fx_fee_pct; ?><br /><small><?php echo $entry_fx_fee_help; ?></small></td>
                <td><input type="number" name="fx_fee_pct" value="<?php echo number_format((float)$ch['config']['fx_fee_pct'], 2, '.', ''); ?>" min="0" max="100" step="0.01" style="width:80px;" /> &nbsp;%</td>
              </tr>
              <tr>
                <td><?php echo $entry_returns_pct; ?><br /><small><?php echo $entry_returns_help; ?></small></td>
                <td><input type="number" name="returns_pct" value="<?php echo number_format((float)$ch['config']['returns_pct'], 2, '.', ''); ?>" min="0" max="100" step="0.01" style="width:80px;" /> &nbsp;%</td>
              </tr>
              <tr class="highlighted">
                <td></td>
                <td><a onclick="$('#form-channel-<?php echo (int)$ch['channel_id']; ?>').submit();" class="button-save ripple"><?php echo $button_save; ?></a></td>
              </tr>
            </table>
          </form>
          <?php } ?>
        <?php } else { ?>
          <div class="warning"><?php echo $text_no_channels; ?></div>
        <?php } ?>
      </div>
      <!-- ================================================================
           TAB: Help
           ================================================================ -->
      <div id="tab-help">
        <div style="padding: 10px 14px;">
          <p><?php echo $help_intro; ?></p>
          <p><?php echo $help_global; ?></p>
          <p><?php echo $help_channels; ?></p>
          <p><?php echo $help_vat; ?></p>
          <p><?php echo $help_proration; ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script>

<?php echo $footer; ?>