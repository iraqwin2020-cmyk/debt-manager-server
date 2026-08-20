/* =========================================================
   خادم تفعيل وإدارة اشتراكات - برنامج إدارة الديون (v3)
   Node.js + Express + ملف JSON محلي (بدون أي تبعية أصلية،
   يعمل على أي إصدار Node.js وأي منصة استضافة بدون استثناء)
   ========================================================= */
const path = require('path');
const fs = require('fs');

/* Crash-safe logging: writes ANY error (even during module loading) to
   crash.log next to this file, so it can be read via File Manager on
   shared hosting where console logs aren't visible. Registered FIRST,
   before any risky require(), so nothing can crash silently. */
const CRASH_LOG = path.join(__dirname, 'crash.log');
function logCrash(label, err) {
  try {
    fs.appendFileSync(CRASH_LOG, `[${new Date().toISOString()}] ${label}:\n${(err && err.stack) || err}\n\n`);
  } catch (e) {}
  try { console.error(label, err); } catch (e) {}
}
process.on('uncaughtException', (err) => logCrash('UNCAUGHT EXCEPTION', err));
process.on('unhandledRejection', (err) => logCrash('UNHANDLED REJECTION', err));

let express, crypto;
try {
  express = require('express');
  crypto = require('crypto');
} catch (err) {
  logCrash('REQUIRE FAILED (is "npm install" needed? run it from cPanel Setup Node.js App)', err);
  throw err;
}

const PORT = process.env.PORT || 3000;

const DB_FILE = process.env.DB_FILE || path.join(__dirname, 'data.json');
const INITIAL_ADMIN_USER = process.env.ADMIN_USER || 'admin';
const INITIAL_ADMIN_PASS = process.env.ADMIN_PASS || 'ChangeMe123';

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES = 15;

const app = express();
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  if (req.method === 'OPTIONS') return res.sendStatus(204);
  next();
});
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

/* ---------------- Database (JSON file, zero native dependencies) ---------------- */
function defaultData() {
  return {
    licenses: [],
    join_requests: [],
    admin_sessions: [],
    admin_credentials: { username: INITIAL_ADMIN_USER, password: INITIAL_ADMIN_PASS },
    app_info: { company_name: '', whatsapp: '', email: '', about_text: '' },
    plans: [
      { id: 1, name: 'شهري', months: 1, price: 15000 },
      { id: 2, name: 'سنوي', months: 12, price: 150000 },
    ],
    login_attempts: [],
    audit_log: [],
    _seq: { licenses: 0, join_requests: 0, login_attempts: 0, audit_log: 0, plans: 2 },
  };
}
let DB;
function loadDB() {
  try {
    const raw = fs.readFileSync(DB_FILE, 'utf8');
    DB = JSON.parse(raw);
    const def = defaultData();
    for (const k of Object.keys(def)) if (!(k in DB)) DB[k] = def[k];
  } catch (e) {
    DB = defaultData();
    saveDB();
  }
}
function saveDB() {
  fs.writeFileSync(DB_FILE, JSON.stringify(DB, null, 2), 'utf8');
}
function nextId(table) {
  DB._seq[table] = (DB._seq[table] || 0) + 1;
  return DB._seq[table];
}
loadDB();

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
  DB.audit_log.unshift({ id: nextId('audit_log'), action, details: JSON.stringify(details || {}), created_at: nowISO() });
  if (DB.audit_log.length > 500) DB.audit_log.length = 500;
}
function clientIp(req) {
  return (req.headers['x-forwarded-for'] || req.socket.remoteAddress || 'unknown').split(',')[0].trim();
}
function requireAdmin(req, res, next) {
  const auth = req.headers.authorization || '';
  const token = auth.startsWith('Bearer ') ? auth.slice(7) : null;
  if (!token) return res.status(401).json({ error: 'unauthorized' });
  const found = DB.admin_sessions.find(s => s.token === token);
  if (!found) return res.status(401).json({ error: 'unauthorized' });
  next();
}
function refreshStatus(row) {
  if (row.status === 'active' && row.expires_at && new Date(row.expires_at) < new Date()) {
    row.status = 'expired';
  }
  return row;
}
function findLicense(id) {
  return DB.licenses.find(l => String(l.id) === String(id));
}

/* ---------------- Public API (يستخدمها تطبيق العميل) ---------------- */
app.post('/api/join-request', (req, res) => {
  const { customerName, phone, note } = req.body || {};
  if (!customerName || !phone) return res.status(400).json({ ok: false, error: 'missing_fields' });
  if (!/^[0-9]{11}$/.test(phone)) return res.status(400).json({ ok: false, error: 'invalid_phone' });
  DB.join_requests.unshift({ id: nextId('join_requests'), customer_name: customerName, phone, note: note || '', status: 'pending', created_at: nowISO() });
  logAudit('join_request_received', { customerName, phone });
  saveDB();
  res.json({ ok: true });
});

app.post('/api/activate', (req, res) => {
  const { code, deviceInfo } = req.body || {};
  if (!code) return res.status(400).json({ valid: false, reason: 'missing_code' });
  let row = DB.licenses.find(l => l.code === code.trim().toUpperCase());
  if (!row) return res.json({ valid: false, reason: 'not_found' });
  row = refreshStatus(row);
  if (row.status === 'revoked') return res.json({ valid: false, reason: 'revoked' });
  if (row.status === 'expired') return res.json({ valid: false, reason: 'expired', expiresAt: row.expires_at });
  if (row.status === 'pending') {
    const now = nowISO();
    row.status = 'active';
    row.activated_at = now;
    row.expires_at = computeExpiry(row, now);
    row.device_info = deviceInfo || '';
    logAudit('client_activated', { code: row.code, customer: row.customer_name });
  }
  saveDB();
  return res.json({
    valid: true, customerName: row.customer_name, plan: row.plan,
    activatedAt: row.activated_at, expiresAt: row.expires_at,
  });
});

app.post('/api/check-subscription', (req, res) => {
  const { code } = req.body || {};
  if (!code) return res.status(400).json({ valid: false, reason: 'missing_code' });
  let row = DB.licenses.find(l => l.code === code.trim().toUpperCase());
  if (!row) return res.json({ valid: false, reason: 'not_found' });
  row = refreshStatus(row);
  saveDB();
  if (row.status !== 'active') return res.json({ valid: false, reason: row.status, expiresAt: row.expires_at });
  return res.json({ valid: true, expiresAt: row.expires_at, plan: row.plan });
});

app.get('/api/app-info', (req, res) => {
  const info = DB.app_info;
  res.json({
    companyName: info.company_name || '',
    whatsapp: info.whatsapp || '',
    email: info.email || '',
    aboutText: info.about_text || '',
  });
});

app.post('/api/admin/app-info', requireAdmin, (req, res) => {
  const { companyName, whatsapp, email, aboutText } = req.body || {};
  DB.app_info = { company_name: companyName || '', whatsapp: whatsapp || '', email: email || '', about_text: aboutText || '' };
  logAudit('app_info_updated', {});
  saveDB();
  res.json({ ok: true });
});

/* ---------------- Admin Auth (مع حماية من محاولات التخمين) ---------------- */
app.post('/api/admin/login', (req, res) => {
  const ip = clientIp(req);
  const since = new Date(Date.now() - LOCKOUT_MINUTES * 60000).toISOString();
  const recentFails = DB.login_attempts.filter(a => a.ip === ip && a.success === 0 && a.created_at > since).length;

  if (recentFails >= MAX_LOGIN_ATTEMPTS) {
    return res.status(429).json({ error: 'too_many_attempts', retryAfterMinutes: LOCKOUT_MINUTES });
  }

  const { username, password } = req.body || {};
  const cred = DB.admin_credentials;
  const ok = cred && username === cred.username && password === cred.password;

  DB.login_attempts.push({ id: nextId('login_attempts'), ip, success: ok ? 1 : 0, created_at: nowISO() });
  if (DB.login_attempts.length > 1000) DB.login_attempts = DB.login_attempts.slice(-500);

  if (!ok) {
    logAudit('login_failed', { ip, username });
    saveDB();
    return res.status(401).json({ error: 'invalid_credentials', attemptsLeft: Math.max(0, MAX_LOGIN_ATTEMPTS - recentFails - 1) });
  }
  const token = crypto.randomBytes(24).toString('hex');
  DB.admin_sessions.push({ token, created_at: nowISO() });
  logAudit('login_success', { ip });
  saveDB();
  res.json({ token });
});

app.post('/api/admin/change-password', requireAdmin, (req, res) => {
  const { currentPassword, newUsername, newPassword } = req.body || {};
  const cred = DB.admin_credentials;
  if (currentPassword !== cred.password) return res.status(401).json({ error: 'wrong_current_password' });
  if (!newPassword || newPassword.length < 6) return res.status(400).json({ error: 'password_too_short' });
  DB.admin_credentials = { username: newUsername || cred.username, password: newPassword };
  logAudit('password_changed', {});
  saveDB();
  res.json({ ok: true });
});

/* ---------------- Admin API: التراخيص (بحث + فلترة) ---------------- */
app.get('/api/admin/licenses', requireAdmin, (req, res) => {
  const { q, status } = req.query;
  let rows = DB.licenses.slice().sort((a, b) => b.id - a.id);
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
  saveDB();
  res.json(rows);
});

app.get('/api/admin/licenses/export', requireAdmin, (req, res) => {
  const rows = DB.licenses.slice().sort((a, b) => b.id - a.id);
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
  const row = {
    id: nextId('licenses'), code, customer_name: customerName, phone: phone || '',
    plan: plan || 'شهري', months: Number(months) || 1, trial_days: null, price: Number(price) || 0,
    status: 'pending', created_at: created, activated_at: null, expires_at: null, device_info: null, notes: null,
  };
  DB.licenses.push(row);
  logAudit('license_created', { code, customerName });
  saveDB();
  res.json(row);
});

app.post('/api/admin/licenses/trial', requireAdmin, (req, res) => {
  const { customerName, phone, trialDays } = req.body || {};
  if (!customerName) return res.status(400).json({ error: 'missing_customer_name' });
  const days = Number(trialDays) || 7;
  if (days < 1) return res.status(400).json({ error: 'invalid_trial_days' });
  const code = genCode();
  const created = nowISO();
  const row = {
    id: nextId('licenses'), code, customer_name: customerName, phone: phone || '',
    plan: 'تجريبي', months: 1, trial_days: days, price: 0,
    status: 'pending', created_at: created, activated_at: null, expires_at: null, device_info: null, notes: null,
  };
  DB.licenses.push(row);
  logAudit('trial_license_created', { code, customerName, trialDays: days });
  saveDB();
  res.json(row);
});

app.post('/api/admin/licenses/:id/renew', requireAdmin, (req, res) => {
  const { months, price } = req.body || {};
  const row = findLicense(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  if (!row.activated_at) {
    const now = nowISO();
    row.status = 'active';
    row.activated_at = now;
    row.expires_at = computeExpiry(row, now);
    row.price = (row.price || 0) + (Number(price) || 0);
    logAudit('license_activated', { code: row.code });
    saveDB();
    return res.json(row);
  }
  const base = (row.expires_at && new Date(row.expires_at) > new Date()) ? row.expires_at : nowISO();
  const durationMonths = months || row.months || 1;
  row.expires_at = addMonths(base, durationMonths);
  if (row.status === 'revoked' || row.status === 'expired') row.status = 'active';
  row.price = (row.price || 0) + (Number(price) || 0);
  logAudit('license_renewed', { code: row.code, months: durationMonths });
  saveDB();
  res.json(row);
});

app.post('/api/admin/licenses/:id/revoke', requireAdmin, (req, res) => {
  const row = findLicense(req.params.id);
  if (row) row.status = 'revoked';
  logAudit('license_revoked', { code: row && row.code });
  saveDB();
  res.json(row || {});
});

app.post('/api/admin/licenses/:id/set-expiry', requireAdmin, (req, res) => {
  const { expiresAt } = req.body || {};
  const row = findLicense(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  if (!expiresAt) return res.status(400).json({ error: 'missing_expires_at' });
  logAudit('expiry_manually_fixed', { code: row.code, from: row.expires_at, to: expiresAt });
  row.expires_at = expiresAt;
  saveDB();
  res.json(row);
});

app.post('/api/admin/licenses/:id/reactivate', requireAdmin, (req, res) => {
  const row = findLicense(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  row.status = row.activated_at ? 'active' : 'pending';
  logAudit('license_reactivated', { code: row.code });
  saveDB();
  res.json(row);
});

app.post('/api/admin/licenses/:id/activate', requireAdmin, (req, res) => {
  const row = findLicense(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  const now = nowISO();
  row.status = 'active';
  row.activated_at = now;
  row.expires_at = computeExpiry(row, now);
  logAudit('license_activated', { code: row.code });
  saveDB();
  res.json(row);
});

/* ---------------- Admin: Join Requests ---------------- */
app.get('/api/admin/join-requests', requireAdmin, (req, res) => {
  res.json(DB.join_requests.slice().sort((a, b) => b.id - a.id));
});

app.post('/api/admin/join-requests/:id/dismiss', requireAdmin, (req, res) => {
  const row = DB.join_requests.find(r => String(r.id) === String(req.params.id));
  if (!row) return res.status(404).json({ error: 'not_found' });
  row.status = 'dismissed';
  logAudit('join_request_dismissed', { customerName: row.customer_name });
  saveDB();
  res.json({ ok: true });
});

app.post('/api/admin/join-requests/:id/mark-handled', requireAdmin, (req, res) => {
  const row = DB.join_requests.find(r => String(r.id) === String(req.params.id));
  if (!row) return res.status(404).json({ error: 'not_found' });
  row.status = 'handled';
  saveDB();
  res.json({ ok: true });
});

app.get('/api/admin/stats', requireAdmin, (req, res) => {
  const rows = DB.licenses.slice();
  rows.forEach(refreshStatus);
  const revenue = rows.reduce((sum, r) => sum + (r.price || 0), 0);
  const pendingRequests = DB.join_requests.filter(r => r.status === 'pending').length;
  saveDB();
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
  const rows = DB.audit_log.slice(0, 200);
  res.json(rows.map(r => ({ ...r, details: JSON.parse(r.details || '{}') })));
});

/* ---------------- Admin: Plans ---------------- */
app.get('/api/admin/plans', requireAdmin, (req, res) => {
  res.json(DB.plans);
});

app.post('/api/admin/plans', requireAdmin, (req, res) => {
  const { name, months, price } = req.body || {};
  if (!name) return res.status(400).json({ error: 'missing_name' });
  if (DB.plans.some(p => p.name === name)) return res.status(400).json({ error: 'duplicate_name' });
  const plan = { id: nextId('plans'), name, months: Number(months) || 1, price: Number(price) || 0 };
  DB.plans.push(plan);
  logAudit('plan_created', { name, months: plan.months, price: plan.price });
  saveDB();
  res.json(plan);
});

app.post('/api/admin/plans/:id/delete', requireAdmin, (req, res) => {
  if (DB.plans.length <= 1) return res.status(400).json({ error: 'must_keep_one_plan' });
  const plan = DB.plans.find(p => String(p.id) === String(req.params.id));
  DB.plans = DB.plans.filter(p => String(p.id) !== String(req.params.id));
  logAudit('plan_deleted', { name: plan && plan.name });
  saveDB();
  res.json({ ok: true });
});

/* ---------------- Admin: Delete license (permanent) ---------------- */
app.post('/api/admin/licenses/:id/delete', requireAdmin, (req, res) => {
  const row = findLicense(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  DB.licenses = DB.licenses.filter(l => String(l.id) !== String(req.params.id));
  logAudit('license_deleted', { code: row.code, customerName: row.customer_name });
  saveDB();
  res.json({ ok: true });
});

app.post('/api/admin/licenses/:id/edit', requireAdmin, (req, res) => {
  const { customerName, phone, plan, price } = req.body || {};
  const row = findLicense(req.params.id);
  if (!row) return res.status(404).json({ error: 'not_found' });
  if (!customerName) return res.status(400).json({ error: 'missing_customer_name' });
  const planChanged = plan && row.plan !== plan;
  const wasLocked = row.status === 'active' && row.expires_at && new Date(row.expires_at) > new Date();
  row.customer_name = customerName;
  row.phone = phone || '';
  if (plan) row.plan = plan;
  row.price = Number(price) || 0;
  if (planChanged) {
    const planDef = DB.plans.find(p => p.name === row.plan);
    row.months = planDef ? planDef.months : (row.months || 1);
    if (row.activated_at && !wasLocked) {
      const now = nowISO();
      row.activated_at = now;
      row.expires_at = addMonths(now, row.months);
      row.status = 'active';
      logAudit('license_plan_upgraded', { code: row.code, newPlan: row.plan, months: row.months });
    }
  }
  logAudit('license_edited', { code: row.code });
  saveDB();
  res.json(row);
});

app.listen(PORT, () => {
  console.log(`License server running on http://localhost:${PORT}`);
  console.log(`Admin dashboard: http://localhost:${PORT}/admin/`);
}).on('error', (err) => {
  logCrash('SERVER LISTEN FAILED', err);
});
