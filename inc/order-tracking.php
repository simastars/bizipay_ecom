<?php
// Simple order tracking helper: creates history table if missing and provides add/get helpers.
if(!function_exists('ot_ensure_table')){
    function ot_ensure_table($pdo){
        $sql = "CREATE TABLE IF NOT EXISTS tbl_order_status_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id VARCHAR(255) NOT NULL,
            status_type VARCHAR(50) NOT NULL,
            status_value VARCHAR(100) NOT NULL,
            note TEXT NULL,
            changed_by VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try{
            $pdo->exec($sql);
        }catch(Exception $e){
            // Ignore - table creation may fail if permissions are restricted. Functions will still attempt inserts.
        }
    }
}

if(!function_exists('ot_add_history')){
    function ot_add_history($pdo, $payment_id, $status_type, $status_value, $note = '', $changed_by = null){
        ot_ensure_table($pdo);
        try{
            $stmt = $pdo->prepare("INSERT INTO tbl_order_status_history (payment_id,status_type,status_value,note,changed_by) VALUES (?,?,?,?,?)");
            $stmt->execute(array($payment_id, $status_type, $status_value, $note, $changed_by));
        }catch(Exception $e){
            // ignore on failure
        }
    }
}

if(!function_exists('ot_get_history')){
    function ot_get_history($pdo, $payment_id){
        ot_ensure_table($pdo);
        try{
            $stmt = $pdo->prepare("SELECT * FROM tbl_order_status_history WHERE payment_id=? ORDER BY created_at DESC");
            $stmt->execute(array($payment_id));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            return array();
        }
    }
}

?>
