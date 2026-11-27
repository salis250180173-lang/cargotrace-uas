@extends('layouts.app')

@section('title', 'Edit Shipment')
@section('page-title', 'Edit Shipment: ' . $shipment->shipment_number)

@section('content')
    <form method="POST" action="{{ route('shipments.update', $shipment) }}" class="space-y-6 max-w-4xl">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Customer *</label>
                    <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $shipment->customer_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Carrier *</label>
                    <select name="carrier_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                        @foreach($carriers as $carrier)
                            <option value="{{ $carrier->id }}" @selected(old('carrier_id', $shipment->carrier_id) == $carrier->id)>{{ $carrier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Origin *</label>
                    <select name="origin_location_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(old('origin_location_id', $shipment->origin_location_id) == $loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Destination *</label>
                    <select name="destination_location_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(old('destination_location_id', $shipment->destination_location_id) == $loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cargo Type</label>
                    <input type="text" name="cargo_type" value="{{ old('cargo_type', $shipment->cargo_type) }}"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Shipping Cost</label>
                        <input type="number" step="0.01" name="shipping_cost" value="{{ old('shipping_cost', $shipment->shipping_cost) }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Currency</label>
                        <select name="currency_code" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                            @foreach($currencies as $cur)
                                <option value="{{ $cur }}" @selected(old('currency_code', $shipment->currency_code) === $cur)>{{ $cur }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Expected Arrival</label>
                    <input type="datetime-local" name="expected_arrival"
                           value="{{ old('expected_arrival', $shipment->expected_arrival?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estimated Arrival (ETA)</label>
                    <input type="datetime-local" name="estimated_arrival"
                           value="{{ old('estimated_arrival', $shipment->estimated_arrival?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                Update Shipment
            </button>
            <a href="{{ route('shipments.show', $shipment) }}" class="px-4 py-2.5 text-sm text-slate-600 hover:text-slate-800">Batal</a>
        </div>
    </form>
@endsection

