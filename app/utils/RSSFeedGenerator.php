<?php
/**
 * RSSGenerator - generare feed-uri RSS pentru starea sediilor
 * Conform cerintelor: consultarea starii sediilor prin RSS
 */
require_once 'app/models/SediuModel.php';
require_once 'app/models/ComandaModel.php';
require_once 'app/models/ResourceModel.php';

class RSSGenerator {

    private static function escapeXmlUrl($url) {
        return htmlspecialchars($url, ENT_XML1, 'UTF-8');
    }
    
    /**
     * Genereaza RSS feed
     */
    public static function generate($type) {
        // Seteaza header-ul doar daca nu a fost deja trimis
        if (!headers_sent()) {
            header('Content-Type: application/rss+xml; charset=utf-8');
        }
        
        try {
            $sediuModel = new SediuModel();
            $comandaModel = new ComandaModel();
            $resourceModel = new ResourceModel();
            
            $rss = new DOMDocument('1.0', 'UTF-8');
            $rss->formatOutput = true;
            
            // Element RSS root
            $rssElement = $rss->createElement('rss');
            $rssElement->setAttribute('version', '2.0');
            $rss->appendChild($rssElement);
            
            // Element channel
            $channel = $rss->createElement('channel');
            $rssElement->appendChild($channel);
            
            switch ($type) {
                case 'sedii':
                    self::generateSediiRSS($rss, $channel, $sediuModel);
                    break;
                case 'statistici':
                    self::generateStatisticiRSS($rss, $channel, $comandaModel, $sediuModel);
                    break;
                case 'resurse':
                    self::generateResurseRSS($rss, $channel, $resourceModel);
                    break;
                default:
                    self::generateGeneralRSS($rss, $channel, $sediuModel, $comandaModel);
            }
            
            echo $rss->saveXML();
            
        } catch (Exception $e) {
            error_log("RSS Generation Error: " . $e->getMessage());
            self::generateErrorRSS();
        }
    }
    
    /**
     * RSS pentru starea sediilor (monitorizare timp real)
     */
    private static function generateSediiRSS($rss, $channel, $sediuModel) {
        // Metadata channel
        self::addChannelInfo($rss, $channel, 'Stare Sedii CaS', 'Status sediilor de spalatorie in timp real');
        
        $sedii = $sediuModel->getAllSedii();
        
        foreach ($sedii as $sediu) {
            $item = $rss->createElement('item');
            
            $title = $rss->createElement('title', 'Sediul: ' . htmlspecialchars($sediu['Nume']));
            $item->appendChild($title);
            
            // Obtine statistici pentru sediu
            $stats = $sediuModel->getSediuStats($sediu['id']);
            
            $description = "Status: " . ucfirst($sediu['Stare']) . "\n";
            $description .= "Adresa: " . ($sediu['Adresa'] ?? 'N/A') . "\n";
            $description .= "Total comenzi: " . ($sediu['total_comenzi'] ?? 0) . "\n";
            $description .= "Comenzi astazi: " . ($stats['comenzi_astazi'] ?? 0) . "\n";
            $description .= "Eficienta: " . number_format($stats['rata_finalizare'] ?? 0, 1) . "%\n";
            $description .= "Ultima actualizare: " . date('d.m.Y H:i:s');
            
            $descElement = $rss->createElement('description', htmlspecialchars($description));
            $item->appendChild($descElement);
            
            // Link catre detaliile sediului
            $link = $rss->createElement('link', self::escapeXmlUrl(BASE_URL . '?controller=public&action=getLocationDetails&id=' . $sediu['id']));            $item->appendChild($link);
            
            $guid = $rss->createElement('guid', 'sediu-' . $sediu['id'] . '-' . time());
            $guid->setAttribute('isPermaLink', 'false');
            $item->appendChild($guid);
            
            $pubDate = $rss->createElement('pubDate', date('r'));
            $item->appendChild($pubDate);
            
            // Categorii pentru tipurile de servicii
            $categorii = ['covor', 'auto', 'textil'];
            foreach ($categorii as $categorie) {
                $category = $rss->createElement('category', $categorie);
                $item->appendChild($category);
            }
            
            $channel->appendChild($item);
        }
    }
    
    /**
     * RSS pentru statistici sistem
     */
    private static function generateStatisticiRSS($rss, $channel, $comandaModel, $sediuModel) {
        self::addChannelInfo($rss, $channel, 'Statistici CaS', 'Statistici comenzi si activitate spalatorii');
        
        $stats = $comandaModel->getStatsGeneral();
        $sedii = $sediuModel->getAllSedii();
        
        // Item pentru statistici generale
        $item = $rss->createElement('item');
        
        $title = $rss->createElement('title', 'Statistici Sistem - ' . date('d.m.Y H:i'));
        $item->appendChild($title);
        
        $description = "Statistici Generale Spalatorii:\n";
        $description .= "Total sedii: " . count($sedii) . "\n";
        $description .= "Sedii active: " . count(array_filter($sedii, function($s) { return $s['Stare'] === 'activ'; })) . "\n";
        $description .= "Total comenzi: " . ($stats['total_comenzi'] ?? 0) . "\n";
        $description .= "Comenzi noi: " . ($stats['comenzi_noi'] ?? 0) . "\n";
        $description .= "Comenzi in curs: " . ($stats['comenzi_in_curs'] ?? 0) . "\n";
        $description .= "Comenzi finalizate: " . ($stats['comenzi_finalizate'] ?? 0) . "\n";
        $description .= "Comenzi astazi: " . ($stats['comenzi_astazi'] ?? 0) . "\n";
        $description .= "Ultima actualizare: " . date('d.m.Y H:i:s');
        
        $descElement = $rss->createElement('description', htmlspecialchars($description));
        $item->appendChild($descElement);
        
        $link = $rss->createElement('link', self::escapeXmlUrl(BASE_URL . '?controller=admin&action=dashboard'));        $item->appendChild($link);
        
        $guid = $rss->createElement('guid', 'stats-' . date('Y-m-d-H'));
        $guid->setAttribute('isPermaLink', 'false');
        $item->appendChild($guid);
        
        $pubDate = $rss->createElement('pubDate', date('r'));
        $item->appendChild($pubDate);
        
        $channel->appendChild($item);
        
        // Adauga iteme pentru sediile cu probleme
        foreach ($sedii as $sediu) {
            if ($sediu['Stare'] !== 'activ') {
                $alertItem = $rss->createElement('item');
                
                $alertTitle = $rss->createElement('title', 'ALERT: Sediu ' . htmlspecialchars($sediu['Nume']) . ' - ' . ucfirst($sediu['Stare']));
                $alertItem->appendChild($alertTitle);
                
                $alertDesc = "Sediul " . htmlspecialchars($sediu['Nume']) . " este in starea: " . ucfirst($sediu['Stare']) . "\n";
                $alertDesc .= "Adresa: " . ($sediu['Adresa'] ?? 'N/A') . "\n";
                $alertDesc .= "Necesita atentie pentru reactivare servicii.\n";
                $alertDesc .= "Raport generat: " . date('d.m.Y H:i:s');
                
                $alertDescElement = $rss->createElement('description', htmlspecialchars($alertDesc));
                $alertItem->appendChild($alertDescElement);
                
                $alertPubDate = $rss->createElement('pubDate', date('r'));
                $alertItem->appendChild($alertPubDate);
                
                $channel->appendChild($alertItem);
            }
        }
    }
    
    /**
     * RSS pentru starea resurselor (detergenti, echipamente)
     */
    private static function generateResurseRSS($rss, $channel, $resourceModel) {
        self::addChannelInfo($rss, $channel, 'Resurse CaS', 'Starea resurselor spalatorii');
        
        $resurse_stoc_redus = $resourceModel->getLowStockResources();
        $toate_resursele = $resourceModel->getAllResources();
        
        // Item general pentru starea resurselor
        $item = $rss->createElement('item');
        
        $title = $rss->createElement('title', 'Raport Resurse - ' . date('d.m.Y H:i'));
        $item->appendChild($title);
        
        $description = "Starea Resurselor Spalatorii:\n";
        $description .= "Total resurse: " . count($toate_resursele) . "\n";
        $description .= "Resurse stoc redus: " . count($resurse_stoc_redus) . "\n";
        $description .= "Ultima verificare: " . date('d.m.Y H:i:s');
        
        $descElement = $rss->createElement('description', htmlspecialchars($description));
        $item->appendChild($descElement);
        
        $pubDate = $rss->createElement('pubDate', date('r'));
        $item->appendChild($pubDate);
        
        $channel->appendChild($item);
        
        // Iteme pentru resursele cu stoc redus
        foreach ($resurse_stoc_redus as $resursa) {
            $alertItem = $rss->createElement('item');
            
            $alertTitle = $rss->createElement('title', 'STOC REDUS: ' . htmlspecialchars($resursa['Nume']));
            $alertItem->appendChild($alertTitle);
            
            $alertDesc = "Resursa: " . htmlspecialchars($resursa['Nume']) . "\n";
            $alertDesc .= "Tip: " . htmlspecialchars($resursa['Tip']) . "\n";
            $alertDesc .= "Cantitate disponibila: " . $resursa['CantitateDisponibila'] . "\n";
            $alertDesc .= "Necesita reaprovizionare urgenta!\n";
            $alertDesc .= "Alert generat: " . date('d.m.Y H:i:s');
            
            $alertDescElement = $rss->createElement('description', htmlspecialchars($alertDesc));
            $alertItem->appendChild($alertDescElement);
            
            $alertPubDate = $rss->createElement('pubDate', date('r'));
            $alertItem->appendChild($alertPubDate);
            
            $channel->appendChild($alertItem);
        }
    }
    
    /**
     * RSS general - overview sistem
     */
    private static function generateGeneralRSS($rss, $channel, $sediuModel, $comandaModel) {
        self::addChannelInfo($rss, $channel, 'CaS - Feed General', 'Informatii complete sistem spalatorii');
        
        // Status general sistem
        $stats = $comandaModel->getStatsGeneral();
        $sedii = $sediuModel->getAllSedii();
        $sedii_active = $sediuModel->getSediiActive();
        
        $statsItem = $rss->createElement('item');
        
        $title = $rss->createElement('title', 'Status General Sistem Spalatorii');
        $statsItem->appendChild($title);
        
        $description = "Status General CaS:\n";
        $description .= "Total sedii: " . count($sedii) . "\n";
        $description .= "Sedii operationale: " . count($sedii_active) . "\n";
        $description .= "Total comenzi: " . ($stats['total_comenzi'] ?? 0) . "\n";
        $description .= "Comenzi astazi: " . ($stats['comenzi_astazi'] ?? 0) . "\n";
        $description .= "Comenzi active: " . (($stats['comenzi_noi'] ?? 0) + ($stats['comenzi_in_curs'] ?? 0)) . "\n";
        $description .= "Eficienta generala: " . self::calculateGeneralEfficiency($stats) . "%\n";
        $description .= "Actualizat: " . date('d.m.Y H:i:s');
        
        $descElement = $rss->createElement('description', htmlspecialchars($description));
        $statsItem->appendChild($descElement);
        
        $link = $rss->createElement('link', self::escapeXmlUrl(BASE_URL));
        $statsItem->appendChild($link);
        
        $pubDate = $rss->createElement('pubDate', date('r'));
        $statsItem->appendChild($pubDate);
        
        $channel->appendChild($statsItem);
        
        // Adauga sedii cu activitate mare
        foreach (array_slice($sedii_active, 0, 3) as $sediu) {
            $sediuStats = $sediuModel->getSediuStats($sediu['id']);
            
            $item = $rss->createElement('item');
            
            $title = $rss->createElement('title', 'Sediu Activ: ' . htmlspecialchars($sediu['Nume']));
            $item->appendChild($title);
            
            $description = "Adresa: " . ($sediu['Adresa'] ?? 'N/A') . "\n";
            $description .= "Status: Operational\n";
            $description .= "Comenzi astazi: " . ($sediuStats['comenzi_astazi'] ?? 0) . "\n";
            $description .= "Servicii: Covoare, Auto, Textile";
            
            $descElement = $rss->createElement('description', htmlspecialchars($description));
            $item->appendChild($descElement);
            
            $pubDate = $rss->createElement('pubDate', date('r'));
            $item->appendChild($pubDate);
            
            $channel->appendChild($item);
        }
    }
    
    /**
     * Adauga informatii channel
     */
    private static function addChannelInfo($rss, $channel, $title, $description) {
        $titleEl = $rss->createElement('title', htmlspecialchars($title));
        $channel->appendChild($titleEl);
        
        $descEl = $rss->createElement('description', htmlspecialchars($description));
        $channel->appendChild($descEl);
        
        $linkEl = $rss->createElement('link', self::escapeXmlUrl(BASE_URL));
        $channel->appendChild($linkEl);
        
        $langEl = $rss->createElement('language', 'ro-RO');
        $channel->appendChild($langEl);
        
        $dateEl = $rss->createElement('lastBuildDate', date('r'));
        $channel->appendChild($dateEl);
        
        $generatorEl = $rss->createElement('generator', 'CaS - Sistem Spalatorii v1.0');
        $channel->appendChild($generatorEl);
        
        $ttlEl = $rss->createElement('ttl', '30'); // Refresh la 30 minute pentru timp real
        $channel->appendChild($ttlEl);
        
        // Copyright
        $copyrightEl = $rss->createElement('copyright', 'Copyright ' . date('Y') . ' CaS - Cleaning Web Simulator');
        $channel->appendChild($copyrightEl);
        
        // Managing Editor
        $editorEl = $rss->createElement('managingEditor', 'admin@cas-simulator.ro (CaS Admin)');
        $channel->appendChild($editorEl);
    }
    
    /**
     * Calculeaza eficienta generala
     */
    private static function calculateGeneralEfficiency($stats) {
        $total = $stats['total_comenzi'] ?? 0;
        $finalizate = $stats['comenzi_finalizate'] ?? 0;
        
        if ($total == 0) return 0;
        
        return number_format(($finalizate / $total) * 100, 1);
    }
    
    /**
     * Genereaza RSS de eroare
     */
    private static function generateErrorRSS() {
        $rss = new DOMDocument('1.0', 'UTF-8');
        $rss->formatOutput = true;
        
        $rssElement = $rss->createElement('rss');
        $rssElement->setAttribute('version', '2.0');
        $rss->appendChild($rssElement);
        
        $channel = $rss->createElement('channel');
        $rssElement->appendChild($channel);
        
        $title = $rss->createElement('title', 'CaS - Eroare RSS');
        $channel->appendChild($title);
        
        $description = $rss->createElement('description', 'Eroare la generarea feed-ului RSS pentru sistemul de spalatorii');
        $channel->appendChild($description);
        
        $link = $rss->createElement('link', BASE_URL);
        $channel->appendChild($link);
        
        $item = $rss->createElement('item');
        $itemTitle = $rss->createElement('title', 'Eroare Sistema RSS');
        $itemDesc = $rss->createElement('description', 'A aparut o eroare la generarea feed-ului RSS. Va rugam contactati administratorul.');
        $itemDate = $rss->createElement('pubDate', date('r'));
        
        $item->appendChild($itemTitle);
        $item->appendChild($itemDesc);
        $item->appendChild($itemDate);
        $channel->appendChild($item);
        
        echo $rss->saveXML();
    }
}
?>
