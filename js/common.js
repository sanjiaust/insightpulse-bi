// ---------- Shared formatting helpers ----------
function formatCurrency(n) {
  return '$' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function formatNumber(n) {
  return Number(n).toLocaleString('en-US');
}
function formatDate(d) {
  const dt = new Date(d + 'T00:00:00');
  return dt.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
}
function monthLabel(ym) {
  if (!ym) return '–';
  const [y, m] = ym.split('-');
  const dt = new Date(Number(y), Number(m) - 1, 1);
  return dt.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}
function formatPercent(n, withSign) {
  const num = Number(n);
  const sign = withSign && num > 0 ? '+' : '';
  return sign + num.toFixed(1) + '%';
}
function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}
function redirectIfUnauthorized(res) {
  if (res.status === 401) {
    window.location.href = 'login.php';
    return true;
  }
  return false;
}
