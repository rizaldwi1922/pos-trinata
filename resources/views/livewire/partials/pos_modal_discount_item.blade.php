<div class="modal fade text-left" id="item-discount-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel12"
    style="display: none;" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel13"><b>Atur Diskon - @if ($item_discount) {{ $item_discount['name'] }}
                            @if (!empty($item_discount['variant_id']))
                                ({{ $item_discount['measurement'] }}
                                {{ $item_discount['unit_name'] }})
                            @endif
                        @endif
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
                                    Tipe Diskon: </th>
                                <td
                                    class="border-0 justify-content-end d-flex text-primary font-size-lg font-size-bold px-0 font-size-lg mb-0 font-size-bold text-primary">
                                    <select id="item_discount_type" wire:change="update_item_discount_type($event.target.value)"
                                        style="width:100%;border:none;padding:10px">
                                        <option value="" selected>Pilih Tipe</option>
                                        <option value="1" @if($item_discount && $item_discount['discount_type'] == '1') selected @endif>Persentase (%)</option>
                                        <option value="2" @if($item_discount && $item_discount['discount_type'] == '2') selected @endif>Nominal (Rp)</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="form-group row">
                        <div class="col-md-12 wrap-barcode">
                            <fieldset class="form-group mb-3">
                                <input type="text" class="form-control number-format"
                                    id="item_discount_value" wire:change="update_item_discount_value($event.target.value)"
                                    value="@if($item_discount){{ $item_discount['discount_value'] }}@endif"
                                    placeholder="Masukan jumlah besaran diskon">
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" wire:click="submit_item_discount"
                        style="width: 100%; background: #2469a5; color: white; font-weight: bold;">
                        Pasang Diskon
                 </button>
            </div>
        </div>
    </div>
</div>
