<div class="modal fade text-left" id="item-custom-product-modal" tabindex="-1" role="dialog"
    aria-labelledby="myModalLabel12" style="display: none;" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel13"><b>Tambah Item Kustom
                    </b></h3>
                <button type="button" class="close rounded-pill btn btn-sm btn-icon btn-light btn-hover-primary m-0"
                    data-dismiss="modal" aria-label="Close">
                    <svg width="20px" height="20px" viewBox="0 0 16 16" class="bi bi-x" fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="wrap-history-transaction">
                    <table class="table right-table">
                        <tbody>
                            <tr class="align-items-center">
                                <th class="border-0 px-0 font-size-lg mb-0 font-size-bold text-primary">
                                    Produk Bahan: </th>
                                <td
                                    class="border-0 justify-content-end d-flex text-primary font-size-lg font-size-bold px-0 font-size-lg mb-0 font-size-bold text-primary">
                                    <select id="item_custom_ingredient" wire:change="update_item_custom($event.target.value)"
                                        style="width:100%;border:none;padding:10px">
                                        <option value="" selected>Pilih Produk Bahan</option>
                                        @foreach ($listIngredients as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name . ' (' . $item->stock . ' Tersedia)' }}
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <fieldset class="form-group mb-3">
                                <input type="text" class="form-control number-format" id="item_custom_price" placeholder="Masukan harga jual" wire:change="update_item_custom_price($event.target.value)">
                            </fieldset>
                        </div>
                        <div class="col-md-6">
                            <fieldset class="form-group mb-3">
                                <input type="number" class="form-control" id="item_custom_amount" placeholder="Masukan jumlah" wire:change="update_item_custom_amount($event.target.value)">
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" wire:click="submit_item_custom"
                        style="width: 100%; background: #2469a5; color: white; font-weight: bold;">
                        Tambah Item
                </button>
            </div>
        </div>
    </div>
</div>
