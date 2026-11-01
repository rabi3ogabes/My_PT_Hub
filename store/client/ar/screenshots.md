# Screenshots — Aurum client app (ar)

Eight portrait frames, in order. Captions are final copy for `ar`; headline ≤ 28 characters, sub-caption ≤ 45.

> **RTL:** compose every frame right-to-left: headline right-aligned, device mockup shifted to the left edge, arrows and progress indicators mirrored, and screens captured with the app in Arabic so the UI itself is RTL. Western digits are kept in data (`1,840`) to match the app; captions use Arabic punctuation (`،` `؛`). Gold accent stays on the reading start (right). Frame 8 shows Arabic on the right-hand phone.

## Frame 1 — سجّل تمرينك في ثوانٍ

- **Headline:** سجّل تمرينك في ثوانٍ
- **Sub-caption:** أوزان آخر مرة جاهزة. اضغط ✓ وأكمل.
- **Screen:** شاشة تسجيل التمرين، المجموعة 3 من 4، مؤقت راحة 1:12
- **Visual:** إبهام يضغط ✓ على صف جاهز؛ علامة ذهبية متحركة

## Frame 2 — خطتك بالذكاء الاصطناعي

- **Headline:** خطتك بالذكاء الاصطناعي
- **Sub-caption:** يراجعها مدربك قبل أن تصلك
- **Screen:** عرض أسبوع البرنامج مع شارة «راجعها الكوتش سارة»
- **Visual:** شريط الأسبوع مع تمرين اليوم مميّز

## Frame 3 — مدربك في جيبك

- **Headline:** مدربك في جيبك
- **Sub-caption:** رسائل وملاحظات صوتية ومتابعة أسبوعية
- **Screen:** محادثة مع ملاحظة صوتية وبطاقة تمرين مشتركة
- **Visual:** صورة المدرب أعلى الشاشة، والملاحظة الصوتية تعمل

## Frame 4 — التزم بسعراتك بسهولة

- **Headline:** التزم بسعراتك بسهولة
- **Sub-caption:** سجّل وجبة كاملة من خطتك بنقرة
- **Screen:** يوم التغذية: حلقة 1,840/2,200 سعرة وأشرطة الماكروز وزر «سجّل من الخطة»
- **Visual:** حلقات تمتلئ بالذهبي والأحمر والأزرق

## Frame 5 — امسح أو صوّر أو ابحث

- **Headline:** امسح أو صوّر أو ابحث
- **Sub-caption:** كبسة، مجبوس، هريس، كسكس وأكثر
- **Screen:** ماسح الباركود فوق نتائج بحث لأكلات خليجية
- **Visual:** إطار الكاميرا على باركود منتج

## Frame 6 — لن تخسر أي تمرين

- **Headline:** لن تخسر أي تمرين
- **Sub-caption:** يعمل من دون إنترنت ويكمل بعد الإغلاق
- **Screen:** شاشة التمرين مع شارة «غير متصل · ستتم المزامنة» ومؤقت على شاشة القفل
- **Visual:** قسمان: شاشة القفل + شاشة التمرين

## Frame 7 — كل رقم قياسي له احتفال

- **Headline:** كل رقم قياسي له احتفال
- **Sub-caption:** أرقام وصور وقياسات
- **Screen:** احتفال رقم قياسي فوق رسوم التقدّم
- **Visual:** قصاصات ذهبية، ومنحنى الوزن ينخفض

## Frame 8 — تمرّن بلغتك

- **Headline:** تمرّن بلغتك
- **Sub-caption:** العربية · Français · English · Español
- **Screen:** شاشة اليوم بالعربية بجانب الفرنسية
- **Visual:** هاتفان بتخطيطين متعاكسين

## Production spec (all frames)

| Target | Size (portrait) | Notes |
|---|---|---|
| iPhone 6.9" | 1320 × 2868 | required; App Store scales down to 6.5"/6.3" |
| iPhone 6.5" | 1284 × 2778 | legacy slot, same art |
| iPhone 5.5" | 1242 × 2208 | only if older devices are supported |
| iPad 13" | 2064 × 2752 | landscape variants for the coach app builder/calendar |
| Android phone | 1080 × 1920 (min) – 1440 × 3120 | 9:16 to 9:19.5 |
| Android 7" / 10" tablet | 1200 × 1920 / 1600 × 2560 | |

- Source: real app screens captured by `integration_test` screenshot runs (seeded demo tenant, `SEED_DEMO=true`), composited onto the Aurum template: charcoal `#0B0B0F` background, champagne gold `#D4AF6A` headline accents, Manrope 800 (Latin) / IBM Plex Sans Arabic 700 (Arabic), headline ≤ 28 characters on 2 lines max.
- Frames 1–3 carry the top benefits in large type; they are the only frames most shoppers see.
- Device status bar: 9:41, full battery, no carrier; dark mode for frames 1, 2, 6, 8 and light mode for 3, 4, 5, 7 to show both themes.
- Demo data only (flagged demo coach "Coach Sara"); no real client names, faces only from licensed stock with model release.
- Never show prices or competitor names in screenshots.
