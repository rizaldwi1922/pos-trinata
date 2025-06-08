<header class="pos-header bg-white {{ $wrap_header_status }}">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-6 ">
                <div class="">
                    <div class="greeting-text d-flex align-items-center">
                        @if($user->roles->first()->id == 1 || $user->roles->first()->id == 3)
                        <a href="/admin/dashboard" class="btn btn-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                            </svg>
                            Kembali</a>
                        @endif
                        <h3 class="card-label mb-0 font-weight-bold text-primary">
                            {{ \App\Models\Store::find($user->store_id)->name }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-6 order-lg-last order-second">
                <div class="topbar justify-content-end">
                    <div class="dropdown" onclick="location.reload();">
                        <div id="btn-shift-out" class="topbar-item">
                            <div class="btn btn-icon w-auto h-auto btn-clean d-flex align-items-center py-0 mr-3">
                                <span class="symbol symbol-35 symbol-light-success">
                                    <span class="symbol-label" style="background: #2469a5;font-size:22px;color:white">
                                        <i class="mdi mdi-refresh"></i>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown" onclick="logout()">
                        <div id="btn-shift-out" class="topbar-item">
                            <div class="btn btn-icon w-auto h-auto btn-clean d-flex align-items-center py-0 mr-3">
                                <span class="symbol symbol-35 symbol-light-success">
                                    <span class="symbol-label" style="background: #2469a5;font-size:22px;color:white">
                                        <i class="mdi mdi-login-variant"></i>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <script>
                        function logout() {
                            Swal.fire({
                                title: 'Apakah anda yakin?',
                                text: "Anda akan keluar dari Akun ini",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Logout',
                                cancelButtonText: 'Batal',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('auth.logout') }}";
                                }
                            });
                        }
                    </script>

                    <div class="dropdown"
                        @if (!$shift) onclick="startShift()" @else wire:click="shift_out" @endif>
                        <div id="btn-shift-out" class="topbar-item">
                            <div class="btn btn-icon w-auto h-auto btn-clean d-flex align-items-center py-0 mr-3">
                                <span class="symbol symbol-35 symbol-light-success">
                                    <span class="symbol-label" style="background: #2469a5;font-size:22px;color:white">
                                        <i class="mdi mdi-calendar-clock"></i>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown" wire:click="get_history_transaction">
                        <div id="btn-history-transaction" class="topbar-item">
                            <div class="btn btn-icon w-auto h-auto btn-clean d-flex align-items-center py-0 mr-3">
                                <span class="symbol symbol-35 symbol-light-success">
                                    <span class="symbol-label" style="background: #2469a5;font-size:22px;color:white;">
                                        <i class="mdi mdi-history"></i>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown" wire:click="get_history_preorder">
                        <div id="btn-history-preorder" class="topbar-item">
                            <div class="btn btn-icon w-auto h-auto btn-clean d-flex align-items-center py-0 mr-3">
                                <span class="symbol symbol-35 symbol-light-success">
                                    <span class="symbol-label" style="background: #2469a5;font-size:22px;color:white;">
                                        <i class="mdi mdi-playlist-edit"></i>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    @desktop
                    @elsedesktop
                        <div class="dropdown" onclick="location.href='opencamera:'" id="camera-scanner">
                            <div id="btn-history-transaction" class="topbar-item">
                                <div class="btn btn-icon w-auto h-auto btn-clean d-flex align-items-center py-0 mr-3">
                                    <span class="symbol symbol-35 symbol-light-success">
                                        <span class="symbol-label" style="background: red;font-size:22px;color:white;">
                                            <i class="mdi mdi-barcode-scan"></i>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @enddesktop
                </div>
            </div>
        </div>
    </div>
</header>

<div class="wrap-bar-navigation {{ $wrap_header_status }}">
    <div class="minimize-button" style="{{ $wrap_header_status == 'close' ? 'line-height: 30px;' : '' }}">
        <span class="mdi mdi-chevron-{{ $wrap_header_status == '' ? 'up' : 'down' }}" wire:click="open_navbar()"></span>
    </div>
</div>
