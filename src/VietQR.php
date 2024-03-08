<?php

namespace FriendsOfBotble\VietnamBankQr;

use Botble\Payment\Enums\PaymentMethodEnum;

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

    public static function getBankInfo(): array|null
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

    public static function getBankByBin(string $bin): array|null
    {
        $banks = static::getBanksList();

        return collect($banks)->first(fn ($bank) => $bank['bin'] === $bin);
    }

    public static function getBanksList(): array
    {
        return  [
            [
               'name' => 'Ngân hàng TMCP An Bình',
               'code' => 'ABB',
               'bin' => '970425',
               'short_name' => 'ABBANK',
            ],
            [
               'name' => 'Ngân hàng TMCP Á Châu',
               'code' => 'ACB',
               'bin' => '970416',
               'short_name' => 'ACB',
            ],
            [
               'name' => 'Ngân hàng TMCP Bắc Á',
               'code' => 'BAB',
               'bin' => '970409',
               'short_name' => 'BacABank',
            ],
            [
               'name' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam',
               'code' => 'BIDV',
               'bin' => '970418',
               'short_name' => 'BIDV',
            ],
            [
               'name' => 'Ngân hàng TMCP Bảo Việt',
               'code' => 'BVB',
               'bin' => '970438',
               'short_name' => 'BaoVietBank',
            ],
            [
               'name' => 'TMCP Việt Nam Thịnh Vượng - Ngân hàng số CAKE by VPBank',
               'code' => 'CAKE',
               'bin' => '546034',
               'short_name' => 'CAKE',
            ],
            [
               'name' => 'Ngân hàng TNHH MTV CIMB Việt Nam',
               'code' => 'CIMB',
               'bin' => '422589',
               'short_name' => 'CIMB',
            ],
            [
               'name' => 'Ngân hàng Hợp tác xã Việt Nam',
               'code' => 'COOPBANK',
               'bin' => '970446',
               'short_name' => 'COOPBANK',
            ],
            [
               'name' => 'Ngân hàng TMCP Xuất Nhập khẩu Việt Nam',
               'code' => 'EIB',
               'bin' => '970431',
               'short_name' => 'Eximbank',
            ],
            [
               'name' => 'Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh',
               'code' => 'HDB',
               'bin' => '970437',
               'short_name' => 'HDBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Công thương Việt Nam',
               'code' => 'ICB',
               'bin' => '970415',
               'short_name' => 'VietinBank',
            ],
            [
               'name' => 'Ngân hàng Đại chúng TNHH Kasikornbank',
               'code' => 'KBank',
               'bin' => '668888',
               'short_name' => 'KBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Kiên Long',
               'code' => 'KLB',
               'bin' => '970452',
               'short_name' => 'KienLongBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Bưu Điện Liên Việt',
               'code' => 'LPB',
               'bin' => '970449',
               'short_name' => 'LienVietPostBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Quân đội',
               'code' => 'MB',
               'bin' => '970422',
               'short_name' => 'MBBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Hàng Hải',
               'code' => 'MSB',
               'bin' => '970426',
               'short_name' => 'MSB',
            ],
            [
               'name' => 'Ngân hàng TMCP Nam Á',
               'code' => 'NAB',
               'bin' => '970428',
               'short_name' => 'NamABank',
            ],
            [
               'name' => 'Ngân hàng TMCP Quốc Dân',
               'code' => 'NCB',
               'bin' => '970419',
               'short_name' => 'NCB',
            ],
            [
               'name' => 'Ngân hàng TMCP Phương Đông',
               'code' => 'OCB',
               'bin' => '970448',
               'short_name' => 'OCB',
            ],
            [
               'name' => 'Ngân hàng Thương mại TNHH MTV Đại Dương',
               'code' => 'Oceanbank',
               'bin' => '970414',
               'short_name' => 'Oceanbank',
            ],
            [
               'name' => 'Ngân hàng TMCP Xăng dầu Petrolimex',
               'code' => 'PGB',
               'bin' => '970430',
               'short_name' => 'PGBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Đại Chúng Việt Nam',
               'code' => 'PVCB',
               'bin' => '970412',
               'short_name' => 'PVcomBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Sài Gòn',
               'code' => 'SCB',
               'bin' => '970429',
               'short_name' => 'SCB',
            ],
            [
               'name' => 'Ngân hàng TMCP Đông Nam Á',
               'code' => 'SEAB',
               'bin' => '970440',
               'short_name' => 'SeABank',
            ],
            [
               'name' => 'Ngân hàng TMCP Sài Gòn Công Thương',
               'code' => 'SGICB',
               'bin' => '970400',
               'short_name' => 'SaigonBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Sài Gòn - Hà Nội',
               'code' => 'SHB',
               'bin' => '970443',
               'short_name' => 'SHB',
            ],
            [
               'name' => 'Ngân hàng TNHH MTV Shinhan Việt Nam',
               'code' => 'SHBVN',
               'bin' => '970424',
               'short_name' => 'ShinhanBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Sài Gòn Thương Tín',
               'code' => 'STB',
               'bin' => '970403',
               'short_name' => 'Sacombank',
            ],
            [
               'name' => 'Ngân hàng TMCP Kỹ thương Việt Nam',
               'code' => 'TCB',
               'bin' => '970407',
               'short_name' => 'Techcombank',
            ],
            [
               'name' => 'Ngân hàng số Timo by Ban Viet Bank (Timo by Ban Viet Bank)',
               'code' => 'TIMO',
               'bin' => '963388',
               'short_name' => 'Timo',
            ],
            [
               'name' => 'Ngân hàng TMCP Tiên Phong',
               'code' => 'TPB',
               'bin' => '970423',
               'short_name' => 'TPBank',
            ],
            [
               'name' => 'TMCP Việt Nam Thịnh Vượng - Ngân hàng số Ubank by VPBank',
               'code' => 'Ubank',
               'bin' => '546035',
               'short_name' => 'Ubank',
            ],
            [
               'name' => 'Ngân hàng TMCP Việt Á',
               'code' => 'VAB',
               'bin' => '970427',
               'short_name' => 'VietABank',
            ],
            [
               'name' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam',
               'code' => 'VBA',
               'bin' => '970405',
               'short_name' => 'Agribank',
            ],
            [
               'name' => 'Ngân hàng TMCP Ngoại Thương Việt Nam',
               'code' => 'VCB',
               'bin' => '970436',
               'short_name' => 'Vietcombank',
            ],
            [
               'name' => 'Ngân hàng TMCP Bản Việt',
               'code' => 'VCCB',
               'bin' => '970454',
               'short_name' => 'VietCapitalBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Quốc tế Việt Nam',
               'code' => 'VIB',
               'bin' => '970441',
               'short_name' => 'VIB',
            ],
            [
               'name' => 'Ngân hàng TMCP Việt Nam Thương Tín',
               'code' => 'VIETBANK',
               'bin' => '970433',
               'short_name' => 'VietBank',
            ],
            [
               'name' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng',
               'code' => 'VPB',
               'bin' => '970432',
               'short_name' => 'VPBank',
            ],
            [
               'name' => 'Ngân hàng TNHH MTV Woori Việt Nam',
               'code' => 'WVN',
               'bin' => '970457',
               'short_name' => 'Woori',
            ],
        ];
    }
}
