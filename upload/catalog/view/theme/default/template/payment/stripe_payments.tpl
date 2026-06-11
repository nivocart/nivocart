<h2><?php echo $text_credit_card; ?></h2>
<?php if ($stripe_error): ?>
<div class="attention"><?php echo $stripe_error; ?></div>
<?php endif; ?>
<div class="content" id="payment">
  <span class="payment-errors error" id="stripe-card-errors" role="alert"></span>
  <table class="form">
    <tr>
      <td><?php echo $entry_cc_owner; ?></td>
      <td><input type="text" id="cc_owner" name="cc_owner" value="" size="30" /></td>
    </tr>
    <tr>
      <td colspan="2">
        <!-- Stripe Elements mounts the card input here — no raw card data touches the server -->
        <div id="stripe-card-element" style="padding: 8px; border: 1px solid #ccc; border-radius: 3px; background: #fff;"></div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <img src="catalog/view/theme/<?php echo $template; ?>/image/payment/stripe-cc-logos.png" alt="" />
      </td>
    </tr>
  </table>
</div>
<div class="buttons">
  <div class="right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
  </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    var publishableKey = '<?php echo $stripe_publishable_key; ?>';
    var clientSecret = '<?php echo $stripe_client_secret; ?>';
    var sendUrl = 'index.php?route=payment/stripe_payments/send';

    // Bail out cleanly if the PaymentIntent failed to create server-side
    if (!clientSecret) {
        document.getElementById('button-confirm').disabled = true;
        return;
    }

    var stripe = Stripe(publishableKey);
    var elements = stripe.elements();

    var cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '14px',
                color: '#333',
                '::placeholder': { color: '#aaa' }
            },
            invalid: { color: '#c0392b' }
        }
    });

    cardElement.mount('#stripe-card-element');

    // Real-time validation feedback
    cardElement.on('change', function (event) {
        document.getElementById('stripe-card-errors').textContent = event.error ? event.error.message : '';
    });

    document.getElementById('button-confirm').addEventListener('click', function () {
        var btn = this;
        var errorDiv = document.getElementById('stripe-card-errors');
        var ownerName = document.getElementById('cc_owner').value;

        btn.disabled = true;
        btn.value = '<?php echo $text_wait; ?>';
        errorDiv.textContent = '';

        stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: { name: ownerName }
            }
        }).then(function (result) {
            if (result.error) {
                errorDiv.textContent = result.error.message;
                btn.disabled = false;
                btn.value = '<?php echo $button_confirm; ?>';
                return;
            }

			if (result.paymentIntent.status === 'succeeded') {
				fetch('index.php?route=payment/stripe_payments/send', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'payment_intent_id=' + encodeURIComponent(result.paymentIntent.id)
				})
				.then(function(r) { return r.json(); })
				.then(function(json) {
					if (json['error']) {
						errorDiv.textContent = json['error'];
						btn.disabled = false;
						btn.value = '<?php echo $button_confirm; ?>';
						return;
					}
					if (json['success']) {
						location = json['success'];
					}
				})
				.catch(function() {
					errorDiv.textContent = 'Network error — please contact support.';
					btn.disabled = false;
					btn.value = '<?php echo $button_confirm; ?>';
				});
			}
        });
    });
})();
</script>
