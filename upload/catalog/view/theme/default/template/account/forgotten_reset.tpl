<?php echo $header; ?>
<?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php echo $content_higher; ?>
<?php echo $content_left; ?><?php echo $content_right; ?>
<div id="content"><?php echo $content_high; ?>
  <h1><?php echo $heading_title_reset; ?></h1>
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
    <div class="content">
    <table class="form">
    <tr>
      <td><span class="required">*</span> <?php echo $entry_password; ?></td>
      <td><input type="password" name="password" id="password1" value="" size="40" />
	  <span id="check" class="hidden"></span></td>
    </tr>
    <tr>
      <td><span class="required">*</span> <?php echo $entry_confirm; ?></td>
      <td><input type="password" name="confirm" id="password2" value="" size="40" />&nbsp;
	  <span id="match" class="hidden"></span></td>
    </tr>
    </table>
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" />
    </div>
    <div class="buttons">
      <div class="right"><input type="submit" value="<?php echo $button_continue; ?>" class="button" /></div>
    </div>
  </form>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
$(document).ready(function() {
	$('#password1').on('keyup', function() {
		$('#check').html(checkStrength($('#password1').val()));
	});

	function checkStrength(password1) {
		var strength = 0;

		if (password1.length < 4) {
			$('#check').removeClass().addClass('short');
			return '<img src="catalog/view/theme/<?php echo $template; ?>/image/account/password-short.png" alt="" />';
		}

		if (password1.length > 4) { strength += 1; };
		if (password1.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) { strength += 1; };
		if (password1.match(/([a-zA-Z])/) && password1.match(/([0-9])/)) { strength += 1; };
		if (password1.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) { strength += 1; };
		if (password1.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/)) { strength += 1; };

		if (strength < 2) {
			$('#check').removeClass().addClass('weak');
			return '<img src="catalog/view/theme/<?php echo $template; ?>/image/account/password-weak.png" alt="" />';
		} else if (strength === 2) {
			$('#check').removeClass().addClass('good');
			return '<img src="catalog/view/theme/<?php echo $template; ?>/image/account/password-good.png" alt="" />';
		} else {
			$('#check').removeClass().addClass('strong');
			return '<img src="catalog/view/theme/<?php echo $template; ?>/image/account/password-strong.png" alt="" />';
		}
	}
});
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
	var password1 = $('#password1');
	var password2 = $('#password2');

	$(password2).on('keyup', function() {
		if (password1.val() && password2.val() === password1.val()) {
			$('#match').removeClass().addClass('match').html('<img src="catalog/view/theme/<?php echo $template; ?>/image/account/tick.png" alt="" />');
		} else {
			$('#match').removeClass('match').addClass('hidden').html('');
		}
	});
});
//--></script>

<?php echo $footer; ?>