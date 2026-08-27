# WhatsApp Birthday Reminder

Sends a WhatsApp greeting (optional image + caption) to customers on their birthday, with a configurable loyalty reward.

## Integration

- **Customers:** `users.date_of_birth` (month + day match)
- **WhatsApp:** existing `OneSenderWhatsAppService` (`modules/User`)
- **Loyalty points:** `LoyaltyWalletService` with the same yearly `birthday` reference as `loyalty:award-birthday-bonus` (no double points)
- **Discount / voucher:** creates a one-use `Coupon` and includes the code in the message

While this module is enabled and OneSender is configured, Loyalty’s plain-text birthday WhatsApp is skipped to avoid duplicate messages.

## Setup

1. Enable the module in `modules.json` (`WhatsappBirthdayReminder: true`)
2. Dump autoload if needed: `composer dump-autoload`
3. Migrate: `php artisan migrate`
4. Grant permissions: `php artisan whatsapp-birthday:grant-admin-permissions`
5. Refresh translation cache: `php artisan translation:refresh-cache --sync`
6. Admin → **Settings → WhatsApp Notifications** (`?tab=sms`): enable birthday reminder, template, image, reward type
7. Ensure OneSender is configured on the same tab
8. Optional: Admin → Birthday WhatsApp → delivery logs

## Commands

```bash
# Preview who has a birthday today
php artisan whatsapp-birthday:send --dry-run

# Send now
php artisan whatsapp-birthday:send

# Force even if disabled
php artisan whatsapp-birthday:send --force
```

## Schedule

Registered in `app/Console/Kernel.php` at **09:00** daily when the module is enabled:

```php
$schedule->command('whatsapp-birthday:send')->dailyAt('09:00');
```

Ensure the system cron runs Laravel’s scheduler:

```bash
* * * * * cd /path/to/fleetcart && php artisan schedule:run >> /dev/null 2>&1
```

## Placeholders

`{first_name}` `{last_name}` `{full_name}` `{points}` `{coupon_code}` `{reward_label}` `{reward_line}` `{store_name}`

## Logs

Delivery attempts are stored in `whatsapp_birthday_reminder_logs` (unique per `user_id` + `year`) and shown in the admin logs tab.
