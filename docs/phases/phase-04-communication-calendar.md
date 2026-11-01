# Phase 4 — Messaging, Notifications, Community, Calendar & Bookings

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Build on Phases 1–3.

## Goal
Real-time communication that feels like a premium messenger, a community that keeps clients engaged, and a scheduling system that handles 1:1 sessions, classes, credits and two-way calendar sync without the admin pain competitors have.

## Competitor complaints this phase must solve
- Message history buffers; notifications unreliable.
- Hard to edit past sessions and deduct credits; calendar jumps back to current week.
- Only one-way calendar sync; no WhatsApp reminders.

## Backend

### 1. Messaging (Reverb WebSockets)
- `conversations` (direct, group, broadcast, check-in thread), `conversation_participants` (role, muted, last_read_at), `messages` (text, voice note, image, video, file, workout/meal/habit share card, system), `message_attachments`, reactions, replies (threading), edits within 15 min, delete for everyone.
- Delivery & read receipts, typing indicators (presence channel), unread counts.
- Cursor-paginated history with server-side caching; messages encrypted at rest.
- Voice notes: upload → transcode to AAC → waveform JSON; optional transcript (Phase 6).
- Video replies: coach records up to 3 min; same HLS pipeline as exercises.
- Scheduled messages and broadcasts to segments (tags, groups, program, at-risk).
- **Automations** (`automations` table): trigger (client joined, program started/finished, workout missed N days, check-in overdue, PR hit, birthday, package expiring, payment failed) → conditions → actions (send message template, assign habit/program, notify coach, add tag). Visual builder in web.
- Quick replies / saved message templates with variables (`{first_name}`, `{program}`) in 4 languages.

### 2. Notifications
- Central `NotificationService`: channels push (FCM/APNs), email, in-app, WhatsApp (Phase 7 driver), SMS (optional driver).
- User preferences per category + quiet hours + digest mode.
- Rich push: images, action buttons (Log now, Reply), deep links, notification grouping per conversation.
- Delivery tracking and retry; device cleanup on invalid tokens.

### 3. Community
- `community_spaces` (per tenant, per group/program), `posts` (text, media, poll, workout share), `comments`, `reactions`, pinning, moderation (report, hide, mute member), coach-only announcement mode.
- `challenges` (steps, workouts completed, habit streak, volume, custom metric; start/end; teams optional) → `challenge_entries` with leaderboards; badges awarded on completion.
- Badges & achievements engine (first workout, 7/30/100-day streak, PR count, check-in streak).

### 4. Calendar & bookings
- `calendars` per coach and location; `availability_rules` (weekly hours, breaks, buffers, min notice, max advance, timezone), date overrides and holidays.
- `events`: 1:1 session, class (capacity, waitlist), event, live stream (Zoom link, Phase 7), block-out time; recurrence (RRULE) with exceptions.
- `bookings` with status (booked, attended, no-show, late-cancel, cancelled), policies (cancellation window, no-show fee or credit forfeit), check-in by QR.
- `credit_ledgers` (append-only): purchases (Phase 5 packages), deductions on attended/no-show, manual adjustments with reason — always auditable.
- **Retroactive editing:** coach can mark past sessions attended/no-show in bulk; calendar keeps the selected week after edits.
- Waitlist auto-promotion with notification and claim window.
- Reminders: 24h + 2h push/email/WhatsApp templates; ICS attachments.
- Public booking page per coach (feeds Phase 8 profile pages).

## Coach web
- Inbox (three-pane: conversations | thread | client context sidebar with compliance, program, last check-in, payments).
- Broadcast composer with segment preview; scheduled messages list; automation builder (trigger → condition → action canvas).
- Community moderation view; challenge creator with leaderboard.
- Calendar: day/week/month/resource (per coach/room) views, drag to reschedule, recurring series editing, attendance register for classes, bulk mark attendance, credit balance on hover, availability editor.

## Coach & client apps
- Chat: fast list with unread badges, thread with voice note recorder (hold to record, slide to cancel), image/video picker, share workout card, reactions, reply, search messages, offline compose queue.
- Community feed, post composer, challenges with live leaderboard, badges cabinet.
- Calendar: upcoming sessions, book a session/class from availability, join waitlist, cancel within policy, add to device calendar, QR check-in.
- Notification center in-app with filters.

## Tests
- Messaging: authorization on channels, read receipts, pagination, attachment security (no cross-tenant URL access).
- Automations engine: each trigger fires once (idempotency), conditions evaluated correctly.
- Booking: capacity, double-booking prevention (DB constraint + lock), timezone/DST across Doha/Paris/Casablanca, recurrence exceptions, credit ledger balance always equals sum of entries.
- Load test: 1,000 concurrent socket connections per tenant; message send p95 < 150ms.

## Acceptance checklist
- [ ] Chat history of 5,000 messages scrolls smoothly with no buffering.
- [ ] Voice note recorded on client app plays on coach web with waveform.
- [ ] Automation "missed 3 days → send message" fires once and is logged.
- [ ] Coach marks last week's 10 sessions attended in one action; credits deducted correctly; view stays on last week.
- [ ] Class waitlist auto-promotes when someone cancels.
