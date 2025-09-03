<?php
// get_setting function is already defined in config/database.php

function get_promo_details($pdo, $kode_promo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM promo_codes 
            WHERE kode_promo = ? 
            AND status = 'active'
        ");
        $stmt->execute([$kode_promo]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in get_promo_details: " . $e->getMessage());
        return null;
    }
}

function get_midtrans_enabled_payments($pdo) {
    try {
        $stmt = $pdo->query("SELECT enabled_payments FROM midtrans_settings WHERE id = 1");
        $enabled_payments_json = $stmt->fetchColumn();

        if ($enabled_payments_json) {
            $payments = json_decode($enabled_payments_json, true);
            return is_array($payments) ? $payments : ['qris', 'bank_transfer'];
        }

        return ['qris', 'bank_transfer']; // Default fallback
    } catch (Exception $e) {
        error_log("Error getting enabled payments: " . $e->getMessage());
        return ['qris', 'bank_transfer']; // Default fallback
    }
}

function get_admin_fee_settings($pdo) {
    try {
        $stmt = $pdo->query("SELECT admin_fee_type, admin_fee_amount, admin_fee_percentage FROM midtrans_settings WHERE id = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [
            'admin_fee_type' => 'fixed',
            'admin_fee_amount' => 5000,
            'admin_fee_percentage' => 3.00
        ];
    }
}

function calculate_admin_fee($amount, $pdo) {
    $settings = get_admin_fee_settings($pdo);

    if ($settings['admin_fee_type'] === 'percentage') {
        return round($amount * ($settings['admin_fee_percentage'] / 100));
    } else {
        return $settings['admin_fee_amount'];
    }
}

function get_midtrans_production_mode($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT is_production FROM midtrans_settings WHERE id = 1");
        $stmt->execute();
        $is_production = $stmt->fetchColumn();
        return (bool)$is_production;
    } catch (Exception $e) {
        error_log("Error getting production mode: " . $e->getMessage());
        return true; // Default to production
    }
}

function update_setting($pdo, $setting_key, $value) {
    try {
        // Check if setting already exists
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $stmt->execute([$setting_key]);

        if ($stmt->fetch()) {
            // Update existing setting
            $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $setting_key]);
        } else {
            // Insert new setting
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, value) VALUES (?, ?)");
            $stmt->execute([$setting_key, $value]);
        }

        // Also update in midtrans_settings table for compatibility
        if ($setting_key === 'midtrans_server_key') {
            $stmt = $pdo->prepare("UPDATE midtrans_settings SET server_key = ? WHERE id = 1");
            $stmt->execute([$value]);
        } elseif ($setting_key === 'midtrans_client_key') {
            $stmt = $pdo->prepare("UPDATE midtrans_settings SET client_key = ? WHERE id = 1");
            $stmt->execute([$value]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Error updating setting: " . $e->getMessage());
        return false;
    }
}
?>