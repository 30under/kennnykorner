(function () {
  const origin = window.location.origin;

  function editable(target) {
    return target && target.closest ? target.closest("[data-kk-edit]") : null;
  }

  document.addEventListener("click", function (event) {
    const node = editable(event.target);
    const link = event.target.closest ? event.target.closest("a") : null;
    if (link || (node && node.tagName !== "VIDEO")) {
      event.preventDefault();
      event.stopPropagation();
    }
    if (!node || window.parent === window) {
      return;
    }
    document.querySelectorAll(".is-kk-selected").forEach(function (item) {
      item.classList.remove("is-kk-selected");
    });
    node.classList.add("is-kk-selected");
    window.parent.postMessage({
      source: "kk-live",
      type: "focus",
      path: node.getAttribute("data-kk-edit") || "",
      screen: node.getAttribute("data-kk-screen") || "",
    }, origin);
  }, true);

  window.addEventListener("message", function (event) {
    if (event.origin !== origin || !event.data || event.data.source !== "kk-editor") {
      return;
    }
    if (event.data.type === "scroll") {
      window.scrollTo(0, Number(event.data.y) || 0);
      return;
    }
    if (event.data.type === "select") {
      const node = document.querySelector('[data-kk-edit="' + CSS.escape(event.data.path || "") + '"]');
      document.querySelectorAll(".is-kk-selected").forEach(function (item) {
        item.classList.remove("is-kk-selected");
      });
      if (node) {
        node.classList.add("is-kk-selected");
        node.scrollIntoView({ block: "center", behavior: "smooth" });
      }
      return;
    }
    if (event.data.type !== "bind") {
      return;
    }
    const nodes = document.querySelectorAll('[data-kk-bind="' + CSS.escape(event.data.path || "") + '"]');
    if (!nodes.length) {
      window.parent.postMessage({ source: "kk-live", type: "missing", path: event.data.path || "" }, origin);
      return;
    }
    nodes.forEach(function (node) {
      if (event.data.kind === "image") {
        const image = node.tagName === "IMG" ? node : node.querySelector("img");
        if (!image || !event.data.url) {
          window.parent.postMessage({ source: "kk-live", type: "missing", path: event.data.path || "" }, origin);
          return;
        }
        image.src = event.data.url;
        image.removeAttribute("srcset");
        node.querySelectorAll("source").forEach(function (source) {
          source.removeAttribute("srcset");
        });
        if (typeof event.data.alt === "string") {
          image.alt = event.data.alt;
        }
        return;
      }
      if (event.data.kind === "html") {
        node.innerHTML = event.data.html || "";
        return;
      }
      node.textContent = event.data.value || "";
    });
  });
})();
