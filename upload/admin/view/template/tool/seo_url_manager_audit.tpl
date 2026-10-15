<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/server.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a href="<?php echo $run_audit; ?>" class="button ripple"><?php echo $button_run_audit; ?></a>
        <a href="<?php echo $back; ?>" class="button-cancel ripple"><?php echo $button_back; ?></a>
      </div>
    </div>
    <div class="content-body">
    <?php if ($error_url_status) { ?>
      <div class="attention"><?php echo $error_url_status; ?></div>
    <?php } ?>
    <?php if ($audit_total) { ?>
      <div class="warning"><?php echo $text_problems_found; ?></div>
    <?php } else { ?>
      <div class="success"><?php echo $text_no_results; ?></div>
    <?php } ?>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $text_summary; ?></td>
          <?php foreach ($problem_labels as $problem_label) { ?>
            <td class="right"><?php echo $problem_label; ?></td>
          <?php } ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($summary_rows as $summary_row) { ?>
          <tr>
            <td class="left"><?php echo $summary_row['label']; ?></td>
          <?php foreach ($summary_row['counts'] as $summary_count) { ?>
            <td class="right"><?php if ($summary_count['href']) { ?>
              <a href="<?php echo $summary_count['href']; ?>"><b><?php echo $summary_count['count']; ?></b></a>
            <?php } else { ?>
              <?php echo $summary_count['count']; ?>
            <?php } ?></td>
          <?php } ?>
          </tr>
        <?php } ?>
          <tr>
            <td class="left"><b><?php echo $text_total; ?></b></td>
          <?php foreach ($summary_totals as $summary_total) { ?>
            <td class="right"><?php if ($summary_total['href']) { ?>
              <a href="<?php echo $summary_total['href']; ?>"><b><?php echo $summary_total['count']; ?></b></a>
            <?php } else { ?>
              <b><?php echo $summary_total['count']; ?></b>
            <?php } ?></td>
          <?php } ?>
          </tr>
        </tbody>
      </table>
    <?php if ($audit_total) { ?>
      <p>
        <?php if ($text_filtered) { ?>
          <?php echo $text_filtered; ?> <a href="<?php echo $run_audit; ?>"><b><?php echo $button_show_all; ?></b></a>
        <?php } ?>
      </p>
      <?php if ($navigation_hi) { ?>
        <div class="pagination"><?php echo $pagination; ?></div>
      <?php } ?>
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_problem; ?></td>
            <td class="left"><?php echo $column_type; ?></td>
            <td class="left"><?php echo $column_name; ?></td>
            <td class="left"><?php echo $column_query; ?></td>
            <td class="left"><?php echo $column_keyword; ?></td>
            <td class="left"><?php echo $column_detail; ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
        <?php if ($audit_rows) { ?>
          <?php foreach ($audit_rows as $audit_row) { ?>
          <tr>
            <td class="left"><?php echo $audit_row['problem']; ?></td>
            <td class="left"><?php echo $audit_row['type']; ?></td>
            <td class="left"><?php if ($audit_row['href']) { ?>
              <a href="<?php echo $audit_row['href']; ?>"><?php echo $audit_row['name'] ? $audit_row['name'] : $audit_row['query']; ?></a>
            <?php } ?></td>
            <td class="left"><?php echo $audit_row['query']; ?></td>
            <td class="left"><?php echo $audit_row['keyword']; ?></td>
            <td class="left"><?php echo $audit_row['detail']; ?></td>
            <td class="right">
              <?php if ($audit_row['href']) { ?>
                <a href="<?php echo $audit_row['href']; ?>" class="button-form animated fadeIn ripple"><?php echo $text_edit_entry; ?></a>
              <?php } ?>
              <?php if ($audit_row['alias']) { ?>
                <a href="<?php echo $audit_row['alias']; ?>" class="button-form animated fadeIn ripple"><?php echo $text_edit_alias; ?></a>
              <?php } ?>
            </td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="7"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      <?php if ($navigation_lo) { ?>
        <div class="pagination"><?php echo $pagination; ?></div>
      <?php } ?>
    <?php } ?>
    </div>
  </div>
</div>
<?php echo $footer; ?>