<div class="modal fade text-left" id="confirm-product" tabindex="-1" role="dialog" aria-labelledby="myModalLabel12"
    style="display: none;" aria-hidden="true" wire:ignore>
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel11">Tambahkan Item</h3>
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
                <div class="d-flex">
                    <div class="image-product-modal align-self-center">
                        <img alt="thumbnail" id="image-chart-modal">
                    </div>
                    <div class="detail-product-modal align-self-center">
                        <div class="name">
                            <div class="title">Nama</div>
                            <div class="separator">:</div>
                            <div class="value" id="title-chart-modal"></div>
                        </div>
                        <div class="code">
                            <div class="title">Kode Seri</div>
                            <div class="separator">:</div>
                            <div class="value" id="code-chart-modal"></div>
                        </div>
                        <div class="price">
                            <div class="title">Harga</div>
                            <div class="separator">:</div>
                            <div class="value" id="price-chart-modal"></div>
                        </div>
                        <div class="price">
                            <div class="title">Jumlah</div>
                            <div class="separator">:</div>
                            <div class="value">
                                <input type="number" value="1" id="amount-item-chart-modal"
                                    wire:change="add_amount_from_scanner($event.target.value)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="list_product" value="{{ json_encode($list_product) }}">
                <input type="hidden" name="amount_total" value="{{ $totalAmount }}">
                <input type="hidden" name="id_product_from_scanner" value="" id="input-add-to-chart">
                <button type="submit" class="action pay float-right" style="width: 120px; height: 50px; border-color: #2469a5;"
                    wire:click="add_item_from_scanner($event.target.value)" value=""
                    id="input-id-to-chart-modal">Tambahkan</button>
            </div>
        </div>
    </div>
</div>
