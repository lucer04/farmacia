@extends('template.administrador')

@section('title', 'NUEVA VENTA')
@section('description', 'Registrar venta y sus medicamentos')
@section('item-breadcrumb', 'Nueva')
@section('section-breadcrumb', 'Ventas')
@section('module-breadcrumb', 'Gestión')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            @if ($customers->isEmpty() || $medicines->isEmpty())
                <div class="alert alert-warning">Para registrar una venta se necesita al menos un cliente y un medicamento con stock disponible.</div>
            @endif
            <form action="{{ route('sales.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer_id" class="form-label">Cliente</label>
                        <select id="customer_id" name="customer_id" required class="form-select @error('customer_id') is-invalid @enderror">
                            <option value="">Seleccione un cliente</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} — {{ $customer->dni }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="seller" class="form-label">Vendedor</label>
                        <input id="seller" type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                    </div>
                </div>

                <hr>
                <h5>Medicamentos</h5>
                @error('items')<div class="alert alert-danger">{{ $message }}</div>@enderror
                <div id="sale-items">
                    @foreach (old('items', [['medicine_id' => '', 'amount' => 1]]) as $index => $item)
                        @include('sales._item-row', ['index' => $index, 'item' => $item])
                    @endforeach
                </div>
                <template id="sale-item-template">@include('sales._item-row', ['index' => '__INDEX__', 'item' => []])</template>
                <button type="button" id="add-sale-item" class="btn btn-outline-primary">Agregar medicamento</button>
                <div class="mt-4">
                    <p class="mb-1">Total (incluye IGV): <strong>S/ <span id="sale-total">0.00</span></strong></p>
                    <small class="text-muted">El total y el IGV del 18% se calculan y validan al registrar la venta.</small>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary text-white">Guardar venta</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const rows = document.getElementById('sale-items');
            const template = document.getElementById('sale-item-template');
            let nextIndex = rows.querySelectorAll('.sale-item').length;

            const updateTotal = () => {
                let total = 0;
                rows.querySelectorAll('.sale-item').forEach((row) => {
                    const select = row.querySelector('select');
                    const amount = Number(row.querySelector('input[type="number"]').value);
                    total += Number(select.selectedOptions[0]?.dataset.price || 0) * amount;
                });
                document.getElementById('sale-total').textContent = total.toFixed(2);
            };

            document.getElementById('add-sale-item').addEventListener('click', () => {
                rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
                updateTotal();
            });

            rows.addEventListener('click', (event) => {
                if (event.target.closest('.remove-sale-item')) {
                    event.target.closest('.sale-item').remove();
                    updateTotal();
                }
            });

            rows.addEventListener('input', updateTotal);
            rows.addEventListener('change', updateTotal);
            updateTotal();
        })();
    </script>
@endsection
