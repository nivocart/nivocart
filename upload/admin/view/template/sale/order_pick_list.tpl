<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $language; ?>" xml:lang="<?php echo $language; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Pick List</title>
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
  <?php if ($logo) { ?>
    <img src="<?php echo $image_base; ?>data/<?php echo $logo_name; ?>.<?php echo $logo_ext; ?>" alt="" style="padding:15px 0 0 5px;" />
  <?php } ?>
  <h1><?php echo $text_pick_list; ?></h1>
  <table class="store">
    <tr>
      <td class="top-left">
        <b><?php echo $order['store_name']; ?></b><br />
        <?php echo $order['store_address']; ?><br /><br />
        <img src="<?php echo $admin_base; ?>view/image/location/phone.png" alt="" height="14" width="14" /> <?php echo $order['store_telephone']; ?><br />
        <img src="<?php echo $admin_base; ?>view/image/location/mail.png" alt="" height="14" width="14" /> <?php echo $order['store_email']; ?><br />
        <img src="<?php echo $admin_base; ?>view/image/location/global.png" alt="" height="14" width="14" /> <?php echo $order['store_url']; ?><br />
        <?php if ($order['store_company_id']) { ?>
          <img src="<?php echo $admin_base; ?>view/image/location/company.png" alt="" height="14" width="14" /> <?php echo $order['store_company_id']; ?><br />
        <?php } ?>
        <?php if ($order['store_company_tax_id']) { ?>
          <img src="<?php echo $admin_base; ?>view/image/location/tax.png" alt="" height="14" width="14" /> <?php echo $order['store_company_tax_id']; ?><br />
        <?php } ?>
      </td>
      <td class="top-right">
        <table>
          <tr>
            <td><b><?php echo $text_date_added; ?></b></td>
            <td><?php echo $order['date_added']; ?></td>
          </tr>
          <?php if ($order['invoice_no']) { ?>
          <tr>
            <td><b><?php echo $text_invoice_no; ?></b></td>
            <td><?php echo $order['invoice_no']; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td><b><?php echo $text_order_id; ?></b></td>
            <td><?php echo $order['order_id']; ?></td>
          </tr>
          <tr>
            <td><b><?php echo $text_payment_method; ?></b></td>
            <td><?php echo $order['payment_method']; ?></td>
          </tr>
          <?php if ($order['shipping_method']) { ?>
          <tr>
            <td><b><?php echo $text_shipping_method; ?></b></td>
            <td><?php echo $order['shipping_method']; ?></td>
          </tr>
          <?php } ?>
        </table>
      </td>
    </tr>
  </table>
  <table class="address">
    <tr class="heading">
      <td width="50%"><b><?php echo $text_to; ?></b></td>
      <td width="50%"><b><?php echo $text_ship_to; ?></b></td>
    </tr>
    <tr>
      <td>
        <?php echo $order['payment_address']; ?><br/><br/>
        <img src="<?php echo $admin_base; ?>view/image/location/phone.png" alt="" height="14" width="14" /> <?php echo $order['telephone']; ?><br/>
        <?php if ($order['payment_company']) { ?>
          <img src="<?php echo $admin_base; ?>view/image/location/company.png" alt="" height="14" width="14" /> <?php echo $order['payment_company']; ?><br/>
        <?php } ?>
        <?php if ($order['payment_company_id']) { ?>
          <img src="<?php echo $admin_base; ?>view/image/location/tax.png" alt="" height="14" width="14" /> <?php echo $order['payment_company_id']; ?><br/>
        <?php } ?>
      </td>
      <td><?php echo $order['shipping_address']; ?></td>
    </tr>
  </table>
  <table class="product">
    <tr class="heading">
      <td class="left"><b><?php echo $column_location; ?></b></td>
      <td class="left"><b><?php echo $column_product; ?></b></td>
      <td class="left"><b><?php echo $column_model; ?></b></td>
      <td class="center"><b><?php echo $column_quantity; ?></b></td>
      <td class="left"><b><?php echo $column_status_picked; ?></b></td>
      <td class="left" width="20%"><b><?php echo $column_status_backordered; ?></b></td>
    </tr>
    <?php foreach ($order['product'] as $product) { ?>
    <tr>
      <td class="left"><?php foreach ($product['location'] as $product_bin) { ?>
        <?php echo ($product_bin) ? $product_bin : '---'; ?>
      <?php } ?></td>
      <td class="left"><?php echo $product['name']; ?>
        <?php foreach ($product['option'] as $option) { ?>
          <br />
          &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
        <?php } ?>
      </td>
      <td class="left"><?php echo $product['barcode']; ?><?php echo $product['model']; ?></td>
      <td class="center"><?php echo $product['quantity']; ?></td>
      <td></td>
      <td></td>
    </tr>
    <?php } ?>
  </table>
  <?php if ($order['comment']) { ?>
  <table class="comment">
    <tr class="heading">
      <td><b><?php echo $column_comment; ?></b></td>
    </tr>
    <tr>
      <td><?php echo $order['comment']; ?></td>
    </tr>
  </table>
  <?php } ?>
  <table class="delivery">
    <tr class="heading">
      <td width="50%"><b><?php echo $text_shipping_collection; ?></b></td>
      <td width="50%"><b><?php echo $text_customer_reception; ?></b></td>
    </tr>
    <tr>
      <td>
        <?php echo $text_collection_reference; ?><br /><br />
        <?php echo $text_collection_date; ?><br /><br />
        <?php echo $text_collection_time; ?><br /><br />
        <?php echo $text_collection_name; ?><br /><br />
        <?php echo $text_collection_sign; ?><br /><br />
      </td>
      <td>
        <?php echo $text_reception_name; ?><br /><br />
        <?php echo $text_reception_sign; ?><br /><br />
        <?php echo $text_reception_date; ?><br /><br />
        <?php echo $text_reception_condition; ?><br /><br />
      </td>
    </tr>
  </table>
  <table class="bank">
    <tr>
      <td class="center"><span><?php echo $order['store_name']; ?> <?php echo $text_copyrights; ?></span></td>
    </tr>
  <table>
</div>
<?php } ?>
</body>
</html>