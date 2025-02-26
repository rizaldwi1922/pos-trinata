<div class="modal fade text-left" id="history-preorder-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel12"
    style="display: none;" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel13"><b>Riwayat Pre-Order (Pending)</b></h3>
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
                <fieldset class="form-group mb-3">
                    <input type="text" class="form-control" placeholder="Pencarian..." wire:change="search_history_preorder($event.target.value)">
                </fieldset>
                <div class="wrap-history-transaction">
                    @if (count($list_history_preorder) > 0)
                        @foreach ($list_history_preorder as $key => $transaction)
                            @php
                                $credit = $transaction['amount_less'];
                            @endphp
                            <div class="list-history-transaction">
                                <div class="detail">
                                    <div class="id">{{ $transaction['trx_id'] }}</div>
                                    <div class="customer" style="@if (empty($transaction['customer_name'])) color: blue; @endif">
                                        {{ $transaction['customer_name'] ?? 'Guest' }}
                                    </div>
                                    <div class="date">
                                        {{ Carbon\Carbon::parse($transaction['created_at'])->translatedFormat('d F Y, H:i:s') }}
                                        WIB
                                    </div>
                                    <div class="amount-total">
                                        Total Kekurangan: <b>@currency($transaction['amount_less'])</b>
                                    </div>
                                </div>
                                <div class="action">
                                    <div onclick="location.href = '{{ route('admin.transactions.show', $transaction['id']) . '?from=pos' }}'"
                                        class="btn btn-primary">
                                        <i class="mdi mdi-printer"></i>
                                    </div>

                                    <div wire:click="open_modal_payment_preorder({{ $transaction['id'] }})"
                                        class="btn btn-success">
                                        <i class="mdi mdi-cash-clock"></i>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>Tidak ada data</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
