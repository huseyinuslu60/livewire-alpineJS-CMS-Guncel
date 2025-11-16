@props(['message' => null])

@if($message)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="feather icon-check-circle mr-2"></i>
        {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
