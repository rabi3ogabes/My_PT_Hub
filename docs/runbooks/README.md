# Runbooks

| Runbook | Use when |
|---|---|
| [outage.md](outage.md) | Site/API down or degraded, elevated 5xx, deploy went wrong, rollback |
| [payment-webhook-failures.md](payment-webhook-failures.md) | Payments succeed at the gateway but orders stay pending / content not delivered |
| [push-failures.md](push-failures.md) | Push notifications not arriving (FCM/APNs) |
| [store-rejection.md](store-rejection.md) | App Store / Google Play rejection or removal |
| [queue-backlog.md](queue-backlog.md) | Horizon queues growing, jobs delayed or failing |
| [restore-drill.md](restore-drill.md) | Quarterly backup restore drill, or a real restore |

Severity: **SEV1** full outage / data loss / payments broken for all · **SEV2** major feature down or
one region/gateway down · **SEV3** degraded, workaround exists. SEV1/2 page the on-call primary;
the incident commander (IC) owns comms until resolved. Every SEV1/2 gets a blameless
post-mortem within 5 business days.
