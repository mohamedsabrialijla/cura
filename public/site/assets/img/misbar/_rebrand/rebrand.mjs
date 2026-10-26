import sharp from "sharp";
import path from "path";
import fs from "fs";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");
const assets = path.join(__dirname, "assets");
const origDir = path.join(__dirname, "orig");

function safeWrite(srcPath, buf) {
  const tmp = srcPath + ".new.jpg";
  fs.writeFileSync(tmp, buf);
  try {
    fs.unlinkSync(srcPath);
  } catch {
    /* ignore */
  }
  try {
    fs.renameSync(tmp, srcPath);
  } catch {
    fs.copyFileSync(tmp, srcPath);
    fs.unlinkSync(tmp);
  }
}

async function withOpacity(pngBuf, opacity) {
  const { data, info } = await sharp(pngBuf).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
  for (let i = 3; i < data.length; i += 4) {
    data[i] = Math.round(data[i] * opacity);
  }
  return sharp(data, { raw: info }).png().toBuffer();
}

/**
 * Approximate removal of a WHITE watermark blend:
 * observed = (1-a)*bg + a*255  →  bg = (observed - a*255)/(1-a)
 */
async function deblendWhiteWatermark(backupPath, L, T, W, H, alpha = 0.72) {
  const { data, info } = await sharp(backupPath)
    .extract({ left: L, top: T, width: W, height: H })
    .ensureAlpha()
    .raw()
    .toBuffer({ resolveWithObject: true });

  const a = alpha;
  for (let i = 0; i < data.length; i += 4) {
    for (let c = 0; c < 3; c++) {
      const v = data[i + c];
      let bg = (v - a * 255) / (1 - a);
      data[i + c] = Math.max(0, Math.min(255, Math.round(bg)));
    }
  }
  return {
    input: await sharp(data, { raw: info }).png().toBuffer(),
    left: L,
    top: T,
  };
}

/** Soft navy fill for printed logos on dark vests (forced fabric color, feathered) */
async function softNavyFill(L, T, W, H, color = { r: 18, g: 30, b: 52 }) {
  const pad = Math.max(8, Math.round(Math.min(W, H) * 0.18));
  const eW = W + pad * 2;
  const eH = H + pad * 2;
  const solid = await sharp({
    create: { width: eW, height: eH, channels: 3, background: color },
  })
    .png()
    .toBuffer();

  const maskSvg = Buffer.from(`
    <svg width="${eW}" height="${eH}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <radialGradient id="g" cx="50%" cy="50%" r="50%">
          <stop offset="0%" stop-color="#fff" stop-opacity="1"/>
          <stop offset="58%" stop-color="#fff" stop-opacity="1"/>
          <stop offset="78%" stop-color="#fff" stop-opacity="0.55"/>
          <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
        </radialGradient>
      </defs>
      <ellipse cx="${eW / 2}" cy="${eH / 2}" rx="${Math.round(eW * 0.5)}" ry="${Math.round(eH * 0.5)}" fill="url(#g)"/>
    </svg>`);

  return {
    input: await sharp(solid)
      .ensureAlpha()
      .composite([{ input: await sharp(maskSvg).png().toBuffer(), blend: "dest-in" }])
      .png()
      .toBuffer(),
    left: Math.max(0, L - pad),
    top: Math.max(0, T - pad),
  };
}

async function curaMark(logoKey, w, h, opacity) {
  const file =
    logoKey === "color"
      ? path.join(assets, "cura-color.png")
      : logoKey === "teal"
        ? path.join(assets, "cura-teal.png")
        : path.join(assets, "cura-white.png");
  const resized = await sharp(file).resize({ width: w, height: h, fit: "inside" }).png().toBuffer();
  return withOpacity(resized, opacity);
}

async function apply(file, steps) {
  const srcPath = path.join(root, file);
  const backupPath = path.join(origDir, file.replace(/[\\/]/g, "_"));
  if (!fs.existsSync(backupPath)) fs.copyFileSync(srcPath, backupPath);

  // Always start from clean original
  let imgBuf = await sharp(backupPath).jpeg({ quality: 95 }).toBuffer();

  for (const step of steps) {
    const composites = [];
    if (step.type === "deblend") {
      composites.push(await deblendWhiteWatermark(backupPath, step.left, step.top, step.width, step.height, step.alpha ?? 0.72));
      // After deblend from backup, we need to apply on current - re-deblend from current if previous steps changed area
      // Simpler: build all composites against backup in one go for banners
    } else if (step.type === "navy") {
      composites.push(await softNavyFill(step.left, step.top, step.width, step.height, step.color));
    }
    const mark = await curaMark(step.logo ?? "white", Math.round(step.width * (step.scale ?? 0.9)), Math.round(step.height * (step.scale ?? 0.9)), step.opacity ?? 0.65);
    const sm = await sharp(mark).metadata();
    composites.push({
      input: mark,
      left: step.left + Math.round((step.width - sm.width) / 2),
      top: step.top + Math.round((step.height - sm.height) / 2),
    });
    imgBuf = await sharp(imgBuf).composite(composites).jpeg({ quality: 93, mozjpeg: true }).toBuffer();
  }

  safeWrite(srcPath, imgBuf);
  console.log("OK", file);
}

async function main() {
  // --- Designed banners: remove white Misbar watermark via deblend, stamp Cura watermark ---
  await apply("banner-skilled-hands.jpeg", [
    { type: "deblend", left: 25, top: 20, width: 420, height: 155, alpha: 0.78, logo: "white", opacity: 0.72, scale: 0.95 },
    { type: "deblend", left: 1090, top: 720, width: 230, height: 145, alpha: 0.75, logo: "white", opacity: 0.68, scale: 0.92 },
  ]);

  await apply("banner-practice.jpeg", [
    { type: "deblend", left: 900, top: 15, width: 340, height: 175, alpha: 0.8, logo: "white", opacity: 0.75, scale: 0.95 },
  ]);

  await apply("banner-workshop.jpeg", [
    { type: "deblend", left: 1085, top: 15, width: 340, height: 145, alpha: 0.8, logo: "white", opacity: 0.75, scale: 0.95 },
  ]);

  // @misbar_team → @cura_team (deblend handle then watermark text)
  {
    const file = "banner-workshop.jpeg";
    const srcPath = path.join(root, file);
    const de = await deblendWhiteWatermark(srcPath, 55, 990, 380, 60, 0.7);
    const svg = Buffer.from(`
      <svg width="300" height="40" xmlns="http://www.w3.org/2000/svg">
        <text x="4" y="28" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="24" font-weight="600">@cura_team</text>
      </svg>`);
    const text = await withOpacity(await sharp(svg).png().toBuffer(), 0.8);
    const buf = await sharp(srcPath)
      .composite([de, { input: text, left: 75, top: 1005 }])
      .jpeg({ quality: 93, mozjpeg: true })
      .toBuffer();
    safeWrite(srcPath, buf);
    console.log("OK", file, "handle");
  }

  // --- Vest prints: soft navy hide + faint teal watermark (same place/size as Misbar) ---
  await apply("photo-team-vests.jpeg", [
    { type: "navy", left: 310, top: 390, width: 300, height: 320, logo: "teal", opacity: 0.52, scale: 0.88 },
    { type: "navy", left: 1000, top: 430, width: 320, height: 340, logo: "teal", opacity: 0.52, scale: 0.88 },
  ]);

  await apply("photo-46.jpeg", [
    { type: "navy", left: 610, top: 170, width: 320, height: 340, logo: "teal", opacity: 0.52, scale: 0.88 },
  ]);

  await apply("photo-training.jpeg", [
    { type: "navy", left: 945, top: 485, width: 150, height: 175, logo: "teal", opacity: 0.5, scale: 0.9 },
  ]);

  await apply("photo-presenters.jpeg", [
    { type: "navy", left: 485, top: 225, width: 120, height: 135, logo: "teal", opacity: 0.5, scale: 0.9 },
    { type: "navy", left: 730, top: 235, width: 120, height: 135, logo: "teal", opacity: 0.5, scale: 0.9 },
    { type: "deblend", left: 1095, top: 35, width: 170, height: 210, alpha: 0.55, logo: "color", opacity: 0.75, scale: 0.9 },
  ]);

  await apply("photo-class.jpeg", [
    { type: "deblend", left: 1088, top: 28, width: 155, height: 185, alpha: 0.5, logo: "color", opacity: 0.75, scale: 0.9 },
  ]);

  await apply("photo-19.jpeg", [
    { type: "deblend", left: 520, top: 95, width: 175, height: 205, alpha: 0.5, logo: "color", opacity: 0.75, scale: 0.9 },
  ]);

  await apply("photo-gloves.jpeg", [
    { type: "deblend", left: 535, top: 515, width: 250, height: 100, alpha: 0.45, logo: "teal", opacity: 0.55, scale: 0.95 },
  ]);

  await apply("photo-34.jpeg", [
    { type: "navy", left: 158, top: 338, width: 65, height: 70, logo: "teal", opacity: 0.48, scale: 0.9 },
    { type: "navy", left: 712, top: 358, width: 60, height: 65, logo: "teal", opacity: 0.48, scale: 0.9 },
  ]);

  await apply("albums/suturing/3.jpg", [
    { type: "navy", left: 570, top: 50, width: 170, height: 200, logo: "teal", opacity: 0.5, scale: 0.9 },
  ]);

  await apply("albums/suturing/8.jpg", [
    { type: "navy", left: 930, top: 50, width: 170, height: 200, logo: "teal", opacity: 0.5, scale: 0.9 },
  ]);

  await apply("albums/suturing/10.jpg", [
    { type: "navy", left: 45, top: 1015, width: 150, height: 150, logo: "teal", opacity: 0.5, scale: 0.9 },
  ]);

  console.log("DONE");
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
