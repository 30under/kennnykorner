import { createBrowserRouter } from "react-router"
import SiteLayout from "../components/SiteLayout"
import HomePage from "../pages/HomePage"

export const router = createBrowserRouter([
  {
    path: "/",
    Component: SiteLayout,
    children: [
      { index: true, Component: HomePage },
      {
        path: "menu",
        lazy: async () => {
          const { MenuPage } = await import("../pages/SecondaryPages")
          return { Component: MenuPage }
        },
      },
      {
        path: "gallery",
        lazy: async () => {
          const { GalleryPage } = await import("../pages/SecondaryPages")
          return { Component: GalleryPage }
        },
      },
      {
        path: "our-story",
        lazy: async () => {
          const { StoryPage } = await import("../pages/SecondaryPages")
          return { Component: StoryPage }
        },
      },
      {
        path: "visit",
        lazy: async () => {
          const { VisitPage } = await import("../pages/SecondaryPages")
          return { Component: VisitPage }
        },
      },
      {
        path: "*",
        lazy: async () => {
          const { NotFoundPage } = await import("../pages/SecondaryPages")
          return { Component: NotFoundPage }
        },
      },
    ],
  },
])
