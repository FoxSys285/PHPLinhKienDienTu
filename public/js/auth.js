(function ($) {
    "use strict";

    var eye = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var eyeOff = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 5.1A13 13 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3 3.7M6.3 6.3A19 19 0 0 0 2 12s3.5 7 10 7a12 12 0 0 0 5.5-1.5"/><path d="M10 10a3 3 0 0 0 4 4"/></svg>';

    $(function () {
        $(".password-toggle").each(function () {
            var button = this;
            var input = document.getElementById(button.getAttribute("data-password-target"));
            var showLabel = button.getAttribute("aria-label");
            if (!input) return;
            button.innerHTML = eye;
            button.setAttribute("aria-controls", input.id);
            button.addEventListener("click", function () {
                var showing = input.type === "password";
                input.type = showing ? "text" : "password";
                button.setAttribute("aria-pressed", String(showing));
                button.setAttribute("aria-label", showing ? showLabel.replace("Hiện", "Ẩn") : showLabel);
                button.innerHTML = showing ? eyeOff : eye;
            });
        });

        $.validator.addMethod("validBirthday", function (value, element) {
            return this.optional(element) || (value >= element.min && value <= element.max);
        }, "Ngày sinh phải từ năm 1900 đến hôm nay.");
        $.validator.addMethod("acceptTerms", function (value, element) {
            return element.checked;
        }, "Bạn cần đồng ý với điều khoản và chính sách bảo mật.");
        $.validator.addMethod("nonWhitespace", function (value, element) {
            return this.optional(element) || value.trim().length > 0;
        }, "Vui lòng nhập thông tin, không chỉ nhập khoảng trắng.");

        $(".auth-form").each(function () {
            var form = $(this);
            var validator = form.data("validator") || form.validate();
            var birthday = form.find('[name="NgaySinh"]');
            var terms = form.find('[name="DongYDieuKhoan"][type="checkbox"]');
            if (birthday.length) birthday.rules("add", { validBirthday: true });
            if (terms.length) {
                terms.attr("aria-describedby", "terms-error");
                terms.rules("add", { acceptTerms: true });
            }
            form.find('input[type="text"], input[type="email"], input[type="tel"]').each(function () {
                $(this).rules("add", { nonWhitespace: true });
            });
            form.on("input change", "input:not([type=hidden]), select", function () {
                validator.element(this);
                var fieldName = this.name;
                form.find("[data-val-equalto-other]").each(function () {
                    var other = this.getAttribute("data-val-equalto-other");
                    var prefix = this.name.substring(0, this.name.lastIndexOf(".") + 1);
                    if (other === "*." + fieldName || prefix + other.substring(2) === fieldName) {
                        if (this.value) validator.element(this);
                    }
                });
            });
            form.on("submit", function () {
                if (!form.valid()) {
                    validator.focusInvalid();
                    return;
                }
                form.find('.auth-submit[type="submit"]').prop("disabled", true);
                form.attr("aria-busy", "true");
            });
        });

        // Trình duyệt có thể khôi phục trạng thái nút disabled khi quay lại trang.
        window.addEventListener("pageshow", function () {
            $('.auth-submit[type="submit"]').prop("disabled", false);
            $(".auth-form").removeAttr("aria-busy");
        });

        var dialog = document.getElementById("policy-dialog");
        if (dialog) {
            $("[data-open-policy]").on("click", function () {
                var section = this.getAttribute("data-open-policy");
                document.getElementById("policy-title").textContent = section === "terms" ? "Điều khoản dịch vụ" : "Chính sách bảo mật";
                $("[data-policy-content]").each(function () {
                    this.hidden = this.getAttribute("data-policy-content") !== section;
                });
                dialog.showModal();
            });
            $("[data-close-policy]").on("click", function () { dialog.close(); });
            dialog.addEventListener("click", function (event) {
                var bounds = dialog.getBoundingClientRect();
                if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) dialog.close();
            });
        }
    });
})(jQuery);
