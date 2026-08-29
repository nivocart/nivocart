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
      <h1><img src="view/image/server.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="location='<?php echo $close; ?>';" class="button-cancel animated fadeIn ripple"><?php echo $button_close; ?></a>
      </div>
    </div>
    <div class="content">
      <p><?php echo $text_info; ?></p>
      <!-- Last Run Status -->
      <h2><?php echo $text_last_run; ?></h2>
      <div class="toolbox">
        <table class="tool">
          <tr>
            <th style="text-align:left; width:55%;"><?php echo $text_task; ?></th>
            <th style="text-align:left; width:10%;"><?php echo $text_rows; ?></th>
            <th style="text-align:left; width:12%;"><?php echo $text_status; ?></th>
            <th style="text-align:left; width:23%;"><?php echo $text_date; ?></th>
          </tr>
          <?php foreach ($tasks as $key => $label) { ?>
          <tr>
            <td><?php echo $label; ?></td>
            <?php if (isset($last_runs[$key])) { ?>
            <td><?php echo (int)$last_runs[$key]['rows_affected']; ?></td>
            <td>
              <?php if ($last_runs[$key]['status'] === 'success') { ?>
                <span style="color:#5DC15E;">&#10003; success</span>
              <?php } else { ?>
                <span style="color:#DE5954;">&#10007; error</span>
              <?php } ?>
            </td>
            <td><?php echo $last_runs[$key]['date_added']; ?></td>
            <?php } else { ?>
            <td>—</td>
            <td>—</td>
            <td style="color:#aaa;"><?php echo $text_never; ?></td>
            <?php } ?>
          </tr>
          <?php } ?>
        </table>
      </div>
      <!-- Manual Trigger -->
      <div class="buttons" style="margin:15px 0;">
        <div class="right">
          <a onclick="if(confirm('<?php echo $text_confirm; ?>')) { location='<?php echo $action; ?>'; }" class="button ripple animated fadeIn"><?php echo $button_run_now; ?></a>
        </div>
      </div>
      <!-- Cron Command -->
      <h2><?php echo $text_cron_cmd; ?></h2>
      <div class="toolbox">
        <pre style="margin:0; padding:8px; background:#f5f5f5; border:1px solid #ddd; font-size:12px; white-space:pre-wrap; word-break:break-all; color:#333;">0 2 * * * php /path/to/upload/cron.php >> /var/log/nivocart_cron.log 2>&1</pre>
      </div>
      <!-- Recent Activity Log -->
      <h2 style="margin-top:20px;"><?php echo $text_recent_log; ?></h2>
      <div class="toolbox">
        <?php if ($recent_log) { ?>
        <table class="tool">
          <tr>
            <th style="text-align:left; width:55%;"><?php echo $text_task; ?></th>
            <th style="text-align:left; width:10%;"><?php echo $text_rows; ?></th>
            <th style="text-align:left; width:12%;"><?php echo $text_status; ?></th>
            <th style="text-align:left; width:23%;"><?php echo $text_date; ?></th>
          </tr>
          <?php foreach ($recent_log as $entry) { ?>
          <tr>
            <td><?php echo htmlspecialchars($entry['task']); ?><?php if ($entry['message']) { ?> <small style="color:#aaa;">— <?php echo htmlspecialchars($entry['message']); ?></small><?php } ?></td>
            <td><?php echo (int)$entry['rows_affected']; ?></td>
            <td>
              <?php if ($entry['status'] === 'success') { ?>
                <span style="color:#5DC15E;">&#10003; success</span>
              <?php } else { ?>
                <span style="color:#DE5954;">&#10007; error</span>
              <?php } ?>
            </td>
            <td><?php echo $entry['date_added']; ?></td>
          </tr>
          <?php } ?>
        </table>
        <?php } else { ?>
        <p style="padding:8px;"><?php echo $text_no_log; ?></p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>