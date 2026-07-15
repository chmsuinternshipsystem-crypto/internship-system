import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const BASE = 'http://127.0.0.1:8000';
const SS = 'C:\\xampp\\htdocs\\intern\\backend\\storage\\app\\manuals\\screenshots';
const sleep = ms => new Promise(r => setTimeout(r, ms));

const browser = await puppeteer.launch({
  headless: true,
  executablePath: 'C:\\Users\\HomePC\\.agent-browser\\browsers\\chrome-150.0.7871.124\\chrome.exe',
  args: ['--no-sandbox'],
  defaultViewport: { width: 1440, height: 900 },
});

function fp(r, n) { const d = path.join(SS, r); if (!fs.existsSync(d)) fs.mkdirSync(d, {recursive:true}); return path.join(d, n + '.png'); }
async function snap(p, r, n) {
  await sleep(3000);
  try {
    const dims = await p.evaluate(() => ({w: document.body.scrollWidth, h: document.body.scrollHeight}));
    if (dims.w < 100) { console.log(`  ⚠ ${r}/${n} too narrow (${dims.w}px), retrying...`); await sleep(3000); }
  } catch(e) { console.log(`  ⚠ ${r}/${n} eval error: ${e.message}`); }
  try { await p.screenshot({path: fp(r,n), fullPage: true}); console.log(`  ✓ ${r}/${n}`); }
  catch(e) { console.log(`  ✗ ${r}/${n} FAILED: ${e.message}`); }
}
async function go(p, u, ms = 3000) {
  try { await p.goto(u, {waitUntil: 'domcontentloaded', timeout: 20000}); } catch(e){}
  await sleep(ms);
}

async function capture(role, pages) {
  console.log(`\n=== ${role.toUpperCase()} ===`);
  const p = await browser.newPage();
  for (const [url, name] of pages) {
    const fullUrl = `${BASE}${url}?_as=${role === 'student' && !url.startsWith('/attendance') ? 'student' : role}`;
    await go(p, fullUrl);
    await snap(p, role, name);
  }
  await p.close();
  console.log(`  ✅ ${role}`);
}

await capture('instructor', [
  ['/dashboard', 'dashboard'],
  ['/students', 'students-list'],
  ['/students/create', 'student-create'],
  ['/students/import', 'student-import'],
  ['/companies', 'companies-list'],
  ['/companies/create', 'company-create'],
  ['/deployments', 'deployments-list'],
  ['/required-documents', 'required-documents'],
  ['/workflow/queue', 'document-queue'],
  ['/compliance', 'compliance'],
  ['/attendance', 'attendance'],
  ['/evaluations', 'evaluations-list'],
  ['/evaluations/send-hte-link', 'send-hte-link'],
  ['/weekly-journals', 'weekly-journals'],
  ['/dtr', 'dtr-calendar'],
  ['/certificates', 'certificates-list'],
  ['/certificates/create', 'certificate-create'],
  ['/announcements', 'announcements-list'],
  ['/messages', 'messages-inbox'],
  ['/messages/create', 'messages-create'],
  ['/reports', 'reports'],
  ['/settings/campus', 'campus-settings'],
  ['/archive', 'archive-list'],
  ['/company-industries', 'company-industries'],
  ['/evaluations/criteria', 'evaluation-criteria'],
  ['/document-forwarding', 'document-forwarding'],
  ['/notifications', 'notifications'],
  ['/students/1', 'student-profile-tabbed'],
  ['/companies/1', 'company-show'],
  ['/companies/1/edit', 'company-edit'],
  ['/deployments/1', 'deployment-show'],
]);

await capture('chairperson', [
  ['/dashboard', 'dashboard'],
  ['/students', 'students-view'],
  ['/compliance', 'compliance'],
  ['/reports', 'reports'],
  ['/announcements', 'announcements'],
  ['/messages', 'messages'],
  ['/messages/create', 'messages-create'],
]);

await capture('dean', [
  ['/dashboard', 'dashboard'],
  ['/students', 'students-view'],
  ['/compliance', 'compliance'],
  ['/announcements', 'announcements'],
  ['/messages', 'messages'],
  ['/messages/create', 'messages-create'],
  ['/reports', 'reports'],
]);

await capture('student', [
  ['/student/dashboard', 'dashboard'],
  ['/student/documents', 'documents'],
  ['/student/documents?tab=missing', 'documents-missing'],
  ['/student/weekly-journals', 'weekly-journals'],
  ['/student/dtr', 'dtr-calendar'],
  ['/student/messages', 'messages'],
  ['/student/messages/create', 'messages-create'],
  ['/student/certificates', 'certificates'],
  ['/student/profile', 'profile'],
  ['/student/announcements', 'announcements'],
  ['/attendance/check-in', 'attendance-checkin'],
]);

// Shared
console.log('\n=== SHARED ===');
let p = await browser.newPage();
await go(p, `${BASE}/login`, 3000);
await snap(p, 'shared', 'login-page');
// Click Student button
const btns = await p.$$('button');
for (const b of btns) {
  const t = await b.evaluate(el => el.textContent);
  if (t && t.includes('Student')) { await b.click(); break; }
}
await sleep(1000);
await snap(p, 'shared', 'student-login');
// Click Back then Faculty
await (await p.$('button'))?.click();
await sleep(500);
for (const b of btns) {
  const t = await b.evaluate(el => el.textContent);
  if (t && t.includes('Faculty')) { await b.click(); break; }
}
await sleep(1000);
await snap(p, 'shared', 'staff-login');
await p.close();

console.log('\n=== SUMMARY ===');
await browser.close();
let total = 0;
for (const d of fs.readdirSync(SS, { withFileTypes: true })) {
  if (d.isDirectory()) {
    const files = fs.readdirSync(path.join(SS, d.name)).filter(f => f.endsWith('.png'));
    console.log(`  ${d.name}: ${files.length}`);
    total += files.length;
  }
}
console.log(`Total: ${total}`);
