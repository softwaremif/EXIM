@props([
    'items' => [],
    'cols' => 'row-cols-md-4',
])

@once
    
    <style>
        /* Info Utama: Lebih Tegas & Berstruktur */
        .info-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 600;
        }
    </style>
@endonce

<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-4">

        <div class="row g-4 row-cols-1 row-cols-sm-2 {{ $cols }}">

            @foreach($items as $item)
                <div class="col">
                    <div class="d-flex align-items-start gap-3">

                        <div
                            class="rounded-3 d-flex align-items-center justify-content-center {{ $item['class'] ?? 'bg-light text-dark' }}"
                            style="width:36px;height:36px;flex-shrink:0;">

                            <i class="fas {{ $item['icon'] ?? 'fa-circle' }} fs-6"></i>

                        </div>

                        <div class="overflow-hidden">

                            <div
                                class="text-secondary text-uppercase fw-semibold mb-0.5"
                                style="font-size:10px;letter-spacing:.5px;">

                                {{ $item['title'] }}

                            </div>

                            @if(isset($item['description']))
                                <div
                                    class="text-muted fw-normal"
                                    style="font-size:12px;line-height:1.4;word-break:break-word;">

                                    {{ $item['description'] }}

                                </div>
                            @else
                                <div
                                    class="fw-bold text-dark text-truncate"
                                    style="font-size:14px;"
                                    title="{{ $item['value'] ?? '' }}">

                                    {{ $item['value'] ?? '-' }}

                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            @endforeach

        </div>

    </div>
</div>