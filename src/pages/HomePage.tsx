import type { ReactNode } from "react"
import KitchenVideo from "../components/KitchenVideo"
import MenuPdfViewer from "../components/LazyMenuPdfViewer"
import OptimizedImage from "../components/OptimizedImage"

const orderingUrl =
  "https://order.toasttab.com/online/kennys-korner-restaurant-kennys-korner-restaurant"

function ArrowIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="M5 12h14M14 7l5 5-5 5"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

function PinIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"
        stroke="currentColor"
        strokeWidth="2"
      />
      <circle cx="12" cy="10" r="2.5" stroke="currentColor" strokeWidth="2" />
    </svg>
  )
}

function ClockIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="2" />
      <path
        d="M12 7v5l3 2"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
      />
    </svg>
  )
}

function BagIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <path
        d="M5 8h14l1 13H4L5 8Z"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinejoin="round"
      />
      <path
        d="M9 9V6a3 3 0 0 1 6 0v3"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
      />
    </svg>
  )
}

function InstagramIcon() {
  return (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
      <rect
        x="3"
        y="3"
        width="18"
        height="18"
        rx="5"
        stroke="currentColor"
        strokeWidth="2"
      />
      <circle cx="12" cy="12" r="4" stroke="currentColor" strokeWidth="2" />
      <circle cx="17.5" cy="6.5" r="1" fill="currentColor" />
    </svg>
  )
}

function CoastalHorizon() {
  return (
    <div className="coastal-horizon" aria-hidden="true">
      <svg className="coastal-palm" viewBox="0 0 150 180" fill="none">
        <path
          d="M67 178c8-48 7-94-1-137"
          stroke="currentColor"
          strokeWidth="7"
          strokeLinecap="round"
        />
        <path
          d="M66 48C47 24 29 17 8 18c17 6 29 17 37 34M68 45C66 22 74 8 92 2c-7 14-9 28-5 43M70 48c20-21 40-27 61-20-19 2-33 10-43 26M64 45C47 35 32 35 18 43c16 1 29 7 39 19"
          stroke="currentColor"
          strokeWidth="7"
          strokeLinecap="round"
          strokeLinejoin="round"
        />
      </svg>
      <span className="coastal-sun" />
      <span className="coastal-wave coastal-wave--one" />
      <span className="coastal-wave coastal-wave--two" />
      <span className="coastal-wave coastal-wave--three" />
    </div>
  )
}

function ActionLink({
  href,
  children,
  variant = "primary",
  external = false,
  icon,
}: {
  href: string
  children: ReactNode
  variant?: "primary" | "secondary" | "light"
  external?: boolean
  icon?: ReactNode
}) {
  return (
    <a
      className={`action-link action-link--${variant}`}
      href={href}
      target={external ? "_blank" : undefined}
      rel={external ? "noreferrer" : undefined}
    >
      {icon ? <span className="action-link-icon">{icon}</span> : null}
      <span>{children}</span>
      <ArrowIcon />
    </a>
  )
}

function SectionLabel({
  children,
  light = false,
}: {
  children: ReactNode
  light?: boolean
}) {
  return (
    <p className={`section-label${light ? " section-label--light" : ""}`}>
      {children}
    </p>
  )
}

export default function HomePage() {
  return (
    <main>
      <section className="hero" id="top">
        <div className="hero-sun" aria-hidden="true" />
        <div className="hero-copy">
          <SectionLabel>Your neighborhood breakfast spot</SectionLabel>
          <h1>
            Good mornings
            <br />
            start <em>here.</em>
          </h1>
          <p className="hero-description">
            Classic breakfast, fresh coffee, and friendly faces. Serving our
            community since 1990.
          </p>
          <div className="hero-actions">
            <ActionLink href="#menu">View menu</ActionLink>
            <ActionLink
              href="https://www.google.com/maps/search/?api=1&query=Kenny%27s+Korner"
              variant="secondary"
              external
              icon={<PinIcon />}
            >
              Get directions
            </ActionLink>
            <ActionLink
              href={orderingUrl}
              variant="secondary"
              external
              icon={<BagIcon />}
            >
              Online ordering
            </ActionLink>
          </div>
          <p className="hero-fresh-note">
            Breakfast & lunch · Made fresh daily
          </p>
          <div className="open-note">
            <ClockIcon />
            <span>
              <strong>Open daily</strong> 7:00 AM—2:00 PM
            </span>
          </div>
        </div>
        <CoastalHorizon />

        <div className="hero-visual">
          <div className="hero-rays" aria-hidden="true" />
          <span className="sparkle sparkle--one" aria-hidden="true" />
          <span className="sparkle sparkle--two" aria-hidden="true" />
          <div className="hero-image-waves" aria-hidden="true">
            <span />
            <span />
            <span />
          </div>
          <div className="hero-image-frame">
            <OptimizedImage
              src="/images/hero-breakfast-platter.jpg"
              alt="A Kenny's Korner breakfast platter with eggs, bacon, sausage, ham, and breakfast potatoes"
              priority
              sizes="(max-width: 900px) calc(100vw - 32px), (orientation: portrait) and (max-width: 1100px) calc(100vw - 64px), 44vw"
            />
          </div>
          <div className="hero-logo-card">
            <OptimizedImage
              src="/images/kennys-korner-logo.jpg"
              alt="Kenny's Korner mascot"
              loading="eager"
              sizes="205px"
            />
          </div>
          <div className="sunburst" aria-hidden="true">
            <span>Fresh</span>
            <span>every day</span>
          </div>
        </div>

        <div className="wave wave--one" aria-hidden="true" />
        <div className="wave wave--two" aria-hidden="true" />
      </section>

      <section className="home-location">
        <div className="home-location-photo">
          <OptimizedImage
            src="/images/kennys-exterior-front.jpg"
            alt="The Kenny's Korner restaurant exterior with its striped awning"
            sizes="(max-width: 900px) calc(100vw - 48px), 58vw"
          />
          <span className="home-location-stamp" aria-hidden="true">
            Right around
            <strong>the Korner</strong>
          </span>
        </div>
        <div className="home-location-copy">
          <SectionLabel>Look for the striped awning</SectionLabel>
          <h2>Your neighborhood breakfast spot.</h2>
          <p>
            The sunny corner building is part of the Kenny&apos;s experience.
            Come by for a fresh breakfast, an easy lunch, and a familiar
            welcome.
          </p>
          <ActionLink
            href="https://www.google.com/maps/search/?api=1&query=Kenny%27s+Korner"
            variant="secondary"
            external
          >
            Get directions
          </ActionLink>
        </div>
      </section>

      <section className="quick-actions" aria-label="Quick links">
        <a href="#menu">
          <span className="quick-number">01</span>
          <span>
            <strong>Explore our menu</strong>
            Breakfast, lunch & favorites
          </span>
          <ArrowIcon />
        </a>
        <a
          href="https://www.google.com/maps/search/?api=1&query=Kenny%27s+Korner"
          target="_blank"
          rel="noreferrer"
        >
          <span className="quick-number">02</span>
          <span>
            <strong>Find your way</strong>
            Directions in one tap
          </span>
          <PinIcon />
        </a>
        <a href={orderingUrl} target="_blank" rel="noreferrer">
          <span className="quick-number">03</span>
          <span>
            <strong>Online ordering</strong>
            Easy pickup through Toast
          </span>
          <ArrowIcon />
        </a>
      </section>

      <section className="menu-section" id="menu">
        <div className="section-heading">
          <div>
            <SectionLabel>From our kitchen</SectionLabel>
            <h2>Find your new favorite.</h2>
          </div>
          <p>
            Classic comfort, fresh ingredients, and plates made to brighten your
            day.
          </p>
        </div>

        <MenuPdfViewer />
      </section>

      <div className="section-wave-divider" aria-hidden="true">
        <span />
        <span />
        <span />
      </div>

      <section className="kitchen-film">
        <div className="kitchen-film-copy">
          <SectionLabel>Behind the griddle</SectionLabel>
          <h2>Real food. Real heat. Made right here.</h2>
          <p>
            A quick look at the color, sizzle, and care that goes into every
            plate before it reaches your table.
          </p>
          <a className="text-link" href="/gallery">
            See more from our kitchen <ArrowIcon />
          </a>
        </div>
        <div className="kitchen-film-frame">
          <div className="kitchen-film-rings" aria-hidden="true" />
          <KitchenVideo label="A dish being prepared in the Kenny's Korner kitchen" />
          <span className="kitchen-film-badge">Made fresh</span>
        </div>
      </section>

      <section className="story-section" id="story">
        <div className="story-collage">
          <OptimizedImage
            className="story-image-main"
            src="/images/steak-and-eggs.jpg"
            alt="Steak and eggs breakfast plate"
            sizes="(max-width: 900px) 88vw, 42vw"
          />
          <OptimizedImage
            className="story-image-small"
            src="/images/waffle-cut.jpg"
            alt="A waffle breakfast being enjoyed"
            sizes="(max-width: 900px) 42vw, 22vw"
          />
          <div className="story-stamp" aria-hidden="true">
            <span>Made</span>
            <strong>with heart</strong>
          </div>
        </div>
        <div className="story-copy">
          <SectionLabel>Welcome to the Korner</SectionLabel>
          <h2>A little coastal, a lot of comfort.</h2>
          <p>
            Kenny&apos;s Korner is the kind of place where breakfast feels easy
            and everybody belongs. We make the classics you know, add our own
            sunny spin, and serve it all with genuine hospitality.
          </p>
          <p>
            Whether you&apos;re a regular or just passing through, there&apos;s
            always a seat—and something delicious—waiting for you.
          </p>
          <a className="text-link" href="/our-story">
            Our story <ArrowIcon />
          </a>
        </div>
      </section>

      <section className="gallery-section" id="gallery">
        <div className="gallery-heading">
          <div>
            <SectionLabel>Fresh from the griddle</SectionLabel>
            <h2>Made to make your day.</h2>
          </div>
          <a
            className="instagram-link"
            href="#gallery"
            aria-label="See more from Kenny's Korner"
          >
            <InstagramIcon />
            Follow along
          </a>
        </div>
        <div className="gallery-grid">
          <figure className="gallery-tall">
            <OptimizedImage
              src="/images/gallery-one.jpg"
              alt="Fresh breakfast from Kenny's Korner"
              sizes="(max-width: 900px) calc(100vw - 32px), 58vw"
            />
          </figure>
          <figure>
            <OptimizedImage
              src="/images/gallery-two.jpg"
              alt="A house favorite breakfast plate"
              sizes="(max-width: 900px) 50vw, 29vw"
            />
          </figure>
          <figure>
            <OptimizedImage
              src="/images/waffle-syrup.jpg"
              alt="Waffle sandwich with syrup"
              sizes="(max-width: 900px) 50vw, 29vw"
            />
          </figure>
        </div>
      </section>

      <section className="visit-section" id="visit">
        <div className="visit-content">
          <SectionLabel light>Come say hello</SectionLabel>
          <h2>Your table is waiting.</h2>
          <p>Breakfast and lunch made fresh, seven days a week.</p>
          <div className="visit-details">
            <div>
              <ClockIcon />
              <span>
                <small>Hours</small>
                Daily, 7:00 AM—2:00 PM
              </span>
            </div>
            <div>
              <PinIcon />
              <span>
                <small>Location</small>
                Find Kenny&apos;s Korner near you
              </span>
            </div>
          </div>
          <ActionLink
            href="https://www.google.com/maps/search/?api=1&query=Kenny%27s+Korner"
            variant="light"
            external
          >
            Get directions
          </ActionLink>
        </div>
        <div className="visit-image">
          <OptimizedImage
            src="/images/waffle-coffee.jpg"
            alt="Breakfast and coffee ready to enjoy"
            sizes="(max-width: 900px) 100vw, 50vw"
          />
        </div>
      </section>

      <section className="order-strip" id="order">
        <div>
          <span className="order-kicker">Craving Kenny&apos;s?</span>
          <h2>Skip the wait. Order ahead.</h2>
        </div>
        <ActionLink href={orderingUrl} variant="primary" external>
          Order online
        </ActionLink>
      </section>
    </main>
  )
}
