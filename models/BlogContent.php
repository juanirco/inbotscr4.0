<?php

namespace Model;

class BlogContent {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function findByPostId($post_id) {
        $sql = "SELECT * FROM blog_content WHERE blog_post_id = ? ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $content = [];
        while ($row = $result->fetch_assoc()) {
            $row['content_data'] = json_decode($row['content_data'], true);
            $content[] = $row;
        }
        
        return $content;
    }
    
    public function create($post_id, $content_type, $content_data, $sort_order = 0) {
        $sql = "INSERT INTO blog_content (blog_post_id, content_type, content_data, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $content_json = json_encode($content_data);
        $stmt->bind_param('issi', $post_id, $content_type, $content_json, $sort_order);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    public function update($id, $content_data, $sort_order = null) {
        if ($sort_order !== null) {
            $sql = "UPDATE blog_content SET content_data = ?, sort_order = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $content_json = json_encode($content_data);
            $stmt->bind_param('sii', $content_json, $sort_order, $id);
        } else {
            $sql = "UPDATE blog_content SET content_data = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $content_json = json_encode($content_data);
            $stmt->bind_param('si', $content_json, $id);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM blog_content WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    
    public function deleteByPostId($post_id) {
        $sql = "DELETE FROM blog_content WHERE blog_post_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $post_id);
        return $stmt->execute();
    }
    
    public function reorder($content_blocks) {
        try {
            $this->db->begin_transaction();
            
            foreach ($content_blocks as $order => $content_id) {
                $sort_order = $order + 1;
                $sql = "UPDATE blog_content SET sort_order = ?, updated_at = NOW() 
                        WHERE id = ? AND blog_post_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('iii', $sort_order, $content_id, $post_id);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function getNextSortOrder($post_id) {
        $sql = "SELECT MAX(sort_order) as max_order FROM blog_content WHERE blog_post_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $post_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return ($result['max_order'] ?? 0) + 1;
    }

    public function uploadImage($file, $post_id = null) {
        // SIMPLE: Guardar directamente en public/build/img/ para visualización inmediata
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/build/img/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'url' => '/build/img/' . $filename,
                'filename' => $filename,
                'filepath' => $filepath
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Error uploading file'
        ];
    }

    // Función para obtener la mejor versión de imagen (WebP si existe, sino original)
    public static function getBestImageUrl($imagePath) {
        if (empty($imagePath)) {
            return $imagePath;
        }
        
        // Asegurar que la ruta sea correcta
        if (!str_starts_with($imagePath, '/build/img/')) {
            $imagePath = '/build/img/' . basename($imagePath);
        }
        
        // Obtener información del archivo
        $pathInfo = pathinfo($imagePath);
        $extension = strtolower($pathInfo['extension'] ?? '');
        
        // Solo generar WebP para JPG y PNG
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            $webpFile = $_SERVER['DOCUMENT_ROOT'] . $webpPath;
            
            // Si existe la versión WebP, usarla
            if (file_exists($webpFile)) {
                return $webpPath;
            }
        }
        
        // Sino, devolver la imagen original
        return $imagePath;
    }

    // MÉTODO CORREGIDO: renderContent con aplicación de estilos CSS
    public function renderContent($content_blocks) {
        if (empty($content_blocks)) return '';
        
        // Group blocks by row
        $rows = [];
        foreach ($content_blocks as $block) {
            $data = $block['content_data'];
            if (isset($data['row'])) {
                $rowIndex = $data['row'];
                if (!isset($rows[$rowIndex])) {
                    $rows[$rowIndex] = [];
                }
                // CORRECCIÓN: Usar toda la estructura de datos, no solo $data['content']
                $rows[$rowIndex][] = $data;
            }
        }
        
        $html = '';
        foreach ($rows as $rowData) {
            $html .= '<div class="content-row">';
            foreach ($rowData as $columnData) {
                // CORRECCIÓN CRÍTICA: Extraer las propiedades de estilo del bloque
                $columnClasses = 'content-column';
                
                // Aplicar clases de tamaño del bloque
                if (isset($columnData['content']['blockSize'])) {
                    $columnClasses .= ' block-size-' . $columnData['content']['blockSize'];
                } elseif (isset($columnData['blockSize'])) {
                    $columnClasses .= ' block-size-' . $columnData['blockSize'];
                }
                
                // Aplicar clases de alineación del bloque
                if (isset($columnData['content']['blockAlign'])) {
                    $columnClasses .= ' block-align-' . $columnData['content']['blockAlign'];
                } elseif (isset($columnData['blockAlign'])) {
                    $columnClasses .= ' block-align-' . $columnData['blockAlign'];
                }
                
                $html .= '<div class="' . $columnClasses . '">';
                
                // CORRECCIÓN: Pasar la estructura correcta al renderBlock
                $html .= $this->renderBlock([
                    'content_type' => $columnData['content']['type'] ?? $columnData['type'] ?? 'text',
                    'content_data' => $columnData['content'] ?? $columnData,
                    // NUEVO: Pasar también las propiedades de estilo
                    'block_styles' => [
                        'blockSize' => $columnData['content']['blockSize'] ?? $columnData['blockSize'] ?? 'medium',
                        'blockAlign' => $columnData['content']['blockAlign'] ?? $columnData['blockAlign'] ?? 'center'
                    ]
                ]);
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        return $html;
    }

    // MÉTODO CORREGIDO: renderBlock con aplicación de estilos CSS
    private function renderBlock($block) {
        $type = $block['content_type'];
        $data = $block['content_data'];
        $styles = $block['block_styles'] ?? [];
        
        // Construir clases CSS del bloque
        $blockClasses = 'content-block content-' . $type;
        
        // Aplicar clases de tamaño
        if (isset($styles['blockSize'])) {
            $blockClasses .= ' block-size-' . $styles['blockSize'];
        }
        
        // Aplicar clases de alineación
        if (isset($styles['blockAlign'])) {
            $blockClasses .= ' block-align-' . $styles['blockAlign'];
        }
        
        // Generar el HTML del contenido según el tipo
        $content = '';
        switch ($type) {
            case 'text':
                $content = $this->renderTextBlock($data);
                break;
            case 'image':
                $content = $this->renderImageBlock($data);
                break;
            case 'gif':
                $content = $this->renderGifBlock($data);
                break;
            case 'code':
                $content = $this->renderCodeBlock($data);
                break;
            case 'quote':
                $content = $this->renderQuoteBlock($data);
                break;
            case 'video':
                $content = $this->renderVideoBlock($data);
                break;
            case 'card':
                $content = $this->renderCardBlock($data);
                break;
            default:
                $content = '';
        }
        
        // CORRECCIÓN CRÍTICA: Envolver el contenido con las clases CSS correctas
        return "<div class=\"{$blockClasses}\">{$content}</div>";
    }

    // MÉTODO SOLUCIONADO: renderTextBlock con aplicación de alineación de texto
    private function renderTextBlock($data) {
        // CORRECCIÓN 1: Buscar el contenido en múltiples ubicaciones posibles
        $html = '';
        
        if (isset($data['content'])) {
            $html = $data['content'];
        } elseif (isset($data['text'])) {
            $html = $data['text'];
        } elseif (isset($data['html'])) {
            $html = $data['html'];
        }
        
        // CORRECCIÓN 2: Si aún no hay contenido, verificar estructura anidada
        if (empty($html) && is_array($data)) {
            foreach ($data as $key => $value) {
                if (in_array($key, ['content', 'text', 'html']) && !empty($value)) {
                    $html = $value;
                    break;
                }
            }
        }
        
        // SOLUCIÓN DEL PROBLEMA: Aplicar clases de alineación de texto
        $textClasses = 'content-text';
        
        // Detectar alineación de texto desde múltiples ubicaciones posibles
        $textAlign = null;
        
        // Buscar textAlign en la estructura de datos
        if (isset($data['textAlign'])) {
            $textAlign = $data['textAlign'];
        } elseif (isset($data['content']['textAlign'])) {
            $textAlign = $data['content']['textAlign'];
        } elseif (isset($data['text_align'])) {
            $textAlign = $data['text_align'];
        }
        
        // Aplicar clase CSS según la alineación
        if ($textAlign && in_array($textAlign, ['left', 'center', 'right'])) {
            $textClasses .= ' text-align-' . $textAlign;
        }
        
        // CORRECCIÓN 3: Preservar exactamente el HTML como está (con estilos inline y clases) 
        // pero ahora aplicando también las clases de alineación de texto
        return "<div class=\"{$textClasses}\">{$html}</div>";
    }

    // MÉTODO MEJORADO: renderImageBlock
    private function renderImageBlock($data) {
        // Buscar la URL de la imagen en diferentes ubicaciones
        $imageUrl = $data['url'] ?? $data['src'] ?? null;
        
        if (!$imageUrl) return '';
        
        // Usar WebP si existe, sino original
        $bestImageUrl = self::getBestImageUrl($imageUrl);
        $alt = $data['alt'] ?? '';
        
        return "<div class=\"content-image\"><img src=\"{$bestImageUrl}\" alt=\"{$alt}\" style=\"max-width: 100%;\"></div>";
    }

    // MÉTODO CORREGIDO: renderGifBlock - Convierte URLs de Giphy/Tenor a URLs directas de GIF
    private function renderGifBlock($data) {
        $imageUrl = $data['url'] ?? $data['src'] ?? null;
        if (!$imageUrl) return '';
        
        // Detectar si es de Giphy y convertir a URL directa
        if (strpos($imageUrl, 'giphy.com') !== false) {
            $giphyId = $this->extractGiphyId($imageUrl);
            if ($giphyId) {
                // Usar URL directa del GIF de Giphy (mejor calidad)
                $directUrl = "https://i.giphy.com/media/{$giphyId}/giphy.gif";
                $alt = $data['alt'] ?? 'GIF';
                return "<div class=\"content-gif\"><img src=\"{$directUrl}\" alt=\"{$alt}\" style=\"max-width: 100%;\"></div>";
            }
        }
        
        // Detectar si es de Tenor y convertir a URL directa
        if (strpos($imageUrl, 'tenor.com') !== false) {
            $tenorId = $this->extractTenorId($imageUrl);
            if ($tenorId) {
                // Usar URL directa del GIF de Tenor
                $directUrl = "https://c.tenor.com/{$tenorId}/tenor.gif";
                $alt = $data['alt'] ?? 'GIF';
                return "<div class=\"content-gif\"><img src=\"{$directUrl}\" alt=\"{$alt}\" style=\"max-width: 100%;\"></div>";
            }
        }
        
        // Para URLs directas de GIF (.gif, .webp) mantener como imagen
        if (preg_match('/\.(gif|webp)$/i', $imageUrl)) {
            $alt = $data['alt'] ?? 'GIF';
            return "<div class=\"content-gif\"><img src=\"{$imageUrl}\" alt=\"{$alt}\" style=\"max-width: 100%;\"></div>";
        }
        
        // Fallback: intentar como imagen
        $alt = $data['alt'] ?? 'GIF';
        return "<div class=\"content-gif\"><img src=\"{$imageUrl}\" alt=\"{$alt}\" style=\"max-width: 100%;\"></div>";
    }

    // MÉTODO MEJORADO: renderCodeBlock  
    private function renderCodeBlock($data) {
        $code = $data['content'] ?? $data['code'] ?? '';
        $language = $data['language'] ?? 'text';
        
        // Escapar el código para HTML pero preservar formato
        $escapedCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        
        return "<div class=\"content-code\"><pre><code class=\"language-{$language}\">{$escapedCode}</code></pre></div>";
    }

    // MÉTODO MEJORADO: renderQuoteBlock
    private function renderQuoteBlock($data) {
        $quote = $data['text'] ?? $data['quote'] ?? '';
        $author = $data['author'] ?? '';
        
        $html = '<div class="content-quote"><blockquote>';
        $html .= "<p>{$quote}</p>";
        if ($author) {
            $html .= "<cite>— {$author}</cite>";
        }
        $html .= '</blockquote></div>';
        
        return $html;
    }

    // MÉTODO MEJORADO: renderVideoBlock - Mejor manejo de URLs de YouTube
    private function renderVideoBlock($data) {
        $url = $data['url'] ?? $data['embedUrl'] ?? '';
        if (!$url) return '';
        
        // Extraer ID de YouTube y generar URL de embed
        $videoId = $this->extractYouTubeId($url);
        if ($videoId) {
            $embedUrl = "https://www.youtube.com/embed/{$videoId}";
            return "<div class=\"content-video\"><iframe src=\"{$embedUrl}\" width=\"100%\" height=\"315\" frameborder=\"0\" allowfullscreen></iframe></div>";
        }
        
        // Si ya es una URL de embed, usarla directamente
        if (strpos($url, 'youtube.com/embed/') !== false) {
            return "<div class=\"content-video\"><iframe src=\"{$url}\" width=\"100%\" height=\"315\" frameborder=\"0\" allowfullscreen></iframe></div>";
        }
        
        // Convertir URLs de YouTube watch a embed como fallback
        if (strpos($url, 'youtube.com/watch') !== false) {
            $url = str_replace('youtube.com/watch?v=', 'youtube.com/embed/', $url);
            return "<div class=\"content-video\"><iframe src=\"{$url}\" width=\"100%\" height=\"315\" frameborder=\"0\" allowfullscreen></iframe></div>";
        }
        
        // Si no es YouTube, intentar usar la URL directamente como iframe
        return "<div class=\"content-video\"><iframe src=\"{$url}\" width=\"100%\" height=\"315\" frameborder=\"0\" allowfullscreen></iframe></div>";
    }

    // MÉTODO MEJORADO: renderCardBlock
    private function renderCardBlock($data) {
        $header = $data['header'] ?? '';
        $body = $data['body'] ?? '';
        $footer = $data['footer'] ?? '';
        
        $html = '<div class="content-card"><div class="card">';
        if ($header) $html .= "<div class=\"card-header\">{$header}</div>";
        if ($body) $html .= "<div class=\"card-body\">{$body}</div>";
        if ($footer) $html .= "<div class=\"card-footer\">{$footer}</div>";
        $html .= '</div></div>';
        
        return $html;
    }

    // MÉTODO HELPER NUEVO: Extraer ID de Giphy
    private function extractGiphyId($url) {
        // Patrón para URLs de Giphy: https://giphy.com/gifs/something-ID
        if (preg_match('/giphy\.com\/gifs\/.*-([a-zA-Z0-9]+)(?:\?|$)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Patrón alternativo: https://media.giphy.com/media/ID/giphy.gif
        if (preg_match('/media\.giphy\.com\/media\/([a-zA-Z0-9]+)\//', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    // MÉTODO HELPER NUEVO: Extraer ID de Tenor
    private function extractTenorId($url) {
        // Patrón para URLs de Tenor: https://tenor.com/view/something-ID
        if (preg_match('/tenor\.com\/view\/.*-([0-9A-Za-z_]+)(?:\?|$)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Patrón alternativo: https://media.tenor.com/ID/ o similar
        if (preg_match('/(?:media|c)\.tenor\.com\/([A-Za-z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Patrón para URLs cortas de Tenor: https://tenor.com/ID
        if (preg_match('/tenor\.com\/([A-Za-z0-9_-]+)(?:\?|$)/', $url, $matches)) {
            $id = $matches[1];
            // Verificar que no sea una página común como 'view', 'search', etc.
            if (!in_array($id, ['view', 'search', 'gif-maker', 'explore', 'categories'])) {
                return $id;
            }
        }
        
        return null;
    }

    // MÉTODO HELPER NUEVO: Extraer ID de YouTube
    private function extractYouTubeId($url) {
        // Patrón completo para diferentes formatos de YouTube
        $pattern = '/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/';
        if (preg_match($pattern, $url, $matches)) {
            $videoId = $matches[2] ?? '';
            // Verificar que el ID tenga la longitud correcta (11 caracteres)
            if (strlen($videoId) === 11) {
                return $videoId;
            }
        }
        
        return null;
    }
}