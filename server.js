/* =========================================================
   خادم تفعيل وإدارة اشتراكات - برنامج إدارة الديون (v2)
   Node.js + Express + node:sqlite
   ========================================================= */
const express = require('express');
const path = require('path');
const crypto = require('crypto');
const { DatabaseSync } = require('node:sqlite');

const PORT = process.env.PORT || 3000;
const DB_FILE = process.env.DB_FILE || path.join(__dirname, 'licenses.db');
const INITIAL_ADMIN_USER = process.env.ADMIN_USER || 'admin';
const INITIAL_ADMIN_PASS = process.env.ADMIN_PASS || 'ChangeMe123';

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES = 15;

const app = express();
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

/* ---------------- Database ---------------- */
const db = new DatabaseSync(DB_FILE);
db.exec(`
  CREATE TABLE IF NOT EXISTS licenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT UNIQUE NOT NULL,
    customer_name TEXT NOT NULL,
    phone TEXT,
    plan TEXT DEFAULT 'شهري',
    months INTEGER DEFAULT 1,
    trial_days INTEGER,
    price REAL DEFAULT 0,
    status TEXT DEFAULT 'pending',
    created_at TEXT NOT NULL,
    activated_at TEXT,
    expires_at TEXT,
    device_info TEXT,
    notes TEXT
  );
  CREATE TABLE IF NOT EXISTS join_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    note TEXT,
    status TEXT DEFAULT 'pending',
    created_at TEXT NOT NULL
  );
  CREATE TABLE IF NOT EXISTS admin_sessions (
    token TEXT PRIMARY KEY,
    created_at TEXT NOT NULL
  );
  CREATE TABLE IF NOT EXISTS admin_credentials (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    username TEXT NOT NULL,
    password TEXT NOT NULL
  );
  CREATE TABLE IF NOT EXISTS app_info (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    company_name TEXT DEFAULT '',
    whatsapp TEXT DEFAULT '',
    email TEXT DEFAULT '',
    about_text TEXT DEFAULT ''
  );
  CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT NOT NULL,
    success INTEGER NOT NULL,
    created_at TEXT NOT NULL
  );
  CREATE TABLE IF NOT EXISTS audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    action TEXT NOT NULL,
    details TEXT,
    created_at TEXT NOT NULL
  );
`);

const existingCred = db.prepare('SELECT * FROM admin_credentials WHERE id = 1').get();
if (!existingCred) {
  db.prepare('INSERT INTO admin_credentials (id, username, password) VALUES (1, ?, ?)')
    .run(INITIAL_ADMIN_USER, INITIAL_ADMIN_PASS);
}
const existingAppInfo = db.prepare('SELECT * FROM app_info WHERE id = 1').get();
if (!existingAppInfo) {
  db.prepare('INSERT INTO app_info (id, company_name, whatsapp, email, about_text) VALUES (1, ?, ?, ?, ?)')
    .run('', '', '', '');
}

/* ---------------- Helpers ---------------- */
function genCode() {
  return crypto.randomBytes(4).toString('hex').toUpperCase().match(/.{1,4}/g).join('-');
}
function nowISO() { return new Date().toISOString(); }
function addMonths(dateISO, months) {
  const d = new Date(dateISO);
  d.setMonth(d.getMonth() + Number(months));
  return d.toISOString();
}
function addDays(dateISO, days) {
  const d = new Date(dateISO);
  d.setDate(d.getDate() + Number(days));
  return d.toISOString();
}
function computeExpiry(row, fromISO) {
  return row.trial_days ? addDays(fromISO, row.trial_days) : addMonths(fromISO, row.months || 1);
}
function logAudit(action, details) {
  db.prepare('INSERT INTO audit_log (action, details, created_at) VALUES (?, ?, ?)')
    .run(action, JSON.stringify(details || {}), nowISO());
}
function clientIp(req) {
  return (req.headers['x-forwarded-for'] || req.socket.remoteAddress || 'unknown').split(',')[0].trim();
}
function requireAdmin(req, res, next) {
  const auth = req.headers.authorization || '';
  const token = auth.startsWith('Bearer ') ? auth.slice(7) : null;
  if (!token) return res.status(401).json({ error: 'unauthorized' });
  const row = db.prepare('SELECT token FROM admin_sessions WHERE token = ?').get(token);
  if (!row) return res.status(401).json({ error: 'unauthorized' });
  next();
}
function refreshStatus(row) {
  if (row.status === 'active' && row.expires_at && new Date(row.expires_at) < new Date()) {
    db.prepare('UPDATE licenses SET status = ? WHERE id = ?').run('expired', row.id);
    row.status = 'expired';
  }
  return row;
}

/* ---------------- Public API (يستخدمها تطبيق العميل) ---------------- */
/* ---------------- Public: Join Requests (from customer app) ---------------- */
app.post('/api/join-request', (req, res) => {
  const { customerName, phone, note } = req.body || {};
  if (!customerName || !phone) return res.status(400).json({ ok: false, error: 'missing_fields' });
  if (!/^[0-9]{11}$/.test(phone)) return res.status(400).json({ ok: false, error: 'invalid_phone' });
  const created = nowISO();
  db.prepare(`INSERT INTO join_requests (customer_name, phone, note, status, created_at) VALUES (?, ?, ?, 'pending', ?)`)
    .run(customerName, phone, note || '', created);
  logAudit('join_request_received', { customerName, phone });
  res.json({ ok: true });
});

app.post('/api/activate', (req, res) => {
  const { code, deviceInfo } = req.body || {};
  if (!code) return res.status(400).json({ valid: false, reason: 'missing_code' });
  let row = db.prepare('SELECT * FROM licenses WHERE code = ?').get(code.trim().toUpperCase());
  if (!row) return res.json({ valid: false, reason: 'not_found' });
  row = refreshStatus(row);
  if (row.status === 'revoked') return res.json({ valid: false, reason: 'revoked' });
  if (row.status === 'expired') return res.json({ valid: false, reason: 'expired', expiresAt: row.expires_at });
  if (row.status === 'pending') {
    const now = nowISO();
    const expires = computeExpiry(row, now);
    db.prepare('UPDATE licenses SET status = ?, activated_at = ?, expires_at = ?, device_info = ? WHERE id = ?')
      .run('active', now, expires, deviceInfo || '', row.id);
    row = db.prepare('SELECT * FROM licenses WHERE id = ?').get(row.id);
    logAudit('client_activated', { code: row.code, customer: row.customer_name });
  }
  return res.json({
    valid: true, customerName: row.customer_name, plan: row.plan,
    activatedAt: row.activated_at, expiresAt: row.expires_at,
  });
});

app.post('/api/check-subscription', (req, res) => {
  const { code } = req.body || {};
  if (!code) return res.status(400).json({ valid: false, reason: 'missing_code' });
  let row = db.prepare('SELECT * FROM licenses WHERE code = ?').get(code.trim().toUpperCase());
  if (!row) return res.json({ valid: false, reason: 'not_found' });
  row = refreshStatus(row);
  if (row.status !== 'active') return res.json({ valid: false, reason: row.status, expiresAt: row.expires_at });
  return res.json({ valid: true, expiresAt: row.expires_at, plan: row.plan });
});

// معلومات "حول التطبيق" — يقرؤها تطبيق العميل للعرض فقط (للقراءة، بدون تسجيل دخول)
app.get('/api/app-info', (req, res) => {
  const row = db.prepare('SELECT company_name, whatsapp, email, about_text FROM app_info WHERE id = 1').get();
  res.json({
    companyName: row.company_name || '',
    whatsapp: row.whatsapp || '',
    email: row.email || '',
    aboutText: row.about_text || '',
  });
});

app.post('/api/admin/app-info', requireAdmin, (req, res) => {
  const { companyName, whatsapp, email, aboutText } = req.body || {};
  db.prepare('UPDATE app_info SET company_name=?, whatsapp=?, email=?, about_text=? WHERE id=1')
    .run(companyName||'', whatsapp||'', email||'', aboutText||'');
  logAudit('app_info_updated', {});
  res.json({ ok: true });
});

/* ---------------- Admin Auth (مع حماية من محاولات التخمين) ---------------- */
app.post('/api/admin/login', (req, res) => {
  const ip = clientIp(req);
  const since = new Date(Date.now() - LOCKOUT_MINUTES * 60000).toISOString();
  const recentFails = db.prepare(
    'SELECT COUNT(*) as c FROM login_attempts WHERE ip = ? AND success = 0 AND created_at > ?'
  ).get(ip, since).c;

  if (recentFails >= MAX_LOGIN_ATTEMPTS) {
    return res.status(429).json({ error: 'too_many_attempts', retryAfterMinutes: LOCKOUT_MINUTES });
  }

  const { username, password } = req.body || {};
  const cred = db.prepare('SELECT * FROM admin_credentials WHERE id = 1').get();
  const ok = cred && username === cred.username && password === cred.password;

  db.prepare('INSERT INTO login_attempts (ip, success, created_at) VALUES (?, ?, ?)').run(ip, ok ? 1 : 0, nowISO());

  if (!ok) {
    logAudit('login_failed', { ip, username });
    return res.status(401).json({ error: 'invalid_credentials', attemptsLeft: Math.max(0, MAX_LOGIN_ATTEMPTS - recentFails - 1) });
  }
  const token = crypto.randomBytes(24).toString('hex');
  db.prepare('INSERT INTO admin_sessions (token, created_at) VALUES (?, ?)').run(token, nowISO());
  logAudit('login_success', { ip });
  res.json({ token });
});

app.post('/api/admin/change-password', requireAdmin, (req, res) => {
  const { currentPassword, newUsername, newPassword } = req.body || {};
  const cred = db.prepare('SELECT * FROM admin_credentials WHERE id = 1').get();
  if (currentPassword !== cred.password) return res.status(401).json({ error: 'wrong_current_password' });
  if (!newPassword || newPassword.length < 6) return res.status(400).json({ error: 'password_too_short' });
  db.prepare('UPDATE admin_credentials SET username = ?, password = ? WHERE id = 1')
    .run(newUsername || cred.username, newPassword);
  logAudit('password_changed', {});
  res.json({ ok: true });
});

/* ---------------- Admin API: التراخيص (بحث + فلترة) ---------------- */
app.get('/api/admin/licenses', requireAdmin, (req, res) => {
  const { q, status } = req.query;
  let rows = db.prepare('SELECT * FROM licenses ORDER BY id DESC').all();
  rows.forEach(refreshStatus);
  if (q) {
    const needle = q.toLowerCase();
    rows = rows.filter(r =>
      r.customer_name.toLowerCase().includes(needle) ||
      (r.phone || '').includes(needle) ||
      r.code.toLowerCase().includes(needle)
    );
  }
  if (status && status !== 'all') rows = rows.filter(r => r.status === status);
  res.json(rows);
});

app.get('/api/admin/licenses/export', requireAdmin, (req, res) => {
  const rows = db.prepare('SELECT * FROM licenses ORDER BY id DESC').all();
  rows.forEach(refreshStatus);
  const header = ['الاسم', 'الهاتف', 'الكود', 'الخطة', 'السعر', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التفعيل', 'ينتهي بتاريخ'];
  const csvRows = [header.join(',')];
  rows.forEach(r => {
    csvRows.push([r.customer_name, r.phone || '', r.code, r.plan, r.price || 0, r.status, r.created_at, r.activated_at || '', r.expires_at || '']
      .map(v => `"${String(v).replace(/"/g, '""')}"`).join(','));
  });
  const csv = '\uFEFF' + csvRows.join('\n');
  res.setHeader('Content-Type', 'text/csv; charset=utf-8');
  res.setHeader('Content-Disposition', 'attachment; filename="licenses-export.csv"');
  res.send(csv);
});

app.post('/api/admin/licenses', requireAdmin, (req, res) => {
  const { customerName, phone, plan, months, price } = req.body || {};
  if (!customerName) return res.status(400).json({ error: 'missing_customer_name' });
  const code = genCode();
  const created = nowISO();
  const monthsVal = Number(months) || 1;
  db.prepare(`INSERT INTO licenses (code, customer_name, phone, plan, months, price, status, created_at, expires_at)
              VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NULL)`)
    .run(code, customerName, phone || '', plan || 'شهري', monthsVal, Number(price) || 0, created);
  const row = db.prepare('SELECT * FROM licenses WHERE code = ?').get(code);
  logAudit('license_created', { code, customerName });
  res.json(row);
});

app.post('/api/admin/licenses/trial', requireAdmin, (req, res) => {
  const { customerName, phone, trialDays } = req.body || {};
  if (!customerName) return res.status(400).json({ error: 'missing_customer_name' });
  const days = Number(trialDays) || 7;
  if (days < 1) return res.status(400).json({ error: 'invalid_trial_days' });
  const code = genCode();
  const created = nowISO();
  db.prepare(`INSERT INTO licenses (code, customer_name, phone, plan, months, trial_days, price, status, created_at, expires_at)
              VALUES (?, ?, ?, 'تجريبي', 1, ?, 0, 'pending', ?, NULL)`)
    .run(code, customerName, phone || '', days, created);
  const row = db.prepare('SELECT * FROM licenses WHERE code = ?').get(code);
  logAudit('trial_license_created', { code, customerName, trialDays: days });
  res.json(row);
});

app.post('/api/admin/licenses/:id/renew', requireAdmin, (req, res) => {
  const { months, price } = req.body || {};
  const row = db.prepare('SELECT * FROM licenses WHERE id = ?').get(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  if (!row.activated_at) {
    // Never activated yet: renewing means activating now, counting the full term from today
    const now = nowISO();
    const expires = computeExpiry(row, now);
    db.prepare('UPDATE licenses SET status = ?, activated_at = ?, expires_at = ?, price = price + ? WHERE id = ?')
      .run('active', now, expires, Number(price) || 0, row.id);
    logAudit('license_activated', { code: row.code });
    return res.json(db.prepare('SELECT * FROM licenses WHERE id = ?').get(row.id));
  }
  const base = (row.expires_at && new Date(row.expires_at) > new Date()) ? row.expires_at : nowISO();
  const newExpiry = addMonths(base, months || 1);
  db.prepare('UPDATE licenses SET expires_at = ?, status = ?, price = price + ? WHERE id = ?')
    .run(newExpiry, (row.status === 'revoked' || row.status === 'expired') ? 'active' : row.status, Number(price) || 0, row.id);
  logAudit('license_renewed', { code: row.code, months });
  res.json(db.prepare('SELECT * FROM licenses WHERE id = ?').get(row.id));
});

app.post('/api/admin/licenses/:id/revoke', requireAdmin, (req, res) => {
  const row = db.prepare('SELECT * FROM licenses WHERE id = ?').get(req.params.id);
  db.prepare('UPDATE licenses SET status = ? WHERE id = ?').run('revoked', req.params.id);
  logAudit('license_revoked', { code: row && row.code });
  res.json(db.prepare('SELECT * FROM licenses WHERE id = ?').get(req.params.id));
});

app.post('/api/admin/licenses/:id/set-expiry', requireAdmin, (req, res) => {
  const { expiresAt } = req.body || {};
  const row = db.prepare('SELECT * FROM licenses WHERE id = ?').get(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  if (!expiresAt) return res.status(400).json({ error: 'missing_expires_at' });
  db.prepare('UPDATE licenses SET expires_at = ? WHERE id = ?').run(expiresAt, row.id);
  logAudit('expiry_manually_fixed', { code: row.code, from: row.expires_at, to: expiresAt });
  res.json(db.prepare('SELECT * FROM licenses WHERE id = ?').get(row.id));
});

app.post('/api/admin/licenses/:id/reactivate', requireAdmin, (req, res) => {
  const row = db.prepare('SELECT * FROM licenses WHERE id = ?').get(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  const newStatus = row.activated_at ? 'active' : 'pending';
  db.prepare('UPDATE licenses SET status = ? WHERE id = ?').run(newStatus, row.id);
  logAudit('license_reactivated', { code: row.code });
  res.json(db.prepare('SELECT * FROM licenses WHERE id = ?').get(row.id));
});

app.post('/api/admin/licenses/:id/activate', requireAdmin, (req, res) => {
  const row = db.prepare('SELECT * FROM licenses WHERE id = ?').get(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  const now = nowISO();
  const expires = computeExpiry(row, now);
  db.prepare('UPDATE licenses SET status = ?, activated_at = ?, expires_at = ? WHERE id = ?')
    .run('active', now, expires, row.id);
  logAudit('license_activated', { code: row.code });
  res.json(db.prepare('SELECT * FROM licenses WHERE id = ?').get(row.id));
});

/* ---------------- Admin: Join Requests ---------------- */
app.get('/api/admin/join-requests', requireAdmin, (req, res) => {
  const rows = db.prepare(`SELECT * FROM join_requests ORDER BY id DESC`).all();
  res.json(rows);
});

app.post('/api/admin/join-requests/:id/dismiss', requireAdmin, (req, res) => {
  const row = db.prepare('SELECT * FROM join_requests WHERE id = ?').get(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  db.prepare(`UPDATE join_requests SET status = 'dismissed' WHERE id = ?`).run(row.id);
  logAudit('join_request_dismissed', { customerName: row.customer_name });
  res.json({ ok: true });
});

app.post('/api/admin/join-requests/:id/mark-handled', requireAdmin, (req, res) => {
  const row = db.prepare('SELECT * FROM join_requests WHERE id = ?').get(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  db.prepare(`UPDATE join_requests SET status = 'handled' WHERE id = ?`).run(row.id);
  res.json({ ok: true });
});

app.get('/api/admin/stats', requireAdmin, (req, res) => {
  const rows = db.prepare('SELECT * FROM licenses').all();
  rows.forEach(refreshStatus);
  const revenue = rows.reduce((sum, r) => sum + (r.price || 0), 0);
  const pendingRequests = db.prepare(`SELECT COUNT(*) as c FROM join_requests WHERE status = 'pending'`).get().c;
  res.json({
    total: rows.length,
    active: rows.filter(r => r.status === 'active').length,
    pending: rows.filter(r => r.status === 'pending').length,
    expired: rows.filter(r => r.status === 'expired').length,
    revoked: rows.filter(r => r.status === 'revoked').length,
    revenue,
    pendingRequests,
  });
});

app.get('/api/admin/audit-log', requireAdmin, (req, res) => {
  const rows = db.prepare('SELECT * FROM audit_log ORDER BY id DESC LIMIT 200').all();
  res.json(rows.map(r => ({ ...r, details: JSON.parse(r.details || '{}') })));
});

app.listen(PORT, () => {
  console.log(`License server running on http://localhost:${PORT}`);
  console.log(`Admin dashboard: http://localhost:${PORT}/admin.html`);
});
