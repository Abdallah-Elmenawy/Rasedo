<!-- Back-to-top -->
<a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>
@include('layouts.flash-modal')
<!-- JQuery min js -->
<script src="{{URL::asset('assets/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap Bundle js -->
<script src="{{URL::asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script>
jQuery(document).on('show.bs.modal', '.modal', function () {
    if (this.parentElement !== document.body) {
        document.body.appendChild(this);
    }
});
</script>
<!-- Ionicons js -->
<script src="{{URL::asset('assets/plugins/ionicons/ionicons.js')}}"></script>
<!-- Moment js -->
<script src="{{URL::asset('assets/plugins/moment/moment.js')}}"></script>

<!-- Rating js-->
<script src="{{URL::asset('assets/plugins/rating/jquery.rating-stars.js')}}"></script>
<script src="{{URL::asset('assets/plugins/rating/jquery.barrating.js')}}"></script>

<!--Internal  Perfect-scrollbar js -->
<script src="{{URL::asset('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/perfect-scrollbar/p-scroll.js')}}"></script>
<!--Internal Sparkline js -->
<script src="{{URL::asset('assets/plugins/jquery-sparkline/jquery.sparkline.min.js')}}"></script>
<!-- Custom Scroll bar Js-->
<script src="{{URL::asset('assets/plugins/mscrollbar/jquery.mCustomScrollbar.concat.min.js')}}"></script>
<!-- right-sidebar js -->
<script src="{{URL::asset('assets/plugins/sidebar/sidebar-rtl.js')}}"></script>
<script src="{{URL::asset('assets/plugins/sidebar/sidebar-custom.js')}}"></script>
<!-- Eva-icons js -->
<script src="{{URL::asset('assets/js/eva-icons.min.js')}}"></script>
@yield('js')
@stack('scripts')
<script>
document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input.matches || !input.matches('.file-drop input[type="file"]')) return;
    var drop = input.closest('.file-drop');
    var label = drop.querySelector('.file-drop-name');
    var file = input.files && input.files[0];
    label.textContent = file ? file.name : label.getAttribute('data-placeholder');
    drop.classList.toggle('has-file', !!file);
});
</script>
<!-- Sticky js -->
<script src="{{URL::asset('assets/js/sticky.js')}}"></script>
<!-- custom js -->
<script src="{{URL::asset('assets/js/custom.js')}}"></script><!-- Left-menu js-->
<script src="{{URL::asset('assets/js/rasedo-select.js')}}?v=4"></script>
<script src="{{URL::asset('assets/plugins/side-menu/sidemenu.js')}}?v=6"></script>
<script>
(function ($) {
    if (!$ || !$('#appConfirmModal').length) return;

    var $modal = $('#appConfirmModal');
    var pendingForm = null;

    $(document).on('submit', 'form[data-confirm]', function (e) {
        var form = this;
        if (form.dataset.confirmAccepted === '1') {
            delete form.dataset.confirmAccepted;
            return true;
        }

        e.preventDefault();
        pendingForm = form;

        var methodInput = form.querySelector('input[name="_method"]');
        var isDelete = String((methodInput && methodInput.value) || form.getAttribute('method') || '').toUpperCase() === 'DELETE';

        $('#appConfirmModalTitle').text(isDelete ? 'هل تريد الاستمرار؟' : (form.getAttribute('data-confirm-title') || 'تأكيد'));
        $('#appConfirmModalMessage').text(isDelete ? 'هل أنت متأكد أنك تريد استكمال عملية الحذف؟' : (form.getAttribute('data-confirm') || ''));
        $('#appConfirmOkBtn').text(isDelete ? 'تأكيد' : (form.getAttribute('data-confirm-ok') || 'تأكيد'));
        $('#appConfirmCancelBtn').text('إلغاء');

        var iconClass = isDelete ? 'fe fe-trash-2' : (form.getAttribute('data-confirm-icon') || 'fe fe-trash-2');
        $modal.find('.app-confirm-icon i').attr('class', iconClass);

        $modal.modal('show');
        return false;
    });

    $('#appConfirmOkBtn').on('click', function () {
        if (!pendingForm) return;
        var form = pendingForm;
        pendingForm = null;
        form.dataset.confirmAccepted = '1';
        $modal.modal('hide');
        form.submit();
    });

    $modal.on('hidden.bs.modal', function () {
        pendingForm = null;
    });
})(window.jQuery);

(function () {
    var frame = null;
    var pendingUrl = '';

    function companyFormat() {
        var meta = document.querySelector('meta[name="print-format"]');
        return (meta && meta.content) || 'a4';
    }

    function startPrint(format) {
        if (!pendingUrl) return;
        format = format || companyFormat();
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'appPrintFrame';
            frame.title = 'طباعة';
            frame.setAttribute('aria-hidden', 'true');
            frame.style.cssText = 'position:fixed;left:-10000px;top:0;width:1100px;height:1600px;border:0;';
            document.body.appendChild(frame);
        }
        var glue = pendingUrl.indexOf('?') === -1 ? '?' : '&';
        frame.src = pendingUrl + glue + 'format=' + encodeURIComponent(format) + '&_print=' + Date.now();
    }

    function beginPrint() {
        var open = window.jQuery && window.jQuery('.modal.show');
        if (open && open.length) {
            open.one('hidden.bs.modal', function () { startPrint(companyFormat()); });
            open.modal('hide');
            return;
        }
        startPrint(companyFormat());
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-print-url]');
        if (!btn || btn.disabled) return;
        e.preventDefault();
        pendingUrl = btn.getAttribute('data-print-url') || '';
        if (!pendingUrl) return;
        beginPrint();
    });
})();

(function ($) {
    if (!$ || !$('#appToast').length) return;
    var $toast = $('#appToast');
    var $bar = $('#appToastBar');
    var hideTimer = null;
    var duration = 1500;

    function hideToast() {
        if (hideTimer) {
            clearTimeout(hideTimer);
            hideTimer = null;
        }
        $bar.removeClass('is-run');
        $toast.removeClass('is-show').addClass('is-hide');
        setTimeout(function () {
            $toast.removeClass('is-hide').attr('hidden', true);
        }, 300);
    }

    window.showAppToast = function (type, message) {
        var isSuccess = type === 'success';
        if (hideTimer) clearTimeout(hideTimer);
        $toast.removeClass('is-hide');
        $toast.toggleClass('is-success', isSuccess).toggleClass('is-error', !isSuccess);
        $('#appToastIcon').attr('class', isSuccess ? 'fe fe-check' : 'fe fe-shield');
        $('#appToastMessage').text(message || '');
        $bar.removeClass('is-run');
        $toast.removeAttr('hidden').addClass('is-show');
        void $bar[0].offsetWidth;
        $bar.addClass('is-run');
        hideTimer = setTimeout(hideToast, duration);
    }

    function showToast(type, message) {
        window.showAppToast(type, message);
    }

    $('#appToastClose').on('click', hideToast);

    var $data = $('#appFlashData');
    if ($data.length) {
        showToast($data.data('type') || 'success', $data.data('message') || '');
    }
})(window.jQuery);
</script>
<script>
document.addEventListener('click', function (e) {
    var lineEdit = e.target.closest('.line-edit');
    if (lineEdit) {
        var line = lineEdit.closest('tr');
        var field = line && line.querySelector('select.prod, input, select');
        if (field) {
            field.focus();
            if (typeof field.select === 'function' && field.tagName !== 'SELECT') field.select();
        }
        return;
    }
    var addBtn = e.target.closest('.js-add-item');
    var delBtn = e.target.closest('button.del');
    var table = addBtn && addBtn.dataset.table ? document.getElementById(addBtn.dataset.table) : (delBtn ? delBtn.closest('table') : null);
    if (!table) return;
    setTimeout(function () {
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function (tr, i) {
            var cell = tr.querySelector('td.col-serial');
            if (cell) cell.textContent = String(i + 1);
            var productCell = tr.children[1];
            if (addBtn && productCell && !productCell.querySelector('.line-scan')) {
                var scan = document.createElement('input');
                scan.type = 'text';
                scan.className = 'form-control line-scan';
                scan.placeholder = 'امسح السيريال';
                scan.setAttribute('autocomplete', 'off');
                var serials = productCell.querySelector('.line-serials');
                if (serials) productCell.insertBefore(scan, serials);
                else productCell.appendChild(scan);
            }
            if (addBtn && table.querySelector('th.col-unit') && !tr.querySelector('.line-unit') && productCell && window.lineUnitHtml) {
                var unitCell = document.createElement('td');
                unitCell.className = 'col-unit';
                unitCell.innerHTML = window.lineUnitHtml();
                productCell.insertAdjacentElement('afterend', unitCell);
            }
        });
        if (!addBtn || !rows.length) return;
        var last = rows[rows.length - 1];
        table.querySelectorAll('tr.is-scan-line').forEach(function (tr) { tr.classList.remove('is-scan-line'); });
        last.classList.add('is-scan-line');
        var focus = last.querySelector('.line-scan');
        if (focus) focus.focus();
    }, 0);
});
</script>