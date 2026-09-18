        <!-- Your Image Here -->
        @if(file_exists(base_path(findAvatar($userinfo->id))))
        <img alt="avatar" id="avatar" class="rounded-avatar fadein" src="{{ url(findAvatar($userinfo->id)) }}" height="128px" width="128px" style="object-fit: cover;">
        @elseif(file_exists(base_path("assets/nexsus/images/").findFile('avatar')))
        <img alt="avatar" id="avatar" class="fadein" src="{{ url("assets/nexsus/images/")."/".findFile('avatar') }}" height="128px" width="128px" style="object-fit: cover;">
        @else
        <img alt="avatar" id="avatar" class="fadein" src="{{ asset('assets/nexsus/images/logo.svg') }}" height="128px" style="width:auto;min-width:128px;object-fit: cover;">
        @endif
