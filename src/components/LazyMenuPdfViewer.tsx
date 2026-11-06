import { lazy, Suspense } from "react"

const MenuPdfViewer = lazy(() => import("./MenuPdfViewer"))

export default function LazyMenuPdfViewer() {
  return (
    <Suspense
      fallback={
        <div className="menu-pdf-document-loading" role="status">
          Loading official menu…
        </div>
      }
    >
      <MenuPdfViewer />
    </Suspense>
  )
}
