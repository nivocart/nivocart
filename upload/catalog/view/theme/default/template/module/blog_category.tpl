<?php if ($blog_categories) { ?>
<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="box-content">
    <div class="box-category">
      <ul>
      <?php foreach ($blog_categories as $blog_category_1) { ?>
        <li><a href="<?php echo $blog_category_1['href']; ?>"><?php echo $blog_category_1['name']; ?></a>
        <?php if ($blog_category_1['children']) { ?>
          <ul>
          <?php foreach ($blog_category_1['children'] as $blog_category_2) { ?>
            <li><a href="<?php echo $blog_category_2['href']; ?>"><?php echo $blog_category_2['name']; ?></a></li>
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
  <div class="box-category">
    <ul>
    <?php foreach ($blog_categories as $blog_category_1) { ?>
      <li><a href="<?php echo $blog_category_1['href']; ?>"><?php echo $blog_category_1['name']; ?></a>
      <?php if ($blog_category_1['children']) { ?>
        <ul>
        <?php foreach ($blog_category_1['children'] as $blog_category_2) { ?>
          <li><a href="<?php echo $blog_category_2['href']; ?>"><?php echo $blog_category_2['name']; ?></a></li>
        <?php } ?>
        </ul>
      <?php } ?>
      </li>
    <?php } ?>
    </ul>
  </div>
</div>
<?php } ?>
<?php } ?>