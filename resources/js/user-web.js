function closestFormControl(target, selector) {
    if (!(target instanceof Element)) {
        return null;
    }

    return target.closest(selector);
}

function toggleClasses(element, classList, enabled) {
    classList
        .split(/\s+/)
        .filter(Boolean)
        .forEach((className) => element.classList.toggle(className, enabled));
}

function updateCheckoutAddress(form) {
    if (!form) {
        return;
    }

    const address = form.querySelector('[name="shipping_address"]');
    if (!(address instanceof HTMLInputElement)) {
        return;
    }

    const values = [
        form.querySelector('[name="shipping_line1"]')?.value,
        form.querySelector('[name="shipping_ward_name"]')?.value,
        form.querySelector('[name="shipping_district_name"]')?.value,
        form.querySelector('[name="shipping_province_name"]')?.value,
    ].map((value) => String(value ?? "").trim()).filter(Boolean);

    address.value = values.join(", ");
}

document.addEventListener("click", (event) => {
    const toggle = closestFormControl(event.target, "[data-toggle]");
    if (toggle) {
        const target = document.querySelector(toggle.getAttribute("data-toggle"));
        target?.classList.toggle("hidden");
    }

    const copyButton = closestFormControl(event.target, "[data-copy-value]");
    if (copyButton) {
        const value = copyButton.getAttribute("data-copy-value") ?? "";
        void navigator.clipboard?.writeText(value);
        copyButton.setAttribute("data-copied", "true");
        window.setTimeout(() => copyButton.removeAttribute("data-copied"), 1400);
    }

    const tabButton = closestFormControl(event.target, "[data-tab-target]");
    if (tabButton) {
        const group = tabButton.getAttribute("data-tab-group");
        const activeClass = tabButton.getAttribute("data-tab-active-class") ?? "bg-primary text-on-primary";
        const inactiveClass = tabButton.getAttribute("data-tab-inactive-class") ?? "text-on-surface-variant";

        document.querySelectorAll(`[data-tab-panel][data-tab-group="${group}"]`).forEach((panel) => {
            panel.classList.toggle("hidden", panel.id !== tabButton.getAttribute("data-tab-target"));
        });
        document.querySelectorAll(`[data-tab-target][data-tab-group="${group}"]`).forEach((button) => {
            const isActive = button === tabButton;
            toggleClasses(button, activeClass, isActive);
            toggleClasses(button, inactiveClass, !isActive);
        });
    }

    const galleryThumb = closestFormControl(event.target, "[data-gallery-thumb]");
    if (galleryThumb) {
        const main = document.querySelector(galleryThumb.getAttribute("data-gallery-main") ?? "");
        const source = galleryThumb.getAttribute("data-gallery-src") ?? "";
        const alt = galleryThumb.getAttribute("data-gallery-alt") ?? "";

        if (main instanceof HTMLImageElement && source) {
            main.src = source;
            main.alt = alt;
        }

        galleryThumb.parentElement?.querySelectorAll("[data-gallery-thumb]").forEach((button) => {
            button.classList.toggle("border-2", button === galleryThumb);
            button.classList.toggle("border-primary", button === galleryThumb);
            button.classList.toggle("opacity-70", button !== galleryThumb);
        });
    }

    const quantityStep = closestFormControl(event.target, "[data-quantity-step]");
    if (quantityStep) {
        const wrapper = quantityStep.closest("[data-quantity-control]");
        const input = wrapper?.querySelector("[data-quantity-input]");

        if (input instanceof HTMLInputElement) {
            const step = Number(quantityStep.getAttribute("data-quantity-step") ?? "0");
            const min = Number(input.min || "1");
            const max = Number(input.max || "999");
            const current = Number(input.value || min);
            const next = Math.min(max, Math.max(min, current + step));
            input.value = String(next);
            input.dispatchEvent(new Event("input", { bubbles: true }));
        }
    }

    const confirmButton = closestFormControl(event.target, "[data-confirm]");
    if (confirmButton && !window.confirm(confirmButton.getAttribute("data-confirm"))) {
        event.preventDefault();
    }
});

document.addEventListener("input", (event) => {
    const quantity = closestFormControl(event.target, "[data-line-price]");
    if (quantity) {
        const line = quantity.closest("[data-cart-line]");
        const output = line?.querySelector("[data-line-total]");
        const price = Number(quantity.getAttribute("data-line-price") ?? "0");
        const count = Number(quantity.value || "0");

        if (output) {
            output.textContent = new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "VND",
                maximumFractionDigits: 0,
            }).format(price * count);
        }

        if (quantity.hasAttribute("data-submit-on-change")) {
            window.clearTimeout(quantity.submitTimer);
            quantity.submitTimer = window.setTimeout(() => quantity.form?.requestSubmit(), 450);
        }
    }

    const range = closestFormControl(event.target, "[data-range-output]");
    if (range) {
        const outputSelector = range.getAttribute("data-range-output");
        const output = outputSelector ? document.querySelector(outputSelector) : null;
        if (output) {
            output.textContent = `${Number(range.value || "0").toLocaleString("vi-VN")}đ`;
        }
    }

    const addressField = closestFormControl(event.target, '[name="shipping_line1"]');
    if (addressField) {
        updateCheckoutAddress(addressField.closest("form"));
    }
});

document.addEventListener("change", async (event) => {
    const submitOnChange = closestFormControl(event.target, "[data-submit-on-change]");
    if (submitOnChange instanceof HTMLInputElement) {
        submitOnChange.form?.requestSubmit();
    }

    const nameOnlySelect = closestFormControl(event.target, "select[data-location-name-target]");
    if (nameOnlySelect) {
        const form = nameOnlySelect.closest("form");
        const nameTarget = nameOnlySelect.getAttribute("data-location-name-target");
        const input = nameTarget ? form?.querySelector(`[name="${nameTarget}"]`) : null;

        if (input) {
            input.value = nameOnlySelect.selectedOptions[0]?.textContent?.trim() ?? "";
        }

        updateCheckoutAddress(form);
    }

    const select = closestFormControl(event.target, "select[data-location-level]");
    if (!select) {
        return;
    }

    const level = select.getAttribute("data-location-level");
    const form = select.closest("form");
    const selectedOption = select.selectedOptions[0];
    const nameTarget = select.getAttribute("data-location-name-target");

    if (nameTarget) {
        const input = form?.querySelector(`[name="${nameTarget}"]`);
        if (input) {
            input.value = selectedOption?.textContent?.trim() ?? "";
        }
    }

    updateCheckoutAddress(form);

    const nextSelector = select.getAttribute("data-location-next");
    if (!nextSelector || !select.value) {
        return;
    }

    const next = form?.querySelector(nextSelector);
    if (!(next instanceof HTMLSelectElement)) {
        return;
    }

    const endpoint = level === "province"
        ? `/api/shipping/ghn/districts?province_id=${encodeURIComponent(select.value)}`
        : `/api/shipping/ghn/wards?district_id=${encodeURIComponent(select.value)}`;

    next.innerHTML = '<option value="">Đang tải...</option>';

    try {
        const response = await fetch(endpoint, { headers: { Accept: "application/json" } });
        const payload = await response.json();
        const items = payload.data ?? [];
        const valueKey = level === "province" ? "DistrictID" : "WardCode";
        const labelKey = level === "province" ? "DistrictName" : "WardName";
        next.innerHTML = '<option value="">Chọn</option>' + items.map((item) => {
            const value = item[valueKey] ?? item.id ?? "";
            const label = item[labelKey] ?? item.name ?? value;
            return `<option value="${String(value).replace(/"/g, "&quot;")}">${label}</option>`;
        }).join("");
    } catch {
        next.innerHTML = '<option value="">Không tải được dữ liệu</option>';
    }
});
