import sharp from "sharp";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const assets = path.join(__dirname, "assets");
const extract = path.resolve(__dirname, "../../../../../../_album_extract");
const outRoot = path.resolve(__dirname, "../albums");

async function stampLogo(srcPath, destPath) {
  const oriented = await sharp(srcPath).rotate().jpeg({ quality: 95 }).toBuffer();
  const meta = await sharp(oriented).metadata();
  const w = meta.width;
  const h = meta.height;
  const size = Math.max(40, Math.round(Math.min(w, h) * 0.18));
  const { data } = await sharp(oriented)
    .extract({
      left: Math.max(0, w - size),
      top: Math.max(0, h - size),
      width: Math.min(size, w),
      height: Math.min(size, h),
    })
    .removeAlpha()
    .raw()
    .toBuffer({ resolveWithObject: true });
  let sum = 0;
  for (let i = 0; i < data.length; i += 3) sum += (data[i] + data[i + 1] + data[i + 2]) / 3;
  const lum = sum / (data.length / 3);

  const logoW = Math.max(72, Math.round(Math.min(w, h) * 0.16));
  const pad = Math.max(10, Math.round(logoW * 0.18));
  const logoFile =
    lum > 140 ? path.join(assets, "cura-color.png") : path.join(assets, "cura-white.png");

  let logo = await sharp(logoFile)
    .ensureAlpha()
    .resize({ width: logoW, withoutEnlargement: true })
    .png()
    .toBuffer();

  const { data: ldata, info: linfo } = await sharp(logo)
    .ensureAlpha()
    .raw()
    .toBuffer({ resolveWithObject: true });
  for (let i = 3; i < ldata.length; i += 4) ldata[i] = Math.round(ldata[i] * 0.82);
  logo = await sharp(ldata, {
    raw: { width: linfo.width, height: linfo.height, channels: 4 },
  })
    .png()
    .toBuffer();
  const lm = await sharp(logo).metadata();

  const composites = [];
  if (lum <= 140) {
    const plateW = lm.width + Math.round(pad * 0.6);
    const plateH = lm.height + Math.round(pad * 0.6);
    const plateSvg = Buffer.from(
      `<svg width="${plateW}" height="${plateH}" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="${plateW}" height="${plateH}" rx="10" ry="10" fill="rgba(8,18,28,0.38)"/>
      </svg>`
    );
    composites.push({
      input: await sharp(plateSvg).png().toBuffer(),
      left: w - pad - plateW + Math.round(pad * 0.3),
      top: h - pad - plateH + Math.round(pad * 0.3),
    });
  }
  composites.push({
    input: logo,
    left: w - pad - lm.width,
    top: h - pad - lm.height,
  });

  await sharp(oriented)
    .composite(composites)
    .jpeg({ quality: 88, mozjpeg: true })
    .toFile(destPath);

  return { w, h, logoW, lum: Math.round(lum), logo: path.basename(logoFile) };
}

async function processAlbum(srcDir, destName, prefix) {
  const dest = path.join(outRoot, destName);
  fs.mkdirSync(dest, { recursive: true });
  for (const f of fs.readdirSync(dest)) {
    if (/\.(jpe?g|png|webp)$/i.test(f)) fs.unlinkSync(path.join(dest, f));
  }
  const files = fs
    .readdirSync(srcDir)
    .filter((f) => /\.(jpe?g|png|webp)$/i.test(f))
    .sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));

  let i = 0;
  for (const f of files) {
    i++;
    const destFile = path.join(dest, `${prefix}-${String(i).padStart(2, "0")}.jpeg`);
    const info = await stampLogo(path.join(srcDir, f), destFile);
    console.log(destName, `${prefix}-${String(i).padStart(2, "0")}`, info);
  }
  console.log(destName, "total", i);
  return i;
}

await processAlbum(path.join(extract, "set1"), "taghriz", "taghriz");
await processAlbum(path.join(extract, "set2"), "firstaid", "firstaid");
console.log("done");
