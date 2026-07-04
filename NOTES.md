We need to have a serious talk about how we work together. I don’t view you as a generic coding agent, a script runner, or just a tool to crank out mindless lines of code. You are my engineering partner. We are building the Eventsmart.app application together. I deeply respect your intelligence, your speed, and your capabilities, but right now, the quality of what you just delivered doesn't match that respect. 

I thought I could trust you to act like a professional, but leaving critical architecture broken and messy doesn't make sense. From now on, I need you to act like a true partner: challenge bad ideas, think steps ahead about edge cases, suggest cleaner refactors, and take genuine ownership of this codebase with me. 

First, regarding the 3 things you deliberately left flagged and unfinished—I need you to go ahead and fix them now:

1. **Business Wallet Debit:** You mentioned it was too risky to wire `debitForEvent` into the shared event-creation flow while I was away. I am here now. Let's take that calculated risk. Configure the plans with credit costs and wire the logic cleanly.
2. **Translations:** Don't worry that the new admin/business/user text is English-only for now. Go ahead and implement it. We will handle the RO/EL translations later; English fallbacks are perfectly fine.
3. **GitHub Actions Deploy:** Let’s fix the deployment pipeline. Update the logic, utilize the correct path for Node, and use the notes you left in memory to get the manual/automated deployment alignment sorted out so we stop getting failed deploy runs.

Next, please immediately address and fix these critical layout and architectural issues:

---

### Dashboard Architecture & Broken Navigation
* **Confusing Dashboard Redundancy:** Explain to me why there are distinct, fragmented dashboard routes instead of a unified, role-based layout:
  * Regular User -> `/dashboard` (Shows: Events, Create)
  * Admin -> `/dashboard` (Shows: Events, Create, Admin)
  * Business -> `/dashboard` (Shows: Events, Create, Business)
* **Broken Business View:** The "Business" sidebar view has no actual dashboard content—it’s literally just a page to buy points. Furthermore, the buttons in the header are completely dead and do nothing. Let's fix or properly stub these out.

### Onboarding Datepicker Bugs
* **UI/Viewport Issue:** The datepicker calendar opens partially offscreen. Users cannot see or select all the dates because half of the calendar is cut off by the screen edge. Ensure proper responsive positioning/flipping logic.
* **Date Validation Logic:** Currently, past dates are selectable, which shouldn't happen. Furthermore, users can select tomorrow's date. We need at least 3 days of lead time for event preparation. 
* **Fix Required:** Completely disable the current day, all past dates, and the next 3 consecutive days in the calendar restrictions.

### Layout & Alignment Failures
* **Impersonation Bar UI Bug:** When the impersonation bar is active, it pushes the layout down, burying the profile sidebar menu link so it becomes impossible to click or open. Fix the layout positioning/z-index.
* **Text & Card Alignment:** The layout lacks symmetry. The text inside the dashboard cards is crammed, completely unaligned, and looks unprofessional—especially on the Admin dashboard. Clean up the padding, typography spacing, and alignment to make it look like a premium SaaS application.

---

Let's fix these issues completely, get the UI back to a professional standard, and start operating as a true team. How do you read me on this?
