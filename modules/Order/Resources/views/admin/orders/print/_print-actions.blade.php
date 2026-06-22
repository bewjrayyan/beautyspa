@unless ($forPdf ?? false)
    @if ($autoPrint ?? true)
        <script type="module">
            window.print();
        </script>
    @else
        <div class="order-print-toolbar">
            <button type="button" class="order-print-toolbar__btn" onclick="window.print()">
                {{ trans('order::print.print_or_save') }}
            </button>
        </div>
    @endif
@endunless
