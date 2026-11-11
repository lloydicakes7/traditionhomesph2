<?php

/*
|--------------------------------------------------------------------------
| Printed Documents
|--------------------------------------------------------------------------
|
| The printed wording of the association's paper documents. These strings are
| the form itself: they are copied from the association's own sheets, so a
| label keeps the capitalization, punctuation and order the paper carries.
|
| A document's view never assembles a sentence of its own. It prints these
| segments in order with its fill-in blanks between them.
|
*/

return [

    'errors' => [
        'missing'  => 'This document cannot be printed yet. Fill in: :items.',
        'blank'    => 'Print blank form',
        'title'    => 'This document needs a few details first',
        'hint'     => 'Fill in the form on the left, then print again.',
    ],

    'letterhead' => [
        'registration' => 'HLURB REGISTRATION #',
        'contact'      => 'CONTACT NO.',
        'email'        => 'Email:',
        'tin'          => 'Non-Vat Registration TIN#',
    ],

    'sidebar' => [
        'board' => 'Board of Directors:',
    ],

    'membership' => [
        'title'      => 'MEMBERSHIP FORM',
        'date'       => 'Date',
        'surname'    => 'Surname',
        'first_name' => 'First Name',
        'middle_name' => 'Middle Name',
        'religion'   => 'Religion',
        'sex'        => 'Sex',
        'citizenship' => 'Citizenship',
        'age'        => 'Age',
        'birth_date' => 'Birth date',
        'birth_place' => 'Birthplace',
        'civil_status' => 'Civil Status:',
        'spouse'     => 'if married, Name of Spouse:',
        'address'    => 'Address:',
        'present'    => 'Present:',
        'block'      => 'Block',
        'lot'        => 'Lot',
        'present_tail' => 'Phase 2 Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite',
        'previous'   => 'Previous:',
        'education'  => 'Highest Educational Attainment:',
        'occupation' => 'Occupation:',
        'mobile'     => 'Mobile No. #:',
        'members'    => 'List of Household Member',
        'columns'    => [
            'name'        => 'Name',
            'age'         => 'Age',
            'birth_date'  => 'B-date',
            'birth_place' => 'B-place',
            'relation'    => 'Relation',
            'occupation'  => 'Occupation',
        ],
        'member_types' => [
            'Owner',
            'Renter',
            'Caretaker',
            'Informal Settler',
        ],
        'sex_options' => [
            'Male',
            'Female',
        ],
        'civil_options' => [
            'Single',
            'Married',
            'Separated',
            'Widow',
        ],
        'owner'      => 'Name of owner:',
        'signature'  => "Member's Signature",
        'checked_by' => 'Checked by:',
        'approved_by' => 'Approved by:',
        'note'       => 'NOTE:',
        'fee'        => '1. Membership Fee - Php',
        'dues'       => '2. Monthly Dues - Php',
        'chairwoman' => 'Chairwoman',
        'committee'  => 'Education and Membership Committee',
        'president'  => 'THHA President',
    ],

    'permit' => [
        'title'      => 'HOMEOWNERS ASSOCIATION PERMIT',
        'granted'    => 'Permit is hereby granted',
        'to'         => 'to:',
        'address'    => 'Address:',
        'purpose'    => 'Purpose:',
        'location'   => 'Location:',
        'date_event' => 'Date of event:',
        'date_issued' => 'Date Issued:',
        'paragraph'  => 'In view of the foregoing, this HOA thru the undersigned interpose no objection for the issuance of the corresponding permits being applied for.',
        'note'       => 'Note:',
        'dry_seal'   => 'Not valid without HOA Dry Seal',
        'certified'  => 'Certified by:',
        'role'       => 'THHA-President',
    ],

    'endorsement' => [
        'title'      => 'ENDORSEMENT LETTER',
        'date'       => 'Date:',
        'intro_a'    => 'Please be informed that certain',
        'intro_b'    => 'of legal age and a resident of Block',
        'intro_c'    => 'Lot',
        'intro_d'    => ', Phase 2, Tradition Homes Barangay Inocencio, Trece Martires City, Cavite is hereby given an endorsement to the Barangay to file a case against',
        'intro_e'    => 'with resident address, Block',
        'intro_f'    => ', Lot',
        'intro_g'    => 'Phase 2, Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite.',
        'problem'    => 'The problem is with regards to',
        'attempt'    => 'They have been invited to the Homeowners Office to talk with and they were both given a chance to settle their problems and yet to no avail.',
        'closing'    => 'This endorsement letter will serve the complainant to raise his/her case to your Honorable Office for further amicable settlement.',
        'respectfully' => 'Respectfully yours',
        'role'       => 'THHA-President',
        'choice'     => [
            'Ms.' => 'Ms.',
            'Mr.' => 'Mr.',
        ],
    ],

    'invitation' => [
        'title'      => 'INVITATION LETTER',
        'date'       => 'Date:',
        'to'         => 'To :',
        'address'    => 'Address:',
        'body_a'     => "You are hereby requested to attend a meeting to the Homeowners Association's Office located at Block 20, Lot 06, Phase 2, Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite on",
        'body_b'     => 'at exactly',
        'body_c'     => 'in the',
        'body_d'     => 'with regards to a concern that needs to fix.',
        'appreciated' => 'Your attendance on this concern is highly appreciated.',
        'respectfully' => 'Respectfully yours,',
        'role'       => 'THHA-President',
        'received'   => 'Person who received the letter',
        'date_time'  => 'Date/Time',
        'given'      => 'Person who gave the letter',
    ],

    'certification' => [
        'title'       => 'CERTIFICATION',
        'certify'     => 'This is to certify that Block',
        'lot'         => 'Lot',
        'occupied_by' => 'Tradition Homes Phase 2 Barangay Inocencio, Trece Martires City, Cavite is currently occupied by',
        'salutations' => [
            'MR.',
            'MS.',
            'MRS.',
        ],
        'known'       => 'He/She is known to be a good moral character, a law-abiding citizen and has no derogatory records in this subdivision.',
        'on_request'  => 'This certification is being issued upon the request of the above named for whatever legal purposes it may serve him/her best.',
        'purpose'     => 'PURPOSE:',
        'given'       => 'Given this',
        'day_of'      => 'day of',
        'at'          => 'at Tradition Homes Homeowners Association Office.',
        'note'        => 'Note:',
        'valid'       => '1) Certification is valid for three (3) months from the date issued.',
        'dry_seal'    => '2) Not valid without HOA Dry Seal',
        'conforme'    => 'Conforme:',
        'treasurer_role' => 'THHA-Treasurer',
        'president_role' => 'THHA President',
    ],

    'business_permit' => [
        'title'       => 'BUSINESS PERMIT',
        'applicant'   => 'Name of Applicant:',
        'gender'      => 'Gender:',
        'address'     => 'Address:',
        'block'       => 'Block',
        'lot'         => 'Lot',
        'address_tail' => 'Phase 2 Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite',
        'birth_date'  => 'Date of Birth:',
        'age'         => 'Age:',
        'mobile'      => 'Mobile No.:',
        'purpose'     => 'PURPOSE:',
        'resides'     => 'This is to certify that the above - written name is a resident of Phase 2 Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite. This certifies further that he/she is of good moral character and has no violation of the THHA rules and regulations.',
        'issued'      => 'This certification is being issued this,',
        'day_of'      => 'day of',
        'for_purpose' => 'for such legal purpose it may serve him/her best.',
        'note'        => 'Note:',
        'valid'       => '1) Certification is valid for three (3) months from the date issued.',
        'dry_seal'    => '2) Not valid without HOA Dry Seal',
        'certified'   => 'Certified by:',
        'approved'    => 'Approved by:',
        'treasurer_role' => 'Treasurer',
        'president_role' => 'THHA President',
        'control'     => 'Control #:',
    ],

    'salaysay' => [
        'title'      => 'SINUMPAANG SALAYSAY',
        'case_no'    => 'Case #',
        'case'       => 'Case:',
        'affiant'    => 'AKO SI,',
        'age'        => 'taong gulang',
        'civil'      => [
            'may-asawa',
            'binata',
            'dalaga',
        ],
        'residing'   => ', biyuda at kasalukuyang naninirahan sa',
        'oath'       => 'matapos makapanumpa alinsunod sa ating binagong Saligang Batas, ay nagsasaad ng mga sumusunod:',
        'item_1'     => '1. Na aking inireklamo si',
        'item_1_resides' => 'taong gulang Naninirahan sa',
        'item_2'     => '2. Na, noong ika-',
        'item_2_month' => 'ng',
        'item_2_time' => 'humigit kumulang sa ika-',
        'item_2_part' => 'ng',
        'item_2_story' => 'ay isasalaysay ko ang buong pangyayari gaya ng mga sumusunod:',
        'item_3'     => '3. Na, ang salaysay na ito ay kusang loob kong ginawa at sa abot ng aking nalalaman na walang pumilit o tumakot sa akin.',
        'item_4'     => '4. Bilang patunay sa lahat na nasasaad dito ay nilagda ko ang aking pangalan sa ibaba nito ngayong ika-',
        'signature'  => '(Lagda ng nagsalaysay)',
        'sworn'      => 'SINUMPAAN AT NILAGDAAN',
        'sworn_before' => 'sa harap ko ngayong ika-',
        'sworn_month' => 'ng',
        'sworn_place' => ', dito sa opisina ng Tradition Homes Phase 2 Homeowners Association Inc. Tradition Homes, Inocencio Lungsod ng Trece Martires, Cavite.',
        'day_parts'  => [
            'umaga',
            'gabi',
            'tanghali',
            'hapon',
        ],
        'grievance_1' => 'Grievance and Adjudication',
        'grievance_2' => 'Committee',
        'president_role' => 'Pangulo- THHA',
    ],

];
