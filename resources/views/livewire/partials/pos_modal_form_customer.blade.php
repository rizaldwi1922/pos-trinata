<div class="modal fade text-left" id="form-customer-modal" tabindex="-1" role="dialog"
    aria-labelledby="myModalLabel12" style="display: none;" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel13"><b>Tambah Pelanggan
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
                    <div class="form-group">
                        <fieldset class="form-group mb-3">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" class="form-control" placeholder="Masukan nama pelanggan" wire:model="form_customer_name">
                        </fieldset>
                    </div>
                    <div class="form-group">
                        <fieldset class="form-group mb-3">
                            <label class="form-label">Nomor Handphone</label>
                            <input type="text" class="form-control" placeholder="Masukan nomor handphone" wire:model="form_customer_phone">
                        </fieldset>
                    </div>
                    <div class="form-group">
                        <fieldset class="form-group mb-3">
                            <label class="form-label">Alamat Email</label>
                            <input type="text" class="form-control" placeholder="Masukan alamat email" wire:model="form_customer_email">
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" wire:click="submit_form_customer"
                        style="width: 100%; background: #2469a5; color: white; font-weight: bold;">
                        Simpan
                </button>
            </div>
        </div>
    </div>
</div>
