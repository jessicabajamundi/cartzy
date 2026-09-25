/**
 * Cartzy Admin - Commission Calculator Scripts
 */

function calculateCommission() {
    const amount = parseFloat(document.getElementById('calcOrderAmount').value) || 0;
    const commission = amount * 0.10;
    const sellerNet = amount - commission;

    const cutEl = document.getElementById('calcPlatformCut');
    const netEl = document.getElementById('calcSellerNet');
    if (cutEl) cutEl.innerText = '₱' + commission.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (netEl) netEl.innerText = '₱' + sellerNet.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
