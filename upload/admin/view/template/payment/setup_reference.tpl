<div class="box setup-ref">
  <div class="heading">
    <h1><img src="view/image/api.png" alt="" /> <?php echo $setup_title; ?></h1>
  </div>
  <div class="content">
    <table class="form">
    <?php foreach ($setup_sections as $setup_section) { ?>
      <tr>
        <td colspan="2" class="setup-step">
          <strong><?php echo $setup_section['title']; ?></strong>
          <?php if (!empty($setup_section['intro'])) { ?>
            <br /><?php echo $setup_section['intro']; ?>
          <?php } ?>
        </td>
      </tr>
      <?php if (!empty($setup_section['url'])) { ?>
      <tr>
        <td class="setup-label"><?php echo $setup_url_label; ?></td>
        <td><input type="text" value="<?php echo htmlspecialchars($setup_url); ?>" size="60" readonly="readonly" onclick="this.select();" class="setup-url" title="<?php echo $setup_url_hint; ?>" /></td>
      </tr>
      <?php } ?>
      <?php if (!empty($setup_section['rows'])) { ?>
        <?php foreach ($setup_section['rows'] as $setup_row) { ?>
      <tr>
        <td class="setup-label"><?php echo $setup_row[0]; ?></td>
        <td><?php echo $setup_row[1]; ?></td>
      </tr>
        <?php } ?>
      <?php } ?>
    <?php } ?>
    </table>
  </div>
</div>