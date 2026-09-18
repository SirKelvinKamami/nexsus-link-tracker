        <!-- Short Bio -->
        <style>.description-parent * {margin-bottom: 1em;}.description-parent {padding-bottom: 30px;}</style>
        <center><div class="fadein description-parent dynamic-contrast"><p class="fadein">@if(env('ALLOW_USER_HTML') === true){!! $info->bio !!}@else{{ $info->bio }}@endif</p></div></center>
