<?php
class Notifications {
    private $dbconn;
    private $lowStockThreshold = 10; // Adjust as needed
    private $expiryWarningDays = 30; // Adjust as needed

    public function __construct($dbconn) {
        $this->dbconn = $dbconn;
    }

    public function checkLowStock() {
        $query = "SELECT name, quantity FROM medicine WHERE quantity <= ?";
        $stmt = $this->dbconn->prepare($query);
        $stmt->bind_param("i", $this->lowStockThreshold);
        $stmt->execute();
        $result = $stmt->get_result();

        $lowStockItems = [];
        while ($row = $result->fetch_assoc()) {
            $lowStockItems[] = $row;
        }

        return $lowStockItems;
    }

    public function checkExpiringItems() {
        $expiryDate = date('Y-m-d', strtotime("+{$this->expiryWarningDays} days"));
        $query = "SELECT name, expiry_date FROM medicine WHERE expiry_date <= ? AND expiry_date > CURDATE()";
        $stmt = $this->dbconn->prepare($query);
        $stmt->bind_param("s", $expiryDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $expiringItems = [];
        while ($row = $result->fetch_assoc()) {
            $expiringItems[] = $row;
        }

        return $expiringItems;
    }

    public function getNotifications() {
        $lowStockItems = $this->checkLowStock();
        $expiringItems = $this->checkExpiringItems();

        $notifications = [];

        foreach ($lowStockItems as $item) {
            $notifications[] = [
                'type' => 'low_stock',
                'message' => "Low stock alert: {$item['name']} (Quantity: {$item['quantity']})"
            ];
        }

        foreach ($expiringItems as $item) {
            $notifications[] = [
                'type' => 'expiring',
                'message' => "Expiring soon: {$item['name']} (Expiry: {$item['expiry_date']})"
            ];
        }

        return $notifications;
    }
}
?>
