<!DOCTYPE html>
<html lang="ro" prefix="schema: http://schema.org/ xsd: http://www.w3.org/2001/XMLSchema# sa: https://ns.science.ai/">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Scholarly HTML Metadata -->
    <title property="schema:name">FII-Clean: Specificații de Sistem și Cerințe Funcționale</title>
    
    <!-- Authors and Contributors -->
    <meta name="author" content="Dragan Claudia, Bujoreanu Teodor">
    <meta name="description" content="Specificațiile sistemului FII-Clean conform Scholarly">
    
    <!-- Dublin Core Metadata -->
    <meta name="DC.title" content="FII-Clean System Requirements Specification">
    <meta name="DC.creator" content="FII-Clean Development Team">
    <meta name="DC.subject" content="Web Application, Cleaning Services, System Requirements">
    <meta name="DC.description" content="System requirements specification for FII-Clean platform">
    <meta name="DC.type" content="Technical Specification">
    <meta name="DC.format" content="text/html">
    <meta name="DC.language" content="ro">
    <meta name="DC.date" content="2025-01-26">
    
    <!-- Schema.org structured data -->
    <script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "TechnicalArticle",
        "headline": "FII-Clean: Specificații de Sistem",
        "author": {
            "@type": "Organization",
            "name": "FII-Clean Development Team"
        },
        "datePublished": "2025-01-26",
        "dateModified": "2025-01-26",
        "description": "Specificațiile complete ale sistemului FII-Clean conform standardelor IEEE",
        "keywords": ["Web Application", "PHP", "MySQL", "REST API", "RSS", "AJAX"],
        "about": {
            "@type": "SoftwareApplication",
            "name": "FII-Clean",
            "applicationCategory": "Business Application",
            "operatingSystem": "Web-based"
        }
    }
    </script>
    
    <!-- Academic Paper Styling -->
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #06b6d4;
            --text-color: #1f2937;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
        }
        
        body {
            font-family: 'Times New Roman', 'Libre Baskerville', serif;
            line-height: 1.6;
            color: var(--text-color);
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            background: #ffffff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .paper-header {
            text-align: center;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 2rem;
            margin-bottom: 3rem;
        }
        
        .paper-title {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .paper-subtitle {
            font-size: 1.2rem;
            color: var(--secondary-color);
            font-style: italic;
            margin-bottom: 1.5rem;
        }
        
        .authors {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        .affiliation {
            font-size: 1rem;
            color: #666;
            font-style: italic;
        }
        
        .date {
            margin-top: 1rem;
            font-size: 0.95rem;
            color: #666;
        }
        
        .abstract {
            background: var(--light-bg);
            padding: 2rem;
            border-radius: 8px;
            margin: 2rem 0;
            border-left: 4px solid var(--primary-color);
        }
        
        .abstract h2 {
            margin-top: 0;
            color: var(--primary-color);
        }
        
        .section {
            margin: 2rem 0;
        }
        
        .section h1 {
            font-size: 1.6rem;
            color: var(--primary-color);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .section h2 {
            font-size: 1.3rem;
            color: var(--secondary-color);
            margin-top: 1.5rem;
            margin-bottom: 0.8rem;
        }
        
        .section h3 {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.95rem;
        }
        
        th, td {
            border: 1px solid var(--border-color);
            padding: 0.8rem;
            text-align: left;
        }
        
        th {
            background: var(--light-bg);
            font-weight: bold;
            color: var(--primary-color);
        }
        
        tr:nth-child(even) {
            background: #fafafa;
        }
        
        code {
            background: #f4f4f4;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        ul, ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }
        
        li {
            margin-bottom: 0.3rem;
        }
        
        .requirement {
            background: #fff;
            border: 2px solid var(--accent-color);
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .requirement-header {
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .nav-back {
            position: fixed;
            top: 20px;
            left: 20px;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .nav-back:hover {
            background: var(--secondary-color);
            color: white;
        }
        
        .tech-stack {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
                margin: 0;
            }
            
            .paper-title {
                font-size: 1.8rem;
            }
            
            table {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <a href="?controller=public&action=home" class="nav-back">← Înapoi la FII-Clean</a>
    
    <!-- Paper Header -->
    <header class="paper-header">
        <h1 class="paper-title">FII-Clean</h1>
        <p class="paper-subtitle">Sistem Web pentru Managementul Serviciilor de Spălătorie</p>
        <p class="authors"><strong>Echipa FII-Clean Development</strong></p>
        <p class="affiliation">Facultatea de Informatică, Universitatea "Alexandru Ioan Cuza" Iași</p>
        <p class="date">Data publicării: <time datetime="2025-01-26">26 ianuarie 2025</time></p>
    </header>

    <!-- Abstract -->
    <section class="abstract">
        <h2>Abstract</h2>
        <p>
            <strong>FII-Clean</strong> este o platformă web dezvoltată în PHP pentru managementul serviciilor de spălătorie. 
            Sistemul implementează arhitectura MVC, servicii web REST, monitorizare în timp real prin AJAX și fluxuri RSS. 
            Platforma gestionează comenzi, sedii, resurse și utilizatori, oferind interfețe diferențiate pentru clienți și administratori.
        </p>
        <p><strong>Cuvinte cheie:</strong> PHP, MySQL, REST API, RSS, AJAX, MVC, Servicii Web</p>
    </section>

    <!-- 1. Prezentare Generală -->
    <section class="section">
        <h1>1. Prezentare Generală</h1>
        
        <p>
            FII-Clean este o aplicație web pentru gestionarea unei rețele de spălătorii care oferă:
        </p>
        <ul>
            <li>Spălare și curățare covoare</li>
            <li>Spălare și detailing autoturisme</li>
            <li>Curățenie îmbrăcăminte și textile</li>
            <li>Transport la domiciliu</li>
        </ul>
        
        <div class="tech-stack">
            <h3>Stack Tehnologic</h3>
            <table>
                <thead>
                    <tr>
                        <th>Componentă</th>
                        <th>Tehnologie</th>
                        <th>Versiune/Standard</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Backend</td>
                        <td>PHP</td>
                        <td>8.0+</td>
                    </tr>
                    <tr>
                        <td>Baza de Date</td>
                        <td>MySQL</td>
                        <td>8.4</td>
                    </tr>
                    <tr>
                        <td>Frontend</td>
                        <td>HTML5, CSS3, JavaScript</td>
                        <td>ES6+</td>
                    </tr>
                    <tr>
                        <td>API</td>
                        <td>REST</td>
                        <td>JSON, HTTP/1.1</td>
                    </tr>
                    <tr>
                        <td>Feeds</td>
                        <td>RSS</td>
                        <td>2.0</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 2. Arhitectura Sistemului -->
    <section class="section">
        <h1>2. Arhitectura Sistemului</h1>
        
        <h2>2.1 Arhitectura MVC</h2>
        <p>Implementează pattern-ul Model-View-Controller pentru separarea responsabilităților:</p>
        
        <table>
            <thead>
                <tr>
                    <th>Componentă</th>
                    <th>Responsabilitate</th>
                    <th>Fișiere</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Model</td>
                    <td>Logica de business și acces la date</td>
                    <td>app/models/*.php</td>
                </tr>
                <tr>
                    <td>View</td>
                    <td>Interfețe utilizator și template-uri</td>
                    <td>app/views/*.php</td>
                </tr>
                <tr>
                    <td>Controller</td>
                    <td>Coordonarea și routing</td>
                    <td>app/controllers/*.php</td>
                </tr>
            </tbody>
        </table>
        
        <h2>2.2 Baza de Date</h2>
        <p>Schema MySQL normalizată cu tabele principale:</p>
        
        <table>
            <thead>
                <tr>
                    <th>Tabelă</th>
                    <th>Descriere</th>
                    <th>Câmpuri Cheie</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>client</td>
                    <td>Utilizatori și autentificare</td>
                    <td>id, username, email, parola, rol</td>
                </tr>
                <tr>
                    <td>sediu</td>
                    <td>Locații spălătorii</td>
                    <td>idSediu, Nume, Adresa, Stare</td>
                </tr>
                <tr>
                    <td>comanda</td>
                    <td>Comenzi servicii</td>
                    <td>idComanda, idClient, idSediu, TipServiciu, Status</td>
                </tr>
                <tr>
                    <td>resursa</td>
                    <td>Inventar materiale</td>
                    <td>id, idSediu, Tip, Nume, CantitateDisponibila</td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- 3. Funcționalități Principale -->
    <section class="section">
        <h1>3. Funcționalități Principale</h1>
        
        <h2>3.1 Gestionarea Utilizatorilor</h2>
        <div class="requirement">
            <div class="requirement-header">Autentificare și Autorizare</div>
            <ul>
                <li>Înregistrare cu validare email și parolă securizată</li>
                <li>Login cu session management</li>
                <li>Roluri: Admin (acces complet) și User (funcții client)</li>
                <li>Protecție CSRF și XSS</li>
            </ul>
        </div>
        
        <h2>3.2 Managementul Comenzilor</h2>
        <div class="requirement">
            <div class="requirement-header">Fluxul Comenzilor</div>
            <ul>
                <li>Creare comenzi pentru servicii: covor, auto, textil</li>
                <li>Statusuri: noua → in curs → finalizata/anulata</li>
                <li>Opțiuni: transport la domiciliu, recurență</li>
                <li>Urmărire în timp real prin AJAX</li>
            </ul>
        </div>
        
        <h2>3.3 Administrarea Sediilor</h2>
        <div class="requirement">
            <div class="requirement-header">Monitorizare Operațională</div>
            <ul>
                <li>Gestionare locații cu coordonate GPS</li>
                <li>Statusuri: activ, reparații, inactiv</li>
                <li>Statistici în timp real: comenzi, eficiență</li>
                <li>Capacitate și disponibilitate</li>
            </ul>
        </div>
        
        <h2>3.4 Managementul Resurselor</h2>
        <div class="requirement">
            <div class="requirement-header">Inventar și Stocuri</div>
            <ul>
                <li>Tipuri: detergenti, echipamente, apă</li>
                <li>Monitorizare cantități per sediu</li>
                <li>Alerte stoc redus prin RSS</li>
                <li>Actualizări în timp real</li>
            </ul>
        </div>
    </section>

    <!-- 4. Servicii Web și API -->
    <section class="section">
        <h1>4. Servicii Web și API</h1>
        
        <h2>4.1 REST API</h2>
        <p>Endpoint-uri RESTful pentru integrări externe:</p>
        
        <table>
            <thead>
                <tr>
                    <th>Endpoint</th>
                    <th>Metodă</th>
                    <th>Funcție</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>/api/sedii</td>
                    <td>GET</td>
                    <td>Lista sedii cu filtrare</td>
                </tr>
                <tr>
                    <td>/api/comenzi</td>
                    <td>GET, POST</td>
                    <td>Gestionare comenzi</td>
                </tr>
                <tr>
                    <td>/api/resurse</td>
                    <td>GET, PUT</td>
                    <td>Monitorizare stocuri</td>
                </tr>
                <tr>
                    <td>/api/statistici</td>
                    <td>GET</td>
                    <td>Metrici performanță</td>
                </tr>
            </tbody>
        </table>
        
        <h2>4.2 Fluxuri RSS</h2>
        <p>Monitorizare în timp real prin RSS 2.0:</p>
        
        <table>
            <thead>
                <tr>
                    <th>Feed</th>
                    <th>Conținut</th>
                    <th>Update Interval</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>?rss=sedii</td>
                    <td>Status sedii și alerte</td>
                    <td>30 minute</td>
                </tr>
                <tr>
                    <td>?rss=statistici</td>
                    <td>Metrici sistem</td>
                    <td>1 oră</td>
                </tr>
                <tr>
                    <td>?rss=resurse</td>
                    <td>Alerte stoc redus</td>
                    <td>15 minute</td>
                </tr>
            </tbody>
        </table>
        
        <h2>4.3 AJAX și Actualizări în Timp Real</h2>
        <ul>
            <li>Dashboard cu metrici live</li>
            <li>Status comenzi fără refresh</li>
            <li>Formulare asincrone</li>
            <li>Notificări push</li>
        </ul>
    </section>

    <!-- 5. Cerințe Tehnice -->
    <section class="section">
        <h1>5. Cerințe Tehnice</h1>
        
        <h2>5.1 Performanță</h2>
        <ul>
            <li>Timp răspuns pagini: < 3 secunde</li>
            <li>API endpoints: < 1 secundă</li>
            <li>Suport 100+ utilizatori concurenți</li>
            <li>Capacitate 10,000 comenzi active</li>
        </ul>
        
        <h2>5.2 Securitate</h2>
        <ul>
            <li>Protecție CSRF cu tokeni</li>
            <li>Sanitizare input (XSS prevention)</li>
            <li>Prepared statements (SQL injection prevention)</li>
            <li>Hash parolă cu bcrypt</li>
            <li>Autorizare bazată pe roluri</li>
        </ul>
        
        <h2>5.3 Compatibilitate</h2>
        <ul>
            <li>Browsere: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+</li>
            <li>Responsive design: Desktop, Tablet, Mobile</li>
            <li>PHP 8.0+ cu mysqli/PDO</li>
            <li>MySQL 8.4 cu InnoDB</li>
        </ul>
    </section>

    <!-- 6. Structura Proiectului -->
    <section class="section">
        <h1>6. Structura Proiectului</h1>
        
        <div class="tech-stack">
            <h3>Organizarea Fișierelor</h3>
            <pre><code>fii-clean/
├── app/
│   ├── controllers/     # Controllere MVC
│   ├── models/          # Modele de date
│   ├── views/           # Template-uri UI
│   ├── core/            # Clase de bază
│   ├── utils/           # Utilitare și helpere
│   └── config/          # Configurări sistem
├── public/
│   ├── css/             # Stiluri CSS
│   ├── js/              # JavaScript
│   └── assets/          # Imagini, fonturi
└── index.php            # Entry point și routing</code></pre>
        </div>
        
        <h2>6.1 Componente Cheie</h2>
        <table>
            <thead>
                <tr>
                    <th>Fișier/Director</th>
                    <th>Funcție</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>index.php</td>
                    <td>Entry point, routing, API și RSS handling</td>
                </tr>
                <tr>
                    <td>APIController.php</td>
                    <td>Servicii web REST cu documentație completă</td>
                </tr>
                <tr>
                    <td>RSSFeedGenerator.php</td>
                    <td>Generare fluxuri RSS pentru monitorizare</td>
                </tr>
                <tr>
                    <td>main.js</td>
                    <td>AJAX și actualizări în timp real</td>
                </tr>
                <tr>
                    <td>style.css</td>
                    <td>Design modern responsive</td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- Footer -->
    <footer style="border-top: 2px solid var(--border-color); padding-top: 2rem; margin-top: 3rem; text-align: center; color: #666; font-size: 0.9rem;">
        <p>
            <strong>FII-Clean</strong> - Sistem Web pentru Managementul Serviciilor de Spălătorie<br>
            Dezvoltat conform standardelor IEEE System Requirements Specification Template<br>
            © 2025 Facultatea de Informatică, UAIC Iași
        </p>
    </footer>
</body>
</html>