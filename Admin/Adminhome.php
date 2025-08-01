<?php
// Example data, replace with database queries
$totalUsers = 50;
$totalTeam = 70;
$totalPortfolio = 30;
$totalSlider = 15;
$totalStaticPage = 4;
$totalServices = 20;
$totalEnquiries = 65;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/Admin/AdminHome.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Master Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="active">Dashboard</li>
                <li>Settings</li>
                <li>Page Management</li>
                <li>Metadata</li>
                <li>Slider Management</li>
                <li>Team Management</li>
                <li>Services Management</li>
                <li>Portfolio Management</li>
                <li>User Management</li>
                <li>Report Management</li>
                <li>Enquiry Management</li>
            </ul>
        </aside>
        <main class="dashboard">
            <div class="dashboard-header">
                <h1>Dashboard <span class="subtext">Control panel</span></h1>
                <div class="profile">Laravel Admin</div>
            </div>
            <div class="card-grid">
                <div class="card card-blue">
                    <div class="card-value"><?= $totalStaticPage ?></div>
                    <div class="card-label">Total Static Page</div>
                    <a href="#staticpage" class="card-link">More info <span>&#8594;</span></a>
                </div>
                <div class="card card-orange">
                    <div class="card-value"><?= $totalSlider ?></div>
                    <div class="card-label">Total Slider</div>
                    <a href="#slider" class="card-link">More info <span>&#8594;</span></a>
                </div>
                <div class="card card-red">
                    <div class="card-value"><?= $totalTeam ?></div>
                    <div class="card-label">Total Team</div>
                    <a href="#team" class="card-link">More info <span>&#8594;</span></a>
                </div>
                <div class="card card-green">
                    <div class="card-value"><?= $totalServices ?></div>
                    <div class="card-label">Total Services</div>
                    <a href="#services" class="card-link">More info <span>&#8594;</span></a>
                </div>
                <div class="card card-blue">
                    <div class="card-value"><?= $totalPortfolio ?></div>
                    <div class="card-label">Total Portfolio</div>
                    <a href="#portfolio" class="card-link">More info <span>&#8594;</span></a>
                </div>
                <div class="card card-pink">
                    <div class="card-value"><?= $totalUsers ?></div>
                    <div class="card-label">Total User</div>
                    <a href="#users" class="card-link">More info <span>&#8594;</span></a>
                </div>
                <div class="card card-green">
                    <div class="card-value"><?= $totalEnquiries ?></div>
                    <div class="card-label">Total Enquiries</div>
                    <a href="#enquiries" class="card-link">More info <span>&#8594;</span></a>
                </div>
            </div>

            <!-- Example sections for user and report management -->
            <section id="users">
                <h2>User Management</h2>
                <!-- User management table/list goes here -->
                <p>Show, add, edit, or delete users here.</p>
            </section>
            <section id="reports">
                <h2>Report Management</h2>
                <!-- Report management table/list goes here -->
                <p>Show, view, or delete reports here.</p>
            </section>
        </main>
    </div>
</body>
</html>