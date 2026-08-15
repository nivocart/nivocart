<?php if ($menu_vertical) { ?>
<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="box-content">
  <div id="menu_vertical_<?php echo $module; ?>" class="box-category">
  <ul>
  <?php foreach ($menu_vertical as $category) { ?>
  <li id="menu-v-item-<?php echo $category['item_id']; ?>">
  <?php if ($category['href']) { ?>
  <a href="<?php echo $category['href']; ?>" class="inactive"><?php echo $category['name']; ?></a>
  <?php } else { ?>
  <a class="inactive"><?php echo $category['name']; ?></a>
  <?php } ?>
  <?php if ($category['children']) { ?>
  <ul class="children">
  <?php foreach ($category['children'] as $child) { ?>
  <li>
  <?php if ($child['href']) { ?>
  <a href="<?php echo $child['href']; ?>"><span class="inactive"><?php echo $child['name']; ?></span></a>
  <?php } else { ?>
  <a><span class="inactive"><?php echo $child['name']; ?></span></a>
  <?php } ?>
  </li>
  <?php } ?>
  </ul>
  <?php } ?>
  </li>
  <?php } ?>
  </ul>
  </div>
  </div>
</div>
<?php } else { ?>
<div style="margin-bottom:20px;">
  <div id="menu_vertical_<?php echo $module; ?>" class="box-category">
  <ul>
  <?php foreach ($menu_vertical as $category) { ?>
  <li id="menu-v-item-<?php echo $category['item_id']; ?>">
  <?php if ($category['href']) { ?>
  <a href="<?php echo $category['href']; ?>" class="inactive"><?php echo $category['name']; ?></a>
  <?php } else { ?>
  <a class="inactive"><?php echo $category['name']; ?></a>
  <?php } ?>
  <?php if ($category['children']) { ?>
  <ul class="children">
  <?php foreach ($category['children'] as $child) { ?>
  <li>
  <?php if ($child['href']) { ?>
  <a href="<?php echo $child['href']; ?>"><span class="inactive"><?php echo $child['name']; ?></span></a>
  <?php } else { ?>
  <a><span class="inactive"><?php echo $child['name']; ?></span></a>
  <?php } ?>
  </li>
  <?php } ?>
  </ul>
  <?php } ?>
  </li>
  <?php } ?>
  </ul>
  </div>
</div>
<?php } ?>
<script>
$(document).ready(function() {
  <?php foreach ($menu_vertical as $category) { ?>
  $('#menu-v-item-<?php echo $category['item_id']; ?>').on('click', function() {
    $('#menu-v-item-<?php echo $category['item_id']; ?> a').toggleClass('active');
  });
  <?php } ?>
});
</script>
<?php } ?>
