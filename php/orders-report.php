<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connection.php';
require_once('TCPDF/tcpdf.php');
date_default_timezone_set('Asia/Manila');

// Get current date and time
$dateTime = date("F j, Y - g:i A");

// Fixed admin email
$adminEmail = 'admin@gmail.com';
$adminUsername = 'Unknown Admin';

$stmt = mysqli_prepare($conn, "SELECT username FROM admin WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $adminEmail);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $adminUsername = $row['username'];
}
mysqli_stmt_close($stmt);

// TCPDF setup
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sunny Bloom Admin');
$pdf->SetTitle('Orders Report');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

$startDate = $_GET['start-date'] ?? date('Y-m-d');
$endDate = $_GET['end-date'] ?? date('Y-m-d');

// HTML Header
$html = '
<h1 style="text-align:center;">Payment History Report</h1>
<p><b>Generated by:</b> ' . htmlspecialchars($adminUsername) . '</p>
<p><b>Date & Time:</b> ' . $dateTime . '</p>
<p><b>Date Range:</b> ' . $startDate . ' to ' . $endDate . '</p>
<hr>
<h2>Orders Summary</h2>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>User Name</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Payment Method</th>
            <th>Total Amount</th>
            <th>Order Date</th>
        </tr>
    </thead>
    <tbody>';

// Fetch orders
$orderQuery = "SELECT * FROM orders WHERE order_date BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
$orderResult = mysqli_query($conn, $orderQuery);
while ($row = mysqli_fetch_assoc($orderResult)) {
    $html .= '
        <tr>
            <td>' . $row['id'] . '</td>
            <td>' . htmlspecialchars($row['user_name']) . '</td>
            <td>' . htmlspecialchars($row['name']) . '</td>
            <td>' . htmlspecialchars($row['phone']) . '</td>
            <td>' . htmlspecialchars($row['address']) . '</td>
            <td>' . htmlspecialchars($row['payment_method']) . '</td>
            <td>' . number_format($row['total_amount'], 2) . '</td>
            <td>' . $row['order_date'] . '</td>
        </tr>';
}
$html .= '</tbody></table>';

// Order item details
$html .= '<h2>Order Items</h2>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>ID</th>
            <th>Order ID</th>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total Price</th>
        </tr>
    </thead>
    <tbody>';

$itemResult = mysqli_query($conn, "SELECT * FROM order_items");
while ($item = mysqli_fetch_assoc($itemResult)) {
    $html .= '
        <tr>
            <td>' . $item['id'] . '</td>
            <td>' . $item['order_id'] . '</td>
            <td>' . htmlspecialchars($item['product_name']) . '</td>
            <td>' . $item['quantity'] . '</td>
            <td>' . number_format($item['price'], 2) . '</td>
            <td>' . number_format($item['total_price'], 2) . '</td>
        </tr>';
}
$html .= '</tbody></table>';

$pdf->writeHTML($html);
$pdf->Output('orders_report.pdf', 'I');
?>
