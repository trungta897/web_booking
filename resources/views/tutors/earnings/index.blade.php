@extends('layouts.app')

@section('title', __('common.earnings_dashboard'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 text-gray-800 mb-1">💰 {{ __('common.earnings_dashboard') }}</h2>
                    <p class="text-muted mb-0">{{ __('common.manage_your_earnings') }}</p>
                </div>
                @if($earnings['available_earnings'] >= 100000)
                    <a href="{{ route('tutors.earnings.payout.create') }}" class="btn btn-success">
                        <i class="fas fa-money-bill-wave"></i> {{ __('common.request_payout') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Earnings Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('common.available_earnings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($earnings['available_earnings'], 0, ',', '.') }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('common.pending_payout') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($earnings['pending_payout'], 0, ',', '.') }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('common.total_paid_out') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($earnings['total_paid_out'], 0, ',', '.') }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-university fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('common.commission_rate') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['average_commission_rate'] }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">⚡ {{ __('common.quick_actions') }}</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('tutors.earnings.details') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-info"></i>
                            <span class="ml-2">{{ __('common.view_earnings_details') }}</span>
                        </a>

                        @if($earnings['available_earnings'] >= 100000)
                            <a href="{{ route('tutors.earnings.payout.create') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-money-bill-wave text-success"></i>
                                <span class="ml-2">{{ __('common.request_payout') }}</span>
                            </a>
                        @else
                            <div class="list-group-item text-muted">
                                <i class="fas fa-lock text-secondary"></i>
                                <span class="ml-2">{{ __('common.payout_minimum_not_met') }}</span>
                                <small class="d-block mt-1">{{ __('common.minimum_payout_amount') }}: 100,000 VND</small>
                            </div>
                        @endif

                        <a href="{{ route('tutors.earnings.history') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-history text-warning"></i>
                            <span class="ml-2">{{ __('common.payout_history') }}</span>
                        </a>

                        <a href="{{ route('tutor.dashboard') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt text-primary"></i>
                            <span class="ml-2">{{ __('common.back_to_dashboard') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payouts -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">🕒 {{ __('common.recent_payouts') }}</h6>
                    @if($recentPayouts->count() > 0)
                        <a href="{{ route('tutors.earnings.history') }}" class="btn btn-sm btn-outline-primary">
                            {{ __('common.view_all') }}
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($recentPayouts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('common.date') }}</th>
                                        <th>{{ __('common.amount') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th>{{ __('common.bank') }}</th>
                                        <th>{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPayouts as $payout)
                                        <tr>
                                            <td>{{ $payout->requested_at->format('d/m/Y') }}</td>
                                            <td class="font-weight-bold">{{ $payout->formatted_amount }}</td>
                                            <td>
                                                <span class="badge badge-{{ $payout->status_color }}">
                                                    {{ $payout->status_display }}
                                                </span>
                                            </td>
                                            <td>{{ $payout->bank_name }}</td>
                                            <td>
                                                <a href="{{ route('tutors.earnings.payout.show', $payout) }}" class="btn btn-sm btn-outline-info">
                                                    {{ __('common.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-money-bill-wave fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">{{ __('common.no_payouts_yet') }}</h5>
                            <p class="text-muted">{{ __('common.payouts_will_appear_here') }}</p>
                            @if($earnings['available_earnings'] >= 100000)
                                <a href="{{ route('tutors.earnings.payout.create') }}" class="btn btn-success mt-2">
                                    {{ __('common.request_first_payout') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Chart -->
    @if($analytics['total_bookings'] > 0)
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">📊 {{ __('common.earnings_analytics') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <canvas id="earningsChart" width="400" height="200"></canvas>
                            </div>
                            <div class="col-lg-4">
                                <h6 class="font-weight-bold">{{ __('common.summary') }}</h6>
                                <ul class="list-unstyled">
                                    <li><strong>{{ __('common.total_bookings') }}:</strong> {{ $analytics['total_bookings'] }}</li>
                                    <li><strong>{{ __('common.total_revenue') }}:</strong> {{ number_format($analytics['total_revenue'], 0, ',', '.') }} VND</li>
                                    <li><strong>{{ __('common.total_earnings') }}:</strong> {{ number_format($analytics['total_earnings'], 0, ',', '.') }} VND</li>
                                    <li><strong>{{ __('common.platform_fees') }}:</strong> {{ number_format($analytics['total_platform_fees'], 0, ',', '.') }} VND</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
@if($analytics['total_bookings'] > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@endif
@endpush

    @push('scripts')
        <script src="{{ asset('js/pages/tutors-earnings-index.js') }}"></script>
    @endpush
@endsection
