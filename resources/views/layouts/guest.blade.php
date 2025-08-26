@props(['title' => config('app.name'), 'metaDescription' => '', 'metaKeywords' => '', 'metaRobots' => 'index, follow'])

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title Section -->
    <title>{{ $title }} - {{ config('app.name') }}</title>

    <!-- Author and Copyright meta tags -->
    <meta name="author" content="{{ config('app.author', 'OnyxCode') }}"/>
    <meta name="copyright" content="{{ config('app.consumer') }}">

    <!-- Site Content SEO Meta Tags -->
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="{{ trim($metaRobots) !== '' ? $metaRobots : 'index, follow' }}">

    <!-- Search Engine Verification (if available) todo: add this config data -->
    @if(config('app.google_verification'))
        <meta name="google-site-verification" content="{{ config('app.google_verification') }}">
    @endif

    @if(config('app.bing_verification'))
        <meta name="msvalidate.01" content="{{ config('app.bing_verification') }}">
    @endif

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph meta tags -->
    <meta property="og:locale" content="{{ config('app.locale') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Twitter Card meta tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">

    <!-- Structured Data (JSON-LD) todo: add this data -->
    {{--    <script type="application/ld+json">--}}
    {{--        {--}}
    {{--            "@context": "https://schema.org",--}}
    {{--            "@type": "WebSite",--}}
    {{--            "name": "{{ config('app.name') }}",--}}
    {{--        "url": "{{ url('/') }}"--}}
    {{--    }--}}
    {{--    </script>--}}

    <!-- Favicons -->
    @include('partials.favicons')

    <!-- Fonts -->
    {{--    @include('partials.fonts')--}}

    <!-- CSS Styles -->
    @include('partials.auth.styles')
</head>

<body class="guest-body" x-data="{}">
<main class="main-auth">
    <div class="container-fluid g-0">
        <div class="row min-vh-100 g-0">
            {{ $slot }}
        </div>
    </div>
</main>

<!-- JS Scripts -->
<!-- todo: add scripts -->
@include('partials.auth.scripts')
{{--<script>--}}
{{--    window.translations = @json(trans('validation'));    // np. validation.required, email, max_64...--}}
{{--    window.attributes  = @json(trans('validation.attributes'));     // np. attributes.first_name => "first name"--}}
{{--</script>--}}
</body>
</html>
