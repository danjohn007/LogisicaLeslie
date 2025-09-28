<?php
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Cambiar Contraseña
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">
                                <i class="fas fa-lock me-1"></i>
                                Contraseña Actual *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye" id="current_password_icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">
                                <i class="fas fa-key me-1"></i>
                                Nueva Contraseña *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                La contraseña debe tener al menos 6 caracteres.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-check me-1"></i>
                                Confirmar Nueva Contraseña *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="confirm_password_icon"></i>
                                </button>
                            </div>
                            <div id="password_match_feedback" class="form-text"></div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo BASE_URL; ?>profile" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Volver al Perfil
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validar que las contraseñas coincidan
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    const feedback = document.getElementById('password_match_feedback');
    
    if (confirmPassword === '') {
        feedback.textContent = '';
        feedback.className = 'form-text';
    } else if (newPassword === confirmPassword) {
        feedback.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>Las contraseñas coinciden';
        feedback.className = 'form-text text-success';
    } else {
        feedback.innerHTML = '<i class="fas fa-exclamation-circle text-danger me-1"></i>Las contraseñas no coinciden';
        feedback.className = 'form-text text-danger';
    }
});

// Validar fortaleza de contraseña
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const length = password.length;
    
    if (length === 0) return;
    
    let strength = 'Débil';
    let color = 'text-danger';
    
    if (length >= 8) {
        strength = 'Media';
        color = 'text-warning';
    }
    
    if (length >= 10 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
        strength = 'Fuerte';
        color = 'text-success';
    }
    
    // Se podría mostrar la fortaleza si se desea
});
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>