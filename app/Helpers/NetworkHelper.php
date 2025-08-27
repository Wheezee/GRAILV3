<?php

namespace App\Helpers;

class NetworkHelper
{
    /**
     * Get the server's actual network IP address
     */
    public static function getServerIP()
    {
        // Prefer Python microservice if available
        $ipFromPython = self::getServerIPViaPython();
        if ($ipFromPython) {
            return $ipFromPython;
        }

        return self::getServerIPWithoutPython();
    }

    /**
     * Attempt to get host IP via the Python microservice exposed on port 5055.
     */
    public static function getServerIPViaPython()
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 0.2,
                ]
            ]);
            $response = @file_get_contents('http://host.docker.internal:5055/api/host-ip', false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['success']) && $data['success'] && !empty($data['ip']) && $data['ip'] !== '127.0.0.1') {
                    return $data['ip'];
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return null;
    }

    /**
     * Get server IP without using the Python microservice (direct methods only).
     */
    public static function getServerIPWithoutPython()
    {
        // Method 1: Try to get from $_SERVER variables
        if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
            return $_SERVER['SERVER_ADDR'];
        }
        
        if (!empty($_SERVER['LOCAL_ADDR']) && $_SERVER['LOCAL_ADDR'] !== '127.0.0.1') {
            return $_SERVER['LOCAL_ADDR'];
        }
        
        // Method 2: Use shell command to get network IP
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('ipconfig | findstr "IPv4" | findstr /v "127.0.0.1" | findstr /v "169.254"');
            if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $output, $matches)) {
                return $matches[1];
            }
        } else {
            $output = shell_exec("hostname -I | awk '{print $1}'");
            $ip = trim($output);
            if ($ip && $ip !== '127.0.0.1') {
                return $ip;
            }
        }
        
        // Method 3: Try to get from network interfaces
        if (function_exists('socket_create')) {
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($sock) {
                socket_connect($sock, '8.8.8.8', 53);
                socket_getsockname($sock, $ip);
                socket_close($sock);
                if ($ip && $ip !== '127.0.0.1') {
                    return $ip;
                }
            }
        }
        
        // Fallback: return a placeholder that indicates we need to configure manually
        return 'YOUR_NETWORK_IP';
    }
}
