{{-- Unified delete confirmation modal — matches Rasedo Bootstrap RTL theme --}}
<div class="modal fade" id="appConfirmModal" tabindex="-1" role="dialog" aria-labelledby="appConfirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered app-confirm-dialog" role="document">
        <div class="modal-content border-0 app-confirm-modal">
            <div class="modal-body text-center">
                <div class="app-confirm-icon">
                    <i class="fe fe-trash-2"></i>
                </div>
                <h5 id="appConfirmModalTitle">هل تريد الاستمرار؟</h5>
                <p id="appConfirmModalMessage">هل أنت متأكد أنك تريد استكمال عملية الحذف؟</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" id="appConfirmOkBtn">تأكيد</button>
                <button type="button" class="btn" data-dismiss="modal" id="appConfirmCancelBtn">إلغاء</button>
            </div>
        </div>
    </div>
</div>
