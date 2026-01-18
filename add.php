<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Records</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .form-container { background: white; padding: 20px; border-radius: 8px; max-width: 500px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h3 { border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        .nav-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

<a href="index.php" class="nav-link">← Back to Dashboard</a>

<?php
// --- LOGIC TO ADD CUSTOMER ---
if (isset($_POST['add_customer'])) {
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $date = $_POST['join_date'];

    $sql = "INSERT INTO customers (first_name, last_name, phone, email, join_date) 
            VALUES ('$first', '$last', '$phone', '$email', '$date')";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>New customer added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}

// --- LOGIC TO ADD EMPLOYEE ---
if (isset($_POST['add_employee'])) {
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $pos = $_POST['position'];
    $wage = $_POST['hourly_wage'];

    $sql = "INSERT INTO employees (first_name, last_name, position, hourly_wage) 
            VALUES ('$first', '$last', '$pos', '$wage')";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>New employee added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}
// --- LOGIC TO CREATE INVOICE & DETAILS ---
if (isset($_POST['create_invoice'])) {
    $cust_id = $_POST['customer_id'];
    $vehi_id = $_POST['vehicle_id'];
    $emp_id = $_POST['employee_id']; // The cashier
    $pay_meth = $_POST['payment_method'];
    $serv_id = $_POST['service_id'];
    $serv_price = $_POST['service_price'];
    $assigned_emp = $_POST['assigned_employee_id']; // The detailer
    $inv_date = date('Y-m-d H:i:s');

    // 1. Insert into 'invoices' table
    $sql_invoice = "INSERT INTO invoices (customer_id, vehicle_id, employee_id, invoice_date, total_amount, payment_method) 
                    VALUES ('$cust_id', '$vehi_id', '$emp_id', '$inv_date', '$serv_price', '$pay_meth')";

    if ($conn->query($sql_invoice) === TRUE) {
        $last_invoice_id = $conn->insert_id; // Get the ID of the invoice we just made

        // 2. Insert into 'invoice_details' table using that ID
        $sql_details = "INSERT INTO invoice_details (invoice_id, service_id, service_price, employee_assigned_id) 
                        VALUES ('$last_invoice_id', '$serv_id', '$serv_price', '$assigned_emp')";
        
        if ($conn->query($sql_details) === TRUE) {
            echo "<p style='color:green; text-align:center;'>Invoice #$last_invoice_id created successfully!</p>";
        }
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}
?>

<div class="form-container">
    <h3>➕ Add New Customer</h3>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="phone" placeholder="Phone Number">
        <input type="email" name="email" placeholder="Email Address">
        <label>Join Date:</label>
        <input type="date" name="join_date" value="<?php echo date('Y-m-d'); ?>" required>
        <button type="submit" name="add_customer">Save Customer</button>
    </form>
</div>

<div class="form-container">
    <h3>👷 Add New Employee</h3>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="position" placeholder="Position (e.g., Detailer, Cashier)">
        <input type="number" step="0.01" name="hourly_wage" placeholder="Hourly Wage">
        <button type="submit" name="add_employee">Save Employee</button>
    </form>
</div>
<?php
// --- LOGIC TO ADD SERVICE ---
if (isset($_POST['add_service'])) {
    $name = $_POST['service_name'];
    $desc = $_POST['description'];
    $price = $_POST['base_price'];
    $duration = $_POST['duration_minutes'];

    $sql = "INSERT INTO services (service_name, description, base_price, duration_minutes) 
            VALUES ('$name', '$desc', '$price', '$duration')";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>Service added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}

// --- LOGIC TO ADD VEHICLE ---
if (isset($_POST['add_vehicle'])) {
    $cust_id = $_POST['customer_id'];
    $plate = $_POST['license_plate'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $color = $_POST['color'];
    $type = $_POST['vehicle_type'];

    $sql = "INSERT INTO vehicles (customer_id, license_plate, make, model, color, vehicle_type) 
            VALUES ('$cust_id', '$plate', '$make', '$model', '$color', '$type')";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>Vehicle registered successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}
?>
<div class="form-container">
    <h3>🛠️ Add New Service</h3>
    <form method="POST">
        <input type="text" name="service_name" placeholder="Service Name (e.g., Engine Wash)" required>
        <textarea name="description" placeholder="Description" style="width:100%; margin-bottom:10px;"></textarea>
        <input type="number" step="0.01" name="base_price" placeholder="Price ($)" required>
        <input type="number" name="duration_minutes" placeholder="Duration (minutes)">
        <button type="submit" name="add_service">Save Service</button>
    </form>
</div>

<div class="form-container">
    <h3>🚗 Register Vehicle</h3>
    <form method="POST">
        <label>Assign to Customer:</label>
        <select name="customer_id" required>
            <option value="">-- Select Customer --</option>
            <?php
            $customers = $conn->query("SELECT customer_id, first_name, last_name FROM customers");
            while($c = $customers->fetch_assoc()) {
                echo "<option value='{$c['customer_id']}'>{$c['first_name']} {$c['last_name']}</option>";
            }
            ?>
        </select>

        <input type="text" name="license_plate" placeholder="License Plate" required>
        <input type="text" name="make" placeholder="Make (e.g., Toyota)">
        <input type="text" name="model" placeholder="Model (e.g., Camry)">
        <input type="text" name="color" placeholder="Color">
        
        <label>Vehicle Type:</label>
        <select name="vehicle_type" required>
            <option value="Sedan">Sedan</option>
            <option value="SUV">SUV</option>
            <option value="Truck">Truck</option>
            <option value="Van">Van</option>
            <option value="Motorcycle">Motorcycle</option>
        </select>
        
        <button type="submit" name="add_vehicle">Register Vehicle</button>
    </form>
</div>
<div class="form-card" style="width: 450px;">
    <h3>🧾 Generate New Invoice</h3>
    <form method="POST">
        <label>Customer:</label>
        <select name="customer_id" required>
            <?php
            $res = $conn->query("SELECT customer_id, first_name FROM customers");
            while($row = $res->fetch_assoc()) echo "<option value='{$row['customer_id']}'>{$row['first_name']}</option>";
            ?>
        </select>

        <label>Vehicle:</label>
        <select name="vehicle_id" required>
            <?php
            $res = $conn->query("SELECT vehicle_id, license_plate, model FROM vehicles");
            while($row = $res->fetch_assoc()) echo "<option value='{$row['vehicle_id']}'>{$row['license_plate']} ({$row['model']})</option>";
            ?>
        </select>

        <label>Service Performed:</label>
        <select name="service_id" required>
            <?php
            $res = $conn->query("SELECT service_id, service_name, base_price FROM services");
            while($row = $res->fetch_assoc()) echo "<option value='{$row['service_id']}'>{$row['service_name']} ($\${row['base_price']})</option>";
            ?>
        </select>
        <input type="number" step="0.01" name="service_price" placeholder="Confirm Price" required>

        <label>Staff (Cashier):</label>
        <select name="employee_id" required>
            <?php
            $res = $conn->query("SELECT employee_id, first_name FROM employees");
            while($row = $res->fetch_assoc()) echo "<option value='{$row['employee_id']}'>{$row['first_name']}</option>";
            ?>
        </select>

        <label>Staff (Detailer/Worker):</label>
        <select name="assigned_employee_id" required>
            <?php
            $res = $conn->query("SELECT employee_id, first_name FROM employees");
            while($row = $res->fetch_assoc()) echo "<option value='{$row['employee_id']}'>{$row['first_name']}</option>";
            ?>
        </select>

        <label>Payment:</label>
        <select name="payment_method">
            <option value="Cash">Cash</option>
            <option value="Credit Card">Credit Card</option>
            <option value="Mobile Pay">Mobile Pay</option>
            <option value="Gift Card">Gift Card</option>
        </select>

        <button type="submit" name="create_invoice" style="background: #28a745;">Create Invoice</button>
    </form>
</div>
</body>
</html>