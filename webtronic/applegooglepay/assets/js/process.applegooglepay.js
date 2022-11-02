+function ($) {
    "use strict"
    var stripe_pi = null;
    var first_name = $('#first-name');
    var last_name = $('#last-name');
    var telephone = $('#telephone');
    var email = $('#email');



    var ProcessAppleGooglePay = function (element, options) {
        this.$el = $(element)
        this.options = options || {}
        this.$checkoutForm = this.$el.closest('#checkout-form')
        this.stripe = null
        this.card = null


        $('[name=payment][value=applegooglepay]', this.$checkoutForm).on('change', $.proxy(this.init, this))
    }

    ProcessAppleGooglePay.prototype.init = function () {

        if (this.options.publishableKey === undefined)
            throw new Error('Missing stripe publishable key')

        this.stripe = Stripe(this.options.publishableKey)

        if (this.stripe === null)
            throw new Error('Unable to Init Stripe')


        var stripe = this.stripe;

        var frm = this.$checkoutForm;

        // Used by Stripe to identify this integration
        stripe.registerAppInfo({
            name: "TastyIgniter Stripe Payment Button",
            url: 'https://webtronic.ie/'
        });

        var paymentRequest = stripe.paymentRequest({
            country: orderCountry,
            currency: orderCurrency,
            total: {
                label: storeName,
                amount: orderTotal
            },
            requestPayerName: true,
            requestPayerEmail: true,
            requestPayerPhone: true
        });


// Check the availability of the Payment Request API first.
        paymentRequest.canMakePayment().then(function (result) {
            if (result) {
                prButton.mount('#payment-request-button');

            } else {
                $("#applegooglepay-card-errors").html('<div class="alert alert-warning">Sorry Apple / Google Pay is not available on your device.</div>')
                //uncheck option
                $('input[name="payment"][value="stripeapplepay"]').prop('checked', false);
            }
        });


        var prButton = stripe.elements().create('paymentRequestButton', {
            paymentRequest: paymentRequest,
        });


        paymentRequest.on('paymentmethod', function (ev) {

            $.ajax({
                url: siteUrl + "/ti_applegooglepay/stripe_applegooglepay_init/handler",
                type: "GET",
                dataType: "json",
                success: function (response) {

                    stripe_pi = response.intent;

                    // Confirm the PaymentIntent without handling potential next actions (yet).
                    stripe.confirmCardPayment(
                        stripe_pi,
                        {payment_method: ev.paymentMethod.id},
                        {handleActions: false}
                    ).then(function (confirmResult) {
                        if (confirmResult.error) {
                            // Report to the browser that the payment failed, prompting it to
                            // re-show the payment interface, or show an error message and close
                            // the payment interface.
                            ev.complete('fail');
                            $("#applegooglepay-card-errors").html(result.error.message);

                        } else {
                            // Report to the browser that the confirmation was successful, prompting
                            // it to close the browser payment method collection interface.
                            ev.complete('success');

                            frm.find('input[name="stripe_applepay_intent"]').val(ev.paymentMethod.id);

                            if (email.val() === '') {

                                email.val(ev.payerEmail);

                            }
                            if (telephone.val() === '') {
                                telephone.val(ev.payerPhone);
                            }

                            var splitName = ev.payerName.split(' ');

                            if (first_name.val() === '') {
                                first_name.val(splitName[0]);
                            }

                            if (last_name.val() === '') {
                                last_name.val(splitName[1]);

                            }


                            // Check if the PaymentIntent requires any actions and if so let Stripe.js
                            // handle the flow. If using an API version older than "2019-02-11"
                            // instead check for: `paymentIntent.status === "requires_source_action"`.
                            if (confirmResult.paymentIntent.status === "requires_action") {
                                // Let Stripe.js handle the rest of the payment flow.
                                stripe.confirmCardPayment(clientSecret).then(function (result) {
                                    if (result.error) {

                                        $("#applegooglepay-card-errors").html("<div class='alert alert-danger'>" + result.error.message + "</div>");
                                        // The payment failed -- ask your customer for a new payment method.
                                    } else {
                                        // The payment has succeeded.
                                        $('#checkout-form').submit();

                                    }
                                });
                            } else {

                                // The payment has succeeded.
                                $('#checkout-form').submit();
                            }
                        }


                    });
                }

            })


        });


        //this.$checkoutForm.on('submitCheckoutForm', $.proxy(this.submitFormHandler, this))


    }

    ProcessAppleGooglePay.prototype.validationErrorHandler = function (event) {
        var $el = this.$checkoutForm.find(this.options.errorSelector)
        if (event.error) {
            $el.html(event.error.message);
        } else {
            $el.empty();
        }

        $('.checkout-btn').prop('disabled', false)
        this.card.update({disabled: false});
    }

    ProcessAppleGooglePay.prototype.submitFormHandler = function (event) {

    }

    ProcessAppleGooglePay.DEFAULTS = {
        publishableKey: undefined,
        cardSelector: '#payment-request-button',
        errorSelector: '#applegooglepay-card-errors',
    }



    // PLUGIN DEFINITION
    // ============================

    var old = $.fn.processAppelGooglePay

    $.fn.processAppleGooglePay = function (option) {
        var $this = $(this).first()
        var options = $.extend(true, {}, ProcessAppleGooglePay.DEFAULTS, $this.data(), typeof option == 'object' && option)

        return new ProcessAppleGooglePay($this, options)
    }

    $.fn.processAppleGooglePay.Constructor = ProcessAppleGooglePay

    $.fn.processAppleGooglePay.noConflict = function () {
        $.fn.processAppleGooglePay = old
        return this
    }

    $(document).render(function () {


        $('#appleGooglePayForm').processAppleGooglePay()
    })
}(window.jQuery)
