# Email System Setup Guide

## Overview
The Ticketix system now includes automated email notifications for bookings:
1. **Booking Confirmation Email** - Sent immediately after a successful booking
2. **Show Reminder Email** - Sent 24 hours before the show time

## Features

### Booking Confirmation Email
- Automatically sent after successful booking
- Includes:
  - Movie title and details
  - Show date and time
  - Seat numbers
  - Ticket number
  - Payment information
  - Food orders (if any)
  - Link to view ticket with QR code

### Show Reminder Email
- Sent 24 hours before show time
- Includes:
  - Show reminder
  - Movie details
  - Show time and location
  - Link to view ticket

## Setup Instructions

### 1. Booking Confirmation (Automatic)
The booking confirmation email is automatically sent when a booking is completed. No additional setup is required.

### 2. Show Reminder (Cron Job Setup)

To enable automatic reminder emails, set up a cron job to run `send-reminder-emails.php` periodically (recommended: every hour).

#### For Linux/Unix (cPanel, VPS, etc.):
```bash
# Edit crontab
crontab -e

# Add this line to run every hour
0 * * * * /usr/bin/php /path/to/ticketix/send-reminder-emails.php >> /path/to/ticketix/reminder-logs.txt 2>&1
```

#### For Windows (Task Scheduler):
1. Open Task Scheduler
2. Create Basic Task
3. Set trigger: Daily, repeat every hour
4. Action: Start a program
5. Program: `php.exe`
6. Arguments: `C:\laragon\www\ticketix\send-reminder-emails.php`
7. Start in: `C:\laragon\www\ticketix`

#### Manual Testing:
You can also run the reminder script manually:
```bash
php send-reminder-emails.php
```

## Database Changes

The reminder system automatically adds a `reminder_sent` column to the `TICKET` table if it doesn't exist. This prevents sending duplicate reminders.

## Email Configuration

Email settings are configured in `mailer.php`:
- SMTP Host: smtp.gmail.com
- Port: 587
- Encryption: STARTTLS
- From: ticketix0@gmail.com

**Note:** Make sure the email account has "Less secure app access" enabled or uses an App Password for Gmail.

## Troubleshooting

### Emails Not Sending
1. Check PHP error logs
2. Verify SMTP credentials in `mailer.php`
3. Ensure PHPMailer is properly installed
4. Check if firewall is blocking SMTP port 587

### Reminder Emails Not Working
1. Verify cron job is running: `crontab -l`
2. Check reminder script logs
3. Verify database connection
4. Ensure `reminder_sent` column exists in TICKET table

## Testing

To test the email system:
1. Make a test booking
2. Check your email inbox for confirmation
3. For reminders, manually adjust show times in database or wait for scheduled time

## Files Involved

- `send-booking-email.php` - Booking confirmation email function
- `send-reminder-emails.php` - Reminder email script (cron job)
- `mailer.php` - PHPMailer configuration
- `process-booking.php` - Integrated email sending after booking

