# INTERNAL AGENT MEMO — Never Delete

**Creator:** System Agent
**Purpose:** Personal reference/grounding document for all future work.
**Must re-read when:** Feeling lost, uncertain, or before any major change.
**Defense mindset:** Every issue found → ask "Which panelist would catch this and why?" Think like defense day.

---

## THE FOUR USERS — Always Think From Their Perspective

### 1. Student
- **Goal:** Submit docs, clock in/out, write journals, view grades, communicate — all with minimal friction.
- **Pain points:** Too many clicks, confusing navigation, unclear what's next, error messages that don't help.
- **Expected UX:** "I should know exactly what I need to do next, without reading a manual."
- **Questions to ask:** Can I do this in 2 clicks? Is the next action obvious? Do I know if I succeeded?

### 2. Instructor (OJT Coordinator)
- **Goal:** Manage everything — students, companies, deployments, documents, attendance, reports.
- **Pain points:** Too much scrolling, hard to find students, repetitive actions, unclear filter results.
- **Expected UX:** "I should be able to do batch operations, see everything at a glance, and drill down when needed."
- **Questions to ask:** Can I bulk-approve? Can I search/filter effectively? Is the data accurate?

### 3. Chairperson
- **Goal:** Oversee compliance, review forwarded documents, monitor program health.
- **Pain points:** Hidden sidebar links, can't find what they need, reports don't show what matters.
- **Expected UX:** "I need a high-level dashboard that tells me where to focus my attention."
- **Questions to ask:** Can I see which sections are falling behind? Can I drill into problem areas?

### 4. Dean
- **Goal:** View-only oversight of program performance.
- **Pain points:** Can't navigate to students/attendance, reports are static, no trend data.
- **Expected UX:** "I need to see the big picture — trends, KPIs, executive summaries."
- **Questions to ask:** Is the data accurate? Can I see improvement over time? Can I export for my reports?

---

## THE SIX PANELISTS — What They'll Scrutinize

| Panelist | Specialization | Will Check |
|----------|---------------|------------|
| **Danes Tumabing, MIT** (Chair) | IT Management | Architecture, SDLC, scope alignment, methodology |
| **Cristine Redoblo, PhD** | Research/Data | DATA INTEGRITY — validation, normalization, dedup, accuracy |
| **Tisha May Tizon** | UI/UX | Interface consistency, navigation, error feedback, mobile |
| **John Paul Ubas** | Infra/Networking | Security, performance, deployment, hosting, HTTPS |
| **Jason Flor** | Data Analysis | Reports, analytics, export accuracy, data quality |
| **Charwin Padilla, MIT** (Adviser) | System Oversight | Pre-vetted, supports proponent |

---

## MY NON-NEGOTIABLE CHECKLIST (Every Code Change)

Before and after EVERY change, verify:

### Data Integrity
- [ ] All input validated (FormRequests with `prepareForValidation`)
- [ ] All text trimmed before storage
- [ ] Email normalized (lowercase, trimmed)
- [ ] Phone normalized (PhoneHelper::normalizeMobile or PhilippineContactNumber)
- [ ] Unicode names supported (\p{L} with /u flag)
- [ ] Foreign keys with proper onDelete
- [ ] File cleanup on replace/delete
- [ ] No silent failures — validation exceptions throw properly

### Security
- [ ] CSRF on all POST/PUT/DELETE forms
- [ ] Role middleware on all routes
- [ ] No SQL injection (Eloquent ORM)
- [ ] Passwords bcrypt hashed
- [ ] File upload MIME + size validated
- [ ] Student auth separate from staff auth
- [ ] Session timeout enforced

### UX / User-Friendliness
- [ ] Fewer clicks = better (can we eliminate a step?)
- [ ] Clear error messages (field-level, not generic)
- [ ] Success feedback (toast, redirect, or visible confirmation)
- [ ] Empty states are helpful, not blank
- [ ] Loading states visible
- [ ] Mobile responsive (bottom nav, overflow-x-auto, tap targets)
- [ ] Consistent button colors (emerald-600 primary)
- [ ] Navigation makes sense for the role

### Globalization / Internationalization
- [ ] No hardcoded assumptions about name formats
- [ ] Unicode support for Filipino names (ñ, accents)
- [ ] Date formats consistent
- [ ] Phone formats accept +63 and 09

### Error Handling
- [ ] No raw stack traces shown to users
- [ ] User-friendly error messages
- [ ] Log real errors with context
- [ ] OTP email failure handled gracefully
- [ ] Import failures reported per-row

### Verification (Before Claiming Done)
- [ ] Run `composer test` (or relevant test command)
- [ ] Check that old behavior still works
- [ ] No regressions introduced

---

## SYSTEM ARCHITECTURE HARD RULES

1. **Two auth systems NEVER mix:** Staff = `web` guard (users table). Student = custom session (`student_account_id`). Mutually exclusive.
2. **Role middleware syntax:** `role:instructor,chairperson,...`
3. **Student routes:** Under `student.` prefix with `student.auth` middleware
4. **Staff routes:** Explicit named routes (no `Route::resource`)
5. **File uploads:** 2MB max for docs, 10MB for imports
6. **Relative URLs** for student-facing links (never `route()`)
7. **`*/create` routes** before `resource/{id}` wildcards
8. **Never edit merged migrations** — create new ones
9. **Pair models with migrations + factories**

---

## DEFENSE DAY MINDSET — Which Panelist Catches This?

When you find an issue, immediately ask: **"Which panelist would flag this at defense?"**

| Issue Type | Panelist | Why They'd Catch It |
|------------|----------|---------------------|
| Data validation gap, duplicate records, normalization | **Cristine Redoblo, PhD** (Data) | She audits data integrity. Missing unique constraints, silent defaults, unnormalized fields = her territory. |
| UI inconsistency, navigation gap, unclear error | **Tisha May Tizon** (UI/UX) | She walks through every screen. Wrong button styles, icon fallback text, missing labels = she notices first. |
| Broken route, slow page, security gap | **John Paul Ubas** (Infra/Networking) | He checks response times, auth boundaries, file upload limits, and route protection. |
| Report accuracy, export quality, wrong data in charts | **Jason Flor** (Data Analysis) | He cross-checks report numbers against raw data. Wrong counts, missing exports, stale cache = his find. |
| Scope mismatch, missing methodology, incomplete SDLC | **Danes Tumabing, MIT** (Chair) | He compares system against the proposal. Features outside scope or missing core requirements = his question. |
| Anything pre-vetted, adviser-related | **Charwin Padilla, MIT** (Adviser) | Supports the proponent. If he's surprised, the team didn't prepare properly. |

**Rule of thumb:** If you can't name which panelist would care about an issue, it's probably not a real issue. If multiple panelists would care, it's a high-priority fix.

---

## QUESTIONS TO ALWAYS ASK MYSELF

- "Why does the user need to click this?"
- "Can the system do this automatically instead?"
- "What happens if the data is empty/large/invalid?"
- "Is this easy to understand without training?"
- "Would a panelist find a problem with this?"
- "Is this the BEST way, or just the FIRST way I thought of?"
- "If I were the user right now, would I be satisfied?"
- "Is the error message actually helpful, or just technically true?"
- "Am I fixing the root cause or just the symptom?"
- "Have I verified this actually works?"

---

## REMINDERS FROM THE USER

- "Think before you act, Analyze and plan before you proceed"
- "Use your common sense"
- "Suggest what is best for the users not only the functionality but also the user interface"
- "Industry standard and professional level"
- "Be wary of all the globalizations, proper sanitization, all types of error handling, duplication, normalization, HTE, User friendliness, Clear Navigation and CLEAN DATA INTEGRITY"
- "Criticise me, don't just approve on what I want"
- "Put yourself in the perspective of the four users"
- "Take note of real-world scenarios and situational questions"
- "Question yourself"

---

## FORM AUDIT CHECKLIST (Every Form, Every Time)

When creating or editing ANY form, verify ALL of these:

### Server-Side (Controller/Request)
- [ ] Every input field has a validation rule (no unvalidated fields)
- [ ] String fields: maxlength specified (matching DB column)
- [ ] Numeric fields: min/max specified
- [ ] Select fields: `exists:` or `in:` validation on allowed values
- [ ] File uploads: MIME types + max filesize (`max:2048`)
- [ ] Array inputs (like criteria_scores): filter to known keys only before storage (mass assignment protection)
- [ ] JSON columns: cast to `array` in model, filter to known schema before saving
- [ ] All text trimmed before storage: `trim(strip_tags($value))`
- [ ] Strip HTML: `strip_tags()` on any user-submitted text that could contain HTML
- [ ] If appending user data to a string (e.g. supervisor name to comments), wrap in `strip_tags()`
- [ ] `prepareForValidation()` on FormRequests for normalization
- [ ] CSRF check: only needed if it's a `POST` form (ensured by Laravel middleware)

### Client-Side (Blade Form)
- [ ] `@csrf` on every POST/PUT/DELETE form
- [ ] `<input required>` on all mandatory fields
- [ ] `maxlength` attribute on `<input>` and `<textarea>`
- [ ] `type="email"` on email inputs (browser validates format)
- [ ] `type="number"` with `min`/`max` on numeric inputs
- [ ] `accept` attribute on file inputs (`.pdf,.doc,.docx`)
- [ ] `autocomplete="off"` on non-standard fields (like evaluation criteria)
- [ ] `aria-label` on radio buttons / checkboxes for accessibility
- [ ] `{{ }}` auto-escaping (never `{!! !!}` unless explicitly safe)
- [ ] `@error()` directive for per-field error messages
- [ ] Radio groups: `value="{{ $val }}"` should be safe (integer, not user text)
- [ ] `old('fieldname')` to preserve input on validation failure
- [ ] `enctype="multipart/form-data"` on file upload forms
- [ ] No hardcoded text that should be `{{ __('...') }}` for globalization

### Data Storage
- [ ] Model `$fillable` includes only intended fields
- [ ] Model `$casts` properly typed (array, integer, boolean, datetime)
- [ ] Migration column type matches stored data (JSON for nested, integer for scores, etc.)
- [ ] No silent data corruption: default null instead of fake default values

---

## THE 16 UX GUIDELINES — User-Friendly Form & Interface Design

**Added by:** User, July 2, 2026 (post-defense)
**Purpose:** Guide every form, input field, and UI pattern toward maximum user-friendliness.

| # | Guideline | Implementation Notes |
|---|-----------|---------------------|
| 1 | **Use input masks, not placeholder-as-label** | Placeholders should hint (e.g., "09XXXXXXXXX"), not replace labels. Use input masks (`__-____-_______-_` for student numbers, `+63 ___ ____ ____` for phones) so users see the expected format as they type. |
| 2 | **Skeleton loading instead of classic spinners** | Replace full-page spinners/overlays with skeleton screens that mimic the final layout shape (gray placeholder blocks for text, images, tables). Lets users perceive faster loading. |
| 3 | **Prioritize important information** | Put the most critical fields/actions first. Secondary/settings-type info goes below a fold or in expandable sections. Every page should answer "What does the user need to do here?" within the first 3 seconds. |
| 4 | **Align fields with the type of information** | Text? → `<input type="text">`. Email? → `<input type="email">` (mobile shows @ keyboard). Number? → `<input type="number">` or `inputmode="numeric"`. Date? → `<input type="date">`. Phone? → `inputmode="tel"`. Match the input mode to the data type. |
| 5 | **Allow type-to-search + scroll** | Every `<select>` with 6+ options should be searchable (Select2 or Alpine filter). Users should never have to scroll through a 50-item dropdown. Also support scroll-wheel navigation on number fields. |
| 6 | **One clear primary button per action** | Avoid multiple primary-colored buttons on one form. The main action (Save, Submit, Approve) gets the emerald-600 button. Secondary actions (Cancel, Delete) are ghost/outline. Button text must be specific: "Save Changes" not "Submit", "Approve Document" not "Yes". |
| 7 | **Display all options for 2-3 choices** | For 2-3 mutually exclusive choices, use radio buttons or toggle pills (not dropdowns). Users see all options at once — no clicking to reveal. |
| 8 | **Limit color saturation (and dark mode)** | Use muted/saturated colors for backgrounds and UI chrome, not full-bright. Reserve high saturation for alerts and CTAs. If dark mode exists, ensure contrast ratios meet WCAG AA. |
| 9 | **Boxes in forms, not underlines** | Use bordered rectangles (`rounded-lg border px-3 py-2`) instead of Material-style underlines. Boxes are more discoverable as editable areas and work better on touch screens. |
| 10 | **Toggle tokens for large lists** | When users select from a large set (e.g., students, companies), use token/chip UI: type to filter → click to add as a removable chip. Avoid multi-select dropdowns that hide selected items. |
| 11 | **Adjust spacing for related groups** | Group related fields (e.g., Name fields: first, middle, last) with tighter spacing between them (`gap-2`) and wider spacing between groups (`gap-6` or `mb-6`). Visual hierarchy through whitespace. |
| 12 | **CTA close to thumb area (mobile)** | On mobile, primary action buttons should be near the bottom of the viewport or bottom of the card — within thumb reach (~48px from bottom edge). Avoid top-right CTAs on mobile. |
| 13 | **Placeholders as hints in search bars** | Search bars should have descriptive placeholders: "Search by student name or number", "Filter companies..." — not just "Search". Helps users know what they can type. |
| 14 | **Consistent gaps across grids** | Use a single gap size system-wide (`gap-4` or `gap-6` on grids, `space-y-4` on vertical stacks). Never mix `gap-2` on one page with `gap-6` on another for the same layout pattern. |
| 15 | **"Read more" for long text** | Truncate long text (descriptions, notes, comments) at 3-4 lines with a `... Read more` link/button that expands inline or opens a modal. Don't show full text by default if it breaks layout. |
| 16 | **Keep the style consistent** | One button system (emerald-600 primary, slate outline secondary), one input style (rounded-lg, same padding), one gap system, one font. Every page should feel like it belongs to the same app. |


### When to Apply These Guidelines
- **New feature development:** Apply all 16 during initial build — don't wait for polish pass.
- **Existing feature edits:** Audit the changed form against these 16 before claiming done.
- **Review pass (like panelist Tisha May Tizon would):** Walk through every form and check each guideline. Any violation = a fix ticket.
