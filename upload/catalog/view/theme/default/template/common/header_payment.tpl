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
<?php } ?>
<?php foreach ($metas as $meta) { ?>
<?php echo $meta; ?>
<?php } ?>
<?php if ($icon) { ?>
<link rel="icon" type="image/png" href="<?php echo $icon; ?>" />
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
<?php foreach ($styles as $style) { ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-2.2.4.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-migrate-1.4.1.min.js"></script>
<?php echo $google_analytics ? $google_analytics : ''; ?>
</head>
<body>
<div id="container">
<div class="container-<?php echo $display_size; ?>">
