/**
 * Cartzy Admin - Reports Export Scripts
 * Handles CSV report generation and download
 */

function exportCSV() {
    const dataEl = document.getElementById('reportsExportData');
    const reportType = dataEl?.dataset?.reportType || 'sales';
    let categories = [];
    try {
        categories = dataEl?.dataset?.categories ? JSON.parse(dataEl.dataset.categories) : [];
    } catch (e) {
        categories = [];
    }

    let csv = "Category,Gross Sales,10% Platform Commission,Merchant Payout\n";
    categories.forEach(c => {
        const cat = (c.category || '').replace(/"/g, '""');
        const sales = parseFloat(c.sales) || 0;
        const comm = parseFloat(c.commission) || 0;
        const payout = (sales * 0.9).toFixed(2);
        csv += `"${cat}",${sales},${comm},${payout}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", `MarketStore_Reports_${reportType}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
