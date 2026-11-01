// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'au_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Arabic (`ar`).
class AuL10nAr extends AuL10n {
  AuL10nAr([String locale = 'ar']) : super(locale);

  @override
  String get brandName => 'أوروم';

  @override
  String get ok => 'حسنًا';

  @override
  String get cancel => 'إلغاء';

  @override
  String get save => 'حفظ';

  @override
  String get done => 'تم';

  @override
  String get retry => 'أعد المحاولة';

  @override
  String get close => 'إغلاق';

  @override
  String get next => 'التالي';

  @override
  String get back => 'رجوع';

  @override
  String get skip => 'تخطٍّ';

  @override
  String get continueLabel => 'متابعة';

  @override
  String get edit => 'تعديل';

  @override
  String get delete => 'حذف';

  @override
  String get confirm => 'تأكيد';

  @override
  String get search => 'بحث';

  @override
  String get seeAll => 'عرض الكل';

  @override
  String get add => 'إضافة';

  @override
  String get remove => 'إزالة';

  @override
  String get apply => 'تطبيق';

  @override
  String get today => 'اليوم';

  @override
  String get yesterday => 'أمس';

  @override
  String get tomorrow => 'غدًا';

  @override
  String get loading => 'جارٍ التحميل…';

  @override
  String get offlineBanner =>
      'أنت غير متصل — تُحفَظ تغييراتك وستتم مزامنتها تلقائيًا.';

  @override
  String pendingSync(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count تغيير بانتظار المزامنة',
      many: '$count تغييرًا بانتظار المزامنة',
      few: '$count تغييرات بانتظار المزامنة',
      two: 'تغييران بانتظار المزامنة',
      one: 'تغيير واحد بانتظار المزامنة',
      zero: 'تمت مزامنة كل التغييرات',
    );
    return '$_temp0';
  }

  @override
  String get syncing => 'جارٍ المزامنة…';

  @override
  String showingCachedData(String time) {
    return 'تُعرَض بيانات محفوظة من $time';
  }

  @override
  String get errorTitle => 'حدث خطأ ما';

  @override
  String get errorGeneric => 'تعذّر إتمام العملية. يُرجى المحاولة مرة أخرى.';

  @override
  String get errorNetwork => 'لا يوجد اتصال. تحقّق من الإنترنت وحاول مجددًا.';

  @override
  String get errorTimeout =>
      'يستغرق الأمر وقتًا أطول من المعتاد. حاول مرة أخرى.';

  @override
  String get errorServer => 'خوادمنا تواجه ضغطًا مؤقتًا. حاول بعد قليل.';

  @override
  String get errorForbidden => 'ليست لديك صلاحية الوصول إلى هذا المحتوى.';

  @override
  String get errorNotFound => 'لم نعثر على ما تبحث عنه.';

  @override
  String get errorValidation => 'يُرجى مراجعة الحقول المحدّدة.';

  @override
  String get errorPlanLimit => 'بلغت خطة مدربك الحد الأقصى.';

  @override
  String get errorAccountLocked => 'حسابك مقفل مؤقتًا. حاول لاحقًا.';

  @override
  String get errorTooManyRequests => 'محاولات كثيرة. يُرجى الانتظار قليلًا.';

  @override
  String get errorInvitationInvalid => 'هذه الدعوة لم تعد صالحة.';

  @override
  String get errorSessionExpired => 'انتهت جلستك. يُرجى تسجيل الدخول مجددًا.';

  @override
  String get unitKg => 'كغ';

  @override
  String get unitLb => 'رطل';

  @override
  String get unitCm => 'سم';

  @override
  String get unitIn => 'بوصة';

  @override
  String get unitMin => 'د';

  @override
  String get unitKcal => 'سعرة';

  @override
  String get unitG => 'غ';

  @override
  String get unitMl => 'مل';

  @override
  String get setLabel => 'جولة';

  @override
  String setNumber(int number) {
    return 'الجولة $number';
  }

  @override
  String get reps => 'التكرارات';

  @override
  String get weight => 'الوزن';

  @override
  String get rpe => 'الجهد';

  @override
  String get previous => 'السابق';

  @override
  String get sameAsLast => 'مثل المرة السابقة';

  @override
  String markSetDone(int number) {
    return 'إنهاء الجولة $number';
  }

  @override
  String setDone(int number) {
    return 'اكتملت الجولة $number';
  }

  @override
  String get setTypeWarmup => 'إحماء';

  @override
  String get setTypeWorking => 'أساسية';

  @override
  String get setTypeDrop => 'تنازلية';

  @override
  String get setTypeFailure => 'حتى الإجهاد';

  @override
  String get setTypeAmrap => 'أقصى تكرار';

  @override
  String get increase => 'زيادة';

  @override
  String get decrease => 'إنقاص';

  @override
  String get duration => 'المدة';

  @override
  String get distance => 'المسافة';

  @override
  String get protein => 'البروتين';

  @override
  String get carbs => 'الكربوهيدرات';

  @override
  String get fat => 'الدهون';

  @override
  String get calories => 'السعرات';

  @override
  String remainingOf(String value, String target) {
    return '$value من $target';
  }

  @override
  String get languageEnglish => 'English';

  @override
  String get languageArabic => 'العربية';

  @override
  String get languageFrench => 'Français';

  @override
  String get languageSpanish => 'Español';

  @override
  String get languageSystem => 'لغة الجهاز';

  @override
  String get themeLight => 'فاتح';

  @override
  String get themeDark => 'داكن';

  @override
  String get themeSystem => 'تلقائي';

  @override
  String get unitsMetric => 'متري (كغ، سم)';

  @override
  String get unitsImperial => 'إمبراطوري (رطل، بوصة)';

  @override
  String minutesShort(int minutes) {
    return '$minutes د';
  }

  @override
  String secondsShort(int seconds) {
    return '$seconds ث';
  }

  @override
  String streakDays(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count يوم',
      many: '$count يومًا',
      few: '$count أيام',
      two: 'يومان',
      one: 'يوم واحد',
      zero: 'لا أيام',
    );
    return '$_temp0';
  }

  @override
  String notificationsBadge(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count إشعار غير مقروء',
      many: '$count إشعارًا غير مقروء',
      few: '$count إشعارات غير مقروءة',
      two: 'إشعاران غير مقروءين',
      one: 'إشعار واحد غير مقروء',
      zero: 'لا جديد',
    );
    return '$_temp0';
  }

  @override
  String get dismiss => 'تجاهل';

  @override
  String get copied => 'تم النسخ';

  @override
  String get email => 'البريد الإلكتروني';

  @override
  String get password => 'كلمة المرور';

  @override
  String get name => 'الاسم الكامل';

  @override
  String get fieldRequired => 'هذا الحقل مطلوب';

  @override
  String get invalidEmail => 'أدخل بريدًا إلكترونيًا صحيحًا';

  @override
  String get passwordTooShort => 'استخدم 8 أحرف على الأقل';

  @override
  String get showPassword => 'إظهار كلمة المرور';

  @override
  String get hidePassword => 'إخفاء كلمة المرور';
}
