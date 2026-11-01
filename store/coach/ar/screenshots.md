# Screenshots — Aurum Coach app (ar)

Eight portrait frames, in order. Captions are final copy for `ar`; headline ≤ 28 characters, sub-caption ≤ 45.

> **RTL:** compose every frame right-to-left: headline right-aligned, device mockup shifted to the left edge, arrows and progress indicators mirrored, and screens captured with the app in Arabic so the UI itself is RTL. Western digits are kept in data (`1,840`) to match the app; captions use Arabic punctuation (`،` `؛`). Gold accent stays on the reading start (right). Frame 8 shows Arabic on the right-hand phone.

## Frame 1 — عملك كمدرب في تطبيق واحد

- **Headline:** عملك كمدرب في تطبيق واحد
- **Sub-caption:** برامج، تغذية، متابعات، مدفوعات
- **Screen:** لوحة المدرب: المؤشرات (المتدربون النشطون، الإيراد الشهري)، المعرّضون للانقطاع، جلسات اليوم
- **Visual:** بطاقات مؤشرات ذهبية على خلفية داكنة

## Frame 2 — الذكاء الاصطناعي يقترح وأنت توافق

- **Headline:** الذكاء الاصطناعي يقترح وأنت توافق
- **Sub-caption:** برامج وردود بأسلوبك أنت
- **Screen:** مراجعة مسودة المساعد: مقارنة مع «قبول / تعديل / إعادة»
- **Visual:** شريط الأوامر «✦ المساعد» مفتوح

## Frame 3 — صمّم البرامج في دقائق

- **Headline:** صمّم البرامج في دقائق
- **Sub-caption:** سوبر سِت، تاباتا، EMOM، AMRAP
- **Screen:** منشئ التمارين مع سوبر سِت وكتلة تاباتا
- **Visual:** سحب عنصر أثناء الحركة

## Frame 4 — اكتشف المعرّضين للانقطاع أولًا

- **Headline:** اكتشف المعرّضين للانقطاع أولًا
- **Sub-caption:** درجات الالتزام وتذكير بنقرة
- **Screen:** لوحة الالتزام بالأحمر والأصفر والأخضر ونافذة رسالة جماعية
- **Visual:** شارات حمراء تنبض بهدوء

## Frame 5 — المتابعات جنبًا إلى جنب

- **Headline:** المتابعات جنبًا إلى جنب
- **Sub-caption:** الإجابات والمؤشرات والصور معًا
- **Screen:** مراجعة المتابعة: مقارنة الصور + منحنى الوزن + التعليقات
- **Visual:** شريط قبل/بعد في المنتصف

## Frame 6 — استلم مدفوعاتك تلقائيًا

- **Headline:** استلم مدفوعاتك تلقائيًا
- **Sub-caption:** باقات ورصيد جلسات وبوابات محلية
- **Screen:** قائمة المنتجات + تنبيه «تم الدفع ← تم تعيين البرنامج»
- **Visual:** نافذة Apple Pay بحالة النجاح

## Frame 7 — حجوزات بلا أعمال إدارية

- **Headline:** حجوزات بلا أعمال إدارية
- **Sub-caption:** قوائم انتظار ورصيد وحضور جماعي
- **Screen:** تقويم أسبوعي بالكلاسات والسعة وزر «تسجيل الحضور» الجماعي
- **Visual:** يبقى الأسبوع محددًا بعد التعديل

## Frame 8 — بالعربية والفرنسية والإنجليزية والإسبانية

- **Headline:** بالعربية والفرنسية والإنجليزية والإسبانية
- **Sub-caption:** متدربوك بلغتهم
- **Screen:** لوحة التحكم بالعربية بجانب الفرنسية
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
