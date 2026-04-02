<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');

// Must be logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit();
}

$userId   = $_SESSION['user_id'] ?? $_SESSION['acc_id'] ?? null;
$ticketId = intval($_POST['ticket_id'] ?? 0);

if (!$userId || !$ticketId) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$conn = getDBConnection();

// Check optional columns
$msBranchCheck = $conn->query("SHOW COLUMNS FROM MOVIE_SCHEDULE LIKE 'branch_id'");
$msHasBranch   = $msBranchCheck && $msBranchCheck->num_rows > 0;

$paymentStatusCheck = $conn->query("SHOW COLUMNS FROM TICKET LIKE 'payment_status'");
$has_payment_status = $paymentStatusCheck && $paymentStatusCheck->num_rows > 0;

// Build ticket query
$ticketQuery = "
    SELECT t.ticket_id, t.ticket_number, t.e_ticket_code, t.ticket_status,
           r.reservation_id, r.reserve_date, r.ticket_amount, r.sum_price,
           m.title, m.image_poster,
           ms.show_date, ms.show_hour,
           t.payment_type, t.amount_paid, t.reference_number
           " . ($has_payment_status ? ", t.payment_status" : "") . "
           " . ($msHasBranch ? ", b.branch_name" : "") . "
    FROM TICKET t
    JOIN RESERVE r ON t.reserve_id = r.reservation_id
    JOIN MOVIE_SCHEDULE ms ON r.schedule_id = ms.schedule_id
    JOIN MOVIE m ON ms.movie_show_id = m.movie_show_id
    -- payment columns now in TICKET
    " . ($msHasBranch ? "LEFT JOIN BRANCH b ON ms.branch_id = b.branch_id" : "") . "
    WHERE t.ticket_id = ? AND r.acc_id = ?
";

$stmt = $conn->prepare($ticketQuery);
$stmt->bind_param("ii", $ticketId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$stmt->close();

if (!$ticket) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Ticket not found.']);
    exit();
}

// Get seats
$seatStmt = $conn->prepare("
    SELECT rs.seat_number, 'Regular' AS seat_type
    FROM RESERVE_SEAT rs
    -- seat_number now in RESERVE_SEAT
    WHERE rs.reservation_id = ?
    ORDER BY rs.seat_number
");
$seatStmt->bind_param("i", $ticket['reservation_id']);
$seatStmt->execute();
$seatResult = $seatStmt->get_result();
$seats = [];       // flat strings for generateBookingPDF()
while ($row = $seatResult->fetch_assoc()) {
    $seats[] = $row['seat_number'] . ' (' . $row['seat_type'] . ')';
}
$seatStmt->close();

// Get food items
$foodStmt = $conn->prepare("
    SELECT f.food_name, tf.quantity, f.food_price
    FROM TICKET_FOOD tf
    JOIN FOOD f ON tf.food_id = f.food_id
    WHERE tf.ticket_id = ?
");
$foodStmt->bind_param("i", $ticketId);
$foodStmt->execute();
$foodResult = $foodStmt->get_result();
$foodItems = [];
while ($row = $foodResult->fetch_assoc()) {
    $foodItems[] = $row;
}
$foodStmt->close();

// Get user details
$userStmt = $conn->prepare("SELECT firstName, lastName, email FROM USER_ACCOUNT WHERE acc_id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();
$conn->close();

if (!$user || empty($user['email'])) {
    echo json_encode(['success' => false, 'message' => 'User email not found.']);
    exit();
}

// Format values
$showTime    = date('g:i A', strtotime($ticket['show_hour']));
$showDate    = date('F d, Y', strtotime($ticket['show_date']));
$reserveDate = date('F d, Y g:i A', strtotime($ticket['reserve_date']));
$branchName  = $ticket['branch_name'] ?? 'N/A';
$seatsDisplay = !empty($seats) ? implode(', ', $seats) : ($ticket['ticket_amount'] . ' seat(s)');
$amountPaid  = 'PHP ' . number_format($ticket['amount_paid'], 2);
$ticketNum   = $ticket['ticket_number'];
$eTicketCode = !empty($ticket['e_ticket_code']) ? $ticket['e_ticket_code'] : $ticketNum;
$movieTitle  = $ticket['title'];
$userName    = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
$userEmail   = $user['email'];
$paymentType = ucfirst(str_replace('-', ' ', $ticket['payment_type'] ?? 'N/A'));
$refNum      = $ticket['reference_number'] ?? 'N/A';

// QR code URL (external API)
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($eTicketCode);

// ─────────────────────────────────────────────────────────────────────────────
// 1. Generate PDF using generate-booking-pdf.php
// ─────────────────────────────────────────────────────────────────────────────
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/generate-booking-pdf.php';

// Compute food total
$foodTotal = 0;
foreach ($foodItems as $fi) {
    $foodTotal += $fi['food_price'] * $fi['quantity'];
}

$ticketUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . '/ticketix/ticket.php?ticket_id=' . $ticketId;

$pdfTempFile = generateBookingPDF(
    $ticket,
    $seats,
    $foodItems,
    $foodTotal,
    $branchName,
    $eTicketCode,
    $ticketUrl
);

if (!$pdfTempFile || !file_exists($pdfTempFile)) {
    echo json_encode(['success' => false, 'message' => 'Failed to generate PDF ticket.']);
    exit();
}

$pdfContent = file_get_contents($pdfTempFile);
@unlink($pdfTempFile); // clean up temp file

// ─────────────────────────────────────────────────────────────────────────────
// 2. Build HTML Email body (dark themed matching checkout.css)
// ─────────────────────────────────────────────────────────────────────────────

// Food rows for email
$foodRowsHtml = '';
if (!empty($foodItems)) {
    $foodRowsHtml .= '
    <tr>
        <td colspan="2" style="padding:10px 16px 6px;font-weight:700;font-size:11px;
            letter-spacing:1px;text-transform:uppercase;color:#558ace;
            background:rgba(85,138,206,0.08);border-top:1px solid rgba(85,138,206,0.2);">
            Food &amp; Beverages
        </td>
    </tr>';
    foreach ($foodItems as $food) {
        $lineTotal = 'PHP ' . number_format($food['food_price'] * $food['quantity'], 2);
        $foodRowsHtml .= '
        <tr>
            <td style="padding:6px 16px;color:#aaa;">'
                . htmlspecialchars($food['food_name'], ENT_QUOTES, 'UTF-8')
                . ' &times;' . intval($food['quantity']) . '</td>
            <td style="padding:6px 16px;text-align:right;color:#8ec98e;">' . $lineTotal . '</td>
        </tr>';
    }
}

$emailBody = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Ticketix E-Ticket</title>
</head>
<body style="margin:0;padding:0;background-color:#0b0b0b;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0b0b0b;padding:30px 0;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0"
        style="background-color:#12122e;border-radius:14px;overflow:hidden;
               border:1px solid rgba(85,138,206,0.25);
               box-shadow:0 8px 32px rgba(0,0,0,0.6);">

        <!-- Red accent top strip -->
        <tr>
          <td style="background-color:#e50914;height:5px;font-size:1px;line-height:1px;">&nbsp;</td>
        </tr>

        <!-- Header -->
        <tr>
          <td style="background-color:#1a1a3e;padding:24px 32px;text-align:center;">
            <h1 style="margin:0;color:#ffffff;font-size:30px;letter-spacing:4px;font-weight:900;">TICKETIX</h1>
            <p style="margin:6px 0 0;color:rgba(180,180,220,0.8);font-size:12px;letter-spacing:1px;">
              OFFICIAL E-TICKET CONFIRMATION
            </p>
          </td>
        </tr>

        <!-- Movie title bar -->
        <tr>
          <td style="background-color:#e50914;padding:12px 24px;">
            <p style="margin:0;color:#fff;font-size:16px;font-weight:700;">
              ' . htmlspecialchars($movieTitle, ENT_QUOTES, 'UTF-8') . '
            </p>
          </td>
        </tr>

        <!-- Greeting -->
        <tr>
          <td style="padding:22px 32px 8px;">
            <p style="margin:0;font-size:15px;color:#e0e0f0;">
              Hi <strong style="color:#ffffff;">' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . '</strong>,
            </p>
            <p style="margin:8px 0 0;font-size:13px;color:#9090b0;line-height:1.5;">
              Your booking is confirmed! Find your full e-ticket attached below as a PDF.
              Here&rsquo;s a quick summary:
            </p>
          </td>
        </tr>

        <!-- Ticket Details Table -->
        <tr>
          <td style="padding:16px 32px 20px;">
            <table width="100%" cellpadding="0" cellspacing="0"
              style="border:1px solid rgba(85,138,206,0.25);border-radius:10px;overflow:hidden;
                     background-color:rgba(255,255,255,0.03);">

              <!-- Header row -->
              <tr style="background-color:rgba(85,138,206,0.18);">
                <td style="padding:10px 16px;font-size:10px;font-weight:700;letter-spacing:1px;
                    text-transform:uppercase;color:#558ace;" width="45%">Field</td>
                <td style="padding:10px 16px;font-size:10px;font-weight:700;letter-spacing:1px;
                    text-transform:uppercase;color:#558ace;">Details</td>
              </tr>

              <!-- Rows -->
              <tr style="border-top:1px solid rgba(255,255,255,0.05);">
                <td style="padding:9px 16px;color:#888;font-size:12px;">Ticket Number</td>
                <td style="padding:9px 16px;color:#fff;font-size:13px;font-weight:700;">'
                    . htmlspecialchars($ticketNum, ENT_QUOTES, 'UTF-8') . '</td>
              </tr>
              <tr style="background:rgba(255,255,255,0.02);border-top:1px solid rgba(255,255,255,0.05);">
                <td style="padding:9px 16px;color:#888;font-size:12px;">Branch</td>
                <td style="padding:9px 16px;color:#e0e0f0;font-size:13px;">'
                    . htmlspecialchars($branchName, ENT_QUOTES, 'UTF-8') . '</td>
              </tr>
              <tr style="border-top:1px solid rgba(255,255,255,0.05);">
                <td style="padding:9px 16px;color:#888;font-size:12px;">Show Date &amp; Time</td>
                <td style="padding:9px 16px;color:#e0e0f0;font-size:13px;">'
                    . $showDate . ' at ' . $showTime . '</td>
              </tr>
              <tr style="background:rgba(255,255,255,0.02);border-top:1px solid rgba(255,255,255,0.05);">
                <td style="padding:9px 16px;color:#888;font-size:12px;">Seats</td>
                <td style="padding:9px 16px;color:#e0e0f0;font-size:13px;">'
                    . htmlspecialchars($seatsDisplay, ENT_QUOTES, 'UTF-8') . '</td>
              </tr>

              ' . $foodRowsHtml . '

              <tr style="border-top:1px solid rgba(255,255,255,0.05);">
                <td style="padding:9px 16px;color:#888;font-size:12px;">Payment Method</td>
                <td style="padding:9px 16px;color:#e0e0f0;font-size:13px;">'
                    . htmlspecialchars($paymentType, ENT_QUOTES, 'UTF-8') . '</td>
              </tr>
              <tr style="background:rgba(255,255,255,0.02);border-top:1px solid rgba(255,255,255,0.05);">
                <td style="padding:9px 16px;color:#888;font-size:12px;">Reference #</td>
                <td style="padding:9px 16px;color:#e0e0f0;font-size:13px;">'
                    . htmlspecialchars($refNum, ENT_QUOTES, 'UTF-8') . '</td>
              </tr>

              <!-- Total row -->
              <tr style="background:rgba(85,138,206,0.12);border-top:2px solid rgba(85,138,206,0.4);">
                <td style="padding:12px 16px;color:#558ace;font-size:13px;font-weight:700;">Total Paid</td>
                <td style="padding:12px 16px;color:#8ec98e;font-size:15px;font-weight:700;">'
                    . $amountPaid . '</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- QR Code section -->
        <tr>
          <td style="padding:4px 32px 24px;text-align:center;">
            <div style="display:inline-block;background:rgba(22,22,50,0.8);
                border:1px solid rgba(85,138,206,0.3);border-radius:12px;padding:20px;">
              <p style="margin:0 0 12px;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;">
                Present at cinema entrance
              </p>
              <img src="' . $qrCodeUrl . '" alt="QR Code" width="150" height="150"
                style="border:2px solid rgba(85,138,206,0.4);border-radius:8px;display:block;margin:0 auto;">
              <p style="margin:10px 0 0;font-size:11px;color:#558ace;font-weight:700;letter-spacing:1px;">
                ' . htmlspecialchars($eTicketCode, ENT_QUOTES, 'UTF-8') . '
              </p>
            </div>
          </td>
        </tr>

        <!-- PDF notice -->
        <tr>
          <td style="padding:0 32px 20px;">
            <div style="background:rgba(229,9,20,0.1);border:1px solid rgba(229,9,20,0.3);
                border-radius:8px;padding:12px 16px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#ff8080;">
                Your full e-ticket PDF is attached to this email. Download and save it for entry.
              </p>
            </div>
          </td>
        </tr>

        <!-- Red accent bottom strip & footer -->
        <tr>
          <td style="background-color:#e50914;height:4px;font-size:1px;line-height:1px;">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color:#0d0d1f;padding:16px 32px;text-align:center;">
            <p style="margin:0;font-size:11px;color:#555;">Booked on ' . $reserveDate . '</p>
            <p style="margin:5px 0 0;font-size:11px;color:#444;">&copy; 2025 Ticketix &bull; ticketix0@gmail.com</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>';

// ─────────────────────────────────────────────────────────────────────────────
// 3. Send email with PDF attachment
// ─────────────────────────────────────────────────────────────────────────────
try {
    $mail = require __DIR__ . '/mailer.php';

    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom('ticketix0@gmail.com', 'Ticketix');
    $mail->addAddress($userEmail, $userName);
    $mail->isHTML(true);

    // Subject — use ASCII-safe dash to avoid encoding issues in mail clients
    $mail->Subject = 'Your Ticketix E-Ticket - ' . $movieTitle . ' (' . $showDate . ')';
    $mail->Body    = $emailBody;
    $mail->AltBody = "Your Ticketix E-Ticket\n\nTicket: {$ticketNum}\nMovie: {$movieTitle}\nDate: {$showDate} at {$showTime}\nBranch: {$branchName}\nSeats: {$seatsDisplay}\nTotal Paid: {$amountPaid}\nCode: {$eTicketCode}";

    // Attach the PDF from string
    $pdfFilename = 'Ticketix-Ticket-' . preg_replace('/[^A-Za-z0-9_-]/', '_', $ticketNum) . '.pdf';
    $mail->addStringAttachment($pdfContent, $pdfFilename, 'base64', 'application/pdf');
    unset($pdfContent); // free memory

    $mail->send();
    echo json_encode([
        'success' => true,
        'message' => 'Ticket sent to ' . $userEmail . '. Check your inbox or spam folder.'
    ]);
} catch (\PHPMailer\PHPMailer\Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email: ' . $mail->ErrorInfo
    ]);
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error generating ticket: ' . $e->getMessage()
    ]);
}
