<?php if ($tiles) { ?>
<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="box-content tiles-box">
<?php } ?>
    <div class="tiles-wrap">
      <div id="tiles<?php echo $module; ?>" class="tiles tiles-cols-<?php echo $columns; ?> tiles-gap-<?php echo $gap; ?> tiles-<?php echo $shape; ?> tiles-hover-<?php echo $hover; ?>" style="<?php echo $style; ?>">
      <?php foreach ($tiles as $tile) { ?>
        <?php if ($tile['link']) { ?>
        <a href="<?php echo $tile['link']; ?>"
          <?php if ($tile['external_link']) { ?>
          target="_blank" rel="noopener noreferrer"
          <?php } ?>
          class="tile tile-<?php echo $tile['layout']; ?> tile-align-<?php echo $tile['align']; ?> tile-valign-<?php echo $tile['valign']; ?>"
          <?php if ($tile['style']) { ?>
          style="<?php echo $tile['style']; ?>"
          <?php } ?>>
        <?php } else { ?>
        <div class="tile tile-<?php echo $tile['layout']; ?> tile-align-<?php echo $tile['align']; ?> tile-valign-<?php echo $tile['valign']; ?>"
          <?php if ($tile['style']) { ?>
          style="<?php echo $tile['style']; ?>"
          <?php } ?>>
        <?php } ?>
          <?php if ($tile['image']) { ?>
          <span class="tile-image"><img src="<?php echo $tile['image']; ?>" alt="" width="<?php echo $width; ?>" height="<?php echo $height; ?>" loading="lazy" /></span>
          <?php } ?>
          <span class="tile-body">
            <span class="tile-title"><?php echo $tile['title']; ?></span>
            <?php if ($tile['text']) { ?>
            <span class="tile-text"><?php echo $tile['text']; ?></span>
            <?php } ?>
            <?php if ($tile['link_text']) { ?>
            <span class="tile-button"><?php echo $tile['link_text']; ?></span>
            <?php } ?>
          </span>
        <?php if ($tile['link']) { ?>
        </a>
        <?php } else { ?>
        </div>
        <?php } ?>
      <?php } ?>
      </div>
    </div>
<?php if ($theme) { ?>
  </div>
</div>
<?php } ?>
<?php } ?>