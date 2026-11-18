@extends('layouts.admin')

@section('title', 'Kho & kiểm kê')

@section('content')
    @php
        $movementTypeLabels = [
            'opening' => 'Tồn đầu kỳ',
            'in' => 'Nhập kho',
            'adjustment' => 'Điều chỉnh',
            'reserve' => 'Giữ hàng',
            'out' => 'Xuất kho',
            'release' => 'Trả giữ hàng',
        ];
    $summaryCards = [
        ['label' => 'Mặt hàng', 'value' => $stats['products'], 'note' => 'Đang được theo dõi trong kho', 'tone' => 'text-blue-700', 'icon' => 'SP'],
        ['label' => 'Tồn kho thấp', 'value' => $stats['low_stock'], 'note' => 'Cần lên kế hoạch nhập', 'tone' => 'text-amber-600', 'icon' => '↓'],
        ['label' => 'Hết hàng khả dụng', 'value' => $stats['out_of_stock'], 'note' => 'Không còn hàng để xuất', 'tone' => 'text-rose-600', 'icon' => '!'],
        ['label' => 'Giao dịch đã ghi', 'value' => $stats['movements'], 'note' => 'Có lưu người thao tác và thời gian', 'tone' => 'text-emerald-600', 'icon' => '↕'],
    ];
    @endphp

    <div class="mx-auto max-w-screen-2xl">
        <div class="mb-7">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Quản lý kho</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#172554] sm:text-4xl">Tồn kho & kiểm kê</h1>
            <p class="mt-2 text-sm text-slate-500">Kiểm soát hàng khả dụng, nhập kho và xem lại lịch sử từng giao dịch.</p>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <div class="group relative overflow-hidden rounded-2xl border border-white/80 bg-white/75 p-5 shadow-[0_14px_36px_-28px_rgba(23,37,84,.4)] backdrop-blur transition hover:-translate-y-0.5 hover:shadow-lg">
                    <span class="absolute -right-5 -top-7 size-24 rounded-full bg-blue-50/80 transition group-hover:scale-125"></span>
                    <div class="relative flex items-start justify-between gap-3"><div><p class="text-xs font-semibold text-slate-500">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p><p class="mt-1 text-[11px] text-slate-400">{{ $card['note'] }}</p></div><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-xs font-black text-blue-700">{{ $card['icon'] }}</span></div>
                </div>
            @endforeach
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                <p class="font-bold">Bộ lọc chưa hợp lệ.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid items-start gap-5 2xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,.85fr)]">
            <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="text-base font-extrabold text-[#172554]">Danh sách tồn kho</h2><p class="mt-1 text-xs text-slate-500">Chọn sản phẩm để ghi nhận nhập hàng hoặc kiểm kê.</p></div><span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">{{ number_format($products->total()) }} mặt hàng phù hợp</span></div>
                    <form method="GET" class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach (['activity_q', 'movement_type', 'date_from', 'date_to'] as $filterName)
                            @if (request()->filled($filterName))
                                <input type="hidden" name="{{ $filterName }}" value="{{ request($filterName) }}">
                            @endif
                        @endforeach
                        <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                            Tìm sản phẩm
                            <input name="q" value="{{ old('q', request('q')) }}" placeholder="Tên, SKU hoặc thương hiệu" class="rounded-xl border-slate-200 bg-white text-sm">
                        </label>
                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            Danh mục
                            <select name="category_id" class="rounded-xl border-slate-200 bg-white text-sm">
                                <option value="">Tất cả danh mục</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id', request('category_id')) === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            Tình trạng tồn
                            <select name="stock_status" class="rounded-xl border-slate-200 bg-white text-sm">
                                <option value="">Tất cả tình trạng</option>
                                <option value="out" @selected(old('stock_status', request('stock_status')) === 'out')>Hết hàng khả dụng</option>
                                <option value="low" @selected(old('stock_status', request('stock_status')) === 'low')>Tồn thấp</option>
                                <option value="normal" @selected(old('stock_status', request('stock_status')) === 'normal')>Đủ hàng</option>
                            </select>
                        </label>
                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            Trạng thái sản phẩm
                            <select name="product_status" class="rounded-xl border-slate-200 bg-white text-sm">
                                <option value="">Tất cả sản phẩm</option>
                                <option value="active" @selected(old('product_status', request('product_status')) === 'active')>Đang kinh doanh</option>
                                <option value="inactive" @selected(old('product_status', request('product_status')) === 'inactive')>Ngừng kinh doanh</option>
                            </select>
                        </label>
                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            Sắp xếp
                            <select name="sort" class="rounded-xl border-slate-200 bg-white text-sm">
                                <option value="stock_asc" @selected(old('sort', request('sort', 'stock_asc')) === 'stock_asc')>Tồn thấp trước</option>
                                <option value="stock_desc" @selected(old('sort', request('sort')) === 'stock_desc')>Tồn cao trước</option>
                                <option value="name" @selected(old('sort', request('sort')) === 'name')>Tên A–Z</option>
                                <option value="updated_desc" @selected(old('sort', request('sort')) === 'updated_desc')>Cập nhật gần đây</option>
                            </select>
                        </label>
                        <div class="flex flex-wrap items-end gap-2 sm:col-span-2">
                            <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-900">Lọc tồn kho</button>
                            <a href="{{ route('admin.inventory.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Xóa bộ lọc</a>
                            <span class="ml-auto self-center text-xs text-slate-500">{{ $products->total() }} sản phẩm</span>
                        </div>
                    </form>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <details class="group px-5 py-4 transition open:bg-blue-50/25">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                                <span>
                                    <strong class="block text-sm font-bold text-[#172554]">{{ $product->name }}</strong>
                                    <span class="mt-1 block font-mono text-[10px] text-slate-500">{{ $product->sku }} · {{ $product->inventory_movements_count }} giao dịch</span>
                                </span>
                                <span class="flex items-center gap-3">
                                    <span class="text-right">
                                        <strong @class(['block', 'text-rose-700' => $product->availableStock() <= 0, 'text-amber-700' => $product->availableStock() > 0 && $product->availableStock() <= $product->minimum_stock, 'text-slate-900' => $product->availableStock() > $product->minimum_stock])>{{ $product->availableStock() }} <span class="text-xs font-normal text-slate-400">khả dụng</span></strong>
                                        <span class="text-[10px] text-slate-500">{{ $product->stock }} thực tế · Tối thiểu {{ $product->minimum_stock }}</span>
                                    </span>
                                    <span class="grid size-8 place-items-center rounded-full bg-white text-slate-400 shadow-sm transition group-open:rotate-180">⌄</span>
                                </span>
                            </summary>

                            <form method="POST" action="{{ route('admin.inventory.store', $product) }}" class="mt-4 grid gap-3 rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:grid-cols-2">
                                @csrf
                                <label class="grid gap-1 text-[10px] font-bold text-slate-600">
                                    Loại giao dịch
                                    <select name="type" class="rounded-lg border-slate-200 text-sm font-normal">
                                        <option value="in">Nhập hàng (+)</option>
                                        <option value="adjustment">Điều chỉnh / kiểm kê (+/-)</option>
                                    </select>
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold text-slate-600">
                                    Số lượng
                                    <input required type="number" name="quantity" min="-1000000" max="1000000" value="1" class="rounded-lg border-slate-200 text-sm font-normal">
                                    <span class="font-normal text-slate-400">Điều chỉnh: nhập số dương để tăng, số âm để giảm.</span>
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold text-slate-600">
                                    Nhà cung cấp
                                    <input name="supplier" class="rounded-lg border-slate-200 text-sm font-normal">
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold text-slate-600">
                                    Số lô
                                    <input name="lot_number" class="rounded-lg border-slate-200 text-sm font-normal">
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold text-slate-600 sm:col-span-2">
                                    Lý do / ghi chú
                                    <input required name="note" class="rounded-lg border-slate-200 text-sm font-normal">
                                </label>
                                <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-blue-900 sm:col-span-2">Lưu giao dịch kho</button>
                            </form>
                        </details>
                    @empty
                        <div class="grid justify-items-center gap-2 px-4 py-10 text-center">
                            <strong class="text-sm text-slate-700">Không tìm thấy sản phẩm phù hợp</strong>
                            <p class="text-xs text-slate-500">Thử đổi từ khóa hoặc điều kiện lọc tồn kho.</p>
                            <a href="{{ route('admin.inventory.index') }}" class="font-bold text-blue-800 hover:underline">Xóa bộ lọc</a>
                        </div>
                    @endforelse
                </div>

                <div class="border-t border-slate-100 p-4">{{ $products->links() }}</div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <div class="flex items-start justify-between gap-3"><div><h2 class="text-base font-extrabold text-[#172554]">Nhật ký kho</h2><p class="mt-1 text-xs text-slate-500">Tìm theo sản phẩm, mã phiếu hoặc người cập nhật.</p></div><span class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-bold text-violet-700">{{ number_format($movements->total()) }} giao dịch</span></div>
                    <form method="GET" class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach (['q', 'category_id', 'stock_status', 'product_status', 'sort'] as $filterName)
                            @if (request()->filled($filterName))
                                <input type="hidden" name="{{ $filterName }}" value="{{ request($filterName) }}">
                            @endif
                        @endforeach
                        <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                            Tìm giao dịch
                            <input name="activity_q" value="{{ old('activity_q', request('activity_q')) }}" placeholder="Mã phiếu, sản phẩm, số lô, nhà cung cấp..." class="rounded-xl border-slate-200 bg-white text-sm">
                        </label>
                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            Loại giao dịch
                            <select name="movement_type" class="rounded-xl border-slate-200 bg-white text-sm">
                                <option value="">Tất cả giao dịch</option>
                                <option value="opening" @selected(request('movement_type') === 'opening')>Tồn đầu kỳ</option>
                                <option value="in" @selected(request('movement_type') === 'in')>Nhập kho</option>
                                <option value="adjustment" @selected(request('movement_type') === 'adjustment')>Điều chỉnh</option>
                                <option value="reserve" @selected(request('movement_type') === 'reserve')>Giữ hàng</option>
                                <option value="out" @selected(request('movement_type') === 'out')>Xuất kho</option>
                                <option value="release" @selected(request('movement_type') === 'release')>Trả giữ hàng</option>
                            </select>
                        </label>
                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            Từ ngày
                            <input type="date" name="date_from" value="{{ old('date_from', request('date_from')) }}" class="rounded-xl border-slate-200 bg-white text-sm">
                        </label>
                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            Đến ngày
                            <input type="date" name="date_to" value="{{ old('date_to', request('date_to')) }}" class="rounded-xl border-slate-200 bg-white text-sm">
                        </label>
                        <div class="flex flex-wrap items-end gap-2 sm:col-span-2">
                            <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-900">Lọc nhật ký</button>
                            <a href="{{ route('admin.inventory.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Xóa bộ lọc</a>
                        </div>
                    </form>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($movements as $movement)
                        <article class="px-5 py-4 transition hover:bg-blue-50/25">
                            <div class="flex justify-between gap-3">
                                <strong class="text-xs font-bold text-[#172554]">{{ $movement->product?->name }}</strong>
                                <span class="font-mono text-xs font-black {{ $movement->quantity > 0 ? 'text-emerald-800' : 'text-rose-700' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</span>
                            </div>
                            <p class="mt-1 text-[10px] leading-5 text-slate-500">{{ $movementTypeLabels[$movement->type] ?? $movement->type }} · {{ $movement->reference }}<span class="text-slate-300"> · </span>{{ $movement->user?->name ?? 'Hệ thống' }}<span class="text-slate-300"> · </span>{{ $movement->created_at->format('d/m/Y H:i') }}</p>
                            @if ($movement->lot_number)
                                <p class="mt-1 text-[10px] text-slate-500">Lô: {{ $movement->lot_number }}</p>
                            @endif
                            @if ($movement->note)
                                <p class="mt-1 text-xs text-slate-600">{{ $movement->note }}</p>
                            @endif
                        </article>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500">Không tìm thấy giao dịch phù hợp.</div>
                    @endforelse
                </div>

                <div class="border-t border-slate-100 p-4">{{ $movements->links() }}</div>
            </section>
        </div>
    </div>
@endsection
