<?php
/**
 * Controlador Principal
 * Sistema de Logística - Quesos y Productos Leslie
 */

class HomeController extends Controller {
    
    public function index() {
        // Si el usuario está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Bienvenido al ' . APP_NAME,
            'version' => APP_VERSION,
            'base_url' => BASE_URL
        ];
        
        $this->view('home/index', $data);
    }
    
    public function about() {
        $data = [
            'title' => 'Acerca del Sistema',
            'app_name' => APP_NAME,
            'version' => APP_VERSION
        ];
        
        $this->view('home/about', $data);
    }
}