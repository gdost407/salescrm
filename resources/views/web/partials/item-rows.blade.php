<div class="table-responsive mb-3">
<table class="table table-bordered"><thead><tr><th>Item / HSN</th><th>Qty</th><th>Rate</th><th>GST %</th><th>Taxable</th><th>GST</th><th>Total</th><th></th></tr></thead><tbody id="quotation-rows"></tbody></table>
</div>
<button type="button" id="add-quotation-item" class="btn btn-outline-primary mb-3">Add item</button>
<template id="quotation-row-template">
<tr>
<td style="min-width:230px"><select class="form-select item-select" required aria-label="Item"></select><input type="hidden" class="snapshot-id"><small class="item-hsn"></small></td>
<td><input class="form-control item-quantity" type="number" step="0.001" min="0.001" max="99999.999" required aria-label="Quantity" style="min-width:100px"></td>
<td><span class="item-rate"></span><small class="rate-basis d-block"></small></td><td class="item-gst"></td>
<td class="item-subtotal"></td><td class="item-tax"></td><td class="item-total"></td>
<td><button type="button" class="btn btn-sm btn-outline-danger remove-item" aria-label="Remove item">Remove</button></td>
</tr>
</template>
<noscript><div class="alert alert-warning">Enable JavaScript to select document items and preview totals.</div></noscript>
