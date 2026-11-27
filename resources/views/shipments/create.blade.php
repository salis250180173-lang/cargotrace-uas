@extends('layouts.app')

@section('title', 'Create Shipment')
@section('page-title', 'Create Shipment')

@section('content')
    <form method="POST" action="{{ route('shipments.store') }}" class="space-y-6 max-w-4xl">
        @csrf

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
            <h2 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3">Informasi Utama Shipment</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Customer *</label>
                    <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                        <option value="">Pilih Customer</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Carrier *</label>
                    <select name="carrier_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                        <option value="">Pilih Carrier</option>
                        @foreach($carriers as $carrier)
                            <option value="{{ $carrier->id }}" @selected(old('carrier_id') == $carrier->id)>{{ $carrier->name }} ({{ $carrier->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Origin Location *</label>
                    <select name="origin_location_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                        <option value="">Pilih Origin</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(old('origin_location_id') == $loc->id)>{{ $loc->name }} ({{ $loc->country }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Destination Location *</label>
                    <select name="destination_location_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                        <option value="">Pilih Destination</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(old('destination_location_id') == $loc->id)>{{ $loc->name }} ({{ $loc->country }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cargo Type</label>
                    <input type="text" name="cargo_type" value="{{ old('cargo_type') }}" placeholder="e.g. Electronics, Pharma"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" value="{{ old('weight') }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Volume (m³)</label>
                        <input type="number" step="0.01" name="volume" value="{{ old('volume') }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Shipping Cost</label>
                        <input type="number" step="0.01" name="shipping_cost" value="{{ old('shipping_cost') }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Currency *</label>
                        <select name="currency_code" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                            @foreach($currencies as $cur)
                                <option value="{{ $cur }}" @selected(old('currency_code', 'USD') === $cur)>{{ $cur }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 md:col-span-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Expected Departure</label>
                        <input type="datetime-local" name="expected_departure" value="{{ old('expected_departure') }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Expected Arrival</label>
                        <input type="datetime-local" name="expected_arrival" value="{{ old('expected_arrival') }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Estimated Arrival</label>
                        <input type="datetime-local" name="estimated_arrival" value="{{ old('estimated_arrival') }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                </div>
            </div>
        </div>

        {{-- Dynamic Shipment Items --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="font-bold text-slate-800 text-base">Shipment Items</h2>
                <button type="button" onclick="addItemRow()" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-700">
                    + Add Item
                </button>
            </div>

            <div id="items-container" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end item-row">
                    <div class="sm:col-span-6">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Item Name</label>
                        <input type="text" name="items[0][item_name]" placeholder="e.g. Industrial Server Rack"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Qty</label>
                        <input type="number" min="1" name="items[0][quantity]" value="1"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Weight (kg)</label>
                        <input type="number" step="0.01" name="items[0][unit_weight]"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
                    </div>
                    <div class="sm:col-span-1">
                        <button type="button" onclick="this.closest('.item-row').remove()" class="px-2.5 py-2 text-xs text-rose-600 hover:bg-rose-50 rounded-lg">
                            &times;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                Simpan Shipment
            </button>
            <a href="{{ route('shipments.index') }}" class="px-4 py-2.5 text-sm text-slate-600 hover:text-slate-800">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    let itemIndex = 1;
    function addItemRow() {
        const container = document.getElementById('items-container');
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 sm:grid-cols-12 gap-3 items-end item-row';
        row.innerHTML = `
            <div class="sm:col-span-6">
                <input type="text" name="items[${itemIndex}][item_name]" placeholder="Item name" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
            </div>
            <div class="sm:col-span-2">
                <input type="number" min="1" name="items[${itemIndex}][quantity]" value="1" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
            </div>
            <div class="sm:col-span-3">
                <input type="number" step="0.01" name="items[${itemIndex}][unit_weight]" placeholder="Weight" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
            </div>
            <div class="sm:col-span-1">
                <button type="button" onclick="this.closest('.item-row').remove()" class="px-2.5 py-2 text-xs text-rose-600 hover:bg-rose-50 rounded-lg">&times;</button>
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
    }
</script>
@endpush

