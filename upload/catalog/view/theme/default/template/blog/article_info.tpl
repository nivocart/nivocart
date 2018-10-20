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
  <?php if (isset($article_info_found)) { ?>
    <div class="article-info">
      <div class="article-title">
        <h1><?php echo $article_info['article_title']; ?></h1>
      </div>
      <div class="article-sub-title">
        <span class="article-author"><a href="<?php echo $author_url; ?>" title=""><?php echo $article_info['author_name']; ?></a></span>
        <span class="bullet">&bull;</span>
        <span class="article-date"><?php echo $article_date_modified; ?></span>
      <?php if ($article_info['allow_comment']) { ?>
        <span class="bullet">&bull;</span>
        <span class="article-comment"><?php echo $total_comment; ?></span>
      <?php } ?>
      <?php if ($this->config->get('blog_share_social_site')) { ?>
        <span class="article-share" style="float:right; margin-top:10px;">
        <div style="margin:25px 0 10px 0;">
          <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
            <a class="addthis_button_email"></a>
            <a class="addthis_button_print"></a>
            <a class="addthis_button_preferred_1"></a>
            <a class="addthis_button_preferred_2"></a>
            <a class="addthis_button_preferred_3"></a>
            <a class="addthis_button_compact"></a>
            <a class="addthis_counter addthis_bubble_style"></a>
          </div>
          <?php if ($addthis) { ?>
            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $addthis; ?>"></script>
          <?php } else { ?>
            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js"></script>
          <?php } ?>
        </div>
        </span>
      <?php } ?>
      </div>
      <?php if ($image) { ?>
        <div class="article-thumbnail-image" style="min-height:<?php echo $minimum_height; ?>px;">
          <img src="<?php echo $image; ?>" alt="<?php echo $article_info['article_title']; ?>" />
          <span class="article-description">
            <?php echo html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8'); ?>
          </span>
        </div>
      <?php } else { ?>
        <div class="article-description">
          <?php echo html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php } ?>
      <?php if ($article_additional_description) { ?>
        <?php foreach ($article_additional_description as $description) { ?>
          <div class="article-description">
            <?php echo html_entity_decode($description['additional_description'], ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php } ?>
      <?php } ?>
      <?php if ($products) { ?>
        <div class="box">
          <div class="box-heading"><?php echo $text_related_product; ?></div>
          <div class="box-content">
            <div class="box-product">
            <?php foreach ($products as $product) { ?>
              <div>
              <?php if ($product['thumb']) { ?>
                <?php if ($product['stock_label']) { ?>
                  <div class="stock-medium"><img src="<?php echo $product['stock_label']; ?>" alt="" /></div>
                <?php } ?>
                <?php if (!$product['stock_label'] && $product['offer']) { ?>
                  <div class="offer-medium"><img src="<?php echo $product['offer_label']; ?>" alt="" /></div>
                <?php } ?>
                <?php if (!$product['stock_label'] && !$product['offer'] && $product['special']) { ?>
                  <div class="special-medium"><img src="<?php echo $product['special_label']; ?>" alt="" /></div>
                <?php } ?>
                <div class="image">
                  <a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a>
                </div>
              <?php } ?>
                <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
              <?php if ($product['price']) { ?>
                <div class="price">
                <?php if ($product['price_option']) { ?>
                  <span class="from"><?php echo $text_from; ?></span><br />
                <?php } ?>
                <?php if (!$product['special']) { ?>
                  <?php echo $product['price']; ?>
                <?php } else { ?>
                  <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
                <?php } ?>
                </div>
              <?php } ?>
              <?php if ($product['rating']) { ?>
                <div class="rating"><img src="catalog/view/theme/<?php echo $template; ?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
              <?php } ?>
              <?php if ($product['stock_remaining'] && $this->config->get($template . '_product_stock_low') && ($product['stock_quantity'] > 0) && ($product['stock_quantity'] <= $this->config->get($template . '_product_stock_limit'))) { ?>
                <div class="remaining"><?php echo $product['stock_remaining']; ?></div>
              <?php } ?>
                <div class="box-product-bottom">
                <?php if ($product['quote']) { ?>
                  <div><a href="<?php echo $product['quote']; ?>" title="<?php echo $button_quote; ?>"><i class="fa fa-edit"></i></a></div>
                <?php } elseif (!$product['quote'] && !$stock_checkout && $product['stock_quantity'] <= 0) { ?>
                  <div class="stock-status"><a title="<?php echo $product['stock_status']; ?>"><i class="fa fa-clock-o"></i></a></div>
                <?php } elseif (!$product['quote'] && $stock_checkout && $product['stock_quantity'] <= 0) { ?>
                  <div><a onclick="addToCart('<?php echo $product['product_id']; ?>');" title="<?php echo $button_cart; ?>"><i class="fa fa-cart-arrow-down"></i></a></div>
                <?php } else { ?>
                  <div><a onclick="addToCart('<?php echo $product['product_id']; ?>');" title="<?php echo $button_cart; ?>"><i class="fa fa-cart-arrow-down"></i></a></div>
                <?php } ?>
                <div><a onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="<?php echo $button_wishlist; ?>"><i class="fa fa-heart"></i></a></div>
                <div><a href="<?php echo $product['href']; ?>" title="<?php echo $button_view; ?>"><i class="fa fa-eye"></i></a></div>
              </div>
              </div>
            <?php } ?>
            </div>
          </div>
        </div>
      <?php } ?>
      <?php if ($this->config->get('blog_related_articles')) { ?>
        <?php if ($related_articles) { ?>
          <div class="box">
            <div class="box-heading">Related Article</div>
            <div class="box-content">
              <div class="related-article">
                <?php $i = 0; ?>
                <?php foreach ($related_articles as $related_article) { ?>
                  <?php if (($i%2) == 0) { ?>
                    <div class="<?php if ($content_left || $content_right) { ?> related-article-blok-11 <?php } else { ?> related-article-blok-1 <?php } ?>">
                  <?php } else { ?>
                    <div class="<?php if ($content_left || $content_right) { ?> related-article-blok-22 <?php } else { ?> related-article-blok-2 <?php } ?>">
                  <?php } ?>
                    <?php $url = $this->url->link('blog/article/view', 'blog_article_id=' . $related_article['blog_article_id'], 'SSL'); ?>
                    <div class="name"><a href="<?php echo $url; ?>" title=""><?php echo $related_article['article_title']; ?></a></div>
                    <div class="related-article-meta">
                      <?php $author_href = $this->url->link('blog/author', 'blog_author_id=' . $related_article['blog_author_id'], 'SSL'); ?>
                      <?php echo $text_posted_by; ?> <a href="<?php echo $author_href; ?>" title=""><?php echo $related_article['author_name']; ?></a> | <?php echo $text_on; ?> <?php echo $related_article['date_added']; ?> | <?php echo $text_updated; ?> <?php echo $related_article['date_modified']; ?> |
                    </div>
                    <div class="related-article-description">
                      <div class="left"><img src="<?php echo $related_article['image']; ?>" alt="" /></div>
                      <div class="right">
                      <?php if ($content_left || $content_right) { ?>
                        <?php echo utf8_substr(strip_tags(html_entity_decode($related_article['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '...'; ?>
                      <?php } else { ?>
                        <?php echo utf8_substr(strip_tags(html_entity_decode($related_article['description'], ENT_QUOTES, 'UTF-8')), 0, 350) . '...'; ?>
                      <?php } ?>
                      </div>
                    </div>
                    <div class="related-article-button">
                      <a href="<?php echo $url; ?>" class="button"><?php echo $button_continue_reading; ?></a>
                    </div>
                    <div class="related-article-footer">
                      <?php echo $related_article['total_comment']; ?><?php echo $text_comment_on_article; ?> <a href="<?php echo $url; ?>#comment-section"><?php echo $text_view_comment; ?></a>
                    </div>
                  <?php $i++; ?>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>
        <?php } ?>
      <?php } ?>
      <?php if ($this->config->get('blog_author_information')) { ?>
        <?php if (isset($author_image)) { ?>
          <div class="box">
            <div class="box-heading"><?php echo $author_name; ?> <?php echo $text_author_information; ?></div>
            <div class="box-content">
              <div class="author-info">
                <div class="left"><img src="<?php echo $author_image; ?>" alt="<?php echo $article_info['article_title']; ?>" /></div>
                <div class="right"><?php echo $author_description; ?></div>
              </div>
            </div>
          </div>
        <?php } ?>
      <?php } ?>
      <?php if ($article_info['allow_comment']) { ?>
        <div class="box">
          <div class="box-heading"><?php echo $text_related_comment; ?></div>
          <div class="box-content">
            <div id="comments" class="blog-comment-info">
              <div id="comment-list"></div>
              <div id="comment-section"></div>
              <h2 id="review-title">
                <?php echo $text_write_comment; ?>
                <img src="<?php echo HTTPS_SERVER; ?>catalog/view/theme/<?php echo $template; ?>/image/remove.png" alt="Remove" id="reply-remove" style="display:none;" onclick="removeCommentId();" />
              </h2>
              <input type="hidden" name="blog_article_reply_id" value="0" id="blog-reply-id" />
              <div class="comment-left">
                <b><?php echo $entry_name; ?></b><br />
                <input type="text" name="name" value="" />
                <br /><br />
                <b><?php echo $entry_review; ?></b><br />
                <textarea name="text" cols="40" rows="4" style="width:98%;"></textarea>
                <span style="font-size:11px;"><?php echo $text_note; ?></span>
                <br /><br />
                <div id="captcha-wrap">
                  <div class="captcha-box">
                    <div class="captcha-view">
                      <img src="<?php echo $captcha_image; ?>" alt="" id="captcha-image" />
                    </div>
                  </div>
                  <div class="captcha-text">
                    <label><?php echo $entry_captcha; ?></label>
                    <input type="text" name="captcha" id="captcha" value="<?php echo $captcha; ?>" autocomplete="off" />
                  </div>
                  <div class="captcha-action"><i class="fa fa-repeat"></i></div>
                </div>
                <br />
                <div class="buttons">
                  <div class="right">
                    <a id="button-comment" class="button"><?php echo $button_submit; ?></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
		  </div>
        <?php } ?>
      </div>
    <?php } else { ?>
      <div class="buttons">
        <div class="center"><?php echo $text_not_found; ?></div>
      </div>
    <?php } ?>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
$('img#captcha-image').on('load', function(event) {
	$(event.target).show();
});
$('img#captcha-image').trigger('load');
//--></script>

<script type="text/javascript"><!--
function removeCommentId() {
	$("#blog-reply-id").val(0);
	$("#reply-remove").css('display', 'none');
}
//--></script>

<script type="text/javascript"><!--
$('#comment-list .pagination a').live('click', function() {
	$('#comment-list').fadeOut('slow');
	$('#comment-list').load(this.href);
	$('#comment-list').fadeIn('slow');

	return false;
});

$('#comment-list').load('index.php?route=blog/article_info/comment&blog_article_id=<?php echo $blog_article_id; ?>');
//--></script>

<script type="text/javascript"><!--
$('#button-comment').bind('click', function() {
	$.ajax({
		type: 'POST',
		url: 'index.php?route=blog/article_info/writeComment&blog_article_id=<?php echo $blog_article_id; ?>',
		dataType: 'json',
		data: 'name=' + encodeURIComponent($('input[name=\'name\']').val()) + '&text=' + encodeURIComponent($('textarea[name=\'text\']').val()) + '&captcha=' + encodeURIComponent($('input[name=\'captcha\']').val()) + '&reply_id=' + encodeURIComponent($('input[name=\'blog_article_reply_id\']').val()),
		beforeSend: function() {
			$('.success, .warning').remove();
			$('#button-comment').attr('disabled', true);
			$('#review-title').after('<div class="attention"><img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
		},
		complete: function() {
			$('#button-comment').attr('disabled', false);
			$('.attention').remove();
		},
		success: function(data) {
			if (data['error']) {
				$('#review-title').after('<div class="warning">' + data['error'] + '</div>');
			}

			if (data['success']) {
				$('#review-title').after('<div class="success">' + data['success'] + '</div>');

				$('input[name=\'name\']').val('');
				$('textarea[name=\'text\']').val('');
				$('input[name=\'captcha\']').val('');
				$("#blog-reply-id").val(0);
				$("#reply-remove").css('display', 'none');

				$('#comment-list').load('index.php?route=blog/article_info/comment&blog_article_id=<?php echo $blog_article_id; ?>');
			}
		}
	});
});
//--></script>

<?php echo $footer; ?>