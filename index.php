<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Wash Admin Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        h2 { color: #333; text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #FF0000; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .badge { background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; }
        .btn-add { display:inline-block; background:#FF0000; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin-bottom:20px; font-weight: bold; }
        .btn-print { background: #28a745; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 0.85em; }
    </style>
</head>
<body>

<div class="container">
    <h2>🚿 Car Wash Service Report</h2>
    <a href="add.php" class="btn-add">+ Add New Record / Invoice</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Car</th>
                <th>Service</th>
                <th>Price</th>
                <th>Staff</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT i.invoice_id, c.first_name AS Customer, v.model AS Car, 
                           s.service_name AS Service, id.service_price AS Price, e.first_name AS Staff
                    FROM invoices i
                    JOIN customers c ON i.customer_id = c.customer_id
                    JOIN vehicles v ON i.vehicle_id = v.vehicle_id
                    JOIN invoice_details id ON i.invoice_id = id.invoice_id
                    JOIN services s ON id.service_id = s.service_id
                    JOIN employees e ON id.employee_assigned_id = e.employee_id
                    ORDER BY i.invoice_id DESC";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>#{$row['invoice_id']}</td>
                            <td>{$row['Customer']}</td>
                            <td>{$row['Car']}</td>
                            <td>{$row['Service']}</td>
                            <td>\${$row['Price']}</td>
                            <td><span class='badge'>{$row['Staff']}</span></td>
                            <td><a href='print-page.php?id={$row['invoice_id']}' target='_blank' class='btn-print'>Print 🖨️</a></td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>No invoices found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="container">
    <h2>📊 Service Revenue Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Service Name</th>
                <th>Total Washes</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $summary_sql = "SELECT s.service_name, COUNT(id.invoice_id) as total_washes, SUM(id.service_price) as total_revenue
                            FROM services s
                            LEFT JOIN invoice_details id ON s.service_id = id.service_id
                            GROUP BY s.service_name";
            $summary_result = $conn->query($summary_sql);
            while($row = $summary_result->fetch_assoc()) {
                $revenue = $row['total_revenue'] ? $row['total_revenue'] : 0;
                echo "<tr>
                        <td>{$row['service_name']}</td>
                        <td>{$row['total_washes']}</td>
                        <td>\${$revenue}</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>