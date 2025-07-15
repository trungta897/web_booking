@extends('layouts.app')

@section('title', __('common.payout_history'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 text-gray-800 mb-1">📋 {{ __('common.payout_history') }}</h2>
                    <p class="text-muted mb-0">{{ __('common.track_your_payout_requests') }}</p>
                </div>
                <div>
                    <a href="{{ route('tutors.earnings.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
                    </a>
                    @if($earnings['available_earnings'] >= 100000)
                        <a href="{{ route('tutors.earnings.payout.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> {{ __('common.new_payout_request') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('tutors.earnings.history') }}" class="row">
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('common.status') }}</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">{{ __('common.all_statuses') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                    {{ __('common.pending') }}
                                </option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                    {{ __('common.processing') }}
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    {{ __('common.completed') }}
                                </option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>
                                    {{ __('common.failed') }}
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    {{ __('common.cancelled') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="from_date" class="form-label">{{ __('common.from_date') }}</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                   value="{{ request('from_date') }}">
                        </div>

                        <div class="col-md-3">
                            <label for="to_date" class="form-label">{{ __('common.to_date') }}</label>
                            <input type="date" name="to_date" id="to_date" class="form-control"
                                   value="{{ request('to_date') }}">
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> {{ __('common.filter') }}
                                </button>
                                <a href="{{ route('tutors.earnings.history') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> {{ __('common.clear') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('common.pending_payouts') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('common.completed_payouts') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('common.total_requested') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_requested'], 0, ',', '.') }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('common.total_received') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_received'], 0, ',', '.') }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-university fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payouts Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">📊 {{ __('common.payout_requests') }}</h6>
                </div>
                <div class="card-body">
                    @if($payouts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('common.id') }}</th>
                                        <th>{{ __('common.requested_date') }}</th>
                                        <th>{{ __('common.amount') }}</th>
                                        <th>{{ __('common.bank_info') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th>{{ __('common.processed_date') }}</th>
                                        <th>{{ __('common.bookings_count') }}</th>
                                        <th>{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payouts as $payout)
                                        <tr>
                                            <td class="font-weight-bold">#{{ $payout->id }}</td>
                                            <td>{{ $payout->requested_at->format('d/m/Y H:i') }}</td>
                                            <td class="font-weight-bold text-primary">
                                                {{ $payout->formatted_amount }}
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
                                                    <br><small class="text-danger">{{ $payout->failure_reason }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payout->processed_at)
                                                    {{ $payout->processed_at->format('d/m/Y H:i') }}
                                                @else
                                                    <span class="text-muted">{{ __('common.not_processed') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $payout->payout_items_count }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('tutors.earnings.payout.show', $payout) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="{{ __('common.view_details') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if($payout->status === 'pending')
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="cancelPayout({{ $payout->id }})"
                                                                title="{{ __('common.cancel') }}">
                                                            <i class="fas fa-times"></i>
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
                            <h5 class="text-gray-500">{{ __('common.no_payouts_found') }}</h5>
                            @if(request()->hasAny(['status', 'from_date', 'to_date']))
                                <p class="text-muted">{{ __('common.try_different_filters') }}</p>
                                <a href="{{ route('tutors.earnings.history') }}" class="btn btn-outline-primary">
                                    {{ __('common.clear_filters') }}
                                </a>
                            @else
                                <p class="text-muted">{{ __('common.no_payout_requests_yet') }}</p>
                                @if($earnings['available_earnings'] >= 100000)
                                    <a href="{{ route('tutors.earnings.payout.create') }}" class="btn btn-success mt-2">
                                        {{ __('common.create_first_payout') }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')

@endpush

    @push('scripts')
        <script src="{{ asset('js/pages/tutors-earnings-history.js') }}"></script>
    @endpush
@endsection
