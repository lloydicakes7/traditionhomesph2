<div>
    {{-- =================================================================== --}}
    {{-- HEADER: SEARCH + CREATE BUTTON                                       --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 mb-3">
                <h5 class="mb-0 font-weight-bold">Home Page Questions</h5>
                <small class="text-muted">
                    The questions the public home page answers, in the order they are asked.
                </small>
            </div>

            <div class="col-12 col-md-5 col-lg-4 mb-2 mb-md-0">
                <x-adminlte-input name="search" placeholder="Search questions..." wire:model.debounce.500ms="search"
                    igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            @can('create', \App\Models\Faq::class)
                <div class="col-12 col-sm-auto ml-auto">
                    <x-adminlte-button label="New FAQ" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#faqFormModal" class="btn-sm btn-block" />
                </div>
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th style="width:38%;" class="align-middle">Question</th>
                        <th class="align-middle">Answer</th>
                        <th style="width:130px;" class="text-right align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($faqs as $faq)
                        <tr wire:key="faq-row-{{ $faq->id }}">
                            <td class="font-weight-bold align-middle">{{ $faq->question }}</td>
                            <td class="text-muted small align-middle">
                                {{ \Illuminate\Support\Str::limit($faq->answer, 140) }}
                            </td>
                            <td class="text-right align-middle text-nowrap">
                                @include('livewire.admin.hoa-center.faqs.partials.faq-actions', [
                                    'actionKey' => 'Desktop',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-question-circle fa-2x mb-2"></i>
                                <p class="mb-0">
                                    @if ($search !== '')
                                        No questions match your search.
                                    @else
                                        The home page has no questions yet.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE CARDS                                                        --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($faqs as $faq)
            <div class="card mb-2 shadow-sm" wire:key="faq-card-{{ $faq->id }}">
                <div class="card-body py-3">
                    <div class="font-weight-bold mb-1">{{ $faq->question }}</div>

                    <div class="text-muted small mb-3">
                        {{ \Illuminate\Support\Str::limit($faq->answer, 160) }}
                    </div>

                    <div class="float-right">
                        @include('livewire.admin.hoa-center.faqs.partials.faq-actions', [
                            'actionKey' => 'Mobile',
                        ])
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-question-circle fa-2x mb-2"></i>
                    <p class="mb-0">
                        @if ($search !== '')
                            No questions match your search.
                        @else
                            The home page has no questions yet.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($faqs->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $faqs->links() }}
        </div>
    @endif
</div>
