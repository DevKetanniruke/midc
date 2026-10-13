@props(['name', 'label', 'value' => '', 'type' => 'text', 'required' => false, 'hint' => null])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
    </label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
           @required($required)
           {{ $attributes->except('class')->merge(['class' => 'block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-accent-500 focus:ring-accent-500'.($errors->has($name) ? ' border-red-400' : '')]) }}>
    @if($hint)<p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
