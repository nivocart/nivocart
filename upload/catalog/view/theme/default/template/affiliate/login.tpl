<?php echo $header; ?>
<?php echo $content_higher; ?>
<?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
<?php } ?>
<?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($this->config->get($template . '_breadcrumbs')) { ?>
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php } ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title; ?></h1>
  <?php echo $text_description; ?>
  <div class="login-content">
    <div class="left">
      <h2><?php echo $text_new_affiliate; ?></h2>
      <div class="content">
        <?php echo $text_register_account; ?> 
        <a href="<?php echo $register; ?>" class="button"><?php echo $button_continue; ?></a>
      </div>
    </div>
    <div class="right">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
      <h2><?php echo $text_returning_affiliate; ?></h2>
        <div class="content">
          <p><?php echo $text_i_am_returning_affiliate; ?></p>
          <?php echo $entry_email; ?><br />
          <input type="text" name="email" value="<?php echo $email; ?>" size="30" />
          <br />
          <br />
          <?php echo $entry_password; ?><br />
          <input type="password" name="password" value="<?php echo $password; ?>" />
          <br />
          <a href="<?php echo $forgotten; ?>"><?php echo $text_forgotten; ?></a><br />
          <br />
          <input type="submit" value="<?php echo $button_login; ?>" class="button" />
        <?php if ($redirect) { ?>
          <input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
        <?php } ?>
        </div>
      </form>
    </div>
  </div>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>
<?php echo $footer; ?>