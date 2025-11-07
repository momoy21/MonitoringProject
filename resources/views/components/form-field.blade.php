@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'maxlength' => null,
    'pattern' => null,
    'options' => [],
    'class' => 'col-md-6'
])

<div class="{{ $class }}">
    <div class="mb-3">
        <label for="{{ $name }}" class="form-label{{ $required ? ' required-field' : '' }}">{{ $label }}</label>

        @if($type === 'select')
            <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" {{ $required ? 'required' : '' }}>
                <option value="">-- Pilih {{ $label }} --</option>
                @foreach($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($type === 'textarea')
            <textarea
                class="form-control @error($name) is-invalid @enderror"
                id="{{ $name }}"
                name="{{ $name }}"
                placeholder="{{ $placeholder }}"
                {{ $maxlength ? "maxlength={$maxlength}" : '' }}
                {{ $required ? 'required' : '' }}
                rows="3">{{ old($name, $value) }}</textarea>
        @else
            <input
                type="{{ $type }}"
                class="form-control @error($name) is-invalid @enderror"
                id="{{ $name }}"
                name="{{ $name }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                {{ $maxlength ? "maxlength={$maxlength}" : '' }}
                {{ $pattern ? "pattern={$pattern}" : '' }}
                {{ $required ? 'required' : '' }}>
        @endif

        <div class="invalid-feedback" id="{{ $name }}-error">
            @error($name){{ $message }}@enderror
        </div>
        <div class="valid-feedback" id="{{ $name }}-success"></div>

        @error($name)
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
</div>
