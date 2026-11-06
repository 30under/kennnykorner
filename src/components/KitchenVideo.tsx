import { useEffect, useRef } from "react"

export default function KitchenVideo({
  label,
  className,
}: {
  label: string
  className?: string
}) {
  const videoRef = useRef<HTMLVideoElement>(null)

  useEffect(() => {
    const video = videoRef.current
    if (!video) return

    const reducedMotion = window.matchMedia(
      "(prefers-reduced-motion: reduce)",
    ).matches
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting && !reducedMotion) {
          void video.play().catch(() => undefined)
        } else {
          video.pause()
        }
      },
      { threshold: 0.35 },
    )

    observer.observe(video)
    return () => observer.disconnect()
  }, [])

  return (
    <video
      ref={videoRef}
      className={className}
      muted
      loop
      playsInline
      controls
      preload="metadata"
      poster="/images/gallery-one.jpg"
      aria-label={label}
    >
      <source src="/videos/behind-the-griddle.mp4" type="video/mp4" />
    </video>
  )
}
