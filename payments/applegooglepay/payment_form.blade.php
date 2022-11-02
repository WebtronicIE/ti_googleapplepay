<div
    id="appleGooglePayForm"
    class="payment-form w-100"
    data-publishable-key="{{ $paymentMethod->getPublishableKey() }}"
    data-error-selector="#applegooglepay-card-errors"
>
    @foreach ($paymentMethod->getHiddenFields() as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}"/>
    @endforeach



    <div class="form-group">



            <div id="payment-request-button">
                <!-- A Stripe Payment Request Button will be inserted here. -->
            </div>

            <div id="applegooglepay-card-errors" role="alert"></div>

        <script>

            var siteUrl = '{{site_url('')}}';
            var orderCountry = '{{$paymentMethod->getStoreLocale()}}';
            var orderCurrency = '{{$paymentMethod->getStoreCurrency()}}';
            var orderTotal = {{$order->order_total * 100}};
            var storeName = '{{setting('site_name')}}'

        </script>

    </div>


</div>
