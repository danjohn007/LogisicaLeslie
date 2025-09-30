<?php
/**
 * Controlador de Autenticación
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/User.php';

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Iniciar Sesión - ' . APP_NAME,
            'error' => null
        ];
        
        // Procesar formulario de login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $data['error'] = 'Por favor, ingrese usuario y contraseña.';
            } else {
                $user = $this->userModel->authenticate($username, $password);
                
                if ($user) {
                    // Establecer sesión
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['login_time'] = time();
                    
                    // Registrar el login
                    $this->userModel->logLogin($user['id']);
                    
                    // Redirigir al dashboard
                    $this->redirect('dashboard');
                    return;
                } else {
                    $data['error'] = 'Usuario o contraseña incorrectos.';
                }
            }
        }
        
        $this->view('auth/login', $data);
    }
    
    public function logout() {
        // Registrar el logout si hay usuario logueado
        if (isset($_SESSION['user_id'])) {
            $this->userModel->logLogout($_SESSION['user_id']);
        }
        
        // Destruir completamente la sesión
        $_SESSION = array();
        
        // Eliminar la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir sesión
        session_destroy();
        
        // Forzar headers para prevenir caché
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        // Mostrar página de logout con redirección automática
        echo '<html><head><title>Cerrando sesión...</title></head><body>';
        echo '<div style="text-align: center; margin-top: 50px; font-family: Arial, sans-serif;">';
        echo '<h2>Cerrando sesión...</h2>';
        echo '<p>Redirigiendo al login...</p>';
        echo '</div>';
        echo '<script>';
        echo 'setTimeout(function() { window.location.href = "' . BASE_URL . 'auth/login"; }, 1000);';
        echo '</script>';
        echo '</body></html>';
        exit();
    }
    
    public function profile() {
        $this->requireAuth();
        
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        $data = [
            'title' => 'Mi Perfil - ' . APP_NAME,
            'user' => $user,
            'success' => null,
            'error' => null
        ];
        
        // Procesar actualización de perfil
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateData = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? '')
            ];
            
            // Validar datos
            if (empty($updateData['first_name']) || empty($updateData['last_name']) || empty($updateData['email'])) {
                $data['error'] = 'Los campos nombre, apellido y email son obligatorios.';
            } elseif (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'El formato del email no es válido.';
            } else {
                try {
                    if ($this->userModel->update($_SESSION['user_id'], $updateData)) {
                        $_SESSION['full_name'] = $updateData['first_name'] . ' ' . $updateData['last_name'];
                        $data['success'] = 'Perfil actualizado correctamente.';
                        $data['user'] = $this->userModel->findById($_SESSION['user_id']);
                    } else {
                        $data['error'] = 'Error al actualizar el perfil.';
                    }
                } catch (Exception $e) {
                    $data['error'] = 'Error: ' . $e->getMessage();
                }
            }
        }
        
        $this->view('auth/profile', $data);
    }
    
    public function changePassword() {
        $this->requireAuth();
        
        $data = [
            'title' => 'Cambiar Contraseña - ' . APP_NAME,
            'success' => null,
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $data['error'] = 'Todos los campos son obligatorios.';
            } elseif ($newPassword !== $confirmPassword) {
                $data['error'] = 'Las contraseñas nuevas no coinciden.';
            } elseif (strlen($newPassword) < 6) {
                $data['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            } else {
                // Verificar contraseña actual usando el nuevo método
                if ($this->userModel->verifyCurrentPassword($_SESSION['user_id'], $currentPassword)) {
                    if ($this->userModel->changePassword($_SESSION['user_id'], $newPassword)) {
                        $data['success'] = 'Contraseña cambiada correctamente.';
                    } else {
                        $data['error'] = 'Error al cambiar la contraseña.';
                    }
                } else {
                    $data['error'] = 'La contraseña actual es incorrecta.';
                }
            }
        }
        
        $this->view('auth/change-password', $data);
    }
}