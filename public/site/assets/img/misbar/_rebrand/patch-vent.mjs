import sharp from "sharp";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");
const assets = path.join(__dirname, "assets");
const origDir = path.join(__dirname, "orig");
const preview = path.join(__dirname, "preview");

async function logoStamp(logoPath, maxW, maxH, rotateDeg = 0, opacity = 1) {
  const meta = await sharp(logoPath).metadata();
  const scale = Math.min(maxW / meta.width, maxH / meta.height);
  const nw = Math.max(1, Math.round(meta.width * scale));
  const nh = Math.max(1, Math.round(meta.height * scale));
  let buf = await sharp(logoPath).ensureAlpha().resize(nw, nh, { kernel: "lanczos3" }).png().toBuffer();
  if (rotateDeg) {
    buf = await sharp(buf)
      .rotate(rotateDeg, { background: { r: 0, g: 0, b: 0, alpha: 0 } })
      .png()
      .toBuffer();
  }
  if (opacity < 1) {
    const { data, info } = await sharp(buf)
      .ensureAlpha()
      .raw()
      .toBuffer({ resolveWithObject: true });
    for (let i = 3; i < data.length; i += 4) data[i] = Math.round(data[i] * opacity);
    buf = await sharp(data, {
      raw: { width: info.width, height: info.height, channels: 4 },
    })
      .png()
      .toBuffer();
  }
  return buf;
}

function addGrain(data, w, h, amount = 6) {
  for (let i = 0; i < data.length; i += 4) {
    if (data[i + 3] < 8) continue;
    const n = ((Math.random() * 2 - 1) * amount) | 0;
    data[i] = Math.max(0, Math.min(255, data[i] + n));
    data[i + 1] = Math.max(0, Math.min(255, data[i + 1] + n));
    data[i + 2] = Math.max(0, Math.min(255, data[i + 2] + n));
  }
}

async function makeBadgeOverlay(w, h, rotateDeg, logoBuf) {
  const rx = Math.max(3, Math.round(Math.min(w, h) * 0.08));
  const svg = Buffer.from(`<svg width="${w}" height="${h}" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#f4f5f8"/>
        <stop offset="55%" stop-color="#eceef3"/>
        <stop offset="100%" stop-color="#e6e8ee"/>
      </linearGradient>
    </defs>
    <rect x="0.5" y="0.5" width="${w - 1}" height="${h - 1}" rx="${rx}" ry="${rx}" fill="url(#g)"/>
  </svg>`);

  let paper = await sharp(svg).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
  addGrain(paper.data, paper.info.width, paper.info.height, 5);
  // Soft edge alpha (1px inset fade)
  const { data, info } = paper;
  const W = info.width,
    H = info.height;
  for (let y = 0; y < H; y++) {
    for (let x = 0; x < W; x++) {
      const i = (y * W + x) * 4;
      const dx = Math.min(x, W - 1 - x);
      const dy = Math.min(y, H - 1 - y);
      const d = Math.min(dx, dy);
      const a = d === 0 ? 180 : d === 1 ? 230 : 255;
      data[i + 3] = Math.min(data[i + 3], a);
    }
  }
  const paperPng = await sharp(data, {
    raw: { width: W, height: H, channels: 4 },
  })
    .png()
    .toBuffer();

  const lm = await sharp(logoBuf).metadata();
  const stamped = await sharp(paperPng)
    .composite([
      {
        input: logoBuf,
        left: Math.round((w - lm.width) / 2),
        top: Math.round((h - lm.height) / 2),
      },
    ])
    .png()
    .toBuffer();

  return sharp(stamped)
    .rotate(rotateDeg, { background: { r: 0, g: 0, b: 0, alpha: 0 } })
    .png()
    .toBuffer();
}

/** Soft-erase using only bright slide pixels as fill reference */
async function eraseBrightZone(srcPath, left, top, width, height) {
  const { data, info } = await sharp(srcPath)
    .extract({ left, top, width, height })
    .ensureAlpha()
    .raw()
    .toBuffer({ resolveWithObject: true });
  const w = info.width,
    h = info.height;
  let sr = 0,
    sg = 0,
    sb = 0,
    n = 0;
  for (let y = 0; y < h; y++) {
    for (let x = 0; x < w; x++) {
      const i = (y * w + x) * 4;
      const lum = (data[i] + data[i + 1] + data[i + 2]) / 3;
      if (lum > 155) {
        sr += data[i];
        sg += data[i + 1];
        sb += data[i + 2];
        n++;
      }
    }
  }
  if (!n) return { buf: await sharp(data, { raw: { width: w, height: h, channels: 4 } }).png().toBuffer(), fill: null };
  const fr = Math.round(sr / n),
    fg = Math.round(sg / n),
    fb = Math.round(sb / n);
  const out = Buffer.from(data);
  const cx = w / 2,
    cy = h / 2;
  const rx = w * 0.4,
    ry = h * 0.42;
  for (let y = 0; y < h; y++) {
    for (let x = 0; x < w; x++) {
      const i = (y * w + x) * 4;
      const lum = (data[i] + data[i + 1] + data[i + 2]) / 3;
      // Don't paint over dark cables/bezels
      if (lum < 90) continue;
      const nx = (x - cx) / rx;
      const ny = (y - cy) / ry;
      const d = Math.sqrt(nx * nx + ny * ny);
      if (d >= 1.15) continue;
      const t = Math.max(0, Math.min(1, (1.15 - d) / 1.15)) * 0.88;
      out[i] = Math.round(data[i] * (1 - t) + fr * t);
      out[i + 1] = Math.round(data[i + 1] * (1 - t) + fg * t);
      out[i + 2] = Math.round(data[i + 2] * (1 - t) + fb * t);
    }
  }
  return {
    buf: await sharp(out, { raw: { width: w, height: h, channels: 4 } }).png().toBuffer(),
    fill: [fr, fg, fb],
  };
}

async function patchVent05() {
  const bak = path.join(origDir, "albums_ventilation_vent-05.jpeg");
  const out = path.join(root, "albums/ventilation/vent-05.jpeg");

  const baseL = 561,
    baseT = 924,
    cardW = 78,
    cardH = 58;
  const rotate = 6.5;

  const logo = await logoStamp(
    path.join(assets, "cura-color.png"),
    Math.round(cardW * 0.88),
    Math.round(cardH * 0.84),
    0,
    0.96
  );
  const card = await makeBadgeOverlay(cardW, cardH, rotate, logo);
  const cm = await sharp(card).metadata();
  const L = Math.round(baseL + (cardW - cm.width) / 2);
  const T = Math.round(baseT + (cardH - cm.height) / 2);

  await sharp(bak)
    .composite([{ input: card, left: Math.max(0, L), top: Math.max(0, T) }])
    .jpeg({ quality: 93, mozjpeg: true })
    .toFile(out);

  await sharp(out)
    .extract({ left: 480, top: 850, width: 200, height: 174 })
    .resize(420)
    .jpeg({ quality: 95 })
    .toFile(path.join(preview, "after-badge.jpg"));
  await sharp(out)
    .extract({ left: 548, top: 908, width: 105, height: 90 })
    .resize(340)
    .jpeg({ quality: 95 })
    .toFile(path.join(preview, "after-card.jpg"));
  console.log("vent-05", { L, T, cw: cm.width, ch: cm.height });
}

async function patchVent01() {
  const bak = path.join(origDir, "albums_ventilation_vent-01.jpeg");
  const out = path.join(root, "albums/ventilation/vent-01.jpeg");

  // Restore from original first (wipe bad hijab patch)
  // Then soft-erase tiny slide watermark only
  const L = 148,
    T = 625,
    W = 95,
    H = 65;
  const { buf: erased, fill } = await eraseBrightZone(bak, L, T, W, H);

  // Very small faint Cura — no white box
  const logo = await logoStamp(path.join(assets, "cura-teal.png"), 38, 30, 0, 0.3);
  const lm = await sharp(logo).metadata();
  const zone = await sharp(erased)
    .composite([
      {
        input: logo,
        left: Math.round((W - lm.width) / 2),
        top: Math.round((H - lm.height) / 2),
      },
    ])
    .png()
    .toBuffer();

  await sharp(bak)
    .composite([{ input: zone, left: L, top: T }])
    .jpeg({ quality: 93, mozjpeg: true })
    .toFile(out);

  await sharp(out)
    .extract({ left: 40, top: 480, width: 360, height: 260 })
    .resize(500)
    .jpeg({ quality: 92 })
    .toFile(path.join(preview, "after-v01-full.jpg"));
  await sharp(out)
    .extract({ left: L - 15, top: T - 15, width: W + 30, height: H + 30 })
    .resize(320)
    .jpeg({ quality: 95 })
    .toFile(path.join(preview, "after-v01-tight.jpg"));
  await sharp(out)
    .extract({ left: 240, top: 520, width: 200, height: 200 })
    .resize(360)
    .jpeg({ quality: 92 })
    .toFile(path.join(preview, "hijab-cur.jpg"));
  console.log("vent-01", { L, T, W, H, fill });
}

await patchVent05();
await patchVent01();
console.log("done");
