import { useEffect, useRef, useState } from "react"
import { createPortal } from "react-dom"

const menuPdfUrl = "/menu/kennys-korner-official-menu.pdf"

const menuPages = [
  {
    alt: "Official Kenny's Korner menu cover",
    height: 3112,
    page: 1,
  },
  {
    alt: "Official Kenny's Korner breakfast menu, page one",
    height: 3111,
    page: 2,
  },
  {
    alt: "Official Kenny's Korner breakfast and kids menu, page two",
    height: 3112,
    page: 3,
  },
  {
    alt: "Official Kenny's Korner lunch menu",
    height: 3111,
    page: 4,
  },
]

function pageImage(page: number, width: 600 | 1200 | 2200) {
  return `/menu/preview/page-${page}-${width}.webp`
}

function PreviousIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="m14.5 6-6 6 6 6"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

function NextIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="m9.5 6 6 6-6 6"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

function MinusIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="M6 12h12"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
      />
    </svg>
  )
}

function PlusIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="M12 6v12M6 12h12"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
      />
    </svg>
  )
}

function CloseIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="m6 6 12 12M18 6 6 18"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
      />
    </svg>
  )
}

function DownloadIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="M12 4v11m0 0 4-4m-4 4-4-4M5 20h14"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

function OpenIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="M13 5h6v6M19 5l-8 8M10 7H5v12h12v-5"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

export default function MenuPdfViewer() {
  const dragRef = useRef<{
    pointerX: number
    pointerY: number
    startX: number
    startY: number
  } | null>(null)
  const [activeIndex, setActiveIndex] = useState<number | null>(null)
  const [pan, setPan] = useState({ x: 0, y: 0 })
  const [zoom, setZoom] = useState(1)

  const resetView = () => {
    setZoom(1)
    setPan({ x: 0, y: 0 })
  }

  const openPage = (index: number) => {
    resetView()
    setActiveIndex(index)
  }

  const closeViewer = () => {
    setActiveIndex(null)
    resetView()
  }

  const changePage = (index: number) => {
    const nextIndex = (index + menuPages.length) % menuPages.length
    resetView()
    setActiveIndex(nextIndex)
  }

  useEffect(() => {
    if (activeIndex === null) return

    const previousOverflow = document.body.style.overflow
    document.body.style.overflow = "hidden"

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") closeViewer()
      if (event.key === "ArrowLeft") changePage(activeIndex - 1)
      if (event.key === "ArrowRight") changePage(activeIndex + 1)
      if (event.key === "+" || event.key === "=") {
        setZoom((currentZoom) => Math.min(3, currentZoom + 0.25))
      }
      if (event.key === "-") {
        setZoom((currentZoom) => Math.max(1, currentZoom - 0.25))
      }
    }

    window.addEventListener("keydown", handleKeyDown)
    return () => {
      document.body.style.overflow = previousOverflow
      window.removeEventListener("keydown", handleKeyDown)
    }
  }, [activeIndex])

  const activePage = activeIndex === null ? null : menuPages[activeIndex]

  const lightbox =
    activePage && activeIndex !== null
      ? createPortal(
          <div
            className="menu-lightbox"
            role="dialog"
            aria-modal="true"
            aria-label={`Menu page ${activePage.page} image viewer`}
          >
            <div className="menu-lightbox-toolbar">
              <span>
                Page {activePage.page} of {menuPages.length}
              </span>
              <div>
                <button
                  type="button"
                  aria-label="Zoom out"
                  title="Zoom out"
                  disabled={zoom <= 1}
                  onClick={() =>
                    setZoom((currentZoom) => Math.max(1, currentZoom - 0.25))
                  }
                >
                  <MinusIcon />
                </button>
                <span>{Math.round(zoom * 100)}%</span>
                <button
                  type="button"
                  aria-label="Zoom in"
                  title="Zoom in"
                  disabled={zoom >= 3}
                  onClick={() =>
                    setZoom((currentZoom) => Math.min(3, currentZoom + 0.25))
                  }
                >
                  <PlusIcon />
                </button>
                <button
                  type="button"
                  aria-label="Close full-screen menu viewer"
                  title="Close"
                  className="menu-lightbox-close"
                  onClick={closeViewer}
                >
                  <CloseIcon />
                </button>
              </div>
            </div>

            <button
              type="button"
              className="menu-lightbox-nav menu-lightbox-nav--previous"
              aria-label="Previous menu page"
              onClick={() => changePage(activeIndex - 1)}
            >
              <PreviousIcon />
            </button>

            <div
              className={`menu-lightbox-stage${zoom > 1 ? " is-zoomed" : ""}`}
              onPointerDown={(event) => {
                if (zoom <= 1) return
                event.currentTarget.setPointerCapture(event.pointerId)
                dragRef.current = {
                  pointerX: event.clientX,
                  pointerY: event.clientY,
                  startX: pan.x,
                  startY: pan.y,
                }
              }}
              onPointerMove={(event) => {
                if (!dragRef.current) return
                setPan({
                  x:
                    dragRef.current.startX +
                    event.clientX -
                    dragRef.current.pointerX,
                  y:
                    dragRef.current.startY +
                    event.clientY -
                    dragRef.current.pointerY,
                })
              }}
              onPointerUp={() => {
                dragRef.current = null
              }}
              onPointerCancel={() => {
                dragRef.current = null
              }}
            >
              <img
                src={pageImage(activePage.page, 2200)}
                alt={activePage.alt}
                width="2200"
                height={activePage.height}
                draggable="false"
                style={{
                  transform: `translate(${pan.x}px, ${pan.y}px) scale(${zoom})`,
                }}
              />
            </div>

            <button
              type="button"
              className="menu-lightbox-nav menu-lightbox-nav--next"
              aria-label="Next menu page"
              onClick={() => changePage(activeIndex + 1)}
            >
              <NextIcon />
            </button>
          </div>,
          document.body,
        )
      : null

  return (
    <>
      <div className="menu-preview">
        <div className="menu-preview-actions">
          <a href={menuPdfUrl} target="_blank" rel="noreferrer">
            <OpenIcon />
            <span>Open PDF</span>
          </a>
          <a href={menuPdfUrl} download="kennys-korner-official-menu.pdf">
            <DownloadIcon />
            <span>Download PDF</span>
          </a>
        </div>

        <div className="menu-preview-grid">
          {menuPages.map((page, index) => (
            <button
              type="button"
              className="menu-preview-page"
              key={page.page}
              aria-label={`Open menu page ${page.page} in full-screen viewer`}
              onClick={() => openPage(index)}
            >
              <span>Page {page.page}</span>
              <img
                src={pageImage(page.page, 1200)}
                srcSet={`${pageImage(page.page, 600)} 600w, ${pageImage(page.page, 1200)} 1200w, ${pageImage(page.page, 2200)} 2200w`}
                sizes="(max-width: 900px) calc(100vw - 48px), 44vw"
                alt={page.alt}
                width="2200"
                height={page.height}
                loading="lazy"
                decoding="async"
              />
            </button>
          ))}
        </div>
      </div>
      {lightbox}
    </>
  )
}
