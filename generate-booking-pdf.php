<?php
/**
 * Generate PDF for booking confirmation
 * 
 * @param array $ticket Ticket data
 * @param array $seats Seats array
 * @param array $foodItems Food items array
 * @param float $foodTotal Food total
 * @param string $branchName Branch display name
 * @param string $qrData Data encoded into QR
 * @param string $ticketUrl Direct link to ticket page
 * @return string PDF file path or false on failure
 */
function generateBookingPDF($ticket, $seats, $foodItems, $foodTotal, $branchName = 'Branch not specified', $qrData = '', $ticketUrl = '') {
    // Check if TCPDF is available
    $tcpdfPath = __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
    if (!file_exists($tcpdfPath)) {
        // Try alternative path
        $tcpdfPath = __DIR__ . '/vendor/tcpdf/tcpdf/tcpdf.php';
    }
    
    if (!file_exists($tcpdfPath)) {
        error_log("TCPDF not found. Please install it via: composer require tecnickcom/tcpdf");
        return false;
    }
    
    require_once $tcpdfPath;
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Ticketix');
    $pdf->SetAuthor('Ticketix');
    $pdf->SetTitle('Booking Confirmation - ' . $ticket['title']);
    $pdf->SetSubject('Movie Ticket Booking Confirmation');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 20);
    
    // Title
    $pdf->Cell(0, 10, 'Booking Confirmation', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Movie Title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, htmlspecialchars($ticket['title']), 0, 1, 'C');
    $pdf->Ln(2);
    
    if (!empty($ticketUrl)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 102, 204);
        $pdf->Write(0, 'View ticket online', $ticketUrl);
        $pdf->Ln(8);
        $pdf->SetTextColor(0, 0, 0);
    } else {
        $pdf->Ln(3);
    }
    
    // Booking Details
    $pdf->SetFont('helvetica', '', 12);
    
    // Format dates
    $showDate = date('F d, Y', strtotime($ticket['show_date']));
    $showTime = date('g:i A', strtotime($ticket['show_hour']));
    $reserveDate = date('F d, Y g:i A', strtotime($ticket['reserve_date']));
    
    // Details table
    $details = [
        'Ticket Number' => $ticket['ticket_number'],
        'Branch' => $branchName,
        'Show Date' => $showDate,
        'Show Time' => $showTime,
        'Seats' => implode(', ', $seats),
        'Number of Seats' => count($seats) . ' seat(s)',
        'Payment Method' => ucfirst(str_replace('-', ' ', $ticket['payment_type'])),
        'Total Amount' => 'PHP: ' . number_format($ticket['amount_paid'], 2),
        'Booking Date' => $reserveDate
    ];
    
    foreach ($details as $label => $value) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(60, 8, $label . ':', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 8, $value, 0, 1, 'L');
        $pdf->Ln(2);
    }
    
    // Food Items
    if (!empty($foodItems)) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Food Orders:', 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 10);
        foreach ($foodItems as $food) {
            $foodName = htmlspecialchars($food['food_name']);
            $quantity = $food['quantity'];
            $subtotal = number_format($food['food_price'] * $quantity, 2);
            $pdf->Cell(0, 6, "$foodName × $quantity = PHP: $subtotal", 0, 1, 'L');
        }
        
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'Food Total: PHP' . number_format($foodTotal, 2), 0, 1, 'R');
    }
    
    // QR Code section
    if (!empty($qrData)) {
        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Your QR Code', 0, 1, 'C');
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
        $qrImage = @file_get_contents($qrUrl);
        if ($qrImage !== false) {
            $qrFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            file_put_contents($qrFile, $qrImage);
            $currentX = ($pdf->getPageWidth() - 50) / 2;
            $currentY = $pdf->GetY();
            $pdf->Image($qrFile, $currentX, $currentY, 50, 50, 'PNG');
            $pdf->Ln(55);
            @unlink($qrFile);
        } else {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 8, 'Unable to load QR code. Please use the online ticket link.', 0, 1, 'C');
        }
    }
    
    // Footer message
    $pdf->Ln(6);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 6, 'Important Reminders', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(0, 5, '• Please arrive at least 15 minutes before the show time', 0, 'L');
    $pdf->MultiCell(0, 5, '• Present your QR code at the cinema entrance', 0, 'L');
    $pdf->MultiCell(0, 5, '• Keep this PDF as your booking confirmation', 0, 'L');
    
    if (!empty($foodItems)) {
        $pdf->MultiCell(0, 5, '• Your food orders will be ready for pickup at the designated stalls', 0, 'L');
    }
    
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell(0, 5, 'Thank you for choosing Ticketix!', 0, 1, 'C');
    
    // Generate PDF content
    $pdfContent = $pdf->Output('', 'S');
    
    // Save to temporary file
    $tempFile = sys_get_temp_dir() . '/booking_' . $ticket['ticket_id'] . '_' . time() . '.pdf';
    file_put_contents($tempFile, $pdfContent);
    
    return $tempFile;
}