<?php include_once __DIR__ . '/../pages/header.php'?>

<!-- Post Header -->
<article class="blog-post">
    <header class="post-header">
        <div class="post-meta">
            <a href="/blog" class="back-blog">← Volver al blog</a>
            <time datetime="<?php echo date('Y-m-d', strtotime($post['created_at'])); ?>">
                <?php echo date('d M Y', strtotime($post['created_at'])); ?>
            </time>
        </div>
        
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        
        <?php if ($post['excerpt']): ?>
            <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
        <?php endif; ?>
    </header>

    <?php if ($post['featured_image']): ?>
        <div class="post-image">
            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
        </div>
    <?php endif; ?>

    <!-- Post Content -->
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>

    <!-- Post Footer con botones compartir corregidos -->
    <footer class="post-footer">
        <div class="post-actions">
            <a href="/blog" class="btn-secondary">← Todos los posts</a>
            <div class="share-buttons">
                <span>Compartir:</span>
                <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' - ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                   target="_blank" 
                   class="share-btn whatsapp"
                   rel="noopener noreferrer">WhatsApp</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                   target="_blank" 
                   class="share-btn facebook"
                   rel="noopener noreferrer">Facebook</a>
                <a href="https://x.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                   target="_blank" 
                   class="share-btn x"
                   rel="noopener noreferrer">X</a>
            </div>
        </div>
    </footer>
</article>

<!-- Related CTA -->
<section class="related-cta">
    <div class="cta-content">
        <h2>¿Te interesó este tema?</h2>
        <p>Descubre cómo implementar estas soluciones en tu negocio</p>
        <div class="hero-buttons">
            <a href="/contacto" class="btn-primary">Contactar Ahora</a>
            <a href="/smartbots" class="btn-secondary">Ver Smartbots</a>
        </div>
    </div>
</section>