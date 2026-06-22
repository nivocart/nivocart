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
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button-save ripple"><?php echo $button_save; ?></a>
        <a onclick="apply();" class="button-save ripple"><?php echo $button_apply; ?></a>
        <a href="<?php echo $cancel; ?>" class="button-cancel ripple"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <div id="htabs" class="htabs">
        <a href="#tab-general"><?php echo $tab_general; ?></a>
        <a href="#tab-log"><?php echo $tab_log; ?></a>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <div id="tab-general">
        <a onclick="window.open('https://klarna.com/sell-with-klarna');" style="float:right;"><img src="view/image/payment/klarna.png" alt="" /></a>
        <div id="vtabs" class="vtabs">
          <?php foreach ($regions as $region) { ?>
            <a href="#tab-region-<?php echo $region['code']; ?>"><?php echo $region['label']; ?></a>
          <?php } ?>
        </div>
        <?php foreach ($regions as $region) { ?>
          <?php
            $region_code = $region['code'];
            $region_data = isset($klarna[$region_code]) ? $klarna[$region_code] : [];
          ?>
          <div id="tab-region-<?php echo $region_code; ?>" class="vtabs-content">
            <!-- ============================================================
                 Region credentials
                 ============================================================ -->
            <table class="form">
              <tr>
                <td><label for="input-<?php echo $region_code; ?>-username"><?php echo $entry_username; ?><br /><span class="help"><?php echo $help_username; ?></span></label></td>
                <td><input type="text" name="klarna[<?php echo $region_code; ?>][username]" id="input-<?php echo $region_code; ?>-username" value="<?php echo isset($region_data['username']) ? $region_data['username'] : ''; ?>" size="40" autocomplete="off" /></td>
              </tr>
              <tr>
                <td><label for="input-<?php echo $region_code; ?>-password"><?php echo $entry_password; ?><br /><span class="help"><?php echo $help_password; ?></span></label></td>
                <td><input type="password" name="klarna[<?php echo $region_code; ?>][password]" id="input-<?php echo $region_code; ?>-password" value="<?php echo isset($region_data['password']) ? $region_data['password'] : ''; ?>" size="40" autocomplete="off" /></td>
              </tr>
              <tr>
                <td><label for="input-<?php echo $region_code; ?>-server"><?php echo $entry_server; ?><br /><span class="help"><?php echo $help_server; ?></span></label></td>
                <td>
                  <select name="klarna[<?php echo $region_code; ?>][server]" id="input-<?php echo $region_code; ?>-server">
                    <?php $current_server = isset($region_data['server']) ? $region_data['server'] : 'playground'; ?>
                    <option value="live"<?php echo ($current_server === 'live') ? ' selected="selected"' : ''; ?>><?php echo $text_live; ?></option>
                    <option value="playground"<?php echo ($current_server === 'playground') ? ' selected="selected"' : ''; ?>><?php echo $text_playground; ?></option>
                  </select>
                </td>
              </tr>
              <tr>
                <td><label for="input-<?php echo $region_code; ?>-pending-status"><?php echo $entry_pending_status; ?><br /><span class="help"><?php echo $help_pending_status; ?></span></label></td>
                <td>
                  <select name="klarna[<?php echo $region_code; ?>][pending_status_id]" id="input-<?php echo $region_code; ?>-pending-status">
                    <?php foreach ($order_statuses as $order_status) { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>"<?php echo (isset($region_data['pending_status_id']) && $order_status['order_status_id'] == $region_data['pending_status_id']) ? ' selected="selected"' : ''; ?>><?php echo $order_status['name']; ?></option>
                    <?php } ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><label for="input-<?php echo $region_code; ?>-accepted-status"><?php echo $entry_accepted_status; ?><br /><span class="help"><?php echo $help_accepted_status; ?></span></label></td>
                <td>
                  <select name="klarna[<?php echo $region_code; ?>][accepted_status_id]" id="input-<?php echo $region_code; ?>-accepted-status">
                    <?php foreach ($order_statuses as $order_status) { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>"<?php echo (isset($region_data['accepted_status_id']) && $order_status['order_status_id'] == $region_data['accepted_status_id']) ? ' selected="selected"' : ''; ?>><?php echo $order_status['name']; ?></option>
                    <?php } ?>
                  </select>
                </td>
              </tr>
              <tr class="highlighted">
                <td><label for="input-<?php echo $region_code; ?>-status"><?php echo $entry_status; ?></label></td>
                <td>
                  <select name="klarna[<?php echo $region_code; ?>][status]" id="input-<?php echo $region_code; ?>-status">
                    <?php $region_enabled = !empty($region_data['status']); ?>
                    <option value="1"<?php echo $region_enabled ? ' selected="selected"' : ''; ?>><?php echo $text_enabled; ?></option>
                    <option value="0"<?php echo !$region_enabled ? ' selected="selected"' : ''; ?>><?php echo $text_disabled; ?></option>
                  </select>
                </td>
              </tr>
            </table>
            <!-- ============================================================
                 Countries within this region
                 ============================================================ -->
            <h3><?php echo $entry_country; ?></h3>
            <table class="list">
              <thead>
                <tr>
                  <td class="left"><?php echo $entry_country; ?></td>
                  <td class="left"><?php echo $entry_geo_zone; ?></td>
                  <td class="left"><?php echo $entry_sort_order; ?></td>
                  <td class="left"><?php echo $entry_status; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($region['countries'] as $country_code) { ?>
                  <?php
                    $country_data = isset($region_data['countries'][$country_code]) ? $region_data['countries'][$country_code] : [];
                    $country_label = isset($country_names[$country_code]) ? $country_names[$country_code] : $country_code;
                    $country_enabled = !empty($country_data['status']);
                    $country_geo_zone = isset($country_data['geo_zone_id']) ? $country_data['geo_zone_id'] : '0';
                    $country_sort_order = isset($country_data['sort_order']) ? $country_data['sort_order'] : '';
                  ?>
                  <tr>
                    <td class="left"><?php echo $country_label; ?> <span class="help">(<?php echo $country_code; ?>)</span></td>
                    <td class="left">
                      <select name="klarna[<?php echo $region_code; ?>][countries][<?php echo $country_code; ?>][geo_zone_id]">
                        <option value="0"<?php echo ($country_geo_zone == '0') ? ' selected="selected"' : ''; ?>><?php echo $text_all_zones; ?></option>
                        <?php foreach ($geo_zones as $geo_zone) { ?>
                          <option value="<?php echo $geo_zone['geo_zone_id']; ?>"<?php echo ($country_geo_zone == $geo_zone['geo_zone_id']) ? ' selected="selected"' : ''; ?>><?php echo $geo_zone['name']; ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td class="center"><input type="text" name="klarna[<?php echo $region_code; ?>][countries][<?php echo $country_code; ?>][sort_order]" value="<?php echo $country_sort_order; ?>" size="3" /></td>
                    <td class="center">
                      <select name="klarna[<?php echo $region_code; ?>][countries][<?php echo $country_code; ?>][status]">
                        <option value="1"<?php echo $country_enabled ? ' selected="selected"' : ''; ?>><?php echo $text_enabled; ?></option>
                        <option value="0"<?php echo !$country_enabled ? ' selected="selected"' : ''; ?>><?php echo $text_disabled; ?></option>
                      </select>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        <?php } ?>
      </div>
      <div id="tab-log">
        <table class="form">
          <tr>
            <td><textarea wrap="off" style="width:98%; height:300px; padding:5px; border:1px solid #CCC; background:#FFF; overflow:scroll;"><?php echo $log; ?></textarea></td>
          </tr>
          <tr>
            <td style="text-align:right;"><a href="<?php echo $clear; ?>" class="button-form"><?php echo $button_clear; ?></a></td>
          </tr>
        </table>
      </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('#htabs a').tabs();
$('#vtabs a').tabs();
//--></script>

<?php echo $footer; ?>