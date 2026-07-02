<?php

return [

    'users' => [

        'account_created' => 'Akaun anda telah dicipta',
        'no_user_found' => 'Tiada pengguna dengan alamat e-mel tersebut dalam sistem kami',
        'invalid_credentials' => 'E-mel atau kata laluan tidak sah',
        'account_not_activated' => 'Akaun anda belum diaktifkan. Sila semak e-mel anda',
        'account_is_blocked' => 'Akaun anda disekat selama :delay saat',
        'check_email_to_reset_password' => 'Semak e-mel anda untuk set semula kata laluan',
        'invalid_reset_code' => 'Kod set semula tidak sah atau telah tamat tempoh',
        'password_has_been_reset' => 'Kata laluan anda telah diset semula',
        'reset_password_email_sent' => 'E-mel set semula kata laluan telah dihantar',
    ],
    'email' => [

        'reset_password' => 'Set semula kata laluan akaun anda',
    ],
    'whatsapp_otp' => [

        'sent' => 'OTP telah dihantar ke WhatsApp anda.',
        'invalid_phone' => 'Sila masukkan nombor telefon yang sah.',
        'invalid_code' => 'Kod OTP tidak sah.',
        'expired' => 'OTP telah tamat tempoh. Sila minta kod baharu.',
        'too_many_attempts' => 'Terlalu banyak permintaan OTP. Sila cuba lagi dalam :minutes minit.',
        'too_many_verify_attempts' => 'Terlalu banyak kod salah. Sila minta OTP baharu.',
        'send_failed' => 'Gagal menghantar mesej WhatsApp.',
        'connection_failed' => 'Tidak dapat hubungi API WhatsApp. Semak sambungan internet dan URL API OneSender dalam Tetapan.',
        'missing_credentials' => 'API OneSender belum dikonfigurasi.',
        'service_disabled' => 'Log masuk WhatsApp OTP dinyahaktifkan.',
        'sms_message' => ':store kod log masuk: :otp. Sah untuk :minutes minit. Jangan kongsi kod ini.',
        'admin_new_registration' => ':store: Pelanggan baharu daftar melalui WhatsApp OTP (:phone).',
        'admin_no_account' => 'Tiada akaun admin atau beautician sepadan dengan nombor telefon ini.',
    ],
];
