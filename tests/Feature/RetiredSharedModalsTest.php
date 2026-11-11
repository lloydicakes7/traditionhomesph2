<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals\BookingApproveModal;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals\BookingCancelModal;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals\BookingCompleteModal;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals\BookingForfeitDepositModal;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals\BookingMassDeleteModal;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals\BookingRefundDepositModal;
use App\Http\Livewire\Admin\Support\Complaints\Modals\ComplaintMassDeleteModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestMassDeleteModal;
use App\Http\Livewire\Admin\Support\Violations\Modals\ViolationMassDeleteModal;
use App\Http\Livewire\Admin\UserManagement\User\Modals\UserActivityDeleteModal;
use App\Http\Livewire\Admin\UserManagement\User\Modals\UserViewModal;
use App\Http\Livewire\Admin\Settings\CollectionsSettings;
use App\Http\Livewire\Portal\Bookings\BookingList;
use App\Http\Livewire\Portal\Bookings\Modals\BookingDeleteModal as PortalBookingDeleteModal;
use App\Http\Livewire\Portal\HouseholdProperty\Modals\ManageMembersModal;
use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The dialogs that moved off the retired shared components — `confirm-delete-modal`
 * and `confirm-action-modal` — onto the `ModalComponent` + `modal-loader.js`
 * contract.
 *
 * Two things are worth pinning down. First, every converted dialog still carries
 * the skeleton/content markers the loader keys on: a dialog without them opens
 * onto an endless spinner, because `isLazyModal()` never matches. Second, the old
 * shared components are gone and no view renders them any more.
 */
class RetiredSharedModalsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The dialogs that used to be the shared delete/confirm components.
     *
     * @return array<int, class-string>
     */
    private function convertedModalClasses(): array
    {
        return [
            BookingMassDeleteModal::class,
            ComplaintMassDeleteModal::class,
            RequestMassDeleteModal::class,
            ViolationMassDeleteModal::class,
            UserActivityDeleteModal::class,
        ];
    }

    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'modals-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            // The panel turns a deactivated account away, and a freshly-created
            // model carries no column the database filled in for it.
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * The page mounts one dialog per action, each already carrying its skeleton —
     * the loader swaps it for the loaded content on open.
     *
     * @param  array<int, string>  $modalIds
     */
    private function assertPageRendersModals(string $route, array $modalIds): void
    {
        $response = $this->get(route($route));

        $response->assertOk();

        foreach ($modalIds as $id) {
            $response->assertSee('id="' . $id . '"', false);
        }

        // One skeleton per mounted dialog, not one shared between them.
        $this->assertSame(
            count($modalIds),
            substr_count($response->getContent(), 'data-modal-skeleton class='),
            "$route does not render one skeleton per dialog"
        );
    }

    public function test_every_converted_dialog_carries_the_lazy_load_markers(): void
    {
        $this->signIn();

        foreach ($this->convertedModalClasses() as $class) {
            Livewire::test($class)
                ->assertSet('loaded', false)
                // The loader's `isLazyModal()` keys on the marker attribute
                // itself, so the whole attribute is what has to be there.
                ->assertSee('data-modal-skeleton class=', false)
                ->assertSee('data-modal-content class=', false);
        }
    }

    public function test_the_retired_shared_components_are_gone_and_unrendered(): void
    {
        foreach (['confirm-delete-modal', 'confirm-action-modal'] as $component) {
            $this->assertFileNotExists(
                resource_path('views/components/ui/modals/' . $component . '.blade.php'),
                "components/ui/modals/$component.blade.php is still on disk"
            );

            $referencing = [];

            foreach ($this->bladeViews() as $view) {
                if (Str::contains(file_get_contents($view), 'x-ui.modals.' . $component)) {
                    $referencing[] = $view;
                }
            }

            $this->assertSame([], $referencing, "A view still renders x-ui.modals.$component");
        }
    }

    public function test_the_amenities_pages_render_their_dialogs(): void
    {
        $this->signIn();

        // The amenity cards page: view, add/edit and delete, plus the trigger
        // the Add Amenity button carries.
        $this->assertPageRendersModals('amenities.index', [
            'amenityViewModal', 'amenityFormModal', 'amenityDeleteModal',
        ]);
        $this->get(route('amenities.index'))
            ->assertSee('data-target="#amenityFormModal"', false);

        // The reservations table: view, add/edit, one dialog per move in the
        // reservation's life, delete, and the bulk delete its toolbar opens.
        // Every one of them is opened by a Bootstrap trigger — no move spends a
        // Livewire call asking the page to open its confirmation.
        $this->assertPageRendersModals('amenities.bookings', [
            'bookingViewModal', 'bookingFormModal', 'bookingApproveModal',
            'invoiceFormModal', 'recordPaymentModal', 'bookingCompleteModal',
            'bookingRefundDepositModal', 'bookingForfeitDepositModal',
            'bookingCancelModal', 'bookingDeleteModal', 'bookingMassDeleteModal',
        ]);

        // The calendar opens the same reservation dialogs the table does.
        $this->assertPageRendersModals('amenities.calendar', [
            'bookingViewModal', 'bookingFormModal',
        ]);
        $this->get(route('amenities.calendar'))
            ->assertSee('data-target="#bookingFormModal"', false);
    }

    public function test_the_collections_settings_page_renders_the_payment_method_delete_dialog(): void
    {
        $this->signIn();

        PaymentMethod::create(['name' => 'GCash', 'is_active' => true, 'sort_order' => 1]);

        $response = $this->get(route('settings.collections'));

        $response->assertOk();
        // The dialog is mounted with the page, already carrying its skeleton.
        $response->assertSee('id="paymentMethodDeleteModal"', false);
        $response->assertSee('data-modal-skeleton class=', false);

        // The row action that opens it is a Bootstrap trigger carrying the
        // method's id — the payment methods live on the page's second tab.
        Livewire::test(CollectionsSettings::class)
            ->set('activeTab', 'payments')
            ->assertSee('data-target="#paymentMethodDeleteModal"', false);
    }

    public function test_the_activity_log_delete_dialog_is_reached_by_its_own_trigger_and_deletes(): void
    {
        $admin = $this->signIn();

        $activity = AdminActivity::create([
            'admin_id'    => $admin->id,
            'action'      => 'updated',
            'module'      => 'settings',
            'description' => 'Changed a setting',
        ]);

        // The row's trigger is a Bootstrap one now, carrying the log's id.
        Livewire::test(UserViewModal::class)
            ->call('load', $admin->id)
            ->call('switchViewTab', 'activities')
            ->assertSee('data-target="#activityDeleteModal"', false)
            ->assertSee('data-id="' . $activity->id . '"', false);

        // ...and the dialog it opens is the one that deletes.
        Livewire::test(UserActivityDeleteModal::class)
            ->call('load', $activity->id)
            ->assertSet('loaded', true)
            ->assertSee('Changed a setting')
            ->call('delete')
            ->assertDispatchedBrowserEvent('modal-close');

        $this->assertDatabaseMissing('admin_activities', ['id' => $activity->id]);
    }

    public function test_the_portal_member_removal_dialog_is_its_own_and_not_lazy(): void
    {
        // This dialog is a sibling of the loaded "Manage Members" shell and is
        // only ever shown once `confirmRemove()` has the member in hand, so it
        // carries no skeleton — and it no longer listens for the retired
        // component's `show-remove-member-modal` / `close-remove-member-modal`
        // events.
        Livewire::test(ManageMembersModal::class)
            ->assertSee('id="removeHouseholdMemberModal"', false)
            ->assertDontSee('show-remove-member-modal')
            ->assertDontSee('close-remove-member-modal');
    }

    public function test_the_booking_move_dialogs_carry_the_lazy_load_markers(): void
    {
        $this->signIn();

        foreach ([
            BookingApproveModal::class,
            BookingCompleteModal::class,
            BookingRefundDepositModal::class,
            BookingForfeitDepositModal::class,
            BookingCancelModal::class,
            PortalBookingDeleteModal::class,
        ] as $class) {
            Livewire::test($class)
                ->assertSet('loaded', false)
                ->assertSee('data-modal-skeleton class=', false)
                ->assertSee('data-modal-content class=', false);
        }
    }

    public function test_the_portal_booking_deletion_is_its_own_loader_dialog(): void
    {
        // Deleting a request is a dialog on the loader contract: the list's
        // button is a Bootstrap trigger carrying the booking's id, and the
        // dialog checks ownership as it loads and again as it deletes.
        $user = factory(User::class)->create();

        $amenity = Amenity::create([
            'name'        => 'Clubhouse',
            'rate_type'   => 'fixed',
            'rate'        => 1500,
            'is_bookable' => true,
        ]);

        $booking = AmenityBooking::create([
            'amenity_id'     => $amenity->id,
            'user_id'        => $user->id,
            'starts_at'      => now()->addDay()->setTime(8, 0),
            'ends_at'        => now()->addDay()->setTime(12, 0),
            'deposit_amount' => 0,
            'deposit_status' => DepositStatus::NONE,
            'status'         => BookingStatus::PENDING,
        ]);

        Livewire::actingAs($user, 'homeowner')
            ->test(BookingList::class)
            ->assertSee('data-target="#bookingDeleteModal"', false)
            ->assertSee('data-id="' . $booking->id . '"', false);

        Livewire::actingAs($user, 'homeowner')
            ->test(PortalBookingDeleteModal::class)
            ->assertSet('loaded', false)
            ->call('load', $booking->id)
            ->assertSet('loaded', true)
            ->call('delete')
            ->assertEmitted('booking-deleted')
            ->assertDispatchedBrowserEvent('modal-close', ['id' => 'bookingDeleteModal']);

        $this->assertDatabaseMissing('amenity_bookings', ['id' => $booking->id]);
    }

    /**
     * Every Blade view under `resources/views`, so the retired components can be
     * shown to have no renderers left.
     *
     * @return array<int, string>
     */
    private function bladeViews(): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        $views = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && Str::endsWith($file->getFilename(), '.blade.php')) {
                $views[] = $file->getPathname();
            }
        }

        return $views;
    }
}
