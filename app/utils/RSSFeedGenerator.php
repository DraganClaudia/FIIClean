<?php
/**
 * RSSFeedGenerator - generates RSS feeds for location status and statistics
 */
require_once 'app/models/LocationModel.php';
require_once 'app/models/OrderModel.php';

class RSSFeedGenerator {
    private $locationModel;
    private $orderModel;
    
    public function __construct() {
        $this->locationModel = new LocationModel();
        $this->orderModel = new OrderModel();
    }
    
    /**
     * Generate RSS feed for location status
     */
    public function generateLocationsFeed() {
        header('Content-Type: application/rss+xml; charset=utf-8');
        
        try {
            $locations = $this->locationModel->getAllLocations();
            
            $rss = new DOMDocument('1.0', 'UTF-8');
            $rss->formatOutput = true;
            
            // Create RSS root element
            $rss_element = $rss->createElement('rss');
            $rss_element->setAttribute('version', '2.0');
            $rss->appendChild($rss_element);
            
            // Create channel element
            $channel = $rss->createElement('channel');
            $rss_element->appendChild($channel);
            
            // Add channel metadata
            $this->addChannelInfo($rss, $channel, 'Stare Locații CaS', 'Status în timp real al locațiilor de spălătorie');
            
            // Add location items
            foreach ($locations as $location) {
                $this->addLocationItem($rss, $channel, $location);
            }
            
            echo $rss->saveXML();
            
        } catch (Exception $e) {
            error_log("RSS Feed Error: " . $e->getMessage());
            $this->generateErrorFeed('Eroare la generarea feed-ului locațiilor');
        }
    }
    
    /**
     * Generate RSS feed for statistics
     */
    public function generateStatisticsFeed() {
        header('Content-Type: application/rss+xml; charset=utf-8');
        
        try {
            $stats = $this->getSystemStats();
            $recent_orders = $this->orderModel->getRecentOrders(5);
            
            $rss = new DOMDocument('1.0', 'UTF-8');
            $rss->formatOutput = true;
            
            $rss_element = $rss->createElement('rss');
            $rss_element->setAttribute('version', '2.0');
            $rss->appendChild($rss_element);
            
            $channel = $rss->createElement('channel');
            $rss_element->appendChild($channel);
            
            $this->addChannelInfo($rss, $channel, 'Statistici CaS', 'Statistici și activitate în timp real');
            
            // Add statistics item
            $this->addStatsItem($rss, $channel, $stats);
            
            // Add recent orders
            foreach ($recent_orders as $order) {
                $this->addOrderItem($rss, $channel, $order);
            }
            
            echo $rss->saveXML();
            
        } catch (Exception $e) {
            error_log("RSS Feed Error: " . $e->getMessage());
            $this->generateErrorFeed('Eroare la generarea feed-ului statisticilor');
        }
    }
    
    /**
     * Generate combined RSS feed
     */
    public function generateCombinedFeed() {
        header('Content-Type: application/rss+xml; charset=utf-8');
        
        try {
            $locations = $this->locationModel->getAllLocations();
            $recent_orders = $this->orderModel->getRecentOrders(3);
            $stats = $this->getSystemStats();
            
            $rss = new DOMDocument('1.0', 'UTF-8');
            $rss->formatOutput = true;
            
            $rss_element = $rss->createElement('rss');
            $rss_element->setAttribute('version', '2.0');
            $rss->appendChild($rss_element);
            
            $channel = $rss->createElement('channel');
            $rss_element->appendChild($channel);
            
            $this->addChannelInfo($rss, $channel, 'CaS - Feed General', 'Informații complete despre sistem');
            
            // Add statistics
            $this->addStatsItem($rss, $channel, $stats);
            
            // Add locations (only active ones)
            $active_locations = array_filter($locations, function($loc) {
                return $loc['Stare'] === 'activ';
            });
            
            foreach (array_slice($active_locations, 0, 3) as $location) {
                $this->addLocationItem($rss, $channel, $location);
            }
            
            // Add recent orders
            foreach ($recent_orders as $order) {
                $this->addOrderItem($rss, $channel, $order);
            }
            
            echo $rss->saveXML();
            
        } catch (Exception $e) {
            error_log("RSS Feed Error: " . $e->getMessage());
            $this->generateErrorFeed('Eroare la generarea feed-ului general');
        }
    }
    
    /**
     * Add channel information
     */
    private function addChannelInfo($rss, $channel, $title, $description) {
        // Title
        $title_el = $rss->createElement('title', htmlspecialchars($title));
        $channel->appendChild($title_el);
        
        // Description
        $desc_el = $rss->createElement('description', htmlspecialchars($description));
        $channel->appendChild($desc_el);
        
        // Link
        $link_el = $rss->createElement('link', 'http://localhost/CaS_FII-Clean/');
        $channel->appendChild($link_el);
        
        // Language
        $lang_el = $rss->createElement('language', 'ro-RO');
        $channel->appendChild($lang_el);
        
        // Last build date
        $date_el = $rss->createElement('lastBuildDate', date('r'));
        $channel->appendChild($date_el);
        
        // Generator
        $generator_el = $rss->createElement('generator', 'CaS - Cleaning Web Simulator');
        $channel->appendChild($generator_el);
        
        // TTL (Time to Live)
        $ttl_el = $rss->createElement('ttl', '60');
        $channel->appendChild($ttl_el);
    }
    
    /**
     * Add location item to RSS feed
     */
    private function addLocationItem($rss, $channel, $location) {
        $item = $rss->createElement('item');
        
        // Title
        $title = $rss->createElement('title', htmlspecialchars('Locația: ' . $location['Nume']));
        $item->appendChild($title);
        
        // Description
        $status_text = $this->getStatusText($location['Stare']);
        $desc_text = "Status: {$status_text}\n";
        $desc_text .= "Adresa: " . ($location['Adresa'] ?? 'N/A') . "\n";
        
        if (!empty($location['Latitudine']) && !empty($location['Longitudine'])) {
            $desc_text .= "Coordonate: {$location['Latitudine']}, {$location['Longitudine']}\n";
        }
        
        $desc_text .= "Ultima actualizare: " . date('d.m.Y H:i:s');
        
        $description = $rss->createElement('description', htmlspecialchars($desc_text));
        $item->appendChild($description);
        
        // GUID
        $guid = $rss->createElement('guid', 'location-' . $location['id'] . '-' . time());
        $guid->setAttribute('isPermaLink', 'false');
        $item->appendChild($guid);
        
        // Publication date
        $pub_date = $rss->createElement('pubDate', date('r'));
        $item->appendChild($pub_date);
        
        // Category
        $category = $rss->createElement('category', htmlspecialchars($status_text));
        $item->appendChild($category);
        
        $channel->appendChild($item);
    }
    
    /**
     * Add statistics item to RSS feed
     */
    private function addStatsItem($rss, $channel, $stats) {
        $item = $rss->createElement('item');
        
        // Title
        $title = $rss->createElement('title', 'Statistici Sistem - ' . date('d.m.Y H:i'));
        $item->appendChild($title);
        
        // Description
        $desc_text = "Statistici Generale\n\n";
        $desc_text .= "Total locații: " . $stats['total_locations'] . "\n";
        $desc_text .= "Comenzi active: " . $stats['active_orders'] . "\n";
        $desc_text .= "Comenzi astăzi: " . $stats['today_orders'] . "\n";
        $desc_text .= "Ultima actualizare: " . date('d.m.Y H:i:s');
        
        $description = $rss->createElement('description', htmlspecialchars($desc_text));
        $item->appendChild($description);
        
        // GUID
        $guid = $rss->createElement('guid', 'stats-' . date('Y-m-d-H'));
        $guid->setAttribute('isPermaLink', 'false');
        $item->appendChild($guid);
        
        // Publication date
        $pub_date = $rss->createElement('pubDate', date('r'));
        $item->appendChild($pub_date);
        
        // Category
        $category = $rss->createElement('category', 'Statistici');
        $item->appendChild($category);
        
        $channel->appendChild($item);
    }
    
    /**
     * Add order item to RSS feed
     */
    private function addOrderItem($rss, $channel, $order) {
        $item = $rss->createElement('item');
        
        // Title
        $service_name = $this->getServiceName($order['TipServiciu']);
        $title = $rss->createElement('title', htmlspecialchars("Comandă #{$order['id']} - {$service_name}"));
        $item->appendChild($title);
        
        // Description
        $status_text = $this->getOrderStatusText($order['Status']);
        $desc_text = "Comandă: #{$order['id']}\n";
        $desc_text .= "Serviciu: {$service_name}\n";
        $desc_text .= "Locație: " . ($order['sediu_name'] ?? 'N/A') . "\n";
        $desc_text .= "Client: " . ($order['client_name'] ?? 'N/A') . "\n";
        $desc_text .= "Data programare: " . ($order['DataProgramare'] ?? 'N/A') . "\n";
        $desc_text .= "Status: {$status_text}\n";
        $desc_text .= "Transport: " . ($order['Transport'] ? 'Da' : 'Nu') . "\n";
        $desc_text .= "Recurentă: " . ($order['Recurenta'] ? 'Da' : 'Nu');
        
        $description = $rss->createElement('description', htmlspecialchars($desc_text));
        $item->appendChild($description);
        
        // GUID
        $guid = $rss->createElement('guid', 'order-' . $order['id']);
        $guid->setAttribute('isPermaLink', 'false');
        $item->appendChild($guid);
        
        // Publication date
        $pub_date = $rss->createElement('pubDate', date('r', strtotime($order['DataProgramare'] ?? 'now')));
        $item->appendChild($pub_date);
        
        // Category
        $category = $rss->createElement('category', htmlspecialchars($service_name));
        $item->appendChild($category);
        
        $channel->appendChild($item);
    }
    
    /**
     * Get system statistics
     */
    private function getSystemStats() {
        try {
            return [
                'total_locations' => $this->locationModel->getTotalLocations(),
                'active_orders' => $this->orderModel->getActiveOrdersCount(),
                'today_orders' => $this->orderModel->getTodayOrdersCount()
            ];
        } catch (Exception $e) {
            return [
                'total_locations' => 0,
                'active_orders' => 0,
                'today_orders' => 0
            ];
        }
    }
    
    /**
     * Get status text
     */
    private function getStatusText($status) {
        $statuses = [
            'activ' => 'Activ',
            'inactiv' => 'Inactiv',
            'reparatii' => 'În reparații'
        ];
        
        return $statuses[$status] ?? $status;
    }
    
    /**
     * Get order status text
     */
    private function getOrderStatusText($status) {
        $statuses = [
            'noua' => 'Nouă',
            'in curs' => 'În curs',
            'finalizata' => 'Finalizată',
            'anulata' => 'Anulată'
        ];
        
        return $statuses[$status] ?? $status;
    }
    
    /**
     * Get service name
     */
    private function getServiceName($service) {
        $services = [
            'covor' => 'Spălare covoare',
            'auto' => 'Spălare auto',
            'textil' => 'Curățenie textile'
        ];
        
        return $services[$service] ?? $service;
    }
    
    /**
     * Generate error RSS feed
     */
    private function generateErrorFeed($error_message) {
        $rss = new DOMDocument('1.0', 'UTF-8');
        $rss->formatOutput = true;
        
        $rss_element = $rss->createElement('rss');
        $rss_element->setAttribute('version', '2.0');
        $rss->appendChild($rss_element);
        
        $channel = $rss->createElement('channel');
        $rss_element->appendChild($channel);
        
        $title = $rss->createElement('title', 'CaS - Eroare RSS Feed');
        $channel->appendChild($title);
        
        $description = $rss->createElement('description', htmlspecialchars($error_message));
        $channel->appendChild($description);
        
        $link = $rss->createElement('link', 'http://localhost/CaS_FII-Clean/');
        $channel->appendChild($link);
        
        // Error item
        $item = $rss->createElement('item');
        $item_title = $rss->createElement('title', 'Eroare RSS Feed');
        $item_desc = $rss->createElement('description', htmlspecialchars($error_message));
        $item_date = $rss->createElement('pubDate', date('r'));
        
        $item->appendChild($item_title);
        $item->appendChild($item_desc);
        $item->appendChild($item_date);
        $channel->appendChild($item);
        
        echo $rss->saveXML();
    }
    
    /**
     * Static method to handle RSS routing
     */
    public static function handleRSSRequest($type) {
        $generator = new self();
        
        switch ($type) {
            case 'locations':
                $generator->generateLocationsFeed();
                break;
            case 'statistics':
                $generator->generateStatisticsFeed();
                break;
            case 'combined':
                $generator->generateCombinedFeed();
                break;
            default:
                $generator->generateErrorFeed('Tip de feed RSS necunoscut');
        }
    }
}