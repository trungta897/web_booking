# 💰 COMMISSION SYSTEM SPECIFICATION

## 🎯 **OVERVIEW**
Triển khai hệ thống hoa hồng để platform có thể thu phí từ mỗi booking thành công.

## 📊 **BUSINESS LOGIC**

### Commission Structure:
```
Student Payment: 100,000 VND (2 hours × 50,000 VND/hour)
├── Platform Fee (15%): 15,000 VND  
└── Tutor Earnings (85%): 85,000 VND
```

### Commission Rates:
- **Default**: 15% platform fee
- **New Tutors** (first 30 days): 10% platform fee
- **Premium Tutors** (>50 reviews, >4.5 rating): 12% platform fee

## 🗄️ **DATABASE CHANGES**

### 1. Add to `bookings` table:
```sql
ALTER TABLE bookings ADD COLUMN platform_fee_percentage DECIMAL(5,2) DEFAULT 15.00;
ALTER TABLE bookings ADD COLUMN platform_fee_amount DECIMAL(10,2);
ALTER TABLE bookings ADD COLUMN tutor_earnings DECIMAL(10,2);
ALTER TABLE bookings ADD COLUMN commission_calculated_at TIMESTAMP NULL;
```

### 2. Create `tutor_payouts` table:
```sql
CREATE TABLE tutor_payouts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tutor_id BIGINT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    bank_account VARCHAR(50),
    bank_name VARCHAR(100),
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE,
    INDEX idx_tutor_status (tutor_id, status),
    INDEX idx_processed_at (processed_at)
);
```

### 3. Create `payout_items` table:
```sql
CREATE TABLE payout_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    payout_id BIGINT NOT NULL,
    booking_id BIGINT NOT NULL,
    tutor_earnings DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (payout_id) REFERENCES tutor_payouts(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_payout (booking_id, payout_id)
);
```

## 💻 **CODE IMPLEMENTATION**

### 1. Update BookingService:
```php
public function calculateCommission(Booking $booking): array
{
    $commissionRate = $this->getTutorCommissionRate($booking->tutor);
    $platformFee = $booking->price * ($commissionRate / 100);
    $tutorEarnings = $booking->price - $platformFee;
    
    return [
        'platform_fee_percentage' => $commissionRate,
        'platform_fee_amount' => round($platformFee, 2),
        'tutor_earnings' => round($tutorEarnings, 2)
    ];
}

private function getTutorCommissionRate(Tutor $tutor): float
{
    // New tutor discount (first 30 days)
    if ($tutor->created_at->diffInDays(now()) <= 30) {
        return 10.0;
    }
    
    // Premium tutor discount
    if ($tutor->reviews_count >= 50 && $tutor->reviews_avg_rating >= 4.5) {
        return 12.0;
    }
    
    return 15.0; // Default rate
}
```

### 2. Create PayoutService:
```php
class PayoutService
{
    public function calculateTutorPayout(Tutor $tutor): array
    {
        $unpaidBookings = Booking::where('tutor_id', $tutor->id)
            ->where('payment_status', 'paid')
            ->where('status', 'completed')
            ->whereNull('payout_id')
            ->get();
            
        $totalEarnings = $unpaidBookings->sum('tutor_earnings');
        
        return [
            'total_amount' => $totalEarnings,
            'booking_count' => $unpaidBookings->count(),
            'bookings' => $unpaidBookings
        ];
    }
    
    public function createPayout(Tutor $tutor, array $bankInfo): TutorPayout
    {
        $payoutData = $this->calculateTutorPayout($tutor);
        
        $payout = TutorPayout::create([
            'tutor_id' => $tutor->id,
            'total_amount' => $payoutData['total_amount'],
            'bank_account' => $bankInfo['account_number'],
            'bank_name' => $bankInfo['bank_name'],
            'status' => 'pending'
        ]);
        
        // Link bookings to payout
        foreach ($payoutData['bookings'] as $booking) {
            PayoutItem::create([
                'payout_id' => $payout->id,
                'booking_id' => $booking->id,
                'tutor_earnings' => $booking->tutor_earnings
            ]);
            
            $booking->update(['payout_id' => $payout->id]);
        }
        
        return $payout;
    }
}
```

## 🎨 **UI IMPLEMENTATION**

### 1. Tutor Earnings Dashboard:
```
/tutor/earnings
├── Total Earnings: 2,550,000 VND
├── Available for Withdrawal: 850,000 VND  
├── Pending Payout: 300,000 VND
└── Commission Rate: 15%

Recent Earnings:
[Table showing recent completed bookings with earnings]
```

### 2. Payout Request Form:
```
Bank Information:
- Bank Name: [Dropdown: Vietcombank, BIDV, Techcombank...]
- Account Number: [Input]
- Account Holder: [Auto-fill from profile]

Withdraw Amount: 850,000 VND
Processing Time: 3-5 business days
```

## ✅ **TESTING SCENARIOS**

### Test Cases:
1. New tutor gets 10% commission for first 30 days
2. Premium tutor gets 12% commission  
3. Regular tutor gets 15% commission
4. Commission calculation is accurate
5. Payout creation works correctly
6. Bank transfer integration (mock)

## 📈 **ANALYTICS NEEDED**

### Platform Dashboard:
```
Daily Revenue Tracking:
- Total Bookings: 45
- Total Revenue: 4,500,000 VND
- Platform Fees: 675,000 VND (15%)
- Tutor Payments: 3,825,000 VND (85%)

Monthly Trends:
- Commission Rate Optimization
- Tutor Retention Impact
- Revenue Growth
```

## 🚀 **ROLLOUT PLAN**

### Phase 1 (Week 1): Backend Implementation
- Database migrations
- Service classes
- Commission calculation logic

### Phase 2 (Week 2): UI Implementation  
- Tutor earnings dashboard
- Payout request interface
- Admin payout management

### Phase 3 (Week 3): Testing & Launch
- Full testing suite
- Soft launch with select tutors
- Monitor and optimize

---

**💡 Success Metrics:**
- 100% commission calculation accuracy
- <24h payout request processing time
- >90% tutor satisfaction with payout system
- Sustainable 15% platform revenue 
