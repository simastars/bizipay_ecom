<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Virtual Account Reconciliation</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-md-8">
		<div class="box box-primary">
			<div class="box-body">
				<p>Use this tool to reconcile provider transaction data with local records. You can paste JSON from the provider or upload a file.</p>
				<form id="reconcile_form">
					<div class="form-group">
						<label>Paste provider transactions JSON</label>
						<textarea id="provider_json" class="form-control" rows="12"></textarea>
					</div>
					<button type="button" id="do_reconcile" class="btn btn-primary">Run Reconciliation</button>
				</form>
				<div id="reconcile_result" style="margin-top:12px;"></div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="box box-default">
			<div class="box-body">
				<h4>Instructions</h4>
				<p>Paste provider webhook array or transaction list. The tool will try to match by account_number and amount and report missing or unmatched transactions.</p>
			</div>
		</div>
	</div>
</div>
</section>

<?php require_once('footer.php'); ?>

<script>
document.getElementById('do_reconcile').addEventListener('click', function(){
    var raw = document.getElementById('provider_json').value;
    if(!raw) { alert('Paste provider JSON'); return; }
    try{ var arr = JSON.parse(raw); } catch(e){ alert('Invalid JSON'); return; }
    fetch('admin/va-reconcile-process.php', {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({data:arr})
    }).then(r=>r.json()).then(res=>{
        document.getElementById('reconcile_result').innerText = JSON.stringify(res, null, 2);
    }).catch(e=>{ document.getElementById('reconcile_result').innerText = 'Error'; });
});
</script>
