<?php

namespace App\Http\Livewire\Admin\Settings;

use App\Actions\Settings\Backup\CreateDatabaseBackup;
use App\Actions\Settings\Backup\DeleteDatabaseBackup;
use App\Actions\Settings\Backup\RestoreDatabaseBackup;
use App\Http\Livewire\AdminComponent;
use App\Models\Setting;
use App\Queries\Settings\BackupListQuery;

class BackupSettings extends AdminComponent
{
    public $backups = [];

    public $creating = false;

    /** @var BackupListQuery */
    private $query;

    /** @var CreateDatabaseBackup */
    private $createDatabaseBackup;

    /** @var RestoreDatabaseBackup */
    private $restoreDatabaseBackup;

    /** @var DeleteDatabaseBackup */
    private $deleteDatabaseBackup;

    public function boot(
        BackupListQuery $query,
        CreateDatabaseBackup $createDatabaseBackup,
        RestoreDatabaseBackup $restoreDatabaseBackup,
        DeleteDatabaseBackup $deleteDatabaseBackup
    ) {
        $this->query = $query;
        $this->createDatabaseBackup = $createDatabaseBackup;
        $this->restoreDatabaseBackup = $restoreDatabaseBackup;
        $this->deleteDatabaseBackup = $deleteDatabaseBackup;
    }

    public function mount()
    {
        $this->refreshBackups();
    }

    public function refreshBackups()
    {
        $this->backups = $this->query->all();
    }

    public function createBackup()
    {
        $this->authorize('update', Setting::class);

        $this->creating = true;

        try {
            $filename = $this->createDatabaseBackup->handle(auth('web')->user());
            $this->refreshBackups();
            $this->emit('notify', 'success', 'Database backup created successfully: ' . $filename);
        } catch (\Exception $e) {
            $this->emit('notify', 'error', 'Failed to create backup: ' . $e->getMessage());
        }

        $this->creating = false;
    }

    public function restoreBackup($filename)
    {
        $this->authorize('update', Setting::class);

        try {
            $this->restoreDatabaseBackup->handle($filename, auth('web')->user());
            $this->emit('notify', 'success', 'Database restored successfully from: ' . $filename);
        } catch (\Exception $e) {
            $this->emit('notify', 'error', 'Failed to restore backup: ' . $e->getMessage());
        }
    }

    public function deleteBackup($filename)
    {
        $this->authorize('update', Setting::class);

        try {
            $this->deleteDatabaseBackup->handle($filename, auth('web')->user());
            $this->refreshBackups();
            $this->emit('notify', 'success', 'Backup deleted successfully.');
        } catch (\Exception $e) {
            $this->emit('notify', 'error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $this->authorize('viewAny', Setting::class);

        return view('livewire.admin.settings.backup-settings', [
            'backups' => $this->backups,
        ]);
    }
}
