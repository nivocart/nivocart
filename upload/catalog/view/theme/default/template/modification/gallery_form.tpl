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
  <p><?php echo $text_form_intro; ?></p>
  <div id="gallery-warning" aria-live="polite"></div>
  <form id="gallery-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" novalidate="novalidate">
    <input type="hidden" name="token" value="<?php echo $token; ?>" />
    <div class="hidden">
      <label for="gallery-website">Website</label>
      <input type="text" name="website" id="gallery-website" value="" tabindex="-1" autocomplete="off" />
    </div>
    <h2><?php echo $text_pet; ?></h2>
    <table class="form">
      <tr>
        <td><span class="required">*</span> <label for="gallery-pet-name"><?php echo $entry_pet_name; ?></label></td>
        <td><input type="text" name="pet_name" id="gallery-pet-name" value="" size="30" maxlength="32" />
          <br /><span class="help"><?php echo $help_pet_name; ?></span>
          <br /><span class="error" id="error-pet_name"></span></td>
      </tr>
      <tr>
        <td><span class="required">*</span> <?php echo $entry_filter; ?></td>
        <td><?php foreach ($filters as $filter_id => $filter_name) { ?>
          <input type="radio" name="filter_id" value="<?php echo $filter_id; ?>" id="gallery-filter-<?php echo $filter_id; ?>" class="radio" />
          <label for="gallery-filter-<?php echo $filter_id; ?>"><span><span></span></span><?php echo $filter_name; ?></label>
          <?php } ?>
          <br /><span class="error" id="error-filter_id"></span></td>
      </tr>
      <tr>
        <td><label for="gallery-description"><?php echo $entry_description; ?></label></td>
        <td><textarea name="description" id="gallery-description" rows="4" maxlength="<?php echo $description_max; ?>"></textarea>
          <span class="help gallery-count"><span id="gallery-count">0</span> / <?php echo $description_max; ?></span>
          <br /><span class="help"><?php echo $help_description; ?></span>
          <br /><span class="error" id="error-description"></span></td>
      </tr>
    </table>
    <h2><?php echo $text_photo; ?></h2>
    <table class="form">
      <tr>
        <td><span class="required">*</span> <label for="gallery-file"><?php echo $text_choose; ?></label></td>
        <td><input type="file" id="gallery-file" class="gallery-file-input" accept="image/jpeg,image/png" />
          <br /><span class="help"><?php echo $text_photo_help; ?></span>
          <br /><span class="error" id="error-image"></span>
          <div id="gallery-crop" class="gallery-crop hidden">
            <p class="help"><?php echo $text_crop_hint; ?></p>
            <div class="gallery-crop-box"><img id="gallery-crop-image" src="" alt="" /></div>
            <p class="help"><?php echo $text_watermark_note; ?></p>
          </div>
        </td>
      </tr>
    </table>
    <h2><?php echo $text_owner; ?></h2>
    <p class="help"><?php echo $text_owner_help; ?></p>
    <table class="form">
      <tr>
        <td><span class="required">*</span> <label for="gallery-fullname"><?php echo $entry_fullname; ?></label></td>
        <td><input type="text" name="fullname" id="gallery-fullname" value="<?php echo $fullname; ?>" size="30" maxlength="64" />
          <br /><span class="error" id="error-fullname"></span></td>
      </tr>
      <tr>
        <td><span class="required">*</span> <label for="gallery-email"><?php echo $entry_email; ?></label></td>
        <td><input type="text" name="email" id="gallery-email" value="<?php echo $email; ?>" size="40" maxlength="96" />
          <br /><span class="error" id="error-email"></span></td>
      </tr>
      <tr>
        <td></td>
        <td><input type="checkbox" name="contact_optin" value="1" id="gallery-optin" class="checkbox" />
          <label for="gallery-optin"><span></span><?php echo $text_optin; ?></label></td>
      </tr>
      <tr>
        <td></td>
        <td><input type="checkbox" name="agree_age" value="1" id="gallery-agree-age" class="checkbox" />
          <label for="gallery-agree-age"><span></span><?php echo $text_agree_age; ?></label>
          <br /><span class="error" id="error-agree_age"></span></td>
      </tr>
      <tr>
        <td></td>
        <td><input type="checkbox" name="agree_publish" value="1" id="gallery-agree-publish" class="checkbox" />
          <label for="gallery-agree-publish"><span></span><?php echo $text_agree_publish; ?></label>
          <br /><span class="error" id="error-agree_publish"></span></td>
      </tr>
    </table>
    <div id="captcha-wrap">
      <div class="captcha-box">
        <div class="captcha-view">
          <img src="<?php echo $captcha_image; ?>" width="186" height="42" alt="<?php echo $entry_captcha; ?>" />
        </div>
      </div>
      <div class="captcha-text">
        <label><?php echo $entry_captcha; ?></label>
        <input type="text" name="captcha" id="captcha" value="" autocomplete="off" />
      </div>
      <div class="captcha-action"><i class="fa fa-repeat"></i></div>
    </div>
    <span class="error" id="error-captcha"></span>
    <div class="buttons">
      <div class="left"><a href="<?php echo $cancel; ?>" class="button-grey"><?php echo $text_back; ?></a></div>
      <div class="right"><button type="submit" id="button-gallery" class="button"><?php echo $button_send; ?></button></div>
    </div>
  </form>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
$(function() {
	var $form = $('#gallery-form');
	var $file = $('#gallery-file');
	var cropper = null;
	var maxBytes = 8388608;
	var minSide = 300;

	$file.simpleFileInput({
		placeholder: '<?php echo $text_no_file; ?>',
		buttonText: '<?php echo $text_select; ?>',
		allowedExts: ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'],
		width: 282
	});

	function resetFile() {
		$file.val('');

		$file.next('.sfi-wrapper').removeClass('sfi-valid sfi-error').find('.sfi-filename').text('<?php echo $text_no_file; ?>').addClass('empty');
	}

	function showWarning(message) {
		$('#gallery-warning').empty().append($('<div class="warning"></div>').text(message));
	}

	function clearErrors() {
		$form.find('.error').text('');
		$('#gallery-warning').empty();
	}

	$('#gallery-description').on('input', function() {
		$('#gallery-count').text($(this).val().length);
	});

	$file.on('change', function() {
		var file = this.files && this.files[0];

		$('#error-image').text('');

		if (cropper) {
			cropper.destroy();

			cropper = null;
		}

		$('#gallery-crop').addClass('hidden');

		if (!file) {
			return;
		}

		if (['image/jpeg', 'image/png'].indexOf(file.type) === -1 || file.size > maxBytes) {
			$('#error-image').text('<?php echo $error_js_type; ?>');

			resetFile();

			return;
		}

		var image = document.getElementById('gallery-crop-image');

		image.onload = function() {
			if (image.naturalWidth < minSide || image.naturalHeight < minSide) {
				$('#error-image').text('<?php echo $error_js_small; ?>');

				resetFile();

				return;
			}

			$('#gallery-crop').removeClass('hidden');

			cropper = new Cropper(image, {
				aspectRatio: 1,
				viewMode: 1,
				dragMode: 'move',
				autoCropArea: 1,
				background: false,
				guides: false,
				center: false,
				toggleDragModeOnDblclick: false
			});
		};

		image.src = URL.createObjectURL(file);
	});

	$form.on('submit', function(e) {
		e.preventDefault();

		clearErrors();

		if (!cropper) {
			$('#error-image').text('<?php echo $error_no_photo; ?>');

			showWarning('<?php echo $error_general; ?>');

			return;
		}

		var area = cropper.getData(true);

		if (area.width < minSide || area.height < minSide) {
			$('#error-image').text('<?php echo $error_js_small; ?>');

			showWarning('<?php echo $error_general; ?>');

			return;
		}

		// The cropped square, no bigger than 900 px (the browser also turns the photo upright)
		var canvas = cropper.getCroppedCanvas({
			maxWidth: 900,
			maxHeight: 900,
			fillColor: '#fff',
			imageSmoothingQuality: 'high'
		});

		if (!canvas) {
			showWarning('<?php echo $error_general; ?>');

			return;
		}

		canvas.toBlob(function(blob) {
			if (!blob) {
				showWarning('<?php echo $error_general; ?>');

				return;
			}

			var data = new FormData($form[0]);

			data.append('image', blob, 'photo.jpg');

			$.ajax({
				url: $form.attr('action'),
				type: 'post',
				data: data,
				processData: false,
				contentType: false,
				dataType: 'json',
				beforeSend: function() {
					$('#button-gallery').prop('disabled', true);
					$('#gallery-warning').html('<div class="attention"><img src="catalog/view/theme/<?php echo $template; ?>/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
				},
				success: function(json) {
					$('#gallery-warning').empty();

					if (json['success']) {
						window.location = json['redirect'];

						return;
					}

					$('#button-gallery').prop('disabled', false);

					if (json['error']) {
						$.each(json['error'], function(key, message) {
							if (key === 'warning') {
								showWarning(message);
							} else {
								$('#error-' + key).text(message);
							}
						});

						if (json['error']['captcha']) {
							$('#captcha').val('');

							if (typeof refreshCaptcha === 'function') {
								refreshCaptcha();
							}
						}
					}
				},
				error: function() {
					$('#button-gallery').prop('disabled', false);

					showWarning('<?php echo $error_general; ?>');
				}
			});
		}, 'image/jpeg', 0.9);
	});
});
//--></script>

<?php echo $footer; ?>