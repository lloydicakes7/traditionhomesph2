<?php

/*
|--------------------------------------------------------------------------
| User Manual — Homeowner Portal
|--------------------------------------------------------------------------
|
| The portal's copy, keyed by the topics the catalogue in `config/manual.php`
| names under `portal`. The portal is ownership-based, so every topic is
| offered to any signed-in homeowner and none declares abilities. The shape
| of a topic is the same as the admin side: a `title` and its `sections`.
|
*/

return [

    'title' => 'User Manual',
    'intro' => 'How to use your homeowner portal, page by page.',

    'groups' => [
        'getting_started' => 'GETTING STARTED',
        'services'        => 'SERVICES',
        'finance'         => 'FINANCE',
        'my_account'      => 'MY ACCOUNT',
        'help'            => 'HELP',
    ],

    'topics' => [

        'introduction' => [
            'title'    => 'Introduction',
            'sections' => [
                [
                    'title' => 'What the portal is for',
                    'body'  => 'The portal is your household\'s side of the association\'s records. You read what the office has on file, settle your bills, reserve the amenities, and file and follow up on anything you ask the association for — without visiting or phoning the office.',
                ],
                [
                    'title' => 'Who it is for',
                    'body'  => 'Every household has one portal account, issued by the office to a resident of the unit and linked to that resident\'s record. You see your own household and nothing else: your bills, your receipts, your bookings and your filings.',
                ],
                [
                    'title'  => 'How the portal is organised',
                    'fields' => [
                        'Sidebar'      => 'Every page you have, grouped by area: services, finance and your own account. The search box at its top finds a page by name.',
                        'Navbar'       => 'The bar across the top: the bell counts your notifications, and your name opens your profile and the sign-out.',
                        'Dashboard'    => 'Where signing in lands you: your counts, your filings and your year at a glance.',
                        'Content area' => 'The page you opened, headed by its title.',
                    ],
                ],
                [
                    'title'  => 'Key terms',
                    'fields' => [
                        'Statement of Account' => 'The list of bills your household still has to settle.',
                        'Payment'              => 'A receipt you filed against a bill. It counts once the office approves it.',
                        'Booking'              => 'A reservation you filed for an amenity on a date and a time.',
                        'Request'              => 'Your application for a document or a service from the office.',
                        'Resident'             => 'The person the office has profiled — you. Your account is linked to that record.',
                    ],
                    'notes'  => [
                        'The office\'s own manual defines more terms; ask the office if something on a page is unclear.',
                    ],
                ],
            ],
        ],

        'getting-started' => [
            'title'    => 'Getting Started',
            'sections' => [
                [
                    'title'  => 'Opening the portal',
                    'steps'  => [
                        'Type the association\'s web address into your browser and add /portal, then press Enter.',
                        'If you are not signed in, the sign-in page opens.',
                    ],
                    'result' => [
                        'The sign-in page appears, asking for your email address and password.',
                    ],
                    'notes'  => [
                        'The office gives you the address; it is the same site as the association\'s public page.',
                    ],
                ],
                [
                    'title'         => 'How to sign in',
                    'prerequisites' => [
                        'The account the office issued to your household.',
                    ],
                    'steps'  => [
                        'Open the sign-in page.',
                        'Enter the email address and password on your account.',
                        'Select Sign in.',
                    ],
                    'result' => [
                        'Your dashboard opens, headed by your name in the navbar.',
                    ],
                    'notes'  => [
                        'There is no sign-up form: accounts are issued by the office, one per household.',
                        'If too many attempts fail in a row, the sign-in is held back for a short while; wait and try again.',
                    ],
                ],
                [
                    'title'  => 'Forgotten passwords',
                    'steps'  => [
                        'Select Forgot your password? on the sign-in page.',
                        'Enter the email address on your account and submit.',
                        'Open the email and follow the reset link.',
                        'Choose a new password that meets the rules shown, and confirm it.',
                    ],
                    'notes'  => [
                        'The link expires; ask for a fresh one if it has.',
                        'If the reset email never arrives, the office can check which address your account carries.',
                    ],
                ],
                [
                    'title'  => 'A quick tour of your screen',
                    'fields' => [
                        'Dashboard'            => 'Your counts, your filings by status, and your bookings and payments for the year.',
                        'My Bookings'          => 'The amenities you have reserved and where each reservation stands.',
                        'Requests'             => 'What you have asked the office for, and what came of it.',
                        'Complaints'           => 'Concerns you have raised, with the office\'s answer.',
                        'Violations'           => 'Any rule breaches recorded against your household.',
                        'Statement of Account' => 'The bills you still have to settle, and how to pay them.',
                        'My Payments'          => 'The receipts you have filed and where each one stands.',
                        'Profile'              => 'Your photo, your email address and your password.',
                        'Personal Information' => 'The resident details the office keeps on file for you.',
                        'Household'            => 'Your unit and the people living in it.',
                    ],
                ],
                [
                    'title' => 'What you change and what the office changes',
                    'body'  => 'You own your own details: your photo, your password, your contact information and your household members. The office owns the records that decide money and rules — bills, receipts, bookings, requests and violations. You can file and follow up on all of those, but only the office approves, prices and settles them.',
                    'notes' => [
                        'Nothing you file is money until the office approves it: a payment you submit waits, and the bill stays standing until it is approved.',
                    ],
                ],
                [
                    'title' => 'Signing out',
                    'body'  => 'Open your name at the right of the navbar and choose Sign out. On a shared computer, close the browser as well.',
                ],
            ],
        ],

        'dashboard' => [
            'title'    => 'Dashboard',
            'sections' => [
                [
                    'title'  => 'Your counts',
                    'body'   => 'The four cards at the top count what your household has filed and reserved. Each card links to the page behind the figure.',
                    'fields' => [
                        'Requests'   => 'What you have asked the office for.',
                        'Complaints' => 'Concerns you have raised.',
                        'Violations' => 'Rule breaches recorded against your household.',
                        'Bookings'   => 'Amenity reservations you have made.',
                    ],
                ],
                [
                    'title'  => 'My filings',
                    'body'   => 'A chart of your filings by status and a short list of your most recent ones, so you can see at a glance whether anything is still waiting on the office.',
                    'steps'  => [
                        'Open a filing from the list to read it in full.',
                        'Use the card\'s link to see everything you have filed.',
                    ],
                ],
                [
                    'title' => 'Year at a glance',
                    'body'  => 'Your bookings and your payments for the year, side by side. A receipt still waiting on the office is included; the My Payments page says where each one stands.',
                ],
            ],
        ],

        'bookings' => [
            'title'    => 'My Bookings',
            'sections' => [
                [
                    'title'         => 'How to book an amenity',
                    'prerequisites' => [
                        'The amenity must be offered to homeowners — the office decides that.',
                    ],
                    'steps'  => [
                        'Open My Bookings and choose Request a booking.',
                        'Choose the amenity you want, then the date and the hours.',
                        'Check the fee and any deposit the form shows, and submit.',
                    ],
                    'result' => [
                        'The booking appears in your list as Pending.',
                        'The office sees the same reservation and confirms it before the slot is yours.',
                    ],
                    'notes'  => [
                        'A free amenity never asks for payment. A booking with a fee raises an invoice on your Statement of Account once the office approves it.',
                        'Check the amenity\'s schedule before choosing hours: a day the facility is closed cannot be booked.',
                    ],
                ],
                [
                    'title'  => 'How to withdraw a booking',
                    'body'   => 'You can withdraw a booking while the office has not acted on it yet. Once it is confirmed, only the office can change it.',
                    'steps'  => [
                        'Open My Bookings and find the reservation.',
                        'Choose Withdraw and confirm.',
                    ],
                    'result' => [
                        'The booking reads as cancelled and the slot is free again.',
                    ],
                ],
                [
                    'title'  => 'Reading the status',
                    'fields' => [
                        'Pending'   => 'The office has not acted on it yet. You can still withdraw it.',
                        'Approved'  => 'The slot is yours. Download the transaction slip and bring it to the office to pay.',
                        'Completed' => 'The reservation was used.',
                        'Cancelled' => 'It no longer stands.',
                    ],
                ],
            ],
        ],

        'requests' => [
            'title'    => 'Requests',
            'sections' => [
                [
                    'title'  => 'How to file a request',
                    'body'   => 'Ask the office for one of the association\'s documents or services. The form asks only for what the document you chose needs.',
                    'steps'  => [
                        'Open Requests and choose File a request.',
                        'Choose what you are asking for.',
                        'Complete the fields that appear — some requests ask for nothing beyond the record the office already holds.',
                        'Submit it.',
                    ],
                    'result' => [
                        'The request appears in your list as Pending, and the office sees it in the same state.',
                    ],
                    'notes'  => [
                        'Forms built entirely from your profile ask for nothing: keep your Personal Information current so they print correctly.',
                        'You can read a request\'s status at any time from this page.',
                    ],
                ],
                [
                    'title'  => 'Tracking and your document',
                    'fields' => [
                        'Pending'      => 'The office has not decided yet. You can still delete it.',
                        'Approved'     => 'The office accepted it. Download the transaction slip and bring it to the office.',
                        'Completed'    => 'The document has been released and the request is closed.',
                        'Cancelled'    => 'The office did not grant it, and says why.',
                        'Bill waiting' => 'An invoice for the request is still unpaid.',
                        'Download'     => 'The transaction slip is available once the request is approved.',
                    ],
                    'notes'  => [
                        'If the download is withheld, the card says which of the conditions is unmet.',
                    ],
                ],
            ],
        ],

        'complaints' => [
            'title'    => 'Complaints',
            'sections' => [
                [
                    'title'  => 'How to file a complaint',
                    'steps'  => [
                        'Open Complaints and choose File a complaint.',
                        'Choose the category that fits and describe what happened.',
                        'Submit it.',
                    ],
                    'result' => [
                        'The complaint appears in your list as filed, with its category.',
                    ],
                ],
                [
                    'title' => 'Following it up',
                    'body'  => 'Every complaint you file is listed with its status and any remarks the office wrote. Open one to read the office\'s answer; nothing else is needed from you.',
                ],
            ],
        ],

        'violations' => [
            'title'    => 'Violations',
            'sections' => [
                [
                    'title' => 'Reading a violation',
                    'body'  => 'A violation is a rule breach the office recorded against your household. Each entry shows what it was, when it happened and any remarks. Open one to read it in full.',
                ],
                [
                    'title' => 'Acknowledging',
                    'body'  => 'Acknowledge a violation once you have read it. Acknowledging is a receipt, not an admission: it tells the office you have seen the notice.',
                ],
            ],
        ],

        'statement-of-account' => [
            'title'    => 'Statement of Account',
            'sections' => [
                [
                    'title'  => 'Your open bills',
                    'body'   => 'The statement lists the bills your household still has to settle — dues and one-off charges together — with their amounts and due dates.',
                    'fields' => [
                        'Reference' => 'The bill\'s own number. Quote it when asking the office about it.',
                        'Type'      => 'Where the charge came from: monthly dues, an invoice, or a paid booking.',
                        'Amount'    => 'What is still owed on it.',
                        'Due date'  => 'The day it falls due. Dues carry the day the office configured.',
                        'Balance'   => 'What remains after every receipt already recorded against the bill.',
                    ],
                ],
                [
                    'title'         => 'How to pay a bill',
                    'prerequisites' => [
                        'The proof of payment — the deposit slip, the transfer receipt or the counter slip.',
                    ],
                    'steps'  => [
                        'Open Statement of Account and find the bill you are settling.',
                        'Choose Pay beside it.',
                        'Check the amount, then attach your proof of payment and give the reference it carries.',
                        'Submit.',
                    ],
                    'result' => [
                        'The bill stops offering Pay and shows a waiting notice instead.',
                        'The payment appears on My Payments as Pending until the office approves it.',
                    ],
                    'notes'  => [
                        'A bill already waiting on one of your payments never offers Pay again, so the same bill is never paid twice.',
                        'Submitting changes nothing yet: the bill settles when the office approves the receipt.',
                    ],
                ],
                [
                    'title' => 'When the office records it',
                    'body'  => 'The office checks your proof against the amount and approves the receipt. Only then is the bill settled and the payment counted. If something is wrong with what you filed, the office rejects it and the bill offers Pay again so you can file it correctly.',
                ],
            ],
        ],

        'payments' => [
            'title'    => 'My Payments',
            'sections' => [
                [
                    'title' => 'Reading your receipts',
                    'body'  => 'Every payment you have filed, with the amount, the reference you gave and its status. Each row points at the bill it settles.',
                ],
                [
                    'title'  => 'Statuses',
                    'fields' => [
                        'Pending'  => 'The office has not approved it yet. The bill is not settled in the meantime.',
                        'Recorded' => 'The office approved it. It counts against the bill and appears on the association\'s books.',
                    ],
                    'notes'  => [
                        'A payment recorded by the office on your behalf appears here too, so the page is the full history of your household\'s receipts.',
                    ],
                ],
            ],
        ],

        'profile' => [
            'title'    => 'Profile',
            'sections' => [
                [
                    'title'  => 'Photo and email',
                    'steps'  => [
                        'Open Profile.',
                        'Choose a photo, or change the email address the office writes to.',
                        'Save.',
                    ],
                    'result' => [
                        'The photo appears beside your name in the navbar and on any page that shows your household.',
                    ],
                    'notes'  => [
                        'Use an email address you actually read: password resets and office notices go there.',
                    ],
                ],
                [
                    'title'  => 'Password',
                    'steps'  => [
                        'Open Profile.',
                        'Enter your current password, then the new one twice.',
                        'Save.',
                    ],
                    'notes'  => [
                        'The rules the new password must meet are printed on the form. If the office expires passwords, changing yours on time keeps you signed in.',
                    ],
                ],
            ],
        ],

        'personal-information' => [
            'title'    => 'Personal Information',
            'sections' => [
                [
                    'title'  => 'Keeping it current',
                    'body'   => 'The office keeps your resident record — your name, your contact details and the rest of your profile. Review it here and correct anything that has changed.',
                    'steps'  => [
                        'Open Personal Information.',
                        'Update the fields that have changed.',
                        'Save.',
                    ],
                    'result' => [
                        'The record is updated at once, and the office reads the same values.',
                    ],
                    'notes'  => [
                        'The office issues documents and notices from this record: a blank field prints blank, so complete what you can.',
                        'Some details are the office\'s to record rather than yours; ask them if something looks wrong and cannot be edited here.',
                    ],
                ],
            ],
        ],

        'household' => [
            'title'    => 'Household',
            'sections' => [
                [
                    'title'  => 'Your unit and members',
                    'body'   => 'This page shows the property your household occupies and the members the office has on file for it.',
                    'fields' => [
                        'Unit'    => 'The lot or unit your household occupies, as the office records it.',
                        'Members' => 'The people living in the unit, each as a person with their own details.',
                    ],
                ],
                [
                    'title'  => 'How to manage members',
                    'steps'  => [
                        'Open Household and choose Manage members.',
                        'Add a member by entering the person\'s details.',
                        'Remove a member who has moved out, and confirm the removal when asked.',
                    ],
                    'result' => [
                        'The member list updates at once, and the office reads the same household.',
                    ],
                    'notes'  => [
                        'A removal asks for confirmation and takes effect only then.',
                        'Your unit and your account are the office\'s records: if the property shown is wrong, ask them to correct it rather than adding a household of your own.',
                    ],
                ],
            ],
        ],

        'troubleshooting' => [
            'title'    => 'Troubleshooting',
            'sections' => [
                [
                    'title'  => 'Signing in',
                    'fields' => [
                        'The sign-in rejects my password'        => 'Check the email address first, then use Forgot your password. After several wrong attempts the sign-in is held back for a short while.',
                        'I never received the reset email'       => 'Check your spam folder, then ask the office to confirm which address your account carries.',
                        'The portal says my account is not active' => 'The office has switched the account off. Ask them to reactivate it.',
                        'There is no sign-up form'               => 'Accounts are issued by the office, one per household. Ask them to issue one.',
                    ],
                ],
                [
                    'title'  => 'Bills and payments',
                    'fields' => [
                        'My payment is still pending' => 'The office has not approved it yet. The bill stays standing until they do.',
                        'The bill still shows a balance after I paid' => 'Your receipt is waiting for approval, or it was filed against a different bill.',
                        'The Pay button is missing on a bill' => 'A payment of yours is already waiting on that bill. Wait for the office to act on it.',
                        'I attached the wrong proof' => 'Ask the office to reject the payment; the bill offers Pay again once they have.',
                    ],
                ],
                [
                    'title'  => 'Bookings and documents',
                    'fields' => [
                        'My booking is still pending'   => 'The office confirms reservations. Nothing is booked until they do.',
                        'I cannot withdraw a booking'   => 'It has already been confirmed. Ask the office to change it.',
                        'My document will not download' => 'The request must be approved and the invoice raised for it paid, within the download window. The card says which condition is unmet.',
                        'A request was cancelled'       => 'The office recorded a reason. Open the request to read it, and file again if the reason no longer applies.',
                    ],
                ],
                [
                    'title'  => 'Pages and details',
                    'fields' => [
                        'A page I expected is missing'     => 'Every page your account has is in the sidebar. If something is missing, ask the office — the portal has no settings to change.',
                        'My household members are wrong'   => 'Open Household and manage your members. The unit itself is the office\'s record.',
                        'My photo or email is out of date' => 'Change it on Profile.',
                        'A figure looks wrong'             => 'Ask the office, quoting the reference on the record — a bill number, a booking or a request. The office reads the same record you do.',
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
                        'How do I know what I owe?' => 'The Statement of Account lists every bill your household still has to settle, dues and charges together.',
                        'Can I pay in cash?' => 'Yes — hand it to the office and they record the receipt, which then appears on My Payments.',
                        'Why does my payment need approval?' => 'The portal is your word; the books are the office\'s. Approval is where the office takes responsibility for the money.',
                        'Can I book for someone outside the household?' => 'Reservations belong to the household\'s account. Ask the office about anything out of the ordinary.',
                        'Can my household have more than one login?' => 'One account per household, linked to one resident. A second resident of the unit is added as a member instead.',
                        'How long does a request take?' => 'The office decides it. The request stays Pending until they do, and you can check its status at any time.',
                        'Where do I see notices?' => 'The bell in the navbar counts them, and the association\'s announcements appear on the public site.',
                        'Can I delete something I filed?' => 'Withdraw a pending booking, and ask the office about anything else. What the office has recorded is theirs to change.',
                    ],
                ],
                [
                    'title'  => 'Habits that save time',
                    'notes'  => [
                        'Search the sidebar when you cannot remember where a page lives.',
                        'Keep your email address on Profile current — that is where the office writes to you.',
                        'Attach a clear photo of your proof of payment and copy its reference exactly; it is approved faster.',
                        'Check My Payments after the office tells you the receipt was approved, to confirm it was recorded.',
                        'Keep your household members up to date, since the office reads them for its records.',
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
                        'Acknowledge'          => 'Mark a violation as read. It is a receipt, not an admission.',
                        'Amenity'              => 'A shared facility you can reserve, such as a clubhouse or a court.',
                        'Bill'                 => 'One charge you have to settle, with its own reference.',
                        'Booking'              => 'A reservation of an amenity for a date and a window of hours.',
                        'Complaint'            => 'A concern you raise with the office.',
                        'Deposit'              => 'Money held against a booking and refunded or forfeited afterwards.',
                        'Dues'                 => 'The recurring charge billed to every household.',
                        'Household'            => 'The people living in your unit.',
                        'Invoice'              => 'A bill the office raised by hand for a charge of its own.',
                        'Member'               => 'A person living in your unit, recorded under Household.',
                        'Pending'              => 'Filed, and waiting on the office to act.',
                        'Personal information' => 'The resident details the office keeps for you, which documents are printed from.',
                        'Portal'               => 'This site, at /portal — your household\'s side of the association\'s records.',
                        'Receipt'              => 'The recorded money a payment represents.',
                        'Request'              => 'Your application for a document or a service from the office.',
                        'Resident'             => 'The person the office has profiled; your account is linked to that record.',
                        'Statement of Account' => 'The list of bills your household still has to settle.',
                        'Violation'            => 'A recorded breach of the association\'s rules.',
                    ],
                ],
            ],
        ],

    ],
];
