<!-- Necessary specific site styles -->
@yield('page_css')

<!-- Main project styles -->
{{--@vite(['resources/scss/bootstrap.scss'])--}}
{{--@vite(['resources/scss/icons.scss'])--}}
@vite(['resources/scss/auth.scss'])

<!-- Livewire styles -->
@livewireStyles
