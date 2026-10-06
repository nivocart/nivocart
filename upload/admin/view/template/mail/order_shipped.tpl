<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your order has been dispatched</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f4; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333333; }
    .wrapper { width: 100%; background: #f4f4f4; padding: 24px 0; }
    .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 4px; overflow: hidden; border: 1px solid #dddddd; }
    .header { background: #3a7d44; padding: 24px 32px; text-align: center; }
    .header h1 { margin: 0; color: #ffffff; font-size: 22px; font-weight: bold; }
    .body { padding: 32px; }
    .body p { margin: 0 0 16px; line-height: 1.6; }
    .tracking-box { background: #f9f9f9; border: 1px solid #dddddd; border-radius: 4px; padding: 16px 24px; margin: 24px 0; }
    .tracking-box table { width: 100%; border-collapse: collapse; }
    .tracking-box td { padding: 6px 0; font-size: 14px; }
    .tracking-box td:first-child { color: #666666; width: 110px; }
    .tracking-box td:last-child { font-weight: bold; color: #333333; }
    .track-btn { display: block; text-align: center; margin: 24px 0; }
    .track-btn a { background: #3a7d44; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 4px; font-size: 15px; font-weight: bold; display: inline-block; }
    .footer { background: #f4f4f4; padding: 16px 32px; text-align: center; font-size: 12px; color: #999999; border-top: 1px solid #dddddd; }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="container">
    <div class="header">
      <h1>Your Order Has Been Dispatched!</h1>
    </div>
    <div class="body">
      <p>Dear <?php echo htmlspecialchars($customer); ?>,</p>
      <p>Great news! Your order <strong>#<?php echo (int)$order_id; ?></strong> has been dispatched and is on its way to you.</p>
      <div class="tracking-box">
        <table>
          <tr>
            <td>Order:</td>
            <td>#<?php echo (int)$order_id; ?></td>
          </tr>
          <tr>
            <td>Tracking:</td>
            <td><?php echo htmlspecialchars($tracking); ?></td>
          </tr>
          <?php if ($tracking_carrier): ?>
          <tr>
            <td>Carrier:</td>
            <td><?php echo htmlspecialchars($tracking_carrier); ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
      <div class="track-btn">
        <a href="https://t.17track.net/en#nums=<?php echo urlencode($tracking); ?>" target="_blank">Track My Parcel</a>
      </div>
      <p>If you have any questions about your order, please don't hesitate to contact us.</p>
      <p>Thank you for shopping with <strong><?php echo htmlspecialchars($store_name); ?></strong>.</p>
      <p>Kind regards,<br><?php echo htmlspecialchars($store_name); ?> Team</p>
    </div>
    <div class="footer">
      &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($store_name); ?>. All rights reserved.
    </div>
  </div>
</div>
</body>
</html>