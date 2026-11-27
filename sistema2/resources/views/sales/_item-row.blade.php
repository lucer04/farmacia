<div class="row g-2 align-items-end mb-3 sale-item">
    <div class="col-md-8">
        <label class="form-label">Medicamento</label>
        <select name="items[{{ $index }}][medicine_id]" required class="form-select">
            <option value="">Seleccione un medicamento</option>
            @foreach ($medicines as $medicine)
                <option value="{{ $medicine->id }}" data-price="{{ $medicine->price }}" @selected(($item['medicine_id'] ?? '') == $medicine->id)>
                    {{ $medicine->name }} (existencia: {{ $medicine->stock }})
                </option>
            @endforeach
        </select>
        @error("items.$index.medicine_id")<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label">Cantidad</label>
        <input name="items[{{ $index }}][amount]" type="number" min="1" step="1" required value="{{ old("items.$index.amount", $item['amount'] ?? 1) }}" class="form-control">
        @error("items.$index.amount")<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-outline-danger w-100 remove-sale-item">Quitar</button>
    </div>
</div>
