<div>
    <!-- todo: change this to component or send this to partials -->
    @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    <form method="POST"
          action="{{ route('register') }}"
          x-data="{
        showPassword:false,
        tz: Intl.DateTimeFormat().resolvedOptions().timeZone ?? '',
        loc: (navigator.language || navigator.userLanguage || '').replace('-', '_')
     }"
          x-init="$nextTick(() => {
        /* Normalize locale to ll or ll_LL */
        if (loc && !/^[a-z]{2}([_-][A-Z]{2})?$/.test(loc)) { loc = 'pl'; }
     })"
          autocomplete="off"
          novalidate>

        @csrf

        <!-- First and last name inputs -->
        <div class="row">

            <!-- First name input -->
            <div class="col-xl-6">
                <div class="form-group">
                    <input
                        class="form-control {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                        {{--                        :class="errors.first_name ? 'is-invalid' : ''"--}}
                        {{--                        x-model="first_name"--}}
                        {{--                        x-init="first_name = '{{ old('first_name', '') }}'"--}}
                        {{--                        @input="onInput('first_name')"--}}
                        id="first_name"
                        type="text"
                        name="first_name"
                        placeholder=""
                        autocomplete="off"
                        required="">

                    <label for="first_name"
                           class="form-label">{{ __('First name') }}
                    </label>

                    <!-- Show user_first_name errors -->
                    {{--                    <span x-show="errors.first_name"--}}
                    {{--                          x-text="errors.first_name"--}}
                    {{--                          class="invalid-feedback font-size-12">--}}
                    {{--                    </span>--}}
                    @error('first_name')
                    <span class="invalid-feedback font-size-12" role="alert">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Last name input -->
            <div class="col-xl-6">
                <div class="form-group">
                    <input class="form-control {{ $errors->has('last_name') ? 'is-invalid' : '' }}"
                           {{--                           :class="errors.last_name ? 'is-invalid' : ''"--}}
                           {{--                           x-model="last_name"--}}
                           {{--                           x-init="last_name = '{{ old('last_name', '') }}'"--}}
                           {{--                           @input="onInput('last_name')"--}}
                           id="last_name"
                           type="text"
                           name="last_name"
                           placeholder=""
                           autocomplete="off"
                           required="">

                    <label for="last_name"
                           class="form-label">{{ __('Last name') }}
                    </label>

                    <!-- Show user_surname errors -->
                    {{--                    <span x-show="errors.last_name"--}}
                    {{--                          x-text="errors.last_name"--}}
                    {{--                          class="invalid-feedback font-size-12">--}}
                    {{--                    </span>--}}
                    @error('last_name')
                    <span class="invalid-feedback font-size-12" role="alert">{{ $message }}</span>
                    @enderror

                </div>
            </div>

            <!-- Company name input -->
            <div class="col-xl-12">
                <div class="form-group">
                    <input class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}"
                           {{--                           :class="errors.company_name ? 'is-invalid' : ''"--}}
                           {{--                           x-model="company_name"--}}
                           {{--                           x-init="company_name = '{{ old('company_name', '') }}'"--}}
                           {{--                           @input="onInput('company_name')"--}}
                           id="company_name"
                           type="text"
                           name="company_name"
                           placeholder=""
                           autocomplete="off"
                           required="">

                    <label for="company_name"
                           class="form-label">{{ __('Company name') }}
                    </label>

                    <!-- Show company_name errors -->
                    {{--                    <span x-show="errors.company_name"--}}
                    {{--                          x-text="errors.company_name"--}}
                    {{--                          class="invalid-feedback font-size-12">--}}
                    {{--                </span>--}}
                    @error('company_name')
                    <span class="invalid-feedback font-size-12" role="alert">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Email Address -->
            <div class="col-xl-12">
                <div class="form-group">
                    <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           {{--                           :class="errors.email ? 'is-invalid' : ''"--}}
                           {{--                           x-model="email"--}}
                           {{--                           x-init="email = '{{ old('email', '') }}'"--}}
                           {{--                           @input="onInput('email')"--}}
                           id="user_email"
                           type="email"
                           name="email"
                           placeholder=""
                           autocomplete="off"
                           required="">

                    <label for="user_email"
                           class="form-label">{{ __('Email') }}
                    </label>

                    <!-- Show email errors -->
                    {{--                    <span x-show="errors.email"--}}
                    {{--                          x-text="errors.email"--}}
                    {{--                          class="invalid-feedback font-size-12">--}}
                    {{--                    </span>--}}
                    @error('email')
                    <span class="invalid-feedback font-size-12" role="alert">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Password input -->
            <div class="col-xl-12">
                <div class="form-group">
                    <input class="form-control is-password {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           {{--                               :class="errors.password ? 'is-invalid' : ''"--}}
                           {{--                               x-model="password"--}}
                           {{--                               @input="onInput('password')"--}}
                           id="user_password"
                           {{--                               :type="show ? 'text' : 'password'"--}}
                           name="password"
                           placeholder=""
                           autocomplete="new-password"
                           required="">

                    <label for="user_password"
                           class="form-label">{{ __('Password') }}
                    </label>

                    <!-- Toggle icon -->
                    {{--                    <button type="button"--}}
                    {{--                            class="btn password-toggle"--}}
                    {{--                                @click="toggleShow('show')"--}}
                    {{--                            style="z-index: 5;">--}}
                    {{--                            <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>--}}
                    {{--                    </button>--}}

                    <!-- Show password errors -->
                    {{--                        <span x-show="errors.password"--}}
                    {{--                              x-text="errors.password"--}}
                    {{--                              class="invalid-feedback font-size-12">--}}
                    {{--                        </span>--}}

                    @error('password')
                    <span class="invalid-feedback font-size-12" role="alert">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Password confirmation input -->
            <div class="col-xl-12">
                <div class="form-group">
                    <input
                        class="form-control is-password {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                        {{--                               :class="errors.password_confirmation ? 'is-invalid' : ''"--}}
                        {{--                               x-model="password_confirmation"--}}
                        {{--                               @input="onInput('password_confirmation')"--}}
                        id="password_confirmation"
                        {{--                               :type="showConfirm  ? 'text' : 'password'"--}}
                        name="password_confirmation"
                        placeholder=""
                        autocomplete="off"
                        required="">

                    <label for="password_confirmation"
                           class="form-label">{{ __('Confirm password') }}
                    </label>

                    <!-- Toggle icon -->
                    {{--                    <button type="button"--}}
                    {{--                            class="btn password-toggle"--}}
                    {{--                                @click="toggleShow('showConfirm')"--}}
                    {{--                            style="z-index: 5;">--}}
                    {{--                                                    <i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>--}}
                    {{--                    </button>--}}

                    <!-- Show password confirmation errors -->
                    {{--                        <span x-show="errors.password_confirmation"--}}
                    {{--                              x-text="errors.password_confirmation"--}}
                    {{--                              class="invalid-feedback font-size-12">--}}
                    {{--                        </span>--}}

                    @error('password_confirmation')
                    <span class="invalid-feedback font-size-12" role="alert">{{ $message }}</span>
                    @enderror

                </div>
            </div>

            <!-- Hidden UX fields (populated by Alpine) -->
            <input type="hidden" name="time_zone" x-ref="time_zone">
            <input type="hidden" name="locale" x-ref="locale">
        </div>

        <!-- todo: add correct routes to terms and privacy-->
        <!-- Accept privacy and terms -->
        <div class="form-check mt-1 ms-sm-2">
            <input class="form-check-input {{ $errors->has('accept_privacy') ? 'is-invalid' : '' }}"
                   {{--                   :class="errors.accept_privacy ? 'is-invalid' : ''"--}}
                   {{--                   @change="validateField('accept_privacy');"--}}
                   {{--                   x-model="accept_privacy"--}}
                   id="acceptPrivacy"
                   type="checkbox"
                   name="accept_privacy">

            <label class="form-check-label" for="acceptPrivacy">
                {!! trans('I accept the :terms and :privacy.', ['terms' => '<a href="/terms" target="_blank">' . __('Terms of Use') . '</a>','privacy' => '<a href="/privacy" target="_blank">' . __('Privacy Policy') . '</a>',]) !!}
            </label>

            <!-- Show accept privacy errors -->
            {{--            <span x-show="errors.accept_privacy"--}}
            {{--                  x-text="errors.accept_privacy"--}}
            {{--                  class="invalid-feedback font-size-12">--}}
            {{--            </span>--}}
            @error('accept_privacy')
            <span class="invalid-feedback font-size-12" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- Sign Up button -->
        <div class="mt-2 d-grid p-sm-2">
            <button
                {{--                :disabled="hasFormErrors()"--}}
                class="btn btn-auth waves-effect waves-light bg-gradient"
                type="submit">{{ __('Sign Up') }}
            </button>
        </div>
    </form>

    <!-- Login page link -->
    <div class="text-center mt-3">
        @if (Route::has('login'))
            <p class="text-muted">{{ __("Already have an account") }}?
                <span>
                    <a href="{{ route('login') }}">{{ __('Sign In') }}.</a>
                </span>
            </p>
        @endif
    </div>

    <!-- Divider -->
    <div class="divider-with-text">
        <span></span>
        <span class="px-3">{{ __('or continue with') }}</span>
        <span></span>
    </div>

    <!-- todo: add social login functionality in the future -->
    <!-- Social login buttons -->
    <div class="social-login">
        <a href="#" class="btn btn-sm btn-google"><i class="bi bi-google me-2 align-middle"></i>{{ __('Login with') }}
            Google
        </a>
        <a href="#" class="btn btn-sm btn-facebook"><i class="bi bi-facebook me-2 align-middle"></i>{{ __('Login with') }}
            Facebook
        </a>
    </div>
</div>
