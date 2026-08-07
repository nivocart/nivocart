<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
  </div>
  <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <?php if ($error) { ?>
    <div class="warning"><?php echo $error; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?> (<?php echo $total_extensions; ?>)</h1>
      <div class="buttons">
        <a id="sort-az" class="button ripple"><i class="fa fa-sort-alpha-asc"></i> &nbsp; A &rarr; Z</a>
        <a id="sort-za" class="button ripple"><i class="fa fa-sort-alpha-desc"></i> &nbsp; Z &rarr; A</a>
        <a id="installed" class="button ripple"><i class="fa fa-refresh"></i> &nbsp; <?php echo $button_filter; ?></a>
        <a onclick="location = '<?php echo $close; ?>';" class="button-cancel ripple"><?php echo $button_close; ?></a>
      </div>
    </div>
    <div class="content-body">
      <table class="list">
        <thead>
          <tr>
            <td class="left"><?php echo $column_name; ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody id="extension-list">
        <?php if ($extensions) { ?>
          <?php foreach ($extensions as $extension) { ?>
          <tr<?php echo ($extension['set']) ? '' : ' class="not-set"'; ?> data-name="<?php echo htmlspecialchars(strtolower($extension['name']), ENT_QUOTES); ?>" data-installed="<?php echo $extension['set'] ? '1' : '0'; ?>">
            <td class="left"><?php echo $extension['name']; ?></td>
            <td class="right"><?php foreach ($extension['action'] as $action) { ?>
              <?php if ($extension['set'] && ($action['type'] === 'uninstall')) { ?>
                <a class="button-form-<?php echo $action['type']; ?> ripple" data-title="<?php echo $action['text']; ?>" href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a>
              <?php } else { ?>
                <a class="button-form-<?php echo $action['type']; ?> ripple" href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a>
              <?php } ?>
            <?php } ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="2"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$(document).ready(function() {
    var sortDir = 'az';
    var filterOn = false;

    function applyState() {
        var $tbody = $('#extension-list');
        var $rows = $tbody.find('tr[data-name]').detach().toArray();

        // Sort
        $rows.sort(function(a, b) {
            var nameA = $(a).data('name');
            var nameB = $(b).data('name');

            if (nameA < nameB) {
				return sortDir === 'az' ? -1 : 1;
			}

            if (nameA > nameB) {
				return sortDir === 'az' ? 1 : -1;
			}

            return 0;
        });

        // Re-append and apply filter
        $.each($rows, function(i, row) {
            var $row = $(row);

            $tbody.append($row);

            if (filterOn) {
                $row.toggle($row.data('installed') === 1);
            } else {
                $row.show();
                // Restore not-set class visibility
                if ($row.data('installed') === 0) {
                    $row.addClass('not-set');
                }
            }
        });

        // Button active states
        $('#sort-az').toggleClass('button-repair', sortDir === 'az');
        $('#sort-za').toggleClass('button-repair', sortDir === 'za');
        $('#installed').toggleClass('button-repair', filterOn);
    }

    $('#sort-az').on('click', function() {
        sortDir = 'az';
        applyState();
    });

    $('#sort-za').on('click', function() {
        sortDir = 'za';
        applyState();
    });

    $('#installed').on('click', function() {
        filterOn = !filterOn;
        applyState();
    });
});
//--></script>

<script type="text/javascript"><!--
$('a.button-form-uninstall').confirm({
	content: '',
	icon: 'fa fa-question-circle',
	theme: 'light',
	useBootstrap: false,
	boxWidth: 580,
	animation: 'zoom',
	closeAnimation: 'scale',
	opacity: 0.1
});
$('a.button-form-uninstall').on('click', function() {
	$.dialog({
		title: '<?php echo $text_confirm_uninstall; ?>',
		content: '<?php echo $text_confirm; ?>',
		icon: 'fa fa-exclamation-circle',
		theme: 'light',
		useBootstrap: false,
		boxWidth: 580,
		animation: 'zoom',
		closeAnimation: 'scale',
		opacity: 0.1,
		buttons: {
			confirm: function() {
				location.href = this.$target.attr('href');
			},
			cancel: function() {}
		}
	});
});
//--></script>

<?php echo $footer; ?>