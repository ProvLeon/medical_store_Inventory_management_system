<?php
require_once 'session_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'med_admin') {
    error_log('Access denied to med_admin_screen.php. Role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    header("Location: index.html");
    exit();
}

include 'db_connection.php';
$dbconn = Connect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Store Management - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Med Admin Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Admin Functions</h2>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Inventory</h5>
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#inventoryModal">Open</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Users</h5>
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#usersModal">Open</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Generate Reports</h5>
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#reportsModal">Open</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Modal -->
    <div class="modal fade" id="inventoryModal" tabindex="-1" role="dialog" aria-labelledby="inventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inventoryModalLabel">Manage Inventory</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="view-tab" data-toggle="tab" href="#view" role="tab">View Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="add-tab" data-toggle="tab" href="#add" role="tab">Add Item</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="update-tab" data-toggle="tab" href="#update" role="tab">Update Item</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="inventoryTabsContent">
                        <div class="tab-pane fade show active" id="view" role="tabpanel">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Quantity</th>
                                        <th>Cost Price</th>
                                        <th>Selling Price</th>
                                        <th>Expiry Date</th>
                                        <th>Chemical Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryTable">
                                    <!-- This will be populated by AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="add" role="tabpanel">
                            <form id="addItemForm">
                                <div class="form-group">
                                    <label for="itemName">Item Name</label>
                                    <input type="text" class="form-control" id="itemName" name="itemName" required>
                                </div>
                                <div class="form-group">
                                    <label for="itemQuantity">Quantity</label>
                                    <input type="number" class="form-control" id="itemQuantity" name="itemQuantity" required>
                                </div>
                                <div class="form-group">
                                    <label for="itemCp">Cost Price</label>
                                    <input type="number" step="0.01" class="form-control" id="itemCp" name="itemCp" required>
                                </div>
                                <div class="form-group">
                                    <label for="itemSp">Selling Price</label>
                                    <input type="number" step="0.01" class="form-control" id="itemSp" name="itemSp" required>
                                </div>
                                <div class="form-group">
                                    <label for="itemExpiryDate">Expiry Date</label>
                                    <input type="date" class="form-control" id="itemExpiryDate" name="itemExpiryDate" required>
                                </div>
                                <div class="form-group">
                                    <label for="itemChemAmount">Chemical Amount</label>
                                    <input type="text" class="form-control" id="itemChemAmount" name="itemChemAmount" required>
                                </div>
                                <div class="form-group">
                                    <label for="itemPharmaco">Pharmaceutical Company</label>
                                    <input type="text" class="form-control" id="itemPharmaco" name="itemPharmaco">
                                </div>
                                <div class="form-group">
                                    <label for="itemCompound">Compound</label>
                                    <input type="text" class="form-control" id="itemCompound" name="itemCompound">
                                </div>
                                <button type="submit" class="btn btn-primary">Add Item</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="update" role="tabpanel">
                            <form id="updateItemForm">
                                <div class="form-group">
                                    <label for="updateItemId">Item ID</label>
                                    <input type="number" class="form-control" id="updateItemId" name="updateItemId" required>
                                </div>
                                <div class="form-group">
                                    <label for="updateItemName">Name</label>
                                    <input type="text" class="form-control" id="updateItemName" name="updateItemName">
                                </div>
                                <div class="form-group">
                                    <label for="updateItemQuantity">Quantity</label>
                                    <input type="number" class="form-control" id="updateItemQuantity" name="updateItemQuantity">
                                </div>
                                <div class="form-group">
                                    <label for="updateItemCp">Cost Price</label>
                                    <input type="number" step="0.01" class="form-control" id="updateItemCp" name="updateItemCp">
                                </div>
                                <div class="form-group">
                                    <label for="updateItemSp">Selling Price</label>
                                    <input type="number" step="0.01" class="form-control" id="updateItemSp" name="updateItemSp">
                                </div>
                                <div class="form-group">
                                    <label for="updateItemExpiryDate">Expiry Date</label>
                                    <input type="date" class="form-control" id="updateItemExpiryDate" name="updateItemExpiryDate">
                                </div>
                                <div class="form-group">
                                    <label for="updateItemChemAmount">Chemical Amount</label>
                                    <input type="text" class="form-control" id="updateItemChemAmount" name="updateItemChemAmount">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Item</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Modal -->
    <div class="modal fade" id="usersModal" tabindex="-1" role="dialog" aria-labelledby="usersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usersModalLabel">Manage Users</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="usersTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="view-users-tab" data-toggle="tab" href="#viewUsers" role="tab">View Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="add-user-tab" data-toggle="tab" href="#addUser" role="tab">Add User</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="usersTabsContent">
                        <div class="tab-pane fade show active" id="viewUsers" role="tabpanel">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTable">
                                    <!-- This will be populated by AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="addUser" role="tabpanel">
                            <form id="addUserForm">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="receptionist">Receptionist</option>
                                        <option value="doctor">Doctor</option>
                                        <option value="med_admin">Admin</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Add User</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Modal -->
    <div class="modal fade" id="reportsModal" tabindex="-1" role="dialog" aria-labelledby="reportsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportsModalLabel">Generate Reports</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="generateReportForm">
                        <div class="form-group">
                            <label for="reportType">Report Type</label>
                            <select class="form-control" id="reportType" name="reportType" required>
                                <option value="sales">Sales Report</option>
                                <option value="inventory">Inventory Report</option>
                                <option value="users">Users Report</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="startDate">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" required>
                        </div>
                        <div class="form-group">
                            <label for="endDate">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/admin-scripts.js"></script>
</body>
</html>
