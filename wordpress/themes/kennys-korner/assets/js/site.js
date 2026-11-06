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
