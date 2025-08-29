<div>
    @if (session('status'))
        <div class="alert alert-info">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" x-data>
        @csrf

        {{-- First Name --}}
        <div class="form-group">
            <label for="first_name">First name</label>
            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}"
                   autocomplete="given-name">
            @error('first_name')
            <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Last Name --}}
        <div class="form-group">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}"
                   autocomplete="family-name">
            @error('last_name')
            <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email Address --}}
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email')
            <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Phone --}}
        <div class="form-group">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required>
            @error('phone')
            <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-with-addon">
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                {{-- show/hide toggle can be added in Alpine in step 4.4 --}}
            </div>
            @error('password')
            <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   required>
        </div>

        {{-- Submit --}}
        <div class="form-actions">
            <button type="submit">Create account</button>
        </div>
    </form>

</div>
