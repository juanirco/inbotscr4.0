<?php include_once __DIR__ . '/header.php'?>
<div class="bg-animation"></div>
<div class="interactive-bg">
    <div class="glow-cursor"></div>
</div>
<!-- Hero Section -->
<section class="contact-hero">
    <div class="hero-background"></div>
    <div class="hero-content">
        <span class="hero-emoji">🤝</span>
        <h1 class="hero-title">¡Hablemos!</h1>
        <p class="hero-subtitle">
            ¿Sabes qué? Odiamos los formularios tanto como tú. Por eso hemos creado formas más divertidas e inmediatas de conectar contigo. 
        </p>
    </div>
</section>

<!-- Choice Cards Section -->
<section class="choice-section">
    <h2 class="section-title fade-in">Tu Eliges Cómo Conectar</h2>
    <p class="section-subtitle fade-in">
        Dos mundos, dos experiencias completamente diferentes. ¿Cuál prefieres?
    </p>

    <div class="choice-cards">
        <!-- Instant/AI Option -->
        <div class="choice-card instant fade-in">
            <div class="card-badge">Recomendado</div>
            <div class="card-icon">🚀</div>
            <h3 class="card-title">Experiencia del Futuro</h3>
            <p class="card-description">
                Conversa con nuestros smartbots (sí, literal puedes conversar con audios o texto). 
                Respuesta inmediata, disponible 24/7, y pueden resolver hasta las consultas más complejas.
            </p>
            
            <div class="bot-channels">
                <a href="https://wa.me/50686324262?text=Hola! Quiero conocer más sobre los smartbots 🚀" 
                   target="_blank" 
                   class="bot-channel whatsapp">
                    <div class="channel-icon">
                        <img src="/build/img/whatsapp.webp" alt="WhatsApp" loading="lazy">
                    </div>
                    <div class="channel-label">WhatsApp Bot</div>
                </a>
                
                <a href="https://m.me/inbotscr" 
                   target="_blank" 
                   class="bot-channel facebook">
                    <div class="channel-icon">
                        <img src="/build/img/fb.webp" alt="Facebook" loading="lazy">
                    </div>
                    <div class="channel-label">Messenger Bot</div>
                </a>
                
                <a href="https://ig.me/m/inbotscr" 
                   target="_blank" 
                   class="bot-channel instagram">
                    <div class="channel-icon">
                        <img src="/build/img/instagram.webp" alt="Instagram" loading="lazy">
                    </div>
                    <div class="channel-label">Instagram Bot</div>
                </a>
            </div>

            <div style="background: rgba(0, 255, 136, 0.1); border: 1px solid rgba(0, 255, 136, 0.3); border-radius: 15px; padding: 1rem; margin-top: 1.5rem; color: #00ff88; font-size: 0.9rem; text-align: center;">
                ✅ Respuesta en segundos • ✅ Disponible 24/7 • ✅ Puede enviar cotizaciones
            </div>
        </div>

        <!-- Traditional Option -->
        <div class="choice-card traditional fade-in">
            <div class="card-badge">Clásico</div>
            <div class="card-icon">📞</div>
            <h3 class="card-title">Método Tradicional</h3>
            <p class="card-description">
                Si prefieres el camino clásico (y más lento), aquí están las opciones de toda la vida. 
                Eso sí, sujeto a horarios y disponibilidad humana.
            </p>

            <div class="traditional-methods">
                <a href="tel:+50686324262" class="traditional-method">
                    <div class="method-icon">📱</div>
                    <div class="method-label">Llamar</div>
                    <div class="method-detail">+506 8632-4262</div>
                </a>
                
                <a href="mailto:info@inbotscr.com" class="traditional-method">
                    <div class="method-icon">✉️</div>
                    <div class="method-label">Email</div>
                    <div class="method-detail">info@inbotscr.com</div>
                </a>
            </div>

            <div class="warning-note">
                ⚠️ Sujeto a horario y disponibilidad • Puede que tardemos en responder
            </div>
        </div>
    </div>
</section>

<!-- Live Demo Section -->
<section class="live-demo fade-in">
    <div class="demo-container">
        <h2 class="section-title">¿Curioso por la Experiencia Bot?</h2>
        <p class="section-subtitle">
            Mira exactamente cómo será tu conversación con nuestro smartbot. Esto es lo que verás cuando nos contactes:
        </p>
        <!-- Chat embebido sin burbuja -->
        <div class="phone-mockup">
            <div class="phone-screen">
                <div class="whatsapp-header">
                    <div class="contact-avatar">🤖</div>
                    <div class="contact-info">
                        <h3>Smartbot Inbotscr</h3>
                        <div class="contact-status">En línea • Responde inmediatamente</div>
                    </div>
                </div>
                
                <!-- Contenedor placeholder para el iframe dinámico -->
                <div id="chat-iframe-container" style="position:absolute; inset:0; width:100%; height:100%; border:0; border-radius:16px; overflow:hidden;">
                    <!-- El iframe se inyectará aquí dinámicamente -->
                </div>
            </div>
        </div>

        <a href="https://wa.me/50686324262?text=Hola! Quiero ver la demo de smartbots en acción 🚀" 
           target="_blank" 
           class="try-button">
            💬 Probar Ahora en WhatsApp
        </a>
    </div>
</section>