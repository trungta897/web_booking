<div class="row">
    <div class="col-md-6">
        <h6>Thông tin hoàn tiền</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>ID:</strong></td>
                <td>#{{ $refund->id }}</td>
            </tr>
            <tr>
                <td><strong>Booking:</strong></td>
                <td><a href="{{ route('admin.bookings.show', $refund->booking) }}">#{{ $refund->booking_id }}</a></td>
            </tr>
            <tr>
                <td><strong>Số tiền:</strong></td>
                <td>{{ number_format($refund->amount, 0, ',', '.') }} {{ $refund->currency }}</td>
            </tr>
            <tr>
                <td><strong>Trạng thái:</strong></td>
                <td>
                    @php
                        $statusClasses = [
                            'pending' => 'badge-warning',
                            'processing' => 'badge-info',
                            'completed' => 'badge-success'
                        ];
                        $statusLabels = [
                            'pending' => 'Chờ xử lý',
                            'processing' => 'Đang xử lý',
                            'completed' => 'Hoàn thành'
                        ];
                    @endphp
                    <span class="badge {{ $statusClasses[$refund->status] ?? 'badge-secondary' }}">
                        {{ $statusLabels[$refund->status] ?? $refund->status }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Ngày tạo:</strong></td>
                <td>{{ $refund->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            @if($refund->processed_at)
            <tr>
                <td><strong>Ngày xử lý:</strong></td>
                <td>{{ $refund->processed_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="col-md-6">
        <h6>Thông tin học viên</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Tên:</strong></td>
                <td>{{ $refund->booking->student->name }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td>{{ $refund->booking->student->email }}</td>
            </tr>
            <tr>
                <td><strong>Môn học:</strong></td>
                <td>{{ $refund->booking->subject->name }}</td>
            </tr>
            <tr>
                <td><strong>Gia sư:</strong></td>
                <td>{{ $refund->booking->tutor->user->name }}</td>
            </tr>
        </table>
    </div>
</div>

@if($originalTransaction)
<div class="mt-3">
    <h6>Thông tin giao dịch gốc</h6>
    <div class="alert alert-info">
        <div class="row">
            <div class="col-md-6">
                <strong>VNPay TxnRef:</strong> {{ $refund->booking->vnpay_txn_ref }}<br>
                <strong>VNPay Transaction No:</strong> {{ $originalTransaction->gateway_transaction_id }}
            </div>
            <div class="col-md-6">
                <strong>Bank Code:</strong> {{ $originalTransaction->metadata['vnpay_bank_code'] ?? 'N/A' }}<br>
                <strong>Card Type:</strong> {{ $originalTransaction->metadata['vnpay_card_type'] ?? 'N/A' }}
            </div>
        </div>
    </div>
</div>
@endif

@if($refund->metadata)
<div class="mt-3">
    <h6>Metadata</h6>
    <pre class="bg-light p-2 small">{{ json_encode($refund->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endif
