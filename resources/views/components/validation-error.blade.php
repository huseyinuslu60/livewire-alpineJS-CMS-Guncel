@props(['field'])

@error($field)
    <span class="mt-1 text-sm text-red-600 flex items-center gap-1">
        <i class="fas fa-exclamation-circle text-xs"></i>
        <span>{{ $message }}</span>
    </span>
@enderror
