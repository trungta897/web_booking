@extends('layouts.admin')

@section('title', __('admin.payout_management'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800">💸 {{ __('admin.payout_management') }}</h1>
                    <p class="text-muted mb-0">{{ __('admin.manage_tutor_payouts') }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.payouts.analytics') }}" class="btn btn-info me-2">
                        <i class="fas fa-chart-bar"></i> {{ __('admin.analytics') }}
                    </a>
                    <a href="{{ route('admin.payouts.export') . '?' . request()->getQueryString() }}" class="btn btn-success">
                        <i class="fas fa-download"></i> {{ __('admin.export') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('admin.pending') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_count'] }}</div>
                            <div class="text-xs text-muted">{{ number_format($stats['pending_amount'], 0, ',', '.') }} VND</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('admin.processing') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['processing_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('admin.completed') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_count'] }}</div>
                            <div class="text-xs text-muted">{{ number_format($stats['completed_amount'], 0, ',', '.') }} VND</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                {{ __('admin.failed') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['failed_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('admin.total_amount') }}
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_amount'], 0, ',', '.') }}</div>
                            <div class="text-xs text-muted">VND</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                {{ __('admin.avg_time') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_processing_time'] }}h</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-stopwatch fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.payouts.index') }}" class="row">
                        <div class="col-md-2">
                            <label for="status" class="form-label small">{{ __('admin.status') }}</label>
                            <select name="status" id="status" class="form-control form-control-sm">
                                <option value="">{{ __('admin.all_statuses') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                    {{ __('admin.pending') }}
                                </option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                    {{ __('admin.processing') }}
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    {{ __('admin.completed') }}
                                </option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>
                                    {{ __('admin.failed') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="from_date" class="form-label small">{{ __('admin.from_date') }}</label>
                            <input type="date" name="from_date" id="from_date" class="form-control form-control-sm"
                                   value="{{ request('from_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="to_date" class="form-label small">{{ __('admin.to_date') }}</label>
                            <input type="date" name="to_date" id="to_date" class="form-control form-control-sm"
                                   value="{{ request('to_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="sort" class="form-label small">{{ __('admin.sort_by') }}</label>
                            <select name="sort" id="sort" class="form-control form-control-sm">
                                <option value="requested_at" {{ request('sort') == 'requested_at' ? 'selected' : '' }}>
                                    {{ __('admin.request_date') }}
                                </option>
                                <option value="amount" {{ request('sort') == 'amount' ? 'selected' : '' }}>
                                    {{ __('admin.amount') }}
                                </option>
                                <option value="tutor_name" {{ request('sort') == 'tutor_name' ? 'selected' : '' }}>
                                    {{ __('admin.tutor_name') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="direction" class="form-label small">{{ __('admin.direction') }}</label>
                            <select name="direction" id="direction" class="form-control form-control-sm">
                                <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>
                                    {{ __('admin.descending') }}
                                </option>
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>
                                    {{ __('admin.ascending') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-search"></i> {{ __('admin.filter') }}
                                </button>
                                <a href="{{ route('admin.payouts.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payouts Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">📋 {{ __('admin.payout_requests') }}</h6>
                </div>
                <div class="card-body">
                    @if($payouts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('admin.id') }}</th>
                                        <th>{{ __('admin.tutor') }}</th>
                                        <th>{{ __('admin.amount') }}</th>
                                        <th>{{ __('admin.bank_info') }}</th>
                                        <th>{{ __('admin.status') }}</th>
                                        <th>{{ __('admin.requested') }}</th>
                                        <th>{{ __('admin.bookings') }}</th>
                                        <th>{{ __('admin.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payouts as $payout)
                                        <tr class="payout-row" data-payout-id="{{ $payout->id }}">
                                            <td class="font-weight-bold">#{{ $payout->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-2">
                                                        <img class="rounded-circle" width="32" height="32"
                                                             src="https://ui-avatars.com/api/?name={{ urlencode($payout->tutor->user->name) }}&background=007bff&color=fff"
                                                             alt="{{ $payout->tutor->user->name }}">
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold small">{{ $payout->tutor->user->name }}</div>
                                                        <div class="text-muted small">{{ $payout->tutor->user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="font-weight-bold text-primary">
                                                {{ number_format($payout->amount, 0, ',', '.') }} VND
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <strong>{{ $payout->bank_name }}</strong><br>
                                                    {{ $payout->account_number }}<br>
                                                    <span class="text-muted">{{ $payout->account_holder_name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $payout->status_color }} badge-pill">
                                                    {{ $payout->status_display }}
                                                </span>
                                                @if($payout->status === 'failed' && $payout->failure_reason)
                                                    <br><small class="text-danger">{{ Str::limit($payout->failure_reason, 30) }}</small>
                                                @endif
                                            </td>
                                            <td class="small">
                                                {{ $payout->requested_at->format('d/m/Y H:i') }}<br>
                                                <span class="text-muted">{{ $payout->requested_at->diffForHumans() }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $payout->payout_items_count }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.payouts.show', $payout) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="{{ __('admin.view_details') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if($payout->status === 'pending')
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-success"
                                                                onclick="approvePayout({{ $payout->id }})"
                                                                title="{{ __('admin.approve') }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="rejectPayout({{ $payout->id }})"
                                                                title="{{ __('admin.reject') }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif

                                                    @if($payout->status === 'processing')
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                onclick="completePayout({{ $payout->id }})"
                                                                title="{{ __('admin.mark_completed') }}">
                                                            <i class="fas fa-flag-checkered"></i>
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payouts->withQueryString()->links() }}
                        </div>

                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-money-bill-wave fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">{{ __('admin.no_payouts_found') }}</h5>
                            @if(request()->hasAny(['status', 'from_date', 'to_date']))
                                <p class="text-muted">{{ __('admin.try_different_filters') }}</p>
                                <a href="{{ route('admin.payouts.index') }}" class="btn btn-outline-primary">
                                    {{ __('admin.clear_filters') }}
                                </a>
                            @else
                                <p class="text-muted">{{ __('admin.no_payout_requests_yet') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if($recentActivity->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">🕒 {{ __('admin.recent_activity') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($recentActivity as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $activity->status === 'completed' ? 'success' : ($activity->status === 'failed' ? 'danger' : 'warning') }}"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">
                                            {{ __('admin.payout') }} #{{ $activity->id }} - {{ $activity->status_display }}
                                        </h6>
                                        <p class="timeline-description">
                                            {{ $activity->tutor->user->name }} - {{ number_format($activity->amount, 0, ',', '.') }} VND
                                            <br><small class="text-muted">{{ $activity->updated_at->diffForHumans() }}</small>
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 50px;
}

.timeline-marker {
    position: absolute;
    left: 8px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #e3e6f0;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 1rem;
    font-weight: 600;
}

.timeline-description {
    margin-bottom: 0;
    color: #6c757d;
}

.payout-row:hover {
    background-color: #f8f9fc;
}
</style>
@endpush

@push('scripts')
<script>
function approvePayout(payoutId) {
    const notes = prompt('{{ __("admin.admin_notes_optional") }}:');
    if (notes !== null) {
        performPayoutAction(payoutId, 'approve', { admin_notes: notes });
    }
}

function rejectPayout(payoutId) {
    const reason = prompt('{{ __("admin.rejection_reason_required") }}:');
    if (reason && reason.trim()) {
        const notes = prompt('{{ __("admin.admin_notes_optional") }}:');
        performPayoutAction(payoutId, 'reject', {
            rejection_reason: reason.trim(),
            admin_notes: notes || ''
        });
    } else if (reason !== null) {
        alert('{{ __("admin.rejection_reason_required") }}');
    }
}

function completePayout(payoutId) {
    const transactionId = prompt('{{ __("admin.transaction_id_optional") }}:');
    if (transactionId !== null) {
        const notes = prompt('{{ __("admin.completion_notes_optional") }}:');
        performPayoutAction(payoutId, 'complete', {
            transaction_id: transactionId || '',
            completion_notes: notes || ''
        });
    }
}

function performPayoutAction(payoutId, action, data) {
    fetch(`/admin/payouts/${payoutId}/${action}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || '{{ __("admin.error_occurred") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("admin.network_error") }}');
    });
}
</script>
@endpush
@endsection
