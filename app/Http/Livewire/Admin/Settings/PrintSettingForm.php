<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Settings;

use App\Actions\Settings\Print\RemovePrintImage;
use App\Actions\Settings\Print\StorePrintImage;
use App\Actions\Settings\Print\UpdatePrintSettings;
use App\Enums\PrintDocument;
use App\Http\Livewire\AdminComponent;
use App\Queries\Settings\PrintSettingsQuery;
use App\Utility\StorageHelper;
use Livewire\WithFileUploads;

/**
 * The print settings page — the shared letterhead, footer, paper and
 * signatories every printable document draws.
 *
 * Validation happens here, at the one point the settings are written: the
 * widths are the range the documents can lay out, the images are raster
 * (no SVG) and the header and footer text are rich, so the write sanitizes
 * them down to the tags and style properties a letterhead may carry before
 * they are stored (see UpdatePrintSettings).
 *
 * The images are their own previews: a freshly uploaded file is shown through
 * its Livewire temporary URL, a stored one through the private disk's data
 * URI, so nothing about the preview depends on a browser-side file reader that
 * the next render would throw away.
 */
class PrintSettingForm extends AdminComponent
{
    use WithFileUploads;

    /**
     * The image slots the letterhead and footer are made of, and where each
     * keeps the file it uploads and the path it stored.
     *
     * The header's two logos and the brand mark under the footer line are the
     * same thing to this page — one image, one width, one show/hide — so they
     * are saved, replaced and removed by one loop rather than three copies of
     * the same code.
     *
     * @var array<string, array{upload: string, path: string}>
     */
    const IMAGE_SLOTS = [
        'left'   => ['upload' => 'leftImage', 'path' => 'leftImagePath'],
        'right'  => ['upload' => 'rightImage', 'path' => 'rightImagePath'],
        'footer' => ['upload' => 'footerImage', 'path' => 'footerImagePath'],
    ];

    /** @var string The tab the page is showing: letterhead, footers or signatories */
    public $activeTab = 'letterhead';

    /** @var string The centered header text (rich) */
    public $headerHtml = '';

    /** @var mixed Uploaded left slot image */
    public $leftImage;

    /** @var mixed Uploaded right slot image */
    public $rightImage;

    /** @var string The stored left image path */
    public $leftImagePath = '';

    /** @var string The stored right image path */
    public $rightImagePath = '';

    /** @var mixed Left image width, in millimetres */
    public $leftWidth = PrintSettingsQuery::DEFAULT_WIDTH;

    /** @var mixed Right image width, in millimetres */
    public $rightWidth = PrintSettingsQuery::DEFAULT_WIDTH;

    /** @var bool Whether the left slot shows */
    public $leftShow = true;

    /** @var bool Whether the right slot shows */
    public $rightShow = true;

    /** @var bool Whether the 2px rule under the header shows */
    public $divider = true;

    /** @var string The line printed under every document (rich) */
    public $footerText = '';

    /** @var mixed Uploaded footer brand image */
    public $footerImage;

    /** @var string The stored footer brand image path */
    public $footerImagePath = '';

    /** @var mixed Footer brand image width, in millimetres */
    public $footerWidth = PrintSettingsQuery::DEFAULT_FOOTER_WIDTH;

    /** @var bool Whether the footer text and brand image show */
    public $footerShow = true;

    /** @var string a4, letter or legal */
    public $paper = 'a4';

    /** @var string The President's printed name */
    public $presidentName = '';

    /** @var string The Secretary's printed name */
    public $secretaryName = '';

    /** @var string The Treasurer's printed name */
    public $treasurerName = '';

    /** @var string The grievance committee's printed name (the salaysay) */
    public $grievanceName = '';

    /** @var string The first membership checker's printed name */
    public $membershipChecker1 = '';

    /** @var string The second membership checker's printed name */
    public $membershipChecker2 = '';

    /** @var PrintSettingsQuery */
    private $query;

    /** @var UpdatePrintSettings */
    private $updateSettings;

    /** @var StorePrintImage */
    private $storeImage;

    /** @var RemovePrintImage */
    private $removePrintImage;

    public function boot(
        PrintSettingsQuery $query,
        UpdatePrintSettings $updateSettings,
        StorePrintImage $storeImage,
        RemovePrintImage $removePrintImage
    ) {
        $this->query = $query;
        $this->updateSettings = $updateSettings;
        $this->storeImage = $storeImage;
        $this->removePrintImage = $removePrintImage;
    }

    public function mount()
    {
        $settings = $this->query->form();

        $this->headerHtml = $settings['header_html'];
        $this->leftImagePath = $settings['left_image_path'];
        $this->rightImagePath = $settings['right_image_path'];
        $this->leftWidth = $settings['left_image_width'];
        $this->rightWidth = $settings['right_image_width'];
        $this->leftShow = $settings['left_show'];
        $this->rightShow = $settings['right_show'];
        $this->divider = $settings['divider'];
        $this->footerText = $settings['footer_text'];
        $this->footerImagePath = $settings['footer_image_path'];
        $this->footerWidth = $settings['footer_image_width'];
        $this->footerShow = $settings['footer_show'];
        $this->paper = $settings['paper'];
        $this->presidentName = $settings['president_name'];
        $this->secretaryName = $settings['secretary_name'];
        $this->treasurerName = $settings['treasurer_name'];
        $this->grievanceName = $settings['grievance_name'];
        $this->membershipChecker1 = $settings['membership_checker_1'];
        $this->membershipChecker2 = $settings['membership_checker_2'];
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'headerHtml'    => ['nullable', 'string'],
            'leftImage'     => ['nullable', 'image', 'mimes:' . PrintSettingsQuery::imageMimes(), 'max:1024'],
            'rightImage'    => ['nullable', 'image', 'mimes:' . PrintSettingsQuery::imageMimes(), 'max:1024'],
            'leftWidth'     => ['required', 'numeric', 'between:' . PrintSettingsQuery::MIN_WIDTH . ',' . PrintSettingsQuery::MAX_WIDTH],
            'rightWidth'    => ['required', 'numeric', 'between:' . PrintSettingsQuery::MIN_WIDTH . ',' . PrintSettingsQuery::MAX_WIDTH],
            'leftShow'      => ['boolean'],
            'rightShow'     => ['boolean'],
            'divider'       => ['boolean'],
            'footerText'    => ['nullable', 'string', 'max:2000'],
            'footerImage'   => ['nullable', 'image', 'mimes:' . PrintSettingsQuery::imageMimes(), 'max:1024'],
            'footerWidth'   => ['required', 'numeric', 'between:' . PrintSettingsQuery::MIN_FOOTER_WIDTH . ',' . PrintSettingsQuery::MAX_FOOTER_WIDTH],
            'footerShow'    => ['boolean'],
            'paper'         => ['required', 'in:' . implode(',', PrintSettingsQuery::PAPERS)],
            'presidentName' => ['nullable', 'string', 'max:255'],
            'secretaryName' => ['nullable', 'string', 'max:255'],
            'treasurerName' => ['nullable', 'string', 'max:255'],
            'grievanceName' => ['nullable', 'string', 'max:255'],
            'membershipChecker1' => ['nullable', 'string', 'max:255'],
            'membershipChecker2' => ['nullable', 'string', 'max:255'],
        ];
    }

    // =====================================================================
    // Actions
    // =====================================================================

    /**
     * Save the letterhead, footer and signatories.
     */
    public function save(): void
    {
        $this->authorize('print.settings.update');

        $this->validate();

        $this->storeUploads();

        $this->updateSettings->handle([
            'header_html'          => $this->headerHtml,
            'left_image_width'     => $this->leftWidth,
            'right_image_width'    => $this->rightWidth,
            'left_show'            => $this->leftShow,
            'right_show'           => $this->rightShow,
            'divider'              => $this->divider,
            'footer_text'          => $this->footerText,
            'footer_image_width'   => $this->footerWidth,
            'footer_show'          => $this->footerShow,
            'paper'                => $this->paper,
            'president_name'       => $this->presidentName,
            'secretary_name'       => $this->secretaryName,
            'treasurer_name'       => $this->treasurerName,
            'grievance_name'       => $this->grievanceName,
            'membership_checker_1' => $this->membershipChecker1,
            'membership_checker_2' => $this->membershipChecker2,
        ], auth('web')->user());

        $this->emit('notify', 'success', 'Print settings saved. Every document now prints them.');
    }

    /**
     * Store every image that was replaced on this save, and forget the upload
     * so the preview falls back to the file that was just stored.
     */
    private function storeUploads(): void
    {
        foreach (self::IMAGE_SLOTS as $side => $slot) {
            $upload = $this->{$slot['upload']};

            if (! $upload) {
                continue;
            }

            $this->{$slot['path']} = $this->storeImage->handle($side, $upload, auth('web')->user());
            $this->{$slot['upload']} = null;
        }
    }

    /**
     * Remove one image: the left or right logo of the letterhead, or the brand
     * mark under the footer line.
     *
     * @param  string  $side  "left", "right" or "footer"
     */
    public function removeImage(string $side): void
    {
        $this->authorize('print.settings.update');

        if (! array_key_exists($side, self::IMAGE_SLOTS)) {
            return;
        }

        $this->removePrintImage->handle($side, auth('web')->user());

        $slot = self::IMAGE_SLOTS[$side];

        $this->{$slot['path']} = '';
        $this->{$slot['upload']} = null;

        $this->emit('notify', 'info', 'Image removed.');
    }

    // =====================================================================
    // Preview
    // =====================================================================

    /**
     * What the left slot should show: the file just chosen, or the stored one.
     */
    public function leftImagePreview(): ?string
    {
        return $this->imagePreview($this->leftImage, $this->leftImagePath)
            ?? PrintSettingsQuery::seededImageUrl('left');
    }

    /**
     * What the right slot should show: the file just chosen, or the stored one.
     */
    public function rightImagePreview(): ?string
    {
        return $this->imagePreview($this->rightImage, $this->rightImagePath)
            ?? PrintSettingsQuery::seededImageUrl('right');
    }

    /**
     * What the footer's brand image should show: the file just chosen, or the
     * stored one.
     */
    public function footerImagePreview(): ?string
    {
        return $this->imagePreview($this->footerImage, $this->footerImagePath)
            ?? PrintSettingsQuery::seededImageUrl('footer');
    }

    /**
     * A slot's image as something an `<img>` can point at — the temporary URL
     * of the file just uploaded, the stored file's data URI, or nothing.
     *
     * @param  mixed   $upload
     */
    private function imagePreview($upload, string $path): ?string
    {
        if ($upload) {
            try {
                return $upload->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }

        return $path !== '' ? StorageHelper::privateDataUri($path) : null;
    }

    public function render()
    {
        return view('livewire.admin.settings.print-setting-form', [
            'letterhead'     => $this->query->letterhead(),
            'sampleUrl'      => route('print.pdf', ['document' => PrintDocument::CERTIFICATION]),
            'allowedImages'  => $this->query->allowedImageFormats(),
            'imageAccept'    => PrintSettingsQuery::imageAccept(),
            'minWidth'       => PrintSettingsQuery::MIN_WIDTH,
            'maxWidth'       => PrintSettingsQuery::MAX_WIDTH,
            'minFooterWidth' => PrintSettingsQuery::MIN_FOOTER_WIDTH,
            'maxFooterWidth' => PrintSettingsQuery::MAX_FOOTER_WIDTH,
        ]);
    }
}
