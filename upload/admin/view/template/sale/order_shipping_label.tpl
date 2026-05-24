<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $language; ?>" xml:lang="<?php echo $language; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shipping Label</title>
<style>
html {
	margin: 0;
	padding: 0;
	height: 100%;
}
body {
	background: #FFF;
}
body, td, th, input, select, textarea, option, optgroup {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #000;
}
td span {
	font-size: 10px;
}
td > img {
	margin: 2px 2px 0 0;
}
h1 {
	text-transform: uppercase;
	color: #BBB;
	text-align: right;
	font-size: 24px;
	font-weight: normal;
	padding-bottom: 5px;
	margin-top: 0;
	margin-bottom: 15px;
	border-bottom: 1px solid #DDD;
}
/* General */
.documents {
	margin: 0 5px;
}
.store {
	width: 100%;
	margin-bottom: 20px;
}
.div2 {
	float: left;
	display: inline-block;
}
.div3 {
	float: right;
	display: inline-block;
	padding: 5px;
}
.heading td {
	background: #F2F2F2;
}
/* Table */
table.address, table.product, table.comment, table.delivery {
	width: 100%;
	border-collapse: collapse;
	margin-bottom: 20px;
}
table.bank {
	width: 100%;
	margin-top: 20px;
}
.left {
	text-align: left;
}
.right {
	text-align: right;
}
.center {
	text-align: center;
}
.top-left {
	width: 50%;
}
.top-right {
	float: right;
	width: 50%;
	text-align: left;
	vertical-align: top;
}
/* Address */
.address {
	width: 100%;
	margin-bottom: 20px;
	border-top: 1px solid #DDD;
	border-right: 1px solid #DDD;
}
.address th, .address td {
	border-left: 1px solid #DDD;
	border-bottom: 1px solid #DDD;
	padding: 5px;
	vertical-align: text-bottom;
}
.address td {
	width: 50%;
}
/* Product */
.product {
	width: 100%;
	margin-bottom: 20px;
	border-top: 1px solid #DDD;
	border-right: 1px solid #DDD;
}
.product td {
	border-left: 1px solid #DDD;
	border-bottom: 1px solid #DDD;
	padding: 5px;
}
/* Comment */
.comment {
	width: 100%;
	margin-bottom: 20px;
	border-top: 1px solid #DDD;
	border-right: 1px solid #DDD;
}
.comment td {
	border-left: 1px solid #DDD;
	border-bottom: 1px solid #DDD;
	padding: 5px;
}
/* Delivery */
.delivery {
	width: 100%;
	margin-bottom: 20px;
	border-top: 1px solid #DDD;
	border-right: 1px solid #DDD;
}
.delivery th, .delivery td {
	border-left: 1px solid #DDD;
	border-bottom: 1px solid #DDD;
	padding: 5px;
	vertical-align: text-bottom;
}
.delivery td {
	width: 50%;
}
</style>
</head>
<body>
<?php foreach ($orders as $order) { ?>
<div class="documents">
  <div style="width:420px; border:1px dotted #CCC; padding:18px 0 0 18px;">
  <?php if ($logo) { ?>
    <img src="<?php echo $image_base; ?>data/<?php echo $logo_name; ?>.<?php echo $logo_ext; ?>" alt="" style="padding:15px 0 0 5px;" />
  <?php } ?>
  <table class="store" style="width:400px;">
    <tr>
      <td><img src="<?php echo $admin_base; ?>view/image/location/global.png" alt="" height="14" width="14" /> <?php echo $order['store_url']; ?></td>
    </tr>
  </table>
  <table class="address" style="width:400px;">
    <tr class="heading">
      <td><b><?php echo $text_deliver_to; ?></b></td>
    </tr>
    <tr>
      <td>
        <b><?php echo $order['shipping_address']; ?></b><br /><br />
		<img src="<?php echo $admin_base; ?>view/image/location/phone.png" alt="" height="14" width="14" /> <?php echo $order['telephone']; ?><br /><br />
        <?php if ($order['shipping_method']) { ?>
          <b><?php echo $order['shipping_method']; ?></b>
        <?php } ?>
      </td>
    </tr>
  </table>
  <?php if ($order['comment']) { ?>
  <table class="comment" style="width:400px;">
    <tr class="heading">
      <td><b><?php echo $column_comment; ?></b></td>
    </tr>
    <tr>
      <td><?php echo $order['comment']; ?></td>
    </tr>
  </table>
  <?php } ?>
  </div>
</div>
<?php } ?>
</body>
</html>