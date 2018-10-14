<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
<?php if ($error_warning) { ?>
  <div class="success"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
<?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/report.png" alt="" /> <?php echo $heading_title; ?></h1>
    </div>
    <div class="content-body">
    <?php if ($navigation_hi) { ?>
      <div class="pagination" style="margin-bottom:10px;"><?php echo $pagination; ?></div>
    <?php } ?>
      <div class="report">
        <div class="left"><i class="fa fa-search" style="font-size:19px;"></i></div>
        <div class="left"><em><?php echo $entry_date_start; ?></em> <input type="text" name="filter_date_start" value="<?php echo $filter_date_start; ?>" id="date-start" size="12" /> <img src="view/image/calendar.png" alt="" /></div>
        <div class="left"><em><?php echo $entry_date_end; ?></em> <input type="text" name="filter_date_end" value="<?php echo $filter_date_end; ?>" id="date-end" size="12" /> <img src="view/image/calendar.png" alt="" /></div>
        <div class="right"><a onclick="filter();" class="button-filter ripple"><?php echo $button_filter; ?></a></div>
      </div>
      <table class="list">
      <thead>
        <tr>
          <td class="left"><?php if ($sort == 'bad.article_title') { ?>
            <a href="<?php echo $sort_article_title; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_article_name; ?></a>
          <?php } else { ?>
            <a href="<?php echo $sort_article_title; ?>"><?php echo $column_article_name; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
          <?php } ?></td>
          <td class="left"><?php if ($sort == 'bau.author_name') { ?>
            <a href="<?php echo $sort_author_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_author_name; ?></a>
          <?php } else { ?>
            <a href="<?php echo $sort_author_name; ?>"><?php echo $column_author_name; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
          <?php } ?></td>
          <td class="left"><?php if ($sort == 'bv.view') { ?>
            <a href="<?php echo $sort_view; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_viewed; ?></a>
          <?php } else { ?>
            <a href="<?php echo $sort_view; ?>"><?php echo $column_viewed; ?>&nbsp;&nbsp;<img src="view/image/sort.png" alt="" /></a>
          <?php } ?></td>
          <td class="left"><?php echo $column_percent; ?></td>
        </tr>
      </thead>
      <tbody>
    <?php if ($blog_views) { ?>
      <?php foreach ($blog_views as $view) { ?>
        <tr>
          <td class="left"><?php echo $view['article_title']; ?></td>
          <td class="left"><?php echo $view['author_name']; ?></td>
          <td class="center"><?php echo $view['viewed']; ?></td>
          <td class="center"><?php echo $view['percent']; ?></td>
        </tr>
      <?php } ?>
    <?php } else { ?>
        <tr class="even">
          <td colspan="4" class="center"><?php echo $text_no_results; ?></td>
        </tr>
    <?php } ?>
      </tbody>
      </table>
    <?php if ($navigation_lo) { ?>
      <div class="pagination"><?php echo $pagination; ?></div>
    <?php } ?>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function filter() {
	url = 'index.php?route=blog/report&token=<?php echo $token; ?>';

	var filter_date_start = $('input[name=\'filter_date_start\']').attr('value');

	if (filter_date_start) {
		url += '&filter_date_start=' + encodeURIComponent(filter_date_start);
	}

	var filter_date_end = $('input[name=\'filter_date_end\']').attr('value');

	if (filter_date_end) {
		url += '&filter_date_end=' + encodeURIComponent(filter_date_end);
	}

	location = url;
}
//--></script>

<script type="text/javascript"><!--
window.addEventListener("keydown", function(event) {
	if (event.defaultPrevented) {
		return;
	}

	switch (event.key) {
	case "Enter": filter();
		break;
	default:
		return;
	}

	event.preventDefault();
}, true);
//--></script>

<script type="text/javascript"><!--
$(document).ready(function() {
	$('#date-start').datepicker({dateFormat: 'yy-mm-dd'});
	$('#date-end').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script>

<?php echo $footer; ?>