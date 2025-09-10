<?php
// single-file PHP: bind_bvn.php
// Usage: place on a PHP-enabled server, open in browser, enter email, bvn and your Bearer token, then submit.

$endpoint = 'https://api.billstack.co/v2/thirdparty/upgradeVirtualAccount/';
$response = null;
$httpCode = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $bvn   = trim($_POST['bvn'] ?? '');
    $token = trim($_POST['token'] ?? '');

    // basic validation
    if ($email === '' || $bvn === '' || $token === '') {
        $response = ['error' => 'email, bvn and token are required'];
    } else {
        $payload = json_encode(['customer' => $email, 'bvn' => $bvn]);

        $ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_VERBOSE, true);


        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            $response = ['error' => 'curl error', 'message' => $curlErr];
        } else {
            // try decode JSON, otherwise show raw
            $json = json_decode($raw, true);
            $response = $json !== null ? $json : ['raw' => $raw];
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bind BVN (BillStack)</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;max-width:820px;margin:24px auto;padding:0 12px}
    label{display:block;margin:12px 0 4px}
    input[type=text], input[type=password]{width:100%;padding:8px;border:1px solid #ccc;border-radius:6px}
    button{margin-top:10px;padding:10px 14px;border-radius:8px;border:none;cursor:pointer}
    pre{background:#f6f8fa;padding:12px;border-radius:8px;overflow:auto}
  </style>
</head>
<body>
  <h2>Bind BVN to Virtual Account</h2>
  <form method="post" autocomplete="off">
    <label>Email (customer email used when reserving VA)</label>
    <input type="text" name="email" value="<?= isset($email) ? htmlentities($email) : '' ?>" required>

    <label>BVN (numbers only)</label>
    <input type="text" name="bvn" value="<?= isset($bvn) ? htmlentities($bvn) : '' ?>" required>

    <label>Bearer Token (Your API secret)</label>
    <input type="password" name="token" value="<?= isset($token) ? htmlentities($token) : '' ?>" required>

    <button type="submit">Bind BVN</button>
  </form>

  <?php if ($response !== null): ?>
    <h3>Response (HTTP <?= $httpCode ?? '---' ?>)</h3>
    <pre><?= htmlentities(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
  <?php endif; ?>

  <p style="font-size:0.9em;color:#555">
    Note: this sends a POST to the BillStack endpoint documented in their API reference.
  </p>
</body>
</html>
