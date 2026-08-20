/* =========================================================
   خادم النسخ الاحتياطي - تطبيق إدارة الديون (Backend الخاص بالعميل)
   Node.js + Express + ملفات JSON (بدون أي تبعية أصلية)
   كل محل له نسخة احتياطية خاصة به، مربوطة بكود التفعيل الخاص به.
   ========================================================= */
const path = require('path');
const fs = require('fs');

const CRASH_LOG = path.join(__dirname, 'crash.log');
function logCrash(label, err) {
  try {
    fs.appendFileSync(CRASH_LOG, `[${new Date().toISOString()}] ${label}:\n${(err && err.stack) || err}\n\n`);
  } catch (e) {}
  try { console.error(label, err); } catch (e) {}
}
process.on('uncaughtException', (err) => logCrash('UNCAUGHT EXCEPTION', err));
process.on('unhandledRejection', (err) => logCrash('UNHANDLED REJECTION', err));

let express;
try {
  express = require('express');
} catch (err) {
  logCrash('REQUIRE FAILED (run "npm install" from cPanel Setup Node.js App)', err);
  throw err;
}

const PORT = process.env.PORT || 3000;
const DB_DIR = path.join(__dirname, 'database');
if (!fs.existsSync(DB_DIR)) fs.mkdirSync(DB_DIR, { recursive: true });

const app = express();
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type');
  if (req.method === 'OPTIONS') return res.sendStatus(204);
  next();
});
app.use(express.json({ limit: '5mb' }));
app.use(express.static(path.join(__dirname, 'public')));

function safeCodeFile(code) {
  // يمنع أي محاولة للخروج من مجلد database عبر رمز تفعيل ملغوم
  const clean = String(code || '').replace(/[^a-zA-Z0-9-]/g, '');
  if (!clean) return null;
  return path.join(DB_DIR, clean + '.json');
}

/* حفظ نسخة احتياطية (يُستدعى تلقائياً من التطبيق بعد كل تعديل) */
app.post('/api/backup', (req, res) => {
  const { activationCode, data } = req.body || {};
  const file = safeCodeFile(activationCode);
  if (!file) return res.status(400).json({ ok: false, error: 'missing_or_invalid_code' });
  if (!data) return res.status(400).json({ ok: false, error: 'missing_data' });
  try {
    fs.writeFileSync(file, JSON.stringify({ savedAt: new Date().toISOString(), data }, null, 2), 'utf8');
    res.json({ ok: true, savedAt: new Date().toISOString() });
  } catch (err) {
    logCrash('BACKUP WRITE FAILED', err);
    res.status(500).json({ ok: false, error: 'write_failed' });
  }
});

/* استرجاع آخر نسخة احتياطية محفوظة لمحل معيّن */
app.get('/api/backup/:code', (req, res) => {
  const file = safeCodeFile(req.params.code);
  if (!file) return res.status(400).json({ ok: false, error: 'missing_or_invalid_code' });
  if (!fs.existsSync(file)) return res.status(404).json({ ok: false, error: 'no_backup_found' });
  try {
    const content = JSON.parse(fs.readFileSync(file, 'utf8'));
    res.json({ ok: true, savedAt: content.savedAt, data: content.data });
  } catch (err) {
    logCrash('BACKUP READ FAILED', err);
    res.status(500).json({ ok: false, error: 'read_failed' });
  }
});

app.listen(PORT, () => {
  console.log(`Client app + backup server running on http://localhost:${PORT}`);
}).on('error', (err) => {
  logCrash('SERVER LISTEN FAILED', err);
});
