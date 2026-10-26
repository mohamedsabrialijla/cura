<template>
    <section class="ks-faq-page" :lang="locale === 'ar' ? 'ar' : 'en'">
        <div class="ks-faq-wrap">
            <h1 class="ks-faq-title">{{ copy.title }}</h1>
            <p class="ks-faq-subtitle">{{ copy.subtitle }}</p>
            <p v-if="loading" class="ks-faq-subtitle">{{ locale === "ar" ? "جاري التحميل..." : "Loading..." }}</p>

            <div v-else class="ks-faq-tabs" role="tablist" :aria-label="copy.title">
                <button
                    v-for="(section, sIndex) in copy.sections"
                    :key="section.slug || section.title"
                    type="button"
                    class="ks-faq-tab"
                    :class="{ 'is-active': activeTab === sIndex }"
                    role="tab"
                    :aria-selected="activeTab === sIndex"
                    @click="activeTab = sIndex"
                >
                    {{ section.title }}
                </button>
            </div>

            <div v-if="!loading" class="ks-faq-section">
                <h2 class="ks-faq-section-title">{{ activeSection.title }}</h2>
                <p v-if="activeSection.intro" class="ks-faq-section-intro">{{ activeSection.intro }}</p>

                <div
                    v-if="activeSection.cards && activeSection.cards.length"
                    class="ks-glove-cards"
                >
                    <article
                        v-for="card in activeSection.cards"
                        :key="card.title"
                        class="ks-glove-card"
                    >
                        <div class="ks-glove-card-head">
                            <img v-if="card.image" :src="card.image" :alt="card.title" loading="lazy" />
                            <span v-else>{{ card.head }}</span>
                        </div>
                        <div class="ks-glove-card-body">
                            <h4>{{ card.title }}</h4>
                            <p>{{ card.text }}</p>
                        </div>
                    </article>
                </div>

                <div
                    v-if="activeSection.richSections && activeSection.richSections.length"
                    class="ks-rich-blocks"
                >
                    <article
                        v-for="block in activeSection.richSections"
                        :key="block.title"
                        class="ks-rich-block"
                    >
                        <h3>{{ block.title }}</h3>
                        <p>{{ block.text }}</p>
                    </article>
                </div>

                <details
                    v-for="(item, iIndex) in activeSection.items"
                    :key="`${activeTab}-${iIndex}`"
                    class="ks-faq-item"
                    :open="iIndex === 0"
                >
                    <summary>{{ item.q }}</summary>
                    <p>{{ item.a }}</p>
                </details>
            </div>
        </div>
    </section>
</template>

<script>
import { KS_HOME_MEDIA } from "@/constants/keepersportHomeMedia";
import { api } from "@/axiosUser.js";

const FAQ_SECTIONS_EN = [
            {
                slug: "support",
                title: "Support",
                items: [
                    {
                        q: "What can I find in this FAQ?",
                        a: "You can find answers about ordering, payment, shipping, returns, sizing, glove guides, glove care, and customer support contacts.",
                    },
                    {
                        q: "I still need help. What should I do?",
                        a: "If your question is not answered here, contact customer service via live chat, email, WhatsApp, or phone during support hours.",
                    },
                ],
            },
            {
                slug: "i need help with my order",
                title: "I need help with my order",
                items: [
                    {
                        q: "How do I go about ordering?",
                        a: "Use search or browse categories, open the product detail page, select your size, add to cart, then proceed to checkout and complete billing, shipping, and payment details.",
                    },
                    {
                        q: "Have you received my order?",
                        a: "An order confirmation with your order number is automatically sent to your email address. If missing, check spam or verify that the email address was entered correctly.",
                    },
                    {
                        q: "What happens to my order after I have placed it?",
                        a: "After successful payment you see confirmation immediately. Order confirmation is sent by email and includes addresses, prices, payment method, and shipment details if split shipping applies.",
                    },
                    {
                        q: "Do I need an account to be able to place an order?",
                        a: "No. Guest checkout is available. Creating an account gives faster checkout and access to your order history.",
                    },
                    {
                        q: "Can I have my order changed or cancelled after I have placed it?",
                        a: "Changes or cancellation are only possible before processing begins. If processing is already underway, return the shipment for refund according to return policy.",
                    },
                    {
                        q: "Can I change the delivery or billing address later?",
                        a: "Address changes are generally not possible after order placement. If undeliverable, shipment returns and refund is processed after inspection.",
                    },
                    {
                        q: "How do I find my order number?",
                        a: "Order number is shown in order confirmation and invoice, usually consisting of 7 digits.",
                    },
                    {
                        q: "The size I want to order cannot be selected. Why?",
                        a: "Unavailable/sold-out sizes cannot be selected or added to cart.",
                    },
                    {
                        q: "How can I pre-order?",
                        a: "Items marked with pre-order symbol can be pre-ordered and are processed once stock arrives.",
                    },
                    {
                        q: "Can it happen that an item I ordered is no longer in stock?",
                        a: "In rare inventory mismatch cases, support provides suitable alternatives such as replacement options or refund handling.",
                    },
                ],
            },
            {
                slug: "payment options",
                title: "Payment options",
                items: [
                    {
                        q: "Which payment methods can I use to pay?",
                        a: "Credit/debit cards (MasterCard, VISA, American Express), digital wallets (Apple Pay, Google Pay etc.), PayPal, AmazonPay, and in some countries Klarna.",
                    },
                    {
                        q: "What happens when I pay?",
                        a: "As soon as payment is completed, items in your order are reserved for you.",
                    },
                    {
                        q: "Was my payment successful?",
                        a: "Payment confirmation appears immediately, followed by order confirmation email.",
                    },
                    {
                        q: "Where can I find the security code on my credit/debit card?",
                        a: "CVC/CVV security codes are on the back of most cards. For other card types, check with your bank.",
                    },
                    {
                        q: "Can I pay in installments?",
                        a: "This payment option is currently not offered.",
                    },
                    {
                        q: "When do I get my invoice?",
                        a: "Invoice is emailed on the day your package leaves the warehouse along with shipment tracking.",
                    },
                    {
                        q: "Can my company or football club be billed?",
                        a: "Yes, you can set different billing/shipping addresses at checkout. Post-issue invoice changes are generally not possible.",
                    },
                ],
            },
            {
                slug: "shipping information",
                title: "Shipping information",
                items: [
                    {
                        q: "What delivery company do you ship with?",
                        a: "Depending on location and items, shipments are delivered via GLS, DHL, UPS, or DPD.",
                    },
                    {
                        q: "What are the shipping costs?",
                        a: "Shipping costs are calculated after entering your address during checkout.",
                    },
                    {
                        q: "How long does delivery take?",
                        a: "In-stock items are dispatched in 1-2 working days. Shipping can take around 3-10 business days depending on destination.",
                    },
                    {
                        q: "I did not receive my full order. What happened?",
                        a: "Orders may be split into multiple shipments to deliver available items faster, with no extra split-shipment fee.",
                    },
                    {
                        q: "Do you ship to P.O. boxes?",
                        a: "No, P.O. box delivery is currently unavailable.",
                    },
                    {
                        q: "What happens if my package gets lost?",
                        a: "Support can investigate and provide refund or replacement options when shipment loss is confirmed.",
                    },
                    {
                        q: "Can I deliver to a packing station or parcel shop?",
                        a: "This delivery option is currently not offered.",
                    },
                    {
                        q: "Can shipping and billing addresses be different?",
                        a: "Yes, you can provide different billing and delivery addresses.",
                    },
                    {
                        q: "Do you deliver internationally?",
                        a: "Availability depends on selected country/storefront; choose the right country version for international delivery.",
                    },
                ],
            },
            {
                slug: "returns",
                title: "Returns",
                items: [
                    {
                        q: "How do I go about returning something?",
                        a: "We have a customer-friendly return policy. Personalized or used goods are excluded.\n\nFollow these steps:\n1) Return goods within 14 days from delivery.\n2) Items must be unworn, unwashed, and resellable.\n3) Keep original packaging and all labels.\n4) Do not tape directly on shoe boxes; wrap them in outer packaging.\n5) Return shipping is at your own expense; unpaid parcels are not accepted.\n6) If requirements are not met, return may be refused or refund may be reduced.\n7) If bulk/set discounts were applied, partial returns may recalculate discounts.\n\nReturn address:\nKEEPERsport GmbH\nAnzengrubergasse 26\n2542 Kottingbrunn\nAustria - Europe\n\nAfter quality check, refunds are processed within up to 14 days to your original payment method.",
                    },
                    {
                        q: "How long can I return something?",
                        a: "You can return items within 14 days from the day you received the package. Personalized or used goods are excluded from returns.",
                    },
                    {
                        q: "Can I return something from anywhere?",
                        a: "If you are returning from outside Europe, contact customer service before sending.\nBecause of distance, freight, taxes, and customs fees, some returns may not be recommended.",
                    },
                    {
                        q: "Can I exchange my item for a different size?",
                        a: "Direct exchange is not possible. Place a new order and return the original item.",
                    },
                    {
                        q: "When will I get my refund?",
                        a: "As soon as the returns department processes your parcel, you receive a credit memo and email confirmation.",
                    },
                    {
                        q: "Do I have to pay to return unwanted goods?",
                        a: "Yes. You cover return shipping for unwanted goods unless the item is faulty or wrong. Keep proof of postage.",
                    },
                    {
                        q: "How much time does it take overall to get my money back?",
                        a: "Due to customs and processing steps, total refund time may take around 3-4 weeks in some cases.",
                    },
                    {
                        q: "What Brexit changes do I need to pay attention to?",
                        a: "For UK returns, mark the parcel clearly with: Returned Goods - Failed Sale.\nAlso include your original order number on the outside and on customs declaration (CN22/CN23).\nRequired info:\nVAT: ATU 62801445\nEORI: AT EOS 1000 22085",
                    },
                ],
            },
            {
                slug: "glove-guide",
                title: "Glove guide",
                intro:
                    "As a goalkeeper, gloves are your most important tool. This glove guide helps you find the right model based on palm foam, cuts, fit, and proper care.",
                cards: [
                    {
                        head: "GLOVE CARE",
                        title: "Care and maintenance",
                        text: "Learn how to clean, dry, and store gloves to keep grip performance for longer.",
                        image: KS_HOME_MEDIA.teaserCura,
                    },
                    {
                        head: "GLOVE PALM",
                        title: "Different palm foams",
                        text: "From match grip to durable training latex, choose foam by surface and weather.",
                        image: KS_HOME_MEDIA.latestNewsGloveBanner,
                    },
                    {
                        head: "GLOVE CUTS",
                        title: "Different cuts",
                        text: "Negative Cut, Hybrid, Gunn Cut, or Regular Cut: each cut changes fit and feel.",
                        image: KS_HOME_MEDIA.teaserBePro,
                    },
                    {
                        head: "GLOVE SILOS",
                        title: "Different models",
                        text: "Compare all-round and high-tech gloves to match your level and play style.",
                        image: KS_HOME_MEDIA.teaserCircleOfPros,
                    },
                ],
                richSections: [
                    {
                        title: "Choosing the right palm foam",
                        text: "Match: strongest grip in dry weather. Aqua: balanced grip and durability for wet conditions. Training: robust for regular sessions. Indoor/Artificial: developed for hard surfaces with reduced abrasion.",
                    },
                    {
                        title: "Understanding different cuts",
                        text: "Negative Cut (NC) gives a tight fit. Hybrid/Power Cut combines constructions for control. Gunn Cut (GC/Rollfinger) increases contact area. Regular Cut (RC) offers a wider classic fit.",
                    },
                    {
                        title: "Find the right model",
                        text: "From beginner-friendly durable gloves to professional high-tech options with maximum grip and stability, choose what matches your game demands.",
                    },
                    {
                        title: "Additional tips",
                        text: "Strapless gloves provide modern close fit. Strap gloves add wrist fixation and stability. For kids gloves, prioritize fit, comfort, and protection.",
                    },
                ],
                items: [
                    {
                        q: "The ultimate glove guide: how do I find the ideal goalkeeper gloves?",
                        a: "Goalkeeper gloves are your most important tool. The right choice depends on your playing style, surface, weather, fit preference, and how often you train or play matches.",
                    },
                    {
                        q: "What should I evaluate first before buying?",
                        a: "Start with four core points: glove care and durability, palm foam type, cut type, and model family. These determine grip, comfort, control, and glove lifespan.",
                    },
                    {
                        q: "How do I choose the right palm foam?",
                        a: "Palm foam selection can be grouped into 4 categories:\n✔ Match: maximum grip in dry conditions, higher wear.\n✔ Aqua: balanced grip/durability, especially for wet conditions.\n✔ Training: robust and durable for regular use.\n✔ Indoor/Artificial: made for hard surfaces with reduced abrasion.",
                    },
                    {
                        q: "Which glove cuts should I compare?",
                        a: "Common cuts and feel:\n✔ Negative Cut (NC): tight fit, inner seams.\n✔ Hybrid / Power Cut: mix of cuts for control.\n✔ Gunn Cut (GC): round latex construction, larger contact area.\n✔ Regular Cut (RC): roomier fit and classic feel.",
                    },
                    {
                        q: "How do I choose the right model?",
                        a: "From all-round classics to high-tech gloves with improved grip and stability, choose based on your level and needs: beginner durability, advanced fit/control, or pro-level match performance.",
                    },
                    {
                        q: "With strap or without strap?",
                        a: "Strapless gloves (for example modern knit-entry styles) offer a close and modern fit. Strap models add extra wrist fixation and are often preferred for more wrist stability.",
                    },
                    {
                        q: "Do kids goalkeeper gloves need different considerations?",
                        a: "Yes. Kids gloves should prioritize safe fit, enough protection, and reliable grip while staying comfortable for developing hands and regular training sessions.",
                    },
                    {
                        q: "How important is glove size in this guide?",
                        a: "Very important. Correct size is essential for performance. Tight models should fit precisely, while wider cuts can provide more room and comfort.",
                    },
                ],
            },
            {
                slug: "goalkeeper glove models",
                title: "Goalkeeper glove models",
                items: [
                    {
                        q: "How are glove model lines different?",
                        a: "Model lines differ in fit, backhand construction, closure type, and overall contact feeling on the ball.",
                    },
                    {
                        q: "How can I compare two glove models quickly?",
                        a: "Compare palm foam type, cut, strap system, and intended use (match/training/all-round).",
                    },
                ],
            },
            {
                slug: "goalkeeper glove cuts",
                title: "Goalkeeper glove cuts",
                items: [
                    {
                        q: "What is a glove cut?",
                        a: "A glove cut describes finger and palm stitching construction, affecting fit, touch, and catching surface.",
                    },
                    {
                        q: "What cuts are most common?",
                        a: "Popular cuts include Negative Cut, Roll Finger, Hybrid, and Flat Cut, each with different fit and feel.",
                    },
                ],
            },
            {
                slug: "glove-care",
                title: "Glove care",
                intro:
                    "Proper goalkeeper glove care is essential to extend durability and keep maximum grip over time. With regular cleaning and correct storage, latex keeps its performance much longer.",
                items: [
                    {
                        q: "How do I clean goalkeeper gloves correctly?",
                        a: "Rinse gloves with lukewarm water after each use and clean latex gently by hand. Avoid aggressive chemicals and avoid washing machines.",
                    },
                    {
                        q: "How should I dry goalkeeper gloves?",
                        a: "Let gloves air-dry naturally. Keep them away from direct sunlight, radiators, and high heat to protect latex and seams.",
                    },
                    {
                        q: "How should I store gloves between sessions?",
                        a: "Store gloves in a breathable bag in a cool, dry place. Do not leave them wet inside closed compartments for long periods.",
                    },
                    {
                        q: "How do I preserve grip performance for longer?",
                        a: "Clean after every session, slightly moisten palms before play when needed, and avoid unnecessary contact with abrasive surfaces.",
                    },
                ],
            },
            {
                slug: "pro tips on goalkeeper gloves palm foams",
                title: "Pro tips on goalkeeper gloves palm foams",
                items: [
                    {
                        q: "Which palm foams provide the strongest grip?",
                        a: "Premium match foams usually provide top grip in clean conditions, while durable foams last longer under heavy use.",
                    },
                    {
                        q: "How do I increase palm foam lifespan?",
                        a: "Slightly dampen before use, avoid abrasive surfaces, and clean immediately after training or matches.",
                    },
                ],
            },
            {
                slug: "newsletter",
                title: "Newsletter",
                items: [
                    {
                        q: "Why should I subscribe to the newsletter?",
                        a: "Newsletter subscribers receive updates about new releases, promotions, and keeper-focused content.",
                    },
                    {
                        q: "Can I unsubscribe at any time?",
                        a: "Yes. You can unsubscribe anytime using the link in newsletter emails.",
                    },
                ],
            },
            {
                slug: "contact us",
                title: "Contact us",
                items: [
                    {
                        q: "Business hours (CET)",
                        a: "Monday to Thursday 9:00-16:00 and Friday 9:00-13:00.",
                    },
                    {
                        q: "Live chat",
                        a: "Use the on-site chat icon to start a live conversation.",
                    },
                    {
                        q: "Email",
                        a: "office@keepersport.net (emails are typically answered within 24 hours).",
                    },
                    {
                        q: "WhatsApp",
                        a: "+43 (0)676 76646 11.",
                    },
                    {
                        q: "Phone",
                        a: "+43 2252 766 46-43.",
                    },
                ],
            },
];

const FAQ_SECTIONS_AR = [
    {
        slug: "support",
        title: "الدعم",
        items: [
            {
                q: "ماذا أجد في صفحة الأسئلة الشائعة؟",
                a: "ستجد إجابات حول الطلبات، الدفع، الشحن، الإرجاع، المقاسات، دليل القفازات، العناية بالقفازات، وطرق التواصل مع خدمة العملاء.",
            },
            {
                q: "ما الحل إذا لم أجد إجابتي؟",
                a: "إذا لم تجد الإجابة هنا، تواصل مع خدمة العملاء عبر الدردشة المباشرة أو البريد أو واتساب أو الهاتف خلال أوقات العمل.",
            },
        ],
    },
    {
        slug: "i need help with my order",
        title: "أحتاج مساعدة بخصوص طلبي",
        items: [
            {
                q: "كيف أطلب من الموقع؟",
                a: "يمكنك البحث أو تصفح الأقسام، ثم فتح صفحة المنتج واختيار المقاس وإضافته للسلة، وبعدها إكمال بيانات الشحن والفوترة والدفع في صفحة الدفع.",
            },
            {
                q: "هل تم استلام طلبي؟",
                a: "يتم إرسال رسالة تأكيد الطلب مع رقم الطلب إلى بريدك الإلكتروني تلقائيًا. إذا لم تصلك، افحص البريد غير الهام وتأكد من صحة البريد المدخل.",
            },
            {
                q: "ماذا يحدث بعد إتمام الطلب؟",
                a: "بعد نجاح الدفع ستظهر رسالة تأكيد مباشرة، ثم تصلك رسالة فيها تفاصيل الطلب مثل العناوين والأسعار وطريقة الدفع ومعلومات الشحن.",
            },
            {
                q: "هل أحتاج حسابًا لإتمام الطلب؟",
                a: "لا، يمكنك الطلب كزائر. لكن إنشاء حساب يسهل الطلبات القادمة ويعرض لك سجل طلباتك.",
            },
            {
                q: "هل يمكن تعديل أو إلغاء الطلب بعد إرساله؟",
                a: "يمكن التعديل أو الإلغاء فقط قبل بدء المعالجة. بعد بدء المعالجة يمكنك الاستفادة من سياسة الإرجاع بعد الاستلام.",
            },
            {
                q: "هل أستطيع تغيير عنوان الشحن أو الفوترة لاحقًا؟",
                a: "عادة لا يمكن تعديل العناوين بعد تأكيد الطلب. إذا تعذر التسليم، قد تعود الشحنة ويتم إجراء الاسترداد بعد الفحص.",
            },
            {
                q: "كيف أجد رقم طلبي؟",
                a: "رقم الطلب يظهر في رسالة تأكيد الطلب وفي الفاتورة، وغالبًا يتكون من 7 أرقام.",
            },
            {
                q: "المقاس الذي أريده غير قابل للاختيار، لماذا؟",
                a: "المقاسات غير المتوفرة/المنتهية لا يمكن اختيارها أو إضافتها للسلة.",
            },
            {
                q: "كيف أطلب منتجًا بنظام الطلب المسبق؟",
                a: "المنتجات التي تحمل علامة الطلب المسبق يمكن طلبها مسبقًا، ويتم تجهيزها فور توفرها بالمخزون.",
            },
            {
                q: "هل ممكن أن يصبح المنتج غير متوفر بعد طلبي له؟",
                a: "في حالات نادرة قد يحدث اختلاف بالمخزون، وعندها يوفر الدعم حلولًا مناسبة مثل بدائل أو استرداد.",
            },
        ],
    },
    {
        slug: "payment options",
        title: "خيارات الدفع",
        items: [
            {
                q: "ما طرق الدفع المتوفرة؟",
                a: "بطاقات الائتمان/الخصم (MasterCard وVISA وAmerican Express)، المحافظ الرقمية (Apple Pay وGoogle Pay)، PayPal وAmazonPay، وفي بعض الدول Klarna.",
            },
            {
                q: "ماذا يحدث عند الدفع؟",
                a: "بمجرد نجاح الدفع يتم حجز المنتجات الموجودة في طلبك مباشرة.",
            },
            {
                q: "كيف أتأكد من نجاح الدفع؟",
                a: "تظهر رسالة تأكيد مباشرة بعد نجاح العملية، ثم تصلك رسالة تأكيد الطلب على البريد.",
            },
            {
                q: "أين أجد رمز الأمان في البطاقة؟",
                a: "رمز CVC/CVV يكون عادة على ظهر البطاقة. لبعض البطاقات قد يختلف مكانه، ويمكن الرجوع للبنك.",
            },
            {
                q: "هل يمكن الدفع بالتقسيط؟",
                a: "خيار التقسيط غير متاح حاليًا.",
            },
            {
                q: "متى تصلني الفاتورة؟",
                a: "تصل الفاتورة إلى البريد الإلكتروني في يوم خروج الشحنة من المستودع مع معلومات التتبع.",
            },
            {
                q: "هل يمكن إصدار الفاتورة باسم شركة أو نادي؟",
                a: "نعم، يمكنك إدخال عنوان فوترة مختلف أثناء الدفع. بعد إصدار الفاتورة عادة لا يمكن تعديلها.",
            },
        ],
    },
    {
        slug: "shipping information",
        title: "معلومات الشحن",
        items: [
            {
                q: "ما شركة التوصيل المستخدمة؟",
                a: "بحسب موقعك والمنتجات، قد يتم الشحن عبر GLS أو DHL أو UPS أو DPD.",
            },
            {
                q: "كم تكلفة الشحن؟",
                a: "يتم احتساب تكلفة الشحن تلقائيًا بعد إدخال العنوان في صفحة الدفع.",
            },
            {
                q: "كم تستغرق مدة التوصيل؟",
                a: "المنتجات المتوفرة تُجهز عادة خلال 1-2 يوم عمل، والتوصيل غالبًا 3-10 أيام عمل حسب الوجهة.",
            },
            {
                q: "لماذا لم يصلني كامل الطلب؟",
                a: "قد يتم تقسيم الطلب إلى عدة شحنات لتوصيل المنتجات المتوفرة بشكل أسرع وبدون رسوم إضافية للتقسيم.",
            },
            {
                q: "هل يمكن الشحن إلى صندوق بريد؟",
                a: "لا، الشحن إلى صناديق البريد غير متاح حاليًا.",
            },
            {
                q: "ماذا لو ضاعت الشحنة؟",
                a: "يمكن لفريق الدعم فتح متابعة وتقديم بديل أو استرداد عندما يتم تأكيد فقدان الشحنة.",
            },
            {
                q: "هل يمكن التسليم إلى محطة طرود أو Parcel Shop؟",
                a: "هذا الخيار غير متاح حاليًا.",
            },
            {
                q: "هل يمكن أن يكون عنوان الشحن مختلفًا عن الفوترة؟",
                a: "نعم، يمكنك إدخال عنوان شحن مختلف عن عنوان الفوترة.",
            },
            {
                q: "هل الشحن الدولي متاح؟",
                a: "يتحدد حسب البلد/نسخة المتجر المختارة. اختر نسخة البلد المناسبة لإتمام الشحن الدولي.",
            },
        ],
    },
    {
        slug: "returns",
        title: "الإرجاع",
        items: [
            {
                q: "كيف تتم عملية إرجاع منتج؟",
                a: "لدينا سياسة إرجاع مرنة، لكن المنتجات المخصصة أو المستخدمة غير قابلة للإرجاع.\n\nخطوات الإرجاع:\n1) إرجاع المنتجات خلال 14 يومًا من الاستلام.\n2) يجب أن تكون المنتجات غير مستخدمة وغير مغسولة وقابلة لإعادة البيع.\n3) الإرجاع مع التغليف الأصلي وكل الملصقات.\n4) لا تلصق شريطًا لاصقًا مباشرة على علب الأحذية.\n5) تكاليف الإرجاع على العميل، ولا يتم قبول الشحنات غير المدفوعة.\n6) عند عدم الالتزام بالشروط قد يتم رفض الإرجاع أو تخفيض مبلغ الاسترداد.\n7) عند وجود خصومات كمية/حزم، قد يعاد احتساب الخصم في حالة إرجاع جزئي.\n\nعنوان الإرجاع:\nKEEPERsport GmbH\nAnzengrubergasse 26\n2542 Kottingbrunn\nAustria - Europe\n\nبعد فحص الجودة، يتم الاسترداد خلال مدة تصل إلى 14 يومًا على وسيلة الدفع الأصلية.",
            },
            {
                q: "كم المدة المتاحة للإرجاع؟",
                a: "يمكن الإرجاع خلال 14 يومًا من تاريخ استلام الشحنة. المنتجات المخصصة أو المستخدمة مستثناة.",
            },
            {
                q: "هل يمكن الإرجاع من أي دولة؟",
                a: "إذا كان الإرجاع من خارج أوروبا، يفضّل التواصل مع الدعم قبل الشحن.\nفي بعض الحالات قد تكون تكاليف الشحن والجمارك مرتفعة.",
            },
            {
                q: "هل يمكن تبديل المقاس مباشرة؟",
                a: "التبديل المباشر غير متاح. قم بطلب جديد للمقاس المطلوب ثم أرجع المنتج الحالي.",
            },
            {
                q: "متى أستلم المبلغ المسترد؟",
                a: "بعد معالجة المرتجع من قسم الإرجاع، ستصلك مذكرة دائن ورسالة تأكيد عبر البريد.",
            },
            {
                q: "هل أدفع تكلفة إرجاع المنتجات غير المرغوبة؟",
                a: "نعم، تكلفة إرجاع المنتجات غير المرغوبة على العميل، إلا إذا كان المنتج خاطئًا أو تالفًا. احتفظ بإثبات الشحن.",
            },
            {
                q: "كم تستغرق عملية استرجاع المبلغ بالكامل؟",
                a: "قد تستغرق الدورة الكاملة للاسترداد 3-4 أسابيع في بعض الحالات بسبب الجمارك والإجراءات الإدارية.",
            },
            {
                q: "ما تعليمات الإرجاع المتعلقة بـ Brexit؟",
                a: "في إرجاع المملكة المتحدة يجب كتابة: Returned Goods - Failed Sale.\nوإضافة رقم الطلب الأصلي على الصندوق ونماذج الجمارك (CN22/CN23).\nالبيانات المطلوبة:\nVAT: ATU 62801445\nEORI: AT EOS 1000 22085",
            },
        ],
    },
    {
        slug: "glove-guide",
        title: "دليل القفازات",
        intro:
            "قفاز حارس المرمى هو الأداة الأهم للحارس. هذا الدليل يساعدك على اختيار القفاز المناسب حسب اللاتكس، القصّة، المقاس، وطريقة العناية.",
        cards: [
            {
                head: "العناية بالقفازات",
                title: "العناية والصيانة",
                text: "تعلّم طريقة تنظيف وتجفيف وتخزين القفازات للحفاظ على قوة التماسك فترة أطول.",
                image: KS_HOME_MEDIA.teaserCura,
            },
            {
                head: "راحة اليد",
                title: "أنواع اللاتكس",
                text: "من لاتكس المباريات عالي التماسك إلى لاتكس التدريب الأكثر تحمّلًا بحسب الأرضية والطقس.",
                image: KS_HOME_MEDIA.latestNewsGloveBanner,
            },
            {
                head: "قصّات القفاز",
                title: "قصّات مختلفة",
                text: "Negative Cut وHybrid وGunn وRegular: كل قصّة تعطي إحساسًا ومقاسًا مختلفين.",
                image: KS_HOME_MEDIA.teaserBePro,
            },
            {
                head: "موديلات القفازات",
                title: "موديلات متنوعة",
                text: "قارن بين القفازات الكلاسيكية والقفازات التقنية الحديثة بما يناسب مستواك.",
                image: KS_HOME_MEDIA.teaserCircleOfPros,
            },
        ],
        richSections: [
            {
                title: "اختيار اللاتكس المناسب",
                text: "Match: أعلى تماسك في الجفاف. Aqua: توازن ممتاز للمطر والرطوبة. Training: تحمّل قوي للاستخدام المتكرر. Indoor/Artificial: مناسب للأرضيات القاسية لتقليل التآكل.",
            },
            {
                title: "فهم القصّات المختلفة",
                text: "Negative Cut (NC) لملاءمة ضيقة. Hybrid/Power Cut مزيج للتحكم. Gunn Cut (GC/Rollfinger) لسطح تماس أكبر. Regular Cut (RC) لملاءمة أوسع وكلاسيكية.",
            },
            {
                title: "اختيار الموديل المناسب",
                text: "من خيارات متينة للمبتدئين إلى قفازات احترافية بتقنيات متقدمة وثبات أعلى، اختر ما يناسب متطلبات لعبك.",
            },
            {
                title: "نصائح إضافية",
                text: "القفازات بدون Strap تعطي إحساسًا حديثًا وملاءمة قريبة، بينما القفازات مع Strap تمنح ثباتًا إضافيًا للرسغ. في قفازات الأطفال ركز على الراحة والحماية والمقاس الصحيح.",
            },
        ],
        items: [
            {
                q: "كيف أجد قفاز الحراسة المثالي؟",
                a: "يعتمد الاختيار المناسب على أسلوب لعبك، نوع الأرضية، حالة الطقس، تفضيلك للملاءمة، وعدد مرات التدريب أو المباريات.",
            },
            {
                q: "ما أول الأشياء التي يجب تقييمها قبل الشراء؟",
                a: "ابدأ بأربع نقاط رئيسية: العناية والمتانة، نوع اللاتكس، نوع القصّة، وفئة الموديل. هذه العناصر تحدد التماسك والراحة والتحكم والعمر الافتراضي.",
            },
            {
                q: "كيف أختار نوع اللاتكس المناسب؟",
                a: "يمكن تقسيم اللاتكس إلى 4 فئات:\n✔ Match: تماسك قوي في الجاف مع تآكل أعلى.\n✔ Aqua: توازن بين التماسك والتحمل في البلل.\n✔ Training: متين للتدريب المنتظم.\n✔ Indoor/Artificial: مناسب للأسطح القاسية لتقليل التآكل.",
            },
            {
                q: "ما القصّات التي يجب مقارنتها؟",
                a: "أهم القصّات:\n✔ Negative Cut (NC): ضيق وخياطة داخلية.\n✔ Hybrid / Power Cut: دمج بين أكثر من قصّة لزيادة التحكم.\n✔ Gunn Cut (GC): سطح تماس أكبر.\n✔ Regular Cut (RC): ملاءمة أوسع وإحساس كلاسيكي.",
            },
            {
                q: "كيف أختار الموديل المناسب؟",
                a: "اختر حسب مستواك واحتياجك: متانة للمبتدئ، تحكم وملاءمة للمتقدم، أو أداء احترافي للمباريات.",
            },
            {
                q: "أفضل مع Strap أم بدون Strap؟",
                a: "بدون Strap يعطي ملاءمة حديثة وقريبة، بينما Strap يضيف تثبيتًا إضافيًا للرسغ ويفضله من يحتاج ثبات أعلى.",
            },
            {
                q: "هل قفازات الأطفال تحتاج اعتبارات مختلفة؟",
                a: "نعم، يجب التركيز على المقاس الآمن، الراحة، الحماية، وتماسك مناسب لليد أثناء التطور.",
            },
            {
                q: "ما أهمية المقاس في القفازات؟",
                a: "المقاس الصحيح أساسي جدًا للأداء. الموديلات الضيقة تحتاج ملاءمة دقيقة، بينما القصّات الأوسع تعطي مساحة حركة وراحة أكبر.",
            },
        ],
    },
    {
        slug: "goalkeeper glove models",
        title: "موديلات قفازات الحراس",
        items: [
            {
                q: "ما الفرق بين خطوط موديلات القفازات؟",
                a: "تختلف الموديلات في الملاءمة، بناء ظهر القفاز، نوع الإغلاق، والإحساس العام عند التعامل مع الكرة.",
            },
            {
                q: "كيف أقارن بسرعة بين موديلين؟",
                a: "قارن نوع اللاتكس، القصّة، نظام السوار، ونوع الاستخدام (مباريات/تدريب/استخدام شامل).",
            },
        ],
    },
    {
        slug: "goalkeeper glove cuts",
        title: "قصّات قفازات الحراس",
        items: [
            {
                q: "ما المقصود بقصّة القفاز؟",
                a: "قصّة القفاز هي طريقة خياطة الأصابع والكف، وهي تؤثر على الملاءمة والإحساس والتحكم أثناء الالتقاط.",
            },
            {
                q: "ما أشهر قصّات القفازات؟",
                a: "القصّات الأكثر شيوعًا: Negative Cut وRoll Finger وHybrid وFlat Cut، ولكل قصّة إحساس وملاءمة مختلفة.",
            },
        ],
    },
    {
        slug: "glove-care",
        title: "العناية بالقفازات",
        intro:
            "العناية الصحيحة بقفازات الحارس أساسية لإطالة عمر القفاز والحفاظ على أعلى تماسك. مع التنظيف المنتظم والتخزين الصحيح، يحافظ اللاتكس على أداءه لفترة أطول.",
        items: [
            {
                q: "كيف أنظف قفازات الحراسة بالشكل الصحيح؟",
                a: "اشطف القفازات بماء فاتر بعد كل استخدام ونظّف اللاتكس يدويًا بلطف. تجنب المواد الكيميائية القوية والغسيل الآلي.",
            },
            {
                q: "كيف أجفف القفازات بشكل صحيح؟",
                a: "اترك القفازات لتجف طبيعيًا في الهواء، وبعيدًا عن الشمس المباشرة أو المدافئ والحرارة العالية لحماية اللاتكس والخياطة.",
            },
            {
                q: "كيف أخزن القفازات بين الحصص؟",
                a: "احفظ القفازات في كيس يسمح بالتهوية وفي مكان بارد وجاف، ولا تتركها مبللة داخل حقيبة مغلقة لفترة طويلة.",
            },
            {
                q: "كيف أحافظ على التماسك لأطول فترة؟",
                a: "نظّف القفازات بعد كل تدريب/مباراة، ورطّب راحة اليد قليلًا قبل اللعب عند الحاجة، وتجنب الاحتكاك غير الضروري بالأسطح الخشنة.",
            },
        ],
    },
    {
        slug: "pro tips on goalkeeper gloves palm foams",
        title: "نصائح احترافية حول لاتكس القفازات",
        items: [
            {
                q: "أي نوع لاتكس يعطي أقوى تماسك؟",
                a: "أنواع لاتكس المباريات الاحترافية تقدم عادة أعلى تماسك في الظروف الجافة، بينما الأنواع المتينة تدوم أكثر مع الاستخدام المكثف.",
            },
            {
                q: "كيف أطيل عمر لاتكس راحة اليد؟",
                a: "رطّب القفاز قليلًا قبل الاستخدام، وتجنب الأسطح الخشنة، ونظفه مباشرة بعد التدريب أو المباراة.",
            },
        ],
    },
    {
        slug: "newsletter",
        title: "النشرة البريدية",
        items: [
            {
                q: "لماذا أشترك في النشرة البريدية؟",
                a: "المشتركون تصِلهم تحديثات الإصدارات الجديدة والعروض والمحتوى المخصص لحراس المرمى.",
            },
            {
                q: "هل يمكن إلغاء الاشتراك في أي وقت؟",
                a: "نعم، يمكنك إلغاء الاشتراك في أي وقت من رابط الإلغاء داخل رسائل النشرة.",
            },
        ],
    },
    {
        slug: "contact us",
        title: "تواصل معنا",
        items: [
            {
                q: "ما أوقات العمل (CET)؟",
                a: "من الإثنين إلى الخميس 9:00-16:00، ويوم الجمعة 9:00-13:00.",
            },
            {
                q: "الدردشة المباشرة",
                a: "يمكنك بدء محادثة مباشرة عبر أيقونة الدردشة داخل الموقع.",
            },
            {
                q: "البريد الإلكتروني",
                a: "office@keepersport.net (عادة يتم الرد خلال 24 ساعة).",
            },
            {
                q: "واتساب",
                a: "+43 (0)676 76646 11.",
            },
            {
                q: "الهاتف",
                a: "+43 2252 766 46-43.",
            },
        ],
    },
];

const FAQ_COPY = {
    en: {
        title: "FAQ",
        subtitle:
            "Here you will find the most frequently asked questions on topics such as ordering, payment, shipping and returns.",
        sections: FAQ_SECTIONS_EN,
    },
    ar: {
        title: "الأسئلة الشائعة",
        subtitle:
            "هنا ستجد أكثر الأسئلة شيوعًا حول الطلبات، الدفع، الشحن، الإرجاع، والمحتوى الخاص بالقفازات.",
        sections: FAQ_SECTIONS_AR,
    },
};

export default {
    name: "FaqPage",
    data() {
        return {
            activeTab: 0,
            loading: false,
            dbSections: null,
            fromDb: false,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        staticCopy() {
            return this.locale === "ar" ? FAQ_COPY.ar : FAQ_COPY.en;
        },
        copy() {
            if (this.fromDb && Array.isArray(this.dbSections) && this.dbSections.length) {
                const staticBySlug = {};
                (this.staticCopy.sections || []).forEach((section) => {
                    if (section.slug) staticBySlug[section.slug] = section;
                });

                const sections = this.dbSections.map((section) => {
                    const staticSection = staticBySlug[section.slug] || {};
                    return {
                        ...section,
                        intro: staticSection.intro || null,
                        cards: staticSection.cards || null,
                        richSections: staticSection.richSections || null,
                    };
                });

                return {
                    title: this.staticCopy.title,
                    subtitle: this.staticCopy.subtitle,
                    sections,
                };
            }

            return this.staticCopy;
        },
        activeSection() {
            return this.copy.sections[this.activeTab] || this.copy.sections[0] || { title: "", items: [] };
        },
    },
    watch: {
        locale() {
            this.activeTab = 0;
            this.fetchFaqs();
        },
        "$route.query.tab": {
            immediate: true,
            handler(value) {
                this.applyTabQuery(value);
            },
        },
        "copy.sections"() {
            this.applyTabQuery(this.$route.query.tab);
        },
    },
    mounted() {
        this.fetchFaqs();
    },
    methods: {
        applyTabQuery(value) {
            if (!value || !this.copy?.sections?.length) return;
            const normalized = String(value).toLowerCase();
            const index = this.copy.sections.findIndex(
                (section) =>
                    section.title.toLowerCase() === normalized ||
                    (section.slug && section.slug.toLowerCase() === normalized)
            );
            if (index >= 0) this.activeTab = index;
        },
        async fetchFaqs() {
            this.loading = true;
            try {
                const { data } = await api.get("faq/");
                if (data?.from_db && Array.isArray(data.sections) && data.sections.length) {
                    this.dbSections = data.sections;
                    this.fromDb = true;
                } else {
                    this.dbSections = null;
                    this.fromDb = false;
                }
            } catch (_) {
                this.dbSections = null;
                this.fromDb = false;
            } finally {
                this.loading = false;
                this.$nextTick(() => this.applyTabQuery(this.$route.query.tab));
            }
        },
    },
};
</script>

<style scoped>
.ks-faq-page {
    background: #fff;
    color: #111;
    padding: 36px 16px 48px;
}

.ks-faq-wrap {
    max-width: 1080px;
    margin: 0 auto;
}

.ks-faq-title {
    margin: 0 0 8px;
    font-size: clamp(30px, 3.4vw, 46px);
    font-weight: 900;
    letter-spacing: 0.02em;
}

.ks-faq-subtitle {
    margin: 0 0 20px;
    color: #444;
    font-size: 15px;
}

.ks-faq-tabs {
    display: flex;
    gap: 0;
    overflow-x: auto;
    margin: 0 -16px 20px;
    padding: 0 16px;
    background: #2f3438;
    border-top: 1px solid #4a4f54;
    border-bottom: 1px solid #4a4f54;
    scrollbar-width: none;
}

.ks-faq-tabs::-webkit-scrollbar {
    display: none;
}

.ks-faq-tab {
    border: 0;
    background: transparent;
    color: #cdd2d6;
    font-size: 12px;
    font-weight: 700;
    border-radius: 0;
    padding: 12px 10px;
    white-space: nowrap;
    cursor: pointer;
    transition: color 0.2s ease;
    text-transform: uppercase;
}

.ks-faq-tab:hover {
    color: #fff;
}

.ks-faq-tab.is-active {
    color: #fff;
    text-decoration: underline;
    text-underline-offset: 5px;
}

.ks-faq-section {
    margin-bottom: 24px;
}

.ks-faq-section-title {
    margin: 0 0 12px;
    font-size: 38px;
    font-weight: 800;
    text-transform: uppercase;
}

.ks-faq-section-intro {
    margin: 0 0 18px;
    color: #343434;
    line-height: 1.7;
    max-width: 980px;
}

.ks-glove-cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin: 14px 0 20px;
}

.ks-glove-card {
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #2c2c2c;
}

.ks-glove-card-head {
    background: #060606;
    min-height: 212px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.ks-glove-card-head img {
    width: 100%;
    height: 212px;
    object-fit: cover;
    display: block;
}

.ks-glove-card-head span {
    color: #fff;
    font-size: 30px;
    font-weight: 800;
}

.ks-glove-card-body {
    background: #252a2f;
    color: #fff;
    padding: 10px 12px 12px;
    border-top: 1px solid #3a4046;
}

.ks-glove-card-body h4 {
    margin: 0 0 6px;
    font-size: 19px;
    font-weight: 800;
    text-transform: uppercase;
    line-height: 1.05;
}

.ks-glove-card-body p {
    margin: 0;
    font-size: 13px;
    color: #d2d5d7;
    line-height: 1.5;
}

.ks-rich-blocks {
    display: grid;
    gap: 14px;
    margin-bottom: 18px;
}

.ks-rich-block h3 {
    margin: 0 0 5px;
    font-size: 24px;
    font-weight: 800;
}

.ks-rich-block p {
    margin: 0;
    line-height: 1.65;
    color: #3f3f3f;
}

.ks-faq-item {
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    margin-bottom: 8px;
    background: #fafafa;
}

.ks-faq-item summary {
    list-style: none;
    cursor: pointer;
    padding: 14px 16px;
    padding-inline-end: 44px;
    font-weight: 700;
    font-size: 15px;
    position: relative;
}

.ks-faq-item summary::-webkit-details-marker {
    display: none;
}

.ks-faq-item summary::after {
    content: "+";
    position: absolute;
    inset-inline-end: 16px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    line-height: 1;
    color: #666;
    pointer-events: none;
}

.ks-faq-item[open] summary::after {
    content: "−";
}

.ks-faq-item p {
    margin: 0;
    padding: 0 16px 14px;
    color: #4b4b4b;
    line-height: 1.6;
    font-size: 14px;
    white-space: pre-line;
}

@media (max-width: 768px) {
    .ks-faq-page {
        padding: 24px 12px 36px;
    }

    .ks-faq-tab {
        font-size: 12px;
        padding: 7px 10px;
    }

    .ks-faq-section-title {
        font-size: 28px;
    }

    .ks-glove-cards {
        grid-template-columns: 1fr 1fr;
    }

    .ks-glove-card-head,
    .ks-glove-card-head img {
        height: 170px;
        min-height: 170px;
    }

    .ks-glove-card-body h4 {
        font-size: 22px;
    }

    .ks-glove-card-body p {
        font-size: 14px;
    }
}

@media (max-width: 560px) {
    .ks-glove-cards {
        grid-template-columns: 1fr;
    }
}
</style>
