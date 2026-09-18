@extends('nexsus.layout')

@section('content')
    @push('nexsus-head')
        @include('nexsus.modules.meta')
        @include('nexsus.modules.assets')
    @endpush

    @include('nexsus.modules.tracking')

    @push('nexsus-head-end')
        @foreach($information as $info)
            @include('nexsus.modules.theme')
        @endforeach
    @endpush

    @push('nexsus-body-start')
        @include('nexsus.modules.admin-bar')
        @include('nexsus.modules.share-button')
        @include('nexsus.modules.report-icon')
    @endpush

    @push('nexsus-content')
        @foreach($information as $info)
            @include('nexsus.elements.avatar')
            @include('nexsus.elements.heading')
            @include('nexsus.elements.bio')
        @endforeach
        @include('nexsus.elements.icons')
        @include('nexsus.elements.buttons')
        @yield('content')
        @include('nexsus.modules.footer')
    @endpush
@endsection