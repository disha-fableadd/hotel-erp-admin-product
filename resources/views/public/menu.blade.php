<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1a1814">
    <title>{{ $setting->name ?? 'Menu' }} · Digital Menu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1a1814;
            --muted: #6f6860;
            --paper: #f4efe6;
            --card: rgba(255,252,247,0.92);
            --line: rgba(26,24,20,0.1);
            --accent: #b45309;
            --accent-soft: #fde68a;
            --chip: #2a2622;
            --ok: #166534;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            color: var(--ink);
            font-family: "DM Sans", system-ui, sans-serif;
            background:
                radial-gradient(900px 420px at 100% -10%, rgba(180,83,9,0.18), transparent 55%),
                radial-gradient(700px 380px at -10% 20%, rgba(42,38,34,0.08), transparent 50%),
                linear-gradient(180deg, #efe6d8 0%, var(--paper) 40%, #ebe4d8 100%);
            min-height: 100vh;
        }
        .wrap { max-width: 720px; margin: 0 auto; padding: 0 16px 48px; }
        .hero {
            padding: 28px 0 18px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 14px;
        }
        .eyebrow {
            display: inline-block;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 8px;
        }
        .brand {
            font-family: "Fraunces", Georgia, serif;
            font-size: clamp(1.9rem, 6vw, 2.6rem);
            font-weight: 700;
            line-height: 1.1;
            margin: 0 0 8px;
            letter-spacing: -0.02em;
        }
        .meta { color: var(--muted); font-size: 0.92rem; margin: 0; }
        .search-wrap { margin-top: 16px; position: relative; }
        .search-wrap input {
            width: 100%;
            border: 1px solid var(--line);
            background: var(--card);
            border-radius: 14px;
            padding: 12px 14px 12px 40px;
            font: inherit;
            outline: none;
            box-shadow: 0 8px 24px rgba(26,24,20,0.04);
        }
        .search-wrap input:focus { border-color: rgba(180,83,9,0.45); }
        .search-wrap svg {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 16px; height: 16px; opacity: 0.45;
        }
        .cats {
            position: sticky; top: 0; z-index: 20;
            display: flex; gap: 8px; overflow-x: auto;
            padding: 10px 0 12px;
            background: linear-gradient(180deg, rgba(244,239,230,0.96), rgba(244,239,230,0.88));
            backdrop-filter: blur(8px);
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .cats::-webkit-scrollbar { display: none; }
        .cats a {
            flex: 0 0 auto;
            text-decoration: none;
            color: var(--ink);
            background: #fffaf3;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 0.84rem;
            font-weight: 500;
            white-space: nowrap;
        }
        .cats a.active, .cats a:hover {
            background: var(--chip);
            color: #fffaf3;
            border-color: var(--chip);
        }
        .section { margin-top: 22px; scroll-margin-top: 64px; }
        .section-head {
            display: flex; align-items: baseline; justify-content: space-between;
            gap: 12px; margin-bottom: 12px;
        }
        .section-head h2 {
            font-family: "Fraunces", Georgia, serif;
            font-size: 1.35rem;
            margin: 0;
            font-weight: 600;
        }
        .section-head span { color: var(--muted); font-size: 0.8rem; }
        .list { display: grid; gap: 10px; }
        .item {
            display: grid;
            grid-template-columns: 72px 1fr auto;
            gap: 12px;
            align-items: center;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 10px;
            box-shadow: 0 10px 30px rgba(26,24,20,0.04);
        }
        .item.no-img { grid-template-columns: 1fr auto; }
        .thumb {
            width: 72px; height: 72px; border-radius: 12px; object-fit: cover;
            background: #e8dfd0;
        }
        .name {
            font-weight: 600;
            font-size: 0.98rem;
            margin: 0 0 4px;
            text-transform: capitalize;
        }
        .desc {
            margin: 0;
            color: var(--muted);
            font-size: 0.8rem;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .variants {
            margin-top: 6px;
            display: flex; flex-wrap: wrap; gap: 4px;
        }
        .variants span {
            font-size: 0.7rem;
            background: var(--accent-soft);
            color: #7c2d12;
            border-radius: 999px;
            padding: 2px 8px;
            font-weight: 500;
        }
        .price {
            font-weight: 700;
            font-size: 0.95rem;
            white-space: nowrap;
            color: var(--ink);
            text-align: right;
        }
        .price small { display: block; font-weight: 500; color: var(--muted); font-size: 0.7rem; }
        .empty {
            text-align: center; color: var(--muted); padding: 48px 12px;
        }
        .footer {
            margin-top: 36px; text-align: center; color: var(--muted); font-size: 0.78rem;
        }
        .item.is-hidden { display: none; }
        .section.is-empty { display: none; }
        @media (max-width: 420px) {
            .item { grid-template-columns: 64px 1fr auto; }
            .thumb { width: 64px; height: 64px; }
        }
    </style>
</head>
<body>
@php
    $fmt = function ($amount) use ($currency, $currencyPos) {
        $n = number_format((float) $amount, 2);
        return $currencyPos === 'right' ? ($n . ' ' . $currency) : ($currency . ' ' . $n);
    };
@endphp
<div class="wrap">
    <header class="hero">
        <div class="eyebrow">Digital Menu</div>
        <h1 class="brand">{{ $setting->name ?? 'Our Menu' }}</h1>
        @if(!empty($setting->address))
            <p class="meta">{{ $setting->address }}</p>
        @endif
        @if(!empty($setting->phone))
            <p class="meta">Ph: {{ $setting->phone }}</p>
        @endif
        <div class="search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
            <input type="search" id="menuSearch" placeholder="Search dishes…" autocomplete="off" value="{{ $search }}">
        </div>
    </header>

    @if($categories->isEmpty())
        <div class="empty">No menu items available right now.</div>
    @else
        <nav class="cats" id="catNav">
            @foreach($categories as $cat)
                <a href="#cat-{{ $cat->id }}" data-cat="{{ $cat->id }}">{{ $cat->name }}</a>
            @endforeach
        </nav>

        @foreach($categories as $cat)
            <section class="section" id="cat-{{ $cat->id }}" data-section="{{ $cat->id }}">
                <div class="section-head">
                    <h2>{{ $cat->name }}</h2>
                    <span>{{ $cat->products->count() }} items</span>
                </div>
                <div class="list">
                    @foreach($cat->products as $product)
                        @php
                            $imgs = $product->image_url ?? [];
                            $img = is_array($imgs) && count($imgs) ? $imgs[0] : null;
                            $hasImg = $img && !str_contains((string) $img, 'noimage');
                            $variants = $product->variants ?? collect();
                            $base = (float) ($product->price ?? 0);
                            if ($variants->count()) {
                                $prices = $variants->pluck('price')->map(fn ($p) => (float) $p)->filter()->values();
                                if ($prices->count()) {
                                    $min = $prices->min();
                                    $max = $prices->max();
                                    $priceLabel = $min === $max ? $fmt($min) : ($fmt($min) . ' – ' . $fmt($max));
                                    $from = $min !== $max;
                                } else {
                                    $priceLabel = $fmt($base);
                                    $from = false;
                                }
                            } else {
                                $priceLabel = $fmt($base);
                                $from = false;
                            }
                            $desc = trim((string) ($product->description ?? $product->product_description ?? ''));
                            $searchBlob = strtolower(trim($product->name . ' ' . $cat->name . ' ' . $desc . ' ' . $variants->pluck('name')->implode(' ')));
                        @endphp
                        <article class="item {{ $hasImg ? '' : 'no-img' }}" data-search="{{ e($searchBlob) }}">
                            @if($hasImg)
                                <img class="thumb" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" onerror="this.parentElement.classList.add('no-img'); this.remove();">
                            @endif
                            <div>
                                <h3 class="name">{{ $product->name }}</h3>
                                @if($desc !== '')
                                    <p class="desc">{{ $desc }}</p>
                                @endif
                                @if($variants->count())
                                    <div class="variants">
                                        @foreach($variants->take(4) as $v)
                                            <span>{{ $v->name }}{{ isset($v->price) ? ' · '.$fmt($v->price) : '' }}</span>
                                        @endforeach
                                        @if($variants->count() > 4)
                                            <span>+{{ $variants->count() - 4 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="price">
                                @if(!empty($from))<small>From</small>@endif
                                {{ $priceLabel }}
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif

    <p class="footer">Menu powered by {{ $setting->name ?? 'Hotel ERP' }}</p>
</div>
<script>
(function () {
    var input = document.getElementById('menuSearch');
    var sections = Array.prototype.slice.call(document.querySelectorAll('[data-section]'));
    var links = Array.prototype.slice.call(document.querySelectorAll('#catNav a'));

    function filter() {
        var q = (input && input.value || '').toLowerCase().trim();
        sections.forEach(function (sec) {
            var items = sec.querySelectorAll('.item');
            var visible = 0;
            items.forEach(function (el) {
                var hay = el.getAttribute('data-search') || '';
                var show = !q || hay.indexOf(q) !== -1;
                el.classList.toggle('is-hidden', !show);
                if (show) visible++;
            });
            sec.classList.toggle('is-empty', visible === 0);
        });
    }
    if (input) {
        input.addEventListener('input', filter);
        if (input.value) filter();
    }

    function setActive() {
        var y = window.scrollY + 80;
        var current = null;
        sections.forEach(function (sec) {
            if (sec.classList.contains('is-empty')) return;
            if (sec.offsetTop <= y) current = sec.getAttribute('data-section');
        });
        links.forEach(function (a) {
            a.classList.toggle('active', a.getAttribute('data-cat') === current);
        });
    }
    window.addEventListener('scroll', setActive, { passive: true });
    setActive();
})();
</script>
</body>
</html>
