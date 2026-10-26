<template>
    <section class="ks-glove-care-page" :lang="pageLang">
        <div class="ks-glove-care-wrap">
            <header class="ks-glove-care-hero">
                <h1>{{ t.title }}</h1>
                <p>{{ t.intro }}</p>
            </header>

            <div class="ks-glove-care-steps">
                <article
                    v-for="(step, idx) in t.steps"
                    :key="step.title"
                    class="ks-glove-care-step"
                >
                    <div class="ks-step-index">Step {{ idx + 1 }}</div>
                    <h3>{{ step.title }}</h3>
                    <p>{{ step.text }}</p>
                </article>
            </div>

            <section class="ks-glove-care-more">
                <h2>{{ t.moreTitle }}</h2>
                <div class="ks-glove-care-more-grid">
                    <article v-for="item in t.more" :key="item.title" class="ks-more-card">
                        <h4>{{ item.title }}</h4>
                        <p>{{ item.text }}</p>
                    </article>
                </div>
            </section>
        </div>
    </section>
</template>

<script>
const COPY = {
    en: {
        title: "Proper glove care",
        intro: "Goalkeeper gloves are expensive. With correct care you protect grip, increase durability, and keep gloves performing for longer.",
        steps: [
            {
                title: "Remove protective foil before first use",
                text: "Clean palms with lukewarm water before first use to remove production residue. If your gloves have foil, let air in first and remove foil after applying lukewarm water.",
            },
            {
                title: "Thorough cleaning",
                text: "Latex is fragile. Use glove cleaner and spray 4-5 times before first use. Rub over palm foam and rinse with lukewarm water.",
            },
            {
                title: "Correct drying",
                text: "Dry naturally and hang gloves by fingers so moisture does not collect inside. Keep away from direct heat and sunlight.",
            },
            {
                title: "Remove dirt safely",
                text: "If dirt remains after warm water and cleaner, use a microfiber cloth. Do not wash gloves in a washing machine.",
            },
            {
                title: "Wet weather care",
                text: "In rain or wet conditions, use grip-support products made for wet weather. Clean gloves again after use to avoid residue buildup.",
            },
            {
                title: "Palm abrasion handling",
                text: "Worn palms can still be used for training with proper grip enhancers and regular cleaning after sessions.",
            },
            {
                title: "Safe storage",
                text: "Store in a breathable bag in a cool, dry place. Never keep gloves wet inside closed compartments for long periods.",
            },
        ],
        moreTitle: "More recommendations",
        more: [
            {
                title: "Which cut suits me?",
                text: "Negative, roll finger, regular, and hybrid cuts all offer different fit and control feeling.",
            },
            {
                title: "Palm foam guide",
                text: "Choose palm foam by weather and surface for the best balance between grip and durability.",
            },
            {
                title: "Glove models",
                text: "Models differ by design, materials, and price. Compare model families before buying.",
            },
        ],
    },
    ar: {
        title: "العناية الصحيحة بالقفازات",
        intro: "قفازات الحارس مكلفة، والعناية الصحيحة تحافظ على التماسك وتطيل عمر القفاز وتبقي الأداء قويًا لفترة أطول.",
        steps: [
            {
                title: "إزالة الغلاف الواقي قبل أول استخدام",
                text: "نظّف راحة اليد بماء فاتر قبل أول استخدام لإزالة بقايا التصنيع. إذا كان هناك غلاف واقٍ، أدخل بعض الهواء أولًا ثم أزله بعد ترطيب خفيف بالماء الفاتر.",
            },
            {
                title: "تنظيف عميق",
                text: "اللاتكس مادة حساسة. استخدم منظف القفازات مع 4-5 رشات قبل أول استخدام، وافرك راحة اليد ثم اشطف بماء فاتر.",
            },
            {
                title: "التجفيف الصحيح",
                text: "جفف القفازات طبيعيًا وعلّقها من جهة الأصابع حتى لا تتجمع الرطوبة داخلها، مع تجنب الشمس المباشرة والحرارة العالية.",
            },
            {
                title: "إزالة الأوساخ بأمان",
                text: "إذا بقيت أوساخ بعد الماء والمنظف، استخدم قطعة مايكروفايبر. لا تغسل القفازات في الغسالة.",
            },
            {
                title: "العناية في الطقس الماطر",
                text: "في الأمطار أو البلل استخدم منتجات دعم التماسك المخصصة للطقس الرطب، ثم نظف القفازات بعد الاستخدام لتجنب بقايا المواد.",
            },
            {
                title: "التعامل مع تآكل راحة اليد",
                text: "تآكل راحة اليد لا يعني نهاية القفاز. يمكن استخدامه للتدريب مع منتجات تعزيز التماسك والتنظيف المنتظم.",
            },
            {
                title: "التخزين الآمن",
                text: "خزّن القفازات في حقيبة قابلة للتهوية وفي مكان بارد وجاف. لا تتركها مبللة داخل أماكن مغلقة لفترة طويلة.",
            },
        ],
        moreTitle: "توصيات إضافية",
        more: [
            {
                title: "أي قصّة تناسبني؟",
                text: "قصّات Negative وRoll Finger وRegular والقصّات الهجينة تعطي إحساسًا وملاءمة مختلفة.",
            },
            {
                title: "دليل لاتكس راحة اليد",
                text: "اختر نوع اللاتكس حسب الطقس ونوع الأرضية لتحقيق أفضل توازن بين التماسك والتحمل.",
            },
            {
                title: "موديلات القفازات",
                text: "تختلف الموديلات في التصميم والخامات والسعر. قارن بين العائلات قبل الشراء.",
            },
        ],
    },
};

export default {
    name: "GloveCarePage",
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
    },
};
</script>

<style scoped>
.ks-glove-care-page {
    background: #fff;
    color: #111;
    padding: 32px 16px 46px;
}

.ks-glove-care-wrap {
    max-width: 1100px;
    margin: 0 auto;
}

.ks-glove-care-hero h1 {
    margin: 0 0 10px;
    font-size: clamp(30px, 3vw, 46px);
    font-weight: 900;
    text-transform: uppercase;
}

.ks-glove-care-hero p {
    margin: 0 0 18px;
    color: #3f3f3f;
    line-height: 1.7;
    max-width: 900px;
}

.ks-glove-care-steps {
    display: grid;
    gap: 12px;
}

.ks-glove-care-step {
    border: 1px solid #e4e4e4;
    border-radius: 8px;
    background: #fafafa;
    padding: 14px 16px;
}

.ks-step-index {
    display: inline-block;
    margin-bottom: 5px;
    color: #6a6a6a;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.ks-glove-care-step h3 {
    margin: 0 0 6px;
    font-size: 20px;
    font-weight: 800;
}

.ks-glove-care-step p {
    margin: 0;
    line-height: 1.65;
    color: #444;
}

.ks-glove-care-more {
    margin-top: 22px;
}

.ks-glove-care-more h2 {
    margin: 0 0 10px;
    font-size: 24px;
    font-weight: 800;
}

.ks-glove-care-more-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.ks-more-card {
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 12px;
    background: #fff;
}

.ks-more-card h4 {
    margin: 0 0 6px;
    font-size: 18px;
    font-weight: 800;
}

.ks-more-card p {
    margin: 0;
    color: #4a4a4a;
    line-height: 1.6;
}

@media (max-width: 860px) {
    .ks-glove-care-more-grid {
        grid-template-columns: 1fr;
    }
}
</style>
