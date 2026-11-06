import { writeFileSync } from "node:fs"

const images = {
  "breakfast-bowl.jpg": { width: 4482, height: 2988, widths: [480, 960, 1600], alt: "Fresh avocado breakfast bowl" },
  "classic-breakfast.jpg": { width: 4438, height: 2959, widths: [480, 960, 1600], alt: "Classic eggs and steak breakfast" },
  "four-sisters-hut.jpg": { width: 932, height: 960, widths: [480, 932], alt: "The historic 4 Sisters Hut Restaurant" },
  "gallery-one.jpg": { width: 4561, height: 3041, widths: [480, 960, 1600], alt: "Fresh breakfast from Kenny's Korner" },
  "gallery-two.jpg": { width: 4608, height: 3072, widths: [480, 960, 1600], alt: "A colorful breakfast plate" },
  "hero-breakfast-platter.jpg": { width: 1467, height: 2200, widths: [480, 960, 1467], alt: "A Kenny's Korner breakfast platter with eggs, bacon, sausage, ham, and breakfast potatoes" },
  "kennys-exterior-avenue.jpg": { width: 3840, height: 2160, widths: [480, 960, 1600], alt: "Kenny's Korner exterior along the avenue" },
  "kennys-exterior-corner.jpg": { width: 3840, height: 2160, widths: [480, 960, 1600], alt: "Kenny's Korner corner exterior" },
  "kennys-exterior-front.jpg": { width: 3840, height: 2160, widths: [480, 960, 1600], alt: "The Kenny's Korner restaurant exterior with its striped awning" },
  "kennys-exterior-signage.jpg": { width: 3840, height: 2160, widths: [480, 960, 1600], alt: "Kenny's Korner exterior signage" },
  "kennys-exterior-street.jpg": { width: 3840, height: 2160, widths: [480, 960, 1600], alt: "Kenny's Korner street exterior" },
  "kennys-exterior-wide.jpg": { width: 3840, height: 2160, widths: [480, 960, 1600], alt: "Wide view of the Kenny's Korner exterior" },
  "kennys-korner-logo.jpg": { width: 2481, height: 2481, widths: [160, 320, 640, 1280], alt: "Kenny's Korner mascot" },
  "steak-and-eggs.jpg": { width: 4608, height: 3072, widths: [480, 960, 1600], alt: "Steak and eggs breakfast plate" },
  "waffle-coffee.jpg": { width: 4379, height: 2919, widths: [480, 960, 1600], alt: "Waffle breakfast and coffee" },
  "waffle-cut.jpg": { width: 4401, height: 2934, widths: [480, 960, 1600], alt: "A waffle breakfast being enjoyed" },
  "waffle-syrup.jpg": { width: 4608, height: 3072, widths: [480, 960, 1600], alt: "Waffle sandwich with syrup" },
}

const assets = Object.entries(images).map(([file, meta]) => {
  const key = file.replace(/\.[^.]+$/, "")
  return {
    key,
    file: `images/${file}`,
    alt: meta.alt,
    width: meta.width,
    height: meta.height,
    webp: meta.widths.map((width) => ({
      file: `images/optimized/${key}-${width}.webp`,
      width,
    })),
  }
})

assets.push({
  key: "menu-pdf",
  file: "menu/kennys-korner-official-menu.pdf",
  alt: "Kenny's Korner official menu",
  webp: [],
})

const menuHeights = { 1: 3112, 2: 3111, 3: 3112, 4: 3111 }
for (let page = 1; page <= 4; page += 1) {
  for (const width of [600, 1200, 2200]) {
    assets.push({
      key: `menu-page-${page}-${width}`,
      file: `menu/preview/page-${page}-${width}.webp`,
      alt: `Kenny's Korner menu page ${page}`,
      width,
      height: menuHeights[page],
      webp: [],
    })
  }
}

assets.push({
  key: "behind-the-griddle",
  file: "videos/behind-the-griddle.mp4",
  alt: "Behind the griddle at Kenny's Korner",
  webp: [],
})

const target = new URL("../wordpress/plugins/kennys-korner-core/seed/media-manifest.json", import.meta.url)
writeFileSync(target, JSON.stringify({ assets }, null, 2) + "\n")
console.log(`Wrote ${assets.length} assets`)
