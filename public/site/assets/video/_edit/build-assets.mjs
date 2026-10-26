import sharp from "sharp";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");
const imgDir = path.resolve(root, "../img/misbar");
const outDir = __dirname;

const W = 1080;
const H = 1920;
const logoSrc = path.join(imgDir, "logo-transparent.png");

async function loadLogo() {
  return sharp(logoSrc).ensureAlpha();
}

/** Soft removelogo mask: white = remove Misbar watermark zone */
async function buildMask() {
  // Watermark sits ~top-left; soft feathered ellipse/rect
  const x = 8,
    y = 8,
    w = 340,
    h = 300;
  const svg = Buffer.from(`
<svg width="${W}" height="${H}" xmlns="http://www.w3.org/2000/svg">
  <rect width="100%" height="100%" fill="black"/>
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="white"/>
      <stop offset="70%" stop-color="white"/>
      <stop offset="100%" stop-color="#666"/>
    </linearGradient>
  </defs>
  <rect x="${x}" y="${y}" width="${w}" height="${h}" rx="28" ry="28" fill="url(#g)"/>
</svg>`);
  await sharp(svg).png().toFile(path.join(outDir, "mask-wm.png"));
}

/** Bottom overlay removal mask (early CapCut bars) */
async function buildBottomMask() {
  const svg = Buffer.from(`
<svg width="${W}" height="${H}" xmlns="http://www.w3.org/2000/svg">
  <rect width="100%" height="100%" fill="black"/>
  <rect x="20" y="1480" width="620" height="380" rx="20" fill="white"/>
</svg>`);
  await sharp(svg).png().toFile(path.join(outDir, "mask-bottom.png"));
}

/** Transparent Cura watermark sized like Misbar overlay */
async function buildWatermark() {
  await sharp(logoSrc)
    .resize({ width: 250, withoutEnlargement: true })
    .png()
    .toFile(path.join(outDir, "cura-wm.png"));
}

/** Full intro / outro card with Cura branding */
async function buildCard(name, { subtitleAr = true } = {}) {
  const logo = await sharp(logoSrc)
    .resize({ width: 560, withoutEnlargement: true })
    .png()
    .toBuffer();

  const layers = [
    {
      input: Buffer.from(`
<svg width="${W}" height="${H}" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <radialGradient id="bg" cx="50%" cy="35%" r="70%">
      <stop offset="0%" stop-color="#ffffff"/>
      <stop offset="100%" stop-color="#eef5f7"/>
    </radialGradient>
  </defs>
  <rect width="100%" height="100%" fill="url(#bg)"/>
</svg>`),
      top: 0,
      left: 0,
    },
  ];

  const logoMeta = await sharp(logo).metadata();
  const logoLeft = Math.round((W - logoMeta.width) / 2);
  const logoTop = subtitleAr ? 420 : 620;
  layers.push({ input: logo, top: logoTop, left: logoLeft });

  if (subtitleAr) {
    // Arabic slogan + mission (Tahoma)
    const textSvg = Buffer.from(`
<svg width="${W}" height="700" xmlns="http://www.w3.org/2000/svg">
  <style>
    .ar { font-family: Tahoma, Arial, sans-serif; fill: #1a3a4a; direction: rtl; }
    .title { font-size: 54px; font-weight: 700; }
    .body { font-size: 28px; fill: #3a5560; }
    .line { stroke: #2aa8a0; stroke-width: 3; }
  </style>
  <line class="line" x1="280" y1="40" x2="460" y2="40"/>
  <polyline class="line" fill="none" points="460,40 480,20 500,55 520,30 540,40 800,40"/>
  <text class="ar title" x="540" y="130" text-anchor="middle">نتعلّم لنُحدث أثراً</text>
  <text class="ar body" x="540" y="220" text-anchor="middle">فريق طبي تعليمي تطوعي يهدف إلى بناء جيل</text>
  <text class="ar body" x="540" y="265" text-anchor="middle">طبي متميز في التعليم والبحث والتدريب السريري</text>
</svg>`);
    const textPng = await sharp(textSvg).png().toBuffer();
    layers.push({ input: textPng, top: 1080, left: 0 });
  }

  await sharp({
    create: { width: W, height: H, channels: 3, background: "#eef5f7" },
  })
    .composite(layers)
    .png()
    .toFile(path.join(outDir, name));
}

await buildMask();
await buildBottomMask();
await buildWatermark();
await buildCard("cura-intro.png", { subtitleAr: true });
await buildCard("cura-outro.png", { subtitleAr: false });
console.log("assets ready");
