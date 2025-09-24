<?php include_once __DIR__ . '/../pages/header.php'?>
<!-- Blog Hero -->
<section class="blog-hero">
    <div class="hero-content">
        <h1>Blog de Inbotscr</h1>
        <p class="hero-subtitle">Descubre las últimas tendencias en smartbots, IA y automatización empresarial</p>
    </div>
</section>

<!-- Blog Posts -->
<section class="section">
    <div class="blog-container">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <h2>Próximamente</h2>
                <p>Estamos preparando contenido increíble sobre smartbots e inteligencia artificial.</p>
                <a href="/contacto" class="btn-primary">Contáctanos mientras tanto</a>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-card fade-in">
                        <?php if ($post['featured_image']): ?>
                            <div class="blog-image">
                                <a href="/blog/<?php echo $post['slug']; ?>">
                                    <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="blog-content">
                            <div class="blog-meta">
                                <time datetime="<?php echo date('Y-m-d', strtotime($post['created_at'])); ?>">
                                    <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                                </time>
                            </div>
                            
                            <h2 class="blog-title">
                                <a href="/blog/<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                            </h2>
                            
                            <?php if ($post['excerpt']): ?>
                                <p class="blog-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <?php endif; ?>
                            
                            <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" class="read-more">
                                Leer más <span>→</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>