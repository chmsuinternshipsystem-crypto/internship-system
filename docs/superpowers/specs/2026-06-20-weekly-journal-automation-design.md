# Automated Weekly Journal System

## Problem
The current weekly journal requires students to:
1. Manually create each week with date picker
2. Click Save (page reloads, file inputs reset)
3. Separately save supervisor name (another form, another save)
4. Re-type supervisor name every time
5. Download Template — unclear purpose

## Solution
A single-page AJAX-driven journal with auto-generated weeks, auto-saved activities, and inline file uploads.

## Key Changes

### Auto-Generated Weeks
When student visits `/weekly-journals`, the system checks their deployment's `start_date` and auto-creates any missing week blocks (Mon–Sun) up to today. No manual creation needed.

### Single-Page AJAX Editor
- Activities auto-save on blur (no Save button)
- Files upload inline per day (no page reload)
- Supervisor auto-filled from deployment company contact (read-only)
- Submit for Review button validates content exists

### Removed
- Download Template button
- Manual week creation (create view, store route)
- Separate supervisor save form

## Routes

| Method | URI | Purpose |
|---|---|---|
| GET | `/weekly-journals` | List weeks (auto-generates missing) |
| GET | `/weekly-journals/{journal}` | Show/edit journal with Alpine |
| PATCH | `/weekly-journals/{journal}/activities` | Save activities JSON |
| POST | `/weekly-journals/{journal}/files` | Upload one file for a day |
| DELETE | `/weekly-journals/{journal}/files` | Remove a file |
| POST | `/weekly-journals/{journal}/submit` | Submit for review (unchanged) |

## Design Decisions
- Auto-save on blur (not on every keystroke) to reduce requests
- File upload on file selection (no separate upload button)
- Supervisor auto-filled, not editable — matches deployment company contact
- Only one reviewed status (not pre/monitoring/post) — existing staff UI unchanged
