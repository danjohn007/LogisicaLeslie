<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .verification-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .verification-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .card-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .status-badge {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }
        
        .status-pending { background: #fef3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-in_route { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .info-value {
            font-weight: 600;
            color: #495057;
        }
        
        .products-table {
            margin-top: 20px;
        }
        
        .verified-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 576px) {
            .verification-container {
                padding: 10px;
            }
            
            .card-header, .card-body {
                padding: 20px;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="verified-badge">
                <i class="fas fa-check"></i>
            </div>
            
            <div class="card-header">
                <div class="company-logo">
                    <i class="fas fa-cheese text-warning" style="font-size: 24px;"></i>
                </div>
                <h2 class="mb-0">Quesos y Productos Leslie</h2>
                <p class="mb-0 opacity-75">Verificación de Pedido</p>
            </div>
            
            <div class="card-body">
                <!-- Order Header -->
                <div class="text-center mb-4">
                    <h3 class="text-primary mb-2"><?= htmlspecialchars($order['order_number']) ?></h3>
                    <div class="status-badge status-<?= $order['status'] ?>">
                        <?php
                        $statusLabels = [
                            'pending' => 'Pendiente',
                            'confirmed' => 'Confirmado',
                            'in_route' => 'En Ruta',
                            'delivered' => 'Entregado',
                            'cancelled' => 'Cancelado'
                        ];
                        echo $statusLabels[$order['status']] ?? $order['status'];
                        ?>
                    </div>
                </div>
                
                <!-- Customer Information -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>
                        Información del Cliente
                    </h5>
                    
                    <div class="info-item">
                        <span class="info-label">Cliente:</span>
                        <span class="info-value"><?= htmlspecialchars($customer['business_name']) ?></span>
                    </div>
                    
                    <?php if (!empty($customer['contact_name'])): ?>
                    <div class="info-item">
                        <span class="info-label">Contacto:</span>
                        <span class="info-value"><?= htmlspecialchars($customer['contact_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($customer['phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value"><?= htmlspecialchars($customer['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Order Information -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Detalles del Pedido
                    </h5>
                    
                    <div class="info-item">
                        <span class="info-label">Fecha del Pedido:</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($order['order_date'])) ?></span>
                    </div>
                    
                    <?php if ($order['delivery_date']): ?>
                    <div class="info-item">
                        <span class="info-label">Fecha de Entrega:</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($order['delivery_date'])) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <span class="info-label">Método de Pago:</span>
                        <span class="info-value"><?= ucfirst($order['payment_method']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Canal:</span>
                        <span class="info-value">
                            <span class="badge bg-<?= $order['channel_source'] === 'whatsapp' ? 'success' : 'primary' ?>">
                                <?= $order['channel_source'] === 'whatsapp' ? 'WhatsApp' : ucfirst($order['channel_source']) ?>
                            </span>
                        </span>
                    </div>
                </div>
                
                <!-- Products -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Productos del Pedido
                    </h5>
                    
                    <div class="table-responsive products-table">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($order_details)): ?>
                                    <?php foreach ($order_details as $detail): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($detail['product_name']) ?></strong>
                                            <?php if (!empty($detail['product_code'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($detail['product_code']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= number_format($detail['quantity_ordered'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($detail['unit_price'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($detail['subtotal'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No hay productos en este pedido
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                                </tr>
                                <?php if ($order['discount_amount'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Descuento:</strong></td>
                                    <td class="text-end"><strong>-$<?= number_format($order['discount_amount'], 2) ?></strong></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-success">
                                    <td colspan="3" class="text-end"><strong>Total Final:</strong></td>
                                    <td class="text-end"><strong>$<?= number_format($order['final_amount'], 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <!-- Order Notes -->
                <?php if (!empty($order['notes'])): ?>
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-sticky-note me-2"></i>
                        Notas del Pedido
                    </h5>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($order['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Verification Footer -->
                <div class="text-center mt-4 pt-4 border-top">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        <strong class="text-success">Pedido Verificado</strong>
                    </div>
                    
                    <p class="text-muted small mb-3">
                        Este pedido ha sido verificado mediante código QR.<br>
                        Verificado el <?= date('d/m/Y H:i') ?>
                    </p>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>
                                Imprimir
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-success btn-sm w-100" onclick="shareOrder()">
                                <i class="fas fa-share me-1"></i>
                                Compartir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function shareOrder() {
            const url = window.location.href;
            const title = 'Pedido <?= htmlspecialchars($order['order_number']) ?>';
            const text = 'Verificación de pedido para <?= htmlspecialchars($customer['business_name']) ?>';
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: text,
                    url: url
                }).catch(console.error);
            } else {
                // Fallback para navegadores que no soportan Web Share API
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('URL copiada al portapapeles');
                    }).catch(() => {
                        prompt('Copie esta URL:', url);
                    });
                } else {
                    prompt('Copie esta URL:', url);
                }
            }
        }
        
        // Auto-refresh status every 30 seconds if not delivered
        <?php if (!in_array($order['status'], ['delivered', 'cancelled'])): ?>
        setInterval(() => {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newStatus = doc.querySelector('.status-badge').className;
                    const currentStatus = document.querySelector('.status-badge').className;
                    
                    if (newStatus !== currentStatus) {
                        location.reload();
                    }
                })
                .catch(console.error);
        }, 30000);
        <?php endif; ?>
    </script>
    
    <style>
        @media print {
            body {
                background: white !important;
            }
            
            .verification-container {
                min-height: auto;
                padding: 0;
            }
            
            .verification-card {
                box-shadow: none;
                border: 1px solid #ddd;
                max-width: none;
            }
            
            .verified-badge {
                display: none;
            }
            
            .btn {
                display: none;
            }
            
            .card-header {
                background: #f8f9fa !important;
                color: #333 !important;
            }
            
            .company-logo {
                background: #f8f9fa !important;
            }
            
            .status-badge {
                border: 1px solid #ddd !important;
            }
        }
    </style>
</body>
</html>