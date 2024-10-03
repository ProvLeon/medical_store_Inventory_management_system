<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'med_admin') {
    error_log('Access denied to med_admin_screen.php. Role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    header("Location: index.html");
    exit();
}

$dbconn = Connect();

// Fetch notifications
function getNotifications($dbconn) {
    $query = "SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC";
    $result = mysqli_query($dbconn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$notifications = getNotifications($dbconn);

// Fetch inventory overview
$query = "SELECT * FROM " . DB_TABLE_MEDICINE . " ORDER BY name";
$result = mysqli_query($dbconn, $query);
$medicines = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

// Fetch user overview
$query = "SELECT id, username, role FROM " . DB_TABLE_USERS . " ORDER BY username";
$result = mysqli_query($dbconn, $query);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

// Fetch recent transactions
$query = "SELECT t.id, t.txn_timestamp, t.buy_sell, m.name, ti.quantity as qty_buy_sell
          FROM " . DB_TABLE_TRANSACTION . " t
          JOIN " . DB_TABLE_TRANSACTION_ITEMS . " ti ON t.id = ti.transaction_id
          JOIN " . DB_TABLE_MEDICINE . " m ON ti.medicine_id = m.id
          ORDER BY t.txn_timestamp DESC LIMIT 5";
$result = mysqli_query($dbconn, $query);
$recent_transactions = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

mysqli_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Store Management - Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">
            <i class="fas fa-clinic-medical"></i> Med Admin Dashboard
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="badge badge-danger" id="notificationCount"><?php echo count($notifications); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationsDropdown">
                        <?php if (empty($notifications)): ?>
                            <a class="dropdown-item" href="#">No new notifications</a>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a class="dropdown-item notification-item" href="#" data-id="<?php echo $notification['id']; ?>" data-type="<?php echo $notification['type']; ?>" data-related-id="<?php echo $notification['related_id']; ?>">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-tachometer-alt"></i> Quick Actions</h5>
                        <button class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#addMedicineModal">
                            <i class="fas fa-plus-circle"></i> Add New Medicine
                        </button>
                        <button class="btn btn-secondary btn-block mb-2" data-toggle="modal" data-target="#manageUsersModal">
                            <i class="fas fa-users-cog"></i> Manage Users
                        </button>
                        <button class="btn btn-info btn-block" data-toggle="modal" data-target="#generateReportsModal">
                            <i class="fas fa-chart-bar"></i> Generate Reports
                        </button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-pie"></i> Statistics</h5>
                        <p><i class="fas fa-capsules"></i> Total Medicines: <span id="totalMedicines" class="badge badge-primary"><?php echo count($medicines); ?></span></p>
                        <p><i class="fas fa-users"></i> Total Users: <span id="totalUsers" class="badge badge-secondary"><?php echo count($users); ?></span></p>
                        <p><i class="fas fa-exclamation-triangle"></i> Low Stock Items: <span id="lowStockItems" class="badge badge-warning">0</span></p>
                        <p><i class="fas fa-calendar-times"></i> Expiring Soon: <span id="expiringSoon" class="badge badge-danger">0</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="inventory-tab" data-toggle="tab" href="#inventory" role="tab" aria-controls="inventory" aria-selected="true"><i class="fas fa-boxes"></i> Inventory</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="transactions-tab" data-toggle="tab" href="#transactions" role="tab" aria-controls="transactions" aria-selected="false"><i class="fas fa-exchange-alt"></i> Recent Transactions</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="analytics-tab" data-toggle="tab" href="#analytics" role="tab" aria-controls="analytics" aria-selected="false"><i class="fas fa-chart-line"></i> Analytics</a>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="myTabContent">
                <div class="tab-pane fade show active" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="medicineTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Selling Price</th>
                                    <th>Expiry Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medicines as $medicine): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                    <td><?php echo $medicine['quantity']; ?></td>
                                    <td>$<?php echo number_format($medicine['sp'], 2); ?></td>
                                    <td><?php echo $medicine['expiry_date']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-medicine" data-id="<?php echo $medicine['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning edit-medicine" data-id="<?php echo $medicine['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-medicine" data-id="<?php echo $medicine['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="transactions" role="tabpanel" aria-labelledby="transactions-tab">
                    <h3 class="mb-3">Recent Transactions</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="transactionsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Medicine</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo $transaction['id']; ?></td>
                                    <td><?php echo $transaction['txn_timestamp']; ?></td>
                                    <td><?php echo $transaction['buy_sell'] == 'B' ? '<span class="badge badge-success">Buy</span>' : '<span class="badge badge-info">Sell</span>'; ?></td>
                                    <td><?php echo htmlspecialchars($transaction['name']); ?></td>
                                    <td><?php echo $transaction['qty_buy_sell']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                    <div class="tab-pane fade" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
                        <h3 class="mb-3">Sales Analytics</h3>
                        <div class="card">
                            <div class="card-body">
                                <canvas id="salesChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Add Medicine Modal -->
    <div class="modal fade" id="addMedicineModal" tabindex="-1" role="dialog" aria-labelledby="addMedicineModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMedicineModalLabel">Add New Medicine</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addMedicineForm">
                        <div class="form-group">
                            <label for="medicineName">Medicine Name</label>
                            <input type="text" class="form-control" id="medicineName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="medicineQuantity">Quantity</label>
                            <input type="number" class="form-control" id="medicineQuantity" name="quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="medicineCp">Cost Price</label>
                            <input type="number" step="0.01" class="form-control" id="medicineCp" name="cp" required>
                        </div>
                        <div class="form-group">
                            <label for="medicineSp">Selling Price</label>
                            <input type="number" step="0.01" class="form-control" id="medicineSp" name="sp" required>
                        </div>
                        <div class="form-group">
                            <label for="medicineExpiryDate">Expiry Date</label>
                            <input type="date" class="form-control" id="medicineExpiryDate" name="expiry_date" required>
                        </div>
                        <div class="form-group">
                            <label for="medicineChemAmount">Chemical Amount</label>
                            <input type="text" class="form-control" id="medicineChemAmount" name="chem_amount" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Medicine</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Users Modal -->
    <div class="modal fade" id="manageUsersModal" tabindex="-1" role="dialog" aria-labelledby="manageUsersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageUsersModalLabel">Manage Users</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="usersTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="users-list-tab" data-toggle="tab" href="#usersList" role="tab">Users List</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="add-user-tab" data-toggle="tab" href="#addUser" role="tab">Add User</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="usersTabContent">
                        <div class="tab-pane fade show active" id="usersList" role="tabpanel">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning edit-user" data-id="<?php echo $user['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
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

    <!-- Generate Reports Modal -->
    <div class="modal fade" id="generateReportsModal" tabindex="-1" role="dialog" aria-labelledby="generateReportsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateReportsModalLabel">Generate Reports</h5>
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

    <!-- View/Edit Medicine Modal -->
    <div class="modal fade" id="viewEditMedicineModal" tabindex="-1" role="dialog" aria-labelledby="viewEditMedicineModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewEditMedicineModalLabel">Medicine Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editMedicineForm">
                        <input type="hidden" id="editMedicineId" name="id">
                        <div class="form-group">
                            <label for="editMedicineName">Name</label>
                            <input type="text" class="form-control" id="editMedicineName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="editMedicineQuantity">Quantity</label>
                            <input type="number" class="form-control" id="editMedicineQuantity" name="quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="editMedicineCp">Cost Price</label>
                            <input type="number" step="0.01" class="form-control" id="editMedicineCp" name="cp" required>
                        </div>
                        <div class="form-group">
                            <label for="editMedicineSp">Selling Price</label>
                            <input type="number" step="0.01" class="form-control" id="editMedicineSp" name="sp" required>
                        </div>
                        <div class="form-group">
                            <label for="editMedicineExpiryDate">Expiry Date</label>
                            <input type="date" class="form-control" id="editMedicineExpiryDate" name="expiry_date" required>
                        </div>
                        <div class="form-group">
                            <label for="editMedicineChemAmount">Chemical Amount</label>
                            <input type="text" class="form-control" id="editMedicineChemAmount" name="chem_amount" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Medicine</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.0/dist/chartjs-adapter-moment.min.js"></script>
        <script src="js/admin-scripts.js"></script>
        <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#medicineTable').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });

            $('#transactionsTable').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });

        // Medicine search functionality
        $("#medicineSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#medicineTable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.id === 'analytics-tab') {
                loadAnalyticsChart();
            }
        });

        // Load statistics immediately
        loadStatistics();
        loadAnalyticsChart();
        updateNotificationCount();


        // View/Edit medicine details
        $(".view-medicine, .edit-medicine").click(function() {
            var medicineId = $(this).data("id");
            $.ajax({
                url: 'get_medicine_details.php',
                type: 'GET',
                data: { id: medicineId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var medicine = response.medicine;
                        $("#editMedicineId").val(medicine.id);
                        $("#editMedicineName").val(medicine.name);
                        $("#editMedicineQuantity").val(medicine.quantity);
                        $("#editMedicineCp").val(medicine.cp);
                        $("#editMedicineSp").val(medicine.sp);
                        $("#editMedicineExpiryDate").val(medicine.expiry_date);
                        $("#editMedicineChemAmount").val(medicine.chem_amount);
                        $("#viewEditMedicineModal").modal('show');
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching medicine details:", error);
                    alert("Error fetching medicine details. Please try again.");
                }
            });
        });

        // Add new medicine
        $("#addMedicineForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'add_medicine.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert("Medicine added successfully!");
                        $("#addMedicineModal").modal('hide');
                        location.reload(); // Reload the page to show the new medicine
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error adding medicine:", error);
                    alert("Error adding medicine. Please try again.");
                }
            });
        });

        // Update medicine
        $("#editMedicineForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'update_medicine.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert("Medicine updated successfully!");
                        $("#viewEditMedicineModal").modal('hide');
                        location.reload(); // Reload the page to show the updated medicine
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error updating medicine:", error);
                    alert("Error updating medicine. Please try again.");
                }
            });
        });

        // Add new user
        $("#addUserForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'add_user.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert("User added successfully!");
                        $("#manageUsersModal").modal('hide');
                        location.reload(); // Reload the page to show the new user
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error adding user:", error);
                    alert("Error adding user. Please try again.");
                }
            });
        });

        // Delete user
        $(".delete-user").click(function() {
            var userId = $(this).data("id");
            if (confirm("Are you sure you want to delete this user?")) {
                $.ajax({
                    url: 'delete_user.php',
                    type: 'POST',
                    data: { id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert("User deleted successfully!");
                            location.reload(); // Reload the page to update the user list
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error deleting user:", error);
                        alert("Error deleting user. Please try again.");
                    }
                });
            }
        });

        // Generate report
        $("#generateReportForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'generate_report.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert("Report generated successfully!");
                        // Open the PDF in a new tab
                        window.open(response.reportUrl, '_blank');
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error generating report:", error);
                    alert("Error generating report. Please try again.");
                }
            });
        });

        $(document).on('click', '.notification-item', function(e) {
                e.preventDefault();
                var notificationId = $(this).data('id');
                var notificationType = $(this).data('type');
                var relatedId = $(this).data('related-id');

                $.ajax({
                    url: 'update_notification.php',
                    type: 'POST',
                    data: { id: notificationId, type: notificationType },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (notificationType !== 'low_stock') {
                                // Remove the notification from the list
                                $(`[data-id="${notificationId}"]`).remove();
                                updateNotificationCount
                            } else {
                                // For low stock, open the edit medicine modal
                                openEditMedicineModal(relatedId);
                            }
                            // updateNotificationCount();
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating notification:", error);
                    }
                });
            });

            // Function to update notification count
            function updateNotificationCount() {
                var count = $('.notification-item').length;
                $('#notificationCount').text(count);
                if (count === 0) {
                    $('.dropdown-menu[aria-labelledby="notificationsDropdown"]').html('<a class="dropdown-item" href="#">No new notifications</a>');
                }
            }

            // Call this function after any changes to notifications

            function openEditMedicineModal(medicineId) {
                    // Fetch medicine details and open the edit modal
                    $.ajax({
                        url: 'get_medicine_details.php',
                        type: 'GET',
                        data: { id: medicineId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                var medicine = response.medicine;
                                $("#editMedicineId").val(medicine.id);
                                $("#editMedicineName").val(medicine.name);
                                $("#editMedicineQuantity").val(medicine.quantity);
                                $("#editMedicineCp").val(medicine.cp);
                                $("#editMedicineSp").val(medicine.sp);
                                $("#editMedicineExpiryDate").val(medicine.expiry_date);
                                $("#editMedicineChemAmount").val(medicine.chem_amount);
                                $("#viewEditMedicineModal").modal('show');
                            } else {
                                alert("Error: " + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching medicine details:", error);
                        }
                    });
                }

            // Load analytics chart
            // Improve loadStatistics function
                    function loadStatistics() {
                        $.ajax({
                            url: 'get_statistics.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                $("#lowStockItems").text(response.lowStockItems).removeClass().addClass(response.lowStockItems > 0 ? 'badge badge-warning' : 'badge badge-success');
                                $("#expiringSoon").text(response.expiringSoon).removeClass().addClass(response.expiringSoon > 0 ? 'badge badge-danger' : 'badge badge-success');
                            },
                            error: function(xhr, status, error) {
                                console.error("Error loading statistics:", error);
                                $("#lowStockItems").text("Error").removeClass().addClass('badge badge-danger');
                                $("#expiringSoon").text("Error").removeClass().addClass('badge badge-danger');
                            }
                        });
                    }

                    // Call loadStatistics immediately and set interval
                    loadStatistics();
                    setInterval(loadStatistics, 300000); // Refresh every 5 minutes

                    // Improve loadAnalyticsChart function
                    function loadAnalyticsChart() {
                        $.ajax({
                            url: 'get_sales_data.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                var ctx = document.getElementById('salesChart').getContext('2d');
                                if (window.salesChart instanceof Chart) {
                                    window.salesChart.destroy();
                                }
                                window.salesChart = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        datasets: [{
                                            label: 'Daily Sales',
                                            data: response,
                                            borderColor: 'rgb(75, 192, 192)',
                                            tension: 0.1,
                                            fill: false
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            x: {
                                                type: 'time',
                                                time: {
                                                    unit: 'day',
                                                    displayFormats: {
                                                        day: 'MMM D'
                                                    }
                                                },
                                                title: {
                                                    display: true,
                                                    text: 'Date'
                                                }
                                            },
                                            y: {
                                                beginAtZero: true,
                                                title: {
                                                    display: true,
                                                    text: 'Sales Amount ($)'
                                                }
                                            }
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return 'Sales: $' + context.parsed.y.toFixed(2);
                                                    }
                                                }
                                            },
                                            legend: {
                                                display: false
                                            }
                                        }
                                    }
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error("Error loading analytics data:", error);
                                $('#salesChart').html('<p class="text-center text-danger">Error loading chart data. Please try again later.</p>');
                            }
                        });
                    }

                    // Load analytics chart when the tab is shown
                    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                        if (e.target.id === 'analytics-tab') {
                            loadAnalyticsChart();
                        }
                    });

                    function refreshNotifications() {
                            $.ajax({
                                url: 'get_notifications.php',
                                type: 'GET',
                                dataType: 'json',
                                success: function(response) {
                                    var notificationDropdown = $('.dropdown-menu[aria-labelledby="notificationsDropdown"]');
                                    notificationDropdown.empty();
                                    if (response.length === 0) {
                                        notificationDropdown.html('<a class="dropdown-item" href="#">No new notifications</a>');
                                    } else {
                                        $.each(response, function(index, notification) {
                                            notificationDropdown.append(
                                                `<a class="dropdown-item notification-item" href="#" data-id="${notification.id}" data-type="${notification.type}" data-related-id="${notification.related_id}">
                                                    ${notification.message}
                                                </a>`
                                            );
                                        });
                                    }
                                    updateNotificationCount();
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error refreshing notifications:", error);
                                }
                            });
                        }

                        function updateNotificationCount() {
                            var count = $('.notification-item').length;
                            $('#notificationCount').text(count);
                        }

                        $(document).on('click', '.notification-item', function(e) {
                            e.preventDefault();
                            var notificationId = $(this).data('id');
                            var notificationType = $(this).data('type');
                            var relatedId = $(this).data('related-id');

                            if (notificationType === 'low_stock') {
                                openEditMedicineModal(relatedId);
                            } else {
                                // For other types, you can implement specific actions
                                console.log("Notification clicked:", notificationType, relatedId);
                            }

                            // Remove the notification from the list
                            $(this).remove();
                            updateNotificationCount();
                        });

                        function loadStatistics() {
                            $.ajax({
                                url: 'get_statistics.php',
                                type: 'GET',
                                dataType: 'json',
                                success: function(response) {
                                    $("#lowStockItems").text(response.lowStockItems).removeClass().addClass(response.lowStockItems > 0 ? 'badge badge-warning' : 'badge badge-success');
                                    $("#expiringSoon").text(response.expiringSoon).removeClass().addClass(response.expiringSoon > 0 ? 'badge badge-danger' : 'badge badge-success');
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error loading statistics:", error);
                                    $("#lowStockItems").text("Error").removeClass().addClass('badge badge-danger');
                                    $("#expiringSoon").text("Error").removeClass().addClass('badge badge-danger');
                                }
                            });
                        }



                        // Call these functions immediately and set intervals
                        refreshNotifications();
                        loadStatistics();
                        setInterval(refreshNotifications, 60000); // Refresh notifications every minute
                        setInterval(loadStatistics, 300000); // Refresh statistics every 5 minutes

                // Refresh data periodically
                setInterval(function() {
                    loadStatistics();
                    loadAnalyticsChart();
                }, 60000); // Refresh every minute
            });
            </script>
    </body>
</html>
