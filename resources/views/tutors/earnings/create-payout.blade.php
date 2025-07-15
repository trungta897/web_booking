@extends('layouts.app')

@section('title', __('common.request_payout'))

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 text-gray-800 mb-1">💸 {{ __('common.request_payout') }}</h2>
                    <p class="text-muted mb-0">{{ __('common.withdraw_your_earnings') }}</p>
                </div>
                <a href="{{ route('tutors.earnings.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Payout Form -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">💳 {{ __('common.bank_information') }}</h6>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="payoutForm" action="{{ route('tutors.earnings.payout.store') }}" method="POST">
                        @csrf

                        <!-- Bank Selection -->
                        <div class="form-group">
                            <label for="bank_name" class="font-weight-bold">
                                {{ __('common.bank_name') }} <span class="text-danger">*</span>
                            </label>
                            <select name="bank_name" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror" required>
                                <option value="">{{ __('common.select_bank') }}</option>
                                @php
                                    $selectedBank = old('bank_name', '');
                                    if (is_array($selectedBank)) { $selectedBank = ''; }
                                @endphp
                                @foreach($banks as $code => $name)
                                    <option value="{{ $code }}" {{ $selectedBank == $code ? 'selected' : '' }}>
                                        {{ $code }} - {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Account Number -->
                        <div class="form-group">
                            <label for="account_number" class="font-weight-bold">
                                {{ __('common.account_number') }} <span class="text-danger">*</span>
                            </label>
                            @php
                                $accountNumberValue = old('account_number', '');
                                if (is_array($accountNumberValue)) { $accountNumberValue = ''; }
                            @endphp
                            <input type="text"
                                   name="account_number"
                                   id="account_number"
                                   class="form-control @error('account_number') is-invalid @enderror"
                                   value="{{ $accountNumberValue }}"
                                   placeholder="{{ __('common.enter_account_number') }}"
                                   required>
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('common.account_number_help') }}
                            </small>
                        </div>

                        <!-- Account Holder Name -->
                        <div class="form-group">
                            <label for="account_holder_name" class="font-weight-bold">
                                {{ __('common.account_holder_name') }} <span class="text-danger">*</span>
                            </label>
                            @php
                                $accountHolderValue = old('account_holder_name', $tutor->user->name);
                                if (is_array($accountHolderValue)) { $accountHolderValue = $tutor->user->name ?? ''; }
                            @endphp
                            <input type="text"
                                   name="account_holder_name"
                                   id="account_holder_name"
                                   class="form-control @error('account_holder_name') is-invalid @enderror"
                                   value="{{ $accountHolderValue }}"
                                   placeholder="{{ __('common.enter_account_holder_name') }}"
                                   required>
                            @error('account_holder_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('common.account_holder_name_help') }}
                            </small>
                        </div>

                        <!-- Payout Amount -->
                        <div class="form-group">
                            <label for="amount" class="font-weight-bold">
                                {{ __('common.payout_amount') }}
                            </label>
                            <div class="input-group">
                                @php
                                    $amountValue = old('amount', '');
                                    if (is_array($amountValue)) { $amountValue = ''; }
                                @endphp
                                <input type="number"
                                       name="amount"
                                       id="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ $amountValue }}"
                                       min="{{ $minimumPayout }}"
                                       max="{{ $earnings['available_earnings'] }}"
                                       placeholder="{{ __('common.leave_blank_for_full_amount') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">VND</span>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                {{ __('common.leave_blank_to_withdraw_all') }}: {{ number_format($earnings['available_earnings'], 0, ',', '.') }} VND
                            </small>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="terms" required>
                                <label class="custom-control-label" for="terms">
                                    {{ __('common.agree_to_payout_terms') }} <span class="text-danger">*</span>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                {{ __('common.payout_terms_text') }}
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> {{ __('common.submit_payout_request') }}
                            </button>
                            <a href="{{ route('tutors.earnings.index') }}" class="btn btn-secondary btn-lg ml-2">
                                {{ __('common.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payout Summary -->
        <div class="col-lg-4">
            <!-- Available Earnings -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">💰 {{ __('common.available_earnings') }}</h6>
                </div>
                <div class="card-body text-center">
                    <div class="h2 text-success font-weight-bold">
                        {{ number_format($earnings['available_earnings'], 0, ',', '.') }} VND
                    </div>
                    <p class="text-muted mb-0">{{ __('common.ready_for_withdrawal') }}</p>
                </div>
            </div>

            <!-- Payout Information -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">ℹ️ {{ __('common.payout_information') }}</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>{{ __('common.minimum_amount') }}:</strong> {{ number_format($minimumPayout, 0, ',', '.') }} VND
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning"></i>
                            <strong>{{ __('common.processing_time') }}:</strong> {{ __('common.1_3_business_days') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-university text-info"></i>
                            <strong>{{ __('common.transfer_method') }}:</strong> {{ __('common.bank_transfer') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-shield-alt text-success"></i>
                            <strong>{{ __('common.security') }}:</strong> {{ __('common.encrypted_secure') }}
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-phone text-primary"></i>
                            <strong>{{ __('common.support') }}:</strong> {{ __('common.24_7_support') }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">📊 {{ __('common.account_summary') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h6 text-muted">{{ __('common.pending') }}</div>
                                <div class="h5 text-warning">{{ number_format($earnings['pending_payout'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h6 text-muted">{{ __('common.paid_out') }}</div>
                                <div class="h5 text-success">{{ number_format($earnings['total_paid_out'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')

@endpush

    @push('scripts')
        <script src="{{ asset('js/pages/tutors-earnings-create-payout.js') }}"></script>
    @endpush
@endsection
