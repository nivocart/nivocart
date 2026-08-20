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
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
  <h2><?php echo $text_edit_product; ?></h2>
  <div class="content">
    <table class="form">
      <tr>
        <td><span class="required">*</span> <?php echo $entry_product; ?></td>
        <td>
          <input type="text" name="name" value="<?php echo $name; ?>" size="30" />
          <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
          <?php if ($error_product) { ?>
            <span class="error"><?php echo $error_product; ?></span>
          <?php } ?>
        </td>
      </tr>
    </table>
  </div>
  <div class="buttons">
    <div class="left"><a href="<?php echo $back; ?>" class="button"><i class="fa fa-arrow-left"></i> &nbsp; <?php echo $button_back; ?></a></div>
    <div class="right"><input type="submit" value="<?php echo $button_continue; ?>" class="button" /></div>
  </div>
  </form>
  <?php echo $content_low; ?>
</div>
<?php echo $content_lower; ?>

<script type="text/javascript"><!--
var productSelect = new TomSelect('input[name="name"]', {
	dropdownParent: 'body',
    valueField: 'product_id',
    labelField: 'name',
    searchField: ['name'],
    placeholder: 'Search ...',
    create: false,
    maxOptions: 20,
    closeAfterSelect: false,
    shouldLoad: function(query) {
        return query.length >= 2;
    },
    load: function(query, callback) {
        var url = 'index.php?route=affiliate/product/autocomplete' + '&token=<?php echo $token; ?>' + '&filter_name=' + encodeURIComponent(query);
        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(json) { callback(json); })
            .catch(function() { callback(); });
    },
    onItemAdd: function(value, item) {
        // Set the hidden product_id field
        document.querySelector('input[name="product_id"]').value = value;
        // Set the visible name field to the selected label
        document.querySelector('input[name="name"]').value = item.textContent.trim();
        // Clear Tom Select's own input and cached options so next search is fresh
        productSelect.clear(true);
        productSelect.clearOptions();
        productSelect.setTextboxValue('');
    }
});
//--></script>

<?php echo $footer; ?>