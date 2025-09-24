<!-- Admin Auth Page -->
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-icon">🔐</div>
            <h1 class="auth-title">Admin Access</h1>
            <p class="auth-subtitle">Ingresa tus credenciales para continuar</p>
        </div>

        <form class="auth-form" id="authForm">
            <div class="error-message" id="errorMessage"></div>
            
            <div class="form-group">
                <label for="username" class="form-label">Usuario</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input" 
                    placeholder="Ingresa tu usuario..."
                    required
                    autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="Ingresa tu contraseña..."
                    required
                    autocomplete="current-password">
            </div>

            <button type="submit" class="auth-button" id="submitButton">
                Acceder al Admin
            </button>

            <div class="loading" id="loading">
                <div class="loading-spinner"></div>
                Verificando acceso...
            </div>
        </form>

        <div class="back-link">
            <a href="/">← Volver al sitio principal</a>
        </div>
    </div>
</div>