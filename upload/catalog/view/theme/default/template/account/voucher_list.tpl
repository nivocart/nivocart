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
  <?php if ($vouchers) { ?>
    <table class="list">
      <thead>
        <tr>
          <td class="left"><?php echo $column_recipient; ?></td>
          <td class="right"><?php echo $column_value; ?></td>
          <td class="right"><?php echo $column_balance; ?></td>
          <td class="left hide-phone"><?php echo $column_sent; ?></td>
          <td class="left hide-phone"><?php echo $column_expiry; ?></td>
          <td class="left"><?php echo $column_state; ?></td>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($vouchers as $voucher) { ?>
        <tr>
          <td class="left"><?php echo $voucher['recipient']; ?><br /><small><?php echo $voucher['email']; ?></small></td>
          <td class="right"><?php echo $voucher['value']; ?></td>
          <td class="right"><?php echo $voucher['balance']; ?></td>
          <td class="left hide-phone"><?php echo $voucher['sent']; ?></td>
          <td class="left hide-phone"><?php echo $voucher['expiry']; ?></td>
          <td class="left"><?php echo $voucher['state']; ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  <?php } else { ?>
    <div class="content"><?php echo $text_none; ?></div>
  <?php } ?>
  <div class="buttons">
    <div class="left"><a href="<?php echo $back; ?>" class="button"><i class="fa fa-arrow-left"></i> &nbsp; <?php echo $button_back; ?></a></div>
    <div class="right"><a href="<?php echo $add; ?>" class="button"><?php echo $button_add; ?></a></div>
  </div>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>