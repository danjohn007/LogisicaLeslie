<?php
/**
 * Simple QR Code generator wrapper
 * Using Google Charts API as fallback
 */

// QR Code error correction levels
define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

class QRcode {
    
    /**
     * Generate QR code PNG image
     */
    public static function png($text, $outfile = false, $level = QR_ECLEVEL_M, $size = 3, $margin = 4) {
        try {
            // Try to use Google Charts API as fallback
            $qrData = self::generateQRDataURI($text, $size * 50);
            
            if ($outfile) {
                // Save to file
                $imageData = base64_decode(substr($qrData, strpos($qrData, ',') + 1));
                file_put_contents($outfile, $imageData);
                return true;
            } else {
                // Output directly
                header('Content-Type: image/png');
                $imageData = base64_decode(substr($qrData, strpos($qrData, ',') + 1));
                echo $imageData;
                return true;
            }
        } catch (Exception $e) {
            error_log("QR Code generation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate QR code as base64 data URI
     */
    public static function generateQRDataURI($text, $size = 200) {
        // Use a simple QR code generator or Google Charts API
        $encodedText = urlencode($text);
        $apiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedText}";
        
        // Try to get the image from the API
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (compatible; QR Generator)'
            ]
        ]);
        
        $imageData = @file_get_contents($apiUrl, false, $context);
        
        if ($imageData !== false) {
            return 'data:image/png;base64,' . base64_encode($imageData);
        }
        
        // Fallback: create a simple placeholder QR
        return self::createPlaceholderQR($text);
    }
    
    /**
     * Create a placeholder QR code image
     */
    private static function createPlaceholderQR($text) {
        // Create a simple 200x200 white image with text
        $image = imagecreate(200, 200);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fill background
        imagefill($image, 0, 0, $white);
        
        // Add border
        imagerectangle($image, 0, 0, 199, 199, $black);
        
        // Add text
        $font = 3;
        $textWidth = strlen($text) * imagefontwidth($font);
        $textHeight = imagefontheight($font);
        $x = (200 - $textWidth) / 2;
        $y = (200 - $textHeight) / 2;
        
        imagestring($image, $font, $x, $y, 'QR: ' . substr($text, 0, 20), $black);
        
        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Get QR code as text (for debugging)
     */
    public static function text($text, $outfile = false) {
        $result = "QR Code for: " . $text;
        
        if ($outfile) {
            file_put_contents($outfile, $result);
            return true;
        } else {
            echo $result;
            return true;
        }
    }
}

// Compatibility functions
if (!function_exists('qr_png')) {
    function qr_png($text, $outfile = false, $level = QR_ECLEVEL_M, $size = 3, $margin = 4) {
        return QRcode::png($text, $outfile, $level, $size, $margin);
    }
}