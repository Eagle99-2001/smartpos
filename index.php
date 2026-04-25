<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>SmartPOS - Ultimate Point of Sale System | Tanzania</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            overflow-x: hidden;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        
        /* Gradient Background */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Navbar */
        .navbar {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .navbar-scrolled {
            background: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #0F2027 0%, #203A43 50%, #2C5364 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        /* Cards */
        .feature-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        /* Stats Counter */
        .counter-number {
            font-size: 2.5rem;
            font-weight: 800;
        }
        
        /* Chatbot */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .chatbot-button {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .chatbot-button:hover {
            transform: scale(1.1);
        }
        
        .chatbot-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chatbot-window.active {
            display: flex;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }
        
        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 85%;
        }
        
        .message.user {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-left: auto;
        }
        
        .message.ai {
            background: #f0f0f0;
            color: #333;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .counter-number {
                font-size: 1.5rem;
            }
            .chatbot-window {
                width: 320px;
                height: 450px;
            }
        }
        
        /* Dark Mode */
        body.dark-mode {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
        }
        
        body.dark-mode .feature-card {
            background: #2d2d44;
            color: #fff;
        }
        
        body.dark-mode .chatbot-window {
            background: #2d2d44;
            color: #fff;
        }
        
        body.dark-mode .message.ai {
            background: #3d3d5c;
            color: #fff;
        }
    </style>
</head>
<body>

<!-- ========== NAVIGATION BAR ========== -->
<nav class="navbar navbar-expand-lg fixed-top" id="navbar">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="#">
            <span class="gradient-text">SmartPOS</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#products">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
            </ul>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary rounded-pill" onclick="window.location.href='admin/login.php'">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
                <button class="btn btn-primary rounded-pill gradient-bg" onclick="toggleDarkMode()">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- ========== HERO SECTION ========== -->
<section id="home" class="hero-section" style="padding-top: 100px;">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6 text-white" data-aos="fade-right">
                <div class="badge bg-white/20 rounded-pill px-3 py-2 mb-4">
                    <i class="fas fa-star text-warning me-2"></i>
                    <span>Trusted by 500+ Businesses in Tanzania</span>
                </div>
                <h1 class="display-3 fw-bold mb-4">
                    Smartest Way to<br>
                    <span class="gradient-text">Manage Your Business</span>
                </h1>
                <p class="lead mb-4 opacity-75">
                    All-in-one Point of Sale, Inventory Management, and Customer Loyalty System designed for Tanzanian businesses.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="admin/index.html" class="btn btn-light btn-lg rounded-pill px-4">
                        <i class="fas fa-rocket me-2"></i>Get Started Free
                    </a>
                    <button class="btn btn-outline-light btn-lg rounded-pill px-4" onclick="showDemo()">
                        <i class="fas fa-play-circle me-2"></i>Watch Demo
                    </button>
                </div>
                <div class="row mt-5 g-4">
                    <div class="col-4">
                        <h3 class="fw-bold mb-0" id="statUsers">582+</h3>
                        <small class="opacity-75">Active Users</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold mb-0" id="statSales">2.4M+</h3>
                        <small class="opacity-75">Daily Sales (TZS)</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold mb-0" id="statUptime">99.9%</h3>
                        <small class="opacity-75">Uptime</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="floating">
                    <div class="card bg-white/10 backdrop-blur-lg border-0 rounded-4 p-4">
                        <div class="card-body">
                            <h5 class="text-white mb-3">Live Dashboard Preview</h5>
                            <div class="dashboard-preview">
                                <div class="d-flex justify-content-between border-bottom border-white/20 pb-2 mb-2">
                                    <span class="text-white/70">Today's Sales</span>
                                    <span class="text-white fw-bold" id="liveTodaySales">TZS 0</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom border-white/20 pb-2 mb-2">
                                    <span class="text-white/70">Transactions</span>
                                    <span class="text-white fw-bold" id="liveTransactions">0</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom border-white/20 pb-2 mb-2">
                                    <span class="text-white/70">Products</span>
                                    <span class="text-white fw-bold" id="liveProducts">0</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-white/70">Customers</span>
                                    <span class="text-white fw-bold" id="liveCustomers">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== FEATURES SECTION ========== -->
<section id="features" class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold gradient-text">Powerful Features</h2>
            <p class="lead text-secondary">Everything you need to manage and grow your business</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card feature-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body text-center p-4">
                        <div class="gradient-bg rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-bolt text-white fs-2"></i>
                        </div>
                        <h5 class="fw-bold">Fast POS System</h5>
                        <p class="text-secondary">Process sales in under 10 seconds with barcode scanning.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card feature-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body text-center p-4">
                        <div class="gradient-bg rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-boxes text-white fs-2"></i>
                        </div>
                        <h5 class="fw-bold">Inventory Management</h5>
                        <p class="text-secondary">Real-time stock tracking with low stock alerts.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card feature-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body text-center p-4">
                        <div class="gradient-bg rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-chart-line text-white fs-2"></i>
                        </div>
                        <h5 class="fw-bold">Advanced Analytics</h5>
                        <p class="text-secondary">Detailed reports on sales, profits, and behavior.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                <div class="card feature-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body text-center p-4">
                        <div class="gradient-bg rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-users text-white fs-2"></i>
                        </div>
                        <h5 class="fw-bold">Customer Loyalty</h5>
                        <p class="text-secondary">Reward customers with points and discounts.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== STATS SECTION (LIVE DATA) ========== -->
<section class="gradient-bg py-5">
    <div class="container">
        <div class="row text-center text-white g-4">
            <div class="col-md-3" data-aos="zoom-in">
                <div class="counter-number" id="statDailySales">0</div>
                <p class="mb-0 opacity-75">Today's Sales (TZS)</p>
            </div>
            <div class="col-md-3" data-aos="zoom-in" data-aos-delay="100">
                <div class="counter-number" id="statTransactions">0</div>
                <p class="mb-0 opacity-75">Transactions Today</p>
            </div>
            <div class="col-md-3" data-aos="zoom-in" data-aos-delay="200">
                <div class="counter-number" id="statTotalProducts">0</div>
                <p class="mb-0 opacity-75">Total Products</p>
            </div>
            <div class="col-md-3" data-aos="zoom-in" data-aos-delay="300">
                <div class="counter-number" id="statTotalCustomers">0</div>
                <p class="mb-0 opacity-75">Happy Customers</p>
            </div>
        </div>
    </div>
</section>

<!-- ========== SALES CHART SECTION ========== -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4" data-aos="fade-up">
            <h2 class="display-4 fw-bold gradient-text">Sales Analytics</h2>
            <p class="lead text-secondary">Real-time sales performance dashboard</p>
        </div>
        <div class="row">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4">
                        <canvas id="salesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== PRICING SECTION ========== -->
<section id="pricing" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold gradient-text">Simple Pricing</h2>
            <p class="lead text-secondary">Choose the plan that fits your business</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm rounded-4 text-center">
                    <div class="card-body p-4">
                        <h3 class="fw-bold">Starter</h3>
                        <p class="text-secondary">Perfect for small shops</p>
                        <h2 class="fw-bold">TZS 0</h2>
                        <p class="text-secondary">/month</p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Up to 500 products</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Basic reports</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>1 cashier</li>
                        </ul>
                        <button class="btn btn-outline-primary rounded-pill px-4 mt-3">Get Started</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-lg rounded-4 text-center position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 bg-primary text-white px-3 py-1 rounded-start-pill">Popular</div>
                    <div class="card-body p-4">
                        <h3 class="fw-bold">Professional</h3>
                        <p class="text-secondary">For growing businesses</p>
                        <h2 class="fw-bold gradient-text">TZS 49k</h2>
                        <p class="text-secondary">/month</p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Unlimited products</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Advanced analytics</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>5 cashiers</li>
                        </ul>
                        <button class="btn btn-primary rounded-pill px-4 mt-3 gradient-bg">Get Started</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm rounded-4 text-center">
                    <div class="card-body p-4">
                        <h3 class="fw-bold">Enterprise</h3>
                        <p class="text-secondary">For large businesses</p>
                        <h2 class="fw-bold">TZS 99k</h2>
                        <p class="text-secondary">/month</p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Unlimited everything</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Custom reports</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>24/7 support</li>
                        </ul>
                        <button class="btn btn-outline-primary rounded-pill px-4 mt-3">Contact Sales</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer id="contact" class="bg-dark text-white py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h3 class="fw-bold mb-3">SmartPOS</h3>
                <p class="text-secondary">The ultimate POS solution for Tanzanian businesses.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white"><i class="fab fa-facebook fs-4"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter fs-4"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram fs-4"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin fs-4"></i></a>
                </div>
            </div>
            <div class="col-md-2">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#home" class="text-secondary text-decoration-none">Home</a></li>
                    <li><a href="#features" class="text-secondary text-decoration-none">Features</a></li>
                    <li><a href="#pricing" class="text-secondary text-decoration-none">Pricing</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact Info</h5>
                <ul class="list-unstyled text-secondary">
                    <li><i class="fas fa-envelope me-2"></i> info@smartpos.co.tz</li>
                    <li><i class="fas fa-phone me-2"></i> +255 749 456 451</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i> Dar es Salaam, Tanzania</li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Newsletter</h5>
                <div class="input-group">
                    <input type="email" class="form-control" placeholder="Your email">
                    <button class="btn btn-primary gradient-bg">Subscribe</button>
                </div>
            </div>
        </div>
        <div class="text-center text-secondary mt-4 pt-3 border-top border-secondary">
            <p>&copy; 2026 SmartPOS - All Rights Reserved. Made by Pythonhub in Tanzania</p>
        </div>
    </div>
</footer>

<!-- ========== AI CHATBOT ========== -->
<div class="chatbot-container">
    <div class="chatbot-window" id="chatbotWindow">
        <div class="gradient-bg p-3 text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-robot me-2"></i>AI Business Assistant</h6>
            <button class="btn btn-sm text-white" onclick="toggleChatbot()"><i class="fas fa-times"></i></button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="message ai">Hello! I am your AI Business Assistant. Ask me about sales, inventory, or customers!</div>
        </div>
        <div class="p-3 border-top">
            <div class="input-group">
                <input type="text" id="chatInput" class="form-control" placeholder="Ask me anything..." onkeypress="if(event.key==='Enter') sendMessage()">
                <button class="btn btn-primary gradient-bg" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="chatbot-button" onclick="toggleChatbot()">
        <i class="fas fa-robot text-white fs-3"></i>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });
    
    // Dark Mode Toggle
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    }
    
    // Load dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
    
    // Fetch live data from API
    async function fetchDashboardData() {
        try {
            const response = await fetch('http://localhost/smartpos/api/dashboard');
            const data = await response.json();
            
            if (data.status === 'success') {
                const d = data.data;
                
                // Update counters with animation
                animateCounter('statDailySales', d.today_sales || 0, 'TZS ');
                animateCounter('statTransactions', d.today_transactions || 0);
                animateCounter('statTotalProducts', d.product_count || 0);
                animateCounter('statTotalCustomers', d.customer_count || 0);
                
                // Update live preview
                document.getElementById('liveTodaySales').innerText = 'TZS ' + (d.today_sales || 0).toLocaleString();
                document.getElementById('liveTransactions').innerText = d.today_transactions || 0;
                document.getElementById('liveProducts').innerText = d.product_count || 0;
                document.getElementById('liveCustomers').innerText = d.customer_count || 0;
                
                // Update chart if data exists
                if (d.weekly_sales && d.weekly_sales.length > 0) {
                    updateChart(d.weekly_sales);
                }
            }
        } catch (error) {
            console.error('Error fetching data:', error);
            // Fallback demo data
            document.getElementById('liveTodaySales').innerText = 'TZS 2,450,000';
            document.getElementById('liveTransactions').innerText = '156';
            document.getElementById('liveProducts').innerText = '187';
            document.getElementById('liveCustomers').innerText = '543';
        }
    }
    
    // Animate counter
    function animateCounter(elementId, targetValue, prefix = '') {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        let current = 0;
        const increment = targetValue / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= targetValue) {
                element.innerText = prefix + targetValue.toLocaleString();
                clearInterval(timer);
            } else {
                element.innerText = prefix + Math.floor(current).toLocaleString();
            }
        }, 20);
    }
    
    // Initialize and update chart
    let salesChart = null;
    
    function updateChart(weeklyData) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        if (salesChart) {
            salesChart.destroy();
        }
        
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: weeklyData.map(item => item.day),
                datasets: [{
                    label: 'Sales (TZS)',
                    data: weeklyData.map(item => item.total),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#764ba2',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: (context) => `Sales: TZS ${context.parsed.y.toLocaleString()}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => 'TZS ' + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }
    
    // Demo chart with sample data
    function initDemoChart() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                datasets: [{
                    label: 'Sales (TZS)',
                    data: [125000, 150000, 180000, 220000, 280000, 350000, 420000],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => `Sales: TZS ${context.parsed.y.toLocaleString()}`
                        }
                    }
                }
            }
        });
    }
    
    // Chatbot functionality
    function toggleChatbot() {
        const window = document.getElementById('chatbotWindow');
        window.classList.toggle('active');
    }
    
    async function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, 'user');
        input.value = '';
        
        // Show typing indicator
        addMessage('Typing...', 'ai', true);
        
        // Process AI response
        setTimeout(() => {
            removeTypingIndicator();
            const response = getAIResponse(message);
            addMessage(response, 'ai');
        }, 1000);
    }
    
    function addMessage(text, type, isTyping = false) {
        const container = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        if (isTyping) messageDiv.id = 'typingIndicator';
        messageDiv.innerHTML = text;
        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
    }
    
    function removeTypingIndicator() {
        const typing = document.getElementById('typingIndicator');
        if (typing) typing.remove();
    }
    
    function getAIResponse(message) {
        const msg = message.toLowerCase();
        
        // Fetch latest stats
        const todaySales = document.getElementById('liveTodaySales')?.innerText || 'TZS 0';
        const transactions = document.getElementById('liveTransactions')?.innerText || '0';
        
        if (msg.includes('sales') || msg.includes('revenue')) {
            return `📊 Today's sales: ${todaySales}\n📈 Total transactions: ${transactions}\n⭐ Average order: ${(parseInt(todaySales.replace(/[^0-9]/g, '')) / parseInt(transactions) || 0).toLocaleString()} TZS`;
        }
        
        if (msg.includes('product') || msg.includes('stock')) {
            return `📦 We have active products in inventory.\n⚠️ Check dashboard for low stock alerts.\n💡 Tip: Regular inventory updates help prevent stockouts!`;
        }
        
        if (msg.includes('customer')) {
            return `👥 We value our customers! Track customer purchases and offer loyalty rewards through our POS system.`;
        }
        
        if (msg.includes('profit') || msg.includes('earning')) {
            return `💰 Your estimated profit margin is around 30-40%. Want detailed profit reports? Check the analytics dashboard!`;
        }
        
        if (msg.includes('help')) {
            return `I can help you with:\n• Sales reports 📊\n• Inventory status 📦\n• Customer analytics 👥\n• Profit calculations 💰\nJust ask specific questions!`;
        }
        
        return `I understand you're asking about "${message}". For specific business insights, please ask about:\n- Sales performance\n- Inventory status\n- Customer analytics\n- Profit calculations`;
    }
    
    // Load data on page load
    fetchDashboardData();
    initDemoChart();
    
    // Refresh data every 30 seconds
    setInterval(fetchDashboardData, 30000);
    
    function showDemo() {
        alert('Demo video coming soon! Check our YouTube channel.');
    }
</script>
</body>
</html>