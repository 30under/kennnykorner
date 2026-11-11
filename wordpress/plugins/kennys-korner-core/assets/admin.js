(function ($) {
  function previewUrl(attachment) {
    if (attachment.sizes && attachment.sizes.thumbnail) {
      return attachment.sizes.thumbnail.url;
    }
    return attachment.url || "";
  }

  $(document).on("click", ".kk-media-select", function (event) {
    event.preventDefault();
    const wrap = $(this).closest(".kk-media");
    const library = wrap.data("library") || "image";
    const frame = wp.media({
      title: "Select file",
      button: { text: "Use this file" },
      multiple: false,
      library: library === "application" || library === "video" ? { type: library } : { type: "image" },
    });
    frame.on("select", function () {
      const attachment = frame.state().get("selection").first().toJSON();
      const selected = attachment.type || "";
      const matches = (library === "video" && selected === "video")
        || (library === "image" && selected === "image")
        || (library === "application" && selected === "application");
      let note = wrap.children(".kk-media-note");
      if (!matches) {
        if (!note.length) {
          note = $('<p class="kk-media-note"></p>').appendTo(wrap);
        }
        note.text(library === "video"
          ? "Choose an MP4 or WebM file for Video file."
          : "Choose an image for this field. A video belongs in Video file.");
        return;
      }
      note.remove();
      wrap.find(".kk-media-id").val(attachment.id);
      wrap.find(".kk-media-asset").val("");
      if (attachment.type === "image") {
        wrap.find(".kk-media-preview").html('<img src="' + previewUrl(attachment) + '" alt="">');
      } else {
        wrap.find(".kk-media-preview").text(attachment.filename || attachment.title || "File selected");
      }
      wrap.find(".kk-media-select").text("Replace");
      $(document).trigger("kk-image-change", [wrap, attachment]);
    });
    frame.open();
  });

  $(document).on("click", ".kk-media-remove", function (event) {
    event.preventDefault();
    const wrap = $(this).closest(".kk-media");
    wrap.find(".kk-media-id").val("0");
    wrap.find(".kk-media-asset").val("");
    wrap.find(".kk-media-preview").html('<span class="kk-media-empty">No file selected</span>');
    wrap.find(".kk-media-select").text("Select");
    $(document).trigger("kk-structure-change");
  });

  function bindSortable(list) {
    const $list = $(list);
    if ($list.data("ui-sortable")) {
      return;
    }
    $list.sortable({
      handle: ".kk-drag",
      items: "> .kk-repeater-item",
      axis: "y",
      tolerance: "pointer",
      update: function () {
        $(document).trigger("kk-structure-change");
      },
    });
  }

  $(".kk-repeater-list").each(function () {
    bindSortable(this);
  });

  $(document).on("click", ".kk-repeater-add", function (event) {
    event.preventDefault();
    const box = $(this).closest(".kk-repeater");
    const template = box.children("template").get(0);
    if (!template) {
      return;
    }
    const id = "row-" + Date.now().toString(36);
    const html = template.innerHTML.replaceAll("__i__", "n" + Date.now()).replaceAll("__id__", id);
    const list = box.children(".kk-repeater-list");
    list.append(html);
    const added = list.children(".kk-repeater-item").last();
    added.attr("data-kk-id", id);
    added.find("[data-kk-path]").addBack().each(function () {
      const current = this.getAttribute("data-kk-path");
      if (current) {
        this.setAttribute("data-kk-path", current.replaceAll("__id__", id));
      }
    });
    box.find(".kk-empty").attr("hidden", "hidden");
    bindSortable(list.get(0));
    $(document).trigger("kk-structure-change");
  });

  $(document).on("click", ".kk-repeater-remove", function (event) {
    event.preventDefault();
    const item = $(this).closest(".kk-repeater-item");
    const list = item.parent();
    item.remove();
    if (!list.children(".kk-repeater-item").length) {
      list.closest(".kk-repeater").find(".kk-empty").removeAttr("hidden");
    }
    $(document).trigger("kk-structure-change");
  });

  $("#kk-import-start").on("click", async function () {
    const button = $(this);
    const log = $("#kk-import-log");
    button.prop("disabled", true);
    log.removeAttr("hidden").text("Starting import…");
    const payload = {
      action: "kk_import",
      nonce: window.kkAdmin.nonce,
      overwrite: $("#kk-overwrite").is(":checked") ? 1 : 0,
      confirm: $("#kk-confirm").val(),
    };
    try {
      let response = await post({ ...payload, step: "start" });
      while (response.success && !response.data.done) {
        render(log, response.data);
        response = await post({ ...payload, step: "batch" });
      }
      if (!response.success) {
        log.text(response.data && response.data.message ? response.data.message : "Import failed.");
      } else {
        render(log, response.data);
        log.append("\nFinished.");
      }
    } catch (error) {
      log.text(error && error.message ? error.message : "Import failed.");
    }
    button.prop("disabled", false);
  });

  function render(log, data) {
    const lines = []
      .concat(data.log || [])
      .concat((data.errors || []).map((error) => "Error: " + error));
    log.text((data.completed || 0) + " / " + (data.total || 0) + " media items\n" + lines.join("\n"));
  }

  function post(body) {
    return $.post(window.kkAdmin.ajaxUrl, body);
  }

  const studio = document.querySelector(".kk-studio");
  if (studio && window.kkAdmin && window.kkAdmin.previewUrl) {
    const frame = studio.querySelector("iframe");
    const note = studio.querySelector(".kk-live-note");
    const status = studio.querySelector(".kk-studio-status");
    const form = studio.querySelector("form");
    let dirty = false;
    let stageTimer = 0;
    let bindTimer = 0;
    let stageRequest = 0;
    let reloadAfterStage = false;
    const origin = new URL(window.kkAdmin.previewUrl, window.location.href).origin;

    function postToFrame(message) {
      if (!frame || !frame.contentWindow) {
        return;
      }
      frame.contentWindow.postMessage(Object.assign({ source: "kk-editor" }, message), origin);
    }

    function markDirty() {
      dirty = true;
      if (status) {
        status.dataset.state = "dirty";
        status.textContent = "Chưa công khai";
      }
    }

    function escapeHtml(value) {
      return String(value).replace(/[&<>]/g, function (character) {
        return { "&": "&amp;", "<": "&lt;", ">": "&gt;" }[character];
      });
    }

    function fieldNode(input) {
      return input.closest("[data-kk-kind]") || input.closest("[data-kk-path]");
    }

    function pushBind(input) {
      const field = fieldNode(input);
      if (!field) {
        return;
      }
      const path = field.getAttribute("data-kk-path") || "";
      const kind = field.getAttribute("data-kk-kind") || "text";
      if (!path || kind === "select" || kind === "checkbox" || kind === "repeater" || kind === "file") {
        return;
      }
      if (kind === "headline") {
        const lines = [];
        field.querySelectorAll(".kk-headline-row").forEach(function (row) {
          const before = (row.querySelector('[name$="[before]"]') || {}).value || "";
          const accent = (row.querySelector('[name$="[accent]"]') || {}).value || "";
          const after = (row.querySelector('[name$="[after]"]') || {}).value || "";
          let html = escapeHtml(before);
          if (accent) {
            html += "<em>" + escapeHtml(accent) + "</em>";
          }
          html += escapeHtml(after);
          if (html) {
            lines.push(html);
          }
        });
        postToFrame({ type: "bind", path: path, kind: "html", html: lines.join("<br>") });
        return;
      }
      if (kind === "image") {
        return;
      }
      const value = input.value || "";
      if (kind === "textarea" && /\n\s*\n/.test(value)) {
        scheduleStage(true);
        return;
      }
      postToFrame({
        type: "bind",
        path: path,
        kind: kind === "textarea" ? "html" : "text",
        value: value,
        html: escapeHtml(value).replace(/\n/g, "<br>"),
      });
    }

    function scheduleStage(reload) {
      reloadAfterStage = reloadAfterStage || reload;
      window.clearTimeout(stageTimer);
      stageTimer = window.setTimeout(function () {
        const reloadNow = reloadAfterStage;
        reloadAfterStage = false;
        const ticket = ++stageRequest;
        const params = new URLSearchParams(form ? $(form).serialize() : "");
        params.set("action", "kk_stage_preview");
        params.set("nonce", window.kkAdmin.stageNonce);
        $.post(window.kkAdmin.ajaxUrl, params.toString()).done(function (response) {
          if (ticket !== stageRequest) {
            return;
          }
          if (note && response && response.data && response.data.errors && response.data.errors.length && !reloadNow) {
            note.hidden = false;
            note.textContent = response.data.errors[0];
          }
          if (reloadNow) {
            reloadFrame();
          }
        });
      }, reload ? 180 : 350);
    }

    function reloadFrame() {
      if (!frame) {
        return;
      }
      let y = 0;
      try {
        y = frame.contentWindow.scrollY || 0;
      } catch (error) {
        y = 0;
      }
      const next = new URL(frame.src || window.kkAdmin.previewUrl, window.location.href);
      next.searchParams.set("kk_r", String(Date.now()));
      frame.onload = function () {
        postToFrame({ type: "scroll", y: y });
      };
      frame.src = next.toString();
    }

    function showOtherScreen(screen) {
      if (!note) {
        return;
      }
      note.hidden = false;
      note.replaceChildren();
      if (screen === "menus") {
        note.append("Menu điều hướng được sửa trong Appearance → Menus. ");
        const link = document.createElement("a");
        link.href = window.kkAdmin.menusUrl;
        link.textContent = "Mở menu";
        note.append(link);
        return;
      }
      const label = (window.kkAdmin.screens && window.kkAdmin.screens[screen]) || screen;
      note.append("Nội dung này thuộc màn " + label + ". ");
      const link = document.createElement("a");
      link.href = "admin.php?page=kk-" + screen;
      link.textContent = "Mở " + label;
      link.addEventListener("click", function (event) {
        if (dirty && !window.confirm("Thay đổi chưa được lưu. Mở màn khác?")) {
          event.preventDefault();
        }
      });
      note.append(link);
    }

    function focusField(path) {
      if (!path) {
        return;
      }
      let node = studio.querySelector('[data-kk-path="' + CSS.escape(path) + '"]');
      const parts = path.split(".");
      while (!node && parts.length > 1) {
        parts.pop();
        node = studio.querySelector('[data-kk-path="' + CSS.escape(parts.join(".")) + '"]');
      }
      studio.querySelectorAll(".is-kk-target").forEach(function (item) {
        item.classList.remove("is-kk-target");
      });
      if (!node) {
        return;
      }
      node.classList.add("is-kk-target");
      node.scrollIntoView({ block: "nearest", behavior: "smooth" });
      const input = node.matches(":is(input, textarea, select)") ? node : node.querySelector("input, textarea, select");
      if (input) {
        input.focus({ preventScroll: true });
      }
    }

    window.addEventListener("message", function (event) {
      if (event.origin !== origin || !event.data || event.data.source !== "kk-live") {
        return;
      }
      if (event.data.type === "focus") {
        if (event.data.screen && event.data.screen !== studio.dataset.screen) {
          showOtherScreen(event.data.screen);
          if (window.matchMedia("(max-width: 782px)").matches) {
            setPane("edit");
          }
          return;
        }
        if (note) {
          note.hidden = true;
        }
        setPane("edit");
        focusField(event.data.path);
      }
      if (event.data.type === "missing") {
        scheduleStage(true);
      }
    });

    function setPane(pane) {
      studio.classList.toggle("is-preview", pane !== "edit");
      studio.classList.toggle("is-edit", pane === "edit");
      studio.querySelectorAll(".kk-studio-tabs button").forEach(function (button) {
        const active = button.dataset.pane === pane;
        button.classList.toggle("is-active", active);
        button.setAttribute("aria-selected", active ? "true" : "false");
      });
    }

    studio.querySelectorAll(".kk-studio-tabs button").forEach(function (button) {
      button.addEventListener("click", function () {
        setPane(button.dataset.pane);
      });
    });

    studio.querySelectorAll("[data-device]").forEach(function (button) {
      button.addEventListener("click", function () {
        studio.querySelectorAll("[data-device]").forEach(function (item) {
          item.classList.toggle("is-active", item === button);
        });
        const preview = studio.querySelector(".kk-studio-preview");
        preview.classList.remove("is-tablet", "is-mobile");
        if (button.dataset.device !== "desktop") {
          preview.classList.add("is-" + button.dataset.device);
        }
      });
    });

    const missingPage = studio.querySelector("[data-kk-view='404']");
    if (missingPage && frame) {
      missingPage.addEventListener("click", function () {
        const next = new URL(window.kkAdmin.previewUrl, window.location.href);
        next.pathname = "/kk-editor-missing-page/";
        next.searchParams.set("kk_r", String(Date.now()));
        frame.src = next.toString();
        setPane("preview");
      });
    }

    $(document).on("input", ".kk-studio :input", function () {
      markDirty();
      const input = this;
      window.clearTimeout(bindTimer);
      bindTimer = window.setTimeout(function () {
        pushBind(input);
      }, 30);
      scheduleStage(false);
    });

    $(document).on("change", ".kk-studio select, .kk-studio input[type='checkbox']", function () {
      markDirty();
      scheduleStage(true);
    });

    $(document).on("focusin", ".kk-studio [data-kk-path] :input", function () {
      const path = ($(this).closest("[data-kk-path]").attr("data-kk-path")) || "";
      postToFrame({ type: "select", path: path });
    });

    $(document).on("kk-structure-change", function () {
      markDirty();
      scheduleStage(true);
    });

    $(document).on("kk-image-change", function (_event, wrap, attachment) {
      markDirty();
      const path = wrap.closest("[data-kk-path]").attr("data-kk-path") || "";
      postToFrame({
        type: "bind",
        path: path,
        kind: "image",
        url: attachment.url || "",
        alt: attachment.alt || "",
      });
      scheduleStage(false);
    });

    form.addEventListener("submit", function () {
      dirty = false;
    });

    window.addEventListener("beforeunload", function (event) {
      if (!dirty) {
        return;
      }
      event.preventDefault();
      event.returnValue = "";
    });
  }
})(jQuery);
