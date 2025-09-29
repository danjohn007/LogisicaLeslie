<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta - <?php echo htmlspecialchars($sale['sale_number']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            background: #f5f5f5;
            padding: 8px;
            border: 1px solid #ccc;
        }
        
        .sale-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .sale-info-left,
        .sale-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .sale-info-right {
            text-align: right;
        }
        
        .info-row {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .products-table th,
        .products-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        
        .products-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals-section {
            margin-top: 20px;
            text-align: right;
        }
        
        .total-row {
            margin-bottom: 5px;
            padding: 3px 0;
        }
        
        .total-final {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 8px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        
        .payment-method {
            background: #e3f2fd;
            padding: 10px;
            border-left: 4px solid #2196f3;
            margin: 15px 0;
        }
        
        .notes {
            background: #fff3e0;
            padding: 10px;
            border-left: 4px solid #ff9800;
            margin: 15px 0;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header de la empresa -->
    <div class="header">
        <div class="company-name">Quesos y Productos Leslie</div>
        <div class="company-info">
            Sistema de Logística y Ventas<br>
            Tel: (xxx) xxx-xxxx | Email: info@quesosleslie.com<br>
            Dirección de la empresa
        </div>
        <div class="invoice-title">COMPROBANTE DE VENTA</div>
    </div>

    <!-- Información de la venta -->
    <div class="sale-info">
        <div class="sale-info-left">
            <div class="info-row">
                <span class="info-label">No. Venta:</span> 
                <?php echo htmlspecialchars($sale['sale_number']); ?>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha:</span> 
                <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?>
            </div>
            <div class="info-row">
                <span class="info-label">Vendedor:</span> 
                <?php echo htmlspecialchars($sale['seller_name'] . ' ' . $sale['seller_lastname']); ?>
            </div>
        </div>
        <div class="sale-info-right">
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <?php if ($sale['customer_id']): ?>
                    <div style="margin-top: 5px;">
                        <strong><?php echo htmlspecialchars($sale['customer_business_name']); ?></strong><br>
                        <?php if ($sale['customer_contact_name']): ?>
                            <?php echo htmlspecialchars($sale['customer_contact_name']); ?><br>
                        <?php endif; ?>
                        <?php if ($sale['customer_phone']): ?>
                            Tel: <?php echo htmlspecialchars($sale['customer_phone']); ?><br>
                        <?php endif; ?>
                        <?php if ($sale['customer_address']): ?>
                            <?php echo htmlspecialchars($sale['customer_address']); ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 5px;">
                        <strong>Cliente General</strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Método de pago -->
    <div class="payment-method">
        <strong>Método de Pago:</strong> <?php echo ucfirst($sale['payment_method']); ?>
    </div>

    <!-- Tabla de productos -->
    <table class="products-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Precio Unit.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            foreach ($saleItems as $item): 
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemTotal;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_code']); ?></td>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="text-center"><?php echo $item['quantity']; ?></td>
                <td class="text-right">$<?php echo number_format($item['unit_price'], 2); ?></td>
                <td class="text-right">$<?php echo number_format($itemTotal, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totales -->
    <div class="totals-section">
        <div class="total-row">
            <strong>Subtotal: $<?php echo number_format($subtotal, 2); ?></strong>
        </div>
        <div class="total-row">
            <strong>IVA (16%): $<?php echo number_format($subtotal * 0.16, 2); ?></strong>
        </div>
        <div class="total-row total-final">
            <strong>TOTAL: $<?php echo number_format($sale['total_amount'], 2); ?></strong>
        </div>
    </div>

    <!-- Notas -->
    <?php if ($sale['notes']): ?>
    <div class="notes">
        <strong>Notas:</strong><br>
        <?php echo nl2br(htmlspecialchars($sale['notes'])); ?>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p>¡Gracias por su compra!</p>
        <p>Este comprobante fue generado automáticamente el <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>ID de Venta: <?php echo $sale['id']; ?></p>
    </div>

    <!-- Botón de impresión (no se muestra al imprimir) -->
    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Imprimir Comprobante
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            Cerrar
        </button>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>