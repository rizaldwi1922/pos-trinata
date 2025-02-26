@if ($data->total() > 0)
    <a href="javascript::void(0)"@if ($data->currentPage() > 1)  onclick="insertParam('page', {{ $data->currentPage() - 1 }})" @endif class="btn-pagination">Previous</a>

    @php
        $forward = 0;
        $backward = 0;
    @endphp

    <div class="pagination">
        @if ($data->currentPage() == $data->lastPage() || $data->currentPage() == $data->lastPage() - 1)
            @if ($data->currentPage() != 1 && $data->currentPage() != 2 && $data->currentPage() != 3)
                @if ($data->currentPage() == $data->lastPage() && $data->currentPage() > 4)
                    <a href="javascript::void(0)" onclick="insertParam('page','{{ $data->currentPage() - 4 }}')">{{ $data->currentPage() - 4 }}</a>
                @endif
                <a href="javascript::void(0)" onclick="insertParam('page','{{ $data->currentPage() - 3 }}')">{{ $data->currentPage() - 3 }}</a>
            @endif
        @endif

        @if ($data->currentPage() != 1)
            @for ($i = $data->currentPage() - 1; $i <= $data->currentPage(); $i++)
                @if ($backward < 2 && $i - 1 != 0)
                    @php
                        $backward += 1;
                    @endphp
                    <a href="javascript::void(0)" onclick="insertParam('page','{{ $i - 1 }}')">{{ $i - 1 }}</a>
                @endif
            @endfor
        @endif

        @for ($i = $data->currentPage(); $i <= $data->lastPage() + 2; $i++)
            @if ($i <= $data->lastPage())
                @if ($forward < 3)
                    @php
                        $forward += 1;
                        if ($backward == 0 || $backward == 1) {
                            $backward += 1;
                            $forward -= 1;
                        }
                    @endphp
                    <a href="javascript::void(0)" onclick="insertParam('page','{{ $i }}')">{{ $i }}</a>
                @endif
            @endif
        @endfor
    </div>

    <a class="btn-pagination" href="javascript::void(0)" @if ($data->currentPage() != $data->lastPage()) onclick="insertParam('page', {{ $data->currentPage() + 1 }})" @endif>Next</a>

    <script>
        const pagination = document.querySelectorAll('.pagination a');
        pagination.forEach((item) => {
            if (item.innerHTML == {{ $data->currentPage() }}) {
                item.classList.add('active');
            }
        });
    </script>
@endif
