<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_authentication_and_unauthenticated_access(): void
    {
        $this->seed();

        // Unauthenticated access returns 401
        $this->getJson('/api/user')->assertStatus(401)->assertJson(['success' => false]);
        $this->getJson('/api/shipments')->assertStatus(401)->assertJson(['success' => false]);

        // Invalid login returns 401
        $this->postJson('/api/login', [
            'email' => 'admin@cargotrace.test',
            'password' => 'wrong-password',
        ])->assertStatus(401)->assertJson(['success' => false]);

        // Valid login returns 200 with Bearer token
        $loginRes = $this->postJson('/api/login', [
            'email' => 'admin@cargotrace.test',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure([
            'success',
            'message',
            'data' => ['user', 'token', 'token_type'],
        ]);

        $token = $loginRes->json('data.token');

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@cargotrace.test');

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk();

        // Register creates customer role and returns 201
        $this->postJson('/api/register', [
            'name' => 'New Customer',
            'email' => 'newcustomer@cargotrace.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201)->assertJsonPath('data.user.role', 'customer');
    }

    public function test_api_role_authorization_and_customer_isolation(): void
    {
        $this->seed();

        $operator = User::where('role', 'operator')->first();
        $customerA = User::where('role', 'customer')->first();
        $customerB = User::create([
            'name' => 'Customer B',
            'email' => 'customerb@cargotrace.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $carrier = Carrier::first();
        $locations = Location::take(2)->get();

        $tokenA = $customerA->createToken('test')->plainTextToken;
        $tokenB = $customerB->createToken('test')->plainTextToken;
        $tokenOp = $operator->createToken('test')->plainTextToken;

        $shipmentRes = $this->withToken($tokenOp)->postJson('/api/shipments', [
            'customer_id' => $customerA->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $locations[0]->id,
            'destination_location_id' => $locations[1]->id,
            'currency_code' => 'USD',
        ])->assertStatus(201);

        $shipmentId = $shipmentRes->json('data.id');

        // Customer A can view own shipment
        $this->app['auth']->forgetGuards();
        $this->withToken($tokenA)->getJson("/api/shipments/{$shipmentId}")->assertOk();

        // Customer B CANNOT view Customer A's shipment (403)
        $this->app['auth']->forgetGuards();
        $this->withToken($tokenB)->getJson("/api/shipments/{$shipmentId}")->assertStatus(403);
        $this->withToken($tokenB)->getJson("/api/shipments/{$shipmentId}/tracking")->assertStatus(403);
        $this->withToken($tokenB)->getJson("/api/shipments/{$shipmentId}/exceptions")->assertStatus(403);

        // Customer CANNOT create/update shipments or resources (403)
        $this->app['auth']->forgetGuards();
        $this->withToken($tokenA)->postJson('/api/shipments', [])->assertStatus(403);
        $this->withToken($tokenA)->patchJson("/api/shipments/{$shipmentId}", ['cargo_type' => 'Test'])->assertStatus(403);
        $this->withToken($tokenA)->postJson('/api/carriers', [])->assertStatus(403);

        // Operator CANNOT create carriers or delete shipments (403)
        $this->app['auth']->forgetGuards();
        $this->withToken($tokenOp)->postJson('/api/carriers', ['name' => 'X', 'code' => 'XX'])->assertStatus(403);
        $this->withToken($tokenOp)->deleteJson("/api/shipments/{$shipmentId}")->assertStatus(403);

        // Non-existent shipment returns clean 404 without stack trace
        $this->withToken($tokenOp)->getJson('/api/shipments/999999')
            ->assertStatus(404)
            ->assertExactJson([
                'success' => false,
                'message' => 'Resource not found.',
            ]);
    }

    public function test_api_shipment_status_transition_validation_returns_422(): void
    {
        $this->seed();

        $operator = User::where('role', 'operator')->first();
        $customer = User::where('role', 'customer')->first();
        $carrier = Carrier::first();
        $locations = Location::take(2)->get();

        $tokenOp = $operator->createToken('test')->plainTextToken;

        // Create shipment via API (201)
        $createRes = $this->withToken($tokenOp)->postJson('/api/shipments', [
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $locations[0]->id,
            'destination_location_id' => $locations[1]->id,
            'currency_code' => 'USD',
            'shipping_cost' => 1200,
        ])->assertStatus(201);

        $shipmentId = $createRes->json('data.id');

        // Invalid jump CREATED -> IN_TRANSIT returns 422
        $this->withToken($tokenOp)->patchJson("/api/shipments/{$shipmentId}", [
            'current_status' => 'IN_TRANSIT',
        ])->assertStatus(422)->assertJsonPath('success', false);

        // Valid sequential transition via tracking endpoint: CREATED -> BOOKED (201)
        $this->withToken($tokenOp)->postJson("/api/shipments/{$shipmentId}/tracking", [
            'status' => 'BOOKED',
            'location_name' => 'Port of Tanjung Priok',
        ])->assertStatus(201)->assertJsonPath('data.status', 'BOOKED');
    }
}

