<div class="ingredient ingredient_id_{{ isset($ingredient) ? $ingredient->id : 1 }}">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="ingredient_id" class="form-label">Bahan <span class="text-danger">*</span></label>
                <select class="form-select" id="ingredient_id" name="ingredient_ids[]" required>
                    <option value="">Pilih Bahan</option>
                    @foreach (App\Models\ProductIngredient::where('store_id', auth()->user()->store_id)->orderBy('name', 'ASC')->get() as $master_ingredient)
                        <option value="{{ $master_ingredient->id }}" @if (isset($ingredient) && $ingredient->id == $master_ingredient->id) selected @endif>
                            {{ $master_ingredient->name }}
                            ({{ $master_ingredient->unit->name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-5">
            <div class="mb-3">
                <label for="amount" class="form-label">Jumlah</label>
                <input type="number" class="form-control" id="amount" name="amounts[]" value="{{ $amount ?? null }}"
                    maxlength="10" required>
            </div>
        </div>

        <div class="col-md-1">
            <div class="mb-3">
                <label for="remove-ingredient" class="form-label">Hapus</label>
                <button type="button" data-remove="{{ isset($ingredient) ? $ingredient->id : 1 }}"
                    class="btn btn-sm btn-danger remove-ingredient-btn" id="remove-ingredient">X</button>
            </div>
        </div>
    </div>
</div>

@pushOnce('scripts')
    <script>
        addIngredientListener();

        document.getElementById('add-ingredient').addEventListener('click', function() {
            let ingredient = document.querySelector('.ingredient').cloneNode(true);
            let newId = Math.floor(Math.random() * 1000);
            ingredient.querySelector('select').value = '';
            ingredient.querySelector('input[id=amount]').value = '';
            ingredient.classList.add('ingredient_id_' + newId);
            ingredient.querySelector('button').setAttribute('data-remove', newId);
            document.querySelector('.ingredients').appendChild(ingredient);

            addIngredientListener();
        });

        function addIngredientListener() {
            document.querySelectorAll('.remove-ingredient-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (document.querySelectorAll('.remove-ingredient-btn').length === 1) {
                        Swal.fire('Peringatan', 'Minimal harus ada 1 bahan', 'warning');
                        return;
                    }
                    let ingredientId = this.getAttribute('data-remove');
                    document.querySelector('.ingredient_id_' + ingredientId).remove();
                });
            });
        }
    </script>
@endPushOnce
