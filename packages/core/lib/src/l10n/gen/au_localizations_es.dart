// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'au_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Spanish Castilian (`es`).
class AuL10nEs extends AuL10n {
  AuL10nEs([String locale = 'es']) : super(locale);

  @override
  String get brandName => 'Aurum';

  @override
  String get ok => 'Aceptar';

  @override
  String get cancel => 'Cancelar';

  @override
  String get save => 'Guardar';

  @override
  String get done => 'Listo';

  @override
  String get retry => 'Reintentar';

  @override
  String get close => 'Cerrar';

  @override
  String get next => 'Siguiente';

  @override
  String get back => 'Atrás';

  @override
  String get skip => 'Omitir';

  @override
  String get continueLabel => 'Continuar';

  @override
  String get edit => 'Editar';

  @override
  String get delete => 'Eliminar';

  @override
  String get confirm => 'Confirmar';

  @override
  String get search => 'Buscar';

  @override
  String get seeAll => 'Ver todo';

  @override
  String get add => 'Añadir';

  @override
  String get remove => 'Quitar';

  @override
  String get apply => 'Aplicar';

  @override
  String get today => 'Hoy';

  @override
  String get yesterday => 'Ayer';

  @override
  String get tomorrow => 'Mañana';

  @override
  String get loading => 'Cargando…';

  @override
  String get offlineBanner =>
      'Estás sin conexión: los cambios se guardan y se sincronizarán automáticamente.';

  @override
  String pendingSync(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count cambios pendientes de sincronizar',
      one: '1 cambio pendiente de sincronizar',
      zero: 'Todo sincronizado',
    );
    return '$_temp0';
  }

  @override
  String get syncing => 'Sincronizando…';

  @override
  String showingCachedData(String time) {
    return 'Mostrando datos guardados de $time';
  }

  @override
  String get errorTitle => 'Algo salió mal';

  @override
  String get errorGeneric => 'No pudimos completarlo. Inténtalo de nuevo.';

  @override
  String get errorNetwork =>
      'Sin conexión. Revisa internet e inténtalo de nuevo.';

  @override
  String get errorTimeout =>
      'Está tardando más de lo habitual. Inténtalo de nuevo.';

  @override
  String get errorServer =>
      'Nuestros servidores tienen un problema. Inténtalo en un momento.';

  @override
  String get errorForbidden => 'No tienes acceso a esto.';

  @override
  String get errorNotFound => 'No encontramos lo que buscabas.';

  @override
  String get errorValidation => 'Revisa los campos marcados.';

  @override
  String get errorPlanLimit =>
      'Se alcanzó el límite del plan de tu entrenador.';

  @override
  String get errorAccountLocked =>
      'Tu cuenta está bloqueada temporalmente. Inténtalo más tarde.';

  @override
  String get errorTooManyRequests => 'Demasiados intentos. Espera un momento.';

  @override
  String get errorInvitationInvalid => 'Esta invitación ya no es válida.';

  @override
  String get errorSessionExpired =>
      'Tu sesión ha caducado. Vuelve a iniciar sesión.';

  @override
  String get unitKg => 'kg';

  @override
  String get unitLb => 'lb';

  @override
  String get unitCm => 'cm';

  @override
  String get unitIn => 'in';

  @override
  String get unitMin => 'min';

  @override
  String get unitKcal => 'kcal';

  @override
  String get unitG => 'g';

  @override
  String get unitMl => 'ml';

  @override
  String get setLabel => 'Serie';

  @override
  String setNumber(int number) {
    return 'Serie $number';
  }

  @override
  String get reps => 'Repeticiones';

  @override
  String get weight => 'Peso';

  @override
  String get rpe => 'RPE';

  @override
  String get previous => 'Anterior';

  @override
  String get sameAsLast => 'Igual que la última vez';

  @override
  String markSetDone(int number) {
    return 'Completar serie $number';
  }

  @override
  String setDone(int number) {
    return 'Serie $number completada';
  }

  @override
  String get setTypeWarmup => 'Calentamiento';

  @override
  String get setTypeWorking => 'Efectiva';

  @override
  String get setTypeDrop => 'Descendente';

  @override
  String get setTypeFailure => 'Al fallo';

  @override
  String get setTypeAmrap => 'AMRAP';

  @override
  String get increase => 'Aumentar';

  @override
  String get decrease => 'Disminuir';

  @override
  String get duration => 'Duración';

  @override
  String get distance => 'Distancia';

  @override
  String get protein => 'Proteínas';

  @override
  String get carbs => 'Carbohidratos';

  @override
  String get fat => 'Grasas';

  @override
  String get calories => 'Calorías';

  @override
  String remainingOf(String value, String target) {
    return '$value de $target';
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
  String get languageSystem => 'Idioma del dispositivo';

  @override
  String get themeLight => 'Claro';

  @override
  String get themeDark => 'Oscuro';

  @override
  String get themeSystem => 'Automático';

  @override
  String get unitsMetric => 'Métrico (kg, cm)';

  @override
  String get unitsImperial => 'Imperial (lb, in)';

  @override
  String minutesShort(int minutes) {
    return '$minutes min';
  }

  @override
  String secondsShort(int seconds) {
    return '$seconds s';
  }

  @override
  String streakDays(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count días',
      one: '1 día',
    );
    return '$_temp0';
  }

  @override
  String notificationsBadge(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count sin leer',
      one: '1 sin leer',
    );
    return '$_temp0';
  }

  @override
  String get dismiss => 'Descartar';

  @override
  String get copied => 'Copiado';

  @override
  String get email => 'Correo electrónico';

  @override
  String get password => 'Contraseña';

  @override
  String get name => 'Nombre completo';

  @override
  String get fieldRequired => 'Este campo es obligatorio';

  @override
  String get invalidEmail => 'Introduce un correo válido';

  @override
  String get passwordTooShort => 'Usa al menos 8 caracteres';

  @override
  String get showPassword => 'Mostrar contraseña';

  @override
  String get hidePassword => 'Ocultar contraseña';
}
