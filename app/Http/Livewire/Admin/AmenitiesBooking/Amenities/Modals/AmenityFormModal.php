<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Amenities\Modals;

use App\Actions\Amenities\CreateAmenity;
use App\Actions\Amenities\UpdateAmenity;
use App\Enums\AmenityRateType;
use App\Enums\Weekday;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\Amenity;
use App\Queries\Amenities\AmenityListQuery;
use App\Utility\MoneyHelper;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class AmenityFormModal extends AdminModalComponent
{
    use WithFileUploads;

    public $isEditing = false;

    public $editingAmenityId = null;

    public $form = [
        'name'        => '',
        'rate_type'   => AmenityRateType::FIXED,
        'rate'        => '',
        'is_bookable' => true,
        'description' => '',
    ];

    public $schedule = [];

    public $amenityImage;

    public $existingAmenityImageUrl = null;

    public $removeAmenityImage = false;

    protected $listeners = [
        'openAmenityFormModal'  => 'openCreate',
        'loadAmenityFormModal'  => 'openEdit',
        'closeAmenityFormModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AmenityListQuery */
    protected $amenities;

    /** @var CreateAmenity */
    protected $createAmenity;

    /** @var UpdateAmenity */
    protected $updateAmenity;

    public function boot(
        AmenityListQuery $amenities,
        CreateAmenity $createAmenity,
        UpdateAmenity $updateAmenity
    ) {
        $this->amenities = $amenities;
        $this->createAmenity = $createAmenity;
        $this->updateAmenity = $updateAmenity;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    /**
     * The rules the form is validated against: the amenity's own fields, and
     * one schedule rule per day the amenity is still available on — a day
     * switched off takes no hours, so nothing is asked of it.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'form.name'        => ['required', 'string', 'max:150'],
            'form.rate_type'   => ['required', Rule::in(AmenityRateType::values())],
            'form.rate'        => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'form.is_bookable' => ['boolean'],
            'form.description' => ['nullable', 'string', 'max:2000'],
            'amenityImage'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'schedule'         => ['array'],
            'schedule.*.enabled' => ['boolean'],
        ];

        foreach (Weekday::cases() as $day) {
            if (! ($this->schedule[$day->value]['enabled'] ?? true)) {
                continue;
            }

            $key = 'schedule.' . $day->value;

            $rules[$key . '.opens_at']  = ['nullable', 'date_format:H:i'];
            $rules[$key . '.closes_at'] = [
                'nullable', 'date_format:H:i',
                'required_with:' . $key . '.opens_at',
                'after:' . $key . '.opens_at',
            ];
        }

        return $rules;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Amenity::class);

        $this->isEditing = false;
        $this->editingAmenityId = null;
        $this->form = [
            'name'        => '',
            'rate_type'   => AmenityRateType::FIXED,
            'rate'        => '',
            'is_bookable' => true,
            'description' => '',
        ];
        $this->schedule = Amenity::blankWeek();
        $this->resetImageState();

        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Amenity::class);

        $amenity = $this->amenities->detail($id);

        $this->isEditing = true;
        $this->editingAmenityId = $amenity->id;
        $this->form = [
            'name'        => $amenity->name,
            'rate_type'   => $amenity->rate_type,
            'rate'        => $amenity->rate,
            'is_bookable' => $amenity->is_bookable,
            'description' => $amenity->description,
        ];
        $this->schedule = $amenity->scheduleWeek();
        $this->resetImageState();
        $this->existingAmenityImageUrl = $amenity->image_url;

        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->editingAmenityId = null;
        $this->resetImageState();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', Amenity::class);
        $this->validate();

        if ($this->isEditing) {
            $this->updateAmenity->handle(
                $this->amenities->find($this->editingAmenityId),
                $this->form,
                $this->weeklySchedule(),
                $this->amenityImage,
                $this->removeAmenityImage,
                auth('web')->user()
            );
        } else {
            $this->createAmenity->handle(
                $this->form,
                $this->weeklySchedule(),
                $this->amenityImage,
                auth('web')->user()
            );
        }

        $this->emit('notify', 'success',
            $this->isEditing
                ? 'Amenity updated successfully.'
                : 'Amenity added successfully.');

        $this->emit('amenity-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'amenityFormModal']);
    }

    // =====================================================================
    // Image handling
    // =====================================================================

    public function removeImage(): void
    {
        $this->authorize('update', Amenity::class);

        if ($this->amenityImage) {
            $this->resetImageState();

            return;
        }

        $this->removeAmenityImage = true;
        $this->existingAmenityImageUrl = null;

        $this->emit('notify', 'info', 'The amenity photo will be removed on save.');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * The week as the service writes it: a day switched off is saved with no
     * hours at all, so it comes back as a closed day.
     *
     * @return array<int, array<string, string>>
     */
    private function weeklySchedule(): array
    {
        $week = [];

        foreach (Weekday::cases() as $day) {
            $available = (bool) ($this->schedule[$day->value]['enabled'] ?? true);

            $week[$day->value] = [
                'opens_at'  => $available ? ($this->schedule[$day->value]['opens_at'] ?? '') : '',
                'closes_at' => $available ? ($this->schedule[$day->value]['closes_at'] ?? '') : '',
            ];
        }

        return $week;
    }

    private function resetImageState(): void
    {
        $this->amenityImage = null;
        $this->existingAmenityImageUrl = null;
        $this->removeAmenityImage = false;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $rateType = AmenityRateType::tryFrom($this->form['rate_type'] ?? '');
        $rate     = (float) ($this->form['rate'] ?: 0);

        return view('livewire.admin.amenities-booking.amenities.modals.amenity-form-modal', [
            'rateTypes'       => AmenityRateType::labels(),
            'weekdays'        => Weekday::labels(),
            'ratePreview'     => $rate > 0
                ? MoneyHelper::formatShort($rate) . ($rateType ? $rateType->unitSuffix() : '')
                : 'Free',
            'currencySymbol'  => MoneyHelper::symbol(),
        ]);
    }
}
