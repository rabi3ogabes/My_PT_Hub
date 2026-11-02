# SEO keyword map

Primary keyword per page per locale, with search intent and internal links. One primary keyword per URL (no two URLs target the same primary in the same locale — prevents cannibalisation). Secondary keywords go in H2s, image alt text, FAQ questions and the meta description. Slugs are localized; `x-default` is the `/en` URL and every page carries hreflang for all four locales.

Research basis: the seed list from Phase 8 (personal trainer app, personal training software, online coaching app, fitness coaching platform, برنامج مدرب شخصي, تطبيق مدرب رياضي, logiciel coach sportif, application coach sportif, app entrenador personal) expanded with Google Keyword Planner / Search Console data per market (QA, SA, AE, FR, MA, ES) before launch. Re-validate volumes and difficulty every quarter and when Search Console shows impressions for unmapped queries (add a page or a section, never stuff).

## Rules

- **Title tag:** primary keyword first, ≤ 60 chars, `| Aurum` suffix (Arabic: `| Aurum` at the visual end, i.e. last in logical order).
- **H1:** natural variant of the primary keyword; **meta description** ≤ 155 chars with one secondary keyword and a benefit.
- **Arabic:** target Modern Standard Arabic queries first and Gulf colloquial variants (e.g. كوتش, رجيم, دايت) as secondary; never mix Latin transliteration into Arabic slugs.
- **Competitor names** only on `/compare/*` pages, factual and dated (legal review), never in store metadata.
- **Internal links:** every page links to at least two pages listed in its row and receives a link from its hub; anchor text = target page's primary keyword in that locale.
- **Schema:** SoftwareApplication (home, pricing, features), FAQPage (pricing, solutions), LocalBusiness/Person (coach & city pages), HowTo + VideoObject (exercises), Recipe (recipes), Article + BreadcrumbList (blog).

## Page map

### Home

Intent: **Commercial (brand + category)** · Internal links: /pricing, /features, /for-personal-trainers, /coaches, /switch

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en` | personal trainer app | personal training software, online coaching app, fitness coaching platform |
| ar | `/ar` | تطبيق مدرب شخصي | برنامج مدرب شخصي, منصة تدريب أونلاين, تطبيق مدرب رياضي |
| fr | `/fr` | application coach sportif | logiciel coach sportif, plateforme coaching en ligne, appli coach personnel |
| es | `/es` | app entrenador personal | software entrenador personal, app de coaching online, plataforma fitness para entrenadores |

### Pricing

Intent: **Commercial / transactional** · Internal links: /features/ai-copilot, /switch, /help/billing, /compare

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/pricing` | personal trainer software pricing | pt app cost, free personal trainer app, coaching software price |
| ar | `/ar/الأسعار` | سعر برنامج مدرب شخصي | تطبيق مدرب مجاني, اشتراك منصة تدريب |
| fr | `/fr/tarifs` | logiciel coach sportif prix | application coach sportif gratuite, tarif logiciel coaching |
| es | `/es/precios` | precio software entrenador personal | app entrenador personal gratis, coste software coaching |

### Features hub

Intent: **Commercial investigation** · Internal links: each /features/*, /pricing

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features` | personal training software features | client management, workout builder, meal planner for coaches |
| ar | `/ar/المميزات` | مميزات برنامج المدرب الشخصي | إدارة المتدربين, منشئ التمارين, خطط غذائية للمدربين |
| fr | `/fr/fonctionnalites` | fonctionnalités logiciel coach sportif | gestion clients coach, création programme musculation |
| es | `/es/funciones` | funciones software entrenador personal | gestión de clientes, creador de rutinas, planes de dieta |

### Feature: workouts

Intent: **Commercial investigation** · Internal links: /exercises, /templates/workout-programs, /features/ai-copilot

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/workouts` | workout builder for personal trainers | program builder app, workout plan software, exercise library with videos |
| ar | `/ar/المميزات/التمارين` | برنامج تصميم تمارين للمدربين | جدول تمارين جيم, مكتبة تمارين بالفيديو |
| fr | `/fr/fonctionnalites/entrainements` | créer programme musculation clients | logiciel programme sportif, bibliothèque exercices vidéo |
| es | `/es/funciones/entrenamientos` | crear rutinas de entrenamiento para clientes | software de rutinas, biblioteca de ejercicios en video |

### Feature: nutrition

Intent: **Commercial investigation** · Internal links: /calculators/macros, /templates/meal-plans, /for-nutritionists, /recipes

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/nutrition` | meal planning software for coaches | nutrition coaching app, macro tracking for clients, ramadan meal plan |
| ar | `/ar/المميزات/التغذية` | برنامج خطط غذائية للمدربين | نظام غذائي رمضان, حساب ماكروز للمتدربين |
| fr | `/fr/fonctionnalites/nutrition` | logiciel plan alimentaire coach | suivi macros clients, plan repas ramadan |
| es | `/es/funciones/nutricion` | software de planes de dieta para entrenadores | seguimiento de macros, plan de comidas ramadán |

### Feature: AI copilot

Intent: **Commercial investigation** · Internal links: /pricing, /features/check-ins, /blog/ai-for-coaches

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/ai-copilot` | ai personal trainer software | ai workout generator for coaches, ai meal plan generator, ai check-in summaries |
| ar | `/ar/المميزات/الذكاء-الاصطناعي` | ذكاء اصطناعي للمدربين | مولد برامج تمارين بالذكاء الاصطناعي, خطة غذائية بالذكاء الاصطناعي |
| fr | `/fr/fonctionnalites/ia` | ia pour coach sportif | générateur programme musculation ia, plan alimentaire ia |
| es | `/es/funciones/ia` | ia para entrenadores personales | generador de rutinas con ia, plan de dieta con ia |

### Feature: check-ins

Intent: **Commercial investigation** · Internal links: /templates/check-in-forms, /features/ai-copilot

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/check-ins` | client check-in software | weekly check-in form for coaches, client progress tracking |
| ar | `/ar/المميزات/المتابعة` | متابعة المتدربين أونلاين | نموذج متابعة أسبوعية, تتبع تقدم المتدرب |
| fr | `/fr/fonctionnalites/bilans` | bilan hebdomadaire coaching | suivi progression clients, formulaire bilan coach |
| es | `/es/funciones/revisiones` | seguimiento de clientes online | formulario de revisión semanal, progreso de clientes |

### Feature: payments

Intent: **Commercial / transactional** · Internal links: /pricing, /for-gyms, /help/payments

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/payments` | personal trainer payment app | sell coaching packages online, session packs, invoices for trainers |
| ar | `/ar/المميزات/المدفوعات` | استلام مدفوعات المدرب الشخصي | بيع باقات تدريب أونلاين, مدى, آبل باي |
| fr | `/fr/fonctionnalites/paiements` | encaisser paiements coach sportif | vendre packs coaching en ligne, facture coach tva |
| es | `/es/funciones/pagos` | cobrar a clientes entrenador personal | vender paquetes de entrenamiento online, facturas |

### Feature: bookings

Intent: **Commercial investigation** · Internal links: /for-gyms, /features/payments

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/bookings` | personal trainer booking app | class booking software, session credits, scheduling for trainers |
| ar | `/ar/المميزات/الحجوزات` | تطبيق حجز جلسات تدريب | حجز كلاسات, رصيد الجلسات |
| fr | `/fr/fonctionnalites/reservations` | logiciel réservation coach sportif | réservation cours collectifs, crédits séances |
| es | `/es/funciones/reservas` | app de reservas para entrenadores | reservas de clases, bonos de sesiones |

### Feature: community

Intent: **Commercial investigation** · Internal links: /features/ai-copilot, /for-online-coaches

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/community` | fitness community app for coaches | fitness challenges app, client engagement |
| ar | `/ar/المميزات/المجتمع` | مجتمع لياقة للمدربين | تحديات رياضية, تفاعل المتدربين |
| fr | `/fr/fonctionnalites/communaute` | communauté fitness coach | défis sportifs clients, engagement clients |
| es | `/es/funciones/comunidad` | comunidad fitness para entrenadores | retos fitness, compromiso de clientes |

### Feature: white label

Intent: **Commercial / transactional** · Internal links: /for-gyms, /pricing

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/white-label` | white label fitness app | branded coaching app, custom personal trainer app |
| ar | `/ar/المميزات/تطبيق-باسمك` | تطبيق لياقة باسمك | تطبيق مدرب بعلامتك التجارية |
| fr | `/fr/fonctionnalites/marque-blanche` | application fitness marque blanche | appli coach personnalisée à vos couleurs |
| es | `/es/funciones/marca-blanca` | app fitness de marca blanca | app de entrenador con tu marca |

### Feature: integrations

Intent: **Commercial investigation** · Internal links: /help/integrations, /features/check-ins

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/features/integrations` | personal trainer app integrations | wearable sync for coaches, google calendar booking sync, zapier fitness |
| ar | `/ar/المميزات/التكاملات` | ربط الساعات الذكية للمدربين | مزامنة التقويم, واتساب للتذكير |
| fr | `/fr/fonctionnalites/integrations` | intégrations logiciel coach sportif | synchronisation montre connectée, agenda google |
| es | `/es/funciones/integraciones` | integraciones software entrenador | sincronizar reloj inteligente, calendario de google |

### Solution: personal trainers

Intent: **Commercial** · Internal links: /features/bookings, /features/payments, /coaches, /calculators/pt-pricing

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/for-personal-trainers` | personal training software | app for personal trainers, pt client management app |
| ar | `/ar/برنامج-المدرب-الشخصي` | برنامج مدرب شخصي | تطبيق للمدربين الشخصيين, إدارة متدربين |
| fr | `/fr/logiciel-coach-sportif` | logiciel coach sportif | application pour coach sportif, gestion clients coach |
| es | `/es/software-entrenador-personal` | software para entrenador personal | app para entrenadores personales, gestión de clientes |

### Solution: online coaches

Intent: **Commercial** · Internal links: /features/check-ins, /features/ai-copilot, /calculators/online-coach-income

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/for-online-coaches` | online coaching app | online fitness coaching platform, remote personal training software |
| ar | `/ar/التدريب-أونلاين` | تطبيق تدريب أونلاين | منصة تدريب رياضي عن بعد, مدرب أونلاين |
| fr | `/fr/coaching-en-ligne` | application coaching en ligne | plateforme coach sportif à distance, coaching sportif en ligne |
| es | `/es/coaching-online` | app de coaching online | plataforma de entrenamiento online, entrenador personal online |

### Solution: nutritionists

Intent: **Commercial** · Internal links: /features/nutrition, /templates/meal-plans, /recipes

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/for-nutritionists` | nutrition coaching software | dietitian app, meal plan software for nutritionists |
| ar | `/ar/لأخصائيي-التغذية` | برنامج أخصائي تغذية | برنامج خطط غذائية, تطبيق أخصائي تغذية |
| fr | `/fr/logiciel-nutritionniste` | logiciel nutritionniste | logiciel diététicien, plan alimentaire patient |
| es | `/es/software-nutricionista` | software para nutricionistas | programa de dietas para nutricionistas, app dietista |

### Solution: gyms & studios

Intent: **Commercial** · Internal links: /features/white-label, /features/bookings, /pricing

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/for-gyms` | gym management software | fitness studio software, personal training studio app |
| ar | `/ar/للأندية` | برنامج إدارة نادي رياضي | برنامج إدارة جيم, نظام حجز نادي |
| fr | `/fr/logiciel-salle-de-sport` | logiciel salle de sport | logiciel studio fitness, gestion salle de sport |
| es | `/es/software-gimnasio` | software para gimnasios | programa de gestión de gimnasio, estudio de entrenamiento |

### Coach directory

Intent: **Transactional (consumer)** · Internal links: /coaches/{city}, /for-personal-trainers (coach acquisition footer)

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/coaches` | find a personal trainer | online personal trainer, certified fitness coach |
| ar | `/ar/المدربين` | مدرب شخصي | أفضل مدرب شخصي, مدرب أونلاين, مدربة شخصية |
| fr | `/fr/coachs` | trouver un coach sportif | coach sportif en ligne, coach personnel |
| es | `/es/entrenadores` | encontrar entrenador personal | entrenador personal online, entrenadora personal |

### City: Doha

Intent: **Local transactional** · Internal links: /coaches, /blog/become-personal-trainer-qatar

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/coaches/doha` | personal trainer doha | female personal trainer doha, online coach qatar |
| ar | `/ar/المدربين/الدوحة` | مدرب شخصي الدوحة | مدربة شخصية في الدوحة, مدرب شخصي قطر |
| fr | `/fr/coachs/doha` | coach sportif doha | coach personnel qatar |
| es | `/es/entrenadores/doha` | entrenador personal doha | entrenador personal qatar |

### City: Riyadh

Intent: **Local transactional** · Internal links: /coaches, /coaches/jeddah

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/coaches/riyadh` | personal trainer riyadh | female personal trainer riyadh, online coach saudi |
| ar | `/ar/المدربين/الرياض` | مدرب شخصي الرياض | مدربة شخصية الرياض, مدرب خاص بالرياض |
| fr | `/fr/coachs/riyad` | coach sportif riyad | coach personnel arabie saoudite |
| es | `/es/entrenadores/riad` | entrenador personal riad | entrenador personal arabia saudita |

### City: Dubai

Intent: **Local transactional** · Internal links: /coaches, /coaches/abu-dhabi

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/coaches/dubai` | personal trainer dubai | home personal trainer dubai, online coach uae |
| ar | `/ar/المدربين/دبي` | مدرب شخصي دبي | مدربة شخصية دبي, مدرب شخصي الإمارات |
| fr | `/fr/coachs/dubai` | coach sportif dubai | coach personnel émirats |
| es | `/es/entrenadores/dubai` | entrenador personal dubai | entrenador personal emiratos |

### City: Paris

Intent: **Local transactional** · Internal links: /coaches, /coaches/lyon

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/coaches/paris` | personal trainer paris | english speaking personal trainer paris |
| ar | `/ar/المدربين/باريس` | مدرب شخصي باريس | مدرب عربي في باريس |
| fr | `/fr/coachs/paris` | coach sportif paris | coach sportif à domicile paris, coach personnel paris |
| es | `/es/entrenadores/paris` | entrenador personal paris | entrenador en parís |

### City: Casablanca

Intent: **Local transactional** · Internal links: /coaches, /coaches/rabat

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/coaches/casablanca` | personal trainer casablanca | online coach morocco |
| ar | `/ar/المدربين/الدار-البيضاء` | مدرب شخصي الدار البيضاء | كوتش رياضي المغرب |
| fr | `/fr/coachs/casablanca` | coach sportif casablanca | coach sportif maroc, coach à domicile casablanca |
| es | `/es/entrenadores/casablanca` | entrenador personal casablanca | entrenador personal marruecos |

### City: Madrid

Intent: **Local transactional** · Internal links: /coaches, /coaches/barcelona

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/coaches/madrid` | personal trainer madrid | english personal trainer madrid |
| ar | `/ar/المدربين/مدريد` | مدرب شخصي مدريد | مدرب عربي مدريد |
| fr | `/fr/coachs/madrid` | coach sportif madrid | coach personnel espagne |
| es | `/es/entrenadores/madrid` | entrenador personal madrid | entrenador personal a domicilio madrid |

### Exercise library

Intent: **Informational** · Internal links: /exercises/muscle/*, /templates/workout-programs, /features/workouts

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/exercises` | exercise library | gym exercises with videos, exercises by muscle |
| ar | `/ar/التمارين` | تمارين جيم | تمارين بالفيديو, تمارين لكل عضلة |
| fr | `/fr/exercices` | exercices musculation | exercices salle de sport vidéo, exercices par muscle |
| es | `/es/ejercicios` | ejercicios de gimnasio | ejercicios con video, ejercicios por músculo |

### Exercise page template (example: bench press)

Intent: **Informational (how-to)** · Internal links: related exercises (alternatives), muscle hub, programs using it, /calculators/one-rep-max

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/exercises/bench-press` | how to bench press | bench press form, bench press muscles worked, bench press alternatives |
| ar | `/ar/التمارين/ضغط-صدر-بالبار` | تمرين ضغط الصدر بالبار | طريقة ضغط الصدر الصحيحة, عضلات ضغط الصدر |
| fr | `/fr/exercices/developpe-couche` | développé couché | technique développé couché, muscles développé couché |
| es | `/es/ejercicios/press-de-banca` | press de banca | técnica press de banca, músculos press de banca |

### Templates hub

Intent: **Informational / lead magnet** · Internal links: /templates/*, /features/*, /pricing

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/templates` | free workout program templates | personal trainer templates, free meal plan template, check-in form template |
| ar | `/ar/النماذج` | جدول تمارين جاهز | برامج تمارين مجانية, نموذج خطة غذائية |
| fr | `/fr/modeles` | programme musculation gratuit | modèle programme sportif, modèle plan alimentaire |
| es | `/es/plantillas` | rutinas de gimnasio gratis | plantilla de rutina, plantilla plan de comidas |

### Template: Ramadan meal plan

Intent: **Informational / seasonal** · Internal links: /features/nutrition, /calculators/macros, /recipes, /coaches

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/templates/meal-plans/ramadan-meal-plan` | ramadan meal plan | ramadan diet plan, suhoor and iftar meal plan |
| ar | `/ar/النماذج/خطط-غذائية/رجيم-رمضان` | رجيم رمضان | جدول أكل صحي في رمضان, نظام غذائي رمضان سحور وإفطار |
| fr | `/fr/modeles/plans-alimentaires/plan-alimentaire-ramadan` | plan alimentaire ramadan | menu ramadan équilibré, repas shour iftar |
| es | `/es/plantillas/planes-de-comidas/plan-ramadan` | plan de comidas ramadán | dieta ramadán, comidas suhur iftar |

### Calculator: TDEE / calories

Intent: **Informational (tool)** · Internal links: /calculators/macros, /features/nutrition, /templates/meal-plans

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/calculators/tdee` | tdee calculator | calorie calculator, maintenance calories, bmr calculator |
| ar | `/ar/الحاسبات/السعرات` | حاسبة السعرات الحرارية | حساب السعرات اليومية, معدل الأيض |
| fr | `/fr/calculateurs/calories` | calcul calories journalières | calculateur métabolisme de base, besoin calorique |
| es | `/es/calculadoras/calorias` | calculadora de calorías | calorías de mantenimiento, metabolismo basal |

### Calculator: macros

Intent: **Informational (tool)** · Internal links: /calculators/tdee, /calculators/protein, /features/nutrition

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/calculators/macros` | macro calculator | macros for weight loss, protein carbs fat calculator |
| ar | `/ar/الحاسبات/الماكروز` | حساب الماكروز | حاسبة البروتين والكارب والدهون |
| fr | `/fr/calculateurs/macros` | calcul macros | calculateur macronutriments, macros sèche |
| es | `/es/calculadoras/macros` | calculadora de macros | macros para perder grasa, calcular macronutrientes |

### Calculator: one-rep max

Intent: **Informational (tool)** · Internal links: /exercises/bench-press, /features/workouts

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/calculators/one-rep-max` | 1rm calculator | one rep max calculator, max bench calculator |
| ar | `/ar/الحاسبات/أقصى-تكرار` | حاسبة أقصى وزن | حساب الوزن الأقصى لتكرار واحد |
| fr | `/fr/calculateurs/1rm` | calcul 1rm | calculateur charge maximale, rm musculation |
| es | `/es/calculadoras/1rm` | calculadora 1rm | calcular repetición máxima |

### Calculator: PT pricing

Intent: **Informational → commercial** · Internal links: /for-personal-trainers, /features/payments, /pricing

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/calculators/pt-pricing` | how much to charge as a personal trainer | personal trainer rates calculator |
| ar | `/ar/الحاسبات/سعر-المدرب` | كم سعر المدرب الشخصي | تسعير جلسات التدريب الشخصي |
| fr | `/fr/calculateurs/tarif-coach` | tarif coach sportif | prix séance coach sportif, combien facturer coach |
| es | `/es/calculadoras/tarifa-entrenador` | cuánto cobra un entrenador personal | precio sesión entrenador personal |

### Calculator: online coach income

Intent: **Informational → commercial** · Internal links: /for-online-coaches, /pricing

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/calculators/online-coach-income` | online fitness coach income | how much do online coaches make |
| ar | `/ar/الحاسبات/دخل-المدرب-أونلاين` | كم يربح المدرب أونلاين | دخل المدرب الرياضي |
| fr | `/fr/calculateurs/revenu-coach-en-ligne` | salaire coach sportif en ligne | combien gagne un coach sportif |
| es | `/es/calculadoras/ingresos-entrenador-online` | cuánto gana un entrenador online | sueldo entrenador personal |

### Recipes

Intent: **Informational** · Internal links: /features/nutrition, /templates/meal-plans, /calculators/macros

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/recipes` | high protein recipes | healthy recipes with macros, gulf healthy recipes |
| ar | `/ar/الوصفات` | وصفات صحية | وصفات عالية البروتين, كبسة صحية |
| fr | `/fr/recettes` | recettes riches en protéines | recettes healthy macros, couscous healthy |
| es | `/es/recetas` | recetas altas en proteína | recetas saludables con macros |

### Blog pillar: become a PT (local)

Intent: **Informational** · Internal links: /for-personal-trainers, /calculators/pt-pricing, /coaches/doha

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/blog/how-to-become-a-personal-trainer-in-qatar` | how to become a personal trainer in qatar | personal trainer certification qatar, pt license qatar |
| ar | `/ar/blog/كيف-تصبح-مدرب-شخصي-في-قطر` | كيف تصبح مدرب شخصي | شهادة مدرب شخصي, رخصة مدرب في قطر |
| fr | `/fr/blog/devenir-coach-sportif-en-ligne` | devenir coach sportif en ligne | diplôme coach sportif, créer son activité de coach |
| es | `/es/blog/como-ser-entrenador-personal-online` | cómo ser entrenador personal online | certificación entrenador personal, emprender como entrenador |

### Compare hub + vs pages

Intent: **Commercial investigation (competitor)** · Internal links: /switch, /pricing, /features

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/compare` | best personal trainer software | personal trainer app comparison, alternatives to {competitor} |
| ar | `/ar/مقارنة` | أفضل برنامج مدرب شخصي | مقارنة تطبيقات المدربين, بديل {competitor} |
| fr | `/fr/comparatif` | meilleur logiciel coach sportif | comparatif application coach, alternative à {competitor} |
| es | `/es/comparativa` | mejor software para entrenadores | comparativa apps de entrenador, alternativa a {competitor} |

### Migration landing

Intent: **Transactional** · Internal links: /compare, /pricing, /help/import

| Locale | URL | Primary keyword | Secondary keywords |
|---|---|---|---|
| en | `/en/switch` | switch personal trainer software | import clients from another coaching app, migrate coaching platform |
| ar | `/ar/الانتقال` | الانتقال من برنامج مدرب آخر | نقل المتدربين إلى منصة جديدة |
| fr | `/fr/changer` | changer de logiciel coach sportif | importer clients, migration plateforme coaching |
| es | `/es/cambiar` | cambiar de software de entrenador | importar clientes, migrar plataforma |

## Internal linking model

```
Home ──► Features hub ──► Feature pages ──► Pricing
  │            ▲                 │
  ├──► Solutions (PT, online, nutritionists, gyms) ──► relevant features + calculators
  ├──► Coaches directory ──► City pages ──► Coach profiles ──► (checkout / booking)
  ├──► Resources: Exercises ─► exercise pages ─► programs/templates ─► Features: workouts
  │              Templates ─► gated download ─► nurture email ─► trial
  │              Calculators ─► related calculator ─► feature page ─► pricing
  │              Recipes ─► meal-plan templates ─► Features: nutrition
  │              Blog pillars ─► clusters ─► solution page
  └──► Compare ─► Switch ─► Pricing
```

## Tracking

- Search Console property per locale folder (`/en/`, `/ar/`, `/fr/`, `/es/`); weekly export of position and CTR per primary keyword into the KPI dashboard.
- Rank tracker list = the primary keywords above (36 pages × 4 locales) + 10 store keywords per locale (ASO, see `store/*/*/keywords.txt`).
- Quarterly content review: pages with impressions but CTR < 2 % get a title/meta rewrite; pages ranking 8–20 get internal links and content depth.
