<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CargoTraceFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_all_admin_pages_render_without_errors(): void
    {
        $this->seed();

        $this->get('/login')->assertOk()->assertSee('CargoTrace');

        $admin = User::where('role', 'admin')->first();
        $customer = User::where('role', 'customer')->first();
        $carrier = Carrier::first();
        $locations = Location::take(2)->get();

        $this->actingAs($admin);

        $shipment = app(ShipmentService::class)->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $locations[0]->id,
            'destination_location_id' => $locations[1]->id,
            'cargo_type' => 'Medical',
            'shipping_cost' => 2500,
            'currency_code' => 'USD',
        ]);

        $this->get('/admin/dashboard')->assertOk()->assertSee('Delayed')->assertDontSee('Delayd');
        $this->get('/shipments')->assertOk()->assertSee($shipment->shipment_number);
        $this->get('/shipments/create')->assertOk();
        $this->get("/shipments/{$shipment->id}")->assertOk()->assertSee('Interactive Route');
        $this->get("/shipments/{$shipment->id}/edit")->assertOk();
        $this->get('/shipments/export/csv')->assertOk();
        $this->get('/tracking')->assertOk();
        $this->get('/exceptions')->assertOk();
        $this->get('/reports')->assertOk();
        $this->get('/notifications')->assertOk();
        $this->get('/admin/carriers')->assertOk();
        $this->get('/admin/locations')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/audit-logs')->assertOk();
    }

    public function test_role_access_boundaries_for_operator_and_customer(): void
    {
        $this->seed();

        $operator = User::where('role', 'operator')->first();
        $customer = User::where('role', 'customer')->first();

        $this->actingAs($operator);
        $this->get('/operator/dashboard')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/audit-logs')->assertForbidden();

        $this->actingAs($customer);
        $this->get('/customer/dashboard')->assertOk();
        $this->get('/shipments/create')->assertForbidden();
        $this->get('/admin/carriers')->assertForbidden();
    }
}

