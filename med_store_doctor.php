<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'db_connection.php';
require_once 'notifications.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: index.html");
    exit();
}

$dbconn = Connect();

$notificationsManager = new Notifications($dbconn);
$notifications = $notificationsManager->getNotifications();

// Fetch available medicines (quantity > 0)
$query = "SELECT * FROM " . DB_TABLE_MEDICINE . " WHERE quantity > 0 ORDER BY name";
$result = mysqli_query($dbconn, $query);
$medicines = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

// Fetch sold out medicines
$query = "SELECT * FROM " . DB_TABLE_MEDICINE . " WHERE quantity = 0 ORDER BY name";
$result = mysqli_query($dbconn, $query);
$soldOutMedicines = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
    <title>Medical Store Management - Doctor Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/doctor-style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#"><i class="fas fa-user-md"></i> Med Store Doctor</a>
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
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <h2><i class="fas fa-tachometer-alt"></i> Doctor Dashboard</h2>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="fas fa-tablets"></i> Inventory Overview</h3>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="available-tab" data-toggle="tab" href="#available" role="tab">Available Medicines</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="soldout-tab" data-toggle="tab" href="#soldout" role="tab">Sold Out Medicines</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="inventoryTabContent">
                            <div class="tab-pane fade show active" id="available" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="medicineTable">
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
                                                    <button class="btn btn-sm btn-info view-medicine" data-id="<?php echo $medicine['id']; ?>"><i class="fas fa-eye"></i> View</button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="soldout" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="soldOutTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Last Selling Price</th>
                                                <th>Last Expiry Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($soldOutMedicines as $medicine): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                                <td>$<?php echo number_format($medicine['sp'], 2); ?></td>
                                                <td><?php echo $medicine['expiry_date']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info view-medicine" data-id="<?php echo $medicine['id']; ?>"><i class="fas fa-eye"></i> View</button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#viewTransactionsModal">
                            <i class="fas fa-exchange-alt"></i> View Transactions
                        </button>
                        <button class="btn btn-secondary btn-block" data-toggle="modal" data-target="#generateReportsModal">
                            <i class="fas fa-file-alt"></i> Generate Reports
                        </button>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Statistics</h5>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-capsules"></i> Total Medicines: <span id="totalMedicines" class="badge badge-primary"><?php echo count($medicines) + count($soldOutMedicines); ?></span></p>
                        <p><i class="fas fa-exclamation-triangle"></i> Low Stock Items: <span id="lowStockItems" class="badge badge-warning">Loading...</span></p>
                        <p><i class="fas fa-calendar-times"></i> Expiring Soon: <span id="expiringSoon" class="badge badge-danger">Loading...</span></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($recent_transactions as $transaction): ?>
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($transaction['name']); ?></h6>
                                    <small><?php echo $transaction['txn_timestamp']; ?></small>
                                </div>
                                <p class="mb-1">
                                    <?php echo $transaction['buy_sell'] == 'B' ? '<span class="badge badge-success">Bought</span>' : '<span class="badge badge-info">Sold</span>'; ?>
                                    <?php echo $transaction['qty_buy_sell']; ?> units
                                </p>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Transactions Modal -->
    <div class="modal fade" id="viewTransactionsModal" tabindex="-1" role="dialog" aria-labelledby="viewTransactionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTransactionsModalLabel"><i class="fas fa-exchange-alt"></i> View Transactions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="transactionFilterForm" class="mb-3">
                        <div class="form-row">
                            <div class="col-md-4">
                                <input type="date" class="form-control" id="startDate" name="startDate">
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" id="endDate" name="endDate">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTable">
                                <!-- This will be populated by AJAX -->
                            </tbody>
                        </table>
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
                    <h5 class="modal-title" id="generateReportsModalLabel"><i class="fas fa-file-alt"></i> Generate Reports</h5>
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
                                <option value="profitLoss">Profit/Loss Report</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reportStartDate">Start Date</label>
                            <input type="date" class="form-control" id="reportStartDate" name="startDate" required>
                        </div>
                        <div class="form-group">
                            <label for="reportEndDate">End Date</label>
                            <input type="date" class="form-control" id="reportEndDate" name="endDate" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-file-download"></i> Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Medicine Details Modal -->
    <div class="modal fade" id="viewMedicineModal" tabindex="-1" role="dialog" aria-labelledby="viewMedicineModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMedicineModalLabel"><i class="fas fa-pills"></i> Medicine Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewMedicineBody">
                    <!-- Medicine details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <script src="js/doctor-scripts.js"></script>
        <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#medicineTable, #soldOutTable').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });

            // View medicine details
            $(".view-medicine").click(function() {
                var medicineId = $(this).data("id");
                $.ajax({
                    url: 'get_medicine_details.php',
                    type: 'GET',
                    data: { id: medicineId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var medicine = response.medicine;
                            var detailsHtml = `
                                <p><strong><i class="fas fa-prescription-bottle-alt"></i> Name:</strong> ${medicine.name}</p>
                                <p><strong><i class="fas fa-cubes"></i> Quantity:</strong> ${medicine.quantity}</p>
                                <p><strong><i class="fas fa-tags"></i> Cost Price:</strong> $${medicine.cp}</p>
                                <p><strong><i class="fas fa-dollar-sign"></i> Selling Price:</strong> $${medicine.sp}</p>
                                <p><strong><i class="fas fa-calendar-alt"></i> Expiry Date:</strong> ${medicine.expiry_date}</p>
                                <p><strong><i class="fas fa-flask"></i> Chemical Amount:</strong> ${medicine.chem_amount}</p>
                            `;
                            $("#viewMedicineBody").html(detailsHtml);
                            $("#viewMedicineModal").modal('show');
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

            // Load statistics
            function loadStatistics() {
                $.ajax({
                    url: 'get_statistics.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $("#lowStockItems").text(response.lowStockItems).removeClass().addClass(response.lowStockItems > 0 ? 'badge badge-warning' : 'badge badge-success');
                        $("#expiringSoon").text(response.expiringSoon).removeClass().addClass(response.expiringSoon > 0 ? 'badge badge-danger' : 'badge badge-success');
                    },
                    error: function() {
                        console.error("Error loading statistics");
                        $("#lowStockItems").text("Error");
                        $("#expiringSoon").text("Error");
                    }
                });
            }

            // Refresh notifications
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
                        $('#notificationCount').text(response.length);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error refreshing notifications:", error);
                    }
                });
            }

            // Handle notification click
            $(document).on('click', '.notification-item', function(e) {
                e.preventDefault();
                var notificationId = $(this).data('id');
                var notificationType = $(this).data('type');
                var relatedId = $(this).data('related-id');

                // Handle the notification based on its type
                if (notificationType === 'low_stock' || notificationType === 'expiring') {
                    // Open the medicine details modal
                    $(".view-medicine[data-id='" + relatedId + "']").click();
                }

                // Remove the notification from the list
                $(this).remove();
                var count = $('.notification-item').length;
                $('#notificationCount').text(count);
            });

            loadStatistics();
            refreshNotifications();

            // Refresh statistics and notifications periodically
            setInterval(loadStatistics, 300000); // every 5 minutes
            setInterval(refreshNotifications, 60000); // every minute
        });
        </script>
    </body>
    </html>
