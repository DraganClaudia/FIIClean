<?php
/**
 * ReportModel - handles report generation and statistics
 */
class ReportModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get overview report
     */
    public function getOverviewReport() {
        $stats = [
            'total_locations' => $this->getTotalLocations(),
            'total_orders' => $this->getTotalOrders(),
            'total_clients' => $this->getTotalClients(),
            'active_orders' => $this->getActiveOrders(),
            'monthly_orders' => $this->getMonthlyOrders(),
            'efficiency_rate' => $this->getEfficiencyRate()
        ];
        
        return $stats;
    }
    
    /**
     * Get orders report
     */
    public function getOrdersReport() {
        $report = [
            'orders_by_status' => $this->getOrdersByStatus(),
            'orders_by_service' => $this->getOrdersByService(),
            'orders_by_location' => $this->getOrdersByLocation(),
            'monthly_trend' => $this->getMonthlyOrdersTrend(),
            'daily_orders' => $this->getDailyOrdersStats()
        ];
        
        return $report;
    }
    
    /**
     * Get locations report
     */
    public function getLocationsReport() {
        $report = [
            'locations_by_status' => $this->getLocationsByStatus(),
            'location_efficiency' => $this->getLocationEfficiency(),
            'orders_per_location' => $this->getOrdersPerLocation(),
            'location_utilization' => $this->getLocationUtilization()
        ];
        
        return $report;
    }
    
    /**
     * Get resources report
     */
    public function getResourcesReport() {
        $report = [
            'resources_by_type' => $this->getResourcesByType(),
            'resource_consumption' => $this->getResourceConsumption(),
            'low_stock_alerts' => $this->getLowStockAlerts(),
            'resource_utilization' => $this->getResourceUtilization()
        ];
        
        return $report;
    }
    
    /**
     * Get total locations
     */
    private function getTotalLocations() {
        $sql = "SELECT COUNT(*) as count FROM Sediu";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get total orders
     */
    private function getTotalOrders() {
        $sql = "SELECT COUNT(*) as count FROM Comanda";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get total clients
     */
    private function getTotalClients() {
        $sql = "SELECT COUNT(*) as count FROM Client";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get active orders
     */
    private function getActiveOrders() {
        $sql = "SELECT COUNT(*) as count FROM Comanda WHERE Status IN ('noua', 'in curs')";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get monthly orders
     */
    private function getMonthlyOrders() {
        $sql = "SELECT COUNT(*) as count FROM Comanda 
                WHERE MONTH(DataProgramare) = MONTH(CURDATE()) 
                AND YEAR(DataProgramare) = YEAR(CURDATE())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get efficiency rate
     */
    private function getEfficiencyRate() {
        $sql = "SELECT 
                    COUNT(CASE WHEN Status = 'finalizata' THEN 1 END) as completed,
                    COUNT(*) as total
                FROM Comanda";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            return round(($result['completed'] / $result['total']) * 100, 2);
        }
        
        return 0;
    }
    
    /**
     * Get overall efficiency
     */
    public function getOverallEfficiency() {
        return $this->getEfficiencyRate();
    }
    
    /**
     * Get orders by status
     */
    private function getOrdersByStatus() {
        $sql = "SELECT Status, COUNT(*) as count FROM Comanda GROUP BY Status";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get orders by service type
     */
    private function getOrdersByService() {
        $sql = "SELECT TipServiciu, COUNT(*) as count FROM Comanda GROUP BY TipServiciu";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get orders by location
     */
    private function getOrdersByLocation() {
        $sql = "SELECT s.Nume as location_name, COUNT(c.id) as order_count
                FROM Sediu s
                LEFT JOIN Comanda c ON s.id = c.idSediu
                GROUP BY s.id, s.Nume
                ORDER BY order_count DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get monthly orders trend
     */
    private function getMonthlyOrdersTrend() {
        $sql = "SELECT 
                    YEAR(DataProgramare) as year,
                    MONTH(DataProgramare) as month,
                    COUNT(*) as order_count
                FROM Comanda 
                WHERE DataProgramare >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY YEAR(DataProgramare), MONTH(DataProgramare)
                ORDER BY year, month";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get daily orders statistics
     */
    private function getDailyOrdersStats() {
        $sql = "SELECT 
                    DATE(DataProgramare) as order_date,
                    COUNT(*) as order_count
                FROM Comanda 
                WHERE DataProgramare >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(DataProgramare)
                ORDER BY order_date";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get locations by status
     */
    private function getLocationsByStatus() {
        $sql = "SELECT Stare, COUNT(*) as count FROM Sediu GROUP BY Stare";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get location efficiency
     */
    private function getLocationEfficiency() {
        $sql = "SELECT 
                    s.Nume as location_name,
                    COUNT(c.id) as total_orders,
                    COUNT(CASE WHEN c.Status = 'finalizata' THEN 1 END) as completed_orders,
                    ROUND(
                        (COUNT(CASE WHEN c.Status = 'finalizata' THEN 1 END) / 
                         NULLIF(COUNT(c.id), 0)) * 100, 2
                    ) as efficiency_rate
                FROM Sediu s
                LEFT JOIN Comanda c ON s.id = c.idSediu
                GROUP BY s.id, s.Nume
                ORDER BY efficiency_rate DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get orders per location
     */
    private function getOrdersPerLocation() {
        $sql = "SELECT 
                    s.Nume as location_name,
                    s.Adresa as location_address,
                    COUNT(c.id) as total_orders,
                    COUNT(CASE WHEN c.Status = 'noua' THEN 1 END) as new_orders,
                    COUNT(CASE WHEN c.Status = 'in curs' THEN 1 END) as in_progress_orders,
                    COUNT(CASE WHEN c.Status = 'finalizata' THEN 1 END) as completed_orders
                FROM Sediu s
                LEFT JOIN Comanda c ON s.id = c.idSediu
                GROUP BY s.id, s.Nume, s.Adresa
                ORDER BY total_orders DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get location utilization
     */
    private function getLocationUtilization() {
        $sql = "SELECT 
                    s.Nume as location_name,
                    s.Stare as status,
                    COUNT(c.id) as order_count,
                    COUNT(CASE WHEN DATE(c.DataProgramare) = CURDATE() THEN 1 END) as today_orders
                FROM Sediu s
                LEFT JOIN Comanda c ON s.id = c.idSediu
                GROUP BY s.id, s.Nume, s.Stare
                ORDER BY order_count DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get resources by type
     */
    private function getResourcesByType() {
        $sql = "SELECT Tip, COUNT(*) as count, SUM(CantitateDisponibila) as total_quantity
                FROM Resursa 
                GROUP BY Tip";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get resource consumption
     */
    private function getResourceConsumption() {
        $sql = "SELECT 
                    r.Nume as resource_name,
                    r.Tip as resource_type,
                    r.CantitateDisponibila as available_quantity,
                    COALESCE(SUM(con.Cantitate), 0) as consumed_quantity
                FROM Resursa r
                LEFT JOIN Consum con ON r.id = con.idResursa
                GROUP BY r.id, r.Nume, r.Tip, r.CantitateDisponibila
                ORDER BY consumed_quantity DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get low stock alerts
     */
    private function getLowStockAlerts() {
        $sql = "SELECT 
                    Nume as resource_name,
                    Tip as resource_type,
                    CantitateDisponibila as available_quantity
                FROM Resursa 
                WHERE CantitateDisponibila < 10
                ORDER BY CantitateDisponibila ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get resource utilization
     */
    private function getResourceUtilization() {
        $sql = "SELECT 
                    r.Nume as resource_name,
                    r.CantitateDisponibila as available,
                    COUNT(con.id) as usage_count,
                    AVG(con.Cantitate) as avg_consumption
                FROM Resursa r
                LEFT JOIN Consum con ON r.id = con.idResursa
                GROUP BY r.id, r.Nume, r.CantitateDisponibila
                ORDER BY usage_count DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}