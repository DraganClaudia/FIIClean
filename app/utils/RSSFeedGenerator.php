<?php
/**
 * RSSGenerator - generare feed-uri RSS pentru starea sediilor
 */
require_once 'app/models/SediuModel.php';
require_once 'app/models/ComandaModel.php';

class RSSGenerator {
    
    /**
     * Genereaza RSS feed
     */
    public static function generate($type) {
        header('Content-Type: application/rss+xml; charset=utf-8');
        
        try {
            $sediuModel = new SediuModel();
            $comandaModel = new ComandaModel();
            
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
                    self::generateStatisticiRSS($rss, $channel, $comandaModel);
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
     * RSS pentru sedii
     */
    private static function generateSediiRSS($rss, $channel, $sediuModel) {
        // Metadata channel
        self::addChannelInfo($rss, $channel, 'Stare Sedii CaS', 'Status sediilor de spalatorie');
        
        $sedii = $sediuModel->getAllSedii();
        
        foreach ($sedii as $sediu) {
            $item = $rss->createElement('item');
            
            $title = $rss->createElement('title', 'Sediul: ' . htmlspecialchars($sediu['Nume']));
            $item->appendChild($title);
            
            $description = "Status: " . ucfirst($sediu['Stare']) . "\n";
            $description .= "Adresa: " . ($sediu['Adresa'] ?? 'N/A') . "\n";
            $description .= "Total comenzi: " . ($sediu['total_comenzi'] ?? 0) . "\n";
            $description .= "Ultima actualizare: " . date('d.m.Y H:i:s');
            
            $descElement = $rss->createElement('description', htmlspecialchars($description));
            $item->appendChild($descElement);
            
            $guid = $rss->createElement('guid', 'sediu-' . $sediu['id'] . '-' . time());
            $guid->setAttribute('isPermaLink', 'false');
            $item->appendChild($guid);
            
            $pubDate = $rss->createElement('pubDate', date('r'));
            $item->appendChild($pubDate);
            
            $channel->appendChild($item);
        }
    }
    
    /**
     * RSS pentru statistici
     */
    private static function generateStatisticiRSS($rss, $channel, $comandaModel) {
        self::addChannelInfo($rss, $channel, 'Statistici CaS', 'Statistici comenzi si activitate');
        
        $stats = $comandaModel->getStatsGeneral();
        
        $item = $rss->createElement('item');
        
        $title = $rss->createElement('title', 'Statistici Sistem - ' . date('d.m.Y H:i'));
        $item->appendChild($title);
        
        $description = "Statistici Generale:\n";
        $description .= "Total comenzi: " . ($stats['total_comenzi'] ?? 0) . "\n";
        $description .= "Comenzi noi: " . ($stats['comenzi_noi'] ?? 0) . "\n";
        $description .= "Comenzi in curs: " . ($stats['comenzi_in_curs'] ?? 0) . "\n";
        $description .= "Comenzi finalizate: " . ($stats['comenzi_finalizate'] ?? 0) . "\n";
        $description .= "Comenzi astazi: " . ($stats['comenzi_astazi'] ?? 0) . "\n";
        $description .= "Ultima actualizare: " . date('d.m.Y H:i:s');
        
        $descElement = $rss->createElement('description', htmlspecialchars($description));
        $item->appendChild($descElement);
        
        $guid = $rss->createElement('guid', 'stats-' . date('Y-m-d-H'));
        $guid->setAttribute('isPermaLink', 'false');
        $item->appendChild($guid);
        
        $pubDate = $rss->createElement('pubDate', date('r'));
        $item->appendChild($pubDate);
        
        $channel->appendChild($item);
    }
    
    /**
     * RSS general
     */
    private static function generateGeneralRSS($rss, $channel, $sediuModel, $comandaModel) {
        self::addChannelInfo($rss, $channel, 'CaS - Feed General', 'Informatii complete sistem spalatorii');
        
        // Adauga statistici generale
        $stats = $comandaModel->getStatsGeneral();
        $statsItem = $rss->createElement('item');
        
        $title = $rss->createElement('title', 'Status General Sistem');
        $statsItem->appendChild($title);
        
        $description = "Status General:\n";
        $description .= "Total comenzi: " . ($stats['total_comenzi'] ?? 0) . "\n";
        $description .= "Comenzi astazi: " . ($stats['comenzi_astazi'] ?? 0) . "\n";
        $description .= "Comenzi active: " . (($stats['comenzi_noi'] ?? 0) + ($stats['comenzi_in_curs'] ?? 0)) . "\n";
        $description .= "Actualizat: " . date('d.m.Y H:i:s');
        
        $descElement = $rss->createElement('description', htmlspecialchars($description));
        $statsItem->appendChild($descElement);
        
        $pubDate = $rss->createElement('pubDate', date('r'));
        $statsItem->appendChild($pubDate);
        
        $channel->appendChild($statsItem);
        
        // Adauga cateva sedii active
        $sedii = $sediuModel->getSediiActive();
        foreach (array_slice($sedii, 0, 3) as $sediu) {
            $item = $rss->createElement('item');
            
            $title = $rss->createElement('title', 'Sediu Activ: ' . htmlspecialchars($sediu['Nume']));
            $item->appendChild($title);
            
            $description = "Adresa: " . ($sediu['Adresa'] ?? 'N/A') . "\n";
            $description .= "Status: Activ\n";
            $description .= "Servicii: Covor, Auto, Textil";
            
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
        
        $linkEl = $rss->createElement('link', BASE_URL);
        $channel->appendChild($linkEl);
        
        $langEl = $rss->createElement('language', 'ro-RO');
        $channel->appendChild($langEl);
        
        $dateEl = $rss->createElement('lastBuildDate', date('r'));
        $channel->appendChild($dateEl);
        
        $generatorEl = $rss->createElement('generator', 'CaS - Sistem Spalatorii');
        $channel->appendChild($generatorEl);
        
        $ttlEl = $rss->createElement('ttl', '60');
        $channel->appendChild($ttlEl);
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
        
        $description = $rss->createElement('description', 'Eroare la generarea feed-ului RSS');
        $channel->appendChild($description);
        
        $link = $rss->createElement('link', BASE_URL);
        $channel->appendChild($link);
        
        $item = $rss->createElement('item');
        $itemTitle = $rss->createElement('title', 'Eroare RSS');
        $itemDesc = $rss->createElement('description', 'A aparut o eroare la generarea feed-ului RSS');
        $itemDate = $rss->createElement('pubDate', date('r'));
        
        $item->appendChild($itemTitle);
        $item->appendChild($itemDesc);
        $item->appendChild($itemDate);
        $channel->appendChild($item);
        
        echo $rss->saveXML();
    }
}
