(function () {
  const toggle = document.querySelector(".menu-toggle");
  const mobileNav = document.querySelector("#mobile-navigation");
  if (toggle && mobileNav) {
    const setOpen = (open) => {
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
      mobileNav.classList.toggle("is-open", open);
    };
    toggle.addEventListener("click", () => {
      setOpen(toggle.getAttribute("aria-expanded") !== "true");
    });
    mobileNav.addEventListener("click", (event) => {
      if (event.target.closest("a")) {
        setOpen(false);
      }
    });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        setOpen(false);
      }
    });
  }

  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  document.querySelectorAll(".js-kitchen-video").forEach((video) => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting && !reducedMotion) {
          video.play().catch(() => undefined);
        } else {
          video.pause();
        }
      },
      { threshold: 0.35 },
    );
    observer.observe(video);
  });

  document.querySelectorAll(".js-story-video").forEach((video) => {
    const frame = video.closest(".timeline-video-frame");
    const play = frame?.querySelector(".timeline-video-play");
    const fallback = frame?.querySelector(".video-fallback");
    const motion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const failed = () => {
      video.pause();
      if (play) {
        play.hidden = true;
      }
      if (fallback) {
        fallback.hidden = false;
      }
    };
    video.addEventListener("error", failed);
    const sync = (visible) => {
      if (fallback && !fallback.hidden) {
        return;
      }
      if (motion.matches) {
        video.pause();
        video.autoplay = false;
        if (play) {
          play.hidden = false;
        }
        return;
      }
      if (play) {
        play.hidden = true;
      }
      if (visible) {
        video.play().catch(() => {
          if (play) {
            play.hidden = false;
          }
        });
      } else {
        video.pause();
      }
    };
    play?.addEventListener("click", () => {
      video.muted = true;
      video.play().then(() => {
        play.hidden = true;
      }).catch(failed);
    });
    const observer = new IntersectionObserver(([entry]) => sync(entry.isIntersecting), { threshold: 0.35 });
    observer.observe(video);
    motion.addEventListener?.("change", () => sync(true));
    sync(false);
  });

  document.querySelectorAll(".video-carousel").forEach((carousel) => {
    const slides = [...carousel.querySelectorAll(".video-slide")];
    if (!slides.length) {
      return;
    }
    let index = 0;
    let playToken = 0;
    const status = carousel.querySelector(".video-carousel-status");
    const stopAll = () => {
      playToken += 1;
      carousel.querySelectorAll(".js-carousel-video").forEach((video) => {
        video.pause();
      });
    };
    const playFromStart = (video) => {
      const token = ++playToken;
      carousel.querySelectorAll(".js-carousel-video").forEach((other) => {
        if (other !== video) {
          other.pause();
        }
      });
      const frame = video.closest(".video-slide-frame");
      const play = frame?.querySelector(".video-slide-play");
      const fallback = frame?.querySelector(".video-fallback");
      video.pause();
      video.muted = true;
      const start = () => {
        if (token !== playToken) {
          return;
        }
        const begin = () => {
          if (token !== playToken) {
            return;
          }
          const pending = video.play();
          if (!pending || typeof pending.then !== "function") {
            if (play) {
              play.hidden = true;
            }
            return;
          }
          pending.then(() => {
            if (token !== playToken) {
              video.pause();
              return;
            }
            if (play) {
              play.hidden = true;
            }
          }).catch(() => {
            if (token !== playToken) {
              return;
            }
            video.pause();
            if (play) {
              play.hidden = false;
            }
            if (fallback && video.error) {
              fallback.hidden = false;
            }
          });
        };
        if (video.currentTime > 0.05) {
          const onSeeked = () => {
            video.removeEventListener("seeked", onSeeked);
            begin();
          };
          video.addEventListener("seeked", onSeeked);
          try {
            video.currentTime = 0;
          } catch (error) {
            video.removeEventListener("seeked", onSeeked);
            begin();
          }
          return;
        }
        begin();
      };
      if (video.readyState >= 2) {
        start();
        return;
      }
      let ready = false;
      const onReady = () => {
        if (ready) {
          return;
        }
        ready = true;
        video.removeEventListener("loadeddata", onReady);
        video.removeEventListener("canplay", onReady);
        start();
      };
      video.addEventListener("loadeddata", onReady);
      video.addEventListener("canplay", onReady);
      video.load();
    };
    const show = (next) => {
      const wrapped = (next + slides.length) % slides.length;
      if (wrapped === index) {
        return;
      }
      index = wrapped;
      stopAll();
      slides.forEach((slide, slideIndex) => {
        const active = slideIndex === index;
        const video = slide.querySelector("video");
        const play = slide.querySelector(".video-slide-play");
        slide.hidden = !active;
        slide.classList.toggle("is-active", active);
        if (video) {
          video.pause();
        }
        if (!active && play && !video?.error) {
          play.hidden = false;
        }
      });
      carousel.querySelectorAll("[data-carousel-dot]").forEach((dot) => {
        const active = Number(dot.dataset.carouselDot) === index;
        if (active) {
          dot.setAttribute("aria-current", "true");
        } else {
          dot.removeAttribute("aria-current");
        }
      });
      if (status) {
        status.textContent = `${index + 1} / ${slides.length}`;
      }
      const selected = slides[index]?.querySelector("video");
      if (selected && !selected.error) {
        playFromStart(selected);
      }
    };
    carousel.addEventListener("click", (event) => {
      const play = event.target.closest(".video-slide-play");
      const previous = event.target.closest("[data-carousel-prev]");
      const next = event.target.closest("[data-carousel-next]");
      const dot = event.target.closest("[data-carousel-dot]");
      if (play) {
        const video = play.closest(".video-slide")?.querySelector("video");
        if (!video) {
          return;
        }
        playFromStart(video);
      } else if (previous) {
        show(index - 1);
      } else if (next) {
        show(index + 1);
      } else if (dot) {
        show(Number(dot.dataset.carouselDot || 0));
      }
    });
    carousel.addEventListener("keydown", (event) => {
      if (event.target.closest("video, input, textarea, select")) {
        return;
      }
      if (event.key === "ArrowLeft") {
        event.preventDefault();
        show(index - 1);
      } else if (event.key === "ArrowRight") {
        event.preventDefault();
        show(index + 1);
      }
    });
    carousel.querySelectorAll(".js-carousel-video").forEach((video) => {
      video.addEventListener("play", () => {
        if (video.closest(".video-slide")?.hidden) {
          video.pause();
          return;
        }
        carousel.querySelectorAll(".js-carousel-video").forEach((other) => {
          if (other !== video) {
            other.pause();
          }
        });
      });
      video.addEventListener("error", () => {
        const frame = video.closest(".video-slide-frame");
        const fallback = frame?.querySelector(".video-fallback");
        const play = frame?.querySelector(".video-slide-play");
        if (play) {
          play.hidden = true;
        }
        if (fallback) {
          fallback.hidden = false;
        }
      });
    });
    let swipe = null;
    const viewport = carousel.querySelector(".video-carousel-viewport");
    viewport?.addEventListener("pointerdown", (event) => {
      if (event.target.closest("button, a")) {
        return;
      }
      const video = event.target.closest("video");
      if (video) {
        const rect = video.getBoundingClientRect();
        if (event.clientY > rect.bottom - 48) {
          return;
        }
      }
      swipe = { x: event.clientX, y: event.clientY };
      if (viewport.setPointerCapture) {
        viewport.setPointerCapture(event.pointerId);
      }
    });
    viewport?.addEventListener("pointerup", (event) => {
      if (!swipe) {
        return;
      }
      const dx = event.clientX - swipe.x;
      const dy = event.clientY - swipe.y;
      swipe = null;
      if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) {
        return;
      }
      show(index + (dx < 0 ? 1 : -1));
    });
    viewport?.addEventListener("pointercancel", () => {
      swipe = null;
    });
    const observer = new IntersectionObserver(([entry]) => {
      if (!entry.isIntersecting) {
        stopAll();
      }
    }, { threshold: 0.2 });
    observer.observe(carousel);
  });

  document.querySelectorAll(".js-menu-viewer").forEach((viewer) => {
    const dataNode = viewer.querySelector(".js-menu-data");
    if (!dataNode) {
      return;
    }
    let pages = [];
    try {
      pages = JSON.parse(dataNode.textContent || "[]");
    } catch (error) {
      return;
    }
    if (!pages.length) {
      return;
    }

    let activeIndex = null;
    let zoom = 1;
    let pan = { x: 0, y: 0 };
    let drag = null;
    let returnFocus = null;
    let lightbox = null;
    const previousOverflow = document.body.style.overflow;

    const focusable = () =>
      [...lightbox.querySelectorAll("button, [href], input, select, textarea")].filter(
        (element) => !element.disabled,
      );

    const applyTransform = () => {
      const image = lightbox.querySelector(".menu-lightbox-stage img");
      if (!image) {
        return;
      }
      image.style.transform = `translate(${pan.x}px, ${pan.y}px) scale(${zoom})`;
      lightbox.querySelector(".menu-lightbox-stage").classList.toggle("is-zoomed", zoom > 1);
      lightbox.querySelector("[data-zoom-out]").disabled = zoom <= 1;
      lightbox.querySelector("[data-zoom-in]").disabled = zoom >= 3;
      lightbox.querySelector("[data-zoom-label]").textContent = `${Math.round(zoom * 100)}%`;
    };

    const render = () => {
      const page = pages[activeIndex];
      lightbox.querySelector("[data-page-label]").textContent = `Page ${activeIndex + 1} of ${pages.length}`;
      const image = lightbox.querySelector(".menu-lightbox-stage img");
      if (image.getAttribute("src") !== page.large) {
        image.src = page.large;
      }
      image.alt = page.alt || "";
      if (page.width) {
        image.width = page.width;
      }
      if (page.height) {
        image.height = page.height;
      }
      applyTransform();
    };

    const resetView = () => {
      zoom = 1;
      pan = { x: 0, y: 0 };
    };

    const close = () => {
      if (!lightbox) {
        return;
      }
      lightbox.remove();
      lightbox = null;
      activeIndex = null;
      document.body.style.overflow = previousOverflow;
      document.querySelector(".site-shell")?.removeAttribute("aria-hidden");
      if (returnFocus && typeof returnFocus.focus === "function") {
        returnFocus.focus();
      }
    };

    const open = (index) => {
      returnFocus = document.activeElement;
      activeIndex = index;
      resetView();
      lightbox = document.createElement("div");
      lightbox.className = "menu-lightbox";
      lightbox.setAttribute("role", "dialog");
      lightbox.setAttribute("aria-modal", "true");
      lightbox.innerHTML = `
        <div class="menu-lightbox-toolbar">
          <span data-page-label></span>
          <div>
            <button type="button" data-zoom-out aria-label="Zoom out" title="Zoom out">${icon("minus")}</button>
            <span data-zoom-label>100%</span>
            <button type="button" data-zoom-in aria-label="Zoom in" title="Zoom in">${icon("plus")}</button>
            <button type="button" data-close class="menu-lightbox-close" aria-label="Close full-screen menu viewer" title="Close">${icon("close")}</button>
          </div>
        </div>
        <button type="button" class="menu-lightbox-nav menu-lightbox-nav--previous" data-previous aria-label="Previous menu page">${icon("previous")}</button>
        <div class="menu-lightbox-stage"><img alt="" draggable="false"></div>
        <button type="button" class="menu-lightbox-nav menu-lightbox-nav--next" data-next aria-label="Next menu page">${icon("next")}</button>
      `;
      document.body.appendChild(lightbox);
      document.body.style.overflow = "hidden";
      document.querySelector(".site-shell")?.setAttribute("aria-hidden", "true");
      render();
      lightbox.querySelector("[data-close]").focus();
    };

    const changePage = (index) => {
      activeIndex = (index + pages.length) % pages.length;
      resetView();
      render();
    };

    viewer.addEventListener("click", (event) => {
      const button = event.target.closest(".menu-preview-page");
      if (!button) {
        return;
      }
      open(Number(button.dataset.index || 0));
    });

    document.addEventListener("click", (event) => {
      if (!lightbox || !lightbox.contains(event.target)) {
        return;
      }
      if (event.target.closest("[data-close]")) {
        close();
      } else if (event.target.closest("[data-previous]")) {
        changePage(activeIndex - 1);
      } else if (event.target.closest("[data-next]")) {
        changePage(activeIndex + 1);
      } else if (event.target.closest("[data-zoom-out]")) {
        zoom = Math.max(1, zoom - 0.25);
        if (zoom === 1) {
          pan = { x: 0, y: 0 };
        }
        applyTransform();
      } else if (event.target.closest("[data-zoom-in]")) {
        zoom = Math.min(3, zoom + 0.25);
        applyTransform();
      }
    });

    document.addEventListener("keydown", (event) => {
      if (!lightbox) {
        return;
      }
      if (event.key === "Escape") {
        event.preventDefault();
        close();
        return;
      }
      if (event.key === "ArrowLeft") {
        event.preventDefault();
        changePage(activeIndex - 1);
      } else if (event.key === "ArrowRight") {
        event.preventDefault();
        changePage(activeIndex + 1);
      } else if (event.key === "+" || event.key === "=") {
        zoom = Math.min(3, zoom + 0.25);
        applyTransform();
      } else if (event.key === "-") {
        zoom = Math.max(1, zoom - 0.25);
        if (zoom === 1) {
          pan = { x: 0, y: 0 };
        }
        applyTransform();
      } else if (event.key === "Tab") {
        const items = focusable();
        if (!items.length) {
          return;
        }
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    });

    lightboxStageEvents();

    function lightboxStageEvents() {
      document.addEventListener("pointerdown", (event) => {
        const stage = event.target.closest?.(".menu-lightbox-stage");
        if (!lightbox || zoom <= 1 || !stage) {
          return;
        }
        stage.setPointerCapture?.(event.pointerId);
        drag = { pointerX: event.clientX, pointerY: event.clientY, startX: pan.x, startY: pan.y };
      });
      document.addEventListener("pointermove", (event) => {
        if (!drag) {
          return;
        }
        pan = {
          x: drag.startX + event.clientX - drag.pointerX,
          y: drag.startY + event.clientY - drag.pointerY,
        };
        applyTransform();
      });
      document.addEventListener("pointerup", () => {
        drag = null;
      });
      document.addEventListener("pointercancel", () => {
        drag = null;
      });
    }
  });

  function icon(name) {
    const paths = {
      previous: '<path d="m14.5 6-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
      next: '<path d="m9.5 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
      minus: '<path d="M6 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
      plus: '<path d="M12 6v12M6 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
      close: '<path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    };
    return `<svg aria-hidden="true" viewBox="0 0 24 24" fill="none">${paths[name] || ""}</svg>`;
  }
})();
