# Codex Handoff

## Current State

- The app is mid-build but no longer a blank starter.
- Core implemented areas already in progress:
  - event onboarding
  - owner event dashboard
  - media management
  - public album
  - collaborator invites
  - media export
  - storage audit
  - guest likes/comments/profile

## What Was Just Finished

- Production hardening batch completed:
  - public asset delete now requires the matching `guest_token`
  - public album endpoints now use route throttling
  - frontend lint blockers were fixed
- Main files updated in that batch:
  - `app/Http/Controllers/EventController.php`
  - `app/Providers/AppServiceProvider.php`
  - `routes/web.php`
  - `config/events.php`
  - `tests/Feature/EventAlbumUploadTest.php`
  - `resources/js/pages/public/Album.vue`
  - `resources/js/pages/events/Media.vue`
  - `resources/js/composables/useAppearance.ts`
  - `resources/js/pages/Businesses.vue`

## Verified Green

- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/EventAlbumUploadTest.php`
- `npm run lint:check`
- `npm run types:check`

## Important Notes

- The git worktree is already dirty with many in-progress files. Do not revert unrelated changes.
- `pint --dirty` also reformatted a few already-modified PHP files outside the last hardening batch.
- There is a temp file outside the repo shown in git status:
  - `../qr-video-test-XXXXXX.mp4`
- Memory MCP was configured for Codex and Cursor, but in the previous thread it was not exposed as a usable tool to the agent session yet.

## Highest Priority Next Work

### 1. Build a real client/account dashboard

- `/dashboard` currently only redirects.
- `resources/js/pages/Dashboard.vue` is still placeholder-only.
- Need a real authenticated hub for:
  - event switching
  - owner vs collaborator context
  - plan/billing state
  - recent activity
  - quick actions

### 2. Add browser smoke coverage

- There is no `tests/Browser` suite yet.
- Add at least one end-to-end happy path covering:
  - login
  - onboarding or event landing
  - guest album upload
  - owner media review
  - export download

### 3. Decide billing scope before launch

- The app has payment-lock logic.
- No real self-serve checkout/billing flow was found.
- Decide whether launch includes:
  - manual payment/admin-managed billing
  - or actual customer checkout/subscription

## Recommended Immediate Next Batch

1. Replace redirect-only `/dashboard` with a real page.
2. Keep existing event pages as the operational surfaces:
   - `resources/js/pages/events/Home.vue`
   - `resources/js/pages/events/Media.vue`
   - `resources/js/pages/events/Settings.vue`
3. Make dashboard show:
   - owned events
   - collaborator events
   - event status
   - storage/upload usage
   - export status
   - shortcuts into event home/media/settings
4. Add focused Pest coverage for the new dashboard data flow.
5. Then add browser smoke coverage.

## Helpful Existing Findings

- Backend feature areas looked solid and were already covered:
  - collaborator invites
  - event settings
  - album upload
  - media export
  - storage audit
- Before the hardening batch, the main production gaps were:
  - insecure public delete fallback by guest name only
  - missing throttling on public write endpoints
  - placeholder-only dashboard
  - no browser-level validation

## Suggested Prompt For New Agent

Work from `CODEX_HANDOFF.md` and continue the next production-readiness batch.
First, inspect the authenticated dashboard flow and replace the redirect-only `/dashboard`
with a real account dashboard page. Reuse existing event owner pages where possible.
Then add the minimum browser smoke coverage for guest upload and owner review/export flows.
Do not revert unrelated dirty worktree changes.
