<div>
    <div class="row">
        <div class="col-12 col-lg-8">

            <x-adminlte-card title="Registration" icon="fas fa-fw fa-user-plus">
                <form wire:submit.prevent="save">

                    <p class="text-muted small mb-3">
                        Controls which registration screens are open and what happens to a new account.
                        A closed screen answers as not found; hiding a link is never the only guard.
                    </p>

                    {{-- ===== Admin registration ===== --}}
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="allowAdminRegistration"
                            wire:model="allow_admin_registration" value="1">
                        <label class="custom-control-label font-weight-bold" for="allowAdminRegistration">
                            Allow admin registration
                        </label>
                    </div>
                    <p class="text-muted small ml-4 mb-3">
                        Opens <code>/admin/register</code> for officers. Every officer registration still waits
                        for an Administrator to activate it.
                    </p>

                    {{-- ===== Resident registration ===== --}}
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="allowResidentRegistration"
                            wire:model="allow_resident_registration" value="1">
                        <label class="custom-control-label font-weight-bold" for="allowResidentRegistration">
                            Allow resident registration
                        </label>
                    </div>
                    <p class="text-muted small ml-4 mb-3">
                        Opens <code>/portal/register</code> and the Register links on the public site.
                        When off, both are closed.
                    </p>

                    {{-- ===== Auto accept ===== --}}
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="autoAcceptRegistration"
                            wire:model="auto_accept_registration" value="1">
                        <label class="custom-control-label font-weight-bold" for="autoAcceptRegistration">
                            Automatically accept registration
                        </label>
                    </div>
                    <p class="text-muted small ml-4 mb-3">
                        When on, a new resident account is active at once. When off, the resident sees the
                        pending-approval notice and cannot sign in until an officer activates the account
                        under <strong>Homeowners</strong>.
                    </p>

                    <div class="text-right mt-3">
                        <x-adminlte-button label="Save Registration Settings" type="submit" theme="primary"
                            icon="fas fa-fw fa-save" />
                    </div>
                </form>
            </x-adminlte-card>

        </div>
    </div>
</div>
