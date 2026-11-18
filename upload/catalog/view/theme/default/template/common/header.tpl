<!DOCTYPE html>
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>">
<?php if ($description) { ?>
<meta name="description" content="<?php echo $description; ?>">
<?php } ?>
<?php if ($keywords) { ?>
<meta name="keywords" content="<?php echo $keywords; ?>">
<?php } else { ?>
<meta name="keywords" content="<?php echo $text_home; ?>">
<?php } ?>
<?php if ($metas) { ?>
<?php foreach ($metas as $meta) { ?>
<?php echo $meta; ?>
<?php } ?>
<?php } ?>
<?php if ($icon) { ?>
<link rel="icon" type="image/png" href="<?php echo $icon; ?>" />
<?php } ?>
<?php if ($apple_icon) { ?>
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $apple_icon; ?>" />
<?php } ?>
<?php foreach ($links as $link) { ?>
<link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" />
<?php } ?>
<?php if ($stylesheet_main) { ?>
<style><?php echo $stylesheet_main; ?></style>
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $stylesheet_main_fallback; ?>" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $template; ?>/stylesheet/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $template; ?>/stylesheet/stylesheet-modifiers.min.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/ui/jquery-ui-1.14.2.min.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/tom-select/tom-select.default.min.css" />
<?php foreach ($styles as $style) { ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-3.7.1.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-migrate-3.6.0.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/jquery-ui-1.14.2.min.js" async></script>
<script type="text/javascript" src="catalog/view/javascript/tom-select/tom-select-2.6.1-full.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/livesearch.min.js" async></script>
<script type="text/javascript" src="catalog/view/javascript/common.min.js" async></script>
<?php if ($stores) { ?>
<script type="text/javascript"><!--
$(document).ready(function() {
<?php foreach ($stores as $store) { ?>
  $('body').prepend('<iframe src="<?php echo $store; ?>" style="display:none;"></iframe>');
<?php } ?>
});
//--></script>
<?php } ?>
<?php echo ($google_analytics) ? $google_analytics : ''; ?>
<?php if ($rss) { ?>
<link rel="alternate" type="application/rss+xml" href="<?php echo $rss_href; ?>" />
<?php } ?>
<?php if ($sharethis) { ?>
<script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=<?php echo $sharethis; ?>&product=sop" async="async"></script>
<?php } else { ?>
<script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js" defer></script>
<?php } ?>
</head>
<body>
<a class="skip-link" href="#content"><?php echo $text_skip_to_content; ?></a>
<div id="container-top">
  <div class="container-top-inner-<?php echo $display_size; ?>">
  <a class="top-link" href="<?php echo $checkout; ?>"><i class="fa fa-play" aria-hidden="true"></i><span class="hide-phone"> &nbsp;<?php echo $text_checkout; ?></span></a>
  <a class="top-link" href="<?php echo $shopping_cart; ?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span class="hide-phone"> &nbsp;<?php echo $text_shopping_cart; ?></span></a>
<?php if ($logged) { ?>
  <div class="account-widget" id="account-widget">
    <button type="button" class="account-pill" id="account-toggle" aria-haspopup="true" aria-expanded="false" aria-controls="account-menu" aria-label="<?php echo $text_account_menu; ?>">
      <span class="account-avatar <?php echo $avatar_class; ?>" aria-hidden="true"><?php echo $customer_initials; ?></span>
      <span class="account-name hide-phone"><?php echo $customer_first_name; ?></span>
<?php if ($reward_points) { ?>
      <span class="account-points hide-phone"><i class="fa fa-star" aria-hidden="true"></i><?php echo $text_points; ?></span>
<?php } ?>
      <i class="fa fa-angle-down account-caret hide-phone" aria-hidden="true"></i>
    </button>
    <div class="account-menu" id="account-menu">
      <div class="account-menu-head">
        <span class="account-menu-name"><?php echo $customer_full_name; ?></span>
<?php if ($text_points_value) { ?>
        <span class="account-menu-points"><?php echo $text_points_value; ?></span>
<?php } ?>
      </div>
      <a href="<?php echo $account; ?>"><i class="fa fa-user" aria-hidden="true"></i><?php echo $text_my_account; ?></a>
      <a href="<?php echo $wishlist; ?>"><i class="fa fa-heart" aria-hidden="true"></i><?php echo $text_wishlist; ?></a>
      <a href="<?php echo $reward; ?>"><i class="fa fa-star" aria-hidden="true"></i><?php echo $text_reward_points; ?></a>
      <a class="account-menu-logout" href="<?php echo $logout; ?>"><i class="fa fa-sign-out" aria-hidden="true"></i><?php echo $text_logout; ?></a>
    </div>
  </div>
  <script type="text/javascript"><!--
  (function () {
    var widget = document.getElementById('account-widget');
    var toggle = document.getElementById('account-toggle');

    if (!widget || !toggle) {
      return;
    }

    function setOpen(open) {
      widget.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    toggle.addEventListener('click', function (event) {
      event.stopPropagation();
      setOpen(!widget.classList.contains('open'));
    });

    document.addEventListener('click', function (event) {
      if (!widget.contains(event.target)) {
        setOpen(false);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && widget.classList.contains('open')) {
        setOpen(false);
        toggle.focus();
      }
    });
  })();
  //--></script>
<?php } else { ?>
  <div class="account-widget">
    <a class="register-link hide-phone" href="<?php echo $register; ?>"><?php echo $text_new_here; ?> <?php echo $text_register; ?></a>
    <a class="signin-pill" href="<?php echo $account; ?>" aria-label="<?php echo $text_signin; ?>"><i class="fa fa-user-circle-o" aria-hidden="true"></i><span class="hide-phone"><?php echo $text_signin; ?></span><i class="fa fa-long-arrow-right hide-phone" aria-hidden="true"></i></a>
  </div>
<?php } ?>
  <?php echo $language; ?>
  </div>
</div>
<div id="container">
<div class="container-<?php echo $display_size; ?>">
<div id="header" role="banner">
<?php if ($logo) { ?>
  <?php $default_mobile_logo = $this->config->get('default_mobile_logo'); ?>
  <div id="logo"><a href="<?php echo $home; ?>">
    <img class="logo-desktop" src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" />
    <?php if ($default_mobile_logo) { ?>
    <img class="logo-mobile" src="<?php echo $base; ?>image/<?php echo $default_mobile_logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" />
    <?php } else { ?>
    <img class="logo-mobile" src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" />
    <?php } ?>
  </a></div>
<?php } ?>
  <?php echo $cart; ?>
  <div id="header-bottom">
    <div id="search">
      <div class="search-inside">
        <input type="text" name="search" placeholder="<?php echo $text_search; ?>" value="" autocomplete="off" aria-label="<?php echo $text_search; ?>" />
        <div class="button-search" role="button" tabindex="0" aria-label="<?php echo $text_search; ?>"></div>
      </div>
    </div>
  </div>
</div>