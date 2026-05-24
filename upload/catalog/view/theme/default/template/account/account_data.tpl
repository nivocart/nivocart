<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $language; ?>" xml:lang="<?php echo $language; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Account Information</title>
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
    font-size: 16px;
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
.top-left {
    width: 50%;
}
.top-right {
    float: right;
    width: 50%;
    text-align: left;
    vertical-align: top;
}
.center {
    text-align: center;
}
table.address, table.personal {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
table.personal > tbody {
    border: 1px solid #DDD;
}
table.personal > tbody > tr > td:first-child {
    width: 180px;
}
table.personal > tbody > tr > td {
    padding: 10px;
    border-bottom: 1px dotted #CCC;
}
table.bank {
    width: 100%;
    margin-top: 20px;
}
.address {
    width: 100%;
    margin-bottom: 20px;
    border-top: 1px solid #DDD;
    border-right: 1px solid #DDD;
}
.address td {
    padding: 5px;
    border-left: 1px solid #DDD;
    border-bottom: 1px solid #DDD;
    vertical-align: text-bottom;
    margin-bottom: 10px;
}
</style>
</head>
<body>
<?php foreach ($customers as $customer) { ?>
<div class="documents">
  <?php if ($logo) { ?>
    <img src="<?php echo $image_base; ?>data/<?php echo $logo_name; ?>.<?php echo $logo_ext; ?>" alt="" style="padding:15px 0 0 5px;" />
  <?php } ?>
  <h1><?php echo $text_customer_data; ?></h1>
  <table class="store">
    <tr>
      <td class="top-left">
        <b><?php echo $customer['store_name']; ?></b><br />
        <?php echo $customer['store_address']; ?><br /><br />
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/phone.png" alt="" height="14" width="14" /> <?php echo $customer['store_telephone']; ?><br />
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/mail.png" alt="" height="14" width="14" /> <?php echo $customer['store_email']; ?><br />
        <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/global.png" alt="" height="14" width="14" /> <?php echo $customer['store_url']; ?><br />
        <?php if ($customer['store_company_id']) { ?>
          <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/company.png" alt="" height="14" width="14" /> <?php echo $customer['store_company_id']; ?><br />
        <?php } ?>
        <?php if ($customer['store_company_tax_id']) { ?>
          <img src="<?php echo $catalog_base; ?>view/theme/<?php echo $template; ?>/image/location/tax.png" alt="" height="14" width="14" /> <?php echo $customer['store_company_tax_id']; ?><br />
        <?php } ?>
      </td>
      <td class="top-right">
        <table>
          <tr>
            <td><?php echo $text_customer_id; ?></td>
            <td><?php echo $customer['customer_id']; ?></td>
          </tr>
          <tr>
            <td><?php echo $text_date_added; ?></td>
            <td><?php echo $customer['date_added']; ?></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <table class="personal">
    <tbody>
      <tr>
        <td><?php echo $text_firstname; ?></td>
        <td><?php echo $customer['firstname']; ?></td>
      </tr>
      <tr>
        <td><?php echo $text_lastname; ?></td>
        <td><?php echo $customer['lastname']; ?></td>
      </tr>
      <tr>
        <td><?php echo $text_email; ?></td>
        <td><?php echo $customer['email']; ?></td>
      </tr>
      <tr>
        <td><?php echo $text_telephone; ?></td>
        <td><?php echo $customer['telephone']; ?></td>
      </tr>
    <?php if ($customer['gender']) { ?>
      <tr>
        <td><?php echo $text_gender; ?></td>
        <td><?php echo $customer['gender']; ?></td>
      </tr>
    <?php } ?>
    <?php if ($customer['date_of_birth']) { ?>
      <tr>
        <td><?php echo $text_date_of_birth; ?></td>
        <td><?php echo $customer['date_of_birth']; ?></td>
      </tr>
    <?php } ?>
      <tr>
        <td><?php echo $text_ip; ?></td>
        <td><?php echo $customer['ip']; ?></td>
      </tr>
      <tr>
        <td><?php echo $text_user_agent; ?></td>
        <td><?php echo $customer['user_agent']; ?></td>
      </tr>
    </tbody>
  </table>
<?php foreach ($addresses as $address) { ?>
  <table class="address">
    <tr>
      <td><?php echo $address['address']; ?></td>
    </tr>
  </table>
<?php } ?>
  <table class="bank">
    <tr>
      <td class="center"><span><?php echo $customer['store_name']; ?> <?php echo $text_copyrights; ?></span></td>
    </tr>
  <table>
</div>
<?php } ?>
</body>
</html>