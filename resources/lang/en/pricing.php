<?php

return [
    'title' => 'Simple, Transparent Pricing',
    'subtitle' => 'Choose the plan that works best for you',

    'plans' => [
        'basic' => [
            'title' => 'Basic: Up to $25/hr',
            'features' => [
                'One-on-one tutoring',
                'Basic subject coverage',
                'Email support'
            ]
        ],
        'standard' => [
            'title' => 'Standard: $25-$40/hr',
            'features' => [
                'One-on-one tutoring',
                'All subjects covered',
                '24/7 support',
                'Progress tracking'
            ]
        ],
        'premium' => [
            'title' => 'Premium: $40+/hr',
            'features' => [
                'One-on-one tutoring',
                'All subjects covered',
                '24/7 priority support',
                'Advanced progress tracking',
                'Custom learning materials'
            ]
        ]
    ],

    'faq' => [
        'title' => 'Frequently Asked Questions',
        'questions' => [
            [
                'question' => 'How do I choose the right plan?',
                'answer' => 'Consider your learning goals and budget. Basic is great for occasional help, Standard for regular tutoring, and Premium for intensive learning.'
            ],
            [
                'question' => 'Can I change plans later?',
                'answer' => 'Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected in your next billing cycle.'
            ],
            [
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept all major credit cards, PayPal, and bank transfers. All payments are secure and encrypted.'
            ],
            [
                'question' => 'Is there a minimum commitment?',
                'answer' => 'No, there\'s no minimum commitment. You can book sessions as needed and cancel anytime.'
            ]
        ]
    ]
];
