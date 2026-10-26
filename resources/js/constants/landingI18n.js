/**
 * Home page (Landing) copy — Arabic / English only, keyed by route locale.
 * Product model names stay international; UI strings are translated.
 */

/**
 * Replace `__token__` placeholders in manifesto HTML with real URLs.
 * @param {string} html
 * @param {Record<string, string>} tokenMap — keys like `__gloves__`
 */
export function resolveManifestoHtml(html, tokenMap) {
    if (!html) return "";
    let out = html;
    for (const [token, url] of Object.entries(tokenMap)) {
        if (url) out = out.split(token).join(url);
    }
    return out;
}

export const LANDING_COPY = {
    en: {
        page_lang: "en",
        hero_title: "uhlsport planet edition",
        hero_cta: ">> DISCOVER MORE <<",
        hero_img_alt: "uhlsport planet edition",
        planet_strip_aria: "uhlsport goalkeeper gloves — planet edition",
        planet_brands_heading: "TOP BRANDS, LATEST MODELS",
        brand_strip_aria: "Top brands slider",
        scroll_prev: "Scroll back",
        scroll_next: "Scroll forward",
        planet_slider_cta: ">> DISCOVER MORE <<",
        banner_deals_alt: "Banner deals",
        deal_week_title: "DEAL OF THE WEEK",
        deal_week_img_alt: "Deal of the week",
        mega_sale_title: "MEGA SALE",
        mega_sale_img_alt: "Mega sale",
        mega_sale_lead:
            "Discover our special offers and take advantage of real mega-discounts made for those who want to spend less without giving up quality. It is the right place for those looking for convenient products and smart shopping. You are sure to find a real bargain you will not want to miss among our proposals.",
        show_more_cta: ">> SHOW MORE <<",
        category_strip_aria: "Featured categories",
        categories: {
            "/goalkeeper-gloves": "Goalkeeper gloves",
            "/football-boots": "Football boots",
            "/goalkeeper-apparel": "Goalkeeper apparel",
            "/goalkeeper-equipment": "Goalkeeper equipment",
            "/teamsport": "Teamsport",
            "/sale": "Sale",
        },
        news_adidas_title: "Latest news — adidas",
        news_adidas_img_alt: "Latest news adidas",
        news_reusch_title: "Latest news — Reusch",
        news_reusch_img_alt: "Reusch Attrakt 2026",
        discover_cta: ">> DISCOVER MORE <<",
        whiteout_alt: "KEEPERsport whiteout",
        freddo_alt: "Technical apparel",
        freddo_lead:
            "High-performance materials, carefully engineered technologies and advanced protection systems: every collection is designed to deliver control, comfort and maximum safety in any playing condition. From thermal layers to protection for hard pitches, every piece supports consistent, reliable performance at the highest level.",
        battle_banner_alt: "KeeperBattle banner",
        battle_header_alt: "KeeperBattle",
        battle_p1_html:
            "<strong>KeeperBattle</strong> is not just a tournament.<br />It is a pure, direct, intense challenge. <strong>Goalkeeper against goalkeeper</strong>, no filters, no excuses.",
        battle_p2_html:
            "Three events, three different contexts, but one idea:<br />putting the goalkeeper at the centre of the game and turning every action into pure adrenaline.",
        battle_p3_html:
            "From the <strong>sand of Torre Pedrera | 30/05 |</strong>, where every move demands strength and sacrifice",
        battle_more_cta: ">> SHOW MORE <<",
        teasers_aria: "Services and community",
        teaser_bepro_alt: "Be a Pro",
        teaser_bepro_title: "Be a Pro!",
        teaser_circle_alt: "Circle of Pros",
        teaser_circle_title: "#CircleOfPros",
        teaser_save_alt: "Save 10€",
        teaser_save_title: "Save €10.00!",
        teaser_subscribe_cta: ">> Subscribe <<",
        teaser_social_alt: "Social",
        teaser_social_title: "Stay up to date",
        teaser_social_cta: ">> Social media <<",
        teaser_care_alt: "Care and maintenance",
        teaser_care_title: "Care & maintenance",
        teaser_gkd_alt: "Goalkeeper Day",
        teaser_gkd_title: "GOALKEEPERDAY",
        teaser_gkd_cta: ">> 11/11 <<",
        training_aria: "KEEPERtraining",
        training_label: "KEEPERtraining",
        training_img_alt: "KEEPERtraining",
        technic_aria: "Technical goalkeeper apparel",
        technic_title_white: "TECHNICAL",
        technic_title_gold: "APPAREL",
        technic_desc:
            "High-performance materials, carefully engineered technologies and advanced protection systems: every collection is designed to give you control, comfort and maximum safety in any playing conditions.",
        technic_cover_alt: "Goalkeeper technical apparel",
        technic_strip_aria: "Technical apparel products",
        technic_slider_cta: ">> DISCOVER MORE <<",
        manifesto_title:
            "KEEPERsport — the largest online store for every goalkeeper!",
        manifesto_p1_html: `As a goalkeeper you are not just one of eleven. Being a goalkeeper means sweat, joy, tears, responsibility, fight, security, pain, victory, defeat, salvation, <strong>HERO</strong>. It is pure passion that unites us, and as a goalkeeper you deserve the best equipment too. More than 800 <a class="ks-manifesto-a" href="__gloves__" target="_blank" rel="noopener">goalkeeper gloves</a> in all cuts (<a class="ks-manifesto-a" href="__cut_neg__" target="_blank" rel="noopener">negative</a>, <a class="ks-manifesto-a" href="__cut_reg__" target="_blank" rel="noopener">flat</a>, <a class="ks-manifesto-a" href="__cut_roll__" target="_blank" rel="noopener">roll finger</a> or <a class="ks-manifesto-a" href="__cut_hyb__" target="_blank" rel="noopener">hybrid</a>), or <a class="ks-manifesto-a" href="__gloves_stay__" target="_blank" rel="noopener">goalkeeper gloves with finger protection</a> for beginners, children, amateur and professional keepers. In our shop you will find the best brands (adidas, PUMA, NIKE, KEEPERsport, uhlsport, rehab, reusch, Sells and many more) for keepers. In particular, our <a class="ks-manifesto-a" href="__varan__" target="_blank" rel="noopener">Varan goalkeeper glove</a> collection is built on 20 years of product experience. These <a class="ks-manifesto-a" href="__gloves__" target="_blank" rel="noopener">goalkeeper gloves</a> are available for all surfaces (natural grass, artificial turf, hard ground and indoor) in all sizes for children and adults. If you need help finding the right glove cut, the right model or the perfect surface, our <a class="ks-manifesto-a" href="__glove_guide__" target="_blank" rel="noopener">Glove guide</a> will help you.`,
        manifesto_p2_html: `We also offer a selection of more than 700 <a class="ks-manifesto-a" href="__shoes__" target="_blank" rel="noopener">football boots</a> from the latest collections, plus over 800 <a class="ks-manifesto-a" href="__textiles__" target="_blank" rel="noopener">textile products for goalkeepers</a> (<a class="ks-manifesto-a" href="__jerseys__" target="_blank" rel="noopener">goalkeeper jerseys</a>, <a class="ks-manifesto-a" href="__pants__" target="_blank" rel="noopener">goalkeeper trousers</a>, <a class="ks-manifesto-a" href="__underwear__" target="_blank" rel="noopener">goalkeeper underwear</a> with or without padding for high protection and goalkeeper sets with shirt, trousers and socks), training equipment for goalkeepers such as special goalkeeper bags, training accessories and <a class="ks-manifesto-a" href="__keeperdummy__" target="_blank" rel="noopener">KEEPERdummies</a>. On KEEPERsport you will find everything that makes your goalkeeper's heart beat faster. And all this at very reasonable prices, with a wide selection of products ON SALE with high discounts and offers.`,
        newsletter_title: "Newsletter",
        newsletter_placeholder: "Your email",
        newsletter_submit: "Subscribe",
        planet_product_titles: [
            "Cybertec Supersoft HN Night Edition",
            "Dynamic Impulse Advance W02",
            "Dynamic Impulse Superflash HN",
            "Absolutgrip HN Titanium",
            "Supergrip+ HN Bionik+",
            "Pure Alliance Absolutgrip HN",
        ],
    },
    ar: {
        page_lang: "ar",
        hero_title: "إصدار uhlsport بلانيت",
        hero_cta: "« اكتشف المزيد »",
        hero_img_alt: "إصدار uhlsport بلانيت",
        planet_strip_aria: "قفازات حارس المرمى uhlsport — إصدار بلانيت",
        planet_brands_heading: "أفضل العلامات، أحدث الموديلات",
        brand_strip_aria: "سلايدر الماركات",
        scroll_prev: "تحريك للخلف",
        scroll_next: "تحريك للأمام",
        planet_slider_cta: "« اكتشف المزيد »",
        banner_deals_alt: "بانر العروض",
        deal_week_title: "صفقة الأسبوع",
        deal_week_img_alt: "صفقة الأسبوع",
        mega_sale_title: "تخفيضات كبرى",
        mega_sale_img_alt: "تخفيضات كبرى",
        mega_sale_lead:
            "اكتشف عروضنا الخاصة واستفد من تخفيضات كبيرة مخصصة لمن يريد الإنفاق الأقل دون التنازل عن الجودة. المكان المناسب لمن يبحث عن منتجات بسعر مناسب وتسوق ذكي. ستجد بالتأكيد بين عروضنا صفقة لا تفوتك.",
        show_more_cta: "« عرض المزيد »",
        category_strip_aria: "فئات مميزة",
        categories: {
            "/goalkeeper-gloves": "قفازات حارس المرمى",
            "/football-boots": "أحذية كرة القدم",
            "/goalkeeper-apparel": "ملابس حارس المرمى",
            "/goalkeeper-equipment": "معدات حارس المرمى",
            "/teamsport": "ملابس الفريق",
            "/sale": "تخفيضات",
        },
        news_adidas_title: "جديد — adidas",
        news_adidas_img_alt: "جديد adidas",
        news_reusch_title: "جديد — Reusch",
        news_reusch_img_alt: "Reusch Attrakt 2026",
        discover_cta: "« اكتشف المزيد »",
        whiteout_alt: "KEEPERsport وايت أوت",
        freddo_alt: "ملابس تقنية",
        freddo_lead:
            "مواد عالية الأداء، وتقنيات مدروسة بدقة، وأنظمة حماية متطورة: كل مجموعة مصممة لتمنحك تحكمًا وراحة وأقصى أمان في أي ظروف لعب. من الطبقات الحرارية إلى الحماية للملاعب الصلبة، كل قطعة تدعم أداءً ثابتًا وموثوقًا على أعلى المستويات.",
        battle_banner_alt: "بانر KeeperBattle",
        battle_header_alt: "KeeperBattle",
        battle_p1_html:
            "<strong>KeeperBattle</strong> ليست مجرد بطولة.<br />إنها تحدٍ صريح ومباشر ومكثف. <strong>حارس مرمى ضد حارس مرمى</strong>، بلا فلتر ولا أعذار.",
        battle_p2_html:
            "ثلاث فعاليات، ثلاث ساحات مختلفة، وفكرة واحدة:<br />وضع حارس المرمى في قلب اللعبة وتحويل كل لحظة إلى أدرينالين خالص.",
        battle_p3_html:
            "من <strong>رمال توري بيدريرا | ٣٠/٠٥ |</strong> حيث يتطلب كل تحرك قوة وتضحية",
        battle_more_cta: "« عرض المزيد »",
        teasers_aria: "خدمات ومجتمع",
        teaser_bepro_alt: "كن محترفًا",
        teaser_bepro_title: "كن محترفًا!",
        teaser_circle_alt: "دائرة المحترفين",
        teaser_circle_title: "#CircleOfPros",
        teaser_save_alt: "وفر ١٠ يورو",
        teaser_save_title: "وفر ١٠٫٠٠ يورو!",
        teaser_subscribe_cta: "« اشترك »",
        teaser_social_alt: "وسائل التواصل",
        teaser_social_title: "ابقَ على اطلاع",
        teaser_social_cta: "« وسائل التواصل »",
        teaser_care_alt: "العناية والصيانة",
        teaser_care_title: "العناية والصيانة",
        teaser_gkd_alt: "يوم حارس المرمى",
        teaser_gkd_title: "GOALKEEPERDAY",
        teaser_gkd_cta: "« ١١/١١ »",
        training_aria: "KEEPERtraining",
        training_label: "KEEPERtraining",
        training_img_alt: "KEEPERtraining",
        technic_aria: "الملابس التقنية لحراس المرمى",
        technic_title_white: "ملابس",
        technic_title_gold: "تقنية",
        technic_desc:
            "مواد عالية الأداء، وتقنيات مدروسة بدقة، وأنظمة حماية متطورة: كل مجموعة مصممة لتمنحك تحكمًا وراحة وأقصى أمان في أي ظروف لعب.",
        technic_cover_alt: "ملابس تقنية لحراس المرمى",
        technic_strip_aria: "منتجات الملابس التقنية",
        technic_slider_cta: "« اكتشف المزيد »",
        manifesto_title:
            "KEEPERsport — أكبر متجر إلكتروني لكل حراس المرمى!",
        manifesto_p1_html: `لست كحارس مرمى واحدًا من أحد عشر فقط. أن تكون حارس مرمى يعني عرقًا وفرحًا ودموعًا ومسؤولية وصراعًا وأمانًا وألمًا وانتصارًا وخسارةً وخلاصًا، <strong>بطل</strong>. إنها الشغف الخالص الذي يجمعنا، وكحارس مرمى تستحق أفضل معدات أيضًا. أكثر من 800 <a class="ks-manifesto-a" href="__gloves__" target="_blank" rel="noopener">قفاز حارس مرمى</a> بكل القصّات (<a class="ks-manifesto-a" href="__cut_neg__" target="_blank" rel="noopener">سلبي</a>، <a class="ks-manifesto-a" href="__cut_reg__" target="_blank" rel="noopener">مسطح</a>، <a class="ks-manifesto-a" href="__cut_roll__" target="_blank" rel="noopener">رول فينغر</a> أو <a class="ks-manifesto-a" href="__cut_hyb__" target="_blank" rel="noopener">هجين</a>)، أو <a class="ks-manifesto-a" href="__gloves_stay__" target="_blank" rel="noopener">قفازات بأسنان للحماية</a> للمبتدئين والأطفال والهواة والمحترفين. في متجرنا أفضل العلامات (adidas وPUMA وNIKE وKEEPERsport وuhlsport وrehab وreusch وSells وغيرها) لحراس المرمى. خصوصًا مجموعة <a class="ks-manifesto-a" href="__varan__" target="_blank" rel="noopener">Varan</a> مبنية على 20 عامًا من خبرة المنتج. هذه <a class="ks-manifesto-a" href="__gloves__" target="_blank" rel="noopener">القفازات</a> متوفرة لكل الأسطح (عشب طبيعي، عشب صناعي، أرض صلبة وداخل الصالات) وبكل المقاسات للأطفال والكبار. إن احتجت مساعدة في اختيار القصّة أو الموديل أو السطح المناسب، سيساعدك <a class="ks-manifesto-a" href="__glove_guide__" target="_blank" rel="noopener">دليل القفازات</a>.`,
        manifesto_p2_html: `نقدم أيضًا أكثر من 700 <a class="ks-manifesto-a" href="__shoes__" target="_blank" rel="noopener">حذاء كرة قدم</a> من أحدث المجموعات، وأكثر من 800 منتج <a class="ks-manifesto-a" href="__textiles__" target="_blank" rel="noopener">نسيجية لحراس المرمى</a> (<a class="ks-manifesto-a" href="__jerseys__" target="_blank" rel="noopener">قمصان</a>، <a class="ks-manifesto-a" href="__pants__" target="_blank" rel="noopener">بناطيل</a>، <a class="ks-manifesto-a" href="__underwear__" target="_blank" rel="noopener">ملابس داخلية</a> مع أو بدون حشو لحماية أعلى وطقم قميص وبنطلون وشراب)، معدات تدريب مثل حقائب مخصصة وملحقات تدريب و<a class="ks-manifesto-a" href="__keeperdummy__" target="_blank" rel="noopener">KEEPERdummies</a>. على KEEPERsport كل ما ينبض قلب حارس مرماك. وكل ذلك بأسعار معقولة ومع تشكيلة واسعة من المنتجات في التخفيضات.`,
        newsletter_title: "النشرة البريدية",
        newsletter_placeholder: "بريدك الإلكتروني",
        newsletter_submit: "اشترك",
        planet_product_titles: [
            "Cybertec Supersoft HN Night Edition",
            "Dynamic Impulse Advance W02",
            "Dynamic Impulse Superflash HN",
            "Absolutgrip HN Titanium",
            "Supergrip+ HN Bionik+",
            "Pure Alliance Absolutgrip HN",
        ],
    },
};
