<?php

// Intentar cargar app.php desde diferentes ubicaciones
$appPaths = [
    __DIR__ . '/includes/app.php',        // Si está en raíz del proyecto
    __DIR__ . '/../includes/app.php',     // Si está en public/
    dirname(__DIR__) . '/includes/app.php' // Alternativa
];

$appLoaded = false;
foreach ($appPaths as $appPath) {
    if (file_exists($appPath)) {
        require_once $appPath;
        $appLoaded = true;
        break;
    }
}

if (!$appLoaded) {
    die("❌ Error: No se pudo encontrar includes/app.php\n");
}

use Model\BlogPost;

/**
 * Generador de Sitemap XML para Inbotscr
 * Genera automáticamente el sitemap con todas las páginas estáticas y posts del blog
 */
class SitemapGenerator {
    
    private $domain;
    private $staticPages;
    public $blogPosts; // Cambiar de private a public
    
    public function __construct() {
        $this->domain = 'https://inbotscr.com';
        $this->staticPages = [
            '/' => [
                'priority' => '1.0',
                'changefreq' => 'daily'
            ],
            '/nosotros' => [
                'priority' => '0.8',
                'changefreq' => 'monthly'
            ],
            '/smartbots' => [
                'priority' => '0.9',
                'changefreq' => 'weekly'
            ],
            '/contacto' => [
                'priority' => '0.9',
                'changefreq' => 'monthly'
            ],
            '/blog' => [
                'priority' => '0.8',
                'changefreq' => 'daily'
            ],
            '/privacidad' => [
                'priority' => '0.3',
                'changefreq' => 'yearly'
            ],
            '/condiciones' => [
                'priority' => '0.3',
                'changefreq' => 'yearly'
            ]
        ];
        
        $this->blogPosts = $this->getBlogPosts();
    }
    
    /**
     * Obtiene todos los posts del blog publicados
     */
    private function getBlogPosts() {
        try {
            // Verificar si la clase existe antes de usarla
            if (!class_exists('Model\BlogPost')) {
                error_log("Clase BlogPost no encontrada, continuando sin posts del blog");
                return [];
            }
            
            // Crear conexión a base de datos directamente
            $db = new \mysqli('localhost', 'root', '', 'blog');
            
            if ($db->connect_error) {
                error_log("Error de conexión BD: " . $db->connect_error);
                return [];
            }
            
            $db->set_charset('utf8mb4');
            
            // Crear instancia del modelo con nombre completo
            $blogPost = new \Model\BlogPost($db);
            
            // Llamar método correcto: findAll() en lugar de all()
            return $blogPost->findAll('published');
            
        } catch (Exception $e) {
            error_log("Error obteniendo posts del blog: " . $e->getMessage());
            return [];
        } catch (Error $e) {
            error_log("Error obteniendo posts del blog: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Genera el XML del sitemap
     */
    public function generateSitemap() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        
        // Agregar páginas estáticas
        foreach ($this->staticPages as $url => $config) {
            $xml .= $this->generateUrlEntry(
                $this->domain . $url,
                $config['priority'],
                $config['changefreq']
            );
        }
        
        // Agregar posts del blog
        foreach ($this->blogPosts as $post) {
            $xml .= $this->generateUrlEntry(
                $this->domain . '/blog/' . $post['slug'],
                '0.7',
                'monthly',
                $post['updated_at'] ?? $post['created_at']
            );
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Genera una entrada URL individual para el sitemap
     */
    private function generateUrlEntry($url, $priority, $changefreq, $lastmod = null) {
        $xml = '  <url>' . PHP_EOL;
        $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
        
        if ($lastmod) {
            $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s+00:00', strtotime($lastmod)) . '</lastmod>' . PHP_EOL;
        } else {
            $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s+00:00') . '</lastmod>' . PHP_EOL;
        }
        
        $xml .= '    <changefreq>' . $changefreq . '</changefreq>' . PHP_EOL;
        $xml .= '    <priority>' . $priority . '</priority>' . PHP_EOL;
        $xml .= '  </url>' . PHP_EOL;
        
        return $xml;
    }
    
    /**
     * Guarda el sitemap en un archivo
     */
    public function saveSitemap($filename = 'sitemap.xml') {
        $sitemapContent = $this->generateSitemap();
        
        // Buscar directorio public en diferentes ubicaciones
        $publicPaths = [
            __DIR__ . '/public/',
            __DIR__ . '/',
            dirname(__DIR__) . '/public/',
            dirname(__DIR__) . '/'
        ];
        
        $filePath = null;
        foreach ($publicPaths as $publicPath) {
            if (is_dir($publicPath) && is_writable($publicPath)) {
                $filePath = $publicPath . $filename;
                break;
            }
        }
        
        if (!$filePath) {
            echo "❌ Error: No se encontró directorio público escribible" . PHP_EOL;
            return false;
        }
        
        $result = file_put_contents($filePath, $sitemapContent);
        
        if ($result !== false) {
            echo "✅ Sitemap generado exitosamente en: " . $filePath . PHP_EOL;
            echo "📊 Total de URLs: " . (count($this->staticPages) + count($this->blogPosts)) . PHP_EOL;
            echo "📄 Páginas estáticas: " . count($this->staticPages) . PHP_EOL;
            echo "📝 Posts del blog: " . count($this->blogPosts) . PHP_EOL;
            return true;
        } else {
            echo "❌ Error al generar el sitemap" . PHP_EOL;
            return false;
        }
    }
    
    /**
     * Muestra el sitemap en pantalla (para debug)
     */
    public function displaySitemap() {
        header('Content-Type: application/xml; charset=utf-8');
        echo $this->generateSitemap();
    }
    
    /**
     * Ping a los motores de búsqueda sobre el nuevo sitemap
     */
    public function pingSearchEngines() {
        $sitemapUrl = $this->domain . '/sitemap.xml';
        
        $searchEngines = [
            'Google' => 'http://www.google.com/ping?sitemap=' . urlencode($sitemapUrl),
            'Bing' => 'http://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl)
        ];
        
        echo "🔔 Notificando a los motores de búsqueda..." . PHP_EOL;
        
        foreach ($searchEngines as $engine => $pingUrl) {
            $response = @file_get_contents($pingUrl);
            if ($response !== false) {
                echo "✅ " . $engine . " notificado exitosamente" . PHP_EOL;
            } else {
                echo "⚠️  Error notificando a " . $engine . PHP_EOL;
            }
        }
    }
}

// Uso del script
if (php_sapi_name() === 'cli') {
    // Ejecutado desde línea de comandos
    echo "🚀 Generando sitemap para Inbotscr..." . PHP_EOL;
    echo "Fecha: " . date('d \d\e F \d\e Y') . PHP_EOL;
    echo "=====================================" . PHP_EOL;
    
    $generator = new SitemapGenerator();
    
    if ($generator->saveSitemap()) {
        echo PHP_EOL . "🔔 ¿Deseas notificar a los motores de búsqueda? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        
        if (trim(strtolower($line)) === 'y') {
            $generator->pingSearchEngines();
        }
        
        fclose($handle);
    }
    
} else {
    // Ejecutado desde el navegador web
    $action = $_GET['action'] ?? 'debug';
    $generator = new SitemapGenerator();
    
    switch ($action) {
        case 'xml':
            // Mostrar XML sin debug
            $generator->displaySitemap();
            break;
            
        case 'generate':
            echo "<h2>✅ Sitemap generado exitosamente</h2>";
            $generator->saveSitemap();
            echo "<p><a href='sitemap.xml' target='_blank'>Ver sitemap.xml</a></p>";
            echo "<p><a href='?action=ping'>Notificar a motores de búsqueda</a></p>";
            break;
            
        case 'ping':
            echo "<h2>🔔 Motores de búsqueda notificados</h2>";
            $generator->pingSearchEngines();
            break;
            
        case 'debug':
        default:
            // Modo debug - mostrar información básica
            echo "<h1>Sitemap Generator</h1>";
            echo "<hr>";
            echo "<h2>Información del sitemap:</h2>";
            
            // Mostrar información básica sin debug detallado
            $staticPages = 7;
            $blogPosts = count($generator->blogPosts); // Usar propiedad en lugar de método privado
            
            echo "<p><strong>Páginas estáticas:</strong> " . $staticPages . "</p>";
            echo "<p><strong>Posts del blog:</strong> " . $blogPosts . "</p>";
            echo "<p><strong>Total de URLs:</strong> " . ($staticPages + $blogPosts) . "</p>";
            
            echo "<h3>Acciones:</h3>";
            echo "<p><a href='?action=xml' target='_blank'>Ver XML generado</a></p>";
            echo "<p><a href='?action=generate'>Generar sitemap.xml</a></p>";
            break;
    }
}