<div class="modal fade" id="printFormatModal" tabindex="-1" role="dialog" aria-labelledby="printFormatTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 print-format-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="printFormatTitle">اختر الطابعة</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body pt-0">
                <div class="print-format-list">
                    @foreach(\App\Support\InvoicePrintFormat::all() as $key => $format)
                        <button type="button" class="print-format-option" data-format="{{ $key }}">
                            <span>
                                <strong>{{ $format['label'] }}</strong>
                                <small>{{ $format['hint'] }}</small>
                            </span>
                            <em>{{ $format['paper'] }}</em>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
