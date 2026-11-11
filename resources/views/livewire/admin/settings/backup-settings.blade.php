<div>
    {{-- =================================================================== --}}
    {{-- HEADER WITH CREATE BUTTON                                          --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="d-flex justify-content-end align-items-center flex-wrap" style="gap:0.5rem;">
            @can('update', \App\Models\Setting::class)
                <x-adminlte-button
                    label="{{ $creating ? 'Creating Backup...' : 'Create Backup' }}"
                    theme="primary"
                    icon="fas fa-fw fa-download"
                    wire:click="createBackup"
                    wire:loading.attr="disabled"
                    class="btn-sm"
                />
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Backup File</th>
                        <th>Size</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold">{{ $backup['name'] }}</div>
                            </td>
                            <td class="align-middle">{{ $backup['size'] }}</td>
                            <td class="align-middle">{{ $backup['created_at'] }}</td>
                            <td class="text-right align-middle">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('download', \App\Models\Setting::class)
                                        <a href="{{ route('settings.backup.download', ['filename' => $backup['name']]) }}"
                                        class="btn btn-success btn-xs"
                                        title="Download backup">
                                            <i class="fas fa-fw fa-download"></i>
                                        </a>
                                    @endcan
                                    @can('update', \App\Models\Setting::class)
                                        <button type="button"
                                                class="btn btn-info btn-xs"
                                                title="Restore from this backup"
                                                wire:click="restoreBackup('{{ $backup['name'] }}')"
                                                wire:loading.attr="disabled">
                                            <i class="fas fa-fw fa-redo"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-danger btn-xs"
                                                title="Delete backup"
                                                wire:click="deleteBackup('{{ $backup['name'] }}')"
                                                wire:loading.attr="disabled">
                                            <i class="fas fa-fw fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-database fa-2x mb-2"></i>
                                <p class="mb-0">No backups have been created yet.</p>
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
        @forelse ($backups as $backup)
            <div class="card mb-2 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="font-weight-bold text-break">{{ $backup['name'] }}</span>
                    </div>
                    <div class="text-muted small mb-3">
                        <div><i class="fas fa-fw fa-weight-hanging"></i> {{ $backup['size'] }}</div>
                        <div><i class="fas fa-fw fa-calendar-alt"></i> {{ $backup['created_at'] }}</div>
                    </div>
                    <div class="btn-group btn-group-sm d-flex" role="group">
                        <a href="{{ route('settings.backup.download', ['filename' => $backup['name']]) }}"
                           class="btn btn-success btn-xs flex-fill"
                           title="Download backup">
                            <i class="fas fa-fw fa-download"></i>
                        </a>
                        <button type="button"
                                class="btn btn-info btn-xs flex-fill"
                                title="Restore from this backup"
                                wire:click="restoreBackup('{{ $backup['name'] }}')"
                                wire:loading.attr="disabled">
                            <i class="fas fa-fw fa-redo"></i>
                        </button>
                        <button type="button"
                                class="btn btn-danger btn-xs flex-fill"
                                title="Delete backup"
                                wire:click="deleteBackup('{{ $backup['name'] }}')"
                                wire:loading.attr="disabled">
                            <i class="fas fa-fw fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-database fa-2x mb-2"></i>
                    <p class="mb-0">No backups have been created yet.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
