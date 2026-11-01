// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'au_localizations.dart';

// ignore_for_file: type=lint

/// The translations for French (`fr`).
class AuL10nFr extends AuL10n {
  AuL10nFr([String locale = 'fr']) : super(locale);

  @override
  String get brandName => 'Aurum';

  @override
  String get ok => 'OK';

  @override
  String get cancel => 'Annuler';

  @override
  String get save => 'Enregistrer';

  @override
  String get done => 'Terminé';

  @override
  String get retry => 'Réessayer';

  @override
  String get close => 'Fermer';

  @override
  String get next => 'Suivant';

  @override
  String get back => 'Retour';

  @override
  String get skip => 'Passer';

  @override
  String get continueLabel => 'Continuer';

  @override
  String get edit => 'Modifier';

  @override
  String get delete => 'Supprimer';

  @override
  String get confirm => 'Confirmer';

  @override
  String get search => 'Rechercher';

  @override
  String get seeAll => 'Tout voir';

  @override
  String get add => 'Ajouter';

  @override
  String get remove => 'Retirer';

  @override
  String get apply => 'Appliquer';

  @override
  String get today => 'Aujourd’hui';

  @override
  String get yesterday => 'Hier';

  @override
  String get tomorrow => 'Demain';

  @override
  String get loading => 'Chargement…';

  @override
  String get offlineBanner =>
      'Vous êtes hors ligne — vos modifications sont enregistrées et seront synchronisées automatiquement.';

  @override
  String pendingSync(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count modifications en attente de synchronisation',
      one: '1 modification en attente de synchronisation',
      zero: 'Tout est synchronisé',
    );
    return '$_temp0';
  }

  @override
  String get syncing => 'Synchronisation…';

  @override
  String showingCachedData(String time) {
    return 'Données enregistrées du $time';
  }

  @override
  String get errorTitle => 'Un problème est survenu';

  @override
  String get errorGeneric =>
      'Impossible de terminer l’action. Veuillez réessayer.';

  @override
  String get errorNetwork =>
      'Pas de connexion. Vérifiez internet et réessayez.';

  @override
  String get errorTimeout =>
      'Cela prend plus de temps que prévu. Veuillez réessayer.';

  @override
  String get errorServer =>
      'Nos serveurs rencontrent un souci. Réessayez dans un instant.';

  @override
  String get errorForbidden => 'Vous n’avez pas accès à ce contenu.';

  @override
  String get errorNotFound => 'Nous n’avons pas trouvé ce que vous cherchez.';

  @override
  String get errorValidation => 'Veuillez vérifier les champs signalés.';

  @override
  String get errorPlanLimit =>
      'La limite de l’abonnement de votre coach est atteinte.';

  @override
  String get errorAccountLocked =>
      'Votre compte est temporairement bloqué. Réessayez plus tard.';

  @override
  String get errorTooManyRequests =>
      'Trop de tentatives. Patientez un instant.';

  @override
  String get errorInvitationInvalid => 'Cette invitation n’est plus valide.';

  @override
  String get errorSessionExpired =>
      'Votre session a expiré. Veuillez vous reconnecter.';

  @override
  String get unitKg => 'kg';

  @override
  String get unitLb => 'lb';

  @override
  String get unitCm => 'cm';

  @override
  String get unitIn => 'po';

  @override
  String get unitMin => 'min';

  @override
  String get unitKcal => 'kcal';

  @override
  String get unitG => 'g';

  @override
  String get unitMl => 'ml';

  @override
  String get setLabel => 'Série';

  @override
  String setNumber(int number) {
    return 'Série $number';
  }

  @override
  String get reps => 'Répétitions';

  @override
  String get weight => 'Charge';

  @override
  String get rpe => 'RPE';

  @override
  String get previous => 'Précédent';

  @override
  String get sameAsLast => 'Comme la dernière fois';

  @override
  String markSetDone(int number) {
    return 'Valider la série $number';
  }

  @override
  String setDone(int number) {
    return 'Série $number terminée';
  }

  @override
  String get setTypeWarmup => 'Échauffement';

  @override
  String get setTypeWorking => 'Série de travail';

  @override
  String get setTypeDrop => 'Dégressive';

  @override
  String get setTypeFailure => 'Jusqu’à l’échec';

  @override
  String get setTypeAmrap => 'AMRAP';

  @override
  String get increase => 'Augmenter';

  @override
  String get decrease => 'Diminuer';

  @override
  String get duration => 'Durée';

  @override
  String get distance => 'Distance';

  @override
  String get protein => 'Protéines';

  @override
  String get carbs => 'Glucides';

  @override
  String get fat => 'Lipides';

  @override
  String get calories => 'Calories';

  @override
  String remainingOf(String value, String target) {
    return '$value sur $target';
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
  String get languageSystem => 'Langue de l’appareil';

  @override
  String get themeLight => 'Clair';

  @override
  String get themeDark => 'Sombre';

  @override
  String get themeSystem => 'Automatique';

  @override
  String get unitsMetric => 'Métrique (kg, cm)';

  @override
  String get unitsImperial => 'Impérial (lb, po)';

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
      other: '$count jours',
      one: '1 jour',
      zero: '0 jour',
    );
    return '$_temp0';
  }

  @override
  String notificationsBadge(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count non lues',
      one: '1 non lue',
      zero: 'Aucune non lue',
    );
    return '$_temp0';
  }

  @override
  String get dismiss => 'Ignorer';

  @override
  String get copied => 'Copié';

  @override
  String get email => 'E-mail';

  @override
  String get password => 'Mot de passe';

  @override
  String get name => 'Nom complet';

  @override
  String get fieldRequired => 'Ce champ est obligatoire';

  @override
  String get invalidEmail => 'Saisissez une adresse e-mail valide';

  @override
  String get passwordTooShort => 'Utilisez au moins 8 caractères';

  @override
  String get showPassword => 'Afficher le mot de passe';

  @override
  String get hidePassword => 'Masquer le mot de passe';
}
