<?php 
include 'db.php'; 

if(!isset($_GET['id'])) { die("Error: No Invoice ID provided."); }
$inv_id = $_GET['id'];

$query = "SELECT i.*, c.first_name, c.last_name, v.make, v.model, v.license_plate, 
                 s.service_name, id.service_price, e.first_name as staff_name
          FROM invoices i
          JOIN customers c ON i.customer_id = c.customer_id
          JOIN vehicles v ON i.vehicle_id = v.vehicle_id
          JOIN invoice_details id ON i.invoice_id = id.invoice_id
          JOIN services s ON id.service_id = s.service_id
          JOIN employees e ON id.employee_assigned_id = e.employee_id
          WHERE i.invoice_id = $inv_id";

$result = $conn->query($query);
$data = $result->fetch_assoc();

if (!$data) { die("Error: Invoice not found."); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo $inv_id; ?></title>
    <style>
        body { font-family: 'Courier New', monospace; width: 80mm; margin: auto; padding: 15px; border: 1px solid #ddd; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 10px 0; }
        .flex { display: flex; justify-content: space-between; }
        .btn-print { background: #28a745; color: white; padding: 12px; border: none; width: 100%; cursor: pointer; margin-top: 20px; font-weight: bold; border-radius: 5px; }
        @media print { .btn-print { display: none; } body { border: none; width: 100%; } }
    </style>
</head>
<body>
    <div class="center">
        <h3>🚿 CAR WASH PRO</h3>
        <p>123 Clean Street, Accra</p>
    </div>
    <div class="line"></div>
    <p><strong>Inv #:</strong> <?php echo $data['invoice_id']; ?></p>
    <p><strong>Date:</strong> <?php echo $data['invoice_date']; ?></p>
    <p><strong>Customer:</strong> <?php echo $data['first_name'] . " " . $data['last_name']; ?></p>
    <div class="line"></div>
    <p><strong>Car:</strong> <?php echo $data['make'] . " " . $data['model']; ?> (<?php echo $data['license_plate']; ?>)</p>
    <div class="line"></div>
    <div class="flex"><span><?php echo $data['service_name']; ?></span><span>$<?php echo number_format($data['service_price'], 2); ?></span></div>
    <div class="line"></div>
    <div class="flex" style="font-weight:bold;"><span>TOTAL:</span><span>$<?php echo number_format($data['total_amount'], 2); ?></span></div>
    <p><strong>Payment:</strong> <?php echo $data['payment_method']; ?></p>
    <p><strong>Staff:</strong> <?php echo $data['staff_name']; ?></p>
    <div class="line"></div>
    <div class="center"><p>Thank you for visiting!<br>Drive Safely.</p></div>
    <button class="btn-print" onclick="window.print()">Print Receipt</button>
</body>
</html>