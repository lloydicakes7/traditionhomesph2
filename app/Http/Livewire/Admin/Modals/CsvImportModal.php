<?php

namespace App\Http\Livewire\Admin\Modals;

use App\Http\Livewire\AdminModalComponent;
use Livewire\WithFileUploads;

/**
 * The shared "Import from CSV" dialog.
 *
 * The opening page configures it with `loadCsvImportModal` (title and the
 * columns it expects) and gets the uploaded file's path back as
 * `csv-import-confirmed`. The dialog checks the upload before handing it over
 * and closes itself afterwards.
 */
class CsvImportModal extends AdminModalComponent
{
    use WithFileUploads;

    /** @var string Dialog title, set by the opener */
    public $title = 'Import from CSV';

    /** @var array<int, string> Columns the opening page's sheet must have */
    public $columns = [];

    /** @var mixed Uploaded CSV file */
    public $importFile;

    /** @var array<string, string> */
    protected $listeners = [
        'loadCsvImportModal'  => 'load',
        'closeCsvImportModal' => 'closeModal',
    ];

    // ===== Rules =====

    protected function rules(): array
    {
        return [
            'importFile' => ['required', 'file', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'importFile.required' => 'Choose the CSV file you want to import.',
            'importFile.file'     => 'The import must be an uploaded file.',
            'importFile.max'      => 'The file is larger than 2 MB. Split the sheet into smaller files.',
        ];
    }

    // ===== Lifecycle =====

    /**
     * Show the dialog configured by the opening page.
     *
     * @param  array{title?: string, columns?: array<int, string>}  $config
     */
    public function load(array $config = []): void
    {
        $this->title      = $config['title'] ?? $this->title;
        $this->columns    = $config['columns'] ?? [];
        $this->importFile = null;

        $this->resetValidation();

        $this->markLoaded();

        $this->dispatchBrowserEvent('modal-open', ['id' => 'csvImportModal']);
    }

    protected function resetModal(): void
    {
        $this->importFile = null;

        $this->resetValidation();

        parent::resetModal();
    }

    // ===== Actions =====

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $validator = validator(
            ['importFile' => $this->importFile],
            $this->rules(),
            $this->messages()
        );

        if ($validator->fails()) {
            $this->failUpload($validator->errors()->first('importFile'));

            return;
        }

        if (! in_array(strtolower($this->importFile->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            $this->failUpload('Choose a comma-separated file saved as .csv or .txt.');

            return;
        }

        $path = $this->importFile->getRealPath();

        $this->importFile = null;

        $this->emit('csv-import-confirmed', $path);

        $this->dispatchBrowserEvent('modal-close', ['id' => 'csvImportModal']);
    }

    public function render()
    {
        return view('livewire.admin.modals.csv-import-modal');
    }

    // ===== Helpers =====

    /**
     * Show the reason under the upload and settle the progress notice the
     * Import button opened.
     */
    private function failUpload(string $message): void
    {
        $this->addError('importFile', $message);

        $this->emit('notify', 'error', $message);
    }
}
