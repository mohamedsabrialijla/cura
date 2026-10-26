(function () {
  "use strict";

  var STORAGE_KEY = "site_lang";

  var I18N = {
    ar: {
      "meta.title": "كيورا الطبي | CURA Medical Team",
      "top.hours": "فريق طلابي تطوعي — تعلّم • بحث • نمو",
      "top.palestine": "غزة · فلسطين",
      "top.phone": "اتصل بنا: +970 567 744 746",
      "hero.palestine": "من قطاع غزة — فلسطين",
      "nav.home": "الرئيسية",
      "nav.about": "من نحن",
      "nav.offer": "ماذا نقدم",
      "nav.story": "قصتنا",
      "nav.vision": "الرؤية",
      "nav.gallery": "الألبومات",
      "nav.contact": "تواصل معنا",
      "nav.cta": "تعاون معنا",
      "hero1.title": "كيورا الطبي",
      "hero1.text": "نتعلّم اليوم لِنُبدع غدًا. فريق طلابي تطوعي يخدم طلبة الكليات الطبية في غزة عبر التدريب العملي والمعرفة.",
      "hero1.btn": "تعرّف علينا",
      "hero2.title": "التعلّم يبدأ بالممارسة",
      "hero2.text": "دورات تدريبية تجمع الجانب النظري بالتطبيق المباشر: مهارات التمريض الأساسية، الخياطة الجراحية، والوريد.",
      "hero2.btn": "ماذا نقدم",
      "hero3.title": "LEARN • RESEARCH • GROW",
      "hero3.text": "نطمح أن نرافق الطالب الطبي في مسيرته: مهارة عملية، محتوى معرفي، وفرص للنمو الأكاديمي والمهني.",
      "hero3.btn": "تواصل معنا",
      "pillars.learn.title": "نتعلّم",
      "pillars.learn.text": "تعليم طبي جيد لا يبقى حكرًا على من يملك الإمكانيات أو فرصة الوصول.",
      "pillars.practice.title": "نمارس",
      "pillars.practice.text": "تدريب عملي مباشر يسد الفجوة بين ما يُدرَّس نظريًا وما يحتاجه الطالب فعليًا.",
      "pillars.research.title": "نبحث",
      "pillars.research.text": "منصة معرفية تجمع محتوى تعليميًا وبحثيًا ومعلومات عن الفرص الدولية.",
      "pillars.grow.title": "نموّ",
      "pillars.grow.text": "منظومة تدريب وتعلّم مستمرة لطلبة الطب والتمريض والتخصصات الصحية.",
      "cta.title": "شراكة من أجل التعليم الطبي",
      "cta.text": "نرى أرضية مشتركة حقيقية مع الجهات التي تدعم الكوادر الطبية وتدريبها. يسعدنا بحث صيغة تعاون تناسب عملكم: دعم مالي، دعم عيني، أو احتضان مؤسسي.",
      "cta.btn": "لنبدأ الحديث",
      "about.title": "من نحن",
      "about.lead": "فريق طلابي تطوعي يضم طلبة الكليات الطبية والصحية",
      "about.heading": "كيورا الطبي | CURA Medical Team",
      "about.p1": "انطلقنا من إيمان أعضائنا بأن التعليم الطبي الجيد لا يجب أن يبقى حكرًا على من يملك الإمكانيات أو الفرصة للوصول إليه. نعمل بشكل رئيسي على خدمة طلبة الكليات الطبية في غزة، في ظل الظروف الاستثنائية التي يمر بها القطاع التعليمي هناك.",
      "about.li1": "فريق طلابي تطوعي من طلبة الطب والعلوم الصحية",
      "about.li2": "خدمة أساسية لطلبة غزة في ظروف استثنائية",
      "about.li3": "هوية بصرية وخطة عمل واضحة، ودورات فعلية على الأرض",
      "about.p2": "شعارنا: تعلّم • بحث • نمو. نتعلّم اليوم لِنُبدع غدًا.",
      "offer.title": "ماذا نقدم",
      "offer.lead": "تدريب عملي، ومنصة معرفية مستمرة وليست نشاطًا موسميًا",
      "offer.s1.title": "دورات تدريبية عملية",
      "offer.s1.text": "نجمع الجانب النظري بالتطبيق المباشر في تخصصات طبية وصحية متعددة، ونتوسع بحسب احتياجات الطلبة والموارد المتاحة.",
      "offer.s2.title": "مهارات التمريض والخياطة",
      "offer.s2.text": "دورات شملت الوريد والقسطرة الوريدية وسحب الدم، إضافة إلى تدريب عملي على الخياطة الجراحية بخطوات آمنة.",
      "offer.s3.title": "منصة معرفية",
      "offer.s3.text": "نعمل على بناء مرجع يجمع محتوى تعليميًا وبحثيًا ومعلومات عن الفرص الدولية للطلبة.",
      "offer.s4.title": "أثر ملموس على المشاركين",
      "offer.s4.text": "الدورات على الأرض أثبتت أن الفكرة قابلة للتطبيق وأن لها أثرًا حقيقيًا على الطلبة المشاركين.",
      "story.title": "كيف بدأت الفكرة",
      "story.lead": "من نقاشات فردية إلى فريق منظم له أثر على الأرض",
      "story.h": "فجوة حقيقية بين النظرية وما يحتاجه الطالب",
      "story.p": "بدأت كيورا كمبادرة صغيرة بين طلبة لاحظوا فجوة بين ما يُدرَّس نظريًا وما يحتاجه الطالب من مهارات عملية وموارد معرفية موثوقة. تحوّلت الفكرة إلى فريق منظم له هوية بصرية وخطة عمل، ثم إلى دورات تدريبية فعلية — نقطة تحول أثبتت أن الفكرة قابلة للتطبيق.",
      "vision.title": "رؤيتنا وأهدافنا",
      "vision.lead": "مصدر موثوق يرافق الطالب الطبي في مسيرته",
      "vision.h": "منظومة تدريب وتعلّم مستمرة وليست دورات متفرقة",
      "vision.p": "نطمح أن يكون كيورا مصدرًا موثوقًا يعزّز المهارات العملية ويربط الطالب بمحتوى وفرص تساعده على النمو الأكاديمي والمهني. هدفنا بناء منظومة مستدامة لطلبة كليات الطب والتمريض والتخصصات الصحية المرتبطة بها.",
      "plan.title": "خطتنا المستقبلية",
      "plan.lead": "توسيع الدورات، تطوير المنصة، وبناء شراكات مستدامة",
      "plan.p": "نعمل على إعداد دراسة جدوى تفصيلية نعرضها على الشركاء والجهات الداعمة، لتوسيع نطاق الدورات من حيث العدد والتخصصات، وتطوير المنصة المعرفية. نطمح لشراكات تمكّننا من برامج أكثر تنظيمًا وأثرًا على المدى الطويل.",
      "partners.title": "نبحث عن شركاء",
      "partners.lead": "دعم مالي، دعم عيني، أو احتضان مؤسسي",
      "partners.p": "نتطلع لبحث التعاون بالشكل الذي يتناسب مع طبيعة عملكم وسياساتكم. يسعدنا ترتيب مكالمة أو اجتماع قصير لعرض التفاصيل ومناقشة الصيغة الأنسب، أو أي شكل تعاون ترونه ملائمًا.",
      "partners.m1.title": "دعم مالي",
      "partners.m1.text": "يساعدنا على توسيع الدورات التدريبية وتغطية تكاليفها.",
      "partners.m2.title": "دعم عيني",
      "partners.m2.text": "أدوات تدريب، مواد تعليمية، ومساحات للتدريب العملي.",
      "partners.m3.title": "احتضان مؤسسي",
      "partners.m3.text": "إطار يدعم استمرارية الفريق ونموّه على المدى الطويل.",
      "license.title": "بخصوص الترخيص المؤسسي",
      "license.p": "نود أن نكون واضحين وشفافين: كيورا الطبي حاليًا فريق طلابي تطوعي، وليس مسجلاً كجمعية أو مؤسسة قانونية مستقلة، وبالتالي لا نملك في الوقت الراهن ترخيصًا مؤسسيًا رسميًا. نحن على دراية بأهمية هذا الجانب، ونعمل ضمن خطتنا التطويرية على استكمال الإجراءات الرسمية اللازمة لتأسيس الفريق بشكل قانوني في المرحلة المقبلة، بما يتناسب مع نمو عملنا وحجم الشراكات التي نسعى لبنائها.",
      "gallery.title": "ألبوماتنا",
      "gallery.lead": "لحظات من دوراتنا التدريبية العملية على الأرض",
      "gallery.filter.all": "الكل",
      "gallery.filter.nursing": "مهارات التمريض",
      "gallery.filter.suturing": "ورشة الخياطة الأساسية",
      "gallery.filter.ventilation": "التنفس الصناعي",
      "gallery.filter.taghriz": "إكمال دورة التغريز",
      "gallery.filter.firstaid": "الإسعافات الأولية",
      "gallery.album.nursing.title": "مهارات التمريض الأساسية",
      "gallery.album.nursing.lead": "وريد، قسطرة وريدية، وسحب دم — تدريب عملي مباشر",
      "gallery.album.suturing.title": "ورشة مهارات الخياطة الأساسية",
      "gallery.album.suturing.lead": "Basic Suturing Skills Workshop — تدريب عملي على إغلاق الجروح مع الدكتور أسامة مصلح",
      "gallery.album.ventilation.title": "التنفس الصناعي",
      "gallery.album.ventilation.lead": "Mechanical Ventilation — محاضرة وتطبيق عملي على مفاهيم التهوية الميكانيكية",
      "gallery.album.taghriz.title": "إكمال دورة التغريز",
      "gallery.album.taghriz.lead": "جلسة إكمال وتدريب عملي على مهارات التغريز",
      "gallery.album.firstaid.title": "دورة الإسعافات الأولية",
      "gallery.album.firstaid.lead": "تدريب عملي على أساسيات الإسعافات الأولية",
      "contact.title": "تواصل معنا",
      "contact.lead": "شاكرين اهتمامكم، ونتطلع للتواصل قريبًا",
      "contact.address": "غزة — خدمة طلبة الكليات الطبية والصحية",
      "contact.person": "مؤسس من قبل فريق تطوعي من الأطباء والمدربين",
      "contact.form.name": "الاسم",
      "contact.form.email": "البريد الإلكتروني",
      "contact.form.subject": "الموضوع",
      "contact.form.message": "الرسالة",
      "contact.form.send": "إرسال رسالة",
      "footer.about": "فريق طلابي تطوعي يخدم التعليم الطبي العملي في غزة.",
      "footer.links": "روابط سريعة",
      "footer.copy": "جميع الحقوق محفوظة",
      "footer.designed": "كيورا الطبي | LEARN • RESEARCH • GROW",
      "footer.palestine": "من فلسطين — لغزة وللتعليم الطبي"
    },
    en: {
      "meta.title": "CURA Medical Team | كيورا الطبي",
      "top.hours": "Student volunteer team — Learn • Research • Grow",
      "top.palestine": "Gaza · Palestine",
      "top.phone": "Call us: +970 567 744 746",
      "hero.palestine": "From Gaza Strip — Palestine",
      "nav.home": "Home",
      "nav.about": "Who we are",
      "nav.offer": "What we offer",
      "nav.story": "Our story",
      "nav.vision": "Vision",
      "nav.gallery": "Albums",
      "nav.contact": "Contact",
      "nav.cta": "Partner with us",
      "hero1.title": "CURA Medical Team",
      "hero1.text": "We learn today to excel tomorrow. A volunteer student team serving medical students in Gaza through hands-on training and knowledge.",
      "hero1.btn": "About us",
      "hero2.title": "Learning starts with practice",
      "hero2.text": "Training that joins theory with direct practice: basic nursing skills, surgical suturing, and IV access.",
      "hero2.btn": "What we offer",
      "hero3.title": "LEARN • RESEARCH • GROW",
      "hero3.text": "We aim to walk with the medical student: practical skills, knowledge, and opportunities for academic and professional growth.",
      "hero3.btn": "Get in touch",
      "pillars.learn.title": "Learn",
      "pillars.learn.text": "Quality medical education should not be limited to those who already have means or access.",
      "pillars.practice.title": "Practice",
      "pillars.practice.text": "Hands-on training that closes the gap between theory and the skills students actually need.",
      "pillars.research.title": "Research",
      "pillars.research.text": "A knowledge platform for educational and research content, and international opportunities.",
      "pillars.grow.title": "Grow",
      "pillars.grow.text": "A lasting training and learning system for medicine, nursing, and related health fields.",
      "cta.title": "Partnership for medical education",
      "cta.text": "We share common ground with organizations that support and train medical staff. We would welcome financial support, in-kind support, or institutional hosting — in a form that fits your work and policies.",
      "cta.btn": "Start a conversation",
      "about.title": "Who we are",
      "about.lead": "A volunteer student team from medical and health faculties",
      "about.heading": "CURA Medical Team | كيورا الطبي",
      "about.p1": "We started from the belief that good medical education should not remain limited to those who can afford it or reach it. We mainly serve medical students in Gaza, under the exceptional conditions facing education there.",
      "about.li1": "A volunteer student team from medicine and health sciences",
      "about.li2": "Focused on students in Gaza in exceptional circumstances",
      "about.li3": "A clear visual identity, a work plan, and real courses on the ground",
      "about.p2": "Our motto: Learn • Research • Grow. We learn today to excel tomorrow.",
      "offer.title": "What we offer",
      "offer.lead": "Practical training, and a knowledge platform that is not a seasonal activity",
      "offer.s1.title": "Hands-on training courses",
      "offer.s1.text": "We combine theory with direct practice across medical and health fields, and we expand according to student needs and available resources.",
      "offer.s2.title": "Nursing and suturing skills",
      "offer.s2.text": "Courses covering IV access, cannulation, and blood collection, plus hands-on surgical suturing practice with safe steps.",
      "offer.s3.title": "Knowledge platform",
      "offer.s3.text": "We are building a lasting reference of educational and research content, plus information on international opportunities.",
      "offer.s4.title": "A real effect on participants",
      "offer.s4.text": "Delivering courses on the ground proved the idea is workable and has a tangible impact on students who take part.",
      "story.title": "How the idea started",
      "story.lead": "From individual conversations to an organized team with impact on the ground",
      "story.h": "A real gap between theory and what students need",
      "story.p": "CURA began as a small initiative among students who saw a gap between classroom theory and the practical skills and reliable knowledge students actually need. It grew into an organized team with a visual identity and a work plan, then into real training courses — a turning point that showed the idea can work.",
      "vision.title": "Vision and goals",
      "vision.lead": "A trusted source that walks with the medical student",
      "vision.h": "A continuous system — not scattered courses",
      "vision.p": "We want CURA to strengthen practical skills and connect students with knowledge and opportunities for academic and professional growth. Our aim is a sustainable system for students of medicine, nursing, and related health fields.",
      "plan.title": "Our future plan",
      "plan.lead": "More courses, a stronger platform, and lasting partnerships",
      "plan.p": "We are preparing a detailed feasibility study for partners and supporters, to expand the number and range of courses and to develop the knowledge platform. We hope to build partnerships that make programs more organized and more lasting.",
      "partners.title": "We are looking for partners",
      "partners.lead": "Financial support, in-kind support, or institutional hosting",
      "partners.p": "We would like to explore cooperation that fits your work and policies. We would be glad to arrange a short call or meeting to present details and discuss the most suitable form — or any other form you consider appropriate.",
      "partners.m1.title": "Financial support",
      "partners.m1.text": "Helps us expand training courses and cover their costs.",
      "partners.m2.title": "In-kind support",
      "partners.m2.text": "Training tools, educational materials, and spaces for practical training.",
      "partners.m3.title": "Institutional hosting",
      "partners.m3.text": "A framework that supports the team’s continuity and growth over time.",
      "license.title": "On institutional licensing",
      "license.p": "We want to be clear and transparent: CURA Medical is currently a volunteer student team, not registered as an independent association or legal institution, and therefore we do not yet have official institutional licensing. We understand how important this is, and our development plan includes completing the official steps to establish the team legally in the next stage, in line with the growth of our work and the partnerships we seek to build.",
      "gallery.title": "Our albums",
      "gallery.lead": "Moments from our practical training courses on the ground",
      "gallery.filter.all": "All",
      "gallery.filter.nursing": "Nursing skills",
      "gallery.filter.suturing": "Basic suturing workshop",
      "gallery.filter.ventilation": "Mechanical ventilation",
      "gallery.filter.taghriz": "Suturing completion",
      "gallery.filter.firstaid": "First aid",
      "gallery.album.nursing.title": "Basic nursing skills",
      "gallery.album.nursing.lead": "IV access, cannulation, and blood collection — direct practice",
      "gallery.album.suturing.title": "Basic Suturing Skills Workshop",
      "gallery.album.suturing.lead": "Practical introduction to wound closure — presented by Dr. Osama Mosleh",
      "gallery.album.ventilation.title": "Mechanical ventilation",
      "gallery.album.ventilation.lead": "Lecture and practical concepts of mechanical ventilation",
      "gallery.album.taghriz.title": "Suturing course completion",
      "gallery.album.taghriz.lead": "Hands-on follow-up session on suturing skills",
      "gallery.album.firstaid.title": "First aid course",
      "gallery.album.firstaid.lead": "Practical training in basic first aid",
      "contact.title": "Contact us",
      "contact.lead": "Thank you for your time. We look forward to speaking soon.",
      "contact.address": "Gaza — serving students of medical and health faculties",
      "contact.person": "Founded by a volunteer team of doctors and trainers",
      "contact.form.name": "Name",
      "contact.form.email": "Email",
      "contact.form.subject": "Subject",
      "contact.form.message": "Message",
      "contact.form.send": "Send message",
      "footer.about": "A volunteer student team serving practical medical education in Gaza.",
      "footer.links": "Quick links",
      "footer.copy": "All rights reserved",
      "footer.designed": "CURA Medical Team | LEARN • RESEARCH • GROW",
      "footer.palestine": "From Palestine — for Gaza and medical education"
    }
  };

  function currentLang() {
    return localStorage.getItem(STORAGE_KEY) || "en";
  }

  function applyI18n(lang) {
    var dict = I18N[lang] || I18N.ar;
    document.querySelectorAll("[data-i18n]").forEach(function (el) {
      var key = el.getAttribute("data-i18n");
      if (dict[key] != null) el.textContent = dict[key];
    });
    document.querySelectorAll("[data-i18n-placeholder]").forEach(function (el) {
      var key = el.getAttribute("data-i18n-placeholder");
      if (dict[key] != null) el.setAttribute("placeholder", dict[key]);
    });
    if (dict["meta.title"]) document.title = dict["meta.title"];
  }

  function setLang(lang) {
    if (lang !== "ar" && lang !== "en") return;
    if (lang === currentLang()) return;
    localStorage.setItem(STORAGE_KEY, lang);
    window.location.reload();
  }

  document.addEventListener("DOMContentLoaded", function () {
    var lang = currentLang();
    applyI18n(lang);

    document.querySelectorAll("[data-set-lang]").forEach(function (btn) {
      btn.classList.toggle("active", btn.getAttribute("data-set-lang") === lang);
      btn.setAttribute("aria-pressed", btn.getAttribute("data-set-lang") === lang ? "true" : "false");
      btn.addEventListener("click", function () {
        setLang(btn.getAttribute("data-set-lang"));
      });
    });

    var form = document.getElementById("misbar-contact-form");
    if (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        var name = (form.querySelector('[name="name"]') || {}).value || "";
        var email = (form.querySelector('[name="email"]') || {}).value || "";
        var subject = (form.querySelector('[name="subject"]') || {}).value || "CURA Medical Team";
        var message = (form.querySelector('[name="message"]') || {}).value || "";
        var body = name + " <" + email + ">\n\n" + message;
        window.location.href =
          "mailto:info@cura-ps.org?subject=" +
          encodeURIComponent(subject) +
          "&body=" +
          encodeURIComponent(body);
      });
    }
  });
})();
