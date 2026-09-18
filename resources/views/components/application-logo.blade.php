@if(file_exists(base_path("/assets/nexsus/images/logo.svg" )))
    <img class="mb-5" src="{{ asset('/assets/nexsus/images/logo.svg') }}"  style="width: 150px;">
@else
    <img class="mb-5" src="{{ asset('/assets/nexsus/images/logo.svg') }}">
@endif
