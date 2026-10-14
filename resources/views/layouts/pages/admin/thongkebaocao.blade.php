@extends('layouts.admin')

@section('title', 'Thống kê và báo cáo')

@section('content')
    @php($maxDailyRevenue = max((float) $dailyRevenue->max('revenue'), 1))
    <div class="admin-page-heading"><div><span class="admin-eyebrow">HIỆU QUẢ KINH DOANH</span><h1>Thống kê / báo cáo</h1><p>Doanh thu tính từ đơn hoàn thành và đã thanh toán, sau giảm giá.</p></div></div>
    <section class="admin-panel">
        <form class="admin-toolbar" method="get" action="{{ route('admin.reports.index') }}">
            <div class="admin-field"><label for="reportFrom">Từ ngày</label><input id="reportFrom" type="date" name="from" value="{{ $filters['from'] }}"></div>
            <div class="admin-field"><label for="reportTo">Đến ngày</label><input id="reportTo" type="date" name="to" value="{{ $filters['to'] }}"></div>
            <button class="admin-button" type="submit">Xem báo cáo</button>
            <a class="admin-button-secondary" href="{{ route('admin.reports.index') }}">Tháng này</a>
        </form>
    </section>
    <div class="admin-stats-grid">
        <article class="admin-stat-card"><span>Doanh thu</span><strong>{{ number_format((float) $revenue, 0, ',', '.') }}₫</strong><small>Sau giảm giá, chưa tính phí giao</small></article>
        <article class="admin-stat-card"><span>Đơn đã hoàn thành</span><strong>{{ number_format($completedOrders) }}</strong><small>Đã thanh toán trong kỳ</small></article>
        <article class="admin-stat-card"><span>Linh kiện đã bán</span><strong>{{ number_format($unitsSold) }}</strong><small>Số lượng trong đơn đủ điều kiện</small></article>
        <article class="admin-stat-card"><span>Đơn đang xử lý</span><strong>{{ number_format($pendingOrders) }}</strong><small>Trạng thái chờ, xác nhận, đang giao</small></article>
    </div>
    <div class="admin-detail-grid">
        <section class="admin-panel"><div class="admin-panel-header"><div><h2>Doanh thu theo ngày</h2><p>Tối đa 30 ngày có phát sinh doanh thu trong khoảng đã chọn.</p></div></div>
            @if ($dailyRevenue->isNotEmpty())
                <div class="admin-panel-body"><div class="admin-chart" aria-label="Biểu đồ doanh thu theo ngày">
                    @foreach ($dailyRevenue->take(-30) as $day)
                        <div class="admin-chart-item" title="{{ \Carbon\Carbon::parse($day->period)->format('d/m/Y') }}: {{ number_format((float) $day->revenue, 0, ',', '.') }}₫"><div class="admin-chart-bar-wrap"><span class="admin-chart-bar" style="height:{{ max(3, ((float) $day->revenue / $maxDailyRevenue) * 100) }}%"></span></div><small>{{ \Carbon\Carbon::parse($day->period)->format('d/m') }}</small></div>
                    @endforeach
                </div></div>
            @else
                <div class="admin-empty">Chưa có doanh thu trong khoảng thời gian này.</div>
            @endif
        </section>
        <section class="admin-panel"><div class="admin-panel-header"><div><h2>Sản phẩm bán chạy</h2><p>Xếp theo số lượng đã bán.</p></div></div>
            <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Sản phẩm</th><th>Số lượng</th><th>Tiền hàng</th></tr></thead><tbody>
                @forelse ($topProducts as $product)
                    <tr><td><strong>{{ $product->product_name }}</strong></td><td>{{ number_format((int) $product->units_sold) }}</td><td>{{ number_format((float) $product->revenue, 0, ',', '.') }}₫</td></tr>
                @empty
                    <tr><td class="admin-empty" colspan="3">Chưa có sản phẩm bán ra trong kỳ.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </div>
@endsection
