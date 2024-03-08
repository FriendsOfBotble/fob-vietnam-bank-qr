
<style>
.fob .fob-title {
    margin-bottom: 20px;
    margin-top: 40px;
}

.fob .fob-qr-code {
    text-align: center;
    margin-bottom: 20px;
}

.fob .fob-qr-code figure {
    margin-bottom: 15px;
}

.fob .fob-qr-code img {
    width: 200px;
    height: auto;
    margin: 0;
    padding: 0;
}

.fob .fob-qr-code figcaption {
    margin-top: 10px;
    font-size: 14px;
    color: #666;
}
</style>

<div class="fob fob-vietnam-bank-qr fob-container">
<h3 class="fob-title">Quét mã QR để thanh toán</h3>
    <div class="fob-qr-code">
        <figure>
            <img src="{{ $imageUrl }}" alt="QR Code">
            <figcaption>Sử dụng <strong class="text-danger">Ứng dụng ngân hàng</strong> để quét mã.</figcaption>
        </figure>
    </div>

    <div class="fob-qr-information">
        <table class="table table-hover table-striped">
            <tr>
                <td>Tên Ngân Hàng</td>
                <td>
                    <strong>{{ $bank['short_name'] }} - {{ $bank['name'] }}</strong>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>Chủ Tài Khoản</td>
                <td>
                    <strong>{{ $bank['account_name'] }}</strong>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>Số Tài Khoản</td>
                <td>
                    <strong>{{ $bankAccount = $bank['account_number'] }}</strong>
                </td>
                <td class="text-end" style="width: 80px;">
                    <a href="javascript:void(0);" rel="nooper" class="ms-2" type="button" data-clipboard="{{ $bankAccount }}" data-bb-toggle="copy">
                        <x-core::icon name="ti ti-clipboard" />
                    </a>
                </td>
            </tr>
            <tr>
                <td>Nội Dung Chuyển Khoản</td>
                <td>
                    <strong>{{ $bankTransferDescription }}</strong>
                </td>
                <td class="text-end" style="width: 80px;">
                    <a href="javascript:void(0);" rel="nooper" class="ms-2" type="button" data-clipboard="{{ $bankTransferDescription }}" data-bb-toggle="copy">
                        <x-core::icon name="ti ti-clipboard" />
                    </a>
                </td>
            </tr>

            <tr>
                <td>Số Tiền Giao Dịch</td>
                <td>
                    <strong>{{ $formattedOrderAmount = number_format($orderAmount, 0, ',', '.') . ' ₫' }}</strong>
                </td>
                <td class="text-end" style="width: 80px;">
                    <a href="javascript:void(0);" rel="nooper" class="ms-2" type="button" data-clipboard="{{ $orderAmount }}" data-bb-toggle="copy">
                        <x-core::icon name="ti ti-clipboard" />
                    </a>
                </td>
            </tr>
        </table>

        <div class="alert alert-warning">
            <p>Vui lòng giữ nguyên nội dung chuyển khoản <strong class="text-danger">{{ $bankTransferDescription }}</strong> và nhập đúng số tiền <strong class="text-danger">{{ $formattedOrderAmount }}</strong> để được xác nhận thanh toán trực tuyến.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyButtons = document.querySelectorAll('[data-bb-toggle="copy"]');

        copyButtons.forEach((button) => {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const textToCopy = this.getAttribute('data-clipboard');
                fobCopyToClipboard(textToCopy);
            })
        })
    })

    async function fobCopyToClipboard(textToCopy) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(textToCopy);
        } else {
            fobUnsecuredCopyToClipboard(textToCopy);
        }

        MainCheckout.showSuccess('Sao chép thành công!');
    }

    function fobUnsecuredCopyToClipboard(textToCopy) {
        const textArea = document.createElement('textarea');
        textArea.value = textToCopy;
        textArea.style.position = 'absolute';
        textArea.style.left = '-999999px';
        document.body.prepend(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
        } catch (error) {
            console.error('Unable to copy to clipboard', error);
        }

        document.body.removeChild(textArea);
    }
</script>
