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
      wrap.find(".kk-media-id").val(attachment.id);
      wrap.find(".kk-media-asset").val("");
      if (attachment.type === "image") {
        wrap.find(".kk-media-preview").html('<img src="' + previewUrl(attachment) + '" alt="">');
      } else {
        wrap.find(".kk-media-preview").text(attachment.filename || attachment.title || "File selected");
      }
      wrap.find(".kk-media-select").text("Replace");
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
    const html = template.innerHTML.replaceAll("__i__", "n" + Date.now());
    const list = box.children(".kk-repeater-list");
    list.append(html);
    box.find(".kk-empty").attr("hidden", "hidden");
    bindSortable(list.get(0));
  });

  $(document).on("click", ".kk-repeater-remove", function (event) {
    event.preventDefault();
    const item = $(this).closest(".kk-repeater-item");
    const list = item.parent();
    item.remove();
    if (!list.children(".kk-repeater-item").length) {
      list.closest(".kk-repeater").find(".kk-empty").removeAttr("hidden");
    }
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
})(jQuery);
