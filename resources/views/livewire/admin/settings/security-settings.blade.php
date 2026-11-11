<div>
    <div class="row">
        <div class="col-12">

            {{-- ============================================================ --}}
            {{-- CARD 1: PASSWORD POLICY                                      --}}
            {{-- ============================================================ --}}
            <x-adminlte-card title="Password Policy" icon="fas fa-fw fa-lock">
                <form wire:submit.prevent="savePasswordPolicy">
                    <div class="row">
                        <div class="col-12 col-lg-6">

                            <p class="text-muted small mb-3">
                                Define the password strength requirements for all user accounts.
                                These rules are enforced during registration and password changes.
                            </p>

                            <x-adminlte-input name="password_min_length" label="Minimum Length"
                                type="number" placeholder="e.g., 6" min="4" max="128"
                                wire:model.lazy="password_min_length" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-hashtag"></i></div>
                                </x-slot>
                            </x-adminlte-input>

                            <x-adminlte-input name="password_expiry_days" label="Password Expiry (days)"
                                type="number" placeholder="0 = never" min="0" max="365"
                                wire:model.defer="password_expiry_days" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-calendar-alt"></i></div>
                                </x-slot>
                            </x-adminlte-input>

                            <div class="mt-2">
                                <label class="font-weight-bold d-block mb-2">Complexity Requirements</label>
                                <div class="d-flex flex-wrap gap-4">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="pwUpper"
                                            wire:model="password_require_upper" value="1">
                                        <label class="custom-control-label" for="pwUpper">
                                            Uppercase <span class="text-muted small font-weight-normal">(A–Z)</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="pwLower"
                                            wire:model="password_require_lower" value="1">
                                        <label class="custom-control-label" for="pwLower">
                                            Lowercase <span class="text-muted small font-weight-normal">(a–z)</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="pwNumber"
                                            wire:model="password_require_number" value="1">
                                        <label class="custom-control-label" for="pwNumber">
                                            Numbers <span class="text-muted small font-weight-normal">(0–9)</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="pwSymbol"
                                            wire:model="password_require_symbol" value="1">
                                        <label class="custom-control-label" for="pwSymbol">
                                            Symbols <span class="text-muted small font-weight-normal">(!@#$%)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Strength preview — uses live-updating component properties --}}
                            <div class="mt-3">
                                @php
                                    $strength = 0;
                                    if ($password_min_length >= 8) $strength++;
                                    if ($password_require_upper) $strength++;
                                    if ($password_require_lower) $strength++;
                                    if ($password_require_number) $strength++;
                                    if ($password_require_symbol) $strength++;
                                    $pct = min(100, round($strength / 5 * 100));
                                    $barTheme = $pct < 40 ? 'danger' : ($pct < 70 ? 'warning' : 'success');
                                @endphp
                                <label class="small text-muted mb-1">Password Strength Score</label>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar bg-{{ $barTheme }}" style="width:{{ $pct }}%"></div>
                                </div>
                                <small class="text-muted">{{ $strength }}/5 rules enabled</small>
                            </div>

                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <x-adminlte-button label="Save Password Policy" type="submit" theme="primary"
                            icon="fas fa-fw fa-save" />
                    </div>
                </form>
            </x-adminlte-card>

            {{-- CARD 3: LOGIN ATTEMPTS                                       --}}
            {{-- ============================================================ --}}
            <x-adminlte-card title="Login Attempts" icon="fas fa-fw fa-user-lock">
                <form wire:submit.prevent="saveLoginAttempts">
                    <div class="row">
                        <div class="col-12 col-lg-6">

                            <p class="text-muted small mb-3">
                                Configure how many failed login attempts are allowed before
                                the account is temporarily locked out.
                            </p>

                            <x-adminlte-input name="login_max_attempts" label="Max Failed Attempts"
                                type="number" placeholder="e.g., 5" min="1" max="100"
                                wire:model.lazy="login_max_attempts" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-ban"></i></div>
                                </x-slot>
                            </x-adminlte-input>

                            <x-adminlte-input name="login_lockout_minutes" label="Lockout Duration (minutes)"
                                type="number" placeholder="e.g., 15" min="1" max="1440"
                                wire:model.lazy="login_lockout_minutes" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-clock"></i></div>
                                </x-slot>
                            </x-adminlte-input>

                            <div class="alert alert-info small mb-0 py-2">
                                <i class="fas fa-fw fa-info-circle"></i>
                                After <strong>{{ $login_max_attempts }}</strong> failed attempt(s),
                                the account will be locked for
                                <strong>{{ $login_lockout_minutes }}</strong> minute(s).
                            </div>

                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <x-adminlte-button label="Save Login Attempts" type="submit" theme="primary"
                            icon="fas fa-fw fa-save" />
                    </div>
                </form>
            </x-adminlte-card>

            {{-- ============================================================ --}}
            {{-- CARD 4: SESSION TIMEOUT                                      --}}
            {{-- ============================================================ --}}
            <x-adminlte-card title="Session Timeout" icon="fas fa-fw fa-hourglass-half">
                <form wire:submit.prevent="saveSessionTimeout">
                    <div class="row">
                        <div class="col-12 col-lg-6">

                            <p class="text-muted small mb-3">
                                Control how long a user can remain idle before being
                                automatically logged out.
                            </p>

                            <x-adminlte-input name="session_timeout" label="Idle Timeout (minutes)"
                                type="number" placeholder="e.g., 120" min="1" max="43200"
                                wire:model.lazy="session_timeout" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-clock"></i></div>
                                </x-slot>
                            </x-adminlte-input>

                            <div class="custom-control custom-switch mt-3">
                                <input type="checkbox" class="custom-control-input" id="sessionExpire"
                                    wire:model="session_expire_on_close" value="1">
                                <label class="custom-control-label" for="sessionExpire">
                                    Expire on Browser Close
                                </label>
                            </div>

                            <dl class="row small mt-3 mb-0">
                                <dt class="col-6 text-muted font-weight-normal">Session Lifetime</dt>
                                <dd class="col-6 font-weight-bold">
                                    @if ($session_timeout >= 60)
                                        {{ intdiv($session_timeout, 60) }}h {{ $session_timeout % 60 }}m
                                    @else
                                        {{ $session_timeout }} min
                                    @endif
                                </dd>

                                <dt class="col-6 text-muted font-weight-normal">Expire on Close</dt>
                                <dd class="col-6">
                                    @if ($session_expire_on_close)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </dd>
                            </dl>

                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <x-adminlte-button label="Save Session Timeout" type="submit" theme="primary"
                            icon="fas fa-fw fa-save" />
                    </div>
                </form>
            </x-adminlte-card>

        </div>
        {{-- END single column --}}
    </div>
</div>
