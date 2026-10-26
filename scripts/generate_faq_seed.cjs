const fs = require('fs');
const path = require('path');

const srcPath = path.join(__dirname, '../resources/js/components/Website/pages/FAQ.vue');
const src = fs.readFileSync(srcPath, 'utf8');

function extractSections(constName) {
  const start = src.indexOf('const ' + constName + ' = [');
  if (start < 0) throw new Error('missing ' + constName);
  let i = src.indexOf('[', start);
  let depth = 0;
  let end = -1;
  for (; i < src.length; i++) {
    const ch = src[i];
    if (ch === '[') depth++;
    else if (ch === ']') {
      depth--;
      if (depth === 0) {
        end = i + 1;
        break;
      }
    }
  }
  const arrText = src.slice(src.indexOf('[', start), end);
  const fn = new Function('KS_HOME_MEDIA', 'return ' + arrText);
  const KS_HOME_MEDIA = new Proxy({}, { get: () => '' });
  return fn(KS_HOME_MEDIA);
}

const en = extractSections('FAQ_SECTIONS_EN');
const ar = extractSections('FAQ_SECTIONS_AR');
const arBySlug = Object.fromEntries(ar.map((s) => [s.slug, s]));

const rows = [];
for (const section of en) {
  const arSection = arBySlug[section.slug] || { items: [] };
  const enItems = section.items || [];
  const arItems = arSection.items || [];
  const n = Math.max(enItems.length, arItems.length);
  for (let i = 0; i < n; i++) {
    rows.push({
      category: section.slug,
      question_en: (enItems[i] && enItems[i].q) || '',
      question_ar: (arItems[i] && arItems[i].q) || '',
      answer_en: (enItems[i] && enItems[i].a) || '',
      answer_ar: (arItems[i] && arItems[i].a) || '',
      sort_order: i + 1,
    });
  }
}

function phpEscape(s) {
  return String(s)
    .replace(/\\/g, '\\\\')
    .replace(/'/g, "\\'");
}

let out = "<?php\n\nreturn [\n";
for (const r of rows) {
  out += "    [\n";
  out += "        'category' => '" + phpEscape(r.category) + "',\n";
  out += "        'question_en' => '" + phpEscape(r.question_en) + "',\n";
  out += "        'question_ar' => '" + phpEscape(r.question_ar) + "',\n";
  out += "        'answer_en' => '" + phpEscape(r.answer_en) + "',\n";
  out += "        'answer_ar' => '" + phpEscape(r.answer_ar) + "',\n";
  out += "        'sort_order' => " + r.sort_order + ",\n";
  out += "    ],\n";
}
out += "];\n";

const outPath = path.join(__dirname, '../database/data/faq_items_seed.php');
fs.mkdirSync(path.dirname(outPath), { recursive: true });
fs.writeFileSync(outPath, out);

const sqlPath = path.join(__dirname, '../database/sql/seed_faq_items.sql');
function sqlEscape(s) {
  return String(s).replace(/\\/g, '\\\\').replace(/'/g, "''");
}
let sql = "-- Seed FAQ items (same content as public FAQ.vue)\n";
sql += "DELETE FROM faq_items;\n\n";
sql += "INSERT INTO faq_items (category, question_ar, question_en, answer_ar, answer_en, sort_order, status, created_at, updated_at) VALUES\n";
const valueLines = rows.map((r) => {
  return (
    "('" +
    sqlEscape(r.category) +
    "', '" +
    sqlEscape(r.question_ar) +
    "', '" +
    sqlEscape(r.question_en) +
    "', '" +
    sqlEscape(r.answer_ar) +
    "', '" +
    sqlEscape(r.answer_en) +
    "', " +
    r.sort_order +
    ", 'active', NOW(), NOW())"
  );
});
sql += valueLines.join(",\n") + ";\n";
fs.writeFileSync(sqlPath, sql);

console.log('rows=' + rows.length);
console.log('wrote ' + outPath);
console.log('wrote ' + sqlPath);
