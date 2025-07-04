@extends('layouts.admin')

@section('title', 'Quản lý hoàn tiền VNPay')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="fas fa-undo-alt text-primary me-2"></i>
                Quản lý hoàn tiền VNPay
            </h1>
            <p class="text-muted mb-0">Quản lý và xử lý các yêu cầu hoàn tiền từ VNPay</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshPage()">
                <i class="fas fa-sync-alt"></i> Làm mới
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#refundGuideModal">
                <i class="fas fa-question-circle"></i> Hướng dẫn
            </button>
        </div>
    </div>

    <!-- Enhanced Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ xử lý
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                            <div class="text-xs text-muted">Yêu cầu mới</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đang xử lý
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['processing'] }}</div>
                            <div class="text-xs text-muted">Đang thực hiện</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info">
                                <i class="fas fa-cog text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Hoàn thành
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['completed'] }}</div>
                            <div class="text-xs text-muted">Tháng này</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success">
                                <i class="fas fa-check text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng tiền hoàn
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_amount'], 0, ',', '.') }}₫</div>
                            <div class="text-xs text-muted">Tháng này</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-filter text-primary me-2"></i>
                <h6 class="m-0 font-weight-bold text-primary">Bộ lọc tìm kiếm</h6>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.refunds') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label for="status" class="form-label fw-semibold">Trạng thái</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                <i class="fas fa-clock"></i> Chờ xử lý
                            </option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                <i class="fas fa-cog"></i> Đang xử lý
                            </option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                <i class="fas fa-check"></i> Hoàn thành
                            </option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="date_from" class="form-label fw-semibold">Từ ngày</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="date_to" class="form-label fw-semibold">Đến ngày</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                            <a href="{{ route('admin.refunds') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Refunds Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list text-primary me-2"></i>
                    <h6 class="m-0 font-weight-bold text-primary">
                        Danh sách yêu cầu hoàn tiền
                        <span class="badge bg-secondary ms-2">{{ $refunds->total() }}</span>
                    </h6>
                </div>
                @if($refunds->count() > 0)
                    <small class="text-muted">
                        Hiển thị {{ $refunds->firstItem() }}-{{ $refunds->lastItem() }} trong {{ $refunds->total() }} kết quả
                    </small>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($refunds->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="refundsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Mã</th>
                                <th class="fw-semibold">Booking</th>
                                <th class="fw-semibold">Học viên</th>
                                <th class="fw-semibold">Số tiền</th>
                                <th class="fw-semibold">Trạng thái</th>
                                <th class="fw-semibold">Ngày tạo</th>
                                <th class="fw-semibold">Ngày xử lý</th>
                                <th class="fw-semibold text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($refunds as $refund)
                            <tr>
                                <td class="fw-semibold text-primary">#{{ $refund->id }}</td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $refund->booking) }}"
                                       class="text-decoration-none text-info fw-semibold">
                                        #{{ $refund->booking_id }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                {{ substr($refund->booking->student->name, 0, 1) }}
                                            </div>
                                        </div>
                                    <div>
                                            <div class="fw-semibold">{{ $refund->booking->student->name }}</div>
                                        <small class="text-muted">{{ $refund->booking->student->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($refund->amount, 0, ',', '.') }}₫</span>
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $refund->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $refund->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($refund->processed_at)
                                        <div class="fw-semibold">{{ $refund->processed_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $refund->processed_at->format('H:i') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="showRefundDetails({{ $refund->id }})"
                                                data-bs-toggle="tooltip" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($refund->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                    onclick="startProcessing({{ $refund->booking_id }})"
                                                    data-bs-toggle="tooltip" title="Bắt đầu xử lý">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        @endif

                                        @if($refund->status === 'processing')
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="completeRefund({{ $refund->booking_id }})"
                                                    data-bs-toggle="tooltip" title="Hoàn thành">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="text-muted">
                        Hiển thị {{ $refunds->firstItem() }}-{{ $refunds->lastItem() }} trong {{ $refunds->total() }} kết quả
                    </div>
                {{ $refunds->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-inbox fa-3x text-muted opacity-50"></i>
                    </div>
                    <h5 class="text-muted">Không có yêu cầu hoàn tiền nào</h5>
                    <p class="text-muted mb-0">Chưa có yêu cầu hoàn tiền nào được tạo trong hệ thống.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Enhanced Refund Guide Modal -->
<div class="modal fade" id="refundGuideModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-book-open me-2"></i>
                    Hướng dẫn hoàn tiền VNPay
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0 bg-light-info">
                    <div class="d-flex">
                        <i class="fas fa-info-circle text-info me-3 mt-1"></i>
                        <div>
                            <strong>Lưu ý quan trọng:</strong> VNPay không hỗ trợ API hoàn tiền tự động.
                            Tất cả hoàn tiền phải được xử lý thủ công qua VNPay Merchant Portal.
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">
                    <i class="fas fa-list-ol text-primary me-2"></i>
                    Quy trình hoàn tiền (7 bước):
                </h6>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="fw-semibold">Bước 1: Bắt đầu xử lý</h6>
                            <p class="text-muted mb-0">Click nút "Xử lý" cho yêu cầu hoàn tiền cần xử lý</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="fw-semibold">Bước 2: Truy cập VNPay Portal</h6>
                            <p class="text-muted mb-0">Đăng nhập vào VNPay Merchant Portal</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="fw-semibold">Bước 3: Tìm menu hoàn tiền</h6>
                            <p class="text-muted mb-0">Vào mục "Quản lý giao dịch" → "Hoàn tiền"</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="fw-semibold">Bước 4: Tìm giao dịch</h6>
                            <p class="text-muted mb-0">Tìm giao dịch theo mã VNPay Transaction</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="fw-semibold">Bước 5: Thực hiện hoàn tiền</h6>
                            <p class="text-muted mb-0">Thực hiện hoàn tiền trên VNPay portal</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="fw-semibold">Bước 6: Lấy mã giao dịch</h6>
                            <p class="text-muted mb-0">Copy mã giao dịch hoàn tiền từ VNPay</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="fw-semibold">Bước 7: Hoàn thành</h6>
                            <p class="text-muted mb-0">Click "Hoàn thành" và nhập mã giao dịch</p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning border-0 bg-light-warning mt-4">
                    <div class="d-flex">
                        <i class="fas fa-exclamation-triangle text-warning me-3 mt-1"></i>
                        <div>
                            <strong>Quan trọng:</strong> Phải hoàn tất bước 7 để cập nhật trạng thái
                            và gửi thông báo cho học viên.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Refund Details Modal -->
<div class="modal fade" id="refundDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-receipt me-2"></i>
                    Chi tiết hoàn tiền
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="refundDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải thông tin...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Complete Refund Modal -->
<div class="modal fade" id="completeRefundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-check-circle me-2"></i>
                    Hoàn thành hoàn tiền
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeRefundForm">
                <div class="modal-body">
                    <div class="alert alert-info border-0 bg-light-info">
                        <div class="d-flex">
                            <i class="fas fa-info-circle text-info me-3 mt-1"></i>
                            <div>
                                Vui lòng nhập mã giao dịch hoàn tiền từ VNPay portal để hoàn tất quá trình.
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="completeBookingId" name="booking_id">

                    <div class="mb-3">
                        <label for="vnpayRefundTxn" class="form-label fw-semibold">
                            Mã giao dịch VNPay <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="vnpayRefundTxn" name="vnpay_refund_txn"
                               required placeholder="Ví dụ: 14073973">
                        <div class="form-text">Nhập mã giao dịch hoàn tiền từ VNPay portal</div>
                    </div>

                    <div class="mb-3">
                        <label for="adminNotes" class="form-label fw-semibold">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3"
                                  placeholder="Thêm ghi chú về quá trình hoàn tiền..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-success" id="completeRefundBtn">
                        <i class="fas fa-check me-1"></i> Hoàn thành
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none"
     style="background: rgba(0,0,0,0.5); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="bg-white rounded-3 p-4 text-center">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="visually-hidden">Đang xử lý...</span>
            </div>
            <div class="fw-semibold">Đang xử lý...</div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.icon-circle {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
}

.bg-soft-primary {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-light-info {
    background-color: rgba(13, 202, 240, 0.1);
}

.bg-light-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -2.25rem;
    top: 0.25rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 3px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.04);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .timeline {
        padding-left: 1.5rem;
    }

    .timeline-marker {
        left: -1.75rem;
        width: 1rem;
        height: 1rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('d-none');
}

function refreshPage() {
    showLoading();
    window.location.reload();
}

function showRefundDetails(refundId) {
    const modal = new bootstrap.Modal(document.getElementById('refundDetailsModal'));
    modal.show();

    // Reset content
    document.getElementById('refundDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-2 text-muted">Đang tải thông tin...</p>
        </div>
    `;

    // Load refund details via AJAX
    fetch(`/admin/refunds/${refundId}/details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('refundDetailsContent').innerHTML = data.html;
        })
        .catch(error => {
            document.getElementById('refundDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Không thể tải chi tiết hoàn tiền. Vui lòng thử lại.
                </div>
            `;
        });
}

function startProcessing(bookingId) {
    // Enhanced confirmation dialog
    if (confirm('🚀 Bắt đầu xử lý hoàn tiền cho booking #' + bookingId + '?\n\nSau khi xác nhận, bạn sẽ cần thực hiện hoàn tiền trên VNPay portal.')) {
        showLoading();

        fetch(`/admin/refunds/${bookingId}/start-processing`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                // Success notification
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
                alert.style.cssText = 'top: 20px; right: 20px; z-index: 10000; max-width: 400px;';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Thành công!</strong> Đã bắt đầu xử lý hoàn tiền.
                    Vui lòng kiểm tra log để xem hướng dẫn chi tiết.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alert);

                setTimeout(() => {
                window.location.reload();
                }, 2000);
            } else {
                alert('❌ Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            alert('❌ Có lỗi xảy ra khi xử lý yêu cầu. Vui lòng thử lại.');
        });
    }
}

function completeRefund(bookingId) {
    document.getElementById('completeBookingId').value = bookingId;
    document.getElementById('vnpayRefundTxn').value = '';
    document.getElementById('adminNotes').value = '';
    new bootstrap.Modal(document.getElementById('completeRefundModal')).show();
}

document.getElementById('completeRefundForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const bookingId = formData.get('booking_id');
    const submitBtn = document.getElementById('completeRefundBtn');

    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';

    fetch(`/admin/refunds/${bookingId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            vnpay_refund_txn: formData.get('vnpay_refund_txn'),
            admin_notes: formData.get('admin_notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success notification
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 10000; max-width: 400px;';
            alert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                <strong>Thành công!</strong> Hoàn tiền đã được hoàn thành!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);

            bootstrap.Modal.getInstance(document.getElementById('completeRefundModal')).hide();
            setTimeout(() => {
            window.location.reload();
            }, 1500);
        } else {
            alert('❌ Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Có lỗi xảy ra khi hoàn thành hoàn tiền. Vui lòng thử lại.');
    })
    .finally(() => {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Hoàn thành';
    });
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Auto-submit filter form on status change
document.getElementById('status').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>
@endsection
