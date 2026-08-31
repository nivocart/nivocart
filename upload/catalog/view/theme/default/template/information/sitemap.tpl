<?php echo $header; ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <?php
  // Helper: returns true when a route is enabled (null = all enabled, i.e. no setting saved yet)
  $sp_enabled = function($route) use ($sitemap_pages) {
      return $sitemap_pages === null || in_array($route, $sitemap_pages, true);
  };
  ?>
  <div class="sitemap-info">
    <div class="left">
    <?php if ($categories) { ?>
      <ul>
	  <li class="last-line"><a href="<?php echo $home; ?>"><?php echo $text_home; ?></a>
      <?php foreach ($categories as $category_1) { ?>
        <li><a href="<?php echo $category_1['href']; ?>"><?php echo $category_1['name']; ?></a>
        <?php if ($category_1['children']) { ?>
          <ul>
          <?php foreach ($category_1['children'] as $category_2) { ?>
            <li><a href="<?php echo $category_2['href']; ?>"><?php echo $category_2['name']; ?></a>
            <?php if ($category_2['children']) { ?>
              <ul>
              <?php foreach ($category_2['children'] as $category_3) { ?>
                <li><a href="<?php echo $category_3['href']; ?>"><?php echo $category_3['name']; ?></a></li>
              <?php } ?>
              </ul>
            <?php } ?>
            </li>
          <?php } ?>
          </ul>
        <?php } ?>
        </li>
      <?php } ?>
      </ul>
    <?php } ?>
    <br />
    <?php if ($blog_categories) { ?>
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
    <?php } ?>
    </div>
    <div class="right">
      <ul>
        <?php if ($sp_enabled('checkout/cart')) { ?>
        <li><a href="<?php echo $cart; ?>"><?php echo $text_cart; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('checkout/checkout')) { ?>
        <li class="last-line"><a href="<?php echo $checkout; ?>"><?php echo $text_checkout; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('product/product_list')) { ?>
        <li><a href="<?php echo $product_list; ?>"><?php echo $text_product_list; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('product/product_wall')) { ?>
        <li><a href="<?php echo $product_wall; ?>"><?php echo $text_product_wall; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('product/category_list')) { ?>
        <li><a href="<?php echo $category_list; ?>"><?php echo $text_category_list; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('product/manufacturer')) { ?>
        <li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('product/special')) { ?>
        <li><a href="<?php echo $special; ?>"><?php echo $text_special; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('product/review_list')) { ?>
        <li><a href="<?php echo $review_list; ?>"><?php echo $text_review_list; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('product/search')) { ?>
        <li class="last-line"><a href="<?php echo $search; ?>"><?php echo $text_search; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('account/account')) { ?>
        <li class="last-line"><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a>
        <?php if ($sp_enabled('account/login')) { ?>
          <ul>
            <?php if ($sp_enabled('account/login')) { ?><li><a href="<?php echo $login; ?>"><?php echo $text_login; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/edit')) { ?><li><a href="<?php echo $edit; ?>"><?php echo $text_edit; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/password')) { ?><li><a href="<?php echo $password; ?>"><?php echo $text_password; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/address')) { ?><li><a href="<?php echo $address; ?>"><?php echo $text_address; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/wishlist')) { ?><li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/order')) { ?><li><a href="<?php echo $history; ?>"><?php echo $text_history; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/transaction')) { ?><li><a href="<?php echo $transaction; ?>"><?php echo $text_transaction; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/download')) { ?><li><a href="<?php echo $download; ?>"><?php echo $text_download; ?></a></li><?php } ?>
            <?php if ($allow_return && $sp_enabled('account/return')) { ?>
            <li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>
            <?php } ?>
            <?php if ($allow_return && $sp_enabled('account/return/insert')) { ?>
            <li><a href="<?php echo $addreturn; ?>"><?php echo $text_addreturn; ?></a></li>
            <?php } ?>
            <?php if ($sp_enabled('account/reward')) { ?><li><a href="<?php echo $reward; ?>"><?php echo $text_reward; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/voucher')) { ?><li><a href="<?php echo $giftvoucher; ?>"><?php echo $text_giftvoucher; ?></a></li><?php } ?>
            <?php if ($sp_enabled('account/newsletter')) { ?><li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li><?php } ?>
          </ul>
        <?php } ?>
        </li>
        <?php } ?>
        <?php if ($allow_affiliate && $sp_enabled('affiliate/account')) { ?>
        <li class="last-line"><a href="<?php echo $affiliate_account; ?>"><?php echo $text_affiliate_account; ?></a>
        <?php if ($sp_enabled('affiliate/login')) { ?>
          <ul>
            <?php if ($sp_enabled('affiliate/login')) { ?><li><a href="<?php echo $affiliate_login; ?>"><?php echo $text_affiliate_login; ?></a></li><?php } ?>
            <?php if ($sp_enabled('affiliate/edit')) { ?><li><a href="<?php echo $affiliate_edit; ?>"><?php echo $text_affiliate_edit; ?></a></li><?php } ?>
            <?php if ($sp_enabled('affiliate/password')) { ?><li><a href="<?php echo $affiliate_password; ?>"><?php echo $text_affiliate_password; ?></a></li><?php } ?>
            <?php if ($sp_enabled('affiliate/payment')) { ?><li><a href="<?php echo $affiliate_payment; ?>"><?php echo $text_affiliate_payment; ?></a></li><?php } ?>
            <?php if ($sp_enabled('affiliate/product')) { ?><li><a href="<?php echo $affiliate_product; ?>"><?php echo $text_affiliate_product; ?></a></li><?php } ?>
            <?php if ($sp_enabled('affiliate/tracking')) { ?><li><a href="<?php echo $affiliate_tracking; ?>"><?php echo $text_affiliate_tracking; ?></a></li><?php } ?>
            <?php if ($sp_enabled('affiliate/transaction')) { ?><li><a href="<?php echo $affiliate_transaction; ?>"><?php echo $text_affiliate_transaction; ?></a></li><?php } ?>
          </ul>
        <?php } ?>
        </li>
        <?php } ?>
        <li class="last-line"><a href="<?php echo $sitemap; ?>"><?php echo $text_information; ?></a>
        <?php if ($informations) { ?>
          <ul>
          <?php foreach ($informations as $information) { ?>
            <li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
          <?php } ?>
          </ul>
        <?php } ?>
        </li>
        <?php if ($sp_enabled('information/news_list')) { ?>
        <li class="last-line"><a href="<?php echo $news; ?>"><?php echo $text_news; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('information/quote')) { ?>
        <li class="last-line"><a href="<?php echo $quote; ?>"><?php echo $text_quote; ?></a></li>
        <?php } ?>
        <?php if ($sp_enabled('information/contact')) { ?>
        <li class="last-line"><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>