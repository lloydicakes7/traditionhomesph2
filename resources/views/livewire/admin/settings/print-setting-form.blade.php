<div>
    {{-- =================================================================== --}}
    {{-- CARD TABS                                                           --}}
    {{-- =================================================================== --}}
    <div class="card card-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'letterhead' ? 'active' : '' }}" href="#"
                    wire:click.prevent="$set('activeTab', 'letterhead')">
                    <i class="fas fa-fw fa-heading"></i> Letterhead
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'footers' ? 'active' : '' }}" href="#"
                    wire:click.prevent="$set('activeTab', 'footers')">
                    <i class="fas fa-fw fa-align-left"></i> Footers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'signatories' ? 'active' : '' }}" href="#"
                    wire:click.prevent="$set('activeTab', 'signatories')">
                    <i class="fas fa-fw fa-signature"></i> Signatories
                </a>
            </li>
        </ul>
    </div>

    <form wire:submit.prevent="save">
        {{-- ============================================================= --}}
        {{-- TAB 1 — LETTERHEAD                                            --}}
        {{-- ============================================================= --}}
        @if ($activeTab === 'letterhead')
            <div wire:key="tab-letterhead">
                <x-adminlte-card title="Letterhead" icon="fas fa-fw fa-heading">
                    <div class="row">
                        @php($sides = [
                            'left'  => ['label' => 'Logo (left)',  'upload' => 'leftImage',  'path' => 'leftImagePath',  'preview' => 'leftImagePreview'],
                            'right' => ['label' => 'Hope Trece (right)', 'upload' => 'rightImage', 'path' => 'rightImagePath', 'preview' => 'rightImagePreview'],
                        ])
                        @foreach ($sides as $side => $slot)
                            @php($upload = $slot['upload'])
                            @php($path = $slot['path'])
                            @php($width = $side === 'left' ? 'leftWidth' : 'rightWidth')
                            @php($show = $side === 'left' ? 'leftShow' : 'rightShow')
                            @php($preview = $this->{$slot['preview']}())
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 mb-3 h-100">
                                    <label class="font-weight-bold d-block mb-2">{{ $slot['label'] }}</label>

                                    <div class="text-center mb-2" style="min-height:70px;">
                                        @if ($preview)
                                            <img src="{{ $preview }}" class="img-thumbnail" style="max-height:64px;" alt="">
                                        @else
                                            <span class="text-muted small d-inline-block pt-4">No image chosen</span>
                                        @endif
                                    </div>

                                    <x-adminlte-input-file name="{{ $upload }}" label="Choose image"
                                        accept="{{ $imageAccept }}"
                                        placeholder="{{ $allowedImages }}, max 1 MB" wire:model="{{ $upload }}"
                                        legend="Browse" igroupSize="sm">
                                        @if (${$path} !== '')
                                            <x-slot name="prependSlot">
                                                <x-adminlte-button type="button" theme="danger"
                                                    icon="fas fa-fw fa-trash-alt"
                                                    wire:click="removeImage('{{ $side }}')"
                                                    wire:loading.attr="disabled" />
                                            </x-slot>
                                        @endif
                                    </x-adminlte-input-file>
                                    <div wire:loading wire:target="{{ $upload }}" class="text-info small mt-1">
                                        <i class="fas fa-spinner fa-spin"></i> Uploading…
                                    </div>

                                    <div class="form-row align-items-end mt-2">
                                        <div class="col-6">
                                            <label class="small mb-1" for="print-{{ $side }}-width">
                                                Width ({{ $minWidth }}–{{ $maxWidth }} mm)
                                            </label>
                                            <input type="number" min="{{ $minWidth }}"
                                                max="{{ $maxWidth }}" step="1"
                                                id="print-{{ $side }}-width" class="form-control form-control-sm"
                                                wire:model.defer="{{ $width }}">
                                            @error($width)<span class="text-danger small d-block">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="col-6">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="print-{{ $side }}-show"
                                                    wire:model.defer="{{ $show }}">
                                                <label class="custom-control-label small" for="print-{{ $side }}-show">Show</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-12">
                            <label class="mb-1 mt-3">Centered Header Text</label>
                            <div wire:ignore wire:key="print-header-editor">
                                <div id="printHeaderEditor" data-rich-text="printHeaderHtml"
                                    data-rich-text-placeholder="Association name, address, contact…">{!! $letterhead['header_html'] !!}</div>
                            </div>
                            <textarea id="printHeaderHtml" class="d-none" wire:model.defer="headerHtml"></textarea>
                            @error('headerHtml')<span class="text-danger small d-block">{{ $message }}</span>@enderror

                            <div class="custom-control custom-switch mt-3">
                                <input type="checkbox" class="custom-control-input" id="printDivider" wire:model.defer="divider">
                                <label class="custom-control-label" for="printDivider">
                                    Show Ruler
                                    <br><small>Display a line rule under the header</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </x-adminlte-card>
            </div>
        @endif

        {{-- ============================================================= --}}
        {{-- TAB 2 — FOOTERS                                               --}}
        {{-- ============================================================= --}}
        @if ($activeTab === 'footers')
            <div wire:key="tab-footers">
                <x-adminlte-card title="Footer" icon="fas fa-fw fa-align-left">
                    <div class="row">
                        <div class="col-12 col-md-7">
                            <label class="mb-1">Footer Text</label>
                            <div wire:ignore wire:key="print-footer-editor">
                                <div id="printFooterEditor" data-rich-text="printFooterText"
                                    data-rich-text-placeholder="Footer note — office hours, contact…">{!! $footerText !!}</div>
                            </div>
                            <textarea id="printFooterText" class="d-none" wire:model.defer="footerText"></textarea>
                            @error('footerText')<span class="text-danger small d-block">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-12 col-md-5">
                            <div class="border rounded p-3">
                                <label class="font-weight-bold d-block mb-2">Footer Brand Image</label>

                                <div class="text-center mb-2" style="min-height:70px;">
                                    @if ($this->footerImagePreview())
                                        <img src="{{ $this->footerImagePreview() }}" class="img-thumbnail" style="max-height:64px;" alt="">
                                    @else
                                        <span class="text-muted small d-inline-block pt-4">No image chosen</span>
                                    @endif
                                </div>

                                <x-adminlte-input-file name="footerImage" label="Choose image"
                                    accept="{{ $imageAccept }}"
                                    placeholder="{{ $allowedImages }}, max 1 MB" wire:model="footerImage"
                                    legend="Browse" igroupSize="sm">
                                    @if ($footerImagePath !== '')
                                        <x-slot name="prependSlot">
                                            <x-adminlte-button type="button" theme="danger"
                                                icon="fas fa-fw fa-trash-alt"
                                                wire:click="removeImage('footer')"
                                                wire:loading.attr="disabled" />
                                        </x-slot>
                                    @endif
                                </x-adminlte-input-file>
                                <div wire:loading wire:target="footerImage" class="text-info small mt-1">
                                    <i class="fas fa-spinner fa-spin"></i> Uploading…
                                </div>

                                <div class="form-row align-items-end mt-2">
                                    <div class="col-6">
                                        <label class="small mb-1" for="print-footer-width">
                                            Width ({{ $minFooterWidth }}–{{ $maxFooterWidth }} mm)
                                        </label>
                                        <input type="number" min="{{ $minFooterWidth }}"
                                            max="{{ $maxFooterWidth }}" step="1"
                                            id="print-footer-width" class="form-control form-control-sm"
                                            wire:model.defer="footerWidth">
                                        @error('footerWidth')<span class="text-danger small d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-6">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="print-footer-show"
                                                wire:model.defer="footerShow">
                                            <label class="custom-control-label small" for="print-footer-show">Show</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 mt-3">
                            <x-adminlte-select name="paper" label="Paper" wire:model.defer="paper" igroup-size="sm">
                                <option value="a4">A4</option>
                                <option value="letter">Letter</option>
                                <option value="legal">Legal</option>
                            </x-adminlte-select>
                        </div>
                    </div>
                </x-adminlte-card>
            </div>
        @endif

        {{-- ============================================================= --}}
        {{-- TAB 3 — SIGNATORIES                                           --}}
        {{-- ============================================================= --}}
        @if ($activeTab === 'signatories')
            <div wire:key="tab-signatories">
                <x-adminlte-card title="Signatories" icon="fas fa-fw fa-signature">
                    <p class="text-muted small">
                        The printed names under the signature lines every document ends with.
                    </p>

                    <div class="row">
                        <div class="col-12 col-md-3">
                            <x-adminlte-input name="presidentName" label="HOA President" wire:model.defer="presidentName" igroup-size="sm" />
                        </div>
                        <div class="col-12 col-md-3">
                            <x-adminlte-input name="secretaryName" label="HOA Secretary" wire:model.defer="secretaryName" igroup-size="sm" />
                        </div>
                        <div class="col-12 col-md-3">
                            <x-adminlte-input name="treasurerName" label="HOA Treasurer" wire:model.defer="treasurerName" igroup-size="sm" />
                        </div>
                        <div class="col-12 col-md-3">
                            <x-adminlte-input name="grievanceName" label="Grievance Committee" wire:model.defer="grievanceName" igroup-size="sm" />
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 col-md-4">
                            <x-adminlte-input name="membershipChecker1" label="Membership Checker 1" wire:model.defer="membershipChecker1" igroup-size="sm" />
                        </div>
                        <div class="col-12 col-md-4">
                            <x-adminlte-input name="membershipChecker2" label="Membership Checker 2" wire:model.defer="membershipChecker2" igroup-size="sm" />
                        </div>
                    </div>
                </x-adminlte-card>
            </div>
        @endif

        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-4">
            @can('print.export')
                <a href="{{ $sampleUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                    <i class="fas fa-fw fa-file-pdf"></i> Sample PDF
                </a>
            @endcan

            @can('print.settings.update')
                <span>
                    <x-adminlte-button label="Save Print Settings" type="submit" theme="primary"
                        icon="fas fa-fw fa-save" wire:loading.attr="disabled" wire:target="save" />
                    <span wire:loading wire:target="save" class="ml-2 text-info small">
                        <i class="fas fa-spinner fa-spin"></i> Saving…
                    </span>
                </span>
            @endcan
        </div>
    </form>

    @include('livewire.admin.settings.partials.summernote')
</div>
