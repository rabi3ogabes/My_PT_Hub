# Runbook — Push notification failures

**Signals:** users report missing reminders/messages; FCM error rate > 5 % in logs/Sentry
(`FcmChannel`); sudden drop in push-open analytics; APNs certificate/key expiry alert.

## 1. Scope
- One user, one platform, everyone? Check Firebase Console → Cloud Messaging reports (sends, deliveries) for both apps.
- Firebase status page (FCM, APNs dependency); Apple developer system status.
```sql
-- Devices updated recently per app/platform (token churn is normal; zero is suspicious)
SELECT app, platform, count(*) FROM devices WHERE updated_at > now() - interval '24 hours' GROUP BY app, platform;
```

## 2. Common causes
| Cause | How to confirm | Fix |
|---|---|---|
| Service account credentials invalid/rotated | `FcmChannel` errors `UNAUTHENTICATED` | Rotate `FIREBASE_CREDENTIALS` secret, redeploy, verify with a test send |
| APNs auth key revoked / expired in Firebase | iOS only fails; FCM error `THIRD_PARTY_AUTH_ERROR` | Upload a new APNs key (.p8) in Firebase project settings for each iOS app (incl. white-label apps) |
| Stale tokens | `UNREGISTERED` / `INVALID_ARGUMENT` per token | Expected: channel deletes the device row; if it isn't deleting → bug, fix and backfill cleanup |
| Notifications queue backlog | Horizon `notifications` queue wait high | [queue-backlog.md](queue-backlog.md) |
| User preferences / quiet hours | `users.notification_preferences`, `quiet_hours` | Explain to user; quiet hours hold pushes until the window ends (digest) |
| App-side: permission denied / token not registered | `devices` has no row for user/app | Ask user to open the app (re-registers via `POST /me/devices`); check notification permission |
| Android battery optimisation / OEM kill | Only certain OEMs | Help article; high-priority FCM messages only for chat and timers |
| Wrong Firebase project for a flavor | Only a white-label app fails | Verify `google-services.json` / `GoogleService-Info.plist` for that flavor (`WL_FIREBASE_CONFIGS_JSON`) |

## 3. Test send
```bash
php artisan tinker
>>> $u = \App\Domain\Identity\Models\User::where('email', 'oncall.test@aurum.coach')->first();
>>> $u->notify(new \App\Notifications\GenericNotification('system', 'Test push', 'Runbook test '.now()));
```
Check delivery on a physical iOS and Android device signed into the on-call test account.

## 4. Mitigation while push is down
- Email and in-app notifications still flow (channels are independent); coaches with WhatsApp connected (Phase 7) keep session reminders on WhatsApp for opted-in clients.
- In-app banner for coaches: "Push delays — messages are still delivered in the app."

## 5. Close
- Delivery rate back to baseline for 1 h; backlog drained.
- If credentials expired: add/verify expiry reminder 30 days ahead in the ops calendar.
