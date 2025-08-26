<!-- Main project scripts -->
@vite(['resources/js/app.js'])
@livewireScripts

<!-- Necessary specific site scripts and modals -->
@stack('modals')
@stack('scripts')
