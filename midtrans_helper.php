<?php
// Using official Midtrans PHP library
require_once 'vendor/autoload.php';

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

function createSnapToken($params, $server_key, $enabled_payments = null, $is_production = true) {
    // Configure Midtrans
    Config::$serverKey = $server_key;
    Config::$isProduction = $is_production;
    Config::$isSanitized = true;
    Config::$is3ds = true;

    // Add enabled payment methods if specified
    if ($enabled_payments) {
        $params['enabled_payments'] = $enabled_payments;
    }

    try {
        $snapToken = Snap::getSnapToken($params);
        return $snapToken;
    } catch (Exception $e) {
        throw new Exception('Midtrans Error: ' . $e->getMessage());
    }
}

function getTransactionStatus($order_id, $server_key, $is_production = true) {
    // Configure Midtrans
    Config::$serverKey = $server_key;
    Config::$isProduction = $is_production;
    Config::$isSanitized = true;
    Config::$is3ds = true;

    try {
        $status = Transaction::status($order_id);
        return (array)$status;
    } catch (Exception $e) {
        throw new Exception('Midtrans Error: ' . $e->getMessage());
    }
}

function cancelTransaction($order_id, $server_key, $is_production = true) {
    // Configure Midtrans
    Config::$serverKey = $server_key;
    Config::$isProduction = $is_production;
    Config::$isSanitized = true;
    Config::$is3ds = true;

    try {
        $result = Transaction::cancel($order_id);
        return (array)$result;
    } catch (Exception $e) {
        throw new Exception('Midtrans Error: ' . $e->getMessage());
    }
}
?>