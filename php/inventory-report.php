<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('TCPDF/tcpdf.php');
require 'connection.php';

// Fixed admin email (only one admin account)
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

date_default_timezone_set('Asia/Manila');
$dateTime = date("F j, Y - g:i A");

// Create PDF
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sunny Bloom Admin');
$pdf->SetTitle('Product Inventory Report');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

// Report Header
$html = '
<h1 style="text-align:center;">Product Inventory Report</h1>
<p><b>Generated by:</b> ' . htmlspecialchars($adminUsername) . '</p>
<p><b>Date & Time:</b> ' . $dateTime . '</p>
<hr>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th><b>Product ID</b></th>
            <th><b>Name</b></th>
            <th><b>Category</b></th>
            <th><b>Price</b></th>
            <th><b>Total Stocks</b></th>
            <th><b>Sold</b></th>
            <th><b>Available</b></th>
            <th><b>Status</b></th>
        </tr>
    </thead>
    <tbody>';

$query = "SELECT id, name, category, price, qty, total_sold, available_stocks, status FROM product";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $html .= '
        <tr>
            <td>' . $row['id'] . '</td>
            <td>' . htmlspecialchars($row['name']) . '</td>
            <td>' . htmlspecialchars($row['category']) . '</td>
            <td>₱' . number_format($row['price'], 2) . '</td>
            <td>' . $row['qty'] . '</td>
            <td>' . $row['total_sold'] . '</td>
            <td>' . $row['available_stocks'] . '</td>
            <td>' . $row['status'] . '</td>
        </tr>';
}

$html .= '</tbody></table>';
$pdf->writeHTML($html);
$pdf->Output('product_inventory_report.pdf', 'I');
?>
