@extends('layouts.admin.app')

@section('title', (isset($product) ? 'Edit' : 'Tambah') . ' Produk')

@section('content')

    <div class="card mt-4">
        <form action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($product))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">
                    {{ isset($product) ? 'Edit' : 'Tambah' }} Produk</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" placeholder="ex. Susu UHT" class="form-control" id="name"
                                name="name" value="{{ $product->name ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                @foreach (App\Models\ProductCategory::where('store_id', auth()->user()->store_id)->orderBy('name', 'ASC')->get() as $category)
                                    <option value="{{ $category->id }}" @if (isset($product) && $product->category_id == $category->id) selected @endif>
                                        {{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Gambar</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="inputTypeSwitch">
                        <label class="form-check-label" for="inputTypeSwitch">Embed URL</label>
                    </div>
                    <div id="fileInput" style="display:none;">
                        <input class="form-control" type="file" id="image" name="image" accept="image/*">
                    </div>
                    <div id="urlInput" style="display:none;">
                        <input class="form-control" placeholder="ex. https://imageurl.com" type="text" id="imageUrl"
                            name="imageUrl">
                    </div>
                    <script>
                        const inputTypeSwitch = document.getElementById('inputTypeSwitch');
                        const fileInput = document.getElementById('fileInput');
                        const urlInput = document.getElementById('urlInput');
                        inputTypeSwitch.addEventListener('change', () => {
                            if (inputTypeSwitch.checked) {
                                fileInput.style.display = 'none';
                                urlInput.style.display = 'block';

                                // Clear file input value
                                document.getElementById('image').value = '';
                            } else {
                                fileInput.style.display = 'block';
                                urlInput.style.display = 'none';

                                // Clear url input value
                                document.getElementById('imageUrl').value = '';
                            }
                        });

                        let imageData = '{{ $product->image ?? '' }}';
                        if (imageData.includes('http://') || imageData.includes('https://')) {
                            inputTypeSwitch.checked = true;
                            fileInput.style.display = 'none';
                            urlInput.style.display = 'block';
                            urlInput.querySelector('input').value = imageData;
                        } else {
                            fileInput.style.display = 'block';
                            urlInput.style.display = 'none';
                        }
                    </script>
                </div>

                <div id="section_price" @if (!isset($product) || (isset($product) && empty($product->sell_price))) style="display: none;" @endif>
                    <div class="row">
                        <div class="col-4">
                            <div class="mb-3">
                                <label for="name" class="form-label">Harga Modal <span
                                        class="text-danger">*</span></label>
                                <input type="text" placeholder="cth: 22.500" class="form-control number-format" id="buy_price"
                                    name="buy_price" value="{{ $product->buy_price ?? '' }}">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-3">
                                <label for="name" class="form-label">Harga Jual <span
                                        class="text-danger">*</span></label>
                                <input type="text" placeholder="cth: 28.000" class="form-control number-format" id="sell_price"
                                    name="sell_price" value="{{ $product->sell_price ?? '' }}">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-3">
                                <label for="code" class="form-label">Kode Seri (Kosongkan jika otomatis)</label>
                                <input type="text" class="form-control" id="product_code" name="code"
                                    value="{{ $product->code ?? '' }}" maxlength="10">
                                @if (isset($product))
                                    <a style="font-size: 12px;" href="javascript::void(0)"
                                        onclick="openBarcode('{{ $product->code }}')">Lihat Barcode</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    @if (!isset($product))
                        <div class="form-check form-switch">
                            <input class="form-check-input" name="is_variant" type="checkbox"
                                id="inputTypeSwitchTypeProductVarian">
                            <label class="form-check-label" for="inputTypeSwitchTypeProductVarian">Produk berbasis
                                Varian</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" name="is_ingredient" type="checkbox"
                                id="inputTypeSwitchTypeProductIngredient">
                            <label class="form-check-label" for="inputTypeSwitchTypeProductIngredient">Produk berbasis
                                Bahan</label>
                        </div>
                    @endif

                    @if (!isset($product))
                        <hr>
                    @else
                        <div class="mb-2">
                            <label for="variants"
                                class="form-label"><b>{{ !empty($product->sell_price) ? 'Bahan' : 'Varian' }}
                                    Produk</b></label>
                        </div>
                    @endif

                    <div id="product_variant_section" @if (!isset($product) || (isset($product) && !empty($product->sell_price))) style="display: none;" @endif>
                        <div class="variants">
                            @if (isset($product))
                                @foreach ($product->variants as $variant)
                                    @include('admin.products.partials.input_variant', [
                                        'variant' => $variant,
                                    ])
                                @endforeach

                                @if (count($product->variants) === 0 && empty($product->sell_price))
                                    @include('admin.products.partials.input_variant')
                                @endif
                            @else
                                @include('admin.products.partials.input_variant')
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-dark col-12" id="add-variant">Tambah Varian</button>
                    </div>

                    <div id="product_ingredient_section" @if (!isset($product) || (isset($product) && empty($product->sell_price))) style="display: none;" @endif>
                        <div class="ingredients">
                            @if (isset($product))
                                @foreach ($product->ingredients as $ingredient)
                                    @include('admin.products.partials.input_ingredient', [
                                        'ingredient' => $ingredient,
                                        'amount' => App\Models\ProductIngredientRelation::where(
                                            'product_id',
                                            $product->id)->where('ingredient_id', $ingredient->id)->first()->amount,
                                    ])
                                @endforeach
                            @else
                                @include('admin.products.partials.input_ingredient')
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-dark col-12" id="add-ingredient">Tambah
                            Bahan</button>
                    </div>

                    <script>
                        const inputTypeSwitchTypeProductVarian = document.getElementById('inputTypeSwitchTypeProductVarian');
                        const inputTypeSwitchTypeProductIngredient = document.getElementById('inputTypeSwitchTypeProductIngredient');
                        const product_variant_section = document.getElementById('product_variant_section');
                        const product_ingredient_section = document.getElementById('product_ingredient_section');
                        inputTypeSwitchTypeProductVarian.addEventListener('change', () => {
                            if (inputTypeSwitchTypeProductVarian.checked) {
                                product_variant_section.style.display = 'block';
                                product_ingredient_section.style.display = 'none';

                                if (inputTypeSwitchTypeProductIngredient.checked) {
                                    inputTypeSwitchTypeProductIngredient.checked = false;
                                }

                                document.getElementById('section_price').style.display = 'none';
                                document.getElementById('buy_price').removeAttribute('required');
                                document.getElementById('sell_price').removeAttribute('required');

                                // add temporary input and select required attribute
                                document.querySelectorAll('.ingredient').forEach(function(ingredient) {
                                    // get inputs where contains required attribute
                                    ingredient.querySelectorAll('input, select').forEach(function(input) {
                                        if (input.hasAttribute('required')) {
                                            input.setAttribute('temp-required', 'true');
                                        }
                                    });

                                    // remove required attribute
                                    ingredient.querySelectorAll('input, select').forEach(function(input) {
                                        if (input.hasAttribute('required')) {
                                            input.removeAttribute('required');
                                        }
                                    });
                                });

                                // active required attribute for variant
                                document.querySelectorAll('.variant').forEach(function(variant) {
                                    // get inputs where contains required attribute
                                    variant.querySelectorAll('input, select').forEach(function(input) {
                                        if (input.hasAttribute('temp-required')) {
                                            input.setAttribute('required', 'true');
                                            input.removeAttribute('temp-required');
                                        }
                                    });
                                });
                            } else {
                                product_variant_section.style.display = 'none';
                            }
                        });

                        inputTypeSwitchTypeProductIngredient.addEventListener('change', () => {
                            if (inputTypeSwitchTypeProductIngredient.checked) {
                                product_ingredient_section.style.display = 'block';
                                product_variant_section.style.display = 'none';

                                if (inputTypeSwitchTypeProductVarian.checked) {
                                    inputTypeSwitchTypeProductVarian.checked = false;
                                }

                                document.getElementById('section_price').style.display = 'block';
                                document.getElementById('buy_price').setAttribute('required', 'true');
                                document.getElementById('sell_price').setAttribute('required', 'true');

                                // add temporary input and select required attribute
                                document.querySelectorAll('.variant').forEach(function(variant) {
                                    // get inputs where contains required attribute
                                    variant.querySelectorAll('input, select').forEach(function(input) {
                                        if (input.hasAttribute('required')) {
                                            input.setAttribute('temp-required', 'true');
                                        }
                                    });

                                    // remove required attribute
                                    variant.querySelectorAll('input, select').forEach(function(input) {
                                        if (input.hasAttribute('required')) {
                                            input.removeAttribute('required');
                                        }
                                    });
                                });

                                // active required attribute for ingredient
                                document.querySelectorAll('.ingredient').forEach(function(ingredient) {
                                    // get inputs where contains required attribute
                                    ingredient.querySelectorAll('input, select').forEach(function(input) {
                                        if (input.hasAttribute('temp-required')) {
                                            input.setAttribute('required', 'true');
                                            input.removeAttribute('temp-required');
                                        }
                                    });
                                });
                            } else {
                                product_ingredient_section.style.display = 'none';
                                document.getElementById('section_price').style.display = 'none';
                            }
                        });
                    </script>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
