# AGENTS.md: THHA

Tradition Homes Subdivision Homeowners Association Information System.

This file holds the facts and the rules that must not break. It sets limits on how work is done, never what the work is: the request defines the task and its scope. Do not add steps, files, refactors, or polish the request did not ask for. If the request conflicts with a rule here, say so and ask.

How a file is written lives in `CONVENTIONS.md`; this file does not repeat it. Before writing code, read the `CONVENTIONS.md` sections the task touches — always section 1 (version guardrails).

---

## What we build

One Laravel code base, three surfaces, two account types, never merged:

- **Admin panel** (`/admin`): officers and staff profile homeowners, bill and collect dues, post the books, handle requests and violations, publish reports.
- **Homeowner portal** (`/portal`): the household head sees and edits their household, views bills and recorded payments read-only, books amenities, files requests, reads announcements. Scoped to their own records.
- **Public site** (`/`): announcements, directory, general information.

Modules: `Dashboard` · `Homeowners` · `Rbi` · `Collections` · `Accounting` · `Expenses` · `Amenities` · `Support` · `HoaCenter` · `Reports` · `Settings` · `Access` · `Auth`.

Schema and paths: the migrations in `database/migrations/` and the models in `app/Models/` are the truth, over any other description.

---

## Domain rules

Code that breaks one of these is a defect.

1. **Identity.** A `User` is the household head's login, linked 1:1 to a `Resident`. `households.user_id` and `households.property_id` are unique: one account per household. The office profiles the Resident, creates the household, and issues the User. Family members never get accounts; they join through a link request.
2. **Properties.** `(block, lot)` is unique. A property has an address.
3. **Double entry.** Every `journal_entry` has two or more lines with equal debit and credit totals. Posted only through `JournalEntry::post()`; nothing writes `journal_lines` directly. `source_type` / `source_id` is a loose pointer (a short string such as `payment`), not a foreign key.
4. **Closed periods** reject posting. Entry numbers look like `JE-2026-000123`.
5. **Bills.** `(user_id, type, billing_period)` is unique. `paid_amount`, `balance`, and `status` change only through `BillSettlementService`, never from a form.
6. **Payments are walk-in and admin-only.** The portal never creates a payment. An officer takes the money at the HOA office and records it through `RecordPayment`, which posts the journal entry in the same transaction as the bill update.
7. **Money** is `decimal(12,2)`, handled with `MoneyHelper` (bcmath). Never float.
8. **Requests** flow pending → approved or declined. The office prints the issued document and hands it over in person; the portal never downloads it. A printed certification is valid three months (`document_expires_at`); there is no download limit or download count.
9. **Violations** flow issued → acknowledged → resolved. The acknowledgment signature confirms receipt, not agreement.
10. **Amenity bookings** are billed through `bills` by the admin side, when the booking is approved or filed by the office; deposits have their own status. The portal books a facility and pays nothing online.
11. **Audit.** Admin changes write `admin_activities` through `ActivityLogService`, called by the Action after commit. Portal-initiated Actions do not.
12. **Privacy.** Only the 16-digit PhilSys Card Number is stored or printed, never the Serial Number.
13. **Side effects after commit.** Notifications, SMS, mail, and cache flushes that depend on a write happen after its transaction returns, from the Action.
14. **Transaction slip.** Requests and bookings get a unique `reference` (`REQ-2026-000045`, `BKG-2026-000012`) written when they are filed. The portal gives the homeowner a downloadable slip as proof of filing. It is not a receipt and not an issued document.

---

## Must not break

**Stack.** PHP `^8.1`, Laravel **7.30.7**, Livewire **2.12.8**, AdminLTE 3.2 / Bootstrap 4 / jQuery, DomPDF, avadim Excel. Use only the syntax those versions have; newer framework, Livewire, or Bootstrap API does not exist here. No new package without asking. No build step, no Alpine, no JS framework, no Spatie packages.

**Data safety.** Never run `migrate:fresh`, `db:wipe`, or a destructive migration against anything but a throwaway local database. Never edit `public/vendor/**` or an unchanged copy in `resources/views/vendor/**`.

**Secrets.** None in code, Blade, JS, or git; `env()` is read only inside `config/*.php`. Sensitive fields (PhilSys Card Number, birth date, contacts, mother's maiden name, blood type, income, IDs and proof files) are never logged, never in a URL, never in a cached value, and shown only to the roles and owners that need them.

**Accounts.** Two tables, two guards, never merged: `Admin` / `admins` on `web`, `User` / `users` on `homeowner`. Authorization only through Policies — admin by role, portal by ownership. Livewire update requests skip route middleware, so re-authorize inside every action. Never the stale names `Homeowner` (as a model), `Client`, a `role` column on `users`, or `App\Enums\Role`.

**Layers.** Direction is one way: Livewire / Controller / Command / Job → Action or Query → Service → Utility. Never upward. Queries never write. Services never call Actions or Queries. Utilities never import `App\Models` or `App\Enums`. Layer details, defect shapes, and the self-check: `CONVENTIONS.md` section 7.

**No authorization by hand.** Never an inline role check, and never `where('user_id', ...)` in place of a Policy.

**Existing code that breaks a rule is a defect to migrate, never precedent.** Do not copy it. Fix it only when the task is a cleanup or touches that file; otherwise leave it and name it in your report (`file:line`, what, why).

**A security problem found** (authorization gap, SQL injection, exposed private file) goes at the top of your report.

---

## Where to look

| Question | Answer |
|---|---|
| How do I write this file? | `CONVENTIONS.md` |
| What is the real path, class, route, or column? | The code and migrations |
| Which RBI field, label, or deadline? | The paper forms the HOA supplied, and `resources/views/pdf/` |
| Does this class already exist? | `app/Actions/`, `app/Queries/`, `app/Services/`, `app/Utility/`, `app/Http/Livewire/Shared/`, `app/View/Components/` |

---

## Finishing

State what changed, what you ran to check it, and anything you could not check. Never report a check that did not run as a pass.
