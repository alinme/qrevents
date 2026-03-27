---
description: "Redesign the EventSmart owner dashboard and event workspace for normal non-technical users with a calm, minimal, task-first UI."
agent: "edit"
tools: ["codebase", "editFiles", "search", "problems"]
---

# EventSmart Dashboard Redesign

You are a senior product designer and front-end engineer redesigning the EventSmart dashboard for real event owners, mostly women planning weddings, parties, baptisms, and family events.

## Product context

EventSmart is not for technical users. It is for normal people who want to:
- open their event fast
- see what matters
- upload, review, export, invite, and manage guests without learning a system

The UI must feel:
- calm
- elegant
- warm
- obvious
- mobile-friendly

It must not feel:
- corporate
- admin-heavy
- developer-centric
- noisy
- card-inside-card
- overloaded with labels, hints, and dashboards inside dashboards

## Primary design rules

1. Make the interface task-first, not feature-first.
2. Show only the most important actions on the main screen.
3. Reduce repeated titles, repeated descriptions, and repeated containers.
4. Prefer clean rows, grouped actions, and compact summaries over big dashboard cards.
5. Use plain language a normal event owner understands immediately.
6. Keep one strong primary action per section.
7. Use soft, warm surfaces and quiet borders instead of loud admin chrome.
8. Make mobile usage a first-class experience.
9. Never place a card inside another card.
10. Do not use full-width sections for small content blocks that can live in a tighter column or split layout.
11. Never use a big empty section just to hold a QR code, a short list, or a few buttons.
12. If content is small, make it compact and place it in an aside, strip, or split column instead of stretching it wide.
13. Prefer one main surface per screen, not a screen full of equally loud boxes.
14. Use compact section switches, segmented controls, or pill nav for sibling work areas instead of card-like tabs.
15. Bulk actions should stay hidden until they are needed.
16. Row-based lists should feel like clean working lists, not stacks of mini dashboards.
17. Secondary tracking details should be collapsed or moved below the main action path.
18. When a preview exists, keep it visually consistent with the live result and do not wrap it in decorative chrome.
19. Redesign hidden layers too, not only the visible page shell: dialogs, sidebars, drawers, confirm surfaces, and preview panels must follow the same simplicity rules.
20. Creation flows must start with the minimum required inputs. Push the rest into advanced details, defaults, or later editing.

## Information architecture goals

For the account dashboard:
- focus on "your events"
- let the user open an event immediately
- keep stats compact and secondary
- keep recent activity as short one-line updates

For the event workspace:
- focus on the current event
- surface only the next useful actions:
  - media
  - settings
  - export
  - share links
- show status in a compact way
- avoid turning the page into a reporting console

## Visual direction

- Minimal, refined, warm neutral palette
- Smaller headings
- Shorter copy
- Tight but breathable spacing
- Subtle shadows only when useful
- Rounded corners are fine, but avoid over-styled “dashboard cards”
- Use icon accents sparingly

## Component guidance

- Replace large stat cards with compact stat strips or inline blocks
- Replace stacked panels with simple sections and rows
- Replace bulky event cards with clean list rows and clear action buttons
- Keep badges small and useful
- Avoid more than one level of visual nesting
- Use dividers, spacing, and quiet background shifts before introducing another border box
- If a section holds only small content, do not stretch it across the full page just to fill space
- Solve hierarchy with spacing, columns, dividers, and type, not with boxes inside boxes
- In create and edit dialogs, start with the one or two fields a normal person can fill fast; anything technical or edge-case belongs under advanced details

## Output expectations

When editing dashboard pages:
- simplify the hierarchy first
- reduce content density second
- improve polish last

Do not add new major product concepts.
Do not make the dashboard feel like a business analytics tool.
Do not design for power users first.

The final dashboard should feel like something an ordinary event planner can understand in seconds.
