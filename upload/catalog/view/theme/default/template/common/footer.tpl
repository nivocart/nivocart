<?php if ($this->config->get($template . '_back_to_top')) { ?>
<p id="backtotop" style="display:none;">
  <a href="#" title="Back to top"><span class="sr-only">Back to top</span></a>
</p>
<?php } ?>
<div id="footer-holder" class="<?php echo $footer_class; ?>">
  <div id="footer" class="<?php echo $mod_shape; ?> <?php echo $mod_color; ?>">
  <?php if ($footer_blocks) { ?>
  <?php $col_width = (int)(($this->config->get($template . '_footer_big_column')) ? (72 / max(1, $max_position)) : (100 / max(1, $max_position))); ?>
  <div class="column-one" style="width:<?php echo $col_width; ?>%; display:<?php echo ($max_position > 0) ? 'block' : 'none'; ?>">
  <?php foreach ($footer_blocks as $footer_block) { ?>
  <?php if (($footer_block['position'] === '1') && $footer_block['status']) { ?>
  <h3><?php echo $footer_block['name']; ?></h3>
  <ul>
  <?php foreach ($footer_routes as $footer_route) { ?>
  <?php if ($footer_route['footer_id'] === $footer_block['footer_id']) { ?>
  <li><a href="<?php echo $footer_route['route']; ?>"><?php echo $footer_route['title']; ?></a></li>
  <?php } ?>
  <?php } ?>
  </ul>
  <?php } ?>
  <?php } ?>
  </div>
  <div class="column-two" style="width:<?php echo $col_width; ?>%; display:<?php echo ($max_position > 1) ? 'block' : 'none'; ?>">
  <?php foreach ($footer_blocks as $footer_block) { ?>
  <?php if (($footer_block['position'] === '2') && $footer_block['status']) { ?>
  <h3><?php echo $footer_block['name']; ?></h3>
  <ul>
  <?php foreach ($footer_routes as $footer_route) { ?>
  <?php if ($footer_route['footer_id'] === $footer_block['footer_id']) { ?>
  <li><a href="<?php echo $footer_route['route']; ?>"><?php echo $footer_route['title']; ?></a></li>
  <?php } ?>
  <?php } ?>
  </ul>
  <?php } ?>
  <?php } ?>
  </div>
  <div class="column-three" style="width:<?php echo $col_width; ?>%; display:<?php echo ($max_position > 2) ? 'block' : 'none'; ?>">
  <?php foreach ($footer_blocks as $footer_block) { ?>
  <?php if (($footer_block['position'] === '3') && $footer_block['status']) { ?>
  <h3><?php echo $footer_block['name']; ?></h3>
  <ul>
  <?php foreach ($footer_routes as $footer_route) { ?>
  <?php if ($footer_route['footer_id'] === $footer_block['footer_id']) { ?>
  <li><a href="<?php echo $footer_route['route']; ?>"><?php echo $footer_route['title']; ?></a></li>
  <?php } ?>
  <?php } ?>
  </ul>
  <?php } ?>
  <?php } ?>
  </div>
  <div class="column-four" style="width:<?php echo $col_width; ?>%; display:<?php echo ($max_position > 3) ? 'block' : 'none'; ?>">
  <?php foreach ($footer_blocks as $footer_block) { ?>
  <?php if (($footer_block['position'] === '4') && $footer_block['status']) { ?>
  <h3><?php echo $footer_block['name']; ?></h3>
  <ul>
  <?php foreach ($footer_routes as $footer_route) { ?>
  <?php if ($footer_route['footer_id'] === $footer_block['footer_id']) { ?>
  <li><a href="<?php echo $footer_route['route']; ?>"><?php echo $footer_route['title']; ?></a></li>
  <?php } ?>
  <?php } ?>
  </ul>
  <?php } ?>
  <?php } ?>
  </div>
  <?php if ($this->config->get($template . '_footer_big_column')) { ?>
  <div class="big-column">
  <?php if ($this->config->get($template . '_footer_location')) { ?>
  <p class="icon-location-<?php echo $footer_class; ?>"><?php echo $company; ?><br /><?php echo $address; ?></p>
  <?php } ?>
  <?php if ($this->config->get($template . '_footer_phone')) { ?>
  <p class="icon-phone-<?php echo $footer_class; ?>"><?php echo $telephone; ?></p>
  <?php } ?>
  <?php if ($this->config->get($template . '_footer_email')) { ?>
  <p class="icon-mail-<?php echo $footer_class; ?>"><?php echo $email; ?></p>
  <?php } ?>
  <span>
  <?php if ($this->config->get($template . '_footer_instagram') && $instagram) { ?>
  <a href="<?php echo $instagram; ?>" target="_blank" rel="noopener noreferrer" class="icon-instagram" title="Instagram"><span class="sr-only">Instagram</span></a>
  <?php } ?>
  <?php if ($this->config->get($template . '_footer_pinterest') && $pinterest) { ?>
  <a href="<?php echo $pinterest; ?>" target="_blank" rel="noopener noreferrer" class="icon-pinterest" title="Pinterest"><span class="sr-only">Pinterest</span></a>
  <?php } ?>
  <?php if ($this->config->get($template . '_footer_twitter') && $twitter) { ?>
  <a href="<?php echo $twitter; ?>" target="_blank" rel="noopener noreferrer" class="icon-twitter" title="Twitter"><span class="sr-only">Twitter</span></a>
  <?php } ?>
  <?php if ($this->config->get($template . '_footer_facebook') && $facebook) { ?>
  <a href="<?php echo $facebook; ?>" target="_blank" rel="noopener noreferrer" class="icon-facebook" title="Facebook"><span class="sr-only">Facebook</span></a>
  <?php } ?>
  </span>
  </div>
  <?php } ?>
  <?php } ?>
  </div>
</div>
<div id="footer-bottom" style="overflow:hidden;">
  <?php if ($web_design) { ?>
  <div style="float:right;"><?php echo $web_design; ?></div>
  <?php } ?>
  <?php if ($this->config->get($template . '_powered_by')) { ?>
  <div id="powered"><?php echo $powered; ?></div>
  <?php } ?>
</div>
<?php if ($matomo) { echo $matomo; } ?>
</div><!-- /.container -->
</div><!-- /#container -->

<?php foreach ($scripts as $script) { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>

<?php if ($this->config->get($template . '_right_click')) { ?>
<script type="text/javascript">
document.addEventListener('selectstart', function(e) { e.preventDefault(); });
document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
$('img').on('mousedown', function() { return false; });
</script>
<?php } ?>

<?php if ($this->config->get($template . '_back_to_top')) { ?>
<script type="text/javascript">
$(document).ready(function() {
  $('#backtotop').hide();
  $(function() {
    $(window).scroll(function() {
      if ($(this).scrollTop() > 100) {
        $('#backtotop').fadeIn();
      } else {
        $('#backtotop').fadeOut();
      }
    });
    $('#backtotop a').click(function() {
      $('body,html').animate({scrollTop:0}, 800);
      return false;
    });
  });
});
</script>
<?php } ?>

<?php if ($cookie_consent) { ?>
<style>
  :root {
    --cc-popup-bg:   <?php echo $cookie_popup; ?>;
    --cc-text:       <?php echo $cookie_text; ?>;
    --cc-btn-bg:     <?php echo $cookie_button; ?>;
    --cc-btn-text:   #ffffff;
    --cc-radius:     6px;
    --cc-shadow:     0 4px 24px rgba(0,0,0,0.18);
    --cc-z:          99999;
    --cc-transition: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  }

  #cc-banner {
    position: fixed;
    <?php if ($cookie_position === 'top') { ?>
    top: 0;
    <?php } else { ?>
    bottom: 0;
    <?php } ?>
    left: 0;
    right: 0;
    z-index: var(--cc-z);
    background: var(--cc-popup-bg);
    color: var(--cc-text);
    box-shadow: var(--cc-shadow);
    font-family: inherit;
    font-size: 14px;
    line-height: 1.5;
    <?php if ($cookie_position === 'top') { ?>
    transform: translateY(-100%);
    <?php } else { ?>
    transform: translateY(100%);
    <?php } ?>
    transition: transform var(--cc-transition), opacity var(--cc-transition);
    opacity: 0;
  }

  #cc-banner.cc-visible {
    transform: translateY(0);
    opacity: 1;
  }

  <?php if ($cookie_position === 'top') { ?>
  body.cc-top-offset {
    transition: margin-top var(--cc-transition);
  }
  <?php } ?>

  #cc-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  #cc-message {
    flex: 1 1 280px;
    margin: 0;
    color: var(--cc-text);
  }

  #cc-message a {
    color: var(--cc-btn-bg);
    text-decoration: underline;
    white-space: nowrap;
  }

  #cc-message a:hover {
    opacity: 0.85;
  }

  #cc-accept {
    flex-shrink: 0;
    background: var(--cc-btn-bg);
    color: var(--cc-btn-text);
    border: none;
    border-radius: var(--cc-radius);
    padding: 9px 22px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
    white-space: nowrap;
  }

  #cc-accept:hover {
    opacity: 0.88;
    transform: scale(1.03);
  }

  #cc-accept:focus-visible {
    outline: 3px solid var(--cc-btn-bg);
    outline-offset: 3px;
  }
</style>

<div id="cc-banner" role="dialog" aria-live="polite" aria-label="Cookie consent">
  <div id="cc-inner">
    <p id="cc-message">
      <?php echo $text_message; ?>
      <a href="<?php echo $cookie_privacy; ?>" target="_blank" rel="noopener noreferrer">
        <?php echo $text_policy; ?>
      </a>
    </p>
    <button id="cc-accept" type="button"><?php echo $text_accept; ?></button>
  </div>
</div>

<script>
(function () {
  'use strict';

  var COOKIE_NAME = 'cc_accepted';
  var EXPIRY_DAYS = <?php echo (int)$cookie_age; ?>;
  var POSITION = '<?php echo ($cookie_position === 'top') ? 'top' : 'bottom'; ?>';

  /* ── Helpers ─────────────────────────────────────── */
  function getCookie(name) {
    var match = document.cookie.match(
      new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)')
    );
    return match ? decodeURIComponent(match[1]) : null;
  }

  function setCookie(name, value, days) {
    var expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = name + '=' + encodeURIComponent(value) +
      '; expires=' + expires +
      '; path=/' +
      '; SameSite=Lax' +
      '<?php echo $cookie_secure; ?>';
  }

  /* ── Banner logic ─────────────────────────────────── */
  var banner = document.getElementById('cc-banner');
  var btnAccept = document.getElementById('cc-accept');

  function showBanner() {
    banner.classList.add('cc-visible');

    if (POSITION === 'top') {
      banner.addEventListener('transitionend', function nudge() {
        document.body.style.marginTop = banner.offsetHeight + 'px';
        banner.removeEventListener('transitionend', nudge);
      });
    }
  }

  function hideBanner() {
    banner.classList.remove('cc-visible');

    if (POSITION === 'top') {
      document.body.style.marginTop = '';
    }
  }

  function acceptCookies() {
    setCookie(COOKIE_NAME, '1', EXPIRY_DAYS);
    hideBanner();
  }

  /* ── Init ─────────────────────────────────────────── */
  if (!getCookie(COOKIE_NAME)) {
    window.addEventListener('load', function () {
      setTimeout(showBanner, 200);
    });
  }

  btnAccept.addEventListener('click', acceptCookies);

})();
</script>
<?php } ?>

</body>
</html>
