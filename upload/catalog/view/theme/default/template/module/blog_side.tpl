<?php if ($articles) { ?>
<?php if ($theme) { ?>
<div class="box">
  <div class="box-heading"><?php echo $title; ?></div>
  <div class="box-content">
  <?php foreach ($articles as $article) { ?>
    <div class="article-author">
      <a href="<?php echo $article['href']; ?>"><?php echo $article['article_title']; ?></a>
    </div>
    <?php if ($article['description']) { ?>
      <div class="description"><?php echo $article['description']; ?></div>
    <?php } ?>
    <br />
    <div style="border-bottom:1px dotted #CCC;"></div>
    <br />
  <?php } ?>
  </div>
</div>
<?php } else { ?>
<div style="margin-bottom:20px;">
  <?php foreach ($articles as $article) { ?>
    <div class="article-author">
      <a href="<?php echo $article['href']; ?>"><?php echo $article['article_title']; ?></a>
    </div>
    <?php if ($article['description']) { ?>
      <div class="description"><?php echo $article['description']; ?></div>
    <?php } ?>
    <br />
    <div style="border-bottom:1px dotted #CCC;"></div>
    <br />
  <?php } ?>
</div>
<?php } ?>
<?php } ?>