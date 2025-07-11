@extends('layouts.app')

@section('title', __('common.earnings_details'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 text-gray-800 mb-1">📋 {{ __('common.earnings_details') }}</h2>
                    <p class="text-muted mb-0">{{ __('common.detailed_earnings_breakdown') }}</p>
                </div>
                <div>
                    <a href="{{ route('tutors.earnings.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
                    </a>
                    @if($earnings['available_earnings'] >= 100000)
                        <a href="{{ route('tutors.earnings.payout.create') }}" class="btn btn-success">
                            <i class="fas fa-money-bill-wave"></i> {{ __('common.request_payout') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
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

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('common.eligible_bookings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $earnings['eligible_bookings_count'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
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

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('common.total_platform_fees') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($earnings['total_platform_fees'], 0, ',', '.') }} VND
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

    <!-- Bookings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">💰 {{ __('common.earnings_breakdown') }}</h6>
                </div>
                <div class="card-body">
                    @if($eligibleBookings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('common.booking_id') }}</th>
                                        <th>{{ __('common.date') }}</th>
                                        <th>{{ __('common.student') }}</th>
                                        <th>{{ __('common.subject') }}</th>
                                        <th>{{ __('common.duration') }}</th>
                                        <th>{{ __('common.total_amount') }}</th>
                                        <th>{{ __('common.platform_fee') }}</th>
                                        <th>{{ __('common.your_earnings') }}</th>
                                        <th>{{ __('common.commission_rate') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($eligibleBookings as $booking)
                                        <tr>
                                            <td>
                                                <a href="{{ route('bookings.show', $booking) }}" class="font-weight-bold text-primary">
                                                    #{{ $booking->id }}
                                                </a>
                                            </td>
                                            <td>{{ $booking->start_time->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-2">
                                                        <img class="rounded-circle" width="30" height="30"
                                                             src="https://ui-avatars.com/api/?name={{ urlencode($booking->student->name) }}&background=007bff&color=fff"
                                                             alt="{{ $booking->student->name }}">
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold">{{ $booking->student->name }}</div>
                                                        <div class="text-muted small">{{ $booking->student->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $booking->subject->name }}</span>
                                            </td>
                                            <td>{{ $booking->duration }} {{ __('common.minutes') }}</td>
                                            <td class="font-weight-bold">{{ number_format($booking->price, 0, ',', '.') }} VND</td>
                                            <td class="text-warning">{{ $booking->formatted_platform_fee }}</td>
                                            <td class="text-success font-weight-bold">{{ $booking->formatted_tutor_earnings }}</td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    {{ $booking->platform_fee_percentage }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="5" class="text-right">{{ __('common.totals') }}</th>
                                        <th class="font-weight-bold">
                                            {{ number_format($eligibleBookings->sum('price'), 0, ',', '.') }} VND
                                        </th>
                                        <th class="text-warning font-weight-bold">
                                            {{ number_format($eligibleBookings->sum('platform_fee_amount'), 0, ',', '.') }} VND
                                        </th>
                                        <th class="text-success font-weight-bold">
                                            {{ number_format($eligibleBookings->sum('tutor_earnings'), 0, ',', '.') }} VND
                                        </th>
                                        <th>
                                            @php
                                                $totalRevenue = $eligibleBookings->sum('price');
                                                $totalFees = $eligibleBookings->sum('platform_fee_amount');
                                                $avgRate = $totalRevenue > 0 ? round(($totalFees / $totalRevenue) * 100, 1) : 0;
                                            @endphp
                                            <span class="badge badge-secondary">{{ $avgRate }}%</span>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $eligibleBookings->links() }}
                        </div>

                        @if($earnings['available_earnings'] >= 100000)
                            <div class="mt-4 p-3 bg-light rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">{{ __('common.ready_for_payout') }}</h6>
                                        <p class="text-muted mb-0">
                                            {{ __('common.total_available_for_payout') }}:
                                            <strong class="text-success">{{ number_format($earnings['available_earnings'], 0, ',', '.') }} VND</strong>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <a href="{{ route('tutors.earnings.payout.create') }}" class="btn btn-success">
                                            <i class="fas fa-money-bill-wave"></i> {{ __('common.request_payout') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 p-3 bg-warning text-dark rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <div>
                                        <strong>{{ __('common.minimum_payout_not_reached') }}</strong>
                                        <p class="mb-0">
                                            {{ __('common.need_minimum_amount') }}: 100,000 VND.
                                            {{ __('common.current_available') }}: {{ number_format($earnings['available_earnings'], 0, ',', '.') }} VND
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">{{ __('common.no_earnings_available') }}</h5>
                            <p class="text-muted">{{ __('common.complete_bookings_to_earn') }}</p>
                            <a href="{{ route('tutor.dashboard') }}" class="btn btn-primary mt-2">
                                {{ __('common.back_to_dashboard') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
