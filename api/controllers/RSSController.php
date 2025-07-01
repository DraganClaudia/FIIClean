<?php
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Resource.php';

class RSSController {
    private $locationModel;
    private $orderModel;
    private $resourceModel;
    
    public function __construct() {
        $this->locationModel = new Location();
        $this->orderModel = new Order();
        $this->resourceModel = new Resource();
    }
    
    public function handleRequest() {
        $type = $_GET['type'] ?? 'status'; // status, orders, alerts
        
        switch($type) {
            case 'status':
                $this->generateLocationStatusFeed();
                break;
            case 'orders':
                $this->generateOrdersFeed();
                break;
            case 'alerts':
                $this->generateAlertsFeed();
                break;
            default:
                $this->generateLocationStatusFeed();
        }
    }
    
    private function generateLocationStatusFeed() {
        $locations = $this->locationModel->getAll();
        
        header('Content-Type: application/rss+xml; charset=utf-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0">' . "\n";
        echo '<channel>' . "\n";
        echo '<title>CaS - Status Locații Spălătorii</title>' . "\n";
        echo '<description>Status în timp real al locațiilor de spălătorie</description>' . "\n";
        echo '<link>http://localhost/fiiclean/public/index.html</link>' . "\n";
        echo '<lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
        
        foreach ($locations as $location) {
            $ordersCount = count($this->orderModel->getByLocation($location['id']));
            $resourcesLow = count($this->resourceModel->getByLocation($location['id']));
            
            echo '<item>' . "\n";
            echo '<title>Locația: ' . htmlspecialchars($location['name']) . '</title>' . "\n";
            echo '<description>';
            echo 'Status: ' . htmlspecialchars($location['status']) . '. ';
            echo 'Comenzi active: ' . $ordersCount . '. ';
            echo 'Adresa: ' . htmlspecialchars($location['address']) . '.';
            echo '</description>' . "\n";
            echo '<pubDate>' . date('r', strtotime($location['created_at'])) . '</pubDate>' . "\n";
            echo '<guid>' . 'location-' . $location['id'] . '-' . time() . '</guid>' . "\n";
            echo '</item>' . "\n";
        }
        
        echo '</channel>' . "\n";
        echo '</rss>' . "\n";
    }
    
    private function generateOrdersFeed() {
        $orders = $this->orderModel->getAll();
        $recentOrders = array_slice($orders, 0, 20); // Ultimele 20 comenzi
        
        header('Content-Type: application/rss+xml; charset=utf-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0">' . "\n";
        echo '<channel>' . "\n";
        echo '<title>CaS - Comenzi Recente</title>' . "\n";
        echo '<description>Comenzi recente în sistem</description>' . "\n";
        echo '<link>http://localhost/fiiclean/public/orders.html</link>' . "\n";
        echo '<lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
        
        foreach ($recentOrders as $order) {
            echo '<item>' . "\n";
            echo '<title>Comanda #' . $order['id'] . ' - ' . htmlspecialchars($order['client_name']) . '</title>' . "\n";
            echo '<description>';
            echo 'Serviciu: ' . htmlspecialchars($order['service_type']) . '. ';
            echo 'Status: ' . htmlspecialchars($order['status']) . '. ';
            if ($order['price']) {
                echo 'Preț: ' . $order['price'] . ' RON. ';
            }
            echo '</description>' . "\n";
            echo '<pubDate>' . date('r', strtotime($order['created_at'])) . '</pubDate>' . "\n";
            echo '<guid>' . 'order-' . $order['id'] . '-' . strtotime($order['created_at']) . '</guid>' . "\n";
            echo '</item>' . "\n";
        }
        
        echo '</channel>' . "\n";
        echo '</rss>' . "\n";
    }
    
    private function generateAlertsFeed() {
        $lowStock = $this->resourceModel->getLowStock();
        
        header('Content-Type: application/rss+xml; charset=utf-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0">' . "\n";
        echo '<channel>' . "\n";
        echo '<title>CaS - Alerte Sistem</title>' . "\n";
        echo '<description>Alerte pentru stocuri reduse și probleme</description>' . "\n";
        echo '<link>http://localhost/fiiclean/public/resources.html</link>' . "\n";
        echo '<lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
        
        foreach ($lowStock as $resource) {
            echo '<item>' . "\n";
            echo '<title>ALERTĂ: Stoc redus - ' . htmlspecialchars($resource['name']) . '</title>' . "\n";
            echo '<description>';
            echo 'Resursa: ' . htmlspecialchars($resource['name']) . '. ';
            echo 'Stoc actual: ' . $resource['quantity'] . ' ' . $resource['unit'] . '. ';
            echo 'Prag minim: ' . $resource['min_threshold'] . '. ';
            echo 'Locația: ' . htmlspecialchars($resource['location_name'] ?? 'Necunoscută') . '.';
            echo '</description>' . "\n";
            echo '<pubDate>' . date('r') . '</pubDate>' . "\n";
            echo '<guid>' . 'alert-lowstock-' . $resource['id'] . '-' . time() . '</guid>' . "\n";
            echo '</item>' . "\n";
        }
        
        echo '</channel>' . "\n";
        echo '</rss>' . "\n";
    }
}
?>
