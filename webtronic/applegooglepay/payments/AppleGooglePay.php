<?php
namespace Webtronic\AppleGooglePay\Payments;
use Admin\Classes\BasePaymentGateway;
use System\Models;
use Admin\Models\Orders_model;
use Exception;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Traits\EventEmitter;
use Igniter\PayRegister\Traits\PaymentHelpers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Sessions;
use Stripe\StripeClient;
use Igniter\Cart\Classes;
class AppleGooglePay extends BasePaymentGateway
{


    protected $sessionKey = 'ti_applegooglepay';

    public function registerEntryPoints()
    {
        return [
            'stripe_applegooglepay_init' => 'paymentInit',
        ];
    }



    public function getHiddenFields()
    {
        return [

            'stripe_applepay_intent' => ''
        ];
    }

    public function paymentInit(){


        header("Content-type: application/json");

        \Stripe\Stripe::setApiKey($this->getSecretKey());



        $intent = \Stripe\PaymentIntent::create([
            'amount' => \Cart::total()*100,
            'currency' => $this->getStoreCurrency(),
        ]);

        echo json_encode([
            'intent' => $intent->client_secret,
            'total' => \Cart::total(),
            'site' => setting('site_name')
        ]);


    }




    public function isTestMode()
    {
        return $this->model->transaction_mode != 'live';
    }

    public function getPublishableKey()
    {
        return $this->isTestMode() ? $this->model->test_publishable_key : $this->model->live_publishable_key;
    }

    public function getSecretKey()
    {
        return $this->isTestMode() ? $this->model->test_secret_key : $this->model->live_secret_key;
    }


    public function getOrderStatus(){

        return $this->model->order_status ?? 1; //received if empty

    }


    public function getStoreLocale(){

        return $this->model->locale_code ?? 'US';

    }


    public function getStoreCurrency(){

        return strtolower(currency()->getUserCurrency()) ?? 'usd';

    }



    public function isApplicable($total, $host)
    {
        return $host->order_total <= $total;
    }

    /**
     * @param self $host
     * @param \Main\Classes\MainController $controller
     */
    public function beforeRenderPaymentForm($host, $controller)
    {
        $controller->addJs('https://js.stripe.com/v3/', 'stripe-js');

        $controller->addJs('$/webtronic/applegooglepay/assets/js/process.applegooglepay.js', 'process-applegooglepay-js');

    }

    public function completesPaymentOnClient()
    {
        return true;
    }



    /**
     * Processes payment using passed data.
     *
     * @param array $data
     * @param \Admin\Models\Payments_model $host
     * @param \Admin\Models\Orders_model $order
     *
     * @return bool|\Illuminate\Http\RedirectResponse
     * @throws \Igniter\Flame\Exception\ApplicationException
     */
    public function processPaymentForm($data, $host, $order)
    {

        $paymentMethod = $order->payment_method;
        if (!$paymentMethod OR $paymentMethod->code != $host->code)
            throw new ApplicationException('Payment method not found');


        try {

            $fields = $this->getPaymentFormFields($order, $data);

            if(!empty($fields['paymentMethod'])) {
                //intent passed back?
                $order->logPaymentAttempt('Payment successful:  Stripe Payment: '.$fields['paymentMethod'], 1, $fields);
                $order->updateOrderStatus($this->getOrderStatus(), ['notify' => FALSE]);
                $order->markAsPaymentProcessed();

            }else{

                $order->logPaymentAttempt('Payment error -> No Intent supplied from client side', 1, $fields);
                throw new ApplicationException('Sorry something has gone wrong please try again.');

            }
        }
        catch (Exception $ex) {
            //Log::error($ex->getMessage());
            throw new ApplicationException('Payment Failed: Sorry, there was an error processing your payment. '.$ex->getMessage());
        }

    }



    //
    // Payment Profiles
    //

    /**
     * {@inheritdoc}
     */
    public function supportsPaymentProfiles()
    {
        return false;
    }




    protected function getPaymentFormFields($order, $data = [])
    {


        $fields = [
            'amount' => number_format($order->order_total, 2, '.', ''),
            'transactionId' => $order->order_id,
            'paymentMethod' => array_get($data, 'stripe_applepay_intent')
        ];

        return $fields;
    }

}