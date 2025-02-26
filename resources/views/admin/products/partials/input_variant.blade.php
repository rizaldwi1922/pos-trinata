@php
    $is_delete = !isset($delete) || (isset($delete) && $delete);
    $is_divider = !isset($divider) || (isset($divider) && $divider);
@endphp

<div class="variant variant_id_{{ isset($variant) ? $variant->id : 1 }}">
    <input type="hidden" name="variant_ids[]" value="{{ isset($variant) ? $variant->id : null }}">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="unit_id" class="form-label">Satuan <span class="text-danger">*</span></label>
                <select class="form-select" id="unit_id" name="units[]" required>
                    <option value="">Pilih Satuan</option>
                    @foreach (App\Models\ProductUnit::where('store_id', auth()->user()->store_id)->orderBy('name', 'ASC')->get() as $unit)
                        <option value="{{ $unit->id }}" @if (isset($variant) && $variant->unit_id == $unit->id) selected @endif>
                            {{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-{{ $is_delete ? '5' : '6' }}">
            <div class="mb-3">
                <label for="code" class="form-label">Kode Seri (Kosongkan jika otomatis)</label>
                <input type="text" class="form-control" id="code" name="codes[]"
                    value="{{ $variant->code ?? '' }}" maxlength="10">
                @if (isset($variant))
                    <a style="font-size: 12px;" href="javascript::void(0)"
                        onclick="openBarcode('{{ $variant->code }}')">Lihat Barcode</a>
                @endif
            </div>
        </div>

        @if ($is_delete)
            <div class="col-md-1">
                <div class="mb-3">
                    <label for="remove-variant" class="form-label">Hapus</label>
                    <button type="button" data-remove="{{ isset($variant) ? $variant->id : 1 }}"
                        class="btn btn-sm btn-danger remove-variant-btn" id="remove-variant">X</button>
                </div>
            </div>
        @endif
    </div>

    <div class="row" @if (isset($variant)) style="margin-top: -10px;" @endif>
        <div class="col-md-{{ $is_delete ? '2' : '3' }}">
            <div class="mb-3">
                <label for="measurement" class="form-label">Ukuran <span class="text-danger">*</span></label>
                <input type="number" placeholder="ex. 1" class="form-control" id="measurement" name="measurements[]"
                    value="{{ $variant->measurement ?? '' }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="buy_price" class="form-label">Harga Beli <span class="text-danger">*</span></label>
                <input type="text" placeholder="cth: 5.500" class="form-control number-format" id="buy_price" name="buy_prices[]"
                    value="{{ $variant->buy_price ?? '' }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="sell_price" class="form-label">Harga Jual <span class="text-danger">*</span></label>
                <input type="text" placeholder="cth: 7.000" class="form-control number-format" id="sell_price" name="sell_prices[]"
                    value="{{ $variant->sell_price ?? '' }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="sell_price" class="form-label">Harga Jual Grosir</label>
                <input type="text" placeholder="cth: 6.500" class="form-control number-format" id="sell_retail_price" name="sell_retail_prices[]"
                    value="{{ $variant->sell_retail_price ?? '' }}">
            </div>
        </div>
    </div>
    @if ($is_divider)
        <hr>
    @endif
</div>

@pushOnce('scripts')
    <script>
        addVariantListener();

        document.getElementById('add-variant').addEventListener('click', function() {
            let variant = document.querySelector('.variant').cloneNode(true);
            let newId = Math.floor(Math.random() * 1000);
            variant.querySelector('select').value = '';
            variant.querySelector('input[id=buy_price]').value = '';
            variant.querySelector('input[id=sell_price]').value = '';
            variant.querySelector('input[id=sell_retail_price]').value = '';
            variant.querySelector('input[id=measurement]').value = '';
            variant.querySelector('input[id=code]').value = '';
            variant.querySelector('button').setAttribute('data-remove', newId);
            variant.classList.add('variant_id_' + newId);
            variant.querySelector('input[type="hidden"]').value = '';
            document.querySelector('.variants').appendChild(variant);

            addVariantListener();
        });

        function addVariantListener() {
            document.querySelectorAll('.remove-variant-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (document.querySelectorAll('.remove-variant-btn').length === 1) {
                        Swal.fire('Peringatan', 'Minimal harus ada 1 varian', 'warning');
                        return;
                    }
                    let variantId = this.getAttribute('data-remove');
                    document.querySelector('.variant_id_' + variantId).remove();
                });
            });
        }
    </script>
@endPushOnce
