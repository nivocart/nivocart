<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $language; ?>" xml:lang="<?php echo $language; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Order History</title>
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
table.address, table.product, table.comment, table.history {
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
.history {
	width: 100%;
	margin-bottom: 20px;
	border-top: 1px solid #DDD;
	border-right: 1px solid #DDD;
}
.history th, .history td {
	vertical-align: text-bottom;
	border-left: 1px solid #DDD;
	border-bottom: 1px solid #DDD;
	padding: 5px;
}
.history td {
	width: 33%;
}
</style>
</head>
<body>
<div class="documents">
  <?php if ($logo) { ?>
    <div style="margin: 10px 0 0 5px;"><img src="<?php echo $image_base; ?>data/<?php echo $logo_name; ?>.<?php echo $logo_ext; ?>" alt="" /></div>
  <?php } ?>
  <h1><?php echo $heading_order; ?></h1>
  <table class="store">
    <tr>
      <td class="top-left">
        <b><?php echo $store_name; ?></b><br />
        <?php echo $store_address; ?><br /><br />
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/phone.png" alt="" height="14" width="14" /> <?php echo $store_telephone; ?><br />
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/mail.png" alt="" height="14" width="14" /> <?php echo $store_email; ?><br />
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/global.png" alt="" height="14" width="14" /> <?php echo $store_url; ?><br />
      <?php if ($store_company_id) { ?>
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/company.png" alt="" height="14" width="14" /> <?php echo $store_company_id; ?><br />
      <?php } ?>
      <?php if ($store_company_tax_id) { ?>
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/tax.png" alt="" height="14" width="14" /> <?php echo $store_company_tax_id; ?><br />
      <?php } ?>
      </td>
      <td class="top-right">
        <table>
          <tr>
            <td><b><?php echo $text_date_added; ?></b></td>
            <td><?php echo $date_added; ?></td>
          </tr>
        <?php if ($invoice_no) { ?>
          <tr>
            <td><b><?php echo $text_invoice_no; ?></b></td>
            <td><?php echo $invoice_no; ?></td>
          </tr>
        <?php } ?>
          <tr>
            <td><b><?php echo $text_order_id; ?></b></td>
            <td><?php echo $order_id; ?></td>
          </tr>
          <tr>
            <td><b><?php echo $text_payment_method; ?></b></td>
            <td><?php echo $payment_method; ?></td>
          </tr>
        <?php if ($shipping_method) { ?>
          <tr>
            <td><b><?php echo $text_shipping_method; ?></b></td>
            <td><?php echo $shipping_method; ?></td>
          </tr>
        <?php } ?>
        </table>
      </td>
    </tr>
  </table>
  <table class="address">
    <tr class="heading">
      <td width="50%"><b><?php echo $text_payment_address; ?></b></td>
      <td width="50%"><b><?php echo $text_shipping_address; ?></b></td>
    </tr>
    <tr>
      <td>
        <?php echo $payment_address; ?><br/><br/>
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/phone.png" alt="" height="14" width="14" /> <?php echo $telephone; ?><br />
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/mail.png" alt="" height="14" width="14" /> <?php echo $email; ?><br />
        <?php if ($payment_company) { ?>
          <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/company.png" alt="" height="14" width="14" /> <?php echo $payment_company; ?><br />
        <?php } ?>
        <?php if ($payment_company_id) { ?>
          <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/tax.png" alt="" height="14" width="14" /> <?php echo $payment_company_id; ?><br />
        <?php } ?>
      </td>
      <td><?php echo $shipping_address; ?></td>
    </tr>
  </table>
  <table class="product">
    <tr class="heading">
      <td class="left"><b><?php echo $column_name; ?></b></td>
      <td class="left"><b><?php echo $column_model; ?></b></td>
      <td class="center"><b><?php echo $column_quantity; ?></b></td>
      <td class="right"><b><?php echo $column_price; ?></b></td>
    <?php if ($tax_breakdown) { ?>
      <td class="right"><b><?php echo $column_tax_value; ?></b></td>
      <td class="right"><b><?php echo $column_tax_percent; ?></b></td>
    <?php } ?>
      <td class="right"><b><?php echo $column_total; ?></b></td>
    </tr>
  <?php foreach ($products as $product) { ?>
    <tr>
      <td class="left"><?php echo $product['name']; ?>
        <?php foreach ($product['option'] as $option) { ?>
          <br />
          &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
        <?php } ?>
      </td>
      <td class="left"><?php echo $product['model']; ?></td>
      <td class="center"><?php echo $product['quantity']; ?></td>
      <td class="right"><?php echo $product['price']; ?></td>
    <?php if ($tax_breakdown) { ?>
      <td class="right"><?php echo $product['tax_value']; ?></td>
      <td class="right"><?php echo $product['tax_percent']; ?>%</td>
    <?php } ?>
      <td class="right"><?php echo $product['total']; ?></td>
    </tr>
  <?php } ?>
  <?php foreach ($vouchers as $voucher) { ?>
    <tr>
      <td class="left"><?php echo $voucher['description']; ?></td>
      <td class="left"></td>
      <td class="right">1</td>
      <td class="right"><?php echo $voucher['amount']; ?></td>
    <?php if ($tax_breakdown) { ?>
      <td class="right">0.00</td>
      <td class="right">0%</td>
    <?php } ?>
      <td class="right"><?php echo $voucher['amount']; ?></td>
    </tr>
  <?php } ?>
  <?php foreach ($totals as $total) { ?>
    <tr>
      <td class="right" colspan="<?php echo $tax_colspan; ?>"><b><?php echo $total['title']; ?>:</b></td>
      <td class="right"><?php echo $total['text']; ?></td>
    </tr>
  <?php } ?>
  </table>
  <table class="bank">
    <tr>
      <td class="center"><span><b><?php echo $text_damages; ?></b></span></td>
    </tr>
  <?php if (!empty($bank_name) && !empty($bank_account)) { ?>
    <tr>
      <td class="center"><span><?php echo $text_bank_name; ?> <?php echo $bank_name; ?> - <?php echo $text_bank_account; ?> <?php echo $bank_sort_code; ?> <?php echo $bank_account; ?></span></td>
    </tr>
  <?php } ?>
    <tr>
      <td class="center"><span><?php echo $store_name; ?> <?php echo $text_copyrights; ?></span></td>
    </tr>
  <table>
</div>
</body>
</html>