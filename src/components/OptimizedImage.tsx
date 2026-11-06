import type { ImgHTMLAttributes } from "react"

type ImageMetadata = {
  width: number
  height: number
  widths: number[]
}

const imageMetadata: Record<string, ImageMetadata> = {
  "/images/breakfast-bowl.jpg": {
    width: 4482,
    height: 2988,
    widths: [480, 960, 1600],
  },
  "/images/classic-breakfast.jpg": {
    width: 4438,
    height: 2959,
    widths: [480, 960, 1600],
  },
  "/images/four-sisters-hut.jpg": {
    width: 932,
    height: 960,
    widths: [480, 932],
  },
  "/images/gallery-one.jpg": {
    width: 4561,
    height: 3041,
    widths: [480, 960, 1600],
  },
  "/images/gallery-two.jpg": {
    width: 4608,
    height: 3072,
    widths: [480, 960, 1600],
  },
  "/images/hero-breakfast-platter.jpg": {
    width: 1467,
    height: 2200,
    widths: [480, 960, 1467],
  },
  "/images/kennys-exterior-avenue.jpg": {
    width: 3840,
    height: 2160,
    widths: [480, 960, 1600],
  },
  "/images/kennys-exterior-corner.jpg": {
    width: 3840,
    height: 2160,
    widths: [480, 960, 1600],
  },
  "/images/kennys-exterior-front.jpg": {
    width: 3840,
    height: 2160,
    widths: [480, 960, 1600],
  },
  "/images/kennys-exterior-signage.jpg": {
    width: 3840,
    height: 2160,
    widths: [480, 960, 1600],
  },
  "/images/kennys-exterior-street.jpg": {
    width: 3840,
    height: 2160,
    widths: [480, 960, 1600],
  },
  "/images/kennys-exterior-wide.jpg": {
    width: 3840,
    height: 2160,
    widths: [480, 960, 1600],
  },
  "/images/kennys-korner-logo.jpg": {
    width: 2481,
    height: 2481,
    widths: [160, 320, 640, 1280],
  },
  "/images/steak-and-eggs.jpg": {
    width: 4608,
    height: 3072,
    widths: [480, 960, 1600],
  },
  "/images/waffle-coffee.jpg": {
    width: 4379,
    height: 2919,
    widths: [480, 960, 1600],
  },
  "/images/waffle-cut.jpg": {
    width: 4401,
    height: 2934,
    widths: [480, 960, 1600],
  },
  "/images/waffle-syrup.jpg": {
    width: 4608,
    height: 3072,
    widths: [480, 960, 1600],
  },
}

type OptimizedImageProps = Omit<ImgHTMLAttributes<HTMLImageElement>, "alt" | "height" | "sizes" | "src" | "srcSet" | "width"> & {
  alt: string
  priority?: boolean
  sizes?: string
  src: string
}

function optimizedSource(src: string, width: number, extension: "webp") {
  const filename = src
    .split("/")
    .pop()
    ?.replace(/\.[^.]+$/, "")
  return `/images/optimized/${filename}-${width}.${extension}`
}

export default function OptimizedImage({
  alt,
  decoding = "async",
  loading,
  priority = false,
  sizes = "(max-width: 900px) 100vw, 50vw",
  src,
  ...props
}: OptimizedImageProps) {
  const metadata = imageMetadata[src]

  if (!metadata) {
    return (
      <img
        {...props}
        alt={alt}
        decoding={decoding}
        fetchPriority={priority ? "high" : props.fetchPriority}
        loading={priority ? "eager" : (loading ?? "lazy")}
        src={src}
      />
    )
  }

  const webpSrcSet = metadata.widths
    .map((width) => `${optimizedSource(src, width, "webp")} ${width}w`)
    .join(", ")

  return (
    <picture className="optimized-picture">
      <source sizes={sizes} srcSet={webpSrcSet} type="image/webp" />
      <img
        {...props}
        alt={alt}
        decoding={decoding}
        fetchPriority={priority ? "high" : props.fetchPriority}
        height={metadata.height}
        loading={priority ? "eager" : (loading ?? "lazy")}
        sizes={sizes}
        src={src}
        width={metadata.width}
      />
    </picture>
  )
}
