<?php
/**
 * Email utility functions for CaS application
 * Handles email sending for contact forms and notifications
 */

/**
 * Send contact form notification email
 */
function sendContactNotification($data) {
    $to = 'contact@cas-simulator.ro';
    $subject = 'Mesaj nou de la ' . $data['name'];
    
    // Create email body
    $body = createContactEmailBody($data);
    
    // Headers
    $headers = [
        'From: noreply@cas-simulator.ro',
        'Reply-To: ' . $data['email'],
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    // Send email
    $success = mail($to, $subject, $body, implode("\r\n", $headers));
    
    if ($success) {
        // Log successful email
        error_log("Contact email sent successfully to: $to");
        
        // Send auto-reply to customer
        sendContactAutoReply($data);
    } else {
        error_log("Failed to send contact email to: $to");
    }
    
    return $success;
}

/**
 * Send auto-reply to customer
 */
function sendContactAutoReply($data) {
    $to = $data['email'];
    $subject = 'Confirmarea primirii mesajului - CaS Cleaning Services';
    
    $body = createAutoReplyBody($data);
    
    $headers = [
        'From: contact@cas-simulator.ro',
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    $success = mail($to, $subject, $body, implode("\r\n", $headers));
    
    if (!$success) {
        error_log("Failed to send auto-reply to: $to");
    }
    
    return $success;
}

/**
 * Create HTML email body for contact notification
 */
function createContactEmailBody($data) {
    $phone = !empty($data['phone']) ? $data['phone'] : 'Nu a fost furnizat';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Mesaj nou de contact</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3498db; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2c3e50; }
            .value { background: white; padding: 10px; border-radius: 5px; margin-top: 5px; }
            .footer { text-align: center; padding: 20px; color: #7f8c8d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>CaS - Mesaj nou de contact</h1>
            </div>
            
            <div class='content'>
                <div class='field'>
                    <div class='label'>Nume:</div>
                    <div class='value'>" . htmlspecialchars($data['name']) . "</div>
                </div>
                
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>" . htmlspecialchars($data['email']) . "</div>
                </div>
                
                <div class='field'>
                    <div class='label'>Telefon:</div>
                    <div class='value'>" . htmlspecialchars($phone) . "</div>
                </div>
                
                <div class='field'>
                    <div class='label'>Subiect:</div>
                    <div class='value'>" . htmlspecialchars($data['subject']) . "</div>
                </div>
                
                <div class='field'>
                    <div class='label'>Mesaj:</div>
                    <div class='value'>" . nl2br(htmlspecialchars($data['message'])) . "</div>
                </div>
                
                <div class='field'>
                    <div class='label'>Data trimiterii:</div>
                    <div class='value'>" . date('d.m.Y H:i:s') . "</div>
                </div>
            </div>
            
            <div class='footer'>
                <p>Acest email a fost generat automat de sistemul CaS.</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Create auto-reply email body
 */
function createAutoReplyBody($data) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Confirmarea primirii mesajului</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #27ae60; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .highlight { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .contact-info { background: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #7f8c8d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>CaS - Cleaning Services</h1>
                <p>Mulțumim pentru mesajul dumneavoastră!</p>
            </div>
            
            <div class='content'>
                <p>Stimate/ă <strong>" . htmlspecialchars($data['name']) . "</strong>,</p>
                
                <div class='highlight'>
                    <p><strong>✓ Mesajul dumneavoastră a fost primit cu succes!</strong></p>
                    <p>Subiect: " . htmlspecialchars($data['subject']) . "</p>
                    <p>Data: " . date('d.m.Y H:i:s') . "</p>
                </div>
                
                <p>Vă mulțumim că ne-ați contactat! Echipa noastră va analiza mesajul dumneavoastră și vă va răspunde în cel mai scurt timp posibil, de obicei în termen de <strong>24 de ore</strong>.</p>
                
                <div class='contact-info'>
                    <h3>Informații de contact:</h3>
                    <p><strong>📞 Telefon:</strong> +40 123 456 789</p>
                    <p><strong>✉️ Email:</strong> contact@cas-simulator.ro</p>
                    <p><strong>📍 Adresa:</strong> Strada Unirii Nr. 25, București</p>
                    <p><strong>🕒 Program:</strong> Luni - Vineri: 08:00 - 18:00</p>
                </div>
                
                <h3>Serviciile noastre includ:</h3>
                <ul>
                    <li>🏠 Spălarea și curățarea covoarelor</li>
                    <li>🚗 Spălarea și detailing autoturisme</li>
                    <li>👕 Curățenia îmbrăcămintei și textilelor</li>
                    <li>🚚 Transport gratuit la domiciliu</li>
                </ul>
                
                <p>Pentru urgențe, ne puteți contacta la numărul <strong>+40 724 456 789</strong> (disponibil 24/7).</p>
                
                <p>Cu stimă,<br>
                <strong>Echipa CaS - Cleaning Services</strong></p>
            </div>
            
            <div class='footer'>
                <p>Acest email a fost generat automat. Vă rugăm să nu răspundeți la această adresa.</p>
                <p>Pentru întrebări, folosiți adresa: contact@cas-simulator.ro</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Validate email configuration
 */
function isEmailConfigured() {
    // Check if mail function is available
    if (!function_exists('mail')) {
        return false;
    }
    
    // Check basic PHP mail configuration
    $required_settings = [
        'sendmail_path',
        'smtp_port'
    ];
    
    foreach ($required_settings as $setting) {
        if (ini_get($setting) === false) {
            error_log("Email setting not configured: $setting");
        }
    }
    
    return true;
}

/**
 * Send test email to verify configuration
 */
function sendTestEmail($to = 'test@cas-simulator.ro') {
    if (!isEmailConfigured()) {
        return false;
    }
    
    $subject = 'Test Email - CaS System';
    $body = '
    <html>
    <body>
        <h2>Test Email</h2>
        <p>Acesta este un email de test pentru verificarea configurației sistemului de email.</p>
        <p>Data: ' . date('d.m.Y H:i:s') . '</p>
        <p>Sistem: CaS - Cleaning Web Simulator</p>
    </body>
    </html>';
    
    $headers = [
        'From: noreply@cas-simulator.ro',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Log email activity
 */
function logEmailActivity($type, $to, $subject, $success) {
    $status = $success ? 'SUCCESS' : 'FAILED';
    $log_message = sprintf(
        "[%s] Email %s - Type: %s, To: %s, Subject: %s",
        date('Y-m-d H:i:s'),
        $status,
        $type,
        $to,
        $subject
    );
    
    error_log($log_message);
}

/**
 * Queue email for later sending (basic implementation)
 */
function queueEmail($type, $data) {
    $queue_file = '../app/temp/email_queue.json';
    
    // Ensure temp directory exists
    $temp_dir = dirname($queue_file);
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0755, true);
    }
    
    $email_data = [
        'type' => $type,
        'data' => $data,
        'created_at' => date('Y-m-d H:i:s'),
        'attempts' => 0,
        'status' => 'pending'
    ];
    
    // Load existing queue
    $queue = [];
    if (file_exists($queue_file)) {
        $content = file_get_contents($queue_file);
        $queue = json_decode($content, true) ?: [];
    }
    
    // Add new email to queue
    $queue[] = $email_data;
    
    // Save queue
    file_put_contents($queue_file, json_encode($queue, JSON_PRETTY_PRINT));
    
    return true;
}

/**
 * Process email queue (can be called by cron job)
 */
function processEmailQueue() {
    $queue_file = '../app/temp/email_queue.json';
    
    if (!file_exists($queue_file)) {
        return;
    }
    
    $content = file_get_contents($queue_file);
    $queue = json_decode($content, true) ?: [];
    
    $processed = [];
    $remaining = [];
    
foreach ($queue as $email) {
    // You need to implement the logic for processing each email in the queue here.
    // For now, let's just move all emails to processed for demonstration.
    $processed[] = $email;
}

// Optionally, clear the queue after processing
file_put_contents($queue_file, json_encode($remaining, JSON_PRETTY_PRINT));

return count($processed); // Return number of processed emails
}

/**
 * Clean old queue entries
 */
function cleanEmailQueue($days = 7) {
    $queue_file = '../app/temp/email_queue.json';
    
    if (!file_exists($queue_file)) {
        return;
    }
    
    $content = file_get_contents($queue_file);
    $queue = json_decode($content, true) ?: [];
    
    $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    $cleaned_queue = [];
    
    foreach ($queue as $email) {
        if ($email['created_at'] > $cutoff_date) {
            $cleaned_queue[] = $email;
        }
    }
    
    file_put_contents($queue_file, json_encode($cleaned_queue, JSON_PRETTY_PRINT));
    
    return count($queue) - count($cleaned_queue); // Return number of cleaned entries
}

/**
 * Send notification email for new orders
 */
function sendOrderNotification($orderData) {
    $to = 'orders@cas-simulator.ro';
    $subject = 'Comandă nouă #' . $orderData['id'];
    
    $body = createOrderEmailBody($orderData);
    
    $headers = [
        'From: noreply@cas-simulator.ro',
        'Reply-To: contact@cas-simulator.ro',
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    $success = mail($to, $subject, $body, implode("\r\n", $headers));
    
    logEmailActivity('order', $to, $subject, $success);
    
    return $success;
}

/**
 * Create HTML email body for order notification
 */
function createOrderEmailBody($orderData) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Comandă nouă</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #e67e22; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .order-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .field { margin-bottom: 10px; }
            .label { font-weight: bold; color: #2c3e50; }
            .value { margin-left: 10px; }
            .highlight { background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107; }
            .footer { text-align: center; padding: 20px; color: #7f8c8d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>CaS - Comandă Nouă</h1>
                <p>Comandă #" . htmlspecialchars($orderData['id']) . "</p>
            </div>
            
            <div class='content'>
                <div class='highlight'>
                    <strong>🎯 O comandă nouă a fost plasată în sistem!</strong>
                </div>
                
                <div class='order-info'>
                    <h3>Detalii Comandă:</h3>
                    <div class='field'>
                        <span class='label'>ID Comandă:</span>
                        <span class='value'>#" . htmlspecialchars($orderData['id']) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Tip Serviciu:</span>
                        <span class='value'>" . ucfirst(htmlspecialchars($orderData['TipServiciu'])) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Data Programare:</span>
                        <span class='value'>" . htmlspecialchars($orderData['DataProgramare']) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Transport:</span>
                        <span class='value'>" . ($orderData['Transport'] ? 'Da' : 'Nu') . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Recurentă:</span>
                        <span class='value'>" . ($orderData['Recurenta'] ? 'Da' : 'Nu') . "</span>
                    </div>
                </div>
                
                <div class='order-info'>
                    <h3>Informații Client:</h3>
                    <div class='field'>
                        <span class='label'>Nume:</span>
                        <span class='value'>" . htmlspecialchars($orderData['client_name']) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Email:</span>
                        <span class='value'>" . htmlspecialchars($orderData['client_email']) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Telefon:</span>
                        <span class='value'>" . htmlspecialchars($orderData['client_phone'] ?? 'N/A') . "</span>
                    </div>
                </div>
                
                <div class='order-info'>
                    <h3>Sediu Alocat:</h3>
                    <div class='field'>
                        <span class='label'>Nume Sediu:</span>
                        <span class='value'>" . htmlspecialchars($orderData['sediu_name']) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Adresa:</span>
                        <span class='value'>" . htmlspecialchars($orderData['sediu_address']) . "</span>
                    </div>
                </div>
                
                <div class='highlight'>
                    <p><strong>⏰ Acțiuni necesare:</strong></p>
                    <ul>
                        <li>Verificați disponibilitatea resurselor</li>
                        <li>Contactați clientul pentru confirmare</li>
                        <li>Planificați echipa și echipamentele</li>
                    </ul>
                </div>
            </div>
            
            <div class='footer'>
                <p>Pentru gestionarea comenzii, accesați panoul de administrare CaS.</p>
                <p>Data generării: " . date('d.m.Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Send status update email to client
 */
function sendOrderStatusUpdate($orderData, $newStatus) {
    $to = $orderData['client_email'];
    $subject = 'Actualizare status comandă #' . $orderData['id'] . ' - CaS';
    
    $body = createStatusUpdateEmailBody($orderData, $newStatus);
    
    $headers = [
        'From: noreply@cas-simulator.ro',
        'Reply-To: contact@cas-simulator.ro',
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    $success = mail($to, $subject, $body, implode("\r\n", $headers));
    
    logEmailActivity('status_update', $to, $subject, $success);
    
    return $success;
}

/**
 * Create status update email body
 */
function createStatusUpdateEmailBody($orderData, $newStatus) {
    $statusMessages = [
        'noua' => ['🆕 Comandă înregistrată', 'Comanda dumneavoastră a fost înregistrată cu succes în sistemul nostru.'],
        'in curs' => ['⚙️ Comandă în proces', 'Echipa noastră a început procesarea comenzii dumneavoastră.'],
        'finalizata' => ['✅ Comandă finalizată', 'Comanda dumneavoastră a fost finalizată cu succes!'],
        'anulata' => ['❌ Comandă anulată', 'Comanda dumneavoastră a fost anulată.']
    ];
    
    $statusInfo = $statusMessages[$newStatus] ?? ['📋 Status actualizat', 'Statusul comenzii a fost actualizat.'];
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Actualizare comandă</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3498db; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .status-update { background: #e8f5e8; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0; }
            .order-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .contact-info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #7f8c8d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>CaS - Cleaning Services</h1>
                <p>Actualizare comandă #" . htmlspecialchars($orderData['id']) . "</p>
            </div>
            
            <div class='content'>
                <p>Stimate/ă <strong>" . htmlspecialchars($orderData['client_name']) . "</strong>,</p>
                
                <div class='status-update'>
                    <h2>" . $statusInfo[0] . "</h2>
                    <p>" . $statusInfo[1] . "</p>
                    <p><strong>Data actualizării:</strong> " . date('d.m.Y H:i:s') . "</p>
                </div>
                
                <div class='order-details'>
                    <h3>Detalii comandă:</h3>
                    <p><strong>Serviciu:</strong> " . ucfirst(htmlspecialchars($orderData['TipServiciu'])) . "</p>
                    <p><strong>Data programare:</strong> " . htmlspecialchars($orderData['DataProgramare']) . "</p>
                    <p><strong>Sediu:</strong> " . htmlspecialchars($orderData['sediu_name']) . "</p>
                    <p><strong>Transport:</strong> " . ($orderData['Transport'] ? 'Inclus' : 'Nu') . "</p>
                </div>";
    
    if ($newStatus === 'finalizata') {
        $body .= "
                <div class='contact-info'>
                    <h3>🌟 Mulțumim că ați ales serviciile noastre!</h3>
                    <p>Sperăm că sunteți mulțumit de calitatea serviciilor noastre. Feedback-ul dumneavoastră este foarte important pentru noi.</p>
                    <p>Pentru evaluarea serviciului, vă rugăm să ne contactați la: <strong>contact@cas-simulator.ro</strong></p>
                </div>";
    } elseif ($newStatus === 'anulata') {
        $body .= "
                <div class='contact-info'>
                    <h3>Ne pare rău că ați anulat comanda</h3>
                    <p>Dacă doriți să reprogramați serviciul sau aveți întrebări, nu ezitați să ne contactați.</p>
                </div>";
    }
    
    $body .= "
                <div class='contact-info'>
                    <h3>Informații de contact:</h3>
                    <p><strong>📞 Telefon:</strong> +40 123 456 789</p>
                    <p><strong>✉️ Email:</strong> contact@cas-simulator.ro</p>
                    <p><strong>🕒 Program:</strong> Luni - Vineri: 08:00 - 18:00</p>
                </div>
                
                <p>Cu stimă,<br>
                <strong>Echipa CaS - Cleaning Services</strong></p>
            </div>
            
            <div class='footer'>
                <p>Acest email a fost generat automat de sistemul CaS.</p>
                <p>Pentru întrebări, contactați-ne la contact@cas-simulator.ro</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $body;
}

/**
 * Validate email address format
 */
function validateEmailAddress($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Create email template with common styling
 */
function createEmailTemplate($title, $content, $headerColor = '#3498db') {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$title}</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background-color: #f4f4f4; 
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background-color: #ffffff; 
                box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            }
            .header { 
                background: {$headerColor}; 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 24px; 
            }
            .content { 
                padding: 30px 20px; 
            }
            .footer { 
                background: #2c3e50; 
                color: #bdc3c7; 
                text-align: center; 
                padding: 20px; 
                font-size: 14px; 
            }
            .button { 
                display: inline-block; 
                background: {$headerColor}; 
                color: white; 
                padding: 12px 25px; 
                text-decoration: none; 
                border-radius: 5px; 
                margin: 10px 0; 
            }
            .highlight { 
                background: #f8f9fa; 
                padding: 15px; 
                border-left: 4px solid {$headerColor}; 
                margin: 15px 0; 
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>CaS - Cleaning Services</h1>
            </div>
            <div class='content'>
                {$content}
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " CaS - Cleaning Web Simulator. Toate drepturile rezervate.</p>
                <p>📧 contact@cas-simulator.ro | 📞 +40 123 456 789</p>
            </div>
        </div>
    </body>
    </html>";
}
?>