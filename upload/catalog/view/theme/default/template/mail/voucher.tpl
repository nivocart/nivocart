<?php
$designs = [
	'general'         => ['band' => '#2c3e50', 'soft' => '#eaeef2', 'icon' => '&#127873;'],
	'birthday'        => ['band' => '#d6336c', 'soft' => '#fbe9f0', 'icon' => '&#127874;'],
	'christmas'       => ['band' => '#b02a30', 'soft' => '#f8e8e9', 'icon' => '&#127876;'],
	'thank_you'       => ['band' => '#2f855a', 'soft' => '#e6f4ec', 'icon' => '&#128591;'],
	'congratulations' => ['band' => '#c77d0a', 'soft' => '#fbf1de', 'icon' => '&#127881;']
];

$colour = $designs[$design] ?? $designs['general'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $title; ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f2f2f2; font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#333333;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0;"><?php echo $title; ?> - <?php echo $amount; ?></div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f2f2f2;">
  <tr>
    <td align="center" style="padding:20px 10px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden;">
        <tr>
          <td align="center" style="padding:18px 20px; font-size:14px; color:#777777;">
            <a href="<?php echo $store_url; ?>" style="color:#777777; text-decoration:none;"><?php echo $store_name; ?></a>
          </td>
        </tr>
        <tr>
          <td align="center" style="background-color:<?php echo $colour['band']; ?>; padding:32px 20px; color:#ffffff;">
            <div style="font-size:42px; line-height:48px;"><?php echo $colour['icon']; ?></div>
            <div style="font-size:22px; line-height:30px; font-weight:bold; padding-top:8px;"><?php echo $text_design; ?></div>
            <div style="font-size:14px; padding-top:18px; opacity:0.9;"><?php echo $text_amount; ?></div>
            <div style="font-size:44px; line-height:52px; font-weight:bold;"><?php echo $amount; ?></div>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 28px 8px 28px; line-height:24px;">
            <p style="margin:0 0 14px 0;"><?php echo $text_greeting; ?></p>
            <p style="margin:0 0 14px 0;"><?php echo $text_from; ?></p>
            <?php if ($message) { ?>
            <p style="margin:0 0 6px 0; font-size:13px; color:#777777;"><?php echo $text_message; ?></p>
            <p style="margin:0 0 14px 0; padding:12px 16px; background-color:<?php echo $colour['soft']; ?>; border-left:4px solid <?php echo $colour['band']; ?>; font-style:italic;"><?php echo $message; ?></p>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:8px 28px 8px 28px;">
            <div style="font-size:13px; color:#777777; padding-bottom:6px;"><?php echo $text_code; ?></div>
            <div style="display:inline-block; padding:14px 26px; border:2px dashed <?php echo $colour['band']; ?>; border-radius:6px; font-family:'Courier New', Courier, monospace; font-size:30px; font-weight:bold; letter-spacing:4px; color:#222222;"><?php echo $code; ?></div>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:22px 28px 8px 28px;">
            <a href="<?php echo $redeem_url; ?>" style="display:inline-block; padding:14px 34px; background-color:<?php echo $colour['band']; ?>; color:#ffffff; font-size:16px; font-weight:bold; text-decoration:none; border-radius:6px;"><?php echo $text_button; ?></a>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:14px 28px 4px 28px; font-size:14px; font-weight:bold; color:<?php echo $colour['band']; ?>;"><?php echo $text_expiry; ?></td>
        </tr>
        <tr>
          <td style="padding:14px 28px 24px 28px; font-size:14px; line-height:22px; color:#555555;">
            <p style="margin:0 0 12px 0;"><?php echo $text_redeem; ?></p>
            <p style="margin:0;"><?php echo $text_footer; ?></p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>