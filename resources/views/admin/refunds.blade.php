@extends('layouts.admin')

@section('title', 'Quản lý hoàn tiền VNPay')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý hoàn tiền VNPay</h1>
        <div>
            <button type="button" class="btn btn-info btn-sm" onclick="refreshPage()">
                <i class="fas fa-sync-alt"></i> Làm mới
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#refundGuideModal">
                <i class="fas fa-question-circle"></i> Hướng dẫn
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ xử lý
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đang xử lý
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['processing'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Hoàn thành
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng số tiền (tháng này)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_amount'], 0, ',', '.') }} ₫</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.refunds') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Refunds Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách yêu cầu hoàn tiền</h6>
        </div>
        <div class="card-body">
            @if($refunds->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="refundsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Booking</th>
                                <th>Học viên</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Ngày xử lý</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($refunds as $refund)
                            <tr>
                                <td>#{{ $refund->id }}</td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $refund->booking) }}" class="text-decoration-none">
                                        #{{ $refund->booking_id }}
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $refund->booking->student->name }}</strong><br>
                                        <small class="text-muted">{{ $refund->booking->student->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ number_format($refund->amount, 0, ',', '.') }} ₫</strong>
                                </td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'pending' => 'badge-warning',
                                            'processing' => 'badge-info',
                                            'completed' => 'badge-success',
                                            'failed' => 'badge-danger'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'completed' => 'Hoàn thành',
                                            'failed' => 'Thất bại'
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusClasses[$refund->status] ?? 'badge-secondary' }}">
                                        {{ $statusLabels[$refund->status] ?? $refund->status }}
                                    </span>
                                </td>
                                <td>{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    {{ $refund->processed_at ? $refund->processed_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="showRefundDetails({{ $refund->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($refund->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                    onclick="startProcessing({{ $refund->booking_id }})">
                                                <i class="fas fa-play"></i> Xử lý
                                            </button>
                                        @endif

                                        @if($refund->status === 'processing')
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="completeRefund({{ $refund->booking_id }})">
                                                <i class="fas fa-check"></i> Hoàn thành
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $refunds->appends(request()->query())->links() }}
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">Không có yêu cầu hoàn tiền nào.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Refund Guide Modal -->
<div class="modal fade" id="refundGuideModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hướng dẫn hoàn tiền VNPay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Lưu ý:</strong> VNPay không hỗ trợ API hoàn tiền tự động. Tất cả hoàn tiền phải được xử lý thủ công qua portal.
                </div>

                <h6>Quy trình hoàn tiền:</h6>
                <ol>
                    <li><strong>Bước 1:</strong> Click "Xử lý" cho yêu cầu hoàn tiền</li>
                    <li><strong>Bước 2:</strong> Đăng nhập VNPay Merchant Portal</li>
                    <li><strong>Bước 3:</strong> Vào mục "Quản lý giao dịch" → "Hoàn tiền"</li>
                    <li><strong>Bước 4:</strong> Tìm giao dịch theo mã VNPay Transaction</li>
                    <li><strong>Bước 5:</strong> Thực hiện hoàn tiền trên portal</li>
                    <li><strong>Bước 6:</strong> Copy mã giao dịch hoàn tiền từ VNPay</li>
                    <li><strong>Bước 7:</strong> Click "Hoàn thành" và nhập mã giao dịch</li>
                </ol>

                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Quan trọng:</strong> Phải hoàn tất bước 7 để cập nhật trạng thái và gửi thông báo cho học viên.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Refund Details Modal -->
<div class="modal fade" id="refundDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết hoàn tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="refundDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Refund Modal -->
<div class="modal fade" id="completeRefundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hoàn thành hoàn tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeRefundForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Vui lòng nhập mã giao dịch hoàn tiền từ VNPay portal.
                    </div>

                    <input type="hidden" id="completeBookingId" name="booking_id">

                    <div class="mb-3">
                        <label for="vnpayRefundTxn" class="form-label">Mã giao dịch VNPay <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vnpayRefundTxn" name="vnpay_refund_txn" required>
                        <div class="form-text">Nhập mã giao dịch hoàn tiền từ VNPay portal</div>
                    </div>

                    <div class="mb-3">
                        <label for="adminNotes" class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Hoàn thành
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function refreshPage() {
    window.location.reload();
}

function showRefundDetails(refundId) {
    // Load refund details via AJAX
    fetch(`/admin/refunds/${refundId}/details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('refundDetailsContent').innerHTML = data.html;
            new bootstrap.Modal(document.getElementById('refundDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Không thể tải chi tiết hoàn tiền.');
        });
}

function startProcessing(bookingId) {
    if (confirm('Bắt đầu xử lý hoàn tiền cho booking #' + bookingId + '?')) {
        fetch(`/admin/refunds/${bookingId}/start-processing`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã bắt đầu xử lý. Vui lòng kiểm tra console hoặc log để xem hướng dẫn chi tiết.');
                window.location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xử lý yêu cầu.');
        });
    }
}

function completeRefund(bookingId) {
    document.getElementById('completeBookingId').value = bookingId;
    new bootstrap.Modal(document.getElementById('completeRefundModal')).show();
}

document.getElementById('completeRefundForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const bookingId = formData.get('booking_id');

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
            alert('Hoàn tiền đã được hoàn thành thành công!');
            bootstrap.Modal.getInstance(document.getElementById('completeRefundModal')).hide();
            window.location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi hoàn thành hoàn tiền.');
    });
});

// Initialize DataTable if available
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#refundsTable').DataTable({
            "pageLength": 25,
            "order": [[5, "desc"]], // Sort by created date
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json"
            }
        });
    }
});
</script>
@endsection
