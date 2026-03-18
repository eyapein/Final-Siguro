<?php
/**
 * Send reminder emails to users before their show time
 * This script should be run via cron job (e.g., every hour)
 * 
 * Usage: php send-reminder-emails.php
 * Or set up cron: 0 * * * * php /path/to/send-reminder-emails.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/send-booking-email.php';

$conn = getDBConnection();

// Get all tickets for shows happening in the next 24 hours (but not in the past)
// and where reminder hasn't been sent yet
$reminderTime = date('Y-m-d H:i:s', strtotime('+24 hours'));
$currentTime = date('Y-m-d H:i:s');

// Check if REMINDER_SENT column exists in TICKET table
$columnCheck = $conn->query("SHOW COLUMNS FROM TICKET LIKE 'reminder_sent'");
$hasReminderColumn = $columnCheck && $columnCheck->num_rows > 0;

// If column doesn't exist, create it
if (!$hasReminderColumn) {
    $conn->query("ALTER TABLE TICKET ADD COLUMN reminder_sent BOOLEAN DEFAULT FALSE");
}

$query = "
    SELECT t.ticket_id, t.ticket_number, ms.show_date, ms.show_hour,
           DATE_ADD(CONCAT(ms.show_date, ' ', ms.show_hour), INTERVAL 24 HOUR) AS reminder_time,
           u.email, u.firstName, u.lastName, m.title, b.branch_name
    FROM TICKET t
    JOIN RESERVE r ON t.reserve_id = r.reservation_id
    JOIN MOVIE_SCHEDULE ms ON r.schedule_id = ms.schedule_id
    JOIN MOVIE m ON ms.movie_show_id = m.movie_show_id
    LEFT JOIN BRANCH b ON ms.branch_id = b.branch_id
    JOIN USER_ACCOUNT u ON r.acc_id = u.acc_id
    WHERE t.ticket_status = 'valid'
    AND DATE_ADD(CONCAT(ms.show_date, ' ', ms.show_hour), INTERVAL 24 HOUR) <= ?
    AND DATE_ADD(CONCAT(ms.show_date, ' ', ms.show_hour), INTERVAL 24 HOUR) >= ?
    AND (t.reminder_sent = FALSE OR t.reminder_sent IS NULL)
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $reminderTime, $currentTime);
$stmt->execute();
$result = $stmt->get_result();

$emailsSent = 0;
$emailsFailed = 0;

while ($row = $result->fetch_assoc()) {
    // Send reminder email
    if (sendReminderEmail($row, $conn)) {
        // Mark reminder as sent
        $updateStmt = $conn->prepare("UPDATE TICKET SET reminder_sent = TRUE WHERE ticket_id = ?");
        $updateStmt->bind_param("i", $row['ticket_id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        $emailsSent++;
        echo "Reminder sent to: " . $row['email'] . " for ticket: " . $row['ticket_number'] . "\n";
    } else {
        $emailsFailed++;
        echo "Failed to send reminder to: " . $row['email'] . " for ticket: " . $row['ticket_number'] . "\n";
    }
}

$stmt->close();
$conn->close();

echo "\nReminder emails sent: $emailsSent\n";
echo "Failed: $emailsFailed\n";

/**
 * Send reminder email to user
 */
function sendReminderEmail($ticketData, $conn) {
    require_once __DIR__ . '/mailer.php';
    
    $ticketId = $ticketData['ticket_id'];
    $showDate = date('F d, Y', strtotime($ticketData['show_date']));
    $showTime = date('g:i A', strtotime($ticketData['show_hour']));
    $showDateTime = date('F d, Y g:i A', strtotime($ticketData['show_date'] . ' ' . $ticketData['show_hour']));
    
    // Generate ticket URL
    $ticketUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                 "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . 
                 dirname($_SERVER['PHP_SELF'] ?? '/') . 
                 "/ticket.php?ticket_id=" . $ticketId;
    
    $userName = htmlspecialchars($ticketData['firstName'] . ' ' . $ticketData['lastName']);
    $movieTitle = htmlspecialchars($ticketData['title']);
    $branchName = htmlspecialchars($ticketData['branch_name'] ?? 'Branch not specified');
    $ticketNumber = htmlspecialchars($ticketData['ticket_number']);
    
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .reminder-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            .btn { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; color: #666; font-size: 0.9em; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>⏰ Show Reminder</h1>
                <p>Your movie is starting soon!</p>
            </div>
            <div class='content'>
                <p>Hi $userName,</p>
                
                <div class='reminder-box'>
                    <h2 style='margin-top: 0; color: #856404;'>$movieTitle</h2>
                    <p><strong>Show Time:</strong> $showDateTime</p>
                    <p><strong>Location:</strong> $branchName</p>
                    <p><strong>Ticket Number:</strong> $ticketNumber</p>
                </div>
                
                <p><strong>Don't forget:</strong></p>
                <ul>
                    <li>Please arrive at least 15 minutes before the show time</li>
                    <li>Bring your ticket QR code (check your email or visit the link below)</li>
                    <li>Have your ID ready if required</li>
                </ul>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$ticketUrl' class='btn'>View Your Ticket & QR Code</a>
                </div>
                
                <p>We look forward to seeing you at the cinema!</p>
                
                <div class='footer'>
                    <p>Thank you for choosing Ticketix!</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    try {
        // Get mailer instance
        $mail = require __DIR__ . '/mailer.php';
        
        // Clear any previous recipients
        $mail->clearAddresses();
        $mail->clearAttachments();
        
        $mail->setFrom('ticketix0@gmail.com', 'Ticketix');
        $mail->addAddress($ticketData['email'], $userName);
        $mail->Subject = "Show Reminder - $movieTitle - $showDateTime";
        $mail->Body = $emailBody;
        $mail->AltBody = "Show Reminder\n\nMovie: $movieTitle\nShow Time: $showDateTime\nLocation: $branchName\nTicket Number: $ticketNumber\n\nView your ticket: $ticketUrl";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Reminder email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

