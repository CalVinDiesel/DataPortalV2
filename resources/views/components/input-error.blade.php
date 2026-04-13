@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'small mt-1', 'style' => 'color: #ff3e1d; font-weight: 500;']) }}>
        @foreach ((array) $messages as $message)
            <div class="d-flex align-items-start gap-1">
                <span>*</span>
                <span>{{ $message }}</span>
            </div>
        @endforeach
    </div>
@endif
