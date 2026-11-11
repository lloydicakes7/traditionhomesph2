<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    */

    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'inactive' => 'These credentials do not match our records.',
    'confirm_failed' => 'The password you entered is not correct.',

    // ===== Registration =====

    'register' => 'Register',
    'register_closed_title' => 'Registration is closed',
    'register_closed_body' => 'The association is not accepting online registrations at the moment. Please contact the office.',
    'email' => 'Email Address',
    'password' => 'Password',
    'password_confirmation' => 'Confirm Password',
    'password_placeholder' => 'At least 6 characters',
    'password_confirmation_placeholder' => 'Type the password again',

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

    'registered_received' => 'Thank you. Your registration has been received.',
    'registered_pending' => 'We emailed a receipt to :email. Your account is checked by the office before it is opened.',
    'registered_active' => 'We emailed a receipt to :email. Your account is active — sign in with the password you chose.',

    'pending_title' => 'Your registration is pending approval',
    'pending_body' => 'Your account has not been opened yet. The office reviews every registration; you will be able to sign in once it is approved.',
    'pending_sign_in' => 'Try signing in',

    'registration_received_subject' => 'Your Tradition Homes registration was received',
    'registration_received_heading' => 'Registration received',
    'registration_received_greeting' => 'Hello :name,',
    'registration_received_body' => 'Thank you for registering with the Tradition Homes Phase 2 Homeowners Association, Inc. Your application is now with the office.',
    'registration_received_email' => 'Registered email address:',
    'registration_received_active' => 'Your account is already active. Sign in with the password you chose when you registered.',
    'registration_received_pending' => 'Your account is checked by the office before it is opened. You will be able to sign in once it is approved.',
    'registration_received_ignore' => 'If you did not file this registration, please tell the office.',
    'registration_received_regards' => 'Best Regards,',

    // ===== Set password =====

    'set_password_title' => 'Choose your password',
    'set_password_intro' => 'The office opened your homeowner account. Choose the password you will sign in with.',
    'set_password_subject' => 'Choose your Tradition Homes password',
    'set_password_mail_intro' => 'The office opened your homeowner account. Choose the password you will sign in with.',
    'set_password_action' => 'Set your password',
    'set_password_expiry' => 'This link expires shortly. If it has expired, ask the office to send a new one.',
    'set_password_ignore' => 'If you did not expect this, you can ignore this message.',
    'set_password_submit' => 'Save password',
    'set_password_saved' => 'Your password has been saved. You can now sign in.',

    // ===== Officer welcome =====

    'officer_welcome_subject' => 'Your Tradition Homes officer account is ready',
    'officer_welcome_greeting' => 'Hello :name,',
    'officer_welcome_intro' => 'The office has opened an officer account for you on the Tradition Homes information system. Choose the password you will sign in with.',
    'officer_welcome_action' => 'Choose your password',

    'office_register_title' => 'Office Registration',
    'office_register_intro' => 'Create an officer account. An Administrator reviews every registration before the account is opened.',

    // ===== Terms and conditions =====

    'terms' => [
        'title' => 'Terms and Conditions',
        'accept_prefix' => 'I accept the',
        'accept_link' => 'Terms and Conditions',
        'close' => 'Close',
        'sections' => [
            [
                'heading' => 'Acceptance',
                'body' => 'By registering, you agree to these Terms and to the By-Laws and rules of Tradition Homes Phase 2 Homeowners Association, Inc. If you do not agree, do not submit a registration.',
            ],
            [
                'heading' => 'Your account',
                'body' => 'One account belongs to one household head. Keep your password secret, do not let anyone else use it, and tell the office at once if you believe someone else has access.',
            ],
            [
                'heading' => 'Accurate information',
                'body' => 'You confirm that the information and the identification document you submit are true and correct, and you agree to keep them current. Tell the office promptly when your contact details, household members or unit change.',
            ],
            [
                'heading' => 'Association rules and dues',
                'body' => 'Homeowners and household members are bound by the association\'s By-Laws and duly approved rules, including dues, fees and charges and the rules on amenities, violations and requests. Any penalty or sanction follows the By-Laws and Republic Act No. 9904, with prior notice and an opportunity to be heard. Nothing in these Terms limits any right the law or the By-Laws give you.',
            ],
            [
                'heading' => 'Review of registrations',
                'body' => 'The office reviews every registration and may ask for further proof of identity, ownership or residency. A registration that is false, duplicate or made for another person without authority is refused, and an account obtained that way may be suspended or closed after notice to you.',
            ],
            [
                'heading' => 'Changes to these terms',
                'body' => 'The association may update these terms. The version in force when you register is the one you accepted. Material changes will be announced on this website or sent to your contact details, and they never reduce your rights under the law or the By-Laws.',
            ],
        ],
    ],

    // ===== Data privacy consent =====

    'privacy' => [
        'title' => 'Data Privacy Consent',
        'accept_prefix' => 'I consent to the',
        'accept_link' => 'Data Privacy Consent',
        'close' => 'Close',
        'sections' => [
            [
                'heading' => 'Legal basis',
                'body' => 'Tradition Homes Phase 2 Homeowners Association, Inc. processes your personal information under the Data Privacy Act of 2012 (Republic Act No. 10173) and its Implementing Rules and Regulations. Our lawful bases are your consent, your membership and residency relationship with the association under its By-Laws and Republic Act No. 9904 (Magna Carta for Homeowners and Homeowners\' Associations), and the association\'s legitimate interest in keeping accurate and secure records of its residents and households.',
            ],
            [
                'heading' => 'Purpose of collection',
                'body' => 'The association collects your information to register your household, confirm that you live in the unit you claim, open and maintain the unit your household occupies, issue your portal account, keep the association\'s records of residents and households, assess and collect dues, and contact you about association business.',
            ],
            [
                'heading' => 'Data collected',
                'body' => 'We collect your name, birth date and place, sex, civil status, spouse\'s name, contact number, email address, citizenship, religion, occupation, educational attainment, the unit and household you belong to, and one valid identification document. Some of these fields are optional and are marked as such on the form.',
            ],
            [
                'heading' => 'Sensitive personal information',
                'body' => 'Your birth date, civil status, religion (if you give it) and the details of your identification document are sensitive personal information under the Data Privacy Act. By ticking the box you give your explicit consent to the association processing them for the purposes above, and only for those purposes.',
            ],
            [
                'heading' => 'Use and sharing',
                'body' => 'Your information is used only for the purposes above. It is shared with the association\'s officers and staff who need it to do their work, with service providers that support this system under agreements to protect your data, and with government agencies or courts only when the law requires it. It is never sold, and it is not shared with other homeowners except where the law or the By-Laws require.',
            ],
            [
                'heading' => 'Storage, security and retention',
                'body' => 'Your records are stored on the association\'s system with access limited to authorized officers and staff. They are kept for as long as you are a resident or household member, and afterward for the period the association needs to meet its legal, accounting and records-keeping duties. Records no longer needed are securely disposed of. If a data breach affects you, the association will notify you and the National Privacy Commission as the law requires.',
            ],
            [
                'heading' => 'ID handling',
                'body' => 'The identification document you submit is stored privately. Only officers authorized to review registrations can open it, and it is used only to confirm your identity. Only the 16-digit PhilSys Card Number is recorded where a PhilSys number is required; the Serial Number is never recorded.',
            ],
            [
                'heading' => 'Your rights',
                'body' => 'You have the right to be informed, to object, to access, to correct, to erasure or blocking, to damages, to file a complaint, and to data portability, as provided by the Data Privacy Act. You may ask the office to see the information it holds about you and to correct anything that is wrong. If you believe your rights were violated, you may file a complaint with the National Privacy Commission (privacy.gov.ph).',
            ],
            [
                'heading' => 'Withdrawing consent',
                'body' => 'You may withdraw your consent at any time by writing to the Data Protection Officer. Withdrawal does not affect processing already done, and the association may still keep information the law or the By-Laws require it to keep. If you withdraw consent to information needed to maintain your registration, your portal account may no longer be kept active.',
            ],
            [
                'heading' => 'Data protection officer',
                'body' => 'For questions about your information, or to exercise any of your rights, contact the association\'s Data Protection Officer through the office contact details published on this website.',
            ],
        ],
    ],

];
