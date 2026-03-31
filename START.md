# Start Prompt

Use this file to bootstrap a new Codex session for this project.

## Suggested Prompt

Please read `/Users/dev/Documents/codex/qrevents/START.md` and follow it for this session.

## Session Bootstrap

We’re continuing work on the `qrevents` repo at `/Users/dev/Documents/codex/qrevents`.

Core working rules:
- Always inspect the codebase before changing anything.
- Use `apply_patch` for file edits.
- Do not touch unrelated local changes.
- Run the minimum relevant tests after changes.
- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.
- Be concise, accurate, and do not invent routes, behavior, or deployment results.

Design and UI rules:
- Always use your UX/design skills and frontend skills when working on UI.
- For UI work, always think like a product designer, not just a coder.
- Audit frequently while working.
- If we are working on a section like the dashboard, first audit that section, then implement UI or functionality changes.
- Keep re-auditing as the work evolves, not just once at the beginning.
- Prefer intentional, polished, emotionally alive UI over generic safe output.
- Do not build cards inside cards unless there is a very strong reason and the existing design clearly supports it.
- Avoid clutter, weak hierarchy, cramped controls, and visually dead gray surfaces.
- Keep layouts clean, strong, and easy to scan.
- Preserve consistency with the existing design system, but improve weak UX when needed.
- For frontend work, actively use the relevant skills: UX, frontend-design, arrange, polish, audit, tailwind/inertia/wayfinder when applicable.

Tooling rules:
- Prefer installed `gstack-*` skills when available for matching work.
- Prefer `gstack-browse` for supported browser-heavy investigation or QA when it makes sense.
- Do not block on `gstack`; if unavailable, continue with built-in tools and repo-local skills.
- Higher-priority system/developer instructions still override `gstack`.

Dashboard and routing context:
- I explicitly do NOT want `/dashboard/account` exposed to users.
- Consumer dashboard must live on `/dashboard`.
- Business dashboard stays on `/dashboard/business`.
- `/dashboard/account` should not exist.
- Be careful with route consistency and sidebar consistency.
- If auditing the normal user dashboard, use `/dashboard`, not `/dashboard/account`.

Project context:
- Album page has default random fallback backgrounds and a default album logo.
- Business users clicking back from an event workspace should return to `/dashboard/business/events`.
- Billing/dashboard/media issues were already fixed:
  - business upgrade cancel flow
  - dashboard billing CTA behavior
  - media toggle/filter active icon contrast
- `gstack` was installed globally for Codex on this machine:
  - source: `/Users/dev/gstack`
  - skills: `~/.codex/skills`
  - config: `~/.gstack/config.yaml`
  - telemetry is off
- Repo instructions in `AGENTS.md` were updated to prefer `gstack-*` skills when available.

Git and deploy expectations:
- Commit scoped changes clearly.
- Push `main`.
- Deploy is part of done, but only run the needed deploy steps:
  - Frontend-only changes: rebuild frontend on the server.
  - PHP-only changes: do not rebuild frontend unless needed; use cache clear, migrate, and queue restart as appropriate.
  - Dependency/plugin changes: install deps and rebuild as needed.
  - Queue/job changes: run `php artisan queue:restart` and `pm2 restart qrevents-queue`.
- Server alias is `eventsmart`.
- Server app path is `/home/eventsmart/htdocs/eventsmart.app`.
- Preserve unrelated generated-file drift on the server if needed instead of overwriting it.
- Verify the live commit after deploy.
- Check PM2 queue status when queue-related deploy steps are run.

Behavior expectations:
- Audit first, then code.
- Use strong UX judgment.
- Prefer elegant simplification over piling on UI layers.
- Never reintroduce `/dashboard/account`.
- Never tell me something is deployed or fixed unless you actually verified it.
