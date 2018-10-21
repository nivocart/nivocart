<?php echo $header; ?>
<?php echo $content_higher; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <?php if (isset($author_information_found) && $author_details) { ?>
    <div class="author-info">
      <div class="left"><img src="<?php echo $author_image; ?>" alt="<?php echo $author_name; ?>" /></div>
      <div class="right"><h1><?php echo $author_name; ?></h1><?php echo $author_description; ?></div>
    </div>
  <?php } else { ?>
    <h1><?php echo $heading_title; ?></h1>
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
  <?php } else { ?>
    <div class="buttons">
      <div class="center"><?php echo $text_not_found; ?></div>
    </div>
  <?php } ?>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>