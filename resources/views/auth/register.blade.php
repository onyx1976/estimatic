<x-guest-layout title="{{ __('Register your business account') }}"
                metaDescription="{{ __('Create an account to start estimating projects and managing your paving business efficiently.') }}"
                metaKeywords="{{ __('cost estimation, paving estimates, construction quotes, paving business, project calculator, contractor tools') }}">

    <!-- Left section -->
    @include('partials.auth.left_section')

    <!-- Right Section -->
    <div class="col-xl-5 p-sm-0 right-section">
        <div class="right-wrapper">

            <!-- Form header -->
            <div class="form-header">
                <h5 class="text-primary">{{ __('Create a free account') }}</h5>
                <p class="text-muted">{{ __('Unlock advanced tools for accurate and efficient project costing.') }}</p>
            </div>

            <!-- Register form -->
            {{--            @livewire('auth.new-user-form')--}}

        </div>
    </div>
</x-guest-layout>
