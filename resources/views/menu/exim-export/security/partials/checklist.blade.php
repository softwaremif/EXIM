{{-- Satu bagian checklist (A/B/C). Pakai: @include('...partials.checklist', ['key' => 'A', 'section' => $checklist['A'], 'answers' => $s->answers]) --}}
@php
    $codes = collect($section['groups'])->flatMap(fn($g) => array_keys($g['items']));
    $answered = $codes->filter(fn($code) => isset($answers[$code]['v']))->count();
@endphp
<section class="sg-section" data-check-section="{{ $key }}" @if ($key === 'A') id="sec-check" @endif>
    <div class="sg-section__head">
        <h2 class="sg-section__title">{{ $key }}. {{ $section['title'] }}</h2>
        <span class="sg-count {{ $answered === $codes->count() ? 'is-done' : '' }}" data-check-count>{{ $answered }}/{{ $codes->count() }}</span>
    </div>
    @foreach ($section['groups'] as $group)
        <div class="sg-check-group">
            @if ($group['title'])
                <h3 class="sg-check-group__title">{{ $loop->iteration }}. {{ $group['title'] }}</h3>
            @endif
            @foreach ($group['items'] as $code => [$question, $expect])
                @php
                    $v = $answers[$code]['v'] ?? null;
                    $note = $answers[$code]['n'] ?? '';
                    $state = $v === null ? '' : ($v === $expect ? 'is-ok' : 'is-issue');
                @endphp
                <div class="sg-check {{ $state }}" data-check="{{ $code }}" data-expect="{{ $expect }}">
                    <p class="sg-check__q" id="q-{{ $code }}">{{ $question }}</p>
                    <div class="sg-seg" role="radiogroup" aria-labelledby="q-{{ $code }}">
                        <label><input type="radio" name="ck_{{ $code }}" value="Y" {{ $v === 'Y' ? 'checked' : '' }}><span>Ya</span></label>
                        <label><input type="radio" name="ck_{{ $code }}" value="T" {{ $v === 'T' ? 'checked' : '' }}><span>Tidak</span></label>
                    </div>
                    <div class="sg-check__note" {{ $state === 'is-issue' ? '' : 'hidden' }}>
                        <input class="sg-input" name="ckn_{{ $code }}" value="{{ $note }}" maxlength="200" autocomplete="off" placeholder="Catatan temuan (wajib)" aria-label="Catatan temuan">
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</section>
