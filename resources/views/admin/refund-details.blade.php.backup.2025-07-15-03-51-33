<div class="row g-4">
    <!-- Refund Information Card -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-receipt me-2"></i>
                    Thông tin hoàn tiền
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Mã hoàn tiền:</span>
                            <span class="fw-bold text-primary">#{{ $refund->id }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Booking:</span>
                            <a href="{{ route('admin.bookings.show', $refund->booking) }}"
                               class="fw-bold text-info text-decoration-none">
                                #{{ $refund->booking_id }}
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Số tiền hoàn:</span>
                            <span class="fw-bold text-success fs-5">{{ number_format($refund->amount, 0, ',', '.') }} {{ $refund->currency }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Trạng thái:</span>
                            <div>
                                @php
                                    $statusConfig = [
                                        'pending' => ['class' => 'bg-warning', 'icon' => 'clock', 'text' => 'Chờ xử lý'],
                                        'processing' => ['class' => 'bg-info', 'icon' => 'cog', 'text' => 'Đang xử lý'],
                                        'completed' => ['class' => 'bg-success', 'icon' => 'check', 'text' => 'Hoàn thành'],
                                        'failed' => ['class' => 'bg-danger', 'icon' => 'times', 'text' => 'Thất bại']
                                    ];
                                    $config = $statusConfig[$refund->status] ?? ['class' => 'bg-secondary', 'icon' => 'question', 'text' => $refund->status];
                    @endphp
                                <span class="badge {{ $config['class'] }} d-inline-flex align-items-center">
                                    <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                    {{ $config['text'] }}
                    </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Ngày tạo:</span>
                            <div class="text-end">
                                <div class="fw-semibold">{{ $refund->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $refund->created_at->format('H:i:s') }}</small>
                            </div>
                        </div>
                    </div>
            @if($refund->processed_at)
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Ngày xử lý:</span>
                            <div class="text-end">
                                <div class="fw-semibold">{{ $refund->processed_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $refund->processed_at->format('H:i:s') }}</small>
                            </div>
                        </div>
                    </div>
            @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Student & Booking Information Card -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-user-graduate me-2"></i>
                    Thông tin học viên & Booking
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-soft-primary text-primary me-3">
                                {{ substr($refund->booking->student->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $refund->booking->student->name }}</div>
                                <small class="text-muted">{{ $refund->booking->student->email }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Môn học:</span>
                            <span class="fw-semibold">{{ $refund->booking->subject->name }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Gia sư:</span>
                            <span class="fw-semibold">{{ $refund->booking->tutor->user->name }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Thời gian học:</span>
                            <div class="text-end">
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($refund->booking->start_time)->format('d/m/Y') }}</div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($refund->booking->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($refund->booking->end_time)->format('H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Trạng thái booking:</span>
                            <span class="badge bg-{{ $refund->booking->status === 'completed' ? 'success' : ($refund->booking->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($refund->booking->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($originalTransaction)
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-credit-card me-2"></i>
            Thông tin giao dịch gốc
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="info-item">
                    <div class="info-label">VNPay TxnRef</div>
                    <div class="info-value">
                        <code>{{ $refund->booking->vnpay_txn_ref }}</code>
                        <button class="btn btn-sm btn-outline-secondary ms-2"
                                onclick="copyToClipboard('{{ $refund->booking->vnpay_txn_ref }}')"
                                data-bs-toggle="tooltip" title="Copy">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-item">
                    <div class="info-label">VNPay Transaction No</div>
                    <div class="info-value">
                        <code>{{ $originalTransaction->gateway_transaction_id }}</code>
                        <button class="btn btn-sm btn-outline-secondary ms-2"
                                onclick="copyToClipboard('{{ $originalTransaction->gateway_transaction_id }}')"
                                data-bs-toggle="tooltip" title="Copy">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-item">
                    <div class="info-label">Bank Code</div>
                    <div class="info-value">
                        <span class="badge bg-primary">{{ $originalTransaction->metadata['vnpay_bank_code'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-item">
                    <div class="info-label">Card Type</div>
                    <div class="info-value">
                        <span class="badge bg-info">{{ $originalTransaction->metadata['vnpay_card_type'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-item">
                    <div class="info-label">Số tiền gốc</div>
                    <div class="info-value">
                        <span class="fw-bold text-success">{{ number_format($originalTransaction->amount, 0, ',', '.') }}₫</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-item">
                    <div class="info-label">Ngày thanh toán</div>
                    <div class="info-value">
                        <div class="fw-semibold">{{ $originalTransaction->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $originalTransaction->created_at->format('H:i:s') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($refund->metadata && count($refund->metadata) > 0)
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-code me-2"></i>
            Metadata
        </h6>
    </div>
    <div class="card-body">
        <div class="metadata-container">
            @foreach($refund->metadata as $key => $value)
                <div class="metadata-item">
                    <span class="metadata-key">{{ $key }}:</span>
                    <span class="metadata-value">
                        @if(is_array($value) || is_object($value))
                            <code>{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code>
                        @else
                            <code>{{ $value }}</code>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<style>
.avatar-circle {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.25rem;
}

.bg-soft-primary {
    background-color: rgba(13, 110, 253, 0.1);
}

.info-item {
    margin-bottom: 1rem;
}

.info-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.info-value {
    display: flex;
    align-items: center;
    font-weight: 600;
}

.info-value code {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    color: #495057;
}

.metadata-container {
    max-height: 300px;
    overflow-y: auto;
}

.metadata-item {
    display: flex;
    margin-bottom: 0.75rem;
    align-items: flex-start;
}

.metadata-key {
    font-weight: 600;
    color: #495057;
    min-width: 150px;
    flex-shrink: 0;
}

.metadata-value {
    flex: 1;
    margin-left: 1rem;
}

.metadata-value code {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 0.5rem;
    font-size: 0.8rem;
    color: #495057;
    white-space: pre-wrap;
    display: block;
    max-width: 100%;
    overflow-x: auto;
}

@media (max-width: 768px) {
    .metadata-item {
        flex-direction: column;
    }

    .metadata-value {
        margin-left: 0;
        margin-top: 0.25rem;
    }

    .metadata-key {
        min-width: auto;
    }
}
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success toast
        const toast = document.createElement('div');
        toast.className = 'toast position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '10000';
        toast.innerHTML = `
            <div class="toast-body bg-success text-white rounded">
                <i class="fas fa-check me-2"></i>
                Đã copy vào clipboard!
            </div>
        `;
        document.body.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        setTimeout(() => {
            toast.remove();
        }, 3000);
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
