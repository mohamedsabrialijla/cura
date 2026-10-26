export default {
    name: "CURA Medical Team",
    logo: "/site/assets/img/misbar/logo-transparent.png",

    contact: {
        phone: "+970 567 744 746",
        phoneHref: "tel:+970567744746",
        email: "info@cura-ps.org",
        instagram: { label: "@cura_team", url: "https://instagram.com/cura_team" },
        telegram: { label: "Telegram", url: "https://t.me/cura_team" },
    },

    socials: [
        { icon: "bi-instagram", label: "Instagram", url: "https://instagram.com/cura_team", external: true },
        { icon: "bi-telegram", label: "Telegram", url: "https://t.me/cura_team", external: true },
        { icon: "bi-envelope", label: "Email", url: "mailto:info@cura-ps.org" },
        { icon: "bi-telephone", label: "Phone", url: "tel:+970567744746" },
    ],

    nav: [
        { section: "hero", label: "nav.home" },
        { section: "about", label: "nav.about" },
        { section: "services", label: "nav.offer" },
        { section: "story", label: "nav.story" },
        { section: "vision", label: "nav.vision" },
        { section: "gallery", label: "nav.gallery" },
        { section: "contact", label: "nav.contact" },
    ],

    headerCta: { section: "partners", label: "nav.cta" },

    footerLinks: [
        { section: "about", label: "nav.about" },
        { section: "services", label: "nav.offer" },
        { section: "partners", label: "nav.cta" },
        { section: "contact", label: "nav.contact" },
    ],
};
