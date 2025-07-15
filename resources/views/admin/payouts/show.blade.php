@extends('layouts.admin')

@section('title', __('admin.payout_details'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800">🔍 {{ __('admin.payout_details') }} #{{ $payout->id }}</h1>
                    <p class="text-muted mb-0">{{ __('admin.detailed_payout_review') }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.payouts.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_list') }}
                    </a>
                    @if($payout->status === 'pending')
                        <button type="button" class="btn btn-success me-2" onclick="approvePayout({{ $payout->id }})">
                            <i class="fas fa-check"></i> {{ __('admin.approve') }}
                        </button>
                        <button type="button" class="btn btn-danger" onclick="rejectPayout({{ $payout->id }})">
                            <i class="fas fa-times"></i> {{ __('admin.reject') }}
                        </button>
                    @elseif($payout->status === 'processing')
                        <button type="button" class="btn btn-primary" onclick="completePayout({{ $payout->id }})">
                            <i class="fas fa-flag-checkered"></i> {{ __('admin.mark_completed') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Payout Information -->
        <div class="col-lg-4 mb-4">
            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">📋 {{ __('admin.payout_status') }}</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge badge-{{ $payout->status_color }} badge-pill p-3" style="font-size: 1.2em;">
                            {{ $payout->status_display }}
                        </span>
                    </div>

                    @if($payout->status === 'failed' && $payout->failure_reason)
                        <div class="alert alert-danger">
                            <strong>{{ __('admin.failure_reason') }}:</strong><br>
                            {{ $payout->failure_reason }}
                        </div>
                    @endif

                    @if($payout->admin_notes)
                        <div class="alert alert-info">
                            <strong>{{ __('admin.admin_notes') }}:</strong><br>
                            {{ $payout->admin_notes }}
                        </div>
                    @endif

                    @if($payout->transaction_id)
                        <div class="alert alert-success">
                            <strong>{{ __('admin.transaction_id') }}:</strong><br>
                            <code>{{ $payout->transaction_id }}</code>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tutor Information -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">👨‍🏫 {{ __('admin.tutor_information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img class="rounded-circle me-3" width="60" height="60"
                             src="https://ui-avatars.com/api/?name={{ urlencode($payout->tutor->user->name) }}&background=007bff&color=fff"
                             alt="{{ $payout->tutor->user->name }}">
                        <div>
                            <h6 class="mb-1">{{ $payout->tutor->user->name }}</h6>
                            <p class="text-muted mb-0">{{ $payout->tutor->user->email }}</p>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h6 text-muted mb-1">{{ __('admin.total_bookings') }}</div>
                                <div class="h5">{{ $payout->tutor->bookings()->count() }}</div>
                            </div>
                            <div class="col-6">
                                <div class="h6 text-muted mb-1">{{ __('admin.avg_rating') }}</div>
                                <div class="h5">
                                    @if($payout->tutor->average_rating)
                                        {{ number_format($payout->tutor->average_rating, 1) }} ⭐
                                    @else
                                        {{ __('admin.no_ratings') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('admin.tutors.show', $payout->tutor->user) }}" class="btn btn-outline-primary btn-sm w-100">
                            {{ __('admin.view_tutor_profile') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Amount & Bank Information -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">💰 {{ __('admin.payment_details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="h2 text-success font-weight-bold">
                            {{ number_format($payout->amount, 0, ',', '.') }} VND
                        </div>
                        <small class="text-muted">{{ __('admin.payout_amount') }}</small>
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="font-weight-bold mb-2">🏦 {{ __('admin.bank_information') }}</h6>
                        <div class="mb-2">
                            <strong>{{ __('admin.bank_name') }}:</strong><br>
                            {{ $payout->bank_name }}
                        </div>
                        <div class="mb-2">
                            <strong>{{ __('admin.account_number') }}:</strong><br>
                            <code>{{ $payout->account_number }}</code>
                        </div>
                        <div class="mb-0">
                            <strong>{{ __('admin.account_holder') }}:</strong><br>
                            {{ $payout->account_holder_name }}
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('admin.bookings_count') }}:</span>
                            <strong>{{ $payout->payoutItems->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('admin.requested_date') }}:</span>
                            <strong>{{ $payout->requested_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        @if($payout->processed_at)
                            <div class="d-flex justify-content-between">
                                <span>{{ __('admin.processed_date') }}:</span>
                                <strong>{{ $payout->processed_at->format('d/m/Y H:i') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Included Bookings -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">📚 {{ __('admin.included_bookings') }}</h6>
                </div>
                <div class="card-body">
                    @if($payout->payoutItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('admin.booking_id') }}</th>
                                        <th>{{ __('admin.date') }}</th>
                                        <th>{{ __('admin.student') }}</th>
                                        <th>{{ __('admin.subject') }}</th>
                                        <th>{{ __('admin.duration') }}</th>
                                        <th>{{ __('admin.total_amount') }}</th>
                                        <th>{{ __('admin.platform_fee') }}</th>
                                        <th>{{ __('admin.tutor_earnings') }}</th>
                                        <th>{{ __('admin.commission_rate') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payout->payoutItems as $item)
                                        @php $booking = $item->booking; @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.bookings.show', $booking) }}" class="font-weight-bold text-primary">
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
                                                        <div class="font-weight-bold small">{{ $booking->student->name }}</div>
                                                        <div class="text-muted small">{{ $booking->student->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $booking->subject->name }}</span>
                                            </td>
                                            <td>{{ $booking->duration }} {{ __('admin.minutes') }}</td>
                                            <td class="font-weight-bold">{{ number_format($booking->price, 0, ',', '.') }} VND</td>
                                            <td class="text-warning">{{ number_format($booking->platform_fee_amount, 0, ',', '.') }} VND</td>
                                            <td class="text-success font-weight-bold">{{ number_format($item->amount, 0, ',', '.') }} VND</td>
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
                                        <th colspan="5" class="text-right">{{ __('admin.totals') }}</th>
                                        <th class="font-weight-bold">
                                            {{ number_format($payout->payoutItems->sum(function($item) { return $item->booking->price; }), 0, ',', '.') }} VND
                                        </th>
                                        <th class="text-warning font-weight-bold">
                                            {{ number_format($payout->payoutItems->sum(function($item) { return $item->booking->platform_fee_amount; }), 0, ',', '.') }} VND
                                        </th>
                                        <th class="text-success font-weight-bold">
                                            {{ number_format($payout->amount, 0, ',', '.') }} VND
                                        </th>
                                        <th>
                                            @php
                                                $totalRevenue = $payout->payoutItems->sum(function($item) { return $item->booking->price; });
                                                $totalFees = $payout->payoutItems->sum(function($item) { return $item->booking->platform_fee_amount; });
                                                $avgRate = $totalRevenue > 0 ? round(($totalFees / $totalRevenue) * 100, 1) : 0;
                                            @endphp
                                            <span class="badge badge-secondary">{{ $avgRate }}%</span>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Admin Action Timeline -->
                        <div class="mt-4">
                            <h6 class="font-weight-bold mb-3">🕒 {{ __('admin.payout_timeline') }}</h6>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ __('admin.payout_requested') }}</h6>
                                        <p class="timeline-description">
                                            {{ $payout->requested_at->format('d/m/Y H:i') }}
                                            <small class="text-muted">({{ $payout->requested_at->diffForHumans() }})</small>
                                        </p>
                                    </div>
                                </div>

                                @if($payout->status === 'processing')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ __('admin.approved_for_processing') }}</h6>
                                            <p class="timeline-description">
                                                @if($payout->processedBy)
                                                    {{ __('admin.approved_by') }}: {{ $payout->processedBy->name }}
                                                @endif
                                                @if($payout->processed_at)
                                                    <br>{{ $payout->processed_at->format('d/m/Y H:i') }}
                                                    <small class="text-muted">({{ $payout->processed_at->diffForHumans() }})</small>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($payout->status === 'completed')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ __('admin.payout_completed') }}</h6>
                                            <p class="timeline-description">
                                                @if($payout->processedBy)
                                                    {{ __('admin.completed_by') }}: {{ $payout->processedBy->name }}
                                                @endif
                                                @if($payout->processed_at)
                                                    <br>{{ $payout->processed_at->format('d/m/Y H:i') }}
                                                    <small class="text-muted">({{ $payout->processed_at->diffForHumans() }})</small>
                                                @endif
                                                @if($payout->transaction_id)
                                                    <br><strong>{{ __('admin.transaction_id') }}:</strong> {{ $payout->transaction_id }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($payout->status === 'failed')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ __('admin.payout_rejected') }}</h6>
                                            <p class="timeline-description">
                                                @if($payout->processedBy)
                                                    {{ __('admin.rejected_by') }}: {{ $payout->processedBy->name }}
                                                @endif
                                                @if($payout->processed_at)
                                                    <br>{{ $payout->processed_at->format('d/m/Y H:i') }}
                                                    <small class="text-muted">({{ $payout->processed_at->diffForHumans() }})</small>
                                                @endif
                                                @if($payout->failure_reason)
                                                    <br><strong>{{ __('admin.reason') }}:</strong> {{ $payout->failure_reason }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($payout->status === 'pending')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-secondary"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ __('admin.awaiting_admin_action') }}</h6>
                                            <p class="timeline-description">{{ __('admin.please_review_and_approve_or_reject') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">{{ __('admin.no_bookings_found') }}</h5>
                            <p class="text-muted">{{ __('admin.payout_items_not_available') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
</style>
@endpush

@push('scripts')

@endpush

    @push('scripts')
        <script src="{{ asset('js/pages/admin-payouts-show.js') }}"></script>
    @endpush
@endsection
