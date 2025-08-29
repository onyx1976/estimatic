<div>
    @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    {{-- Alpine used only to populate hidden timezone/locale and simple show/hide password --}}
    <form method="POST" action="{{ route('register') }}"
          x-data="{
        showPassword:false,
        tz: Intl.DateTimeFormat().resolvedOptions().timeZone ?? '',
        loc: (navigator.language || navigator.userLanguage || '').replace('-', '_')
     }"
          x-init="$nextTick(() => {
        /* Normalize locale to ll or ll_LL */
        if (loc && !/^[a-z]{2}([_-][A-Z]{2})?$/.test(loc)) { loc = 'pl'; }
     })"
    >
        @csrf

        {{-- First Name (required) --}}
        <div class="form-group">
            <label for="first_name">First name</label>
            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required
                   autocomplete="given-name">
            @error('first_name')
            <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Last Name (required) --}}
        <div class="form-group">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required
                   autocomplete="family-name">
            @error('last_name')
            <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Company Name (required) --}}
        <div class="form-group">
            <label for="company_name">Company name</label>
            <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required
                   autocomplete="organization">
            @error('company_name')
            <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Email (required) --}}
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   maxlength="128">
            @error('email')
            <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Password (required) --}}
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-with-addon">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                       autocomplete="new-password">
                <button type="button" class="btn-ghost" @click="showPassword = !showPassword">
                    <span x-show="!showPassword">Show</span>
                    <span x-show="showPassword">Hide</span>
                </button>
            </div>
            @error('password')
            <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Confirm Password (required) --}}
        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input :type="showPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                   required autocomplete="new-password">
        </div>

        {{-- Privacy consent (required) --}}
        <div class="form-group">
            <label class="checkbox">
                <input type="checkbox" name="accept_privacy" value="1"
                       {{ old('accept_privacy') ? 'checked' : '' }} required>
                <span>I accept the Privacy Policy</span>
            </label>
            @error('accept_privacy')
            <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Hidden UX fields (populated by Alpine) --}}
        <input type="hidden" name="time_zone" :value="tz"> {{-- server will validate with `timezone` rule --}}
        <input type="hidden" name="locale" :value="loc"> {{-- format: ll or ll_LL --}}

        {{-- Submit --}}
        <div class="form-actions">
            <button type="submit">Create account</button>
        </div>
    </form>
</div>
