<?php if ($mode === 'kpi') { ?>
  <?php foreach ($tiles as $tile) { ?>
    <div class="gx-panel gx-c3 gx-kpi">
      <div class="gx-kpi-head"><i class="fa <?php echo $tile['icon']; ?>"></i><a href="<?php echo $tile['href']; ?>"><?php echo $tile['title']; ?></a></div>
      <div class="gx-kpi-value"><span data-nc="counter" data-value="<?php echo $tile['counter']['value']; ?>" data-decimals="<?php echo $tile['counter']['decimals']; ?>" data-prefix="<?php echo htmlspecialchars($tile['counter']['prefix']); ?>" data-suffix="<?php echo htmlspecialchars($tile['counter']['suffix']); ?>" data-delay="<?php echo $tile['delay']; ?>"></span></div>
      <div class="gx-kpi-foot">
      <?php if ($tile['delta']['show']) { ?>
        <span class="gx-delta <?php echo $tile['delta']['class']; ?>"><i class="fa <?php echo $tile['delta']['icon']; ?>"></i> <?php echo $tile['delta']['text']; ?> <?php echo $tile['vs']; ?></span>
      <?php } elseif ($tile['note']) { ?>
        <span class="gx-delta"><?php echo $tile['note']; ?></span>
      <?php } ?>
      </div>
    </div>
  <?php } ?>
<?php } elseif ($mode === 'attention') { ?>
  <?php if ($alerts) { ?>
    <ul class="gx-alerts">
    <?php foreach ($alerts as $alert) { ?>
      <li class="is-<?php echo $alert['status']; ?>"><a href="<?php echo $alert['href']; ?>"><i class="gx-lamp is-<?php echo $alert['status']; ?>"></i><i class="fa <?php echo $alert['icon']; ?> gx-alert-icon"></i><span><?php echo $alert['text']; ?></span><b><?php echo $alert['count']; ?></b></a></li>
    <?php } ?>
    </ul>
  <?php } else { ?>
    <p class="gx-all-clear"><i class="fa fa-check-circle"></i> <?php echo $text_all_clear; ?></p>
  <?php } ?>
<?php } else { ?>
  <?php if ($rows) { ?>
    <div class="gx-rrows<?php echo !empty($columns) ? ' is-cols' : ''; ?>">
    <?php foreach ($rows as $row) { ?>
      <div class="gx-rrow<?php echo $row['status'] ? ' is-' . $row['status'] : ''; ?>">
        <a class="gx-rrow-main" href="<?php echo $row['href']; ?>">
          <i class="fa <?php echo $row['icon']; ?> gx-rrow-icon"></i>
          <span class="gx-rrow-title"><b><?php echo $row['title']; ?></b><small><?php echo $row['note']; ?></small></span>
        <?php if ($row['spark']) { ?>
          <span class="gx-rrow-spark" data-nc="bars" data-values="<?php echo htmlspecialchars(json_encode($row['spark'])); ?>" data-compact="true" data-status="<?php echo $row['status'] ? $row['status'] : 'info'; ?>"></span>
        <?php } ?>
          <span class="gx-rrow-value">
          <?php if ($row['counter']) { ?>
            <span data-nc="counter" data-value="<?php echo $row['counter']['value']; ?>" data-decimals="<?php echo $row['counter']['decimals']; ?>" data-prefix="<?php echo htmlspecialchars($row['counter']['prefix']); ?>" data-suffix="<?php echo htmlspecialchars($row['counter']['suffix']); ?>"></span>
          <?php } else { ?>
            <?php echo $row['text']; ?>
          <?php } ?>
          <?php if ($row['delta'] && $row['delta']['show']) { ?>
            <em class="gx-delta <?php echo $row['delta']['class']; ?>"><i class="fa <?php echo $row['delta']['icon']; ?>"></i> <?php echo $row['delta']['text']; ?></em>
          <?php } ?>
          </span>
          <i class="fa fa-angle-right gx-rrow-go"></i>
        </a>
      <?php if ($row['donut']) { ?>
        <div class="gx-rrow-donut" data-nc="donut" data-segments="<?php echo $row['donut']; ?>"></div>
      <?php } ?>
      <?php if ($row['items']) { ?>
        <ul class="gx-rrow-items">
        <?php foreach ($row['items'] as $item) { ?>
          <li><span><?php echo $item['name']; ?></span><b><?php echo $item['value']; ?></b></li>
        <?php } ?>
        </ul>
      <?php } ?>
      </div>
    <?php } ?>
    </div>
  <?php } else { ?>
    <p class="gx-all-clear"><?php echo $text_no_data; ?></p>
  <?php } ?>
<?php } ?>