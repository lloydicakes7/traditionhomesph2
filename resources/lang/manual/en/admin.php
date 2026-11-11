<?php

/*
|--------------------------------------------------------------------------
| User Manual — Admin Panel
|--------------------------------------------------------------------------
|
| The admin panel's copy, keyed by the topics the catalogue in
| `config/manual.php` names. Each topic is a `title` and its `sections`; a
| section is a `title` plus any of `body` (one paragraph), `prerequisites`,
| `steps`, `result` (what you should see), `fields` (screen reference) and
| `notes`. `partials/topic.blade.php` prints the blocks in that fixed order.
|
| The front matter and the help chapters are offered to every officer, so
| their copy may not name a tool a role might not hold.
|
*/

return [

    'title' => 'User Manual',
    'intro' => 'How to operate the THHA system, task by task. Only the tools your role can open are listed.',

    'groups' => [
        'getting_started'    => 'GETTING STARTED',
        'operations'         => 'OPERATIONS',
        'finance'            => 'FINANCE',
        'reports_and_support' => 'REPORTS AND SUPPORT',
        'administration'     => 'ADMINISTRATION',
        'working_by_role'    => 'WORKING BY ROLE',
        'help'               => 'HELP',
    ],

    'topics' => [

        'introduction' => [
            'title'    => 'Introduction',
            'sections' => [
                [
                    'title' => 'What the system is for',
                    'body'  => 'The THHA Information System is the association\'s office in a browser. It keeps the homeowner file (the RBI records), bills and collects dues and other charges, records what the association spends, keeps a double-entry ledger of both, takes amenity bookings, logs the requests, complaints and violations homeowners file, prints the association\'s forms, and reports on all of it.',
                ],
                [
                    'title' => 'Who it is for',
                    'body'  => 'Officers and staff of the association work in the admin panel, at /admin. Homeowners sign in to the portal, at /portal. They are two separate account types — an officer\'s login is not a homeowner\'s login, and neither can open the other\'s pages.',
                ],
                [
                    'title'  => 'How the system is organised',
                    'body'   => 'One rule explains the whole panel: the sidebar is the index. It lists every tool, grouped by area, and it only draws the tools your role can open.',
                    'fields' => [
                        'Sidebar'      => 'The tools the system offers you, grouped under headers. The search box at its top finds one by name.',
                        'Navbar'       => 'The bar across the top. The hamburger toggles the sidebar, the bell counts your notifications, and your name opens your profile and the sign-out.',
                        'Content area' => 'The page you opened, headed by its title and — on a list — a toolbar of that page\'s own actions.',
                        'Footer'       => 'The association\'s own name and the copyright line.',
                        'Dashboard'    => 'Where signing in lands you: the figures that matter today, the year in charts, and everything still open.',
                    ],
                ],
                [
                    'title'  => 'Key concepts',
                    'body'   => 'Six ideas make the rest of the manual read easily. Fuller definitions are in Help → Glossary.',
                    'fields' => [
                        'Resident'          => 'A person the office has profiled. Every homeowner account is linked to exactly one.',
                        'Homeowner account' => 'The portal login issued to a resident. It is the account the association bills, not the person\'s name.',
                        'Bill'              => 'One charge. Monthly dues, an invoice and a paid booking all land on the same ledger as a bill.',
                        'Payment'           => 'Money received against a bill. A bill settles only from the receipts recorded against it.',
                        'Journal entry'     => 'The double-entry record behind money moving in or out. The books are the whole of these.',
                        'Permission'        => 'One switch on a role — "may add a resident", "may approve a request". Your role decides your menus.',
                    ],
                ],
            ],
        ],

        'getting-started' => [
            'title'    => 'Getting Started',
            'sections' => [
                [
                    'title'  => 'Opening the panel',
                    'body'   => 'The panel is a web address, not an installed program: any current browser can reach it, from the office or from home.',
                    'steps'  => [
                        'Type the association\'s address into the browser\'s address bar and add /admin (for example https://example.org/admin), then press Enter.',
                        'If you are not signed in, the sign-in page opens.',
                    ],
                    'result' => [
                        'The sign-in page appears, asking for your email address and your password.',
                    ],
                    'notes'  => [
                        'Bookmark the address — the office\'s exact address is set by the association, not by this manual.',
                    ],
                ],
                [
                    'title'         => 'How to sign in',
                    'prerequisites' => [
                        'An officer account issued to you by the association.',
                        'The address of the panel.',
                    ],
                    'steps'  => [
                        'Open the sign-in page.',
                        'Enter the email address and password your account was issued with.',
                        'Select Sign in.',
                        'If the office requires a code for your role, type the code that was emailed to you (see the next section).',
                    ],
                    'result' => [
                        'The dashboard opens.',
                        'Your name appears at the right of the navbar, and the sidebar lists the tools your role may open.',
                    ],
                    'notes'  => [
                        'Wrong attempts are counted: after too many in a row the sign-in is held back for a short while. The number and the window are set in Administration → Settings → Security.',
                        'A deactivated account is refused however correct the password is. Ask the Administrator to reactivate it.',
                    ],
                ],
                [
                    'title'         => 'Forgotten passwords',
                    'prerequisites' => [
                        'Access to the email address on your account.',
                    ],
                    'steps'  => [
                        'Select Forgot your password? on the sign-in page.',
                        'Enter your email address and submit.',
                        'Open the email and follow the reset link.',
                        'Choose a new password that meets the rules the form prints, and confirm it.',
                    ],
                    'result' => [
                        'The form answers with a confirmation rather than your password, and the new password signs you in.',
                    ],
                    'notes'  => [
                        'The reset link expires; ask for a fresh one if it has.',
                        'The password rules come from Settings → Security and are the same ones an account form enforces.',
                        'An account whose password has passed the expiry window is parked on its profile page until the password is changed.',
                    ],
                ],
                [
                    'title'  => 'A quick tour of the screen',
                    'body'   => 'Everything the panel does is reached from three places: the sidebar, the navbar and the page you are on.',
                    'fields' => [
                        'Sidebar headers'   => 'The areas of the association: operations, finance, reports and support, administration.',
                        'Sidebar search'    => 'Type a page name and pick the result. It searches the menu you can see, so it never offers a page you cannot open.',
                        'Navbar toggler'    => 'Collapses the sidebar to icons and back — useful on a small screen.',
                        'Navbar bell'       => 'Your own notifications: what was filed, approved, paid or issued, newest first.',
                        'Your name'         => 'Your menu: profile, and sign out.',
                        'Page header'       => 'The page title, and beside it the actions that apply to the whole page.',
                        'List toolbar'      => 'Search, filters, Add, Import and Export on the pages that carry a list.',
                        'Actions (per row)' => 'One menu per row — View, Edit, Delete, and the record\'s own moves such as Print or Approve.',
                        'Footer'            => 'The association\'s name, taken from Settings → General.',
                    ],
                    'steps'  => [
                        'Open a page from the sidebar and watch the header change with it.',
                        'Open a row\'s Actions menu to see what that record allows.',
                    ],
                    'notes'  => [
                        'Almost every dialog opens at once and pulls its own record from the server as it opens — that is why a dialog shows a brief loading state.',
                    ],
                ],
                [
                    'title' => 'Signing out',
                    'body'  => 'Open your name at the right of the navbar and choose Sign out. On a shared computer, close the browser as well so the next person sees the sign-in page rather than your session.',
                ],
            ],
        ],

        'dashboard' => [
            'title'    => 'Dashboard',
            'sections' => [
                [
                    'title' => 'What the dashboard is for',
                    'body'  => 'It is the office\'s landing page: today\'s figures, the year in charts, and everything standing open. It reads the same services its own report pages read, so a figure here and the figure on the report can never disagree.',
                ],
                [
                    'title'  => 'Reading the headline figures',
                    'body'   => 'The four cards at the top are live counts, not cached placeholders.',
                    'fields' => [
                        'Homeowners'        => 'Every portal account issued.',
                        'Paid bills'        => 'Every bill money has been received against, whatever raised it.',
                        'Open requests'     => 'Requests the office has not decided yet.',
                        'Active violations' => 'Violations still standing against a resident.',
                    ],
                    'notes'  => [
                        'Click a card\'s footer link to jump to the list behind the figure. The link only appears when your role may open that list.',
                    ],
                ],
                [
                    'title' => 'The year in charts',
                    'body'  => 'Homeowners by sex, Profit and Loss, Monthly Income and Monthly Expenses, all for the current year. They are drawn from the posted transactions, so they move as receipts and expenses are posted and entries are written.',
                ],
                [
                    'title' => 'Following up',
                    'body'  => 'Unresolved Violations and Requests Awaiting Action sit below the charts.',
                    'steps' => [
                        'Use View All on either card to open the full register.',
                        'Open a row there to act on it.',
                    ],
                ],
            ],
        ],

        'hoa-center' => [
            'title'    => 'HOA Center',
            'sections' => [
                [
                    'title'  => 'What HOA Center holds',
                    'body'   => 'The association\'s own front matter — who serves, what is announced, and what the public directory shows. Everything here is published to the public site, so write it for a reader outside the office.',
                    'fields' => [
                        'HOA Members'   => 'The roster of officers and committee members, shown on the public Members page.',
                        'Announcements' => 'Posts the association publishes, reaching the public site and the homeowner portal once live.',
                        'Directory'     => 'The public list of establishments and services in the subdivision, with their groups and phone numbers.',
                    ],
                ],
                [
                    'title'         => 'How to publish an announcement',
                    'prerequisites' => [
                        'The Create permission on announcements.',
                    ],
                    'steps'  => [
                        'Open HOA Center → Announcements and select Add Announcement.',
                        'Give it a title and the body the public should read.',
                        'Save it, then publish it.',
                    ],
                    'result' => [
                        'The announcement appears in the list, and — once published — on the public site and in the portal.',
                    ],
                    'notes'  => [
                        'An unpublished announcement stays a draft: it is visible to the office and to nobody else.',
                        'Edit or retire a post once it is no longer current rather than leaving it up.',
                    ],
                ],
                [
                    'title'  => 'Keeping the members roster',
                    'steps'  => [
                        'Open HOA Center → HOA Members.',
                        'Add a member with the name and position the public should read.',
                        'Edit or delete a member whose term has ended.',
                    ],
                    'result' => [
                        'The public Members page reads the roster as saved — the office and the public can never disagree, because both read the same record.',
                    ],
                ],
                [
                    'title'  => 'Maintaining the public directory',
                    'steps'  => [
                        'Open HOA Center → Directory.',
                        'Add an entry: the establishment or service, its group, and the phone numbers it publishes.',
                        'Edit or remove an entry that has closed.',
                    ],
                    'notes'  => [
                        'The directory is public. Do not put a homeowner\'s private number in it — that belongs on their resident record.',
                    ],
                ],
            ],
        ],

        'homeowners' => [
            'title'    => 'Homeowners',
            'sections' => [
                [
                    'title'  => 'What the Homeowners area holds',
                    'body'   => 'The people and places every other module reads: the addresses, the lots and units, the residents the office has profiled, the households they live in, and the portal accounts issued to them. Billing, bookings, requests and reports all reach a household through these records, so keep them current.',
                    'fields' => [
                        'Addresses'  => 'Every street address on file. A property is created against one.',
                        'Properties' => 'The lots and units of the subdivision, each tied to an address.',
                        'Residents'  => 'The people the office has profiled. The RBI form and the membership form are printed from this record.',
                        'Homeowners' => 'The portal accounts: one per resident, linking the person to a login.',
                        'Households' => 'An occupied unit, the household living in it, and its members.',
                    ],
                ],
                [
                    'title'         => 'How to add a resident',
                    'prerequisites' => [
                        'The address (and property) the person belongs to — create those first if they are new.',
                        'The Create permission on residents.',
                    ],
                    'steps'  => [
                        'Open Homeowners → Residents and select Add Resident.',
                        'Fill in the personal details the form asks for. Required fields cannot be left blank.',
                        'Save the record.',
                    ],
                    'result' => [
                        'The resident appears in the Residents list.',
                        'The record is now selectable wherever the office names a resident — a household member, a request, a violation.',
                    ],
                    'notes'  => [
                        'A blank field prints blank: the RBI form takes its values from the resident profile, so a missing middle name or birth date shows up in the printout.',
                        'Residents can be imported and exported in bulk from the list toolbar.',
                        'The Residents list can print the membership form for the row under Actions → Print.',
                    ],
                ],
                [
                    'title'         => 'How to issue a portal account',
                    'prerequisites' => [
                        'The resident already profiled.',
                        'The Create permission on homeowners.',
                    ],
                    'steps'  => [
                        'Open Homeowners and select Add Homeowner.',
                        'Choose the resident the account belongs to, and give it an email address and a first password.',
                        'Save, then hand the sign-in details to the resident.',
                    ],
                    'result' => [
                        'The resident appears in the Homeowners list and can sign in to the portal.',
                    ],
                    'notes'  => [
                        'A resident can exist without an account; an account can never exist without a resident.',
                        'Deactivating an account blocks sign-in and keeps its history — the record is not deleted.',
                        'The association does not offer self-registration on the portal: every account is issued here.',
                    ],
                ],
                [
                    'title'         => 'How to record a household',
                    'prerequisites' => [
                        'A property, and a homeowner account that does not already occupy another unit.',
                    ],
                    'steps'  => [
                        'Open Homeowners → Households and select Add Household.',
                        'Choose the homeowner account and the property their household occupies.',
                        'Save, then add the members living in it.',
                    ],
                    'result' => [
                        'The unit shows as occupied, and the household and its members can be read from the property and the account.',
                    ],
                    'notes'  => [
                        'One household per unit and one per account: an account that already occupies a unit is not offered again.',
                        'Households and members can be imported and exported in bulk.',
                    ],
                ],
                [
                    'title'  => 'Reading a list',
                    'body'   => 'Every list in the panel — here and in the modules that follow — wears the same three parts, so one explanation serves them all.',
                    'fields' => [
                        'Toolbar'           => 'Search, filters and the page actions (Add, Import, Export).',
                        'Ticks'             => 'Tick the rows you mean, then use the selection bar that appears — for example Delete Selected.',
                        'Actions (per row)' => 'One menu per row: View, Edit, Delete, and the moves that record allows, such as Print.',
                        'Chevrons / pager'  => 'Long lists page; the pager sits under the table.',
                    ],
                    'notes'  => [
                        'Import is CSV and expects the template the page offers — download it first, and keep the header row.',
                        'Export writes exactly the rows the current filters select.',
                    ],
                ],
            ],
        ],

        'print-documents' => [
            'title'    => 'Print Documents',
            'sections' => [
                [
                    'title'  => 'What the printed forms are',
                    'body'   => 'The association\'s fixed forms and letters. Each one is filled from records already in the system, so the office types as little as possible. Paper size and the letterhead come from Settings → Print.',
                    'fields' => [
                        'RBI Form A'          => 'The household sheet: one line per household, with tenure, water and electricity supply.',
                        'RBI Form B'          => 'The individual sheet: one resident\'s profile, household and attestation.',
                        'RBI Form C'          => 'The semestral summary of the association\'s households in age brackets.',
                        'Membership Form'     => 'The membership application, printed from a member record.',
                        'Residents List'      => 'Every resident at an address, or the whole association, as a table.',
                        'Business Permit'     => 'The permit for a resident\'s business within the subdivision.',
                        'HOA Permit'          => 'The permit for work in the subdivision, giving its type and scope.',
                        'Endorsement Letter'  => 'A letter endorsing a resident to a named addressee.',
                        'Certification'       => 'A certification stating its purpose.',
                        'Invitation Letter'   => 'The invitation, printed for every homeowner or a chosen set.',
                        'Sinumpaang Salaysay' => 'The sworn statement.',
                        'Journal Voucher'     => 'The voucher for a posting on the books, printed from that journal entry.',
                        'Violation Notice'    => 'The notice for a recorded violation.',
                    ],
                ],
                [
                    'title'         => 'How to print a document',
                    'prerequisites' => [
                        'The records the document is built from — the resident, the address or the journal entry — already on file.',
                    ],
                    'steps'  => [
                        'Open the document from the Print Documents menu.',
                        'Complete the fields the preview asks for. Required fields are marked; the form refuses to generate a document it cannot fill.',
                        'Check the preview.',
                        'Select Print for the browser\'s print dialog, or PDF to download the document as a file.',
                    ],
                    'result' => [
                        'The preview draws the finished document, letterhead and all.',
                        'The PDF action downloads the same document, named after the document and the record it was built for.',
                    ],
                    'notes'  => [
                        'A few documents come out better on wider paper: the residents list is printed landscape.',
                        'Very long documents are capped so one PDF stays printable, and the document says so when it stops.',
                        'Printing is recorded in the activity log, so an issued notice can be accounted for later.',
                    ],
                ],
            ],
        ],

        'amenities-booking' => [
            'title'    => 'Amenities Booking',
            'sections' => [
                [
                    'title'  => 'What the booking module holds',
                    'body'   => 'The common facilities of the subdivision and the reservations against them — whether the homeowner filed the booking from the portal or the office took it by phone.',
                    'fields' => [
                        'Bookings'      => 'Every reservation, with the date and hours, the resident, the fee and the deposit it carries.',
                        'Calendar View' => 'The same reservations laid out by date, so a double booking is visible at a glance.',
                        'Amenities'     => 'The facilities themselves: rate, weekly schedule, deposit rule, and whether the portal offers them.',
                    ],
                ],
                [
                    'title'         => 'How to add or change an amenity',
                    'prerequisites' => [
                        'The Create or Edit permission on amenities.',
                    ],
                    'steps'  => [
                        'Open Amenities Booking → Amenities and select Add Amenity.',
                        'Name it, and set how it is charged: a flat rate, or an hourly rate that multiplies the booked hours.',
                        'Set the weekly schedule. Switch a weekday off to close it — a closed day keeps no hours.',
                        'Set the deposit rule, and whether homeowners may book it from the portal.',
                        'Save.',
                    ],
                    'result' => [
                        'The amenity appears in the list and, if the portal is allowed, on the homeowner\'s booking form.',
                    ],
                    'notes'  => [
                        'An amenity whose rate is zero or blank is free: a free booking never reaches a payment step.',
                        'A booking that already exists keeps the rate it was priced at — changing the amenity never re-prices it.',
                    ],
                ],
                [
                    'title'         => 'How the office actions a booking',
                    'prerequisites' => [
                        'The Edit permission on bookings.',
                    ],
                    'steps'  => [
                        'Open Amenities Booking → Bookings and find the reservation.',
                        'Open its Actions menu. The moves offered are the ones that still apply to that booking.',
                        'Approve it to accept the slot.',
                        'Take the payment for a booking that costs anything, through the ledger\'s own payment form.',
                        'Complete it once the amenity has been used.',
                        'Refund or forfeit the deposit if there was one and the household did not meet the conditions.',
                    ],
                    'result' => [
                        'The booking moves through its states and the list, the calendar and the homeowner\'s page all say the same thing.',
                        'A booking\'s invoice appears on Collections → Invoices and is collected there like any other bill; its lines follow the reservation until a payment is recorded.',
                    ],
                    'notes'  => [
                        'Editing an unpaid booking re-prices its invoice; once a payment is recorded, the invoice is the historical bill of record.',
                        'A free booking is closed as used straight from being approved — there is nothing to collect.',
                        'A homeowner may withdraw a booking only while it is still pending approval; after that the office controls it.',
                        'Each move re-checks the booking as it opens, so a page left open overnight cannot fire a move that no longer applies.',
                    ],
                ],
            ],
        ],

        'collections' => [
            'title'    => 'Collections',
            'sections' => [
                [
                    'title'  => 'What billing and receipts cover',
                    'body'   => 'The association\'s income side. Monthly dues, one-off invoices and paid bookings are all bills; every receipt — taken at the counter or filed from the portal — is a payment against one of them.',
                    'fields' => [
                        'Invoices'               => 'Charges the office raises by hand, for a resident, a unit or a booking.',
                        'Monthly Dues'           => 'The recurring dues, generated for a month, with the dues table and the generator behind them.',
                        'Payments'               => 'The one receipt ledger, including the payments homeowners filed that still wait for approval.',
                        'Advance Deposits'       => 'Credit the association holds for an account, drawn down against later bills.',
                        'Products and Services'  => 'The chargeable items invoices and bookings are built from, grouped by category.',
                        'Email SOA'              => 'Statements of account sent to homeowners, and the record of what was sent.',
                    ],
                ],
                [
                    'title'         => 'How to raise a one-off invoice',
                    'prerequisites' => [
                        'The household\'s account, and the product or amount to charge.',
                    ],
                    'steps'  => [
                        'Open Collections → Invoices and select Add Invoice.',
                        'Choose the account being billed and set the billing date.',
                        'Add the lines: the product or service, the quantity and the price.',
                        'Save, then check the total the form shows before generating it.',
                    ],
                    'result' => [
                        'The invoice appears in the list with a balance equal to its total.',
                        'It appears on the household\'s Statement of Account in the portal.',
                    ],
                    'notes'  => [
                        'A line keeps the name and price it was billed at, so renaming a product later never rewrites an issued invoice.',
                        'A bill is money owed, not income: the income is recognised when a payment is recorded.',
                        'A bill money has been received against is cancelled, never deleted.',
                    ],
                ],
                [
                    'title'         => 'How to run the monthly dues',
                    'prerequisites' => [
                        'The dues rates and due day set in Settings → Collections.',
                    ],
                    'steps'  => [
                        'Open Collections → Monthly Dues and check the month you are about to bill.',
                        'Review the generator: it lists the units due to be billed and the rate each one is priced at.',
                        'Run it to raise the dues.',
                        'Read the dues table for the month and correct anything that stands wrongly — adjust the invoice or void it — then generate again.',
                    ],
                    'result' => [
                        'One dues bill per unit for that month, each carrying the rate it was priced from.',
                    ],
                    'notes'  => [
                        'A month is generated once: a unit already billed for the month is not billed again.',
                        'A due copies its rate configuration onto itself, so changing the rate later never re-prices a month already billed.',
                        'Credit held for an account is drawn down against the month automatically as the dues are raised.',
                    ],
                ],
                [
                    'title'         => 'How to record a payment taken at the office',
                    'prerequisites' => [
                        'The bill being settled.',
                    ],
                    'steps'  => [
                        'Open Collections → Payments and select Record Payment.',
                        'Choose the bill the money settles.',
                        'Enter the amount received, the method, the date and the reference.',
                        'Save.',
                    ],
                    'result' => [
                        'The receipt is recorded and the bill recomputes its balance from its receipts.',
                        'A fully settled bill reads as paid wherever it is listed.',
                    ],
                    'notes'  => [
                        'A bill the portal has already been paid against shows a waiting receipt instead: the form refuses a second receipt and names the one waiting. Approve or reject that one first.',
                        'Part payments are ordinary: record what was handed over and the balance stands for the rest.',
                        'Recording a payment does not touch the books. Accounting posts it from the Payments row with Post to Journal, choosing the account the money went into.',
                    ],
                ],
                [
                    'title'         => 'How to approve a payment filed from the portal',
                    'prerequisites' => [
                        'The receipt appears in the Payments list as pending.',
                    ],
                    'steps'  => [
                        'Open Collections → Payments and find the pending receipt.',
                        'Open it and check the proof of payment the homeowner attached against the amount and the reference.',
                        'Approve it to record it, or delete it to reject it so the homeowner can file again.',
                    ],
                    'result' => [
                        'The approved receipt counts against the bill and settles what it covers.',
                        'The homeowner\'s My Payments page shows it as recorded.',
                    ],
                    'notes'  => [
                        'A pending receipt settles nothing and does not reach the books; approving it records the money, and posting the entry stays a separate accounting step.',
                        'Because a pending receipt leaves the bill standing, the office must not take the same money over the counter while it waits.',
                    ],
                ],
                [
                    'title' => 'Advance deposits and products',
                    'body'  => 'Credit held on an account is recorded as an advance deposit, against the account the officer picks rather than a unit. Products are the catalogue invoices and bookings are built from, and each belongs to a category that decides the income account the money credits.',
                    'notes' => [
                        'A product or method a record points at is retired rather than deleted, so old invoices stay readable.',
                        'Deleting a product does not touch the invoices that already name it.',
                    ],
                ],
                [
                    'title'         => 'How to email statements of account',
                    'prerequisites' => [
                        'The accounts have an email address and at least one open bill.',
                    ],
                    'steps'  => [
                        'Open Collections → Email SOA.',
                        'Prepare the batch: it lists the accounts that have something to state.',
                        'Preview one to check the letterhead and the list of open bills.',
                        'Send the batch and read the record of what was sent.',
                    ],
                    'result' => [
                        'Each household receives one statement listing its open dues and charges together.',
                    ],
                    'notes'  => [
                        'A statement is one table: recurring dues and one-off charges are listed together rather than apart.',
                        'Re-sending is safe and is recorded, so the office can see what a household was told and when.',
                    ],
                ],
            ],
        ],

        'expenses' => [
            'title'    => 'Expenses',
            'sections' => [
                [
                    'title'  => 'What the spending pages cover',
                    'body'   => 'The association\'s outgoings and the catalogues behind them.',
                    'fields' => [
                        'Expenses'           => 'Money spent. An expense is drafted, then approved — and accounting posts it to the books separately.',
                        'Expense Categories' => 'The headings expenses are grouped under. A category decides which account the spend posts against.',
                        'Suppliers'          => 'The payees an expense can name.',
                    ],
                ],
                [
                    'title'         => 'How to record an expense',
                    'prerequisites' => [
                        'The supplier and the category already on file.',
                    ],
                    'steps'  => [
                        'Open Expenses and select Record Expense.',
                        'Choose the supplier and the dates the wizard asks for.',
                        'Add the lines: what was bought, the quantity and the price.',
                        'Save it as a draft.',
                        'Open it and approve it once the figures are agreed.',
                    ],
                    'result' => [
                        'The expense moves Draft → Approved, and the list and its totals follow.',
                        'Accounting then posts it from the expense row with Post to Journal, choosing the account the money came from.',
                    ],
                    'notes'  => [
                        'A draft is the pending state — nothing needs sending anywhere for an expense to wait for approval.',
                        'A line keeps the category name it was filed under, so a removed category never rewrites an old expense.',
                        'Only a draft can be edited. Deleting a posted expense posts its reversal first, so the books keep both postings; an approved expense with no entry is simply removed.',
                    ],
                ],
                [
                    'title'  => 'Categories and suppliers',
                    'body'   => 'Both are catalogues the office keeps tidy: a category is still the heading an old expense was filed under, and a supplier is still the payee an old expense named.',
                    'notes'  => [
                        'A category or supplier that expenses already use cannot be deleted.',
                        'A category with no account of its own falls back to the unclassified account.',
                    ],
                ],
            ],
        ],

        'accounting' => [
            'title'    => 'Accounting',
            'sections' => [
                [
                    'title'  => 'What the books are',
                    'body'   => 'The double-entry ledger every money module posts into. Nothing stores a balance: every figure is derived from the lines that were posted, so the books and the reports built from them always agree.',
                    'fields' => [
                        'Chart of Accounts'   => 'The accounts the books are kept on. Charges and receipts post to them automatically.',
                        'Journal Entries'     => 'The journal itself: every posting, with its voucher.',
                        'Transfers'           => 'Money moved between accounts outside a bill or an expense.',
                        'Transactions'        => 'A read-only feed of every posting line, useful for tracing where a balance came from.',
                        'Period Close'        => 'A month closed so nothing more posts to it, reopenable when a correction is unavoidable.',
                    ],
                ],
                [
                    'title'  => 'The chart of accounts',
                    'body'   => 'The chart is grouped into Income, Expenses and Cash.',
                    'steps'  => [
                        'Open Accounting → Chart of Accounts to read the accounts, this year\'s income and expenses, and what each cash account holds.',
                        'Add an account by name and kind — a code is suggested and you can change it. Codes are 3 to 6 digits and must be unique.',
                        'A cash account also asks what it already holds on its start date.',
                        'Rename or change an account while nothing has been posted to it; a category-owned account is renamed on its category page.',
                    ],
                    'result' => [
                        'The account appears in its group with its amount, zero until something posts to it. A starting balance is recorded as money from outside the association.',
                    ],
                    'notes'  => [
                        'An account can be removed only while nothing has been posted to it and no category, product, payment method or transfer points at it. Otherwise the chart says why and keeps it.',
                        'A few anchor accounts are created for you the first time a posting needs one, so a fresh install never posts into nothing.',
                    ],
                ],
                [
                    'title'  => 'The journal',
                    'body'   => 'A bill raised is not a posting: income is recognised when accounting posts the receipt that collects it. An approved expense is posted the same way, from the expense row. A journal entry has one amount, an account it goes to and an account it comes from, so its two sides are equal by construction.',
                    'steps'  => [
                        'Open Accounting → Journal Entries and search for the posting you want.',
                        'View it to read Goes To and Comes From, or print its voucher from the row\'s Actions menu.',
                        'Edit a manual entry to correct its amount, accounts or memo. A payment, expense or transfer entry keeps the amount and accounts its source recorded; its memo can still be clarified.',
                        'Remove a manual entry from its edit window when it should not stand. A module\'s entry is removed with its source instead.',
                    ],
                    'result' => [
                        'The entry keeps its equal sides and reads the same way on the page, in the view window and on the voucher.',
                    ],
                    'notes'  => [
                        'The voucher is the printable record of the entry and prints the same account names the entry was posted with.',
                        'A closed month refuses a new entry, an edit and a removal until it is reopened.',
                    ],
                ],
                [
                    'title' => 'Transfers and the register',
                    'body'  => 'A transfer moves money between two accounts without a bill or an expense behind it — a bank deposit, for instance. The transaction register lists every line, read-only, for tracing.',
                ],
                [
                    'title'  => 'How to close a period',
                    'steps'  => [
                        'Open Accounting → Period Close and find the month.',
                        'Close it.',
                        'Reopen it only if a correction cannot wait for the next period.',
                    ],
                    'result' => [
                        'A closed month takes no further postings; the register says so.',
                    ],
                    'notes'  => [
                        'Closing is per month and is recorded, so who closed what and when is answerable.',
                    ],
                ],
            ],
        ],

        'supports' => [
            'title'    => 'Supports',
            'sections' => [
                [
                    'title'  => 'What the Support pages hold',
                    'body'   => 'The three records the association keeps about its homeowners: requests for a document or a service, complaints about the subdivision, and violations of its rules.',
                    'fields' => [
                        'Requests'   => 'Filings from homeowners for one of the five documents. A request is pending until the office approves or declines it.',
                        'Complaints' => 'Concerns filed by homeowners, with the office\'s own record of how each was handled.',
                        'Violations' => 'Rule breaches recorded against a resident, each with a printable notice.',
                    ],
                ],
                [
                    'title'         => 'How to action a request',
                    'prerequisites' => [
                        'The Edit permission on requests to decide one.',
                    ],
                    'steps'  => [
                        'Open Support → Requests and find the filing.',
                        'View it to read what was asked for and by whom.',
                        'Approve it to issue the document, or decline it — a decline must say why.',
                        'Raise the invoice with Create Invoice when the document is to be paid for.',
                        'Open Payments on the row to see whether the household has paid, and to approve a receipt the portal filed.',
                        'Print the document once the request is approved.',
                    ],
                    'result' => [
                        'An approved request shows its Print and Complete actions and stops offering Edit, Approve and Cancel.',
                        'A cancelled request records the reason and closes.',
                    ],
                    'notes'  => [
                        'A decided request is never rewritten: the moves left on a row follow its own status, not the reader.',
                        'Create Invoice needs the household\'s portal account; a resident with no account cannot be billed, and the row says so.',
                        'Once a request is invoiced the row reads Invoiced and stops offering another invoice, so the same request is never billed twice.',
                    ],
                ],
                [
                    'title' => 'How a request document reaches the homeowner',
                    'body'  => 'Approving a request issues its document and opens the window in which it may be downloaded. The release rule is stated, not guessed: the request must be approved, the invoice raised for it must be paid, the window must still be open, and the download budget must not be spent.',
                    'notes' => [
                        'The portal card explains each withholding — invoice unpaid, window closed, budget spent.',
                        'A document that needs values the request never collected is refused rather than printed half blank.',
                    ],
                ],
                [
                    'title'  => 'How to record a violation',
                    'steps'  => [
                        'Open Support → Violations and select Add Violation.',
                        'Choose the resident, the type of breach and the date it happened.',
                        'Record what was observed.',
                        'Print the notice from the row\'s Actions menu and serve it.',
                        'Resolve the violation once it has been settled.',
                    ],
                    'result' => [
                        'The violation appears on the resident\'s portal page, and the household is notified.',
                        'The notice downloads as a PDF built from the association\'s letterhead.',
                    ],
                    'notes'  => [
                        'Only unresolved violations count on the dashboard and in the community report.',
                    ],
                ],
                [
                    'title'  => 'How to answer a complaint',
                    'steps'  => [
                        'Open Support → Complaints and find the filing.',
                        'View it, then Respond with what the office did about it.',
                        'Edit a filing the office entered itself, or delete one filed in error.',
                    ],
                    'result' => [
                        'The complaint shows its status and the office\'s remarks, which is what the homeowner reads.',
                    ],
                ],
            ],
        ],

        'reports' => [
            'title'    => 'Reports',
            'sections' => [
                [
                    'title'  => 'What the reports are',
                    'body'   => 'The association\'s read-only reporting, each figure drawn from the module that owns it so a report can never disagree with the page it summarises.',
                    'fields' => [
                        'Profit & Loss'        => 'Income against expenses for a period.',
                        'Cash Flow'            => 'Money in and out, by activity.',
                        'AR Aging'             => 'What homeowners owe the association, by age.',
                        'Monthly Income / Expenses' => 'The two movements month by month.',
                        'Daily Collection, Daily Income, Daily Expenses' => 'The day registers: what was collected, earned and spent.',
                        'Statement of Account' => 'One household\'s open bills, ready to hand over or mail.',
                        'Community'            => 'The subdivision in numbers — population, households, bookings and their earnings.',
                        'Residents'            => 'The resident population and its mix.',
                    ],
                ],
                [
                    'title'  => 'How to run and export a report',
                    'steps'  => [
                        'Open the report from the Reports menu.',
                        'Set the period or the filters the report offers.',
                        'Read the figures; the charts redraw with them.',
                        'Export the table if you need it in a spreadsheet or for a meeting.',
                    ],
                    'result' => [
                        'The report draws the rows the filters select, with its totals under the table.',
                        'Export downloads a CSV of exactly those rows and columns.',
                    ],
                    'notes'  => [
                        'The Community and Residents reports are several distributions at once, so they carry no export.',
                        'A report never writes anything: running one leaves the books untouched.',
                    ],
                ],
            ],
        ],

        'user-management' => [
            'title'    => 'User Management',
            'sections' => [
                [
                    'title'  => 'Officer accounts and roles',
                    'body'   => 'Every officer and staff member signs in with an account of their own, and each account holds exactly one role. The role is what carries the permissions: change a role and every officer holding it changes with it.',
                    'fields' => [
                        'Users'       => 'The officer and staff accounts for the panel, with their role and whether they are active.',
                        'Roles'       => 'The permission switches a role carries, and the officers holding it.',
                        'System role' => 'A role the software itself runs on. It cannot be taken away, and the Administrator role always holds the whole catalogue.',
                    ],
                ],
                [
                    'title'         => 'How to add an officer',
                    'prerequisites' => [
                        'The role the officer will hold — create it first if it is new.',
                    ],
                    'steps'  => [
                        'Open Administration → Users and select Add User.',
                        'Give the officer their name and the email address they will sign in with.',
                        'Choose their role and set a first password that meets the printed rules.',
                        'Save, then hand the sign-in details over and ask the officer to change the password.',
                    ],
                    'result' => [
                        'The account appears in the list and can sign in immediately.',
                    ],
                    'notes'  => [
                        'An account holds exactly one role: a person who needs two kinds of access needs a role that carries both.',
                        'The activity log records what each officer did, so an account can be left active with confidence.',
                    ],
                ],
                [
                    'title'  => 'How to change what a role can do',
                    'steps'  => [
                        'Open Administration → User Roles and open the role.',
                        'Turn the permission switches on or off. The switches are grouped by module and each one names the page it opens.',
                        'Save.',
                    ],
                    'result' => [
                        'Every officer holding the role can open the newly switched-on pages, and loses the switched-off ones, the next time they load a page.',
                    ],
                    'notes'  => [
                        'The Administrator role is the catalogue itself: it holds everything whether or not a switch is written on its row, and the office is never offered a switch for it.',
                        'A role still held by an officer cannot be deleted — move the officers to another role first.',
                        'Switching a permission off does not hide history. The records stay; only the page is closed.',
                    ],
                ],
                [
                    'title'  => 'How to deactivate an officer',
                    'steps'  => [
                        'Open Administration → Users and find the account.',
                        'Switch it to inactive.',
                    ],
                    'result' => [
                        'The officer can no longer sign in, and everything they recorded stays on file.',
                    ],
                    'notes'  => [
                        'The sign-in page rejects a deactivated account however correct the password is.',
                        'An officer who has simply forgotten a password is better served by the reset flow than by a new account.',
                    ],
                ],
            ],
        ],

        'settings' => [
            'title'    => 'Settings',
            'sections' => [
                [
                    'title'  => 'What Settings covers',
                    'body'   => 'Everything the application reads as configuration rather than stores as a record. Saved settings are cached and served fresh everywhere — the public site, the portal, every printed document and every screen that shows the association\'s own name.',
                    'fields' => [
                        'General'     => 'The association\'s name, logo, contact details and other public-facing information.',
                        'Collections' => 'The billing defaults: dues rates, the day dues fall due, grace, reminders and penalties.',
                        'Security'    => 'Login lockout, password policy and expiry.',
                        'Authentication' => 'Whether the office and residents may register, and whether a new account is accepted at once.',
                        'Profile'     => 'Your own account: photo, email address and password.',
                        'Print'       => 'How every printed document is headed and signed.',
                        'Backup'      => 'Take a database backup and download one already taken.',
                    ],
                ],
                [
                    'title'  => 'How to change a setting',
                    'steps'  => [
                        'Open the group from the Settings menu.',
                        'Change the values the page shows.',
                        'Save.',
                    ],
                    'result' => [
                        'A confirmation appears, and the change is already in force on the next page you load — a new letterhead reaches the very next document, a new name the next page render.',
                    ],
                    'notes'  => [
                        'Values that drive the framework — session lifetime, lockout, password rules — are checked where they are written, so an unusable value is refused rather than saved.',
                        'The letterhead is rich text, sanitized on the way in: allowed formatting survives, scripts do not.',
                    ],
                ],
                [
                    'title'  => 'Security settings and what they drive',
                    'body'   => 'The security group is not storage: each value is consumed by a named part of the sign-in flow.',
                    'fields' => [
                        'Password minimum length and character rules' => 'Enforced wherever a new password is accepted: an officer account form, both reset flows, a self-registration and both change-password forms.',
                        'Password expiry'        => 'How long a password may be used before the account is parked on its profile page until it is changed.',
                        'Login attempts and lockout' => 'How many wrong attempts are tolerated and for how long the sign-in is held back.',
                        'Session timeout'        => 'How long an idle session lives, and whether closing the browser ends it.',
                        'Login enabled'          => 'Whether the sign-in is accepted at all — on the panel and on the portal.',
                    ],
                ],
                [
                    'title'  => 'Backups',
                    'steps'  => [
                        'Open Settings → Backup.',
                        'Take a backup and wait for it to finish.',
                        'Download the file when it is offered.',
                    ],
                    'result' => [
                        'The backup appears in the list with its size and the time it was taken, and downloads as a file to keep off the server.',
                    ],
                    'notes'  => [
                        'Take one before a change you are unsure about, and keep a copy off the server — a backup on the same disk is not a backup.',
                    ],
                ],
            ],
        ],

        'by-role' => [
            'title'    => 'Working by Role',
            'sections' => [
                [
                    'title'  => 'How access is granted',
                    'body'   => 'Every page and every action in the panel is a named permission. A role is a bundle of those permissions, and an officer\'s account holds exactly one role. That is the whole authorisation model — there is no hidden second list of who may do what.',
                    'fields' => [
                        'Permission'    => 'One switch: "may add a resident", "may approve an expense". Permissions are grouped by the module they belong to.',
                        'Role'          => 'A named bundle of permissions, granted to officers who do that work.',
                        'Account'       => 'One officer\'s sign-in, holding one role, which may be switched off without losing history.',
                        'Administrator' => 'The role the software runs on. It holds the whole catalogue and is the one role the office cannot strip.',
                        'Visitor'       => 'The other role the software ships with: a limited, read-only starting point for a guest account.',
                    ],
                ],
                [
                    'title'  => 'Who does what',
                    'body'   => 'The roles the association actually uses are its own to define: build one per kind of work and grant only what that work needs. These are the shapes that work well.',
                    'fields' => [
                        'Administrator'              => 'Everything: officer accounts and roles, every setting, every page, every report.',
                        'Finance officer'            => 'The billing and receipt pages, the spending pages, the books, and the finance reports.',
                        'Collection officer'         => 'Billing and receipts alone: raise invoices, run the dues, take payments, issue statements.',
                        'Records / front-desk officer' => 'The homeowners records and the support pages — profiling residents, issuing accounts, filing and deciding requests, recording violations.',
                        'Bookings officer'           => 'Amenities, the bookings and their calendar, and the requests that relate to them.',
                        'Reporting officer'          => 'Reports only, read and export. Useful for a board member who needs the figures and nothing else.',
                        'Auditor / guest'            => 'The Visitor role at its starting point: read-only, and granted more only where the association wants it.',
                    ],
                    'notes'  => [
                        'A role may hold permissions from several areas; nothing requires one role per module.',
                        'Grant the smallest set that lets the officer do the job, and add to it when the work asks.',
                    ],
                ],
                [
                    'title'  => 'What an officer without a permission sees',
                    'body'   => 'Nothing. A menu entry the role cannot use is never drawn, so the sidebar is the honest list of what that officer may open. Typing the address of a closed page by hand does not work either: the panel answers as if the page did not exist rather than showing it empty.',
                    'steps'  => [
                        'Sign in as the officer, or read the role on Administration → Users.',
                        'Compare the sidebar with what the officer says they cannot find.',
                        'If a page is missing, open the role and switch the permission on — that is the fix, not a second account.',
                    ],
                    'result' => [
                        'The officer\'s sidebar lists exactly the pages their role opens, and this manual lists exactly the tools they may read.',
                    ],
                ],
                [
                    'title' => 'The homeowner side is different',
                    'body'  => 'The portal has no roles at all. A homeowner sees their own household and nothing else — their own bills, receipts, bookings, requests and members — because ownership, not permission, decides what they may open. Never look for a role setting to change what a homeowner can see; the answer is always the record\'s own account.',
                ],
            ],
        ],

        'troubleshooting' => [
            'title'    => 'Troubleshooting',
            'sections' => [
                [
                    'title'  => 'Sign-in problems',
                    'fields' => [
                        '"These credentials do not match our records."' => 'Check the email address first — it is the one on the account, not necessarily the one the officer prefers. If the password is lost, use Forgot your password rather than trying another guess.',
                        'The sign-in is refused after several attempts' => 'The account is temporarily throttled. Wait for the window to pass, or ask the Administrator to raise the allowed attempts in Settings → Security.',
                        'The emailed code never arrives' => 'Ask for a new code, then check the email address on the account. Codes are also delayed by the cooldown after repeated asks.',
                        'The panel sends you to your profile again and again' => 'The password has passed the expiry window. Change it on the profile page and the panel opens normally.',
                        '"Your account has been deactivated"' => 'An account switched off cannot sign in however correct the password is. Ask the Administrator to reactivate it.',
                    ],
                ],
                [
                    'title'  => 'A page or a button is missing',
                    'body'   => 'The panel only draws what the role may open. A missing menu entry is a permission, not a fault.',
                    'steps'  => [
                        'Check the role on Administration → Users.',
                        'Open the role and find the switch for the page or the action that is missing.',
                        'Switch it on and save.',
                    ],
                    'result' => [
                        'The menu entry appears at the officer\'s next page load.',
                    ],
                    'notes'  => [
                        'A button inside a page can be missing for the same reason: the action has its own permission — approving is separate from viewing.',
                    ],
                ],
                [
                    'title'  => 'A dialog will not load',
                    'body'   => 'Dialogs pull their record from the server as they open, so a spinner that never finishes means the server refused or could not find the record — usually because the page was left open while the record changed underneath it.',
                    'steps'  => [
                        'Close the dialog and reload the page.',
                        'Open the record again and repeat the action.',
                    ],
                    'result' => [
                        'The dialog opens with the record and the moves that still apply to it.',
                    ],
                    'notes'  => [
                        'A refusal is deliberate: a move offered by a stale page — approving something already approved, paying something already paid — is refused rather than repeated.',
                    ],
                ],
                [
                    'title'  => 'A payment is not counted',
                    'body'   => 'A receipt filed from the portal waits as pending: it settles nothing and does not reach the books until the office approves it on the Payments page. That is why a household can show a filed payment while the bill still stands.',
                    'steps'  => [
                        'Open the payments list and filter for the pending receipt.',
                        'Check its proof and reference, then approve it — or delete it so the household can file again.',
                    ],
                ],
                [
                    'title'  => 'A balance looks wrong',
                    'body'   => 'No balance is stored, so there is nothing to fall out of step. Every figure is recomputed from the receipts and postings behind it, which narrows the question to what was actually recorded.',
                    'fields' => [
                        'A bill still shows a balance though money was handed over' => 'The receipt is either still pending, or was recorded against another bill.',
                        'Income looks lower than the collections' => 'Income is recognised when accounting posts a payment, not when a bill is raised — check the journal for posted payments.',
                        'An expense does not show on the books' => 'An expense posts when accounting posts it, not when it is approved. An unposted expense has no journal entry yet.',
                        'A month looks short on the report' => 'Check whether the month is still open, and whether anything was voided or reversed after the fact.',
                    ],
                ],
                [
                    'title'  => 'A printed document is wrong',
                    'fields' => [
                        'A field is blank' => 'The document is printed from records: a blank line means the record is blank. Complete the resident profile or the settings and print again.',
                        'The letterhead is out of date' => 'Change it in Settings → Print. The very next document uses it.',
                        'A long list stops partway' => 'The document is capped so one PDF stays printable. Split the work by address or by range and print each part.',
                        'The paper size is wrong' => 'Set it in Settings → Print, or use the preview to check the page before printing.',
                    ],
                ],
                [
                    'title'  => 'Where to get help',
                    'body'   => 'Start with the office: the association\'s Administrator holds the account and role settings and can restore access, and the association\'s own contact details are published on the public site. If a record looks wrong rather than missing, the chapter for that module states the rule it follows, and the Glossary defines its terms.',
                    'notes'  => [
                        'Quote the record\'s reference — an invoice number, a booking, a request — when asking for help: it can be searched directly.',
                        'Keep a backup before a large change you are unsure about.',
                    ],
                ],
            ],
        ],

        'faq' => [
            'title'    => 'FAQ and Tips',
            'sections' => [
                [
                    'title'  => 'Common questions',
                    'fields' => [
                        'Can one person hold two roles?' => 'No — an account holds exactly one. Build a role that carries both sets of permissions instead.',
                        'Can I undo a deleted record?' => 'Not by restoring it. Records that may be reversed are cancelled or voided rather than deleted for exactly this reason; anything already collected is reversed by a correcting entry.',
                        'Why can I see a bill but not record a payment against it?' => 'The bill already has a receipt waiting for approval. Approve or reject that one first — the same money is never taken twice.',
                        'Why do I have to approve a payment that a homeowner filed?' => 'The portal is the household\'s own word; the books are the office\'s. Approval is where the office takes responsibility for the money.',
                        'Why is a homeowner\'s document not available to them?' => 'The request must be approved and the invoice raised for it paid, within the window and the download budget the approval set.',
                        'What is the difference between a bill and an invoice?' => 'An invoice is one kind of bill. Monthly dues and paid bookings are bills of other kinds, and all of them live in one ledger.',
                        'Why does closing the browser not sign me out?' => 'The session outlives the tab until it times out. Sign out properly on a shared computer; the timeout itself is set in Settings → Security.',
                        'Can the same property have two households?' => 'No. One household per unit, and one unit per household.',
                        'Why is a menu I can see empty?' => 'The list is filtered, or the records have been cleared — check the toolbar before concluding something is missing.',
                        'How do I know what an officer did?' => 'The activity log records the actions taken on the system, and the books record the money movements themselves.',
                    ],
                ],
                [
                    'title'  => 'Habits that save time',
                    'body'   => 'A short list, worth reading once.',
                    'notes'  => [
                        'Search the sidebar before hunting through menus: it finds any page you can open.',
                        'Tick rows before reaching for a bulk action — the selection bar only appears when something is selected.',
                        'Read the row\'s Actions menu rather than guessing: it lists exactly the moves that record still allows.',
                        'Export the view you have filtered to; the file is exactly the rows on screen.',
                        'Check the report before closing a period — a closed month takes no more postings.',
                        'Print the voucher alongside a posting: it is the record you can hand to an auditor.',
                        'Use the manual\'s own search box (in its sidebar) to jump to the chapter for the page you are on.',
                    ],
                ],
            ],
        ],

        'glossary' => [
            'title'    => 'Glossary',
            'sections' => [
                [
                    'title'  => 'Terms used in this manual',
                    'fields' => [
                        'Account'            => 'An officer\'s sign-in on the panel, or a homeowner\'s login on the portal. The two are separate kinds of account and never mix.',
                        'Advance deposit'    => 'Credit the association holds for a household account and draws down against later charges.',
                        'Amenity'            => 'A shared facility that can be reserved, with a rate, a weekly schedule and an optional deposit.',
                        'Bill'               => 'One charge on the ledger — dues, an invoice or a paid booking. It carries a balance until its receipts cover it.',
                        'Booking'            => 'A reservation of an amenity for a date and a window of hours.',
                        'Chart of accounts'  => 'The list of accounts the books are kept on, each with a type and a code.',
                        'Complaint'          => 'A concern filed by a homeowner, answered by the office.',
                        'Deposit'            => 'Money held against a booking and refunded or forfeited afterwards.',
                        'Dues table'         => 'The month\'s monthly-dues bills, laid out for checking.',
                        'Household'          => 'The people living in one unit: the account\'s household and its members.',
                        'Invoice'            => 'A bill the office raises by hand, as opposed to dues or a booking.',
                        'Journal entry'      => 'One balanced posting on the books — the record of money moving.',
                        'Ledger'             => 'The whole of the journal entries; the books.',
                        'Letterhead'         => 'The heading, footer and signatories every printed document carries. Kept in Settings → Print.',
                        'Monthly dues'       => 'The recurring charge raised per unit for a month.',
                        'Payment'            => 'A receipt against a bill. Taken at the office it is recorded at once; filed from the portal it waits as pending.',
                        'Period close'       => 'Freezing a month so nothing more posts to it.',
                        'Permission'         => 'One switch on a role, naming a page or an action.',
                        'Portal'             => 'The homeowner-facing half of the system, at /portal.',
                        'Product'            => 'A chargeable item an invoice or booking is built from, grouped under a category.',
                        'Property'           => 'A lot or unit in the subdivision, tied to an address.',
                        'Receipt'            => 'The recorded money a payment represents.',
                        'Resident'           => 'A person the office has profiled. A homeowner account is linked to exactly one.',
                        'Role'               => 'A named bundle of permissions, held by an officer account.',
                        'Statement of Account' => 'A household\'s open bills, listed together and sent either as a page or by email.',
                        'Transfer'           => 'Money moved between two accounts without a bill or an expense behind it.',
                        'Violation'          => 'A recorded breach of the association\'s rules, with a printable notice.',
                    ],
                ],
            ],
        ],

    ],
];
