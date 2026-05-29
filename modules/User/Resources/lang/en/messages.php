<?php

return [
    'users' => [
        'account_created' => 'Your account has been created',
        'no_user_found' => 'No user with that email address belongs to our system',
        'invalid_credentials' => 'Invalid email address or password',
        'account_not_activated' => 'Your account is not activated. Please check your email',
        'account_is_blocked' => 'Your account is blocked for :delay seconds',
        'check_email_to_reset_password' => 'Check your email address to reset password',
        'invalid_reset_code' => 'Invalid or expired reset code',
        'password_has_been_reset' => 'Your password has been reset',
        'reset_password_email_sent' => 'Reset password email sent',
    ],
    'email' => [
        'reset_password' => 'Reset your account password',
    ],
    'whatsapp_otp' => [
        'sent' => 'OTP has been sent to your WhatsApp.',
        'invalid_phone' => 'Please enter a valid phone number.',
        'invalid_code' => 'Invalid OTP code.',
        'expired' => 'OTP has expired. Please request a new code.',
        'too_many_attempts' => 'Too many OTP requests. Please try again in :minutes minute(s).',
        'too_many_verify_attempts' => 'Too many incorrect codes. Please request a new OTP.',
        'send_failed' => 'Failed to send WhatsApp message.',
        'missing_credentials' => 'OneSender API is not configured.',
        'service_disabled' => 'WhatsApp OTP login is disabled.',
        'sms_message' => ':store login code: :otp. Valid for :minutes minutes. Do not share this code.',
        'admin_new_registration' => ':store: New customer registered via WhatsApp OTP (:phone).',
    ],
];
