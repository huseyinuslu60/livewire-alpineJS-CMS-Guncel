@props(['field'])

@error($field)
    <span class="text-danger small d-block mt-1">
        <i class="feather icon-alert-circle mr-1"></i>{{ $message }}
    </span>
@enderror
