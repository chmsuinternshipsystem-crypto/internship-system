import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const BASE = 'http://127.0.0.1:8002';
const SS = 'C:\\xampp\\htdocs\\intern\\backend\\storage\\app\\manuals\\screenshots';
const VW = { width: 1440, height: 900 };
const sleep = ms => new Promise(r => setTimeout(r, ms));

const browser = await puppeteer.launch({
  headless: true,
  executablePath: 'C:\\Users\\HomePC\\.agent-browser\\browsers\\chrome-150.0.7871.124\\chrome.exe',
  args: ['--no-sandbox'],
  defaultViewport: VW,
});

function fp(r, n) { const d = path.join(SS, r); if (!fs.existsSync(d)) fs.mkdirSync(d, {recursive:true}); return path.join(d, n + '.png'); }
async function ss(p, r, n) { await sleep(2000); await p.screenshot({path: fp(r,n), fullPage: true}); console.log(`  ✓ ${r}/${n}`); }
async function go(p, u, ms = 2000) { 
  try { await p.goto(u, {waitUntil: 'domcontentloaded', timeout: 15000}); } catch(e){} 
  await sleep(ms); 
}

// ==========================================================
// INSTRUCTOR — 30+ pages
// ==========================================================
console.log('\n=== INSTRUCTOR ===');
let p = await browser.newPage();
async function ig(u, n) { await go(p, `${BASE}${u}?_as=instructor`); await ss(p, 'instructor', n); }

await ig('/dashboard', 'dashboard');
await ig('/students', 'students-list');
await ig('/students/create', 'student-create');
await ig('/students/import', 'student-import');
await ig('/companies', 'companies-list');
await ig('/companies/create', 'company-create');
await ig('/deployments', 'deployments-list');
await ig('/required-documents', 'required-documents');
await ig('/workflow/queue', 'document-queue');
await ig('/compliance', 'compliance');
await ig('/attendance', 'attendance');
await ig('/evaluations', 'evaluations-list');
await ig('/evaluations/send-hte-link', 'send-hte-link');
await ig('/weekly-journals', 'weekly-journals');
await ig('/dtr', 'dtr-calendar');
await ig('/certificates', 'certificates-list');
await ig('/certificates/create', 'certificate-create');
await ig('/announcements', 'announcements-list');
await ig('/messages', 'messages-inbox');
await ig('/messages/create', 'messages-create');
await ig('/reports', 'reports');
await ig('/settings/campus', 'campus-settings');
await ig('/archive', 'archive-list');
await ig('/company-industries', 'company-industries');
await ig('/evaluations/criteria', 'evaluation-criteria');
await ig('/document-forwarding', 'document-forwarding');
await ig('/notifications', 'notifications');
await ig('/students/1', 'student-profile-tabbed');
await ig('/companies/1', 'company-show');
await ig('/companies/1/edit', 'company-edit');
await ig('/deployments/1', 'deployment-show');
await p.close();
console.log('  ✅ Instructor');

// ==========================================================
// CHAIRPERSON — 8 pages
// ==========================================================
console.log('\n=== CHAIRPERSON ===');
p = await browser.newPage();
async function cg(u, n) { await go(p, `${BASE}${u}?_as=chairperson`); await ss(p, 'chairperson', n); }

await cg('/dashboard', 'dashboard');
await cg('/students', 'students-view');
await cg('/compliance', 'compliance');
await cg('/reports', 'reports');
await cg('/announcements', 'announcements');
await cg('/messages', 'messages');
await cg('/messages/create', 'messages-create');
await p.close();
console.log('  ✅ Chairperson');

// ==========================================================
// DEAN — 8 pages
// ==========================================================
console.log('\n=== DEAN ===');
p = await browser.newPage();
async function dg(u, n) { await go(p, `${BASE}${u}?_as=dean`); await ss(p, 'dean', n); }

await dg('/dashboard', 'dashboard');
await dg('/students', 'students-view');
await dg('/compliance', 'compliance');
await dg('/announcements', 'announcements');
await dg('/messages', 'messages');
await dg('/messages/create', 'messages-create');
await dg('/reports', 'reports');
await p.close();
console.log('  ✅ Dean');

// ==========================================================
// STUDENT — 12 pages
// ==========================================================
console.log('\n=== STUDENT ===');
p = await browser.newPage();
async function sg(u, n) { await go(p, `${BASE}${u}?_as=student`); await ss(p, 'student', n); }

await sg('/student/dashboard', 'dashboard');
await sg('/student/documents', 'documents');
await sg('/student/documents?tab=missing', 'documents-missing');
await sg('/student/weekly-journals', 'weekly-journals');
await sg('/student/dtr', 'dtr-calendar');
await sg('/student/messages', 'messages');
await sg('/student/messages/create', 'messages-create');
await sg('/student/certificates', 'certificates');
await sg('/student/profile', 'profile');
await sg('/student/announcements', 'announcements');
await sg('/attendance/check-in', 'attendance-checkin');
await p.close();
console.log('  ✅ Student');

// ==========================================================
// SUMMARY
// ==========================================================
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
console.log(`Saved to: ${SS}`);
