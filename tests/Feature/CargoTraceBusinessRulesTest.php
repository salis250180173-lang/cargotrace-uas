<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\User;
use App\Services\EtaService;
use App\Services\ExternalApiService;
use App\Services\ShipmentExceptionService;
use App\Services\ShipmentService;
use App\Services\TrackingEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CargoTraceBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipment_strict_status_transition_and_audit_log(): void
    {
        $this->seed();

        $operator = User::where('role', 'operator')->first();
        $customer = User::where('role', 'customer')->first();
        $carrier = Carrier::first();
        $locations = Location::take(2)->get();

        $this->actingAs($operator);

        /** @var ShipmentService $shipmentService */
        $shipmentService = app(ShipmentService::class);

        $shipment = $shipmentService->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $locations[0]->id,
            'destination_location_id' => $locations[1]->id,
            'cargo_type' => 'Electronics',
            'shipping_cost' => 1500,
            'currency_code' => 'USD',
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5)->addHours(10),
        ]);

        $this->assertSame('CREATED', $shipment->current_status);
        $this->assertTrue($shipment->is_delayed);
        $this->assertSame('MEDIUM', $shipment->delay_severity);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATED',
            'entity_type' => 'Shipment',
            'entity_id' => $shipment->id,
        ]);

        // Jumping CREATED -> IN_TRANSIT must fail
        try {
            $shipmentService->updateStatus($shipment, 'IN_TRANSIT');
            $this->fail('Expected ValidationException when skipping status.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('current_status', $e->errors());
        }

        // Valid sequential transitions
        $shipment = $shipmentService->updateStatus($shipment, 'BOOKED');
        $shipment = $shipmentService->updateStatus($shipment, 'PICKED_UP');
        $shipment = $shipmentService->updateStatus($shipment, 'IN_TRANSIT');
        $shipment = $shipmentService->updateStatus($shipment, 'DELIVERED');
        $this->assertSame('DELIVERED', $shipment->current_status);

        // Moving backward from terminal DELIVERED must fail
        $this->expectException(ValidationException::class);
        $shipmentService->updateStatus($shipment, 'IN_TRANSIT');
    }

    public function test_tracking_event_append_only_chronology_and_notifications(): void
    {
        $this->seed();

        $operator = User::where('role', 'operator')->first();
        $customer = User::where('role', 'customer')->first();
        $carrier = Carrier::first();
        $locations = Location::take(2)->get();

        $this->actingAs($operator);

        $shipment = app(ShipmentService::class)->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $locations[0]->id,
            'destination_location_id' => $locations[1]->id,
            'currency_code' => 'USD',
        ]);

        /** @var TrackingEventService $trackingService */
        $trackingService = app(TrackingEventService::class);

        $t1 = now();
        $trackingService->addEvent($shipment, [
            'status' => 'BOOKED',
            'location_name' => 'Jakarta',
            'recorded_at' => $t1,
        ]);

        $this->assertSame('BOOKED', $shipment->fresh()->current_status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'shipment_id' => $shipment->id,
        ]);

        // Earlier timestamp must fail
        $this->expectException(ValidationException::class);
        $trackingService->addEvent($shipment->fresh(), [
            'status' => 'PICKED_UP',
            'location_name' => 'Jakarta',
            'recorded_at' => $t1->copy()->subHour(),
        ]);
    }

    public function test_eta_service_severity_levels(): void
    {
        /** @var EtaService $etaService */
        $etaService = app(EtaService::class);
        $expected = now();

        $onTime = $etaService->calculateDelay($expected, $expected->copy()->subHour());
        $this->assertFalse($onTime['is_delayed']);
        $this->assertSame('NONE', $onTime['delay_severity']);

        $low = $etaService->calculateDelay($expected, $expected->copy()->addHours(4));
        $this->assertTrue($low['is_delayed']);
        $this->assertSame('LOW', $low['delay_severity']);
        $this->assertFalse($low['exceeds_threshold']);

        $medium = $etaService->calculateDelay($expected, $expected->copy()->addHours(14));
        $this->assertTrue($medium['is_delayed']);
        $this->assertSame('MEDIUM', $medium['delay_severity']);
        $this->assertTrue($medium['exceeds_threshold']);

        $high = $etaService->calculateDelay($expected, $expected->copy()->addHours(30));
        $this->assertTrue($high['is_delayed']);
        $this->assertSame('HIGH', $high['delay_severity']);
    }

    public function test_exception_workflow_and_external_api_fallback(): void
    {
        $this->seed();

        $operator = User::where('role', 'operator')->first();
        $customer = User::where('role', 'customer')->first();
        $carrier = Carrier::first();
        $locations = Location::take(2)->get();

        $this->actingAs($operator);

        $shipment = app(ShipmentService::class)->create([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $locations[0]->id,
            'destination_location_id' => $locations[1]->id,
            'currency_code' => 'USD',
        ]);

        /** @var ShipmentExceptionService $exceptionService */
        $exceptionService = app(ShipmentExceptionService::class);
        $exception = $exceptionService->report($shipment, [
            'exception_type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'notes' => 'Menunggu dokumen bea cukai.',
        ]);

        $this->assertSame('OPEN', $exception->status);
        $this->assertNotNull($exception->detected_at);
        $this->assertNull($exception->resolved_at);

        $exception = $exceptionService->updateStatus($exception, 'INVESTIGATING');
        $this->assertSame('INVESTIGATING', $exception->status);

        $exception = $exceptionService->updateStatus($exception, 'RESOLVED');
        $this->assertSame('RESOLVED', $exception->status);
        $this->assertNotNull($exception->resolved_at);

        // ExternalApiService graceful fallback when API keys are empty
        /** @var ExternalApiService $apiService */
        $apiService = app(ExternalApiService::class);
        $weather = $apiService->getWeather(-6.10, 106.86, 'Jakarta');
        $this->assertFalse($weather['available']);

        $fx = $apiService->convertCurrency(100, 'USD', 'IDR');
        $this->assertFalse($fx['available']);

        // Same currency conversion works offline
        $sameFx = $apiService->convertCurrency(100, 'USD', 'USD');
        $this->assertTrue($sameFx['available']);
        $this->assertSame(100.0, $sameFx['converted_amount']);

        // Mocked HTTP success for OpenWeatherMap & ExchangeRate
        config(['cargotrace.openweathermap.api_key' => 'test-key']);
        config(['cargotrace.exchangerate.api_key' => 'test-key']);
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'name' => 'Jakarta',
                'main' => ['temp' => 30.5, 'humidity' => 75],
                'weather' => [['main' => 'Clouds', 'description' => 'scattered clouds']],
                'wind' => ['speed' => 4.2],
            ], 200),
            'v6.exchangerate-api.com/*' => Http::response([
                'result' => 'success',
                'conversion_rate' => 15800.0,
                'conversion_result' => 1580000.0,
            ], 200),
        ]);

        $weatherOk = $apiService->getWeather(-6.10, 106.86, 'Jakarta');
        $this->assertTrue($weatherOk['available']);
        $this->assertSame(30.5, $weatherOk['temperature']);

        $fxOk = $apiService->convertCurrency(100, 'USD', 'IDR');
        $this->assertTrue($fxOk['available']);
        $this->assertSame(1580000.0, $fxOk['converted_amount']);
    }
}

