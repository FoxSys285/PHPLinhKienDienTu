<a href="{{ route('promotions.show', $promotion->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-[1.6rem] border border-white/90 bg-white/95 shadow-[0_10px_30px_rgba(38,89,180,.09)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(38,89,180,.16)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-700">
    <div class="relative isolate aspect-[3/2] overflow-hidden bg-blue-100">
        <img src="{{ $promotion->image_url }}" alt="{{ $promotion->eyebrow }}" width="768" height="512" loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}" decoding="async" class="size-full object-cover transition duration-500 group-hover:scale-105">
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-slate-950/30 to-transparent"></div>
        <span @class([
            'absolute right-3 top-3 rounded-2xl border border-white/25 px-3 py-2 text-xl font-black tracking-tight text-white shadow-lg backdrop-blur-md',
            'bg-blue-600/90' => $promotion->theme === 'blue',
            'bg-emerald-600/90' => $promotion->theme === 'cyan',
            'bg-violet-600/90' => $promotion->theme === 'violet',
            'bg-orange-600/90' => $promotion->theme === 'amber',
        ])>{{ $promotion->discount_label }}</span>
        <span class="absolute bottom-3 left-3 max-w-[90%] truncate rounded-full border border-white/25 bg-slate-950/30 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wide text-white backdrop-blur-md">{{ $promotion->eyebrow }}</span>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <span class="mb-2 w-fit rounded-md bg-blue-50 px-2 py-1 font-mono text-[10px] font-bold tracking-wide text-blue-700">{{ $promotion->code }}</span>
        <h2 class="text-base font-extrabold leading-snug tracking-tight text-[#102448]">{{ $promotion->title }}</h2>
        <p class="mt-2 flex-1 text-sm leading-6 text-slate-600">{{ $promotion->description }}</p>
        <div class="mt-5 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-4">
            <span class="text-[11px] font-semibold text-slate-500">Mua từ {{ number_format($promotion->minimum_purchase, 0, ',', '.') }}₫</span>
            <span @class([
                'inline-flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-extrabold',
                'border-blue-200 text-blue-600 group-hover:bg-blue-50' => $promotion->theme === 'blue',
                'border-emerald-200 text-emerald-600 group-hover:bg-emerald-50' => $promotion->theme === 'cyan',
                'border-violet-200 text-violet-600 group-hover:bg-violet-50' => $promotion->theme === 'violet',
                'border-orange-200 text-orange-600 group-hover:bg-orange-50' => $promotion->theme === 'amber',
            ])>Chi tiết <span aria-hidden="true">→</span></span>
        </div>
    </div>
</a>
