<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <?php if ($error_image) { ?>
    <div class="warning"><?php echo $error_image; ?></div>
  <?php } ?>
  <?php if ($error_image_cache) { ?>
    <div class="warning"><?php echo $error_image_cache; ?></div>
  <?php } ?>
  <?php if ($error_cache) { ?>
    <div class="warning"><?php echo $error_cache; ?></div>
  <?php } ?>
  <?php if ($error_download) { ?>
    <div class="warning"><?php echo $error_download; ?></div>
  <?php } ?>
  <?php if ($error_upload) { ?>
    <div class="warning"><?php echo $error_upload; ?></div>
  <?php } ?>
  <?php if ($error_logs) { ?>
    <div class="warning"><?php echo $error_logs; ?></div>
  <?php } ?>
  <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_unserialize; ?></div>
  <?php } ?>
  <?php if ($error_user_log) { ?>
    <div class="attention"><?php echo $error_user_log; ?> &nbsp; <a href="<?php echo $view_user_log; ?>" title="<?php echo $text_user_log; ?>"><b><?php echo $text_user_log; ?></b></a></div>
  <?php } ?>
  <?php if ($error_seo_url) { ?>
    <div class="attention"><?php echo $error_seo_url; ?> &nbsp; <a href="<?php echo $open_seo_url_audit; ?>" title="<?php echo $text_seo_url_audit; ?>"><b><?php echo $text_seo_url_audit; ?></b></a></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/home.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="event">
        <ul class="gx-lamps gx-lamps-bar">
          <li><a href="<?php echo $open_mail_log; ?>" title="<?php echo $help_mail_log; ?>"><i class="gx-lamp <?php echo $mail_log_status ? 'is-info' : 'is-off'; ?>"></i> <?php echo $help_mail_log; ?></a></li>
          <li><a href="<?php echo $open_quote_log; ?>" title="<?php echo $help_quote_log; ?>"><i class="gx-lamp <?php echo $quote_log_status ? 'is-info' : 'is-off'; ?>"></i> <?php echo $help_quote_log; ?></a></li>
          <li><a href="<?php echo $open_error_log; ?>" title="<?php echo $help_error_log; ?>"><i class="gx-lamp <?php echo $error_log_status ? 'is-bad' : 'is-off'; ?>"></i> <?php echo $help_error_log; ?></a></li>
          <?php if ($seo_url_status && $seo_url_ratio) { ?>
          <li><a href="<?php echo $open_seo_url; ?>" title="<?php echo $help_seo_url; ?>"><i class="gx-lamp <?php echo ($seo_url_ratio == 3) ? 'is-bad' : (($seo_url_ratio == 2) ? 'is-warn' : 'is-ok'); ?>"></i> <?php echo $help_seo_url; ?></a></li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <div class="content-body">
      <div class="gx-grid" id="gx-dashboard">
        <div class="gx-panel gx-c3 gx-kpi">
          <div class="gx-kpi-head"><i class="fa fa-shopping-cart"></i><a href="<?php echo $view_orders; ?>"><?php echo $text_order_today; ?></a></div>
          <div class="gx-kpi-value"><span data-nc="counter" data-value="<?php echo (int)$total_order_today; ?>"></span></div>
          <div class="gx-kpi-foot"><?php if ($delta_orders['show']) { ?><span class="gx-delta <?php echo $delta_orders['class']; ?>"><i class="fa <?php echo $delta_orders['icon']; ?>"></i> <?php echo $delta_orders['text']; ?> <?php echo $text_vs_yesterday; ?></span><?php } ?></div>
          <div data-nc="bars" data-compact="true" data-status="info" data-max="<?php echo (int)$orders_by_hour_max; ?>" data-current="<?php echo (int)$current_hour; ?>" data-values="<?php echo $orders_by_hour; ?>"></div>
        </div>
        <div class="gx-panel gx-c3 gx-kpi">
          <div class="gx-kpi-head"><i class="fa fa-users"></i><a href="<?php echo $view_customers; ?>"><?php echo $text_customer_today; ?></a></div>
          <div class="gx-kpi-value"><span data-nc="counter" data-value="<?php echo (int)$total_customer_today; ?>" data-delay="120"></span></div>
          <div class="gx-kpi-foot"><?php if ($delta_customers['show']) { ?><span class="gx-delta <?php echo $delta_customers['class']; ?>"><i class="fa <?php echo $delta_customers['icon']; ?>"></i> <?php echo $delta_customers['text']; ?> <?php echo $text_vs_yesterday; ?></span><?php } ?></div>
        </div>
        <div class="gx-panel gx-c3 gx-kpi">
          <div class="gx-kpi-head"><i class="fa fa-line-chart"></i><a href="<?php echo $view_sales; ?>"><?php echo $text_sale_today; ?></a></div>
          <div class="gx-kpi-value"><span data-nc="counter" data-value="<?php echo $raw_sale_today; ?>" data-decimals="<?php echo $money_decimals; ?>" data-prefix="<?php echo htmlspecialchars($money_prefix); ?>" data-suffix="<?php echo htmlspecialchars($money_suffix); ?>" data-delay="240"></span></div>
          <div class="gx-kpi-foot"><?php if ($delta_sales['show']) { ?><span class="gx-delta <?php echo $delta_sales['class']; ?>"><i class="fa <?php echo $delta_sales['icon']; ?>"></i> <?php echo $delta_sales['text']; ?> <?php echo $text_vs_yesterday; ?></span><?php } ?></div>
        </div>
        <div class="gx-panel gx-c3 gx-kpi">
          <div class="gx-kpi-head"><i class="fa fa-feed"></i><a href="<?php echo $view_online; ?>"><?php echo $text_online; ?></a></div>
          <div class="gx-kpi-value"><span data-nc="counter" data-value="<?php echo (int)$total_online; ?>" data-delay="360"></span></div>
          <div class="gx-kpi-foot"><span class="gx-delta is-up"><i class="fa fa-circle"></i> <?php echo $text_live; ?></span></div>
        </div>
        <div class="gx-panel gx-c3">
          <h2 class="gx-panel-title"><?php echo $text_overview; ?></h2>
          <table class="gx-table gx-list">
            <tr><td><?php echo rtrim($text_total_sale, ':'); ?></td><td><span data-nc="counter" data-value="<?php echo $raw_sale; ?>" data-decimals="<?php echo $money_decimals; ?>" data-prefix="<?php echo htmlspecialchars($money_prefix); ?>" data-suffix="<?php echo htmlspecialchars($money_suffix); ?>"></span></td></tr>
            <tr><td><?php echo rtrim($text_total_sale_year, ':'); ?></td><td><span data-nc="counter" data-value="<?php echo $raw_sale_year; ?>" data-decimals="<?php echo $money_decimals; ?>" data-prefix="<?php echo htmlspecialchars($money_prefix); ?>" data-suffix="<?php echo htmlspecialchars($money_suffix); ?>"></span></td></tr>
            <tr><td><?php echo rtrim($text_total_sale_month, ':'); ?></td><td><span data-nc="counter" data-value="<?php echo $raw_sale_month; ?>" data-decimals="<?php echo $money_decimals; ?>" data-prefix="<?php echo htmlspecialchars($money_prefix); ?>" data-suffix="<?php echo htmlspecialchars($money_suffix); ?>"></span></td></tr>
          </table>
          <table class="gx-table gx-list">
            <tr>
              <td><a href="<?php echo $view_orders; ?>"><?php echo rtrim($text_total_order, ':'); ?></a><?php if ($total_pending_orders > 0) { ?> <span class="gx-badge-count is-warn"><?php echo $total_pending_orders; ?></span><?php } ?></td>
              <td><span data-nc="counter" data-value="<?php echo (int)$total_order; ?>"></span></td>
            </tr>
            <tr>
              <td><a href="<?php echo $view_customers; ?>"><?php echo rtrim($text_total_customer, ':'); ?></a><?php if ($total_customer_approval > 0) { ?> <span class="gx-badge-count is-bad"><?php echo $total_customer_approval; ?></span><?php } ?></td>
              <td><span data-nc="counter" data-value="<?php echo (int)$total_customer; ?>"></span></td>
            </tr>
            <tr>
              <td><a href="<?php echo $view_reviews; ?>"><?php echo rtrim($text_total_review, ':'); ?></a><?php if ($total_review_approval > 0) { ?> <span class="gx-badge-count is-bad"><?php echo $total_review_approval; ?></span><?php } ?></td>
              <td><span data-nc="counter" data-value="<?php echo (int)$total_review; ?>"></span></td>
            </tr>
            <?php if ($allow_affiliate) { ?>
            <tr>
              <td><a href="<?php echo $view_affiliates; ?>"><?php echo rtrim($text_total_affiliate, ':'); ?></a><?php if ($total_affiliate_approval > 0) { ?> <span class="gx-badge-count is-bad"><?php echo $total_affiliate_approval; ?></span><?php } ?></td>
              <td><span data-nc="counter" data-value="<?php echo (int)$total_affiliate; ?>"></span></td>
            </tr>
            <?php } ?>
            <tr>
              <td><?php echo rtrim($text_total_store_products, ':'); ?></td>
              <td><span data-nc="counter" data-value="<?php echo (int)$total_store_products; ?>"></span></td>
            </tr>
          </table>
        </div>
        <div class="gx-panel gx-c6 gx-hero">
          <h2 class="gx-panel-title"><?php echo $text_store_health; ?></h2>
          <div class="gx-hero-body">
            <div class="gx-hero-side">
              <div class="gx-readout"><small><?php echo $text_orders_hour; ?></small><b><span data-nc="counter" data-value="<?php echo $orders_hour_rate; ?>" data-decimals="1"></span></b></div>
              <div class="gx-readout"><small><?php echo $text_avg_order; ?></small><b><span data-nc="counter" data-value="<?php echo $avg_order_value; ?>" data-decimals="<?php echo $money_decimals; ?>" data-prefix="<?php echo htmlspecialchars($money_prefix); ?>" data-suffix="<?php echo htmlspecialchars($money_suffix); ?>"></span></b></div>
            </div>
            <div class="gx-orbit">
              <span class="gx-orbit-r1"></span><span class="gx-orbit-r2"></span><span class="gx-orbit-r3"></span><span class="gx-orbit-r4"></span><span class="gx-orbit-sweep"></span>
              <div data-nc="gauge" data-value="<?php echo (int)$store_health; ?>" data-unit="%" data-label="<?php echo htmlspecialchars($text_overall); ?>" data-warn="70" data-bad="40" data-duration="2200"></div>
            </div>
            <div class="gx-hero-side is-right">
              <div class="gx-readout"><small><?php echo $text_awaiting_approval; ?></small><b><span data-nc="counter" data-value="<?php echo (int)$total_awaiting; ?>"></span></b></div>
              <div class="gx-readout"><small><?php echo $text_pending_orders; ?></small><b><span data-nc="counter" data-value="<?php echo (int)$total_pending_orders; ?>"></span></b></div>
            </div>
          </div>
        </div>
        <?php if ($top_countries) { ?>
        <div class="gx-panel gx-c3">
          <h2 class="gx-panel-title"><?php echo $text_topcountry; ?> <?php echo (!empty($top_flag)) ? '<img src="' . $top_flag . '" alt="" />' : ''; ?></h2>
          <div data-nc="donut" data-segments="<?php echo $donut_segments; ?>"></div>
        </div>
        <?php } else { ?>
        <div class="gx-panel gx-c3">
          <h2 class="gx-panel-title"><?php echo $text_topcountry; ?></h2>
          <p class="gx-all-clear"><?php echo $text_no_orders; ?></p>
        </div>
        <?php } ?>
        <div class="gx-panel gx-c8">
          <h2 class="gx-panel-title"><?php echo $text_statistics; ?>
            <span class="gx-tools gx-seg" id="sales-range">
              <button type="button" class="gx-btn" data-range="day"><?php echo $text_day; ?></button>
              <button type="button" class="gx-btn" data-range="week"><?php echo $text_week; ?></button>
              <button type="button" class="gx-btn is-active" data-range="month"><?php echo $text_month; ?></button>
              <button type="button" class="gx-btn" data-range="year"><?php echo $text_year; ?></button>
            </span>
          </h2>
          <ul class="gx-legend" id="sales-legend"></ul>
          <div id="sales-chart" class="gx-chart"></div>
        </div>
        <div class="gx-panel gx-c4">
          <h2 class="gx-panel-title"><?php echo $text_moderation; ?></h2>
          <div data-nc="progress" data-value="<?php echo (int)$pct_orders; ?>" data-label="<?php echo htmlspecialchars($text_orders_processed); ?>" data-warn="75" data-bad="40"></div>
          <div data-nc="progress" data-value="<?php echo (int)$pct_customers; ?>" data-label="<?php echo htmlspecialchars($text_customers_approved); ?>" data-warn="75" data-bad="40" data-delay="150"></div>
          <div data-nc="progress" data-value="<?php echo (int)$pct_reviews; ?>" data-label="<?php echo htmlspecialchars($text_reviews_approved); ?>" data-warn="75" data-bad="40" data-delay="300"></div>
          <?php if ($allow_affiliate) { ?>
          <div data-nc="progress" data-value="<?php echo (int)$pct_affiliates; ?>" data-label="<?php echo htmlspecialchars($text_affiliates_approved); ?>" data-warn="75" data-bad="40" data-delay="450"></div>
          <?php } ?>
          <div data-nc="progress" data-value="<?php echo (int)$pct_seo; ?>" data-label="<?php echo htmlspecialchars($text_seo_healthy); ?>" data-warn="75" data-bad="40" data-delay="600"></div>
        </div>
        <div class="gx-panel gx-c6">
          <h2 class="gx-panel-title"><?php echo $text_system_status; ?></h2>
          <div class="gx-gauges">
            <div data-nc="gauge" data-value="<?php echo (int)$pct_directories; ?>" data-unit="%" data-label="<?php echo htmlspecialchars($text_directories); ?>"></div>
            <div data-nc="gauge" data-value="<?php echo (int)$total_pending_orders; ?>" data-max="20" data-label="<?php echo htmlspecialchars($text_pending_orders); ?>" data-invert="true" data-warn="30" data-bad="70" data-delay="150"></div>
            <div data-nc="gauge" data-value="<?php echo (int)$total_awaiting; ?>" data-max="20" data-label="<?php echo htmlspecialchars($text_awaiting_approval); ?>" data-invert="true" data-warn="30" data-bad="70" data-delay="300"></div>
            <div data-nc="gauge" data-value="<?php echo (int)$seo_url_issues; ?>" data-max="20" data-label="<?php echo htmlspecialchars($text_seo_issues); ?>" data-invert="true" data-warn="10" data-bad="50" data-delay="450"></div>
          </div>
        </div>
        <div class="gx-panel gx-c6">
          <h2 class="gx-panel-title"><?php echo $text_orders_by_hour; ?></h2>
          <div data-nc="bars" data-numbered="true" data-label-every="3" data-current="<?php echo (int)$current_hour; ?>" data-max="<?php echo (int)$orders_by_hour_max; ?>" data-values="<?php echo $orders_by_hour; ?>"></div>
        </div>
        <div class="gx-panel gx-c12">
          <h2 class="gx-panel-title"><?php echo $text_latest; ?></h2>
          <div id="latest-tabs" class="htabs">
            <a href="#tab-latest-map"><?php echo $tab_map; ?></a>
            <?php if ($orders) { ?>
              <a href="#tab-latest-order"><?php echo $tab_order; ?></a>
            <?php } ?>
            <?php if ($customers) { ?>
              <a href="#tab-latest-customer"><?php echo $tab_customer; ?></a>
            <?php } ?>
            <?php if ($reviews) { ?>
              <a href="#tab-latest-review"><?php echo $tab_review; ?></a>
            <?php } ?>
            <?php if ($affiliates && $allow_affiliate) { ?>
              <a href="#tab-latest-affiliate"><?php echo $tab_affiliate; ?></a>
            <?php } ?>
            <?php if ($returns && $allow_return) { ?>
              <a href="#tab-latest-return"><?php echo $tab_return; ?></a>
            <?php } ?>
            <?php if ($uploads) { ?>
              <a href="#tab-latest-upload"><?php echo $tab_upload; ?></a>
            <?php } ?>
          </div>
          <div id="tab-latest-map" class="htabs-content">
            <div id="vmap" class="gx-map"></div>
          </div>
          <?php if ($orders) { ?>
          <div id="tab-latest-order" class="htabs-content">
            <table class="gx-table">
              <thead>
                <tr>
                  <th><?php echo $column_order; ?></th>
                  <th><?php echo $column_customer; ?></th>
                  <th><?php echo $column_customer_group; ?></th>
                  <th colspan="3"><?php echo $column_conversion; ?></th>
                  <th><?php echo $column_date_added; ?></th>
                  <th><?php echo $column_status; ?></th>
                  <th class="gx-r"><?php echo $column_total; ?></th>
                  <th class="gx-r"><?php echo $column_action; ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($orders as $order) { ?>
                <tr>
                  <td class="gx-mono"><?php echo $order['order_id']; ?></td>
                  <td><?php echo $order['customer']; ?></td>
                  <td><?php echo $order['customer_group']; ?></td>
                  <td class="gx-mono"><?php echo $order['passed']; ?></td>
                  <td class="gx-mono"><?php echo $order['missed']; ?></td>
                  <td class="gx-mono"><?php echo $order['conversion']; ?></td>
                  <td class="gx-mono"><?php echo $order['date_added']; ?></td>
                  <td><span class="gx-chip is-info"><?php echo $order['status']; ?></span></td>
                  <td class="gx-r gx-mono"><?php echo $order['total']; ?></td>
                  <td class="gx-r"><?php foreach ($order['action'] as $action) { ?>
                    <a href="<?php echo $action['href']; ?>" class="gx-btn"><?php echo $action['text']; ?></a>
                  <?php } ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
          <?php } ?>
          <?php if ($customers) { ?>
          <div id="tab-latest-customer" class="htabs-content">
            <table class="gx-table">
              <thead>
                <tr>
                  <th><?php echo $column_customer; ?></th>
                  <th><?php echo $column_email; ?></th>
                  <th><?php echo $column_customer_group; ?></th>
                  <th><?php echo $column_approved; ?></th>
                  <th><?php echo $column_status; ?></th>
                  <th><?php echo $column_date_added; ?></th>
                  <th><?php echo $column_orders_passed; ?></th>
                  <th><?php echo $column_orders_missed; ?></th>
                  <th class="gx-r"><?php echo $column_action; ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($customers as $customer) { ?>
                <tr>
                  <td><?php echo $customer['name']; ?><?php if ($show_dob) { ?> (<?php echo $customer['age']; ?>)<?php } ?></td>
                  <td><?php echo $customer['email']; ?></td>
                  <td><?php echo $customer['customer_group']; ?></td>
                  <td class="gx-mono"><?php echo $customer['approved']; ?></td>
                  <?php if ($customer['status'] == 2) { ?>
                    <td><span class="gx-chip is-bad"><?php echo $text_deleted; ?></span></td>
                  <?php } elseif ($customer['status'] == 1) { ?>
                    <td><span class="gx-chip is-ok"><?php echo $text_enabled; ?></span></td>
                  <?php } else { ?>
                    <td><span class="gx-chip is-warn"><?php echo $text_disabled; ?></span></td>
                  <?php } ?>
                  <td class="gx-mono"><?php echo $customer['date_added']; ?></td>
                  <td class="gx-mono"><?php echo $customer['orders_passed']; ?>
                  <?php foreach ($customer['action_passed'] as $action_passed) { ?>
                    <?php if ($customer['orders_passed'] > 0) { ?>
                      <a href="<?php echo $action_passed['href']; ?>" title="<?php echo $action_passed['text']; ?>" class="gx-btn"><i class="fa fa-eye"></i></a>
                    <?php } ?>
                  <?php } ?>
                  </td>
                  <td class="gx-mono"><?php echo $customer['orders_missed']; ?>
                  <?php foreach ($customer['action_missed'] as $action_missed) { ?>
                    <?php if ($customer['orders_missed'] > 0) { ?>
                      <a href="<?php echo $action_missed['href']; ?>" title="<?php echo $action_missed['text']; ?>" class="gx-btn"><i class="fa fa-eye"></i></a>
                    <?php } ?>
                  <?php } ?>
                  </td>
                  <td class="gx-r"><?php foreach ($customer['action'] as $action) { ?>
                    <a href="<?php echo $action['href']; ?>" class="gx-btn"><?php echo $action['text']; ?></a>
                  <?php } ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
          <?php } ?>
          <?php if ($reviews) { ?>
          <div id="tab-latest-review" class="htabs-content">
            <table class="gx-table">
              <thead>
                <tr>
                  <th><?php echo $column_product; ?></th>
                  <th><?php echo $column_author; ?></th>
                  <th><?php echo $column_rating; ?></th>
                  <th><?php echo $column_status; ?></th>
                  <th><?php echo $column_date_added; ?></th>
                  <th><?php echo $column_rating_total; ?></th>
                  <th class="gx-r"><?php echo $column_action; ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($reviews as $review) { ?>
                <tr>
                  <td><?php echo $review['name']; ?></td>
                  <td><?php echo $review['author']; ?></td>
                  <td><img src="view/image/theme-<?php echo $admin_css; ?>/stars-<?php echo $review['rating'] . '.png'; ?>" alt="<?php echo $review['rating']; ?>" /></td>
                  <?php if ($review['status'] == 1) { ?>
                    <td><span class="gx-chip is-ok"><?php echo $text_enabled; ?></span></td>
                  <?php } else { ?>
                    <td><span class="gx-chip is-warn"><?php echo $text_disabled; ?></span></td>
                  <?php } ?>
                  <td class="gx-mono"><?php echo $review['date_added']; ?></td>
                  <td class="gx-mono"><?php echo $review['rating_total']; ?>
                  <?php foreach ($review['action_rated'] as $action_rated) { ?>
                    <?php if ($review['rating_total'] > 0) { ?>
                      <a href="<?php echo $action_rated['href']; ?>" title="<?php echo $action_rated['text']; ?>" class="gx-btn"><i class="fa fa-eye"></i></a>
                    <?php } ?>
                  <?php } ?>
                  </td>
                  <td class="gx-r"><?php foreach ($review['action'] as $action) { ?>
                    <a href="<?php echo $action['href']; ?>" class="gx-btn"><?php echo $action['text']; ?></a>
                  <?php } ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
          <?php } ?>
          <?php if ($affiliates && $allow_affiliate) { ?>
          <div id="tab-latest-affiliate" class="htabs-content">
            <table class="gx-table">
              <thead>
                <tr>
                  <th><?php echo $column_affiliate; ?></th>
                  <th><?php echo $column_email; ?></th>
                  <th><?php echo $column_tracking; ?></th>
                  <th><?php echo $column_balance; ?></th>
                  <th><?php echo $column_approved; ?></th>
                  <th><?php echo $column_status; ?></th>
                  <th><?php echo $column_date_added; ?></th>
                  <th class="gx-r"><?php echo $column_action; ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($affiliates as $affiliate) { ?>
                <tr>
                  <td><?php echo $affiliate['name']; ?></td>
                  <td><?php echo $affiliate['email']; ?></td>
                  <td class="gx-mono"><?php echo $affiliate['code']; ?></td>
                  <td class="gx-mono"><?php echo $affiliate['balance']; ?></td>
                  <td class="gx-mono"><?php echo $affiliate['approved']; ?></td>
                  <?php if ($affiliate['status'] == 1) { ?>
                    <td><span class="gx-chip is-ok"><?php echo $text_enabled; ?></span></td>
                  <?php } else { ?>
                    <td><span class="gx-chip is-warn"><?php echo $text_disabled; ?></span></td>
                  <?php } ?>
                  <td class="gx-mono"><?php echo $affiliate['date_added']; ?></td>
                  <td class="gx-r"><?php foreach ($affiliate['action'] as $action) { ?>
                    <a href="<?php echo $action['href']; ?>" class="gx-btn"><?php echo $action['text']; ?></a>
                  <?php } ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
          <?php } ?>
          <?php if ($returns && $allow_return) { ?>
          <div id="tab-latest-return" class="htabs-content">
            <table class="gx-table">
              <thead>
                <tr>
                  <th><?php echo $column_return_id; ?></th>
                  <th><?php echo $column_order; ?></th>
                  <th><?php echo $column_customer; ?></th>
                  <th><?php echo $column_product; ?></th>
                  <th><?php echo $column_status; ?></th>
                  <th><?php echo $column_date_added; ?></th>
                  <th><?php echo $column_return_history; ?></th>
                  <th class="gx-r"><?php echo $column_action; ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($returns as $return) { ?>
                <tr>
                  <td class="gx-mono"><?php echo $return['return_id']; ?></td>
                  <td class="gx-mono"><?php echo $return['order_id']; ?></td>
                  <td><?php echo $return['customer']; ?></td>
                  <td><?php echo $return['product']; ?></td>
                  <td><span class="gx-chip is-info"><?php echo $return['status']; ?></span></td>
                  <td class="gx-mono"><?php echo $return['date_added']; ?></td>
                  <td class="gx-mono"><?php echo $return['return_history']; ?>
                  <?php foreach ($return['action_return'] as $action_return) { ?>
                    <?php if ($return['return_history'] > 0) { ?>
                      <a href="<?php echo $action_return['href']; ?>" title="<?php echo $action_return['text']; ?>" class="gx-btn"><i class="fa fa-eye"></i></a>
                    <?php } ?>
                  <?php } ?>
                  </td>
                  <td class="gx-r"><?php foreach ($return['action'] as $action) { ?>
                    <a href="<?php echo $action['href']; ?>" class="gx-btn"><?php echo $action['text']; ?></a>
                  <?php } ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
          <?php } ?>
          <?php if ($uploads) { ?>
          <div id="tab-latest-upload" class="htabs-content">
            <table class="gx-table">
              <thead>
                <tr>
                  <th><?php echo $column_upload_id; ?></th>
                  <th><?php echo $column_name; ?></th>
                  <th><?php echo $column_filename; ?></th>
                  <th><?php echo $column_date_added; ?></th>
                  <th class="gx-r"><?php echo $column_action; ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($uploads as $upload) { ?>
                <tr>
                  <td class="gx-mono"><?php echo $upload['upload_id']; ?></td>
                  <td><?php echo $upload['name']; ?></td>
                  <td class="gx-mono"><?php echo $upload['filename']; ?></td>
                  <td class="gx-mono"><?php echo $upload['date_added']; ?></td>
                  <td class="gx-r"><?php foreach ($upload['action'] as $action) { ?>
                    <a href="<?php echo $action['href']; ?>" class="gx-btn"><?php echo $action['text']; ?></a>
                  <?php } ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
          <?php } ?>
        </div>
        <div class="gx-panel gx-c4">
          <h2 class="gx-panel-title"><?php echo $text_topseller; ?></h2>
          <table class="gx-table">
            <thead>
              <tr>
                <th><?php echo $column_product; ?></th>
                <th class="gx-r"><?php echo $column_sold; ?></th>
                <th class="gx-r"><?php echo $column_total; ?></th>
              </tr>
            </thead>
            <tbody>
            <?php if ($sellers) { ?>
              <?php foreach ($sellers as $seller) { ?>
                <tr>
                  <td><a href="<?php echo $seller['href']; ?>" title="<?php echo $seller['model']; ?>"><?php echo $seller['name']; ?></a></td>
                  <td class="gx-r gx-mono"><?php echo $seller['quantity']; ?></td>
                  <td class="gx-r gx-mono"><?php echo $seller['total']; ?></td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="3"><?php echo $text_no_results; ?></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="gx-panel gx-c4">
          <h2 class="gx-panel-title"><?php echo $text_topview; ?></h2>
          <table class="gx-table">
            <thead>
              <tr>
                <th><?php echo $column_product; ?></th>
                <th class="gx-r"><?php echo $column_viewed; ?></th>
                <th class="gx-r"><?php echo $column_percent; ?></th>
              </tr>
            </thead>
            <tbody>
            <?php if ($views) { ?>
              <?php foreach ($views as $view) { ?>
                <tr>
                  <td><a href="<?php echo $view['href']; ?>" title="<?php echo $view['model']; ?>"><?php echo $view['name']; ?></a></td>
                  <td class="gx-r gx-mono"><?php echo $view['viewed']; ?></td>
                  <td class="gx-r gx-mono"><?php echo $view['percent']; ?></td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="3"><?php echo $text_no_results; ?></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="gx-panel gx-c4">
          <h2 class="gx-panel-title"><?php echo $text_topcustomer; ?></h2>
          <table class="gx-table">
            <thead>
              <tr>
                <th><?php echo $column_customer; ?></th>
                <th class="gx-r"><?php echo $column_orders; ?></th>
                <th class="gx-r"><?php echo $column_total; ?></th>
              </tr>
            </thead>
            <tbody>
            <?php if ($clients) { ?>
              <?php foreach ($clients as $client) { ?>
                <tr>
                  <td><a href="<?php echo $client['href']; ?>"><?php echo $client['customer']; ?></a></td>
                  <td class="gx-r gx-mono"><?php echo $client['orders']; ?></td>
                  <td class="gx-r gx-mono"><?php echo $client['total']; ?></td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="3"><?php echo $text_no_results; ?></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$(document).ready(function() {
	var css = getComputedStyle(document.body);
	var current = 'month';
	var $chart = $('#sales-chart');
	var $tip = $('<div class="gx-tip"></div>').appendTo('body');
	var ticksOf = {};

	function token(name) {
		return $.trim(css.getPropertyValue(name));
	}

	function draw(range) {
		$.ajax({
			type: 'get',
			url: 'index.php?route=common/home/chart&token=<?php echo $token; ?>&range=' + range,
			dataType: 'json',
			success: function(json) {
				var step = Math.max(1, Math.ceil(json['xaxis'].length / 12));
				var ticks = [];

				for (var i = 0; i < json['xaxis'].length; i += step) {
					ticks.push(json['xaxis'][i]);
				}

				ticksOf = {};

				$.each(json['xaxis'], function(i, tick) {
					ticksOf[tick[0]] = tick[1];
				});

				var font = {size: 12, family: token('--gx-font-mono') || "'Share Tech Mono', monospace", color: token('--gx-muted')};

				var list = [
					{label: json['order']['label'], data: json['order']['data'], color: token('--gx-seg-0')},
					{label: json['customer']['label'], data: json['customer']['data'], color: token('--gx-seg-3')},
					{label: json['cart']['label'], data: json['cart']['data'], color: token('--gx-seg-1')}
				];

				$.plot($chart, list, {
					series: {
						shadowSize: 0,
						lines: {show: true, lineWidth: 2, fill: true, fillColor: {colors: [{opacity: 0.28}, {opacity: 0.02}]}},
						points: {show: true, radius: 2.5, fill: true, fillColor: token('--gx-bg-0'), lineWidth: 1.5}
					},
					legend: {show: false},
					grid: {borderWidth: 0, color: token('--gx-muted'), tickColor: token('--gx-grid') || 'rgba(42, 79, 176, 0.45)', hoverable: true, labelMargin: 10},
					xaxis: {ticks: ticks, tickLength: 0, font: font},
					yaxis: {min: 0, tickDecimals: 0, font: font}
				});

				$('#sales-legend').html('<li class="nc-seg-0"><i></i>' + $('<div>').text(json['order']['label']).html() + '</li><li class="nc-seg-3"><i></i>' + $('<div>').text(json['customer']['label']).html() + '</li><li class="nc-seg-1"><i></i>' + $('<div>').text(json['cart']['label']).html() + '</li>');

				$chart.removeClass('is-reveal');
				void $chart[0].offsetWidth;
				$chart.addClass('is-reveal');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}

	$chart.on('plothover', function(e, pos, item) {
		if (!item) {
			$tip.hide();
			return;
		}

		$tip.text(item.series.label + ' ' + ticksOf[item.datapoint[0]] + ': ' + item.datapoint[1]).css({left: pos.pageX + 14, top: pos.pageY - 12}).show();
	});

	$('#sales-range').on('click', 'button', function() {
		current = $(this).data('range');

		$(this).addClass('is-active').siblings().removeClass('is-active');

		draw(current);
	});

	// Wait for the web fonts so Flot measures the axis labels correctly.
	var ready = (document.fonts && document.fonts.ready) ? document.fonts.ready : $.Deferred().resolve().promise();

	ready.then(function() {
		draw(current);
	});

	$.nc.init();
});
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
	var css = getComputedStyle(document.body);

	$.ajax({
		type: 'get',
		url: 'index.php?route=common/home/map&token=<?php echo $token; ?>',
		dataType: 'json',
		success: function(json) {
			var data = {};

			for (var i in json) {
				data[i] = json[i]['total'];
			}

			$('#vmap').vectorMap({
				map: '<?php echo $dashboard_map; ?>',
				backgroundColor: $.trim(css.getPropertyValue('--clr-panel-bg')) || '#0b1a54',
				borderColor: $.trim(css.getPropertyValue('--gx-bg-0')) || '#050a24',
				borderOpacity: 0.6,
				borderWidth: 1,
				color: $.trim(css.getPropertyValue('--clr-primary')) || '#3d8bff',
				hoverOpacity: 0.7,
				selectedColor: $.trim(css.getPropertyValue('--gx-accent')) || '#35d0ff',
				enableZoom: true,
				showTooltip: true,
				values: data,
				normalizeFunction: 'polynomial',
				onLabelShow: function(event, label, code) {
					if (json[code]) {
						label.html('<strong>' + label.text() + '</strong><br />' + json[code]['orders'] + '  ' + json[code]['total'] + '<br />' + json[code]['sales'] + '  ' + json[code]['amount']);
					}
				}
			});
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});

	$('#latest-tabs a').tabs();
});
//--></script>

<?php echo $footer; ?>