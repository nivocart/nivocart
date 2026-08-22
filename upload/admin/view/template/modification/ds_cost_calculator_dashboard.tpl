<?php echo $header; ?>
<link rel="stylesheet" href="view/stylesheet/ds_cost_calculator_dashboard.css" />
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
    <div class="content-body">
      <?php if ($success) { ?>
      <div class="dscc-alert dscc-alert-success"><?php echo $success; ?></div>
      <?php } ?>
      <?php if ($error) { ?>
      <div class="dscc-alert dscc-alert-warning"><?php echo $error; ?></div>
      <?php } ?>
      <!-- Tab bar -->
      <div class="dscc-tabs">
        <div class="dscc-tab active" onclick="dsccShowTab('global', this)"><?php echo $tab_global; ?></div>
        <div class="dscc-tab" onclick="dsccShowTab('channels', this)"><?php echo $tab_channels; ?></div>
        <div class="dscc-tab" onclick="dsccShowTab('help', this)"><?php echo $tab_help; ?></div>
      </div>
      <!-- ================================================================
           PANEL: Global Costs
           ================================================================ -->
      <div id="dscc-panel-global" class="dscc-panel active">
        <div class="dscc-section-head">
          <h3><?php echo $text_global_costs; ?></h3>
          <p><?php echo $text_global_costs_desc; ?></p>
        </div>
        <form action="<?php echo $save_global_url; ?>" method="post">
          <div class="dscc-card">
            <div class="dscc-form-row">
              <label><?php echo $entry_hosting_monthly; ?></label>
              <div class="dscc-input-wrap">
                <input type="number" name="hosting_monthly" value="<?php echo number_format((float)$global['hosting_monthly'], 2, '.', ''); ?>" min="0" step="0.01" />
                <span class="dscc-suffix">/ month</span>
              </div>
            </div>
            <div class="dscc-form-row">
              <label><?php echo $entry_domain_annual; ?></label>
              <div class="dscc-input-wrap">
                <input type="number" name="domain_annual" value="<?php echo number_format((float)$global['domain_annual'], 2, '.', ''); ?>" min="0" step="0.01" />
                <span class="dscc-suffix">/ year</span>
              </div>
            </div>
            <div class="dscc-form-row">
              <label>
                <?php echo $entry_tools_annual; ?><br />
                <span class="dscc-help-text"><?php echo $entry_tools_annual_help; ?></span>
              </label>
              <div class="dscc-input-wrap">
                <input type="number" name="tools_annual" value="<?php echo number_format((float)$global['tools_annual'], 2, '.', ''); ?>" min="0" step="0.01" />
                <span class="dscc-suffix">/ year</span>
              </div>
            </div>
            <div class="dscc-form-row">
              <label>
                <?php echo $entry_chargeback_pct; ?><br />
                <span class="dscc-help-text"><?php echo $entry_chargeback_help; ?></span>
              </label>
              <div class="dscc-input-wrap">
                <input type="number" name="chargeback_pct" value="<?php echo number_format((float)$global['chargeback_pct'], 2, '.', ''); ?>" min="0" max="100" step="0.01" />
                <span class="dscc-suffix">%</span>
              </div>
            </div>
            <div class="dscc-form-row">
              <label><?php echo $entry_vat_registered; ?></label>
              <div class="dscc-input-wrap">
                <div class="dscc-radio-group">
                  <label>
                    <input type="radio" name="vat_registered" value="1" <?php echo ($global['vat_registered'] ? 'checked="checked"' : ''); ?> />
                    <?php echo $text_vat_yes; ?>
                  </label>
                  <label>
                    <input type="radio" name="vat_registered" value="0" <?php echo (!$global['vat_registered'] ? 'checked="checked"' : ''); ?> />
                    <?php echo $text_vat_no; ?>
                  </label>
                </div>
              </div>
            </div>
            <div class="dscc-form-row">
              <label><?php echo $entry_other_monthly; ?></label>
              <div class="dscc-input-wrap">
                <input type="number" name="other_monthly" value="<?php echo number_format((float)$global['other_monthly'], 2, '.', ''); ?>" min="0" step="0.01" />
                <span class="dscc-suffix">/ month</span>
              </div>
            </div>
            <div class="dscc-form-row">
              <label><?php echo $entry_other_description; ?></label>
              <div class="dscc-input-wrap">
                <input type="text" name="other_description" value="<?php echo htmlspecialchars($global['other_description']); ?>" style="width:320px;" placeholder="e.g. Phone, utilities, software" />
              </div>
            </div>
          </div>
          <div class="dscc-form-actions" style="margin-bottom:20px;">
            <button type="submit" class="button ripple"><?php echo $button_save; ?></button>
          </div>
        </form>
      </div>
      <!-- ================================================================
           PANEL: Platform / Channel Costs
           ================================================================ -->
      <div id="dscc-panel-channels" class="dscc-panel">
        <div class="dscc-section-head" style="border-radius:4px 4px 0 0; margin-bottom:16px;">
          <h3><?php echo $text_channel_costs; ?></h3>
          <p><?php echo $text_channel_costs_desc; ?></p>
        </div>
        <?php if ($has_channels) { ?>
          <?php foreach ($channels as $ch) { ?>
          <div class="dscc-channel-card">
            <div class="dscc-channel-head">
              <h4><?php echo htmlspecialchars($ch['name']); ?></h4>
              <span class="dscc-channel-badge"><?php echo htmlspecialchars($ch['provider']); ?></span>
            </div>
            <div class="dscc-channel-body">
              <form action="<?php echo $save_channel_url; ?>" method="post">
                <input type="hidden" name="channel_id" value="<?php echo (int)$ch['channel_id']; ?>" />
                <div class="dscc-form-row">
                  <label><?php echo $entry_platform_monthly; ?></label>
                  <div class="dscc-input-wrap">
                    <input type="number" name="platform_monthly" value="<?php echo number_format((float)$ch['config']['platform_monthly'], 2, '.', ''); ?>" min="0" step="0.01" />
                    <span class="dscc-suffix">/ month</span>
                  </div>
                </div>
                <div class="dscc-form-row">
                  <label><?php echo $entry_advertising_monthly; ?></label>
                  <div class="dscc-input-wrap">
                    <input type="number" name="advertising_monthly" value="<?php echo number_format((float)$ch['config']['advertising_monthly'], 2, '.', ''); ?>" min="0" step="0.01" />
                    <span class="dscc-suffix">/ month</span>
                  </div>
                </div>
                <div class="dscc-form-row">
                  <label><?php echo $entry_gateway_fee_pct; ?></label>
                  <div class="dscc-input-wrap">
                    <input type="number" name="gateway_fee_pct" value="<?php echo number_format((float)$ch['config']['gateway_fee_pct'], 2, '.', ''); ?>" min="0" max="100" step="0.01" />
                    <span class="dscc-suffix">%</span>
                  </div>
                </div>
                <div class="dscc-form-row">
                  <label><?php echo $entry_gateway_fee_fixed; ?></label>
                  <div class="dscc-input-wrap">
                    <input type="number" name="gateway_fee_fixed" value="<?php echo number_format((float)$ch['config']['gateway_fee_fixed'], 2, '.', ''); ?>" min="0" step="0.01" />
                    <span class="dscc-suffix">per transaction</span>
                  </div>
                </div>
                <div class="dscc-form-row">
                  <label>
                    <?php echo $entry_fx_fee_pct; ?><br />
                    <span class="dscc-help-text"><?php echo $entry_fx_fee_help; ?></span>
                  </label>
                  <div class="dscc-input-wrap">
                    <input type="number" name="fx_fee_pct" value="<?php echo number_format((float)$ch['config']['fx_fee_pct'], 2, '.', ''); ?>" min="0" max="100" step="0.01" />
                    <span class="dscc-suffix">%</span>
                  </div>
                </div>
                <div class="dscc-form-row">
                  <label>
                    <?php echo $entry_returns_pct; ?><br />
                    <span class="dscc-help-text"><?php echo $entry_returns_help; ?></span>
                  </label>
                  <div class="dscc-input-wrap">
                    <input type="number" name="returns_pct" value="<?php echo number_format((float)$ch['config']['returns_pct'], 2, '.', ''); ?>" min="0" max="100" step="0.01" />
                    <span class="dscc-suffix">%</span>
                  </div>
                </div>
                <div class="dscc-form-actions">
                  <button type="submit" class="button ripple"><?php echo $button_save; ?></button>
                </div>
              </form>
            </div>
          </div>
          <?php } ?>
        <?php } else { ?>
          <div class="dscc-no-channels"><?php echo $text_no_channels; ?></div>
        <?php } ?>
      </div>
      <!-- ================================================================
           PANEL: Help
           ================================================================ -->
      <div id="dscc-panel-help" class="dscc-panel">
        <div class="dscc-help-section" style="padding: 10px 4px;">
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
function dsccShowTab(name, el) {
    // Hide all panels
    var panels = document.querySelectorAll('.dscc-panel');

    for (var i = 0; i < panels.length; i++) {
        panels[i].classList.remove('active');
    }
    // Deactivate all tabs
    var tabs = document.querySelectorAll('.dscc-tab');

    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }

    document.getElementById('dscc-panel-' + name).classList.add('active');
    el.classList.add('active');
}
//--></script>

<?php echo $footer; ?>