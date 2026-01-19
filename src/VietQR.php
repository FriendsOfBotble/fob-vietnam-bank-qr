<?php

namespace FriendsOfBotble\VietnamBankQr;

use Botble\Payment\Enums\PaymentMethodEnum;
use FriendsOfBotble\VietnamBankQr\Services\VietQRService;

class VietQR
{
    public static function isEnabled(): bool
    {
        $paymentMethod = PaymentMethodEnum::BANK_TRANSFER;

        return get_payment_setting('vietnam_bank_bin', $paymentMethod, false)
            && get_payment_setting('vietnam_bank_account_number', $paymentMethod, false);
    }

    public static function getTransferDescription(string $orderCode = '[ma_don_hang]'): string
    {
        $description = get_payment_setting(
            'vietnam_bank_transfer_description',
            PaymentMethodEnum::BANK_TRANSFER,
            static::getDefaultTransferDescription()
        );

        $description = str_replace('[ma_don_hang]', $orderCode, $description);

        return preg_replace('/[^a-zA-Z0-9\s]/si', '', $description);
    }

    public static function getDefaultTransferDescription(): string
    {
        return 'Thanh toan don hang [ma_don_hang]';
    }

    public static function getImageUrl(float $amount, string $orderCode): string
    {
        if (! static::isEnabled()) {
            return '';
        }

        $paymentMethod = PaymentMethodEnum::BANK_TRANSFER;

        $query = http_build_query([
            'amount' => $amount,
            'addInfo' => static::getTransferDescription($orderCode),
            'accountName' => get_payment_setting('vietnam_bank_account_name', $paymentMethod),
        ]);

        return sprintf(
            'https://img.vietqr.io/image/%s-%s-qr_only.png?%s',
            get_payment_setting('vietnam_bank_bin', $paymentMethod),
            get_payment_setting('vietnam_bank_account_number', $paymentMethod),
            $query
        );
    }

    public static function getBankInfo(): ?array
    {
        if (! static::isEnabled()) {
            return null;
        }

        $bank = static::getBankByBin(
            get_payment_setting('vietnam_bank_bin', PaymentMethodEnum::BANK_TRANSFER)
        );

        $paymentMethod = PaymentMethodEnum::BANK_TRANSFER;

        return $bank
            ? [
                ...$bank,
                'account_name' => get_payment_setting('vietnam_bank_account_name', $paymentMethod),
                'account_number' => get_payment_setting('vietnam_bank_account_number', $paymentMethod),
            ]
            : null;
    }

    public static function getBankByBin(string $bin): ?array
    {
        $banks = static::getBanksList();

        return collect($banks)->first(fn ($bank) => $bank['bin'] === $bin);
    }

    public static function getBanksList(): array
    {
        try {
            return VietQRService::getBanks();
        } catch (\RuntimeException) {
            return config('plugins.fob-vietnam-bank-qr.fallback-banks', []);
        }
    }
}
