<?php
// File: api/index.php - Complete API Router
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
require_once __DIR__ . '/config/database.php';

// Parse request
$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$base_path = '/smartpos/api';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}
$request_uri = strtok($request_uri, '?');
$segments = explode('/', trim($request_uri, '/'));
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;
$action = $segments[2] ?? null;

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$conn = getDB();

// =============================================
// AUTHENTICATION ROUTES
// =============================================
if ($resource === 'login' && $method === 'POST') {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Update last login
        $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update->execute([$user['id']]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'store_id' => $user['store_id']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
    exit();
}

// =============================================
// PRODUCTS ROUTES
// =============================================
if ($resource === 'products') {
    if ($method === 'GET') {
        $search = $_GET['search'] ?? '';
        $category_id = $_GET['category_id'] ?? '';
        
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON c.id = p.category_id 
                WHERE p.is_active = 1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        if ($category_id) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category_id;
        }
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $products]);
        exit();
    }
    
    if ($method === 'POST') {
        $stmt = $conn->prepare("INSERT INTO products (store_id, category_id, name, sku, barcode, cost_price, selling_price, stock_quantity, low_stock_threshold) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            1, $input['category_id'], $input['name'], $input['sku'] ?? '', 
            $input['barcode'] ?? '', $input['cost_price'], $input['selling_price'], 
            $input['stock_quantity'] ?? 0, $input['low_stock_threshold'] ?? 10
        ]);
        
        echo json_encode(['status' => $result ? 'success' : 'error', 
                         'message' => $result ? 'Product created' : 'Creation failed']);
        exit();
    }
    
    if ($method === 'PUT' && $id) {
        $stmt = $conn->prepare("UPDATE products SET name=?, cost_price=?, selling_price=?, stock_quantity=?, low_stock_threshold=?, category_id=? WHERE id=?");
        $result = $stmt->execute([$input['name'], $input['cost_price'], $input['selling_price'], 
                                   $input['stock_quantity'], $input['low_stock_threshold'] ?? 10, 
                                   $input['category_id'], $id]);
        echo json_encode(['status' => $result ? 'success' : 'error']);
        exit();
    }
    
    if ($method === 'DELETE' && $id) {
        $stmt = $conn->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $result = $stmt->execute([$id]);
        echo json_encode(['status' => $result ? 'success' : 'error']);
        exit();
    }
}

// =============================================
// SALES ROUTES
// =============================================
if ($resource === 'sales') {
    if ($method === 'GET') {
        $stmt = $conn->prepare("SELECT s.*, u.name as cashier_name, c.name as customer_name 
                                FROM sales s 
                                LEFT JOIN users u ON u.id = s.cashier_id 
                                LEFT JOIN customers c ON c.id = s.customer_id 
                                WHERE s.is_void = 0 
                                ORDER BY s.sale_date DESC LIMIT 50");
        $stmt->execute();
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $sales]);
        exit();
    }
    
    if ($method === 'POST') {
        try {
            $conn->beginTransaction();
            
            // Generate invoice number
            $year = date('Y');
            $month = date('m');
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM sales WHERE YEAR(sale_date) = ? AND MONTH(sale_date) = ?");
            $stmt->execute([$year, $month]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;
            $invoice_no = "INV-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            // Calculate totals
            $subtotal = 0;
            foreach ($input['items'] as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }
            $vat_amount = $subtotal * 0.18;
            $total_amount = $subtotal + $vat_amount;
            $change_due = $input['paid_amount'] - $total_amount;
            
            // Insert sale
            $stmt = $conn->prepare("INSERT INTO sales (store_id, cashier_id, customer_id, invoice_no, subtotal, vat_amount, total_amount, payment_method, paid_amount, change_due) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([1, $input['cashier_id'], $input['customer_id'] ?? null, $invoice_no, 
                           $subtotal, $vat_amount, $total_amount, $input['payment_method'], 
                           $input['paid_amount'], $change_due]);
            $sale_id = $conn->lastInsertId();
            
            // Insert sale items
            $itemStmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($input['items'] as $item) {
                $itemStmt->execute([$sale_id, $item['product_id'], $item['quantity'], $item['price'], $item['quantity'] * $item['price']]);
            }
            
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Sale completed', 'data' => ['invoice_no' => $invoice_no, 'sale_id' => $sale_id]]);
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }
}

// =============================================
// DASHBOARD STATS
// =============================================
if ($resource === 'dashboard' && $method === 'GET') {
    // Today's sales
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(sale_date) = CURDATE() AND is_void = 0");
    $stmt->execute();
    $todaySales = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Today's transactions
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM sales WHERE DATE(sale_date) = CURDATE() AND is_void = 0");
    $stmt->execute();
    $todayTransactions = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Product count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
    $stmt->execute();
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Customer count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers");
    $stmt->execute();
    $customerCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Recent sales
    $stmt = $conn->prepare("SELECT id, invoice_no, total_amount, sale_date FROM sales ORDER BY sale_date DESC LIMIT 10");
    $stmt->execute();
    $recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Low stock products
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= low_stock_threshold AND is_active = 1");
    $stmt->execute();
    $lowStockCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'today_sales' => (float)$todaySales['total'],
            'today_transactions' => (int)$todayTransactions['count'],
            'product_count' => (int)$productCount['count'],
            'customer_count' => (int)$customerCount['count'],
            'low_stock_count' => (int)$lowStockCount['count'],
            'recent_sales' => $recentSales
        ]
    ]);
    exit();
}

// =============================================
// CATEGORIES ROUTES
// =============================================
if ($resource === 'categories' && $method === 'GET') {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE store_id = 1 ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $categories]);
    exit();
}

// =============================================
// CUSTOMERS ROUTES
// =============================================
if ($resource === 'customers') {
    if ($method === 'GET') {
        $search = $_GET['search'] ?? '';
        $sql = "SELECT * FROM customers WHERE store_id = 1";
        if ($search) {
            $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $customers]);
        exit();
    }
    
    if ($method === 'POST') {
        $stmt = $conn->prepare("INSERT INTO customers (store_id, name, phone, email, address) VALUES (1, ?, ?, ?, ?)");
        $result = $stmt->execute([$input['name'], $input['phone'] ?? '', $input['email'] ?? '', $input['address'] ?? '']);
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Customer added' : 'Failed']);
        exit();
    }
}

// =============================================
// USERS ROUTES (for cashier management)
// =============================================
if ($resource === 'users' && $method === 'GET') {
    $stmt = $conn->prepare("SELECT id, name, phone, email, role, is_active, created_at FROM users WHERE store_id = 1");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $users]);
    exit();
}

// =============================================
// REPORTS ROUTES
// =============================================
if ($resource === 'reports' && $method === 'GET') {
    if ($action === 'sales') {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        
        $stmt = $conn->prepare("SELECT DATE(sale_date) as date, COUNT(*) as transactions, SUM(total_amount) as total 
                                FROM sales WHERE DATE(sale_date) BETWEEN ? AND ? AND is_void = 0 
                                GROUP BY DATE(sale_date) ORDER BY date DESC");
        $stmt->execute([$start_date, $end_date]);
        $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $report]);
        exit();
    }
    
    if ($action === 'top-products') {
        $stmt = $conn->prepare("SELECT p.name, SUM(si.quantity) as total_sold, SUM(si.total_price) as revenue 
                                FROM sale_items si JOIN products p ON p.id = si.product_id 
                                GROUP BY p.id ORDER BY total_sold DESC LIMIT 10");
        $stmt->execute();
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $topProducts]);
        exit();
    }
}

// =============================================
// 404 - Endpoint not found
// =============================================
echo json_encode(['status' => 'error', 'message' => 'Endpoint not found', 'resource' => $resource]);
?>