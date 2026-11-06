import { Link } from "react-router"
import KitchenVideo from "../components/KitchenVideo"
import MenuPdfViewer from "../components/LazyMenuPdfViewer"
import OptimizedImage from "../components/OptimizedImage"
import { orderingUrl } from "../components/SiteLayout"

const galleryImages = [
  { src: "/images/waffle-coffee.jpg", alt: "Waffle breakfast and coffee" },
  {
    src: "/images/classic-breakfast.jpg",
    alt: "Classic eggs and steak breakfast",
  },
  { src: "/images/waffle-cut.jpg", alt: "Waffle sandwich being enjoyed" },
  { src: "/images/breakfast-bowl.jpg", alt: "Fresh avocado breakfast bowl" },
  { src: "/images/gallery-one.jpg", alt: "Fresh-made Kenny's Korner plate" },
  { src: "/images/steak-and-eggs.jpg", alt: "Steak and eggs with potatoes" },
  { src: "/images/waffle-syrup.jpg", alt: "Waffle sandwich with syrup" },
  { src: "/images/gallery-two.jpg", alt: "A colorful breakfast plate" },
]

function PageHero({
  eyebrow,
  title,
  description,
  image,
  variant,
}: {
  eyebrow: string
  title: string
  description: string
  image: string
  variant?: "menu" | "story" | "visit"
}) {
  return (
    <section className={`page-hero${variant ? ` page-hero--${variant}` : ""}`}>
      <div className="page-hero-copy">
        <p className="section-label">{eyebrow}</p>
        <h1>{title}</h1>
        <p>{description}</p>
      </div>
      <div className="page-hero-image">
        <div className="page-hero-rays" aria-hidden="true" />
        <OptimizedImage
          src={image}
          alt=""
          priority
          sizes="(max-width: 900px) 94vw, 44vw"
        />
        <span aria-hidden="true">Fresh daily</span>
      </div>
    </section>
  )
}

function OrderBanner() {
  return (
    <section className="page-order-banner">
      <div>
        <span>Hungry already?</span>
        <h2>Your favorites are just a few clicks away.</h2>
      </div>
      <a href={orderingUrl} target="_blank" rel="noreferrer">
        Order online <span aria-hidden="true">→</span>
      </a>
    </section>
  )
}

export function MenuPage() {
  return (
    <main className="inner-page">
      <PageHero
        eyebrow="Breakfast & lunch"
        title="Good food, made from scratch."
        description="Familiar favorites, fresh ingredients, and a little coastal sunshine on every plate."
        image="/images/waffle-syrup.jpg"
        variant="menu"
      />

      <section className="menu-pdf-page">
        <div className="menu-pdf-page-heading">
          <p className="section-label">Official menu</p>
          <h2>Find your new favorite.</h2>
          <p>
            Classic comfort, fresh ingredients, and plates made to brighten your
            day.
          </p>
        </div>
        <MenuPdfViewer />
      </section>
      <OrderBanner />
    </main>
  )
}

export function GalleryPage() {
  return (
    <main className="inner-page">
      <PageHero
        eyebrow="The Kenny's experience"
        title="A feast for your eyes."
        description="Fresh plates, sunny mornings, and a kitchen that always feels alive. Take a look around."
        image="/images/waffle-cut.jpg"
      />

      <section className="gallery-page-section">
        <div className="gallery-intro">
          <p className="section-label">Fresh from the kitchen</p>
          <h2>Made here. Loved here.</h2>
          <p>
            Every plate starts with honest ingredients and ends with the kind of
            food you will want to come back for.
          </p>
        </div>
        <div className="photo-wall">
          {galleryImages.map((image, index) => (
            <figure
              className={`photo-wall-item photo-wall-item--${index + 1}`}
              key={image.src}
            >
              <OptimizedImage
                src={image.src}
                alt={image.alt}
                sizes="(max-width: 900px) 100vw, 58vw"
              />
              <figcaption>Made fresh at Kenny&apos;s</figcaption>
            </figure>
          ))}
        </div>

        <div className="gallery-film">
          <div className="gallery-film-video">
            <KitchenVideo label="Fresh ingredients cooking on the Kenny's Korner griddle" />
          </div>
          <div className="gallery-film-copy">
            <p className="section-label section-label--light">In motion</p>
            <h2>From the flame to your table.</h2>
            <p>
              The best part happens before the plate arrives: fresh ingredients,
              a hot pan, and a kitchen team that knows exactly when it is ready.
            </p>
            <a href={orderingUrl} target="_blank" rel="noreferrer">
              Taste it for yourself <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>
      </section>
      <OrderBanner />
    </main>
  )
}

export function StoryPage() {
  return (
    <main className="inner-page">
      <PageHero
        eyebrow="Our story"
        title="A neighborhood favorite with coastal soul."
        description="Kenny's Korner was built around a simple idea: make great food, welcome everyone, and never rush a good morning."
        image="/images/classic-breakfast.jpg"
        variant="story"
      />

      <section className="story-editorial">
        <div className="story-editorial-copy">
          <p className="section-label">Pull up a chair</p>
          <h2>Comfort food feels better when it comes with a smile.</h2>
          <p>
            We believe the best restaurants feel familiar from the moment you
            walk through the door. That is why our kitchen keeps things honest:
            scratch-made breakfasts, satisfying lunches, fresh coffee, and warm
            hospitality.
          </p>
          <p>
            Inspired by easy coastal mornings, Kenny&apos;s is a place for
            regulars, travelers, families, and anyone who believes breakfast is
            always a good idea.
          </p>
        </div>
        <div className="story-editorial-images">
          <OptimizedImage
            src="/images/steak-and-eggs.jpg"
            alt="A hearty breakfast from Kenny's"
            sizes="(max-width: 900px) 82vw, 44vw"
          />
          <OptimizedImage
            src="/images/kennys-korner-logo.jpg"
            alt="Kenny's Korner mascot"
            sizes="(max-width: 900px) 150px, 210px"
          />
        </div>
      </section>

      <section
        className="story-timeline"
        aria-labelledby="story-timeline-title"
      >
        <div className="story-timeline-heading">
          <p className="section-label">From then to now</p>
          <h2 id="story-timeline-title">
            Good mornings, generations in the making.
          </h2>
        </div>

        <div className="timeline-list">
          <article className="timeline-item">
            <div className="timeline-marker" aria-hidden="true">
              <svg viewBox="0 0 48 48">
                <path d="M12 18h24v9a12 12 0 0 1-12 12 12 12 0 0 1-12-12v-9Z" />
                <path d="M36 21h2a5 5 0 0 1 0 10h-3" />
                <path d="M18 13c-2-2 2-4 0-7M25 13c-2-2 2-4 0-7M32 13c-2-2 2-4 0-7" />
              </svg>
            </div>
            <div className="timeline-copy">
              <p className="timeline-date">1960s</p>
              <h3>The Hut</h3>
              <p>
                It all started in the 1960s as The Hut, a little breakfast spot
                run by four sisters who shared a love for cooking and bringing
                people together over a good meal.
              </p>
            </div>
            <figure className="timeline-photo timeline-photo--left">
              <OptimizedImage
                src="/images/four-sisters-hut.jpg"
                alt="The historic 4 Sisters Hut Restaurant"
                sizes="(max-width: 900px) calc(100vw - 112px), 32vw"
              />
              <figcaption>The original 4 Sisters Hut</figcaption>
            </figure>
          </article>

          <article className="timeline-item">
            <div className="timeline-marker" aria-hidden="true">
              <svg viewBox="0 0 48 48">
                <path d="M9 20h30l-3-9H12l-3 9Z" />
                <path d="M12 23v16h24V23M18 39V29h12v10" />
                <path d="M9 20c0 4 6 4 6 0 0 4 6 4 6 0 0 4 6 4 6 0 0 4 6 4 6 0 0 4 6 4 6 0" />
              </svg>
            </div>
            <div className="timeline-copy">
              <p className="timeline-date">1990</p>
              <h3>Kenny&apos;s Korner</h3>
              <p>
                In 1990, the restaurant was renamed Kenny&apos;s Korner, but the
                heart of the business stayed the same — serving delicious
                breakfast, welcoming everyone, and being a part of the
                neighborhood.
              </p>
            </div>
            <figure className="timeline-photo timeline-photo--right">
              <OptimizedImage
                src="/images/kennys-exterior-front.jpg"
                alt="The Kenny's Korner restaurant exterior"
                sizes="(max-width: 900px) calc(100vw - 112px), 32vw"
              />
              <figcaption>A new name, the same warm welcome</figcaption>
            </figure>
          </article>

          <article className="timeline-item timeline-item--today">
            <div className="timeline-marker" aria-hidden="true">
              <svg viewBox="0 0 48 48">
                <circle cx="24" cy="24" r="8" />
                <path d="M24 5v7M24 36v7M5 24h7M36 24h7M10.5 10.5l5 5M32.5 32.5l5 5M37.5 10.5l-5 5M15.5 32.5l-5 5" />
              </svg>
            </div>
            <div className="timeline-copy">
              <p className="timeline-date">Today</p>
              <h3>Still Serving Good Mornings</h3>
              <p>
                Decades later, we&apos;re proud to continue the tradition.
                Whether you&apos;re a longtime regular or visiting for the first
                time, we&apos;re grateful to be part of your story and to keep
                serving the meals and moments that bring people together.
              </p>
            </div>
            <div className="timeline-waves" aria-hidden="true">
              <span />
              <span />
              <span />
            </div>
          </article>
        </div>
      </section>

      <section className="values-section">
        <p className="section-label section-label--light">What matters to us</p>
        <h2>Simple things, done right.</h2>
        <div className="values-grid">
          <article>
            <span>01</span>
            <h3>Made fresh</h3>
            <p>Comforting plates prepared with care, every single day.</p>
          </article>
          <article>
            <span>02</span>
            <h3>Easygoing</h3>
            <p>
              A sunny, welcoming place where you can settle in and stay awhile.
            </p>
          </article>
          <article>
            <span>03</span>
            <h3>For everyone</h3>
            <p>Neighbors and newcomers always have a place at our table.</p>
          </article>
        </div>
      </section>

      <section className="story-quote">
        <span aria-hidden="true">“</span>
        <blockquote>
          Good mornings, great food, and a seat for everyone.
        </blockquote>
        <Link to="/visit">Come visit us →</Link>
      </section>
      <OrderBanner />
    </main>
  )
}

export function VisitPage() {
  return (
    <main className="inner-page">
      <PageHero
        eyebrow="Visit us"
        title="Come hungry. Leave happy."
        description="Whether it is your first visit or your fiftieth, we are ready to make your day a little brighter."
        image="/images/breakfast-bowl.jpg"
        variant="visit"
      />

      <section className="visit-page-grid">
        <div className="visit-card visit-card--hours">
          <p className="section-label section-label--light">Hours</p>
          <h2>Open seven days a week.</h2>
          <div className="hours-row">
            <span>Monday—Sunday</span>
            <strong>7:00 AM—2:00 PM</strong>
          </div>
          <p>
            Holiday hours may vary. Check with the restaurant before your visit.
          </p>
        </div>

        <div className="visit-card visit-card--location">
          <p className="section-label">Find your way</p>
          <h2>Right around the Korner.</h2>
          <p>Tap below for the quickest route to Kenny&apos;s Korner.</p>
          <a
            href="https://www.google.com/maps/search/?api=1&query=Kenny%27s+Korner"
            target="_blank"
            rel="noreferrer"
          >
            Get directions <span aria-hidden="true">→</span>
          </a>
          <div className="map-visual" aria-hidden="true">
            <span className="map-road map-road--one" />
            <span className="map-road map-road--two" />
            <span className="map-pin">K</span>
          </div>
        </div>

        <div className="visit-card visit-card--photo">
          <OptimizedImage
            src="/images/gallery-one.jpg"
            alt="A fresh plate at Kenny's Korner"
            sizes="(max-width: 900px) calc(100vw - 32px), 55vw"
          />
        </div>
      </section>

      <section className="visit-help">
        <div>
          <p className="section-label">Plan your visit</p>
          <h2>Easy mornings start here.</h2>
        </div>
        <div className="visit-help-list">
          <article>
            <span>01</span>
            <h3>Dine in</h3>
            <p>Pull up a chair and enjoy the full Kenny&apos;s experience.</p>
          </article>
          <article>
            <span>02</span>
            <h3>Pick up</h3>
            <p>Order through Toast and we will have it ready for you.</p>
          </article>
          <article>
            <span>03</span>
            <h3>Bring the crew</h3>
            <p>Families, friends, and hungry travelers are always welcome.</p>
          </article>
        </div>
      </section>
      <OrderBanner />
    </main>
  )
}

export function NotFoundPage() {
  return (
    <main className="not-found">
      <OptimizedImage
        src="/images/kennys-korner-logo.jpg"
        alt=""
        loading="eager"
        sizes="150px"
      />
      <p className="section-label">404 · Wrong turn</p>
      <h1>This is not the Korner you were looking for.</h1>
      <Link to="/">Back to homepage →</Link>
    </main>
  )
}
