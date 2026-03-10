import { Hono } from 'hono'
import { html } from 'hono/html'

const app = new Hono<{ Bindings: { DB: D1Database } }>()
const layout = (content: any) => html`
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Car Wash Admin</title>
    <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
    .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; width: 200px; border-top: 4px solid #007bff; }
    .stat-card h3 { margin: 0; font-size: 0.9rem; color: #666; text-transform: uppercase; }
    .stat-card p { margin: 10px 0 0; font-size: 1.8rem; font-weight: bold; color: #333; }
    .form-container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin: 0 auto 30px auto; }
    table th { font-weight: 600; }
    table td { padding: 12px 8px; color: #444; }
    .nav-link { color: #007bff; text-decoration: none; font-weight: bold; }
    .nav-link:hover { text-decoration: underline; }
</style>
</head>
<body>
    <h1 style="text-align:center;">🧼 Car Wash Management</h1>
    ${content}
</body>
</html>`

// 1. Redirect Home to Dashboard
app.get('/', (c) => c.redirect('/dashboard'))
// --- DASHBOARD VIEW ---
app.get('/dashboard', async (c) => {
    // 1. Fetch Stats
    const rawStats = await c.env.DB.prepare(`
        SELECT 
            (SELECT COUNT(*) FROM customers) as total_customers,
            (SELECT COUNT(*) FROM invoices) as total_invoices,
            (SELECT SUM(total_amount) FROM invoices) as total_revenue
    `).first();
    const stats = rawStats ?? { total_customers: 0, total_invoices: 0, total_revenue: 0 };

    // 2. Fetch Recent Invoices with Joins (to show names instead of IDs)
    const { results: recentInvoices } = await c.env.DB.prepare(`
        SELECT 
            i.invoice_id, 
            c.first_name || ' ' || c.last_name as customer_name, 
            v.license_plate, 
            i.total_amount, 
            i.payment_method, 
            i.invoice_date
        FROM invoices i
        JOIN customers c ON i.customer_id = c.customer_id
        JOIN vehicles v ON i.vehicle_id = v.vehicle_id
        ORDER BY i.invoice_date DESC
        LIMIT 10
    `).all();

    return c.html(layout(html`
        <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px;">
            <div class="stat-card"><h3>👥 Customers</h3><p>${stats.total_customers}</p></div>
            <div class="stat-card"><h3>🧾 Invoices</h3><p>${stats.total_invoices}</p></div>
            <div class="stat-card"><h3>💰 Revenue</h3><p>$${Number(stats.total_revenue || 0).toFixed(2)}</p></div>
        </div>

        <div class="form-container" style="max-width: 900px;">
            <h3>Recent Transactions</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background: #007bff; color: white; text-align: left;">
                        <th style="padding: 10px;">ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    ${recentInvoices.map(inv => html`
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px;">#${inv.invoice_id}</td>
                            <td>${inv.customer_name}</td>
                            <td>${inv.license_plate}</td>
                            <td>$${inv.total_amount}</td>
                            <td>${inv.payment_method}</td>
                            <td>${inv.invoice_date ? new Date(String(inv.invoice_date)).toLocaleDateString() : ''}</td>
                        </tr>
                    `)}
                </tbody>
            </table>
            <br>
            <a href="/add" class="nav-link" style="display: block; text-align: center;">+ Add New Entry</a>
        </div>
    `))
})

// 2. Main Dashboard (Forms)
app.get('/add', async (c) => {
    const customers = await c.env.DB.prepare("SELECT customer_id, first_name, last_name FROM customers").all();
    const employees = await c.env.DB.prepare("SELECT employee_id, first_name FROM employees").all();
    const services = await c.env.DB.prepare("SELECT service_id, service_name, base_price FROM services").all();
    const vehicles = await c.env.DB.prepare("SELECT vehicle_id, license_plate FROM vehicles").all();

    const msg = c.req.query('msg');
    const error = c.req.query('error');

    return c.html(layout(html`
        ${msg ? html`<div class="msg" style="color:green; background:#d4edda;">✅ ${msg}</div>` : ''}
        ${error ? html`<div class="msg" style="color:red; background:#f8d7da;">⚠️ ${error}</div>` : ''}

        <div class="form-container">
            <h3>👤 Add New Customer</h3>
            <form method="POST" action="/add-customer">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="text" name="phone" placeholder="Phone Number">
                <input type="email" name="email" placeholder="Email Address">
                <input type="date" name="join_date" value="${new Date().toISOString().split('T')[0]}" required>
                <button type="submit">Save Customer</button>
            </form>
        </div>

        <div class="form-container">
            <h3>👷 Add New Employee</h3>
            <form method="POST" action="/add-employee">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="text" name="position" placeholder="Position (e.g., Detailer)">
                <input type="number" step="0.01" name="hourly_wage" placeholder="Hourly Wage">
                <button type="submit">Hire Employee</button>
            </form>
        </div>

        <div class="form-container">
            <h3>🚗 Register Vehicle</h3>
            <form method="POST" action="/add-vehicle">
                <select name="customer_id" required>
                    <option value="">-- Select Owner --</option>
                    ${customers.results.map(cust => html`<option value="${cust.customer_id}">${cust.first_name} ${cust.last_name}</option>`)}
                </select>
                <input type="text" name="license_plate" placeholder="License Plate" required>
                <input type="text" name="make" placeholder="Make">
                <input type="text" name="model" placeholder="Model">
                <select name="vehicle_type">
                    <option value="Sedan">Sedan</option><option value="SUV">SUV</option><option value="Truck">Truck</option>
                </select>
                <button type="submit">Register Vehicle</button>
            </form>
        </div>

        <div class="form-container">
            <h3>🧾 Generate New Invoice</h3>
            <form method="POST" action="/create-invoice">
                <select name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    ${customers.results.map(cust => html`<option value="${cust.customer_id}">${cust.first_name}</option>`)}
                </select>
                <select name="vehicle_id" required>
                    <option value="">-- Select Vehicle --</option>
                    ${vehicles.results.map(v => html`<option value="${v.vehicle_id}">${v.license_plate}</option>`)}
                </select>
                <select name="employee_id" required>
                    <option value="">-- Select Cashier --</option>
                    ${employees.results.map(e => html`<option value="${e.employee_id}">${e.first_name}</option>`)}
                </select>
                <select name="service_id" required>
                    <option value="">-- Select Service --</option>
                    ${services.results.map(s => html`<option value="${s.service_id}">${s.service_name} ($${s.base_price})</option>`)}
                </select>
                <input type="number" step="0.01" name="service_price" placeholder="Final Price" required>
                <select name="payment_method">
                    <option value="Cash">Cash</option><option value="Credit Card">Credit Card</option><option value="Mobile Pay">Mobile Pay</option>
                </select>
                <button type="submit">Create Invoice</button>
            </form>
        </div>
    `))
})

// --- POST HANDLERS ---

app.post('/add-customer', async (c) => {
    const b = await c.req.parseBody();
    await c.env.DB.prepare(
        "INSERT INTO customers (first_name, last_name, phone, email, join_date) VALUES (?, ?, ?, ?, ?)"
    ).bind(b.first_name, b.last_name, b.phone, b.email, b.join_date).run();
    return c.redirect('/add?msg=Customer Added');
})

app.post('/add-employee', async (c) => {
    const b = await c.req.parseBody();
    await c.env.DB.prepare(
        "INSERT INTO employees (first_name, last_name, position, hourly_wage) VALUES (?, ?, ?, ?)"
    ).bind(b.first_name, b.last_name, b.position, b.hourly_wage).run();
    return c.redirect('/add?msg=Employee Hired');
})

app.post('/add-vehicle', async (c) => {
    const b = await c.req.parseBody();
    await c.env.DB.prepare(
        "INSERT INTO vehicles (customer_id, license_plate, make, model, vehicle_type) VALUES (?, ?, ?, ?, ?)"
    ).bind(b.customer_id, b.license_plate, b.make, b.model, b.vehicle_type).run();
    return c.redirect('/add?msg=Vehicle Registered');
})

app.post('/create-invoice', async (c) => {
    try {
        const b = await c.req.parseBody();
        const now = new Date().toISOString();
        
        const inv = await c.env.DB.prepare(
            "INSERT INTO invoices (customer_id, vehicle_id, employee_id, invoice_date, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?)"
        ).bind(b.customer_id, b.vehicle_id, b.employee_id, now, b.service_price, b.payment_method).run();

        const lastId = inv.meta.last_row_id;

        await c.env.DB.prepare(
            "INSERT INTO invoice_details (invoice_id, service_id, service_price, employee_assigned_id) VALUES (?, ?, ?, ?)"
        ).bind(lastId, b.service_id, b.service_price, b.employee_id).run();

        return c.redirect(`/add?msg=Invoice #${lastId} Created`);
    } catch (e) {
        const errMsg = e instanceof Error ? e.message : String(e);
        return c.redirect(`/add?error=${encodeURIComponent(errMsg)}`);
    }
})

export default app