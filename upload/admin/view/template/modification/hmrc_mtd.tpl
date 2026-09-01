<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/modification.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form-settings').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <?php if ($success) { ?>
      <div class="success"><?php echo $success; ?></div>
      <?php } ?>
      <?php if ($error_warning) { ?>
      <div class="warning"><?php echo $error_warning; ?></div>
      <?php } ?>
      <form action="<?php echo $action_save; ?>" method="post" id="form-settings">
        <input type="hidden" name="store_id" value="0" />
        <div id="tabs" class="htabs">
          <a href="#tab-core"><?php echo $tab_core; ?></a>
          <a href="#tab-vat"><?php echo $tab_vat; ?></a>
          <a href="#tab-itsa"><?php echo $tab_itsa; ?></a>
        </div>
        <!-- ================================================================
             TAB: Connection & Settings
             ================================================================ -->
        <div id="tab-core">
          <!-- HMRC Developer Hub Credentials -->
          <h2><?php echo $text_credentials; ?></h2>
          <p style="padding:0 10px 10px;"><?php echo $text_credentials_help; ?></p>
          <table class="form">
            <tr>
              <td class="required"><?php echo $entry_client_id; ?></td>
              <td><input type="text" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>" style="width:340px;" autocomplete="off" /></td>
            </tr>
            <tr>
              <td class="required"><?php echo $entry_client_secret; ?></td>
              <td><input type="password" name="client_secret" value="<?php echo htmlspecialchars($client_secret); ?>" style="width:340px;" autocomplete="off" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_sandbox; ?><br /><small><?php echo $text_sandbox_help; ?></small></td>
              <td>
                <input type="radio" name="sandbox" value="1" id="sandbox-yes" class="radio" <?php echo ($sandbox ? 'checked="checked"' : ''); ?> />
                <label for="sandbox-yes"><span><span></span></span><?php echo $text_sandbox_mode; ?></label>
                &nbsp;&nbsp;
                <input type="radio" name="sandbox" value="0" id="sandbox-no" class="radio" <?php echo (!$sandbox ? 'checked="checked"' : ''); ?> />
                <label for="sandbox-no"><span><span></span></span><?php echo $text_production_mode; ?></label>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_redirect_uri; ?><br /><small><?php echo $text_redirect_uri_help; ?></small></td>
              <td><input type="text" value="<?php echo htmlspecialchars($redirect_uri); ?>" readonly="readonly" style="width:420px;" onclick="this.select();" /></td>
            </tr>
          </table>
          <!-- Connection Status -->
          <h2><?php echo $text_connection_status; ?></h2>
          <table class="form">
            <tr>
              <td><?php echo $text_connection_status; ?></td>
              <td>
                <?php if ($is_connected) { ?>
                  <span class="button-form ripple" style="cursor:default;">
                    <i class="fa fa-check-circle" style="color:#5cb85c;"></i> <?php echo $text_connected; ?>
                  </span>
                  <?php if ($token_expires) { ?>
                    &nbsp; <small><?php echo sprintf($this->language->get('text_token_expires'), $token_expires); ?></small>
                  <?php } ?>
                  &nbsp;&nbsp;
                  <a href="<?php echo $action_disconnect; ?>" class="button-delete ripple" onclick="return confirm('<?php echo $button_disconnect; ?>?');">
                    <i class="fa fa-unlink"></i> <?php echo $button_disconnect; ?>
                  </a>
                <?php } else { ?>
                  <span style="color:#999;"><i class="fa fa-times-circle"></i> <?php echo $text_not_connected; ?></span>
                  &nbsp;&nbsp;
                  <a href="<?php echo $action_connect; ?>" class="button-form animated fadeIn ripple">
                    <i class="fa fa-link"></i> <?php echo $button_connect; ?>
                  </a>
                <?php } ?>
              </td>
            </tr>
          </table>
          <!-- Components -->
          <h2><?php echo $text_components; ?></h2>
          <table class="form">
            <tr>
              <td><?php echo $entry_vat_enabled; ?><br /><small><?php echo $text_vat_enabled_help; ?></small></td>
              <td>
                <input type="radio" name="vat_enabled" value="1" id="vat-enabled-yes" class="radio" <?php echo ($vat_enabled ? 'checked="checked"' : ''); ?> />
                <label for="vat-enabled-yes"><span><span></span></span><?php echo $text_enabled; ?></label>
                &nbsp;&nbsp;
                <input type="radio" name="vat_enabled" value="0" id="vat-enabled-no" class="radio" <?php echo (!$vat_enabled ? 'checked="checked"' : ''); ?> />
                <label for="vat-enabled-no"><span><span></span></span><?php echo $text_disabled; ?></label>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_itsa_enabled; ?><br /><small><?php echo $text_itsa_enabled_help; ?></small></td>
              <td>
                <input type="radio" name="itsa_enabled" value="1" id="itsa-enabled-yes" class="radio" <?php echo ($itsa_enabled ? 'checked="checked"' : ''); ?> />
                <label for="itsa-enabled-yes"><span><span></span></span><?php echo $text_enabled; ?></label>
                &nbsp;&nbsp;
                <input type="radio" name="itsa_enabled" value="0" id="itsa-enabled-no" class="radio" <?php echo (!$itsa_enabled ? 'checked="checked"' : ''); ?> />
                <label for="itsa-enabled-no"><span><span></span></span><?php echo $text_disabled; ?></label>
              </td>
            </tr>
            <tr class="highlighted">
              <td></td>
              <td><a onclick="$('#form-settings').submit();" class="button-save ripple"><?php echo $button_save; ?></a></td>
            </tr>
          </table>
        </div>
        <!-- ================================================================
             TAB: VAT MTD
             ================================================================ -->
        <div id="tab-vat">
          <?php if (!$vat_enabled) { ?>
            <div class="warning"><?php echo $text_vat_disabled_notice; ?></div>
          <?php } elseif (!$is_connected) { ?>
            <div class="warning"><?php echo $text_not_connected_notice; ?></div>
          <?php } else { ?>
            <!-- VAT Settings (VRN) -->
            <h2><?php echo $text_vat_settings; ?></h2>
            <table class="form">
              <tr>
                <td class="required"><?php echo $entry_vrn; ?><br /><small><?php echo $text_vrn_help; ?></small></td>
                <td><input type="text" name="vrn" value="<?php echo htmlspecialchars($vrn); ?>" style="width:140px;" maxlength="9" placeholder="123456789" /></td>
              </tr>
              <tr class="highlighted">
                <td></td>
                <td><a onclick="$('#form-settings').submit();" class="button-save ripple"><?php echo $button_save; ?></a></td>
              </tr>
            </table>
            <!-- VAT Obligations -->
            <h2><?php echo $text_vat_obligations; ?></h2>
            <div style="padding:0 10px 8px;">
              <a href="<?php echo $action_obligations; ?>" class="button-form animated fadeIn ripple">
                <i class="fa fa-refresh"></i> <?php echo $button_fetch_obligations; ?>
              </a>
            </div>
            <?php if ($obligations) { ?>
            <table class="list">
              <thead>
                <tr>
                  <td class="left"><?php echo $column_period; ?></td>
                  <td class="left"><?php echo $column_due; ?></td>
                  <td class="center"><?php echo $column_status; ?></td>
                  <td class="right"><?php echo $column_action; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($obligations as $ob) { ?>
                <tr>
                  <td class="left"><?php echo htmlspecialchars($ob['start']); ?> &ndash; <?php echo htmlspecialchars($ob['end']); ?></td>
                  <td class="left"><?php echo htmlspecialchars($ob['due']); ?></td>
                  <td class="center">
                    <?php if ($ob['status'] === 'O') { ?>
                      <span style="color:#e67e22;font-weight:bold;"><?php echo $text_obligation_open; ?></span>
                    <?php } else { ?>
                      <span style="color:#5cb85c;"><?php echo $text_obligation_fulfilled; ?></span>
                    <?php } ?>
                  </td>
                  <td class="right">
                    <?php if ($ob['status'] === 'O') { ?>
                      <a href="<?php echo $action_prepare; ?>&amp;period_key=<?php echo urlencode($ob['period_key']); ?>&amp;token=<?php echo $token; ?>" class="button-form ripple">
                        <?php echo $button_prepare; ?>
                      </a>
                    <?php } else { ?>
                      &mdash;
                    <?php } ?>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
            <?php } else { ?>
            <div class="warning"><?php echo $text_no_obligations; ?></div>
            <?php } ?>
            <!-- Submitted Returns History -->
            <h2><?php echo $text_vat_history; ?></h2>
            <?php if ($vat_returns) { ?>
            <table class="list">
              <thead>
                <tr>
                  <td class="left"><?php echo $column_period_key; ?></td>
                  <td class="left"><?php echo $column_submitted; ?></td>
                  <td class="right"><?php echo $column_net_vat; ?></td>
                  <td class="left"><?php echo $column_received; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($vat_returns as $ret) { ?>
                <tr>
                  <td class="left"><?php echo htmlspecialchars($ret['period_key']); ?></td>
                  <td class="left"><?php echo htmlspecialchars($ret['submitted_at'] ?? '&mdash;'); ?></td>
                  <td class="right">&pound;<?php echo number_format((float)$ret['net_vat_due'], 2); ?></td>
                  <td class="left"><?php echo htmlspecialchars($ret['hmrc_receipt'] ? substr($ret['hmrc_receipt'], 0, 40) . '...' : '&mdash;'); ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
            <?php } else { ?>
            <p style="padding:10px;"><?php echo $text_no_history; ?></p>
            <?php } ?>
          <?php } ?>
        </div>
        <!-- ================================================================
             TAB: ITSA MTD
             ================================================================ -->
        <div id="tab-itsa">
          <?php if (!$itsa_enabled) { ?>
            <div class="warning"><?php echo $text_itsa_disabled_notice; ?></div>
          <?php } elseif (!$is_connected) { ?>
            <div class="warning"><?php echo $text_not_connected_notice; ?></div>
          <?php } else { ?>
          <!-- ITSA Settings -->
          <h2><?php echo $text_itsa_settings; ?></h2>
          <table class="form">
            <tr>
              <td><?php echo $entry_nino; ?></td>
              <td>
                <input type="text" name="nino" value="<?php echo htmlspecialchars($nino); ?>" placeholder="AA999999A" style="width:160px;text-transform:uppercase;" />
                <br /><small><?php echo $text_nino_help; ?></small>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_itsa_business_id; ?></td>
              <td>
                <input type="text" name="itsa_business_id" value="<?php echo htmlspecialchars($itsa_business_id); ?>" placeholder="<?php echo $text_itsa_business_help; ?>" style="width:260px;" />
                <br /><small><?php echo $text_itsa_business_help; ?></small>
              </td>
            </tr>
            <tr class="highlighted">
              <td></td>
              <td><a onclick="$('#form-settings').submit();" class="button-save ripple"><?php echo $button_save; ?></a></td>
            </tr>
          </table>
          <!-- Quarterly Periods -->
          <h2><?php echo $text_itsa_periods; ?></h2>
          <div class="buttons">
            <a href="<?php echo $action_itsa_periods; ?>" class="button-form ripple"><?php echo $button_fetch_periods; ?></a>
          </div>
          <?php if ($itsa_periods) { ?>
          <table class="list">
            <thead>
              <tr>
                <td class="left"><?php echo $column_tax_year; ?></td>
                <td class="left"><?php echo $column_period; ?></td>
                <td class="left"><?php echo $column_due; ?></td>
                <td class="left"><?php echo $column_status; ?></td>
                <td class="right"><?php echo $column_action; ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($itsa_periods as $period) { ?>
              <tr>
                <td class="left"><?php echo htmlspecialchars($period['tax_year']); ?></td>
                <td class="left"><?php echo htmlspecialchars($period['period_start']); ?> &ndash; <?php echo htmlspecialchars($period['period_end']); ?></td>
                <td class="left"><?php echo $period['due'] ? htmlspecialchars($period['due']) : '&mdash;'; ?></td>
                <td class="left"><?php echo $period['status'] === 'F' ? $text_itsa_status_fulfilled : $text_itsa_status_open; ?></td>
                <td class="right">
                  <?php if ($period['status'] !== 'F') { ?>
                  <a href="<?php echo $action_itsa_prepare . '&business_id=' . urlencode($period['business_id']) . '&period_start=' . urlencode($period['period_start']); ?>" class="button-form ripple"><?php echo $button_prepare_update; ?></a>
                  <?php } else { ?>
                  &mdash;
                  <?php } ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          <?php } else { ?>
          <p style="padding:10px;"><?php echo $text_no_periods; ?></p>
          <?php } ?>
          <!-- Submitted History -->
          <h2><?php echo $text_itsa_history; ?></h2>
          <?php if ($itsa_submissions) { ?>
          <table class="list">
            <thead>
              <tr>
                <td class="left"><?php echo $column_tax_year; ?></td>
                <td class="left"><?php echo $column_period; ?></td>
                <td class="left"><?php echo $column_income; ?></td>
                <td class="left"><?php echo $column_expenses; ?></td>
                <td class="left"><?php echo $column_submitted; ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($itsa_submissions as $sub) { ?>
              <tr>
                <td class="left"><?php echo htmlspecialchars($sub['tax_year']); ?></td>
                <td class="left"><?php echo htmlspecialchars($sub['period_start']); ?> &ndash; <?php echo htmlspecialchars($sub['period_end']); ?></td>
                <td class="left">&pound;<?php echo number_format((float)$sub['turnover'], 2); ?></td>
                <td class="left">&pound;<?php echo number_format((float)($sub['cost_of_goods'] + $sub['admin_costs'] + $sub['travel_costs'] + $sub['staff_costs'] + $sub['advertising_costs'] + $sub['premises_costs'] + $sub['other_expenses']), 2); ?></td>
                <td class="left"><?php echo htmlspecialchars($sub['submitted_at']); ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          <?php } else { ?>
          <p style="padding:10px;"><?php echo $text_no_itsa_history; ?></p>
          <?php } ?>
          <!-- Year-End Actions (EOPS + Final Declaration) -->
          <?php if ($itsa_year_status) { ?>
          <h2><?php echo $text_itsa_year_actions; ?></h2>
          <table class="list">
            <thead>
              <tr>
                <td class="left"><?php echo $column_tax_year; ?></td>
                <td class="left"><?php echo $column_eops; ?></td>
                <td class="left"><?php echo $column_declaration; ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($itsa_year_status as $tax_year => $ys) { ?>
              <tr>
                <td class="left"><?php echo htmlspecialchars($tax_year); ?></td>
                <td class="left">
                  <?php if ($ys['eops_submitted']) { ?>
                  <span style="color:#5cb85c;"><i class="fa fa-check-circle"></i> <?php echo $text_eops_submitted; ?></span>
                  <?php } else { ?>
                  <a href="#" onclick="hmrcSubmitEops('<?php echo htmlspecialchars($ys['business_id'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tax_year, ENT_QUOTES); ?>'); return false;" class="button-form ripple"><?php echo $button_submit_eops; ?></a>
                  <?php } ?>
                </td>
                <td class="left">
                  <?php if ($ys['declaration_submitted']) { ?>
                  <span style="color:#5cb85c;"><i class="fa fa-check-circle"></i> <?php echo $text_declaration_submitted; ?></span>
                  <?php } elseif ($ys['eops_submitted']) { ?>
                  <a href="#" onclick="hmrcSubmitDeclaration('<?php echo htmlspecialchars($tax_year, ENT_QUOTES); ?>'); return false;" class="button-form ripple"><?php echo $button_submit_declaration; ?></a>
                  <?php } else { ?>
                  <span style="color:#999;"><?php echo $text_declaration_not_submitted; ?></span>
                  <?php } ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          <?php } ?>
          <?php } ?>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('#tabs a').tabs();

function hmrcSubmitEops(businessId, taxYear) {
    if (!confirm('<?php echo addslashes($itsa_eops_confirm); ?>')) { return; }
    var f = document.createElement('form');
    f.method = 'post';
    f.action = '<?php echo $action_eops; ?>';
    [['business_id', businessId], ['tax_year', taxYear], ['eops_finalised', '1']].forEach(function(p) {
        var i = document.createElement('input');
        i.type = 'hidden'; i.name = p[0]; i.value = p[1];
        f.appendChild(i);
    });
    document.body.appendChild(f);
    f.submit();
}

function hmrcSubmitDeclaration(taxYear) {
    if (!confirm('<?php echo addslashes($itsa_decl_confirm); ?>')) { return; }
    var f = document.createElement('form');
    f.method = 'post';
    f.action = '<?php echo $action_declaration; ?>';
    [['tax_year', taxYear], ['declaration_finalised', '1']].forEach(function(p) {
        var i = document.createElement('input');
        i.type = 'hidden'; i.name = p[0]; i.value = p[1];
        f.appendChild(i);
    });
    document.body.appendChild(f);
    f.submit();
}
//--></script>

<?php echo $footer; ?>