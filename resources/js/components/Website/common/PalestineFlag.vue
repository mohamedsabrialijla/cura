<template>
    <span class="palestine-flag" :class="{ 'palestine-flag--lg': large }" aria-hidden="true">
        <svg viewBox="0 0 24 16" xmlns="http://www.w3.org/2000/svg" focusable="false">
            <rect width="24" height="5.333" y="0" fill="#000" />
            <rect width="24" height="5.333" y="5.333" fill="#fff" />
            <rect width="24" height="5.334" y="10.666" fill="#007A3D" />
            <polygon points="0,0 12,8 0,16" fill="#CE1126" />
        </svg>
    </span>
</template>

<script>
export default {
    name: "PalestineFlag",
    props: {
        large: { type: Boolean, default: false },
    },
};
</script>
