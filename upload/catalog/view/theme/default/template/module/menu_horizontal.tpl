<div class="menu-h-wrapper">
<?php if ($menu_horizontal) { ?>
  <div id="menu-holder">
  <div class="<?php echo $menu_class; ?>">
  <div id="menu" class="<?php echo $mod_shape; ?> <?php echo $mod_color; ?>">
  <ul>
  <?php if ($menu_home) { ?>
  <li><a href="<?php echo $home; ?>" aria-label="Home"><span class="home-icon"></span></a></li>
  <?php } ?>
  <?php foreach ($menu_horizontal as $category) { ?>
  <?php if ($category['href']) { ?>
  <li><a href="<?php echo $category['href']; ?>"<?php if ($category['children']) { ?> aria-haspopup="true" aria-expanded="false"<?php } ?>><?php echo $category['name']; ?><?php if ($category['children']) { ?><span></span><?php } ?></a>
  <?php } else { ?>
  <li><a<?php if ($category['children']) { ?> aria-haspopup="true" aria-expanded="false"<?php } ?>><?php echo $category['name']; ?><?php if ($category['children']) { ?><span></span><?php } ?></a>
  <?php } ?>
  <?php if ($category['children']) { ?>
  <div class="<?php echo $mod_shape; ?>-bottom <?php echo $mod_color; ?>">
  <?php if (count($category['children']) <= $column_limit) { ?>
  <?php for ($i = 0; $i < count($category['children']);) { ?>
  <ul>
  <?php $j = $i + ceil(count($category['children'])); ?>
  <?php for (; $i < $j; $i++) { ?>
  <?php if (isset($category['children'][$i])) { ?>
  <?php if ($category['children'][$i]['href']) { ?>
  <li><a<?php echo $i === (count($category['children']) - 1) ? " class='last-submenu-item'" : ''; ?> href="<?php echo $category['children'][$i]['href']; ?>"><span><?php echo $category['children'][$i]['name']; ?></span></a></li>
  <?php } else { ?>
  <li><a<?php echo $i === (count($category['children']) - 1) ? " class='last-submenu-item'" : ''; ?>><span><?php echo $category['children'][$i]['name']; ?></span></a></li>
  <?php } ?>
  <?php } ?>
  <?php } ?>
  </ul>
  <?php } ?>
  <?php } else { ?>
  <?php for ($i = 0; $i < count($category['children']);) { ?>
  <ul>
  <?php $j = $i + ceil(count($category['children']) / $column_number); ?>
  <?php for (; $i < $j; $i++) { ?>
  <?php if (isset($category['children'][$i])) { ?>
  <?php if ($category['children'][$i]['href']) { ?>
  <li><a<?php echo $i === (count($category['children']) - 1) ? " class='last-submenu-item'" : ''; ?> href="<?php echo $category['children'][$i]['href']; ?>"><span><?php echo $category['children'][$i]['name']; ?></span></a></li>
  <?php } else { ?>
  <li><a<?php echo $i === (count($category['children']) - 1) ? " class='last-submenu-item'" : ''; ?>><span><?php echo $category['children'][$i]['name']; ?></span></a></li>
  <?php } ?>
  <?php } ?>
  <?php } ?>
  </ul>
  <?php } ?>
  <?php } ?>
  </div>
  <?php } ?>
  </li>
  <?php } ?>
  </ul>
  </div>
  </div>
  <!-- Menu Phone -->
  <div id="menu-phone" class="box-phone">
  <ul>
  <?php foreach ($menu_horizontal as $category) { ?>
  <li id="menu-horizontal-<?php echo $category['item_id']; ?>">
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
</div>

<script>
$(document).ready(function() {
  // Burger trigger button — injected so it is absent when JS is disabled (A2/J4)
  var triggerHTML = '<button id="menu-trigger" type="button" class="<?php echo $mod_shape; ?> <?php echo $mod_color; ?>" aria-label="Menu" aria-controls="menu-phone" aria-expanded="false">'
    + '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="16" viewBox="0 0 22 16" aria-hidden="true" focusable="false">'
    + '<rect y="0" width="22" height="2" rx="1" fill="currentColor"/>'
    + '<rect y="7" width="22" height="2" rx="1" fill="currentColor"/>'
    + '<rect y="14" width="22" height="2" rx="1" fill="currentColor"/>'
    + '</svg></button>';
  $('#menu-holder').prepend(triggerHTML);

  $('body').on('click', '#menu-trigger', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var expanded = $btn.attr('aria-expanded') === 'true';
    $btn.attr('aria-expanded', String(!expanded));
    $('#menu-phone').slideToggle();
  });

  // Phone menu — top-level item tap toggles children
  <?php foreach ($menu_horizontal as $category) { ?>
  $('#menu-horizontal-<?php echo $category['item_id']; ?>').on('click', function() {
    $('#menu-horizontal-<?php echo $category['item_id']; ?> a').toggleClass('active');
  });
  <?php } ?>
});
</script>
