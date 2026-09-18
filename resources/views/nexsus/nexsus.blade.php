@extends('Nexsus Tracker.layout')

@section('content')
    @push('Nexsus Tracker-head')
        @include('Nexsus Tracker.modules.meta')
        @include('Nexsus Tracker.modules.assets')
    @endpush

    @include('Nexsus Tracker.modules.tracking')

    @push('Nexsus Tracker-head-end')
        @foreach($information as $info)
            @include('Nexsus Tracker.modules.theme')
        @endforeach
    @endpush

    @push('Nexsus Tracker-body-start')
        @include('Nexsus Tracker.modules.admin-bar')
        @include('Nexsus Tracker.modules.share-button')
        @include('Nexsus Tracker.modules.report-icon')
    @endpush

    @push('Nexsus Tracker-content')
        @foreach($information as $info)
            @include('Nexsus Tracker.elements.avatar')
            @include('Nexsus Tracker.elements.heading')
            @include('Nexsus Tracker.elements.bio')
        @endforeach
        @include('Nexsus Tracker.elements.icons')
        @include('Nexsus Tracker.elements.buttons')
        @yield('content')
        @include('Nexsus Tracker.modules.footer')
    @endpush
@endsection