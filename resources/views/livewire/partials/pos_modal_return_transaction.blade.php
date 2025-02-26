<div class="modal fade text-left" id="retur-transaction-modal" tabindex="-1" role="dialog"
    aria-labelledby="myModalLabel12" style="display: none;" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel13"><b>Retur Transaksi</b></h3>
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
                    @foreach ($retur_item_transactions as $item)
                        @if ($item['amount'] === 0)
                            @continue
                        @endif
                        <div class="list-history-transaction"  style="justify-content: space-between;">
                            <div class="detail">
                                <div class="customer">
                                    {{ $item['name'] }}
                                </div>
                                <div class="date">

                                </div>
                                <div class="amount-total">
                                    @currency($item['sell_price'] * $item['amount']) (x{{ $item['amount'] }})
                                </div>
                            </div>
                            <input type="number" wire:change="update_retur_item({{ $loop->iteration }}, $event.target.value)" class="input-retur">
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" wire:click="submit_retur_item" style="width: 100%; background: #2469a5; color: white; font-weight: bold;">
                    Retur Transaksi
                </button>
            </div>
        </div>
    </div>
</div>
