<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->title }} — {{ config('app.name') }}</title>
    @if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @if($page->og_image)
    <meta property="og:image" content="{{ $page->og_image }}">
    @endif
    <meta property="og:title" content="{{ $page->title }}">
    @if($page->meta_description)
    <meta property="og:description" content="{{ $page->meta_description }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: {{ $settings['font_family'] ?? 'Inter, sans-serif' }};
            background-color: {{ $settings['background_color'] ?? '#ffffff' }};
            color: {{ $settings['text_color'] ?? '#333333' }};
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: {{ $settings['container_width'] ?? '800px' }};
            margin: 0 auto;
            padding: {{ $settings['padding'] ?? '60px 20px' }};
        }
        .block-hero {
            text-align: center;
            padding: 80px 20px;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        .block-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .block-hero p {
            font-size: 1.25rem;
            opacity: 0.9;
        }
        .block-text {
            margin-bottom: 2rem;
        }
        .block-text.align-center { text-align: center; }
        .block-text.align-right { text-align: right; }
        .block-image {
            margin-bottom: 2rem;
        }
        .block-image img {
            max-width: 100%;
            border-radius: 0.5rem;
        }
        .block-cta {
            text-align: center;
            padding: 60px 20px;
            margin-bottom: 2rem;
        }
        .block-cta h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }
        .block-cta .btn {
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 0.5rem;
        }
        .block-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .feature-item {
            text-align: center;
            padding: 1.5rem;
        }
        .feature-item i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .block-testimonial {
            text-align: center;
            padding: 40px 20px;
            margin-bottom: 2rem;
            font-style: italic;
        }
        .block-testimonial blockquote {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        .block-spacer {
            height: {{ $block['data']['height'] ?? 60 }}px;
        }
        .email-form {
            max-width: 400px;
            margin: 0 auto;
        }
        {{ $settings['custom_css'] ?? '' }}
    </style>
    @if($settings['custom_head'])
    {!! $settings['custom_head'] !!}
    @endif
</head>
<body>
    <div class="container">
        @foreach($blocks as $block)
            @if($block['type'] === 'hero')
            <div class="block-hero" style="background-color: {{ $block['data']['background_color'] ?? '#667eea' }}; color: {{ $block['data']['text_color'] ?? '#ffffff' }};">
                <h1>{{ $block['data']['heading'] ?? '' }}</h1>
                @if($block['data']['subheading'] ?? '')
                <p>{{ $block['data']['subheading'] }}</p>
                @endif
            </div>

            @elseif($block['type'] === 'text')
            <div class="block-text align-{{ $block['data']['alignment'] ?? 'center' }}">
                {!! $block['data']['content'] ?? '' !!}
            </div>

            @elseif($block['type'] === 'image')
            @if($block['data']['url'] ?? '')
            <div class="block-image">
                <img src="{{ $block['data']['url'] }}" alt="{{ $block['data']['alt'] ?? '' }}">
            </div>
            @endif

            @elseif($block['type'] === 'cta')
            <div class="block-cta">
                <h2>{{ $block['data']['heading'] ?? '' }}</h2>
                @if($block['data']['button_text'] ?? '')
                <a href="{{ $block['data']['button_url'] ?? '#' }}" class="btn" style="background-color: {{ $block['data']['button_color'] ?? '#667eea' }}; color: white;">
                    {{ $block['data']['button_text'] }}
                </a>
                @endif
            </div>

            @elseif($block['type'] === 'features')
            @php
            $features = array_filter(explode("\n", $block['data']['features'] ?? ''));
            @endphp
            <div class="block-features">
                @foreach($features as $feature)
                @php
                $parts = array_map('trim', explode('|', $feature));
                @endphp
                <div class="feature-item">
                    @if($parts[0] ?? '')
                    <i class="bi {{ $parts[0] }}"></i>
                    @endif
                    <h4>{{ $parts[1] ?? '' }}</h4>
                    <p>{{ $parts[2] ?? '' }}</p>
                </div>
                @endforeach
            </div>

            @elseif($block['type'] === 'testimonial')
            <div class="block-testimonial">
                <blockquote>"{{ $block['data']['quote'] ?? '' }}"</blockquote>
                @if($block['data']['author'] ?? '')
                <cite>— {{ $block['data']['author'] }}</cite>
                @endif
            </div>

            @elseif($block['type'] === 'spacer')
            <div class="block-spacer" style="height: {{ $block['data']['height'] ?? 60 }}px;"></div>

            @elseif($block['type'] === 'form')
            @if($form && ($block['data']['form_id'] ?? '') == $form->id)
            <div class="block-form">
                @include('forms._embed', ['form' => $form])
            </div>
            @endif
            @endif
        @endforeach

        @if($page->collect_emails && !$page->form_id)
        <div class="email-form">
            <form action="{{ route('landing-pages.submit', $page->slug) }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </div>
            </form>
        </div>
        @endif
    </div>

    <div class="text-center mt-4 pb-4">
        <small class="text-muted">
            Powered by <a href="{{ url('/') }}">{{ config('app.name') }}</a>
        </small>
    </div>
</body>
</html>
