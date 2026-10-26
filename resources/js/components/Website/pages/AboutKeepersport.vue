<template>
    <section class="ks-about-page" :lang="pageLang">
        <div class="ks-about-wrap">
            <h1>{{ t.pageTitle }}</h1>

            <div class="ks-about-tabs">
                <button
                    v-for="(tab, index) in t.tabs"
                    :key="tab.key"
                    :class="['ks-about-tab', { active: activeTab === index }]"
                    @click="selectTab(index)"
                    type="button"
                >
                    {{ tab.label }}
                </button>
            </div>

            <section v-if="currentTab.key === 'about'" class="ks-about-block ks-about-block--about">
                <h2>{{ t.aboutTitle }}</h2>
                <h3>{{ t.philosophyTitle }}</h3>
                <p>{{ t.philosophyText }}</p>
                <h3>{{ t.historyTitle }}</h3>
                <p v-for="(p, i) in t.historyParagraphs" :key="`h-${i}`">{{ p }}</p>
                <h3>{{ t.milestonesTitle }}</h3>
                <p v-for="(m, i) in t.milestonesLong" :key="`ml-${i}`">{{ m }}</p>
            </section>

            <section v-else class="ks-about-block ks-about-block--day">
                <div v-if="dayLoading" class="ks-day-muted">
                    {{ pageLang === "ar" ? "جاري التحميل…" : "Loading…" }}
                </div>
                <div v-else-if="dayHtml" class="ks-day-content" v-html="dayHtml"></div>
                <p v-else class="ks-day-muted">
                    {{ pageLang === "ar" ? "لا يوجد محتوى لهذه الصفحة بعد." : "No content for this page yet." }}
                </p>
            </section>
        </div>
    </section>
</template>

<script>
import { api } from "@/axiosUser.js";

const COPY = {
    en: {
        pageTitle: "ABOUT KEEPERsport",
        tabs: [
            { key: "about", label: "About Us" },
            { key: "day", label: "Goalkeeper Day" },
        ],
        aboutTitle: "ABOUT KEEPERsport",
        philosophyTitle: "PHILOSOPHY",
        philosophyText:
            "We are there for every goalkeeper. Our philosophy is standing behind every KEEPER. We thrive on good customer service, love for goalkeeping in general and we are very passionate about our products. Our unique motto #KeepItAll sends everyone this message. Our decades of professional experience is one of our best advantage over others. We stand behind every product we sell. KEEPERsport represents a high-quality quality assurance of all products offered in our shops through a detailed prior inspection is as much a duty for us as a matter of honor among athletes. We are team players and each and every customer of ours is part of our team!",
        historyTitle: "HISTORY",
        historyParagraphs: [
            "Keepersport was founded in 2000 by the Austrian ex-professional KEEPER and goalkeeping coach Georg Heu (Admira Wacker) as the successor to Flying KEEPER, which was also founded in 1999 by Georg Heu. The company is run by Georg Heu, CEO Martin Krenn (also former professional goalkeeper) and financial expert Franz Kalla (General Manager Vienna Capitals / Ice Hockey).",
            "KEEPERsport started its online shop at the millennium. Targeted telephone marketing, competent advice and an unrivalled passion for goalkeeping led to rapid growth. Since 2008, in addition to the headquarters in Kottingbrunn, Austria, there has also been a sister company in Germany based in Augsburg-Bergheim, lead by managing director Martin Krenn and ex-professional keeper Christian Krieglmeier (FC Augsburg, SSV Reutlingen and SSV Ulm 1846) as the sales manager.",
            "KEEPERsport's base of operation is called the KEEPERbase, situated in in Kottingbrunn, Lower Austria. It is a 1,250 square meter building, that features a completely new center for shipping logistics as well as new, large-scale office space for the perfect working atmosphere.",
            "The Heart of Goalkeeping - the nickname of the KEEPERbase - even has its own grass football field and a 250 square meters goalkeeper shop. KEEPERsport now operates franchise companies and partner agencies in many European countries: Our goalkeeper specialists are represented in Greece, France, the Netherlands, Belgium, Poland, Italy, Switzerland, Slovakia, the Czech Republic, Croatia and Hungary.",
            "KEEPERsport underwear has been a constant companion for many world-class goalkeepers for years. Numerous renowned professionals trust the training textiles made by KEEPERsport. In addition to our current partners, they also include the 4-time world goalkeeper Manuel Neuer.",
            "The Varan goalkeeper gloves have evolved from excellent collaborations with many amateur and professional goalkeepers. That is why all KEEPERsport goalkeeper glove models are designed for both professional and amateur users.",
        ],
        milestonesTitle: "MILESTONES IN THE COMPANY'S HISTORY",
        milestonesLong: [
            "1999: Founding of Flying KEEPER by Georg Heu, the establishment of a customer base in Austria.",
            "2000: First online shop.",
            "2001 to 2003: Targeted telephone marketing activities with operators. Well-known brands such as adidas, PUMA or NIKE became included in the program.",
            "2003 to 2006: Opening of franchise partner companies in Italy, Poland, the Czech Republic, Slovakia and Hungary.",
            "2006: New company location and new type of business: KEEPERsport GmbH (Heu, Krenn, Kalla).",
            "2008: Launch of KEEPERsport Germany and relaunch of the homepages.",
            "2009: Opening of KEEPERsport France (franchise company).",
            "2010: KEEPERsport Slovenia opens, English webshop goes online, KEEPERsport Marketing GmbH is founded.",
            "2011: Launch of the KEEPERsport agency in the Netherlands and new headquarters in Bolzano, Italy.",
            "2012: Launch of KEEPERsport Switzerland and launch of KEEPERgallery.",
            "2013: Launch of KEEPERtraining and premiere of Varan goalkeeper gloves in several top leagues.",
            "2014: Live #GloveSpot during the 2014 World Cup and launch of personalization online shop tool.",
            "2015: Launch of #KeepItAll company slogan and KEEPERsport Belgium.",
            "2016: VARAN4 CountryEditions, KEEPERsport Greece opening, first KEEPERbattle EuroTour, and opening of KEEPERbase.",
            "2017: Launch VARAN5 and on-pitch premiere at a major official FIFA tournament.",
            "2018: Launch of KEEPERcast with weekly episodes.",
            "2019: Launch VARAN6 in top leagues.",
            "2021: Product range expansion (25,000+ items) and launch of Glove Creator.",
        ],
        dayFallbackTitle: "Goalkeeper Day",
    },
    ar: {
        pageTitle: "عن KEEPERsport",
        tabs: [
            { key: "about", label: "من نحن" },
            { key: "day", label: "يوم حارس المرمى" },
        ],
        aboutTitle: "عن KEEPERsport",
        philosophyTitle: "الفلسفة",
        philosophyText:
            "نحن موجودون لكل حارس مرمى. فلسفتنا هي الوقوف خلف كل حارس. نعتمد على خدمة عملاء قوية وحب حقيقي لحراسة المرمى وشغف كبير بمنتجاتنا. شعارنا #KeepItAll يعبّر عن هذه الرسالة. خبرتنا الطويلة ميزة أساسية لنا، ونقف خلف كل منتج نبيعه مع فحص جودة دقيق قبل الطرح.",
        historyTitle: "التاريخ",
        historyParagraphs: [
            "تأسست KEEPERsport عام 2000 بواسطة الحارس النمساوي السابق Georg Heu كامتداد لـ Flying KEEPER (1999).",
            "مع بداية الألفية أطلقت الشركة متجرها الإلكتروني، وساهم التسويق الهاتفي والخبرة المتخصصة والشغف الكبير بحراسة المرمى في نمو سريع.",
            "منذ 2008، إلى جانب المقر في Kottingbrunn بالنمسا، توسعت الشركة إلى شركات شقيقة وتمثيل في دول أوروبية عديدة.",
            "مقر KEEPERbase في Kottingbrunn يضم مركز لوجستي ومساحات عمل ومتجرًا متخصصًا بمساحة كبيرة للحراس.",
            "منتجات KEEPERsport وخدمة العملاء موجهة خصيصًا لاحتياجات حارس المرمى العصري، مع تطوير مستمر لقفازات Varan بالتعاون مع محترفين وهواة.",
        ],
        milestonesTitle: "المحطات الرئيسية في تاريخ الشركة",
        milestonesLong: [
            "1999: تأسيس Flying KEEPER بواسطة Georg Heu وبناء قاعدة عملاء في النمسا.",
            "2000: إطلاق أول متجر إلكتروني.",
            "2001-2003: توسع تسويقي ودخول علامات كبرى مثل adidas وPUMA وNIKE.",
            "2003-2006: افتتاح شركات شراكة في إيطاليا وبولندا والتشيك وسلوفاكيا والمجر.",
            "2006: التحول إلى KEEPERsport GmbH.",
            "2008: إطلاق KEEPERsport ألمانيا وإعادة إطلاق المواقع.",
            "2009: افتتاح KEEPERsport فرنسا.",
            "2010: إطلاق KEEPERsport سلوفينيا والمتجر الإنجليزي وتأسيس KEEPERsport Marketing GmbH.",
            "2011: إطلاق وكالة هولندا والمقر الإيطالي الجديد في بولزانو.",
            "2012: إطلاق KEEPERsport سويسرا وقسم KEEPERgallery.",
            "2013: إطلاق KEEPERtraining وظهور قفازات Varan في دوريات كبرى.",
            "2014: Live #GloveSpot في كأس العالم وإطلاق أداة التخصيص.",
            "2015: إطلاق شعار #KeepItAll والتوسع المتجاوب وافتتاح بلجيكا.",
            "2016: VARAN4 CountryEditions، افتتاح اليونان، أول KEEPERbattle EuroTour، وافتتاح KEEPERbase.",
            "2017: إطلاق VARAN5 والظهور في بطولة FIFA رسمية.",
            "2018: إطلاق KEEPERcast بحلقات أسبوعية.",
            "2019: إطلاق VARAN6 في دوريات كبرى.",
            "2021: توسعة المجموعة إلى أكثر من 25,000 منتج وإطلاق Glove Creator.",
        ],
        dayFallbackTitle: "يوم حارس المرمى",
    },
};

export default {
    name: "AboutKeepersport",
    data() {
        return {
            activeTab: 0,
            dayLoading: false,
            dayPage: null,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        t() {
            return COPY[this.pageLang] || COPY.en;
        },
        currentTab() {
            return this.t.tabs[this.activeTab] || this.t.tabs[0];
        },
        dayTitle() {
            const translations = this.dayPage?.translations || [];
            const current = translations.find((row) => row.locale === this.locale);
            return current?.name || this.t.dayFallbackTitle;
        },
        dayHtml() {
            const translations = this.dayPage?.translations || [];
            const current = translations.find((row) => row.locale === this.locale);
            return current?.description || this.dayPage?.description || "";
        },
    },
    watch: {
        locale() {
            this.activeTab = 0;
            this.fetchDayPage();
        },
        activeTab(index) {
            if ((this.t.tabs[index] || {}).key === "day" && !this.dayPage && !this.dayLoading) {
                this.fetchDayPage();
            }
        },
        "$route.query.tab": {
            immediate: true,
            handler(tab) {
                if (!tab) return;
                const normalized = String(tab).toLowerCase();
                const index = this.t.tabs.findIndex(
                    (t) => t.key === normalized || t.label.toLowerCase() === normalized
                );
                if (index >= 0) this.activeTab = index;
            },
        },
    },
    mounted() {
        if (this.currentTab.key === "day") {
            this.fetchDayPage();
        }
    },
    methods: {
        selectTab(index) {
            this.activeTab = index;
            const key = this.t.tabs[index]?.key;
            const query = { ...this.$route.query };
            if (key === "day") {
                query.tab = "day";
                this.fetchDayPage();
            } else {
                delete query.tab;
            }
            this.$router.replace({ query }).catch(() => {});
        },
        async fetchDayPage() {
            this.dayLoading = true;
            try {
                const { data } = await api.get("Pages/getByKey", {
                    params: { key: "goalkeeper-day" },
                });
                this.dayPage = data?.items ?? null;
            } catch (_) {
                this.dayPage = null;
            } finally {
                this.dayLoading = false;
            }
        },
    },
};
</script>

<style scoped>
.ks-about-page { background: #fff; color: #111; padding: 28px 16px 52px; }
.ks-about-wrap { max-width: 1140px; margin: 0 auto; }
h1 { margin: 0 0 14px; font-size: clamp(30px, 3.4vw, 46px); font-weight: 900; text-transform: uppercase; }
.ks-about-tabs {
    display: flex;
    width: 100%;
    overflow-x: auto;
    background: #2f3438;
    border: 1px solid #4a4f54;
    margin-bottom: 14px;
}
.ks-about-tab {
    border: 0;
    background: transparent;
    color: #cfd4d8;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.02em;
    text-transform: none;
    padding: 14px 18px;
    white-space: nowrap;
    cursor: pointer;
}
.ks-about-tab.active {
    color: #fff;
    text-decoration: underline;
    text-underline-offset: 6px;
    text-decoration-thickness: 2px;
}
.ks-about-block { border: 1px solid #e7e7e7; border-radius: 8px; padding: 16px; background: #fafafa; }
.ks-about-block--about { background: #fff; border-color: #efefef; }
.ks-about-block--day { background: #fff; }
h2 { margin: 0 0 10px; font-size: 24px; font-weight: 800; }
h3 { margin: 10px 0 6px; font-size: 18px; font-weight: 800; }
p { margin: 0 0 8px; line-height: 1.7; color: #3e3e3e; }
.ks-day-muted { margin: 0; color: #666; }
.ks-day-content :deep(p),
.ks-day-content :deep(li) { line-height: 1.7; color: #3e3e3e; }
.ks-day-content :deep(h2),
.ks-day-content :deep(h3) { color: #111; margin-top: 1.2em; }
.ks-day-content :deep(img) { max-width: 100%; height: auto; border-radius: 6px; }
</style>
