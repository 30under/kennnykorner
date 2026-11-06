import { useEffect, useState } from "react"
import { Link, NavLink, Outlet, useLocation } from "react-router"
import OptimizedImage from "./OptimizedImage"

export const orderingUrl =
  "https://order.toasttab.com/online/kennys-korner-restaurant-kennys-korner-restaurant"

const navItems = [
  { label: "Home", to: "/" },
  { label: "Menu", to: "/menu" },
  { label: "Gallery", to: "/gallery" },
  { label: "Our story", to: "/our-story" },
  { label: "Visit us", to: "/visit" },
]

function ScrollToTop() {
  const { pathname } = useLocation()

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "instant" })
  }, [pathname])

  return null
}

export default function SiteLayout() {
  const [menuOpen, setMenuOpen] = useState(false)

  return (
    <div className="site-shell">
      <ScrollToTop />
      <header className="site-header">
        <Link className="brand" to="/" aria-label="Kenny's Korner home">
          <span className="brand-mark">
            <OptimizedImage
              src="/images/kennys-korner-logo.jpg"
              alt=""
              loading="eager"
              sizes="52px"
            />
          </span>
          <span className="brand-name">
            Kenny&apos;s <strong>Korner</strong>
          </span>
        </Link>

        <nav className="desktop-nav" aria-label="Main navigation">
          {navItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) => (isActive ? "is-active" : "")}
            >
              {item.label}
            </NavLink>
          ))}
        </nav>

        <a
          className="header-order"
          href={orderingUrl}
          target="_blank"
          rel="noreferrer"
        >
          Order online
        </a>

        <button
          className="menu-toggle"
          type="button"
          aria-expanded={menuOpen}
          aria-controls="mobile-navigation"
          aria-label={menuOpen ? "Close menu" : "Open menu"}
          onClick={() => setMenuOpen((open) => !open)}
        >
          <span />
          <span />
        </button>

        <nav
          id="mobile-navigation"
          className={`mobile-nav${menuOpen ? " is-open" : ""}`}
          aria-label="Mobile navigation"
        >
          {navItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              onClick={() => setMenuOpen(false)}
            >
              {item.label}
            </NavLink>
          ))}
          <a
            href={orderingUrl}
            target="_blank"
            rel="noreferrer"
            onClick={() => setMenuOpen(false)}
          >
            Online ordering
          </a>
        </nav>
      </header>

      <Outlet />

      <footer>
        <Link className="footer-brand" to="/">
          Kenny&apos;s Korner
        </Link>
        <p>Fresh-made breakfast & lunch · Open daily 7:00 AM—2:00 PM</p>
        <div className="footer-links">
          {navItems.map((item) => (
            <Link key={item.to} to={item.to}>
              {item.label}
            </Link>
          ))}
        </div>
        <div className="footer-bottom">
          <span>© 2026 Kenny&apos;s Korner</span>
          <span>Good food. Good mood.</span>
        </div>
      </footer>
    </div>
  )
}
