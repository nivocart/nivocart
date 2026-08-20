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
  <p><?php echo $text_description; ?></p>
  <p><?php echo $text_code; ?><br /><br />
    <textarea cols="40" rows="1"><?php echo $code; ?></textarea>
  </p>
  <p><?php echo $text_generator; ?><br /><br />
    <input type="text" name="name" value="" size="40" />
  </p>
  <p><?php echo $text_link; ?><br /><br />
    <textarea name="link" cols="40" rows="5"></textarea>
  </p>
  <div class="buttons">
    <div class="left"><a href="<?php echo $back; ?>" class="button"><i class="fa fa-arrow-left"></i> &nbsp; <?php echo $button_back; ?></a></div>
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
new TomSelect('input[name="name"]', {
	dropdownParent: 'body',
    valueField: 'link',
    labelField: 'name',
    searchField: ['name'],
    placeholder: 'Search ...',
    create: false,
    maxOptions: 20,
    shouldLoad: function(query) {
        return query.length >= 2;
    },
    load: function(query, callback) {
        var url = 'index.php?route=affiliate/tracking/autocomplete' + '&token=<?php echo $token; ?>' + '&filter_name=' + encodeURIComponent(query);
        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(json) { callback(json); })
            .catch(function() { callback(); });
    },
    onChange: function(value) {
        document.querySelector('textarea[name="link"]').value = value;
    }
});
//--></script>

<?php echo $footer; ?>