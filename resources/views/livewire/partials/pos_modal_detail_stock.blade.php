<div class="modal fade text-left" id="stock-detail-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel12"
    style="display: none;" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel13"><b>Detail Stok Kurang - {{ $titleStockNotEnough }}</b></h3>
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
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Bahan</th>
                                <th scope="col">Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($listStockNotEnough as $key => $stock)
                                <tr>
                                    <td class="no">
                                        {{ $key + 1 }}
                                    </td>
                                    <td>
                                        {{ $stock['name'] }}
                                    </td>
                                    <td style="color: red">
                                        {{ $stock['amount'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
