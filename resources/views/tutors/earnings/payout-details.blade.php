@extends('layouts.app')

@section('title', __('common.payout_details'))

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 text-gray-800 mb-1">🔍 {{ __('common.payout_details') }} #{{ $payout->id }}</h2>
                    <p class="text-muted mb-0">{{ __('common.detailed_payout_information') }}</p>
                </div>
                <div>
                    <a href="{{ route('tutors.earnings.history') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> {{ __('common.back_to_history') }}
                    </a>
                    @if($payout->status === 'pending')
                        <button type="button" class="btn btn-outline-danger" onclick="cancelPayout({{ $payout->id }})">
                            <i class="fas fa-times"></i> {{ __('common.cancel_payout') }}
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
                    <h6 class="m-0 font-weight-bold text-primary">📋 {{ __('common.payout_status') }}</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge badge-{{ $payout->status_color }} badge-pill p-3" style="font-size: 1.1em;">
                            {{ $payout->status_display }}
                        </span>
                    </div>

                    @if($payout->status === 'failed' && $payout->failure_reason)
                        <div class="alert alert-danger">
                            <strong>{{ __('common.failure_reason') }}:</strong><br>
                            {{ $payout->failure_reason }}
                        </div>
                    @endif

                    @if($payout->admin_notes)
                        <div class="alert alert-info">
                            <strong>{{ __('common.admin_notes') }}:</strong><br>
                            {{ $payout->admin_notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Amount Card -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">💰 {{ __('common.amount_details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="h2 text-success font-weight-bold">
                            {{ $payout->formatted_amount }}
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('common.bookings_count') }}:</span>
                            <strong>{{ $payout->payout_items_count }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('common.requested_date') }}:</span>
                            <strong>{{ $payout->requested_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        @if($payout->processed_at)
                            <div class="d-flex justify-content-between">
                                <span>{{ __('common.processed_date') }}:</span>
                                <strong>{{ $payout->processed_at->format('d/m/Y H:i') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">🏦 {{ __('common.bank_information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>{{ __('common.bank_name') }}:</strong><br>
                        {{ $payout->bank_name }}
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('common.account_number') }}:</strong><br>
                        <code>{{ $payout->account_number }}</code>
                    </div>
                    <div class="mb-0">
                        <strong>{{ __('common.account_holder') }}:</strong><br>
                        {{ $payout->account_holder_name }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Included Bookings -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">📚 {{ __('common.included_bookings') }}</h6>
                </div>
                <div class="card-body">
                    @if($payout->payoutItems->count() > 0)
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
                                        <th>{{ __('common.earnings') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payout->payoutItems as $item)
                                        @php $booking = $item->booking; @endphp
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
                                                        <img class="rounded-circle" width="25" height="25"
                                                             src="https://ui-avatars.com/api/?name={{ urlencode($booking->student->name) }}&background=007bff&color=fff"
                                                             alt="{{ $booking->student->name }}">
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold small">{{ $booking->student->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $booking->subject->name }}</span>
                                            </td>
                                            <td>{{ $booking->duration }} {{ __('common.minutes') }}</td>
                                            <td class="font-weight-bold">{{ number_format($booking->price, 0, ',', '.') }} VND</td>
                                            <td class="text-warning">{{ $booking->formatted_platform_fee }}</td>
                                            <td class="text-success font-weight-bold">{{ number_format($item->amount, 0, ',', '.') }} VND</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="5" class="text-right">{{ __('common.totals') }}</th>
                                        <th class="font-weight-bold">
                                            {{ number_format($payout->payoutItems->sum(function($item) { return $item->booking->price; }), 0, ',', '.') }} VND
                                        </th>
                                        <th class="text-warning font-weight-bold">
                                            {{ number_format($payout->payoutItems->sum(function($item) { return $item->booking->platform_fee_amount; }), 0, ',', '.') }} VND
                                        </th>
                                        <th class="text-success font-weight-bold">
                                            {{ $payout->formatted_amount }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Timeline -->
                        <div class="mt-4">
                            <h6 class="font-weight-bold mb-3">🕒 {{ __('common.payout_timeline') }}</h6>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ __('common.payout_requested') }}</h6>
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
                                            <h6 class="timeline-title">{{ __('common.processing_started') }}</h6>
                                            <p class="timeline-description">{{ __('common.payout_being_processed') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($payout->processed_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-{{ $payout->status === 'completed' ? 'success' : 'danger' }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">
                                                {{ $payout->status === 'completed' ? __('common.payout_completed') : __('common.payout_failed') }}
                                            </h6>
                                            <p class="timeline-description">
                                                {{ $payout->processed_at->format('d/m/Y H:i') }}
                                                <small class="text-muted">({{ $payout->processed_at->diffForHumans() }})</small>
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($payout->status === 'pending')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-secondary"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ __('common.waiting_admin_review') }}</h6>
                                            <p class="timeline-description">{{ __('common.admin_will_review_soon') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">{{ __('common.no_bookings_found') }}</h5>
                            <p class="text-muted">{{ __('common.payout_items_not_available') }}</p>
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
        <script src="{{ asset('js/pages/tutors-earnings-payout-details.js') }}"></script>
    @endpush
@endsection
