const IMG = "/site/assets/img/misbar";

function albumPhotos(dir, names) {
    return names.map((name) => `${IMG}/albums/${dir}/${name}`);
}

function numbered(prefix, count) {
    return Array.from({ length: count }, (_, i) => `${prefix}-${String(i + 1).padStart(2, "0")}.jpeg`);
}

export default {
    hero: {
        slides: [
            {
                image: `${IMG}/photo-bp.jpeg`,
                chip: "hero.palestine",
                title: "hero1.title",
                text: "hero1.text",
                button: { label: "hero1.btn", section: "about" },
            },
            {
                image: `${IMG}/photo-supplies.jpeg`,
                title: "hero2.title",
                text: "hero2.text",
                button: { label: "hero2.btn", section: "services" },
            },
            {
                image: `${IMG}/photo-28.jpeg`,
                title: "hero3.title",
                text: "hero3.text",
                button: { label: "hero3.btn", section: "contact" },
            },
        ],
        interval: 6000,
    },

    pillars: [
        { icon: "fas fa-book-medical", title: "pillars.learn.title", text: "pillars.learn.text" },
        { icon: "fas fa-hand-holding-medical", title: "pillars.practice.title", text: "pillars.practice.text" },
        { icon: "fas fa-flask", title: "pillars.research.title", text: "pillars.research.text" },
        { icon: "fas fa-seedling", title: "pillars.grow.title", text: "pillars.grow.text" },
    ],

    cta: {
        title: "cta.title",
        text: "cta.text",
        button: { label: "cta.btn", section: "partners" },
    },

    about: {
        title: "about.title",
        lead: "about.lead",
        heading: "about.heading",
        paragraphs: ["about.p1"],
        points: ["about.li1", "about.li2", "about.li3"],
        closing: "about.p2",
        video: {
            poster: "/site/assets/video/misbar-training-poster.jpg",
            src: "/site/assets/video/misbar-training.mp4",
        },
    },

    services: {
        title: "offer.title",
        lead: "offer.lead",
        items: [
            { icon: "fas fa-user-nurse", title: "offer.s1.title", text: "offer.s1.text" },
            { icon: "fas fa-syringe", title: "offer.s2.title", text: "offer.s2.text" },
            { icon: "fas fa-laptop-medical", title: "offer.s3.title", text: "offer.s3.text" },
            { icon: "fas fa-heart", title: "offer.s4.title", text: "offer.s4.text" },
        ],
    },

    story: {
        title: "story.title",
        lead: "story.lead",
        heading: "story.h",
        text: "story.p",
        image: `${IMG}/photo-bp.jpeg`,
    },

    vision: {
        title: "vision.title",
        lead: "vision.lead",
        heading: "vision.h",
        text: "vision.p",
        image: `${IMG}/photo-supplies.jpeg`,
    },

    plan: {
        title: "plan.title",
        lead: "plan.lead",
        text: "plan.p",
    },

    partners: {
        title: "partners.title",
        lead: "partners.lead",
        text: "partners.p",
        options: [
            { icon: "fas fa-hand-holding-dollar", title: "partners.m1.title", text: "partners.m1.text" },
            { icon: "fas fa-toolbox", title: "partners.m2.title", text: "partners.m2.text" },
            { icon: "fas fa-building", title: "partners.m3.title", text: "partners.m3.text" },
        ],
    },

    gallery: {
        title: "gallery.title",
        lead: "gallery.lead",
        albums: [
            {
                key: "nursing",
                filter: "gallery.filter.nursing",
                title: "gallery.album.nursing.title",
                lead: "gallery.album.nursing.lead",
                photos: ["photo-bp.jpeg", "photo-bp-group.jpeg", "photo-supplies.jpeg", "photo-23.jpeg", "photo-28.jpeg"].map(
                    (name) => `${IMG}/${name}`
                ),
            },
            {
                key: "suturing",
                filter: "gallery.filter.suturing",
                title: "gallery.album.suturing.title",
                lead: "gallery.album.suturing.lead",
                photos: albumPhotos("suturing", ["1.jpg", "2.jpg", "4.jpg", "5.jpg", "6.jpg", "7.jpg", "9.jpg", "11.jpg"]),
            },
            {
                key: "ventilation",
                filter: "gallery.filter.ventilation",
                title: "gallery.album.ventilation.title",
                lead: "gallery.album.ventilation.lead",
                photos: albumPhotos("ventilation", numbered("vent", 5)),
            },
            {
                key: "taghriz",
                filter: "gallery.filter.taghriz",
                title: "gallery.album.taghriz.title",
                lead: "gallery.album.taghriz.lead",
                photos: albumPhotos("taghriz", numbered("taghriz", 14)),
            },
            {
                key: "firstaid",
                filter: "gallery.filter.firstaid",
                title: "gallery.album.firstaid.title",
                lead: "gallery.album.firstaid.lead",
                photos: albumPhotos("firstaid", numbered("firstaid", 13)),
            },
        ],
    },

    contact: {
        title: "contact.title",
        lead: "contact.lead",
        address: "contact.address",
        person: "contact.person",
    },
};
