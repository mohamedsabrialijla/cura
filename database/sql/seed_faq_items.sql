-- Seed FAQ items (same content as public FAQ.vue)
DELETE FROM faq_items;

INSERT INTO faq_items (category, question_ar, question_en, answer_ar, answer_en, sort_order, status, created_at, updated_at) VALUES
('support', 'ماذا أجد في صفحة الأسئلة الشائعة؟', 'What can I find in this FAQ?', 'ستجد إجابات حول الطلبات، الدفع، الشحن، الإرجاع، المقاسات، دليل القفازات، العناية بالقفازات، وطرق التواصل مع خدمة العملاء.', 'You can find answers about ordering, payment, shipping, returns, sizing, glove guides, glove care, and customer support contacts.', 1, 'active', NOW(), NOW()),
('support', 'ما الحل إذا لم أجد إجابتي؟', 'I still need help. What should I do?', 'إذا لم تجد الإجابة هنا، تواصل مع خدمة العملاء عبر الدردشة المباشرة أو البريد أو واتساب أو الهاتف خلال أوقات العمل.', 'If your question is not answered here, contact customer service via live chat, email, WhatsApp, or phone during support hours.', 2, 'active', NOW(), NOW()),
('i need help with my order', 'كيف أطلب من الموقع؟', 'How do I go about ordering?', 'يمكنك البحث أو تصفح الأقسام، ثم فتح صفحة المنتج واختيار المقاس وإضافته للسلة، وبعدها إكمال بيانات الشحن والفوترة والدفع في صفحة الدفع.', 'Use search or browse categories, open the product detail page, select your size, add to cart, then proceed to checkout and complete billing, shipping, and payment details.', 1, 'active', NOW(), NOW()),
('i need help with my order', 'هل تم استلام طلبي؟', 'Have you received my order?', 'يتم إرسال رسالة تأكيد الطلب مع رقم الطلب إلى بريدك الإلكتروني تلقائيًا. إذا لم تصلك، افحص البريد غير الهام وتأكد من صحة البريد المدخل.', 'An order confirmation with your order number is automatically sent to your email address. If missing, check spam or verify that the email address was entered correctly.', 2, 'active', NOW(), NOW()),
('i need help with my order', 'ماذا يحدث بعد إتمام الطلب؟', 'What happens to my order after I have placed it?', 'بعد نجاح الدفع ستظهر رسالة تأكيد مباشرة، ثم تصلك رسالة فيها تفاصيل الطلب مثل العناوين والأسعار وطريقة الدفع ومعلومات الشحن.', 'After successful payment you see confirmation immediately. Order confirmation is sent by email and includes addresses, prices, payment method, and shipment details if split shipping applies.', 3, 'active', NOW(), NOW()),
('i need help with my order', 'هل أحتاج حسابًا لإتمام الطلب؟', 'Do I need an account to be able to place an order?', 'لا، يمكنك الطلب كزائر. لكن إنشاء حساب يسهل الطلبات القادمة ويعرض لك سجل طلباتك.', 'No. Guest checkout is available. Creating an account gives faster checkout and access to your order history.', 4, 'active', NOW(), NOW()),
('i need help with my order', 'هل يمكن تعديل أو إلغاء الطلب بعد إرساله؟', 'Can I have my order changed or cancelled after I have placed it?', 'يمكن التعديل أو الإلغاء فقط قبل بدء المعالجة. بعد بدء المعالجة يمكنك الاستفادة من سياسة الإرجاع بعد الاستلام.', 'Changes or cancellation are only possible before processing begins. If processing is already underway, return the shipment for refund according to return policy.', 5, 'active', NOW(), NOW()),
('i need help with my order', 'هل أستطيع تغيير عنوان الشحن أو الفوترة لاحقًا؟', 'Can I change the delivery or billing address later?', 'عادة لا يمكن تعديل العناوين بعد تأكيد الطلب. إذا تعذر التسليم، قد تعود الشحنة ويتم إجراء الاسترداد بعد الفحص.', 'Address changes are generally not possible after order placement. If undeliverable, shipment returns and refund is processed after inspection.', 6, 'active', NOW(), NOW()),
('i need help with my order', 'كيف أجد رقم طلبي؟', 'How do I find my order number?', 'رقم الطلب يظهر في رسالة تأكيد الطلب وفي الفاتورة، وغالبًا يتكون من 7 أرقام.', 'Order number is shown in order confirmation and invoice, usually consisting of 7 digits.', 7, 'active', NOW(), NOW()),
('i need help with my order', 'المقاس الذي أريده غير قابل للاختيار، لماذا؟', 'The size I want to order cannot be selected. Why?', 'المقاسات غير المتوفرة/المنتهية لا يمكن اختيارها أو إضافتها للسلة.', 'Unavailable/sold-out sizes cannot be selected or added to cart.', 8, 'active', NOW(), NOW()),
('i need help with my order', 'كيف أطلب منتجًا بنظام الطلب المسبق؟', 'How can I pre-order?', 'المنتجات التي تحمل علامة الطلب المسبق يمكن طلبها مسبقًا، ويتم تجهيزها فور توفرها بالمخزون.', 'Items marked with pre-order symbol can be pre-ordered and are processed once stock arrives.', 9, 'active', NOW(), NOW()),
('i need help with my order', 'هل ممكن أن يصبح المنتج غير متوفر بعد طلبي له؟', 'Can it happen that an item I ordered is no longer in stock?', 'في حالات نادرة قد يحدث اختلاف بالمخزون، وعندها يوفر الدعم حلولًا مناسبة مثل بدائل أو استرداد.', 'In rare inventory mismatch cases, support provides suitable alternatives such as replacement options or refund handling.', 10, 'active', NOW(), NOW()),
('payment options', 'ما طرق الدفع المتوفرة؟', 'Which payment methods can I use to pay?', 'بطاقات الائتمان/الخصم (MasterCard وVISA وAmerican Express)، المحافظ الرقمية (Apple Pay وGoogle Pay)، PayPal وAmazonPay، وفي بعض الدول Klarna.', 'Credit/debit cards (MasterCard, VISA, American Express), digital wallets (Apple Pay, Google Pay etc.), PayPal, AmazonPay, and in some countries Klarna.', 1, 'active', NOW(), NOW()),
('payment options', 'ماذا يحدث عند الدفع؟', 'What happens when I pay?', 'بمجرد نجاح الدفع يتم حجز المنتجات الموجودة في طلبك مباشرة.', 'As soon as payment is completed, items in your order are reserved for you.', 2, 'active', NOW(), NOW()),
('payment options', 'كيف أتأكد من نجاح الدفع؟', 'Was my payment successful?', 'تظهر رسالة تأكيد مباشرة بعد نجاح العملية، ثم تصلك رسالة تأكيد الطلب على البريد.', 'Payment confirmation appears immediately, followed by order confirmation email.', 3, 'active', NOW(), NOW()),
('payment options', 'أين أجد رمز الأمان في البطاقة؟', 'Where can I find the security code on my credit/debit card?', 'رمز CVC/CVV يكون عادة على ظهر البطاقة. لبعض البطاقات قد يختلف مكانه، ويمكن الرجوع للبنك.', 'CVC/CVV security codes are on the back of most cards. For other card types, check with your bank.', 4, 'active', NOW(), NOW()),
('payment options', 'هل يمكن الدفع بالتقسيط؟', 'Can I pay in installments?', 'خيار التقسيط غير متاح حاليًا.', 'This payment option is currently not offered.', 5, 'active', NOW(), NOW()),
('payment options', 'متى تصلني الفاتورة؟', 'When do I get my invoice?', 'تصل الفاتورة إلى البريد الإلكتروني في يوم خروج الشحنة من المستودع مع معلومات التتبع.', 'Invoice is emailed on the day your package leaves the warehouse along with shipment tracking.', 6, 'active', NOW(), NOW()),
('payment options', 'هل يمكن إصدار الفاتورة باسم شركة أو نادي؟', 'Can my company or football club be billed?', 'نعم، يمكنك إدخال عنوان فوترة مختلف أثناء الدفع. بعد إصدار الفاتورة عادة لا يمكن تعديلها.', 'Yes, you can set different billing/shipping addresses at checkout. Post-issue invoice changes are generally not possible.', 7, 'active', NOW(), NOW()),
('shipping information', 'ما شركة التوصيل المستخدمة؟', 'What delivery company do you ship with?', 'بحسب موقعك والمنتجات، قد يتم الشحن عبر GLS أو DHL أو UPS أو DPD.', 'Depending on location and items, shipments are delivered via GLS, DHL, UPS, or DPD.', 1, 'active', NOW(), NOW()),
('shipping information', 'كم تكلفة الشحن؟', 'What are the shipping costs?', 'يتم احتساب تكلفة الشحن تلقائيًا بعد إدخال العنوان في صفحة الدفع.', 'Shipping costs are calculated after entering your address during checkout.', 2, 'active', NOW(), NOW()),
('shipping information', 'كم تستغرق مدة التوصيل؟', 'How long does delivery take?', 'المنتجات المتوفرة تُجهز عادة خلال 1-2 يوم عمل، والتوصيل غالبًا 3-10 أيام عمل حسب الوجهة.', 'In-stock items are dispatched in 1-2 working days. Shipping can take around 3-10 business days depending on destination.', 3, 'active', NOW(), NOW()),
('shipping information', 'لماذا لم يصلني كامل الطلب؟', 'I did not receive my full order. What happened?', 'قد يتم تقسيم الطلب إلى عدة شحنات لتوصيل المنتجات المتوفرة بشكل أسرع وبدون رسوم إضافية للتقسيم.', 'Orders may be split into multiple shipments to deliver available items faster, with no extra split-shipment fee.', 4, 'active', NOW(), NOW()),
('shipping information', 'هل يمكن الشحن إلى صندوق بريد؟', 'Do you ship to P.O. boxes?', 'لا، الشحن إلى صناديق البريد غير متاح حاليًا.', 'No, P.O. box delivery is currently unavailable.', 5, 'active', NOW(), NOW()),
('shipping information', 'ماذا لو ضاعت الشحنة؟', 'What happens if my package gets lost?', 'يمكن لفريق الدعم فتح متابعة وتقديم بديل أو استرداد عندما يتم تأكيد فقدان الشحنة.', 'Support can investigate and provide refund or replacement options when shipment loss is confirmed.', 6, 'active', NOW(), NOW()),
('shipping information', 'هل يمكن التسليم إلى محطة طرود أو Parcel Shop؟', 'Can I deliver to a packing station or parcel shop?', 'هذا الخيار غير متاح حاليًا.', 'This delivery option is currently not offered.', 7, 'active', NOW(), NOW()),
('shipping information', 'هل يمكن أن يكون عنوان الشحن مختلفًا عن الفوترة؟', 'Can shipping and billing addresses be different?', 'نعم، يمكنك إدخال عنوان شحن مختلف عن عنوان الفوترة.', 'Yes, you can provide different billing and delivery addresses.', 8, 'active', NOW(), NOW()),
('shipping information', 'هل الشحن الدولي متاح؟', 'Do you deliver internationally?', 'يتحدد حسب البلد/نسخة المتجر المختارة. اختر نسخة البلد المناسبة لإتمام الشحن الدولي.', 'Availability depends on selected country/storefront; choose the right country version for international delivery.', 9, 'active', NOW(), NOW()),
('returns', 'كيف تتم عملية إرجاع منتج؟', 'How do I go about returning something?', 'لدينا سياسة إرجاع مرنة، لكن المنتجات المخصصة أو المستخدمة غير قابلة للإرجاع.

خطوات الإرجاع:
1) إرجاع المنتجات خلال 14 يومًا من الاستلام.
2) يجب أن تكون المنتجات غير مستخدمة وغير مغسولة وقابلة لإعادة البيع.
3) الإرجاع مع التغليف الأصلي وكل الملصقات.
4) لا تلصق شريطًا لاصقًا مباشرة على علب الأحذية.
5) تكاليف الإرجاع على العميل، ولا يتم قبول الشحنات غير المدفوعة.
6) عند عدم الالتزام بالشروط قد يتم رفض الإرجاع أو تخفيض مبلغ الاسترداد.
7) عند وجود خصومات كمية/حزم، قد يعاد احتساب الخصم في حالة إرجاع جزئي.

عنوان الإرجاع:
KEEPERsport GmbH
Anzengrubergasse 26
2542 Kottingbrunn
Austria - Europe

بعد فحص الجودة، يتم الاسترداد خلال مدة تصل إلى 14 يومًا على وسيلة الدفع الأصلية.', 'We have a customer-friendly return policy. Personalized or used goods are excluded.

Follow these steps:
1) Return goods within 14 days from delivery.
2) Items must be unworn, unwashed, and resellable.
3) Keep original packaging and all labels.
4) Do not tape directly on shoe boxes; wrap them in outer packaging.
5) Return shipping is at your own expense; unpaid parcels are not accepted.
6) If requirements are not met, return may be refused or refund may be reduced.
7) If bulk/set discounts were applied, partial returns may recalculate discounts.

Return address:
KEEPERsport GmbH
Anzengrubergasse 26
2542 Kottingbrunn
Austria - Europe

After quality check, refunds are processed within up to 14 days to your original payment method.', 1, 'active', NOW(), NOW()),
('returns', 'كم المدة المتاحة للإرجاع؟', 'How long can I return something?', 'يمكن الإرجاع خلال 14 يومًا من تاريخ استلام الشحنة. المنتجات المخصصة أو المستخدمة مستثناة.', 'You can return items within 14 days from the day you received the package. Personalized or used goods are excluded from returns.', 2, 'active', NOW(), NOW()),
('returns', 'هل يمكن الإرجاع من أي دولة؟', 'Can I return something from anywhere?', 'إذا كان الإرجاع من خارج أوروبا، يفضّل التواصل مع الدعم قبل الشحن.
في بعض الحالات قد تكون تكاليف الشحن والجمارك مرتفعة.', 'If you are returning from outside Europe, contact customer service before sending.
Because of distance, freight, taxes, and customs fees, some returns may not be recommended.', 3, 'active', NOW(), NOW()),
('returns', 'هل يمكن تبديل المقاس مباشرة؟', 'Can I exchange my item for a different size?', 'التبديل المباشر غير متاح. قم بطلب جديد للمقاس المطلوب ثم أرجع المنتج الحالي.', 'Direct exchange is not possible. Place a new order and return the original item.', 4, 'active', NOW(), NOW()),
('returns', 'متى أستلم المبلغ المسترد؟', 'When will I get my refund?', 'بعد معالجة المرتجع من قسم الإرجاع، ستصلك مذكرة دائن ورسالة تأكيد عبر البريد.', 'As soon as the returns department processes your parcel, you receive a credit memo and email confirmation.', 5, 'active', NOW(), NOW()),
('returns', 'هل أدفع تكلفة إرجاع المنتجات غير المرغوبة؟', 'Do I have to pay to return unwanted goods?', 'نعم، تكلفة إرجاع المنتجات غير المرغوبة على العميل، إلا إذا كان المنتج خاطئًا أو تالفًا. احتفظ بإثبات الشحن.', 'Yes. You cover return shipping for unwanted goods unless the item is faulty or wrong. Keep proof of postage.', 6, 'active', NOW(), NOW()),
('returns', 'كم تستغرق عملية استرجاع المبلغ بالكامل؟', 'How much time does it take overall to get my money back?', 'قد تستغرق الدورة الكاملة للاسترداد 3-4 أسابيع في بعض الحالات بسبب الجمارك والإجراءات الإدارية.', 'Due to customs and processing steps, total refund time may take around 3-4 weeks in some cases.', 7, 'active', NOW(), NOW()),
('returns', 'ما تعليمات الإرجاع المتعلقة بـ Brexit؟', 'What Brexit changes do I need to pay attention to?', 'في إرجاع المملكة المتحدة يجب كتابة: Returned Goods - Failed Sale.
وإضافة رقم الطلب الأصلي على الصندوق ونماذج الجمارك (CN22/CN23).
البيانات المطلوبة:
VAT: ATU 62801445
EORI: AT EOS 1000 22085', 'For UK returns, mark the parcel clearly with: Returned Goods - Failed Sale.
Also include your original order number on the outside and on customs declaration (CN22/CN23).
Required info:
VAT: ATU 62801445
EORI: AT EOS 1000 22085', 8, 'active', NOW(), NOW()),
('glove-guide', 'كيف أجد قفاز الحراسة المثالي؟', 'The ultimate glove guide: how do I find the ideal goalkeeper gloves?', 'يعتمد الاختيار المناسب على أسلوب لعبك، نوع الأرضية، حالة الطقس، تفضيلك للملاءمة، وعدد مرات التدريب أو المباريات.', 'Goalkeeper gloves are your most important tool. The right choice depends on your playing style, surface, weather, fit preference, and how often you train or play matches.', 1, 'active', NOW(), NOW()),
('glove-guide', 'ما أول الأشياء التي يجب تقييمها قبل الشراء؟', 'What should I evaluate first before buying?', 'ابدأ بأربع نقاط رئيسية: العناية والمتانة، نوع اللاتكس، نوع القصّة، وفئة الموديل. هذه العناصر تحدد التماسك والراحة والتحكم والعمر الافتراضي.', 'Start with four core points: glove care and durability, palm foam type, cut type, and model family. These determine grip, comfort, control, and glove lifespan.', 2, 'active', NOW(), NOW()),
('glove-guide', 'كيف أختار نوع اللاتكس المناسب؟', 'How do I choose the right palm foam?', 'يمكن تقسيم اللاتكس إلى 4 فئات:
✔ Match: تماسك قوي في الجاف مع تآكل أعلى.
✔ Aqua: توازن بين التماسك والتحمل في البلل.
✔ Training: متين للتدريب المنتظم.
✔ Indoor/Artificial: مناسب للأسطح القاسية لتقليل التآكل.', 'Palm foam selection can be grouped into 4 categories:
✔ Match: maximum grip in dry conditions, higher wear.
✔ Aqua: balanced grip/durability, especially for wet conditions.
✔ Training: robust and durable for regular use.
✔ Indoor/Artificial: made for hard surfaces with reduced abrasion.', 3, 'active', NOW(), NOW()),
('glove-guide', 'ما القصّات التي يجب مقارنتها؟', 'Which glove cuts should I compare?', 'أهم القصّات:
✔ Negative Cut (NC): ضيق وخياطة داخلية.
✔ Hybrid / Power Cut: دمج بين أكثر من قصّة لزيادة التحكم.
✔ Gunn Cut (GC): سطح تماس أكبر.
✔ Regular Cut (RC): ملاءمة أوسع وإحساس كلاسيكي.', 'Common cuts and feel:
✔ Negative Cut (NC): tight fit, inner seams.
✔ Hybrid / Power Cut: mix of cuts for control.
✔ Gunn Cut (GC): round latex construction, larger contact area.
✔ Regular Cut (RC): roomier fit and classic feel.', 4, 'active', NOW(), NOW()),
('glove-guide', 'كيف أختار الموديل المناسب؟', 'How do I choose the right model?', 'اختر حسب مستواك واحتياجك: متانة للمبتدئ، تحكم وملاءمة للمتقدم، أو أداء احترافي للمباريات.', 'From all-round classics to high-tech gloves with improved grip and stability, choose based on your level and needs: beginner durability, advanced fit/control, or pro-level match performance.', 5, 'active', NOW(), NOW()),
('glove-guide', 'أفضل مع Strap أم بدون Strap؟', 'With strap or without strap?', 'بدون Strap يعطي ملاءمة حديثة وقريبة، بينما Strap يضيف تثبيتًا إضافيًا للرسغ ويفضله من يحتاج ثبات أعلى.', 'Strapless gloves (for example modern knit-entry styles) offer a close and modern fit. Strap models add extra wrist fixation and are often preferred for more wrist stability.', 6, 'active', NOW(), NOW()),
('glove-guide', 'هل قفازات الأطفال تحتاج اعتبارات مختلفة؟', 'Do kids goalkeeper gloves need different considerations?', 'نعم، يجب التركيز على المقاس الآمن، الراحة، الحماية، وتماسك مناسب لليد أثناء التطور.', 'Yes. Kids gloves should prioritize safe fit, enough protection, and reliable grip while staying comfortable for developing hands and regular training sessions.', 7, 'active', NOW(), NOW()),
('glove-guide', 'ما أهمية المقاس في القفازات؟', 'How important is glove size in this guide?', 'المقاس الصحيح أساسي جدًا للأداء. الموديلات الضيقة تحتاج ملاءمة دقيقة، بينما القصّات الأوسع تعطي مساحة حركة وراحة أكبر.', 'Very important. Correct size is essential for performance. Tight models should fit precisely, while wider cuts can provide more room and comfort.', 8, 'active', NOW(), NOW()),
('goalkeeper glove models', 'ما الفرق بين خطوط موديلات القفازات؟', 'How are glove model lines different?', 'تختلف الموديلات في الملاءمة، بناء ظهر القفاز، نوع الإغلاق، والإحساس العام عند التعامل مع الكرة.', 'Model lines differ in fit, backhand construction, closure type, and overall contact feeling on the ball.', 1, 'active', NOW(), NOW()),
('goalkeeper glove models', 'كيف أقارن بسرعة بين موديلين؟', 'How can I compare two glove models quickly?', 'قارن نوع اللاتكس، القصّة، نظام السوار، ونوع الاستخدام (مباريات/تدريب/استخدام شامل).', 'Compare palm foam type, cut, strap system, and intended use (match/training/all-round).', 2, 'active', NOW(), NOW()),
('goalkeeper glove cuts', 'ما المقصود بقصّة القفاز؟', 'What is a glove cut?', 'قصّة القفاز هي طريقة خياطة الأصابع والكف، وهي تؤثر على الملاءمة والإحساس والتحكم أثناء الالتقاط.', 'A glove cut describes finger and palm stitching construction, affecting fit, touch, and catching surface.', 1, 'active', NOW(), NOW()),
('goalkeeper glove cuts', 'ما أشهر قصّات القفازات؟', 'What cuts are most common?', 'القصّات الأكثر شيوعًا: Negative Cut وRoll Finger وHybrid وFlat Cut، ولكل قصّة إحساس وملاءمة مختلفة.', 'Popular cuts include Negative Cut, Roll Finger, Hybrid, and Flat Cut, each with different fit and feel.', 2, 'active', NOW(), NOW()),
('glove-care', 'كيف أنظف قفازات الحراسة بالشكل الصحيح؟', 'How do I clean goalkeeper gloves correctly?', 'اشطف القفازات بماء فاتر بعد كل استخدام ونظّف اللاتكس يدويًا بلطف. تجنب المواد الكيميائية القوية والغسيل الآلي.', 'Rinse gloves with lukewarm water after each use and clean latex gently by hand. Avoid aggressive chemicals and avoid washing machines.', 1, 'active', NOW(), NOW()),
('glove-care', 'كيف أجفف القفازات بشكل صحيح؟', 'How should I dry goalkeeper gloves?', 'اترك القفازات لتجف طبيعيًا في الهواء، وبعيدًا عن الشمس المباشرة أو المدافئ والحرارة العالية لحماية اللاتكس والخياطة.', 'Let gloves air-dry naturally. Keep them away from direct sunlight, radiators, and high heat to protect latex and seams.', 2, 'active', NOW(), NOW()),
('glove-care', 'كيف أخزن القفازات بين الحصص؟', 'How should I store gloves between sessions?', 'احفظ القفازات في كيس يسمح بالتهوية وفي مكان بارد وجاف، ولا تتركها مبللة داخل حقيبة مغلقة لفترة طويلة.', 'Store gloves in a breathable bag in a cool, dry place. Do not leave them wet inside closed compartments for long periods.', 3, 'active', NOW(), NOW()),
('glove-care', 'كيف أحافظ على التماسك لأطول فترة؟', 'How do I preserve grip performance for longer?', 'نظّف القفازات بعد كل تدريب/مباراة، ورطّب راحة اليد قليلًا قبل اللعب عند الحاجة، وتجنب الاحتكاك غير الضروري بالأسطح الخشنة.', 'Clean after every session, slightly moisten palms before play when needed, and avoid unnecessary contact with abrasive surfaces.', 4, 'active', NOW(), NOW()),
('pro tips on goalkeeper gloves palm foams', 'أي نوع لاتكس يعطي أقوى تماسك؟', 'Which palm foams provide the strongest grip?', 'أنواع لاتكس المباريات الاحترافية تقدم عادة أعلى تماسك في الظروف الجافة، بينما الأنواع المتينة تدوم أكثر مع الاستخدام المكثف.', 'Premium match foams usually provide top grip in clean conditions, while durable foams last longer under heavy use.', 1, 'active', NOW(), NOW()),
('pro tips on goalkeeper gloves palm foams', 'كيف أطيل عمر لاتكس راحة اليد؟', 'How do I increase palm foam lifespan?', 'رطّب القفاز قليلًا قبل الاستخدام، وتجنب الأسطح الخشنة، ونظفه مباشرة بعد التدريب أو المباراة.', 'Slightly dampen before use, avoid abrasive surfaces, and clean immediately after training or matches.', 2, 'active', NOW(), NOW()),
('newsletter', 'لماذا أشترك في النشرة البريدية؟', 'Why should I subscribe to the newsletter?', 'المشتركون تصِلهم تحديثات الإصدارات الجديدة والعروض والمحتوى المخصص لحراس المرمى.', 'Newsletter subscribers receive updates about new releases, promotions, and keeper-focused content.', 1, 'active', NOW(), NOW()),
('newsletter', 'هل يمكن إلغاء الاشتراك في أي وقت؟', 'Can I unsubscribe at any time?', 'نعم، يمكنك إلغاء الاشتراك في أي وقت من رابط الإلغاء داخل رسائل النشرة.', 'Yes. You can unsubscribe anytime using the link in newsletter emails.', 2, 'active', NOW(), NOW()),
('contact us', 'ما أوقات العمل (CET)؟', 'Business hours (CET)', 'من الإثنين إلى الخميس 9:00-16:00، ويوم الجمعة 9:00-13:00.', 'Monday to Thursday 9:00-16:00 and Friday 9:00-13:00.', 1, 'active', NOW(), NOW()),
('contact us', 'الدردشة المباشرة', 'Live chat', 'يمكنك بدء محادثة مباشرة عبر أيقونة الدردشة داخل الموقع.', 'Use the on-site chat icon to start a live conversation.', 2, 'active', NOW(), NOW()),
('contact us', 'البريد الإلكتروني', 'Email', 'office@keepersport.net (عادة يتم الرد خلال 24 ساعة).', 'office@keepersport.net (emails are typically answered within 24 hours).', 3, 'active', NOW(), NOW()),
('contact us', 'واتساب', 'WhatsApp', '+43 (0)676 76646 11.', '+43 (0)676 76646 11.', 4, 'active', NOW(), NOW()),
('contact us', 'الهاتف', 'Phone', '+43 2252 766 46-43.', '+43 2252 766 46-43.', 5, 'active', NOW(), NOW());
