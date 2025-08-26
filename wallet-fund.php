<?php
// wallet-fund.php
require_once('header.php');

if(!isset($_SESSION['customer'])) {
    header('location: '.BASE_URL.'logout.php');
    exit;
}
$cust_id = $_SESSION['customer']['cust_id'];
?>
<div class="container" style="max-width:500px;margin:40px auto;">
    <h3>Fund Your Wallet</h3>
    <form id="fundWalletForm" method="POST">
        <div class="form-group">
            <label>Amount (₦)</label>
            <input type="number" name="amount" class="form-control" min="1000" required>
        </div>
        <button type="submit" class="btn btn-primary">Proceed to Paystack</button>
    </form>
</div>
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('fundWalletForm').onsubmit = function(e){
    e.preventDefault();
    var amount = document.querySelector('[name="amount"]').value;
    var handler = PaystackPop.setup({
        key: 'pk_test_bc6f4cb0c4c19c220c21602fd5f6886b5707dec9',
        email: '<?php echo $_SESSION['customer']['cust_email']; ?>',
        amount: amount * 100,
        currency: 'NGN',
        ref: 'WALLET-'+Math.floor((Math.random() * 1000000000) + 1),
        callback: function(response){
            // Send to server for verification and credit wallet
            window.location = 'wallet-verify.php?reference=' + response.reference + '&amount=' + amount;
        },
        onClose: function(){ alert('Transaction cancelled'); }
    });
    handler.openIframe();
};
</script>
<?php require_once('footer.php'); ?>