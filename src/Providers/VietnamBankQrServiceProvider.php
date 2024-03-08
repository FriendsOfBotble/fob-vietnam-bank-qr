<?php

namespace FriendsOfBotble\VietnamBankQr\Providers;

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Currency;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Forms\BankTransferPaymentMethodForm;
use FriendsOfBotble\VietnamBankQr\VietQR;
use Illuminate\Support\Collection;

class VietnamBankQrServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        $this
            ->setNamespace('plugins/fob-vietnam-bank-qr')
            ->loadAndPublishViews();

        BankTransferPaymentMethodForm::extend(function (BankTransferPaymentMethodForm $form) {
            $paymentMethod = PaymentMethodEnum::BANK_TRANSFER;

            return $form
                ->addAfter(
                    sprintf('payment_%s_description', $paymentMethod),
                    sprintf('payment_%s_vietnam_bank_bin', $paymentMethod),
                    SelectField::class,
                    SelectFieldOption::make()
                        ->label('Ngân hàng')
                        ->choices(
                            collect(VietQR::getBanksList())
                                ->mapWithKeys(fn ($bank) => [$bank['bin'] => "{$bank['short_name']} - {$bank['name']}"])
                                ->toArray()
                        )
                        ->selected(get_payment_setting('vietnam_bank_bin', $paymentMethod))
                        ->toArray()
                )
                ->addAfter(
                    sprintf('payment_%s_vietnam_bank_bin', $paymentMethod),
                    sprintf('payment_%s_vietnam_bank_account_name', $paymentMethod),
                    TextField::class,
                    TextFieldOption::make()
                        ->label('Chủ tài khoản')
                        ->value(get_payment_setting('vietnam_bank_account_name', $paymentMethod))
                        ->toArray()
                )
                ->addAfter(
                    sprintf('payment_%s_vietnam_bank_account_name', $paymentMethod),
                    sprintf('payment_%s_vietnam_bank_account_number', $paymentMethod),
                    TextField::class,
                    TextFieldOption::make()
                        ->label('Số tài khoản')
                        ->value(get_payment_setting('vietnam_bank_account_number', $paymentMethod))
                        ->toArray()
                )
                ->addAfter(
                    sprintf('payment_%s_vietnam_bank_account_number', $paymentMethod),
                    sprintf('payment_%s_vietnam_bank_description_template', $paymentMethod),
                    TextField::class,
                    TextFieldOption::make()
                        ->label('Nội dung chuyển khoản')
                        ->value(get_payment_setting('vietnam_bank_description_template', $paymentMethod, VietQR::getDefaultTransferDescription()))
                        ->helperText('Bạn có thể dùng [ma_don_hang] để hiển thị mã đơn trong nội dung chuyển khoản, vui lòng dùng Tiếng Việt không dấu và không chứa ký tự đặc biệt.')
                        ->toArray()
                );
        });

        add_filter('ecommerce_thank_you_customer_info', function (string|null $html, Collection|Order $orders) {
            if (! VietQR::isEnabled()) {
                return $html;
            }

            if (! $orders instanceof Collection) {
                $collection = new Collection();
                $collection->add($orders);
                $orders = $collection;
            }

            $currentCurrency = $supportedCurrency = get_application_currency();
            $unsupportedCurrency = false;

            if (strtoupper($currentCurrency->title) !== 'VND') {
                $supportedCurrency = Currency::query()->where('title', 'VND')->first();

                if (! $supportedCurrency) {
                    $unsupportedCurrency = true;
                }
            }

            if ($unsupportedCurrency) {
                return $html;
            }

            $orderAmount = 0;
            $orderCode = '';

            foreach ($orders as $item) {
                $orderAmount += $item->amount;
                $orderCode .= $item->code . ', ';
            }

            $orderCode = rtrim(trim($orderCode), ',');

            if ($supportedCurrency->isNot($currentCurrency)) {
                $paymentData['currency'] = strtoupper($item->payment->currency);

                if ($currentCurrency->is_default) {
                    $orderAmount = $orderAmount * $supportedCurrency->exchange_rate;
                } else {
                    $orderAmount = $orderAmount / $currentCurrency->exchange_rate;
                }
            } else {
                $orderAmount = format_price($orderAmount, withoutCurrency: true);
            }

            $html .= view(
                'plugins/fob-vietnam-bank-qr::bank-info',
                [
                    'orderAmount' => $orderAmount,
                    'imageUrl' => VietQR::getImageUrl($orderAmount, $orderCode),
                    'bank' => VietQR::getBankInfo(),
                    'bankTransferDescription' => VietQR::getTransferDescription($orderCode),
                    'currentCurrency' => $supportedCurrency,
                ]
            )->render();

            return $html;
        }, 9999, 2);
    }
}
