<?php echo $header; ?>
<?php echo $content_higher; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><div itemscope itemtype="http://data-vocabulary.org/Breadcrumb" style="display:inline;">
    <a href="<?php echo $breadcrumb['href']; ?>" itemprop="url"><span itemprop="title"><?php echo $breadcrumb['text']; ?></span></a></div>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($thumb || $description) { ?>
    <div class="category-info">
      <?php if ($thumb) { ?>
        <div class="image"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
      <?php } ?>
      <?php if ($description) { ?>
        <?php echo $description; ?>
      <?php } ?>
    </div>
  <?php } ?>
  <?php if ($categories) { ?>
  <h3><?php echo $text_refine; ?></h3>
  <div class="category-list">
    <?php if (count($categories) <= 5) { ?>
      <ul class="refine">
      <?php foreach ($categories as $category) { ?>
        <li><img src="catalog/view/theme/<?php echo $template; ?>/image/arrow-right.png" alt="" /> &nbsp; <a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
      <?php } ?>
      </ul>
    <?php } else { ?>
      <?php for ($i = 0; $i < count($categories);) { ?>
      <ul class="refine">
        <?php $j = $i + ceil(count($categories) / 4); ?>
        <?php for (; $i < $j; $i++) { ?>
          <?php if (isset($categories[$i])) { ?>
            <li><img src="catalog/view/theme/<?php echo $template; ?>/image/arrow-right.png" alt="" /> &nbsp; <a href="<?php echo $categories[$i]['href']; ?>"><?php echo $categories[$i]['name']; ?></a></li>
          <?php } ?>
        <?php } ?>
      </ul>
      <?php } ?>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($articles) { ?>
    <?php foreach ($articles as $article) { ?>
      <div class="article-info">
        <div class="article-title">
          <h1><a href="<?php echo $article['href']; ?>" title=""><?php echo $article['article_title']; ?></a><h1>
        </div>
        <div class="article-sub-title">
          <span class="article-author"><a href="<?php echo $article['author_href']; ?>" title=""><?php echo $article['author_name']; ?></a></span>
          <span class="bullet">&bull;</span>
          <span class="article-date"><?php echo $article['date_added']; ?></span>
        <?php if ($article['allow_comment']) { ?>
          <span class="bullet">&bull;</span>
          <span class="article-comment"><a href="<?php echo $article['comment_href']; ?>#comment-section"><?php echo $article['total_comment']; ?></a></span>
        <?php } ?>
        </div>
        <?php if ($article['image']) { ?>
          <div class="article-thumbnail-image" style="min-height:110px;">
            <img src="<?php echo $article['image']; ?>" alt="<?php echo $article['article_title']; ?>" />
            <span class="article-description"><?php echo $article['description']; ?></span>
          </div>
        <?php } else { ?>
          <div class="article-description"><?php echo $article['description']; ?></div>
        <?php } ?>
        <div style="text-align:right;"><a href="<?php echo $article['href']; ?>" class="button"><?php echo $button_continue_reading; ?></a></div>
      </div>
    <?php } ?>
    <div class="pagination"><?php echo $pagination; ?></div>
  <?php } ?>
  <?php if (!$categories && !$articles) { ?>
    <div class="content"><?php echo $text_not_found; ?></div>
    <div class="buttons">
      <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
    </div>
  <?php } ?>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>