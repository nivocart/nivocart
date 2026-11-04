<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $title; ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f2f2f2; font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#333333;">
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
          <td style="padding:10px 28px 8px 28px; line-height:24px;">
            <p style="margin:0 0 14px 0;"><?php echo $text_greeting; ?></p>
            <p style="margin:0 0 14px 0;"><?php echo $text_intro; ?></p>
          </td>
        </tr>
        <tr>
          <td style="padding:0 28px 8px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #dddddd; border-radius:6px;">
              <tr>
                <td style="padding:10px 14px; font-size:14px; color:#777777;"><?php echo $text_value; ?></td>
                <td align="right" style="padding:10px 14px; font-weight:bold;"><?php echo $amount; ?></td>
              </tr>
              <tr>
                <td style="padding:10px 14px; font-size:14px; color:#777777; border-top:1px solid #dddddd;"><?php echo $text_recipient; ?></td>
                <td align="right" style="padding:10px 14px; border-top:1px solid #dddddd;"><?php echo $to_name; ?></td>
              </tr>
              <tr>
                <td colspan="2" style="padding:10px 14px; font-size:14px; color:#777777; border-top:1px solid #dddddd;"><?php echo $text_expiry; ?></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:14px 28px 24px 28px; font-size:14px; line-height:22px; color:#555555;">
            <p style="margin:0;"><?php echo $text_footer; ?></p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>