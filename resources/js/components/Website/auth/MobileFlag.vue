<template>
    <div class="ks-mobile-flag">
        <input
            ref="phoneInput"
            type="tel"
            class="ks-mobile-flag-input"
            :placeholder="placeholder"
            autocomplete="tel"
        />
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch, defineEmits, defineProps } from "vue";
import intlTelInput from "intl-tel-input";
import "intl-tel-input/build/css/intlTelInput.css";
import flags1x from "intl-tel-input/build/img/flags.webp";
import flags2x from "intl-tel-input/build/img/flags@2x.webp";
import globe1x from "intl-tel-input/build/img/globe.webp";
import globe2x from "intl-tel-input/build/img/globe@2x.webp";

const props = defineProps({
    modelValue: String,
    initialCountry: {
        type: String,
        default: "sa",
    },
    placeholder: {
        type: String,
        default: "5xxxxxxxx",
    },
    items: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:modelValue", "country-changed"]);
const phoneInput = ref(null);
let iti = null;

const flag1x = `url("${flags1x}")`;
const flag2x = `url("${flags2x}")`;
const globe1xUrl = `url("${globe1x}")`;
const globe2xUrl = `url("${globe2x}")`;

function resolveOnlyCountries() {
    if (!Array.isArray(props.items) || props.items.length === 0) {
        return undefined;
    }
    const codes = props.items
        .map((item) => String(item.slug || item.code || "").trim().toLowerCase())
        .filter((code) => /^[a-z]{2}$/.test(code));
    return codes.length > 0 ? codes : undefined;
}

function digitsOnly(value) {
    return String(value || "").replace(/\D/g, "");
}

/** Convert DB/local mobile to E.164 for intl-tel-input */
function toE164(value) {
    const d = digitsOnly(value);
    if (!d) return "";
    if (d.startsWith("966") && d.length >= 12) return `+${d}`;
    if (d.startsWith("0") && d.length >= 9) return `+966${d.replace(/^0+/, "")}`;
    if (d.length === 9 && d.startsWith("5")) return `+966${d}`;
    return `+${d}`;
}

function normalizedNumber() {
    if (!iti) {
        return "";
    }
    let raw = "";
    try {
        raw = iti.getNumber?.() || phoneInput.value?.value || "";
    } catch {
        raw = phoneInput.value?.value || "";
    }
    if (!raw) {
        return "";
    }
    return digitsOnly(raw);
}

function emitValue() {
    emit("update:modelValue", normalizedNumber());
}

function applyNumber(value) {
    if (!iti || !phoneInput.value) return;
    const e164 = toE164(value);
    const nextDigits = digitsOnly(e164);
    if (!nextDigits) return;
    if (nextDigits === normalizedNumber()) return;
    try {
        iti.setNumber(e164);
    } catch {
        phoneInput.value.value = nextDigits;
    }
}

onMounted(() => {
    if (!phoneInput.value) {
        return;
    }

    const options = {
        initialCountry: props.initialCountry || "sa",
        nationalMode: false,
        separateDialCode: true,
        formatAsYouType: true,
        countrySearch: true,
        loadUtils: () => import("intl-tel-input/build/js/utils.js"),
    };

    const onlyCountries = resolveOnlyCountries();
    if (onlyCountries) {
        options.onlyCountries = onlyCountries;
    }

    iti = intlTelInput(phoneInput.value, options);

    phoneInput.value.addEventListener("input", emitValue);
    phoneInput.value.addEventListener("countrychange", () => {
        const countryData = iti.getSelectedCountryData();
        emit("country-changed", countryData?.dialCode || "");
        emitValue();
    });

    const applySeed = () => {
        applyNumber(props.modelValue);
        if (digitsOnly(props.modelValue)) {
            emitValue();
        }
    };

    if (iti.promise) {
        iti.promise.then(applySeed);
    } else {
        applySeed();
    }
});

watch(
    () => props.modelValue,
    (next) => {
        applyNumber(next);
    }
);

onBeforeUnmount(() => {
    iti?.destroy();
    iti = null;
});
</script>

<style>
.ks-mobile-flag {
    --iti-path-flags-1x: v-bind(flag1x);
    --iti-path-flags-2x: v-bind(flag2x);
    --iti-path-globe-1x: v-bind(globe1xUrl);
    --iti-path-globe-2x: v-bind(globe2xUrl);
    width: 100%;
}

.ks-mobile-flag .iti {
    width: 100%;
    display: block;
}

.ks-mobile-flag .iti__country-container {
    z-index: 3;
}

.ks-mobile-flag-input {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #ccc;
    min-height: 44px;
    padding: 10px 12px;
    font-size: 16px;
    background: #fff;
}

.ks-mobile-flag .iti--separate-dial-code .iti__selected-country {
    padding: 0 8px 0 10px;
    margin-right: 0;
    background: #f7f7f7;
    border-right: 1px solid #ddd;
}

.ks-mobile-flag .iti--separate-dial-code .iti__selected-dial-code {
    margin-left: 6px;
    font-weight: 600;
    color: #111;
}

.ks-mobile-flag .iti__selected-country-primary {
    display: flex;
    align-items: center;
    gap: 6px;
}
</style>
