@extends('layout')

@section('title', 'Solutions - ARTSCI')

@section('content')
<div class="main-content" style="padding: 24px 16px; background: #f5f7fb;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="margin-bottom: 24px; text-align: center;">
            <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 8px;">Enterprise Solutions</h1>
            <p style="color: #6b7280;">Managed in the admin console. Products, barcodes, and images are stored in the database.</p>
        </div>

        @forelse($solutions as $solution)
            @php($anchor = \Illuminate\Support\Str::slug($solution->name ?? 'solution-' . $solution->id))
            <section id="{{ $anchor }}" style="margin-bottom: 32px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <h2 style="font-size: 22px; font-weight: 700; margin: 0;">{{ $solution->icon }} {{ $solution->name }}</h2>
                        @if($solution->description)
                            <p style="color: #6b7280; margin: 4px 0 0 0;">{{ $solution->description }}</p>
                        @endif
                    </div>
                    <span style="background: #eef2ff; color: #4338ca; padding: 6px 10px; border-radius: 999px; font-weight: 600;">{{ $solution->items->count() }} products</span>
                </div>

                @if($solution->items->count() > 0)
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                        @foreach($solution->items as $item)
                            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.04); display: flex; flex-direction: column; min-height: 100%;">
                                <div style="width: 100%; height: 170px; background: #f3f4f6;">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @endif
                                </div>
                                <div style="padding: 14px; display: flex; flex-direction: column; gap: 6px; flex: 1;">
                                    <div style="font-size: 16px; font-weight: 700; color: #111827;">{{ $item->name }}</div>
                                    @if($item->description)
                                        <div style="font-size: 14px; color: #4b5563;">{{ $item->description }}</div>
                                    @endif
                                    <div style="font-size: 13px; color: #6b7280;">ID: #{{ $item->id }} • Barcode: {{ $item->barcode }}</div>
                                    @if(!is_null($item->price))
                                        <div style="font-size: 16px; font-weight: 700; color: #0f766e;">R{{ number_format($item->price, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding: 16px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; color: #9a3412;">
                        No products yet in this category.
                    </div>
                @endif
            </section>
        @empty
            <div style="padding: 16px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; color: #9a3412; text-align: center;">
                No solutions configured. Add solutions and items from the admin dashboard.
            </div>
        @endforelse
    </div>
</div>
@endsection
