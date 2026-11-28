        <!-- Short Bio -->
        <style>.description-parent * {margin-bottom: 1em;}.description-parent {padding: 0 24px 30px;}</style>
        <center><div class="fadein description-parent dynamic-contrast"><p class="fadein" style="font-size: 17px; line-height: 1.7;">@if(env('ALLOW_USER_HTML') === true){!! $info->bio !!}@else{{ $info->bio }}@endif</p></div></center>
