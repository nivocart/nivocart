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
        <?php if (!$error_dependency) { ?>
        <a href="<?php echo $list_url; ?>" class="button-form ripple"><?php echo $button_view_list; ?></a>
        <a href="<?php echo $settings_url; ?>" class="button-form ripple"><?php echo $button_settings; ?></a>
        <?php } ?>
        <a href="<?php echo $close; ?>" class="button-cancel ripple"><?php echo $button_close; ?></a>
      </div>
    </div>
    <div class="content">
      <?php if ($error_dependency) { ?>
      <div class="attention"><?php echo $error_dependency; ?></div>
      <?php } else { ?>
      <?php if ($success) { ?>
      <div class="success"><?php echo $success; ?></div>
      <?php } ?>
      <?php if ($error) { ?>
      <div class="warning"><?php echo $error; ?></div>
      <?php } ?>
      <h2><?php echo $tab_dashboard; ?></h2>
      <div class="mc-kpi-row">
        <div class="mc-kpi">
          <span class="mc-kpi-value"><?php echo (int)$total_products; ?></span>
          <span class="mc-kpi-label"><?php echo $text_total_products; ?></span>
        </div>
        <div class="mc-kpi mc-kpi-warn">
          <span class="mc-kpi-value"><?php echo (int)$below_threshold; ?></span>
          <span class="mc-kpi-label"><?php echo $text_below_threshold; ?> (&lt; <?php echo number_format($threshold, 1); ?>%)</span>
        </div>
        <div class="mc-kpi mc-kpi-danger">
          <span class="mc-kpi-value"><?php echo (int)$negative_margin; ?></span>
          <span class="mc-kpi-label"><?php echo $text_negative_margin; ?></span>
        </div>
        <div class="mc-kpi mc-kpi-good">
          <span class="mc-kpi-value"><?php echo number_format($avg_margin, 1); ?>%</span>
          <span class="mc-kpi-label"><?php echo $text_avg_margin; ?></span>
        </div>
      </div>
      <h2><?php echo $text_worst_performers; ?></h2>
      <?php if ($worst_performers) { ?>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_product; ?></td>
            <td class="left"><?php echo $column_sku; ?></td>
            <td class="right"><?php echo $column_sale_price; ?></td>
            <td class="right"><?php echo $column_cost_inc_vat; ?></td>
            <td class="right"><?php echo $column_margin_pct; ?></td>
            <td class="left"><?php echo $column_status; ?></td>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($worst_performers as $row) { ?>
          <?php
            $m = (float)$row['margin_pct'];

            if ($m < 0) {
              $badge = 'mc-badge-negative';
              $label = $status_negative;
            } elseif ($m < $threshold) {
              $badge = 'mc-badge-low';
              $label = $status_low;
            } else {
              $badge = 'mc-badge-good';
              $label = $status_good;
            }
          ?>
          <tr>
            <td class="left"><?php echo htmlspecialchars($row['product_name'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="left"><?php echo htmlspecialchars($row['model'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="right"><?php echo number_format((float)$row['price'], 2); ?></td>
            <td class="right"><?php echo number_format((float)$row['cost_inc_vat'], 2); ?></td>
            <td class="right mc-margin"><?php echo number_format($m, 2); ?>%</td>
            <td class="left"><span class="mc-badge <?php echo $badge; ?>"><?php echo $label; ?></span></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php } else { ?>
      <div class="attention"><?php echo $text_no_dropship; ?></div>
      <?php } ?>
      <h2><?php echo $tab_about; ?></h2>
      <table class="form">
        <tr>
          <td><?php echo $text_version; ?></td>
          <td><?php echo $mc_version; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_author; ?></td>
          <td><?php echo $mc_author; ?></td>
        </tr>
        <tr>
          <td><?php echo $text_support; ?></td>
          <td><a href="mailto:<?php echo $mc_support; ?>"><?php echo $mc_support; ?></a></td>
        </tr>
        <tr>
          <td><?php echo $text_license; ?></td>
          <td><a class="about" onclick="window.open('http://opensource.org/licenses/gpl-3.0.html');" title=""><?php echo $mc_license; ?></a></td>
        </tr>
      </table>
      <?php } ?>
    </div>
  </div>
</div>
<?php echo $footer; ?>