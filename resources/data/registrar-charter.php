<?php

return [
    [
        'id' => 'enrollment',
        'title' => 'Enrollment Procedures',
        'description' => 'This service covers official enrollment for an academic term, including clearance verification, registration forms, assessment and payment of fees, and inclusion in the official muster list of enrolled students.',
        'who' => 'Students',
        'requirements' => [
            'PMMA-REG 010 (Clearance Form), 1 copy',
            'PMMA-REG 004 (Certificate of Registration), 1 copy',
            'PMMA-REG 013 (Registration for Embarkation), 1 copy for incoming 2CL only',
            'PMMA-REG 014A (Certificate of Shipboard, Deck Cadet), 1 copy for incoming 2CL only',
            'PMMA-REG 014B (Certificate for Shipboard, Engine Cadet), 1 copy for incoming 2CL only',
        ],
        'steps' => [
            [
                'client' => [
                    'Obtain and complete PMMA-REG 010 (Clearance Form) and have it signed by the required signatories.',
                    'For first-semester enrollment, incoming 4CL and 1CL cadets and cadettes do not need to process the Clearance Form.',
                ],
                'agency' => ['Issue PMMA-REG 010 to students.', 'Assist students with questions or concerns.'],
                'fee' => 'None', 'time' => '5 minutes', 'staff' => 'Registrar Staff',
            ],
            [
                'client' => ['Submit the accomplished and signed Clearance Form to the Registrar’s Office.'],
                'agency' => [
                    'Verify that all required signatories signed the Clearance Form.',
                    'Issue PMMA-REG 004 (Certificate of Registration).',
                    'For incoming 2CL (third-year) students, issue PMMA-REG 013, PMMA-REG 014A (Deck Cadet), and PMMA-REG 014B (Engine Cadet).',
                ],
                'fee' => 'None', 'time' => '5 minutes', 'staff' => 'Registrar Staff',
            ],
            [
                'client' => ['Complete PMMA-REG 004. Incoming 2CL students also complete PMMA-REG 013 for embarkation, PMMA-REG 014A for deck cadets, and PMMA-REG 014B for engine cadets.'],
                'agency' => ['Assist students with questions or concerns.'],
                'fee' => 'None', 'time' => '5 minutes', 'staff' => 'Registrar Staff',
            ],
            [
                'client' => ['Proceed to the Accounting Unit for assessment of fees.'],
                'agency' => ['Issue the order of payment.'],
                'fee' => 'None', 'time' => '3 minutes', 'staff' => 'Accounting Staff',
            ],
            [
                'client' => ['Proceed to the Cashier Unit and pay the required fees.'],
                'agency' => ['Receive payment and issue the Official Receipt.'],
                'fee' => 'Varies by year level (1C, 2C, 3C, and 4C)', 'time' => '3 minutes', 'staff' => 'Cashier',
            ],
            [
                'client' => ['Proceed to the Accounting Unit for the signature on the Certificate of Payment.'],
                'agency' => ['Sign the Certificate of Payment portion of PMMA-REG 004.'],
                'fee' => 'None', 'time' => '3 minutes', 'staff' => 'Accounting Staff',
            ],
            [
                'client' => ['Submit the Registration Form to the Registrar’s Office and present the Official Receipt issued by the Cashier Unit.'],
                'agency' => [
                    'Verify that the Registration Form is complete.',
                    'Sign the form, retain one copy, and release the other copies to the cadet or cadette.',
                    'Add the cadet or cadette to the official muster list of enrolled students.',
                ],
                'fee' => 'None', 'time' => '5 minutes', 'staff' => 'Registrar Staff / Head',
            ],
        ],
        'total_fee' => 'Varies by year level (1C, 2C, 3C, and 4C)',
        'total_time' => '29 minutes',
    ],
    [
        'id' => 'documentary-requests',
        'title' => 'Documentary Requests and Issuances Procedures',
        'description' => 'Graduates and undergraduate students may request scholastic records, certifications, and other official academic documents. The process covers clearance verification, preparation, payment, and release in accordance with data privacy regulations.',
        'who' => 'Clients (graduates and undergraduates)',
        'requirements' => [
            'PMMA-REG 010 (Clearance Form)',
            'PMMA-REG 011 (Scholastic Records Request Form)',
        ],
        'steps' => [
            [
                'client' => [
                    'Submit the request to the Office of the Registrar. Requests may be sent by email to registrar@pmma.edu.ph.',
                    'Option 1. Visit the Office of the Registrar in person to process the request.',
                    'Option 2. Execute a Special Power of Attorney (SPA) for an authorized representative, who then visits the Academy to process the request.',
                    'Processing and release follow these options under the Data Privacy Act of 2012. Complete your clearance before the requested documents can be released.',
                ],
                'agency' => [
                    'Receive, verify, and validate the request.',
                    'For emailed requests, explain the processing and release options and arrange a release appointment.',
                    'For in-person or authorized representative requests, issue PMMA-REG 011 and direct the requester to the Cashier to pay the required fees.',
                    'If more time is needed, inform the requester and arrange a release appointment.',
                ],
                'fee' => 'None', 'time' => '5 minutes', 'staff' => 'Registrar Staff',
            ],
            [
                'client' => ['Complete PMMA-REG 011 (Scholastic Records Request Form).'],
                'agency' => ['Assist the client with questions or concerns.', 'Prepare the requested document.'],
                'fee' => 'None', 'time' => '30 minutes (may vary by document)', 'staff' => 'Registrar Staff',
            ],
            [
                'client' => ['Proceed to the Cashier Unit and pay the fees based on the order of payment.'],
                'agency' => ['Receive payment and issue the Official Receipt.', 'Sign the Scholastic Records Request Form.'],
                'fee' => 'Varies by document requested', 'time' => '2 minutes', 'staff' => 'Cashier Staff',
            ],
            [
                'client' => ['Submit the signed Scholastic Records Request Form to the Registrar’s Office and present the Official Receipt.'],
                'agency' => ['Receive and verify the signed form.', 'Release the requested documents to the client.'],
                'fee' => 'None', 'time' => '5 minutes', 'staff' => 'Registrar Staff / Head',
            ],
            [
                'client' => ['Receive the requested documents and sign the acknowledgment receipt or receiving copies.'],
                'agency' => ['Log and close the request.', 'Keep the acknowledgment receipt or receiving copies in the requester’s 201 file.'],
                'fee' => 'None', 'time' => '5 minutes', 'staff' => 'Registrar Staff',
            ],
        ],
        'total_fee' => 'Varies by document requested',
        'total_time' => '47 minutes (page 25)',
        'note' => 'Page 26 repeats the end of step 4 and step 5 and gives a total of 37 minutes. Confirm the applicable processing time with the Registrar’s Office.',
    ],
];
