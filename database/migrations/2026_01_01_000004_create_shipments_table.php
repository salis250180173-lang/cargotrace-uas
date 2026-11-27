<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('carriers')->restrictOnDelete();
            $table->foreignId('origin_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('destination_location_id')->constrained('locations')->restrictOnDelete();
            $table->string('cargo_type', 100)->nullable();
            $table->decimal('weight', 12, 2)->nullable();
            $table->decimal('volume', 12, 2)->nullable();
            $table->decimal('shipping_cost', 14, 2)->nullable();
            $table->char('currency_code', 3)->default('USD');
            $table->dateTime('expected_departure')->nullable();
            $table->dateTime('expected_arrival')->nullable();
            $table->dateTime('estimated_arrival')->nullable();
            $table->enum('current_status', [
                'CREATED',
                'BOOKED',
                'PICKED_UP',
                'IN_TRANSIT',
                'DELIVERED',
            ])->default('CREATED')->index();
            $table->boolean('is_delayed')->default(false)->index();
            $table->decimal('delay_hours', 8, 2)->default(0);
            $table->enum('delay_severity', ['NONE', 'LOW', 'MEDIUM', 'HIGH'])->default('NONE');
            $table->timestamps();

            $table->index(['customer_id', 'current_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

