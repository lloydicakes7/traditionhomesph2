<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    */

    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many failed login attempts. Please try again later.',
    'inactive' => 'These credentials do not match our records.',
    'confirm_failed' => 'The password you entered is not correct.',

    // ===== Registration =====

    'register' => 'Register',
    'register_closed_title' => 'Registration is closed',
    'register_closed_body' => 'The association is not accepting online registrations at the moment. Please contact the office.',
    'email' => 'Email Address',
    'password' => 'Password',
    'password_confirmation' => 'Confirm Password',
    'password_placeholder' => 'At least 8 characters',
    'password_confirmation_placeholder' => 'Type the password again',

    'password_requirements' => [
        'At least 8 characters',
        'One uppercase letter',
        'One lowercase letter',
        'One number',
        'One special character',
    ],

    // ===== Email verification =====

    'unverified' => 'Your email isn\'t verified yet.',
    'verification_resend_intro' => 'Enter the address you registered with and we will send you a new link.',
    'resend_verification' => 'Resend verification email',
    'return_to_login' => 'Return to login',
    'verification_resent' => 'We sent a new link. Check your inbox and spam folder.',
    'verify_expired_title' => 'This link has expired',
    'verify_expired_body' => 'Verification links are short-lived. Ask for a new one and open it soon after it arrives.',
    'back_to_sign_in' => 'Back to sign in',
    'verified_sign_in' => 'Your email address is verified. Sign in with the password you chose.',
    'registered_verify_body' => 'We emailed a verification link to your :email. Open it to verify your account. Check your spam if you can\'t find it anywhere.',

    // ===== Address step =====

    'address' => 'Address',
    'address_primary' => 'Association address',
    'address_primary_missing' => 'The association address is not on file. Please contact the office.',
    'address_new_notice' => 'We have no record of this unit. Give us its size and type; the unit is created together with your registration and checked by the office.',
    'address_occupied' => 'Someone is already registered at this address. Do you live in this household and want to link your registration to it?',
    'address_head' => 'Household head: :name',
    'address_link' => 'I live in this household',
    'address_link_hint' => 'Link my registration to it.',
    'address_link_required' => 'Tick the confirmation to link your registration to the household at this address.',
    'address_free' => 'No household is registered at this address yet. You will be recorded as its household head.',
    'block' => 'Block',
    'block_placeholder' => 'e.g., 7',
    'lot' => 'Lot',
    'lot_placeholder' => 'e.g., 12',
    'sqm' => 'Floor area (sqm)',
    'sqm_placeholder' => 'e.g., 120',
    'property_type' => 'Property type',
    'property_type_required' => 'Tell us the type of this unit so it can be recorded.',
    'property_sqm_required' => 'Tell us the size of this unit so it can be recorded.',
    'household' => 'Household',
    'household_type' => 'Tenure',
    'household_type_required' => 'Tell us how the unit is held so the household can be recorded.',
    'household_composition' => 'Household composition',
    'household_position' => 'Your position in the family',
    'household_prev_address' => 'Previous address',
    'review_linked' => 'Linked to the household already registered at this address.',
    'review_new_unit' => 'This unit is new; it is created together with your registration.',
    'review_head' => 'You will be recorded as the household head.',

    'pending_title' => 'Your registration is pending approval',
    'pending_body' => 'Your account has not been opened yet. The office reviews every registration; you will be able to sign in once it is approved.',
    'pending_sign_in' => 'Try signing in',

    // ===== Onboarding =====

    'onboarding' => [
        'under_review'        => 'Your information is now being reviewed by the office. Every portal service opens once your account is verified.',
        'verified'            => 'Your profile is verified. Every portal service is open to you.',
        'rejected'            => 'The office asked for a correction.',
        'failed'              => 'We could not save your profile. Please try again, or contact the office.',
        'id_required'         => 'Upload one valid ID so the office can check it.',
        'choose_id'           => 'Choose a JPG or PNG image',
        'id_on_file'          => 'Your ID is already on file. Upload a new one only if you want to replace it.',
        'available_title'     => 'What you can use now',
        'locked_title'        => 'What requires completion',
        'available'           => [
            'Dashboard',
            'Statement of Account',
            'My Payments',
            'Account Details',
            'Notifications',
            'User Manual',
        ],
        'locked'              => [
            'Amenity Bookings',
            'Document Requests',
            'Complaints',
            'Violations',
        ],
        'gate_notice'         => 'Complete your profile first. The office verifies it before this page opens.',
    ],

    // ===== Set password =====

    'set_password_title' => 'Choose your password',
    'set_password_intro' => 'The office has opened your homeowner account. Kindly choose the password you will use to sign in.',
    'set_password_subject' => 'Choose your Tradition Homes password',
    'set_password_greeting' => 'Greetings, :name,',
    'set_password_action' => 'Set your password',
    'set_password_expiry' => 'This link expires shortly. If it has expired, ask the office to send a new one.',
    'set_password_ignore' => 'If you did not request this, you may disregard this message.',
    'set_password_submit' => 'Save password',
    'set_password_saved' => 'Your password has been saved. You can now sign in.',

    // ===== Officer welcome =====

    'officer_welcome_subject' => 'Your Tradition Homes officer account is ready',
    'officer_welcome_greeting' => 'Greetings, :name,',
    'officer_welcome_intro' => 'The office has opened an officer account for you in the association information system. Kindly choose the password you will use to sign in.',
    'officer_welcome_action' => 'Choose your password',

    'office_register_title' => 'Office Registration',
    'office_register_intro' => 'Create an officer account. An Administrator reviews every registration before the account is opened.',

    // ===== Consent =====

    'consent' => [
        'title' => 'Terms, Certification and Data Privacy Consent',
        'accept_prefix' => 'I accept the',
        'accept_link' => 'Terms, Certification and Data Privacy Consent',
        'close' => 'Close',
        'paragraphs' => [
            'I hereby certify that all information and proof of residency provided in this registration form are true, accurate, and complete to the best of my knowledge. I agree to abide by the Terms and Conditions of the Tradition Homes Phase 2 Homeowners Association, Inc. (HLURB Reg. No. 02104) Management Information System, including maintaining the confidentiality of my account credentials and recognizing that official Association physical receipts and ledgers shall prevail in case of any billing discrepancy.',
            'Furthermore, I understand that for Tradition Homes Phase 2 Homeowners Association, Inc. to fulfill its administrative functions pursuant to Republic Act No. 9904 (Magna Carta for Homeowners and Homeowners\' Associations) and its Bylaws, the Association must collect and process my personal and household information for resident verification, HOA dues billing and payment tracking, official announcements, and resolution of community requests and concerns. Therefore, I freely give my consent and authorize the Association to collect, store, and process my personal data strictly for these purposes, in accordance with Republic Act No. 10173 (Data Privacy Act of 2012).',
        ],
    ],

];
