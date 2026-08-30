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
  <p><?php echo $text_guest_track_intro; ?></p>
  <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="guest-track-form">
    <div class="content">
      <div class="left">
        <span class="required">*</span> <?php echo $entry_order_id; ?><br />
        <input type="text" name="order_id" value="<?php echo $order_id; ?>" size="15" autocomplete="off" /><br /><br />
        <span class="required">*</span> <?php echo $entry_email_order; ?><br />
        <input type="text" name="email" value="<?php echo $email; ?>" size="35" autocomplete="email" /><br />
      </div>
      <div class="buttons">
        <div class="right">
          <input type="submit" value="<?php echo $button_lookup; ?>" class="button" />
        </div>
      </div>
    </div>
  </form>
  <?php if ($searched && $returns) { ?>
    <table class="list">
      <thead>
        <tr>
          <td class="left"><?php echo $text_return_id; ?></td>
          <td class="left"><?php echo $column_product; ?></td>
          <td class="left"><?php echo $column_reason; ?></td>
          <td class="left"><?php echo $column_status; ?></td>
          <td class="left"><?php echo $column_date_added; ?></td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($returns as $return) { ?>
        <tr>
          <td class="left">#<?php echo $return['return_id']; ?></td>
          <td class="left"><?php echo $return['product']; ?></td>
          <td class="left"><?php echo $return['reason']; ?></td>
          <td class="left"><?php echo $return['status']; ?></td>
          <td class="left"><?php echo $return['date_added']; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>
  <p style="margin-top:20px;"><?php echo $text_guest_no_account; ?></p>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>