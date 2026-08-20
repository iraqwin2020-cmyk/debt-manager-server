# مشروع إدارة الديون - المستودع الكامل

هذا المستودع يحتوي على **مكوّنين مستقلّين تماماً**، كل واحد بخادمه الخاص (كلاهما يحتاج استضافة Node.js):

## 📱 client-app/
تطبيق العميل (إدارة الديون) — يعمل محلياً أولاً (IndexedDB بالمتصفح، يعمل بدون إنترنت)،
مع **نسخة احتياطية تلقائية** تُرسَل لخادمه الخاص (مرتبطة بكود التفعيل) بحيث يمكن استرجاع
البيانات تلقائياً عند فتح التطبيق من جهاز جديد بنفس الكود.

- `index.html` — التطبيق كاملاً (Frontend، PWA)
- `server.js` — خادم النسخ الاحتياطي (Backend)
- `database/` — يُنشأ تلقائياً، ملف JSON منفصل لكل محل (Database)
- `manifest.json`, `service-worker.js`, أيقونات — إعدادات PWA

### التشغيل محلياً
```bash
cd client-app
npm install
npm start
```
ثم افتح: `http://localhost:3000/`

## 🖥️ server-dashboard/
لوحة تحكم البائع + خادم التراخيص + قاعدة بياناته الخاصة — **مستقل تماماً** عن client-app.

- `server.js` — الخادم الكامل (API + قاعدة بيانات JSON)
- `public/admin/index.html` — لوحة تحكم البائع (Frontend)
- `package.json` — الاعتماديات (Express فقط)
- `data.json` — يُنشأ تلقائياً عند أول تشغيل (قاعدة البيانات)

### التشغيل محلياً
```bash
cd server-dashboard
npm install
npm start
```
ثم افتح: `http://localhost:3000/admin/`
