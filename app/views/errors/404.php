<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página No Encontrada - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="py-5">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                    <h1 class="display-1 fw-bold text-primary">404</h1>
                    <h2 class="mb-4">Página No Encontrada</h2>
                    <p class="lead mb-4">
                        Lo sentimos, la página que está buscando no existe o ha sido movida.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>
                            Ir al Inicio
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Volver Atrás
                        </button>
                    </div>
                    
                    <div class="mt-5">
                        <p class="text-muted">
                            Si cree que esto es un error, por favor contacte al administrador del sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>