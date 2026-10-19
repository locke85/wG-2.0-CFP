(() => {
  const loadScript = (src) => new Promise((resolve, reject) => {
    const existing = [...document.scripts].find((node) => node.src === src);
    if (existing) return resolve();
    const node = document.createElement("script");
    node.src = src; node.onload = resolve; node.onerror = reject; document.head.appendChild(node);
  });
  const loadStyle = (href) => {
    if (!href || [...document.styleSheets].some((sheet) => sheet.href === href)) return;
    const node = document.createElement("link"); node.rel = "stylesheet"; node.href = href; document.head.appendChild(node);
  };
  const route = () => `${location.pathname.replace(/\/+$/, "") || "/"}${location.pathname === "/" ? "" : "/"}`;
  const mount = async (node) => {
    const formId = Number(node.dataset.wgCfpFormId || 0);
    if (!formId || node.dataset.wgCfpMounted) return;
    node.dataset.wgCfpMounted = "pending";
    try {
      const url = `/wp-json/wg-cfp/v1/static-form/${encodeURIComponent(formId)}?route=${encodeURIComponent(route())}`;
      const response = await fetch(url, { credentials: "same-origin", cache: "no-store", headers: { Accept: "application/json" } });
      const payload = await response.json();
      if (!response.ok || !payload || typeof payload.html !== "string") throw new Error("unavailable");
      node.innerHTML = payload.html; node.setAttribute("aria-busy", "false");
      loadStyle(payload.form_style_url);
      if (payload.cfp_script_url) await loadScript(payload.cfp_script_url);
      if (payload.form_script_url) await loadScript(payload.form_script_url);
      window.WGCfpFormAbuseGuard?.init?.(node);
      document.dispatchEvent(new Event("DOMContentLoaded"));
      node.dataset.wgCfpMounted = "ready";
    } catch (error) {
      node.setAttribute("aria-busy", "false");
      node.dataset.wgCfpMounted = "failed";
      node.innerHTML = '<p role="alert">Das Formular ist gerade nicht verfügbar. Bitte schreibe direkt an Alissa.</p>';
    }
  };
  const init = () => document.querySelectorAll("[data-wg-cfp-static-form-mount]").forEach(mount);
  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", init, { once: true }) : init();
})();
