<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expire-pending-payments')]
#[Description('Hủy đơn thanh toán quá hạn và giải phóng hàng đã giữ.')]
class ExpirePendingPayments extends Command
{
    public function handle(OrderService $orders): int
    {
        $count = $orders->releaseExpiredReservations();
        $intentCount = $orders->releaseExpiredPayOSIntents();
        $this->info("Đã xử lý {$count} đơn và {$intentCount} liên kết PayOS quá hạn.");

        return self::SUCCESS;
    }
}
