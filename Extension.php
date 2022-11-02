<?php

namespace Webtronic\AppleGooglePay;

use Admin\Models\Payments_model;
use Admin\Requests\Location;
use Admin\Widgets\Form;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseExtension;

class Extension extends BaseExtension
{
    public function registerPaymentGateways()
    {
        return [
            \Webtronic\AppleGooglePay\Payments\AppleGooglePay::class => [
                'code' => 'applegooglepay',
                'name' => 'lang:webtronic.applegooglepay::default.text_payment_title',
                'description' => 'lang:webtronic.applegooglepay::default.text_payment_desc',
            ],

        ];
    }



    public function boot()
    {



        Event::listen('admin.form.extendFieldsBefore', function (Form $form) {
            if ($form->model instanceof \Admin\Models\Orders_model) {
                $form->tabs['fields']['payment_logs']['type'] = 'paymentattempts';
                $form->tabs['fields']['payment_logs']['form'] = '$/igniter/payregister/models/config/payment_logs_model';
            }
        });


        Event::listen('main.theme.activated', function () {
            Payments_model::syncAll();
        });

        Event::listen('igniter.checkout.afterSaveOrder', function ($order) {
            if (!$order->payment_method || !$order->payment_method instanceof Payments_model)
                return;

            if (!$order->payment_method->methodExists('updatePaymentIntentSession'))
                return;

            $order->payment_method->updatePaymentIntentSession($order);
        });

        $this->extendLocationOptionsFields();
    }

    protected function extendLocationOptionsFields()
    {
      

        Event::listen('system.formRequest.extendValidator', function ($formRequest, $dataHolder) {
            if (!$formRequest instanceof Location)
                return;

            $dataHolder->attributes = array_merge($dataHolder->attributes, [
                'options.payments.*' => lang('webtronic.applegooglepay::default.label_payments'),
            ]);

            $dataHolder->rules = array_merge($dataHolder->rules, [
                'options.payments.*' => ['string'],
            ]);
        });
    }
}
