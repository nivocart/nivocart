<?php if ($articles) { ?>
<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="box-content">
  <?php foreach ($articles as $article) { ?>
    <div class="box-news">
    <?php if ($article['image']) { ?>
      <a href="<?php echo $article['href']; ?>" title=""><img src="<?php echo $article['image']; ?>" alt="<?php echo $article['article_title']; ?>" /></a>
    <?php } ?>
	  <div style="padding:10px;">
        <h4><?php echo $article['article_title']; ?></h4>
        <br />
        <?php echo $article['description']; ?>
      </div>
      <div style="margin:20px 0 5px 10px;"><a href="<?php echo $article['href']; ?>" class="button"><?php echo $button_continue_reading; ?></a></div>
    </div>
  <?php } ?>
  </div>
</div>
<?php } else { ?>
<div style="margin-bottom:20px;">
  <?php foreach ($articles as $article) { ?>
    <div class="box-news">
    <?php if ($article['image']) { ?>
      <a href="<?php echo $article['href']; ?>" title=""><img src="<?php echo $article['image']; ?>" alt="<?php echo $article['article_title']; ?>" /></a>
    <?php } ?>
	  <div style="padding:10px;">
        <h4><?php echo $article['article_title']; ?></h4>
        <br />
        <?php echo $article['description']; ?>
      </div>
      <div style="margin:20px 0 5px 10px;"><a href="<?php echo $article['href']; ?>" class="button"><?php echo $button_continue_reading; ?></a></div>
    </div>
  <?php } ?>
</div>
<?php } ?>
<?php } ?>