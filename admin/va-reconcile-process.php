<?php
require_once('header.php');
header('Content-Type: application/json');
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if(!$payload || !isset($payload['data'])){ echo json_encode(['status'=>false,'message'=>'Invalid']); exit; }
$data = $payload['data'];

$report = [ 'matched'=>[], 'unmatched'=>[], 'unknown_account'=>[] ];
foreach($data as $t){
    // provider transaction object mapping. Try common keys
    $acct = $t['account_number'] ?? $t['account'] ?? ($t['dest_account'] ?? null);
    $amount = isset($t['amount']) ? floatval($t['amount']) : (isset($t['credit'])?floatval($t['credit']):0);
    $providerRef = $t['reference'] ?? $t['txid'] ?? $t['provider_ref'] ?? null;
    if(!$acct){ $report['unknown_account'][] = $t; continue; }
    // find VA
    $st = $pdo->prepare("SELECT * FROM tbl_virtual_account WHERE account_number = ? LIMIT 1");
    $st->execute([$acct]);
    $va = $st->fetch(PDO::FETCH_ASSOC);
    if(!$va){ $report['unknown_account'][] = $t; continue; }
    // find tx by providerRef or amount
    $stt = $pdo->prepare("SELECT * FROM tbl_virtual_account_tx WHERE provider_ref = ? LIMIT 1");
    $stt->execute([$providerRef]);
    $tx = $stt->fetch(PDO::FETCH_ASSOC);
    if($tx){
        $report['matched'][] = ['provider'=>$t, 'local'=>$tx];
    } else {
        // try match by amount and not yet credited
        $st2 = $pdo->prepare("SELECT * FROM tbl_virtual_account_tx WHERE virtual_account_id = ? AND amount = ? LIMIT 1");
        $st2->execute([$va['id'], $amount]);
        $tx2 = $st2->fetch(PDO::FETCH_ASSOC);
        if($tx2){ $report['matched'][] = ['provider'=>$t, 'local'=>$tx2]; }
        else { $report['unmatched'][] = ['provider'=>$t, 'va'=>$va]; }
    }
}

echo json_encode($report);

?>
