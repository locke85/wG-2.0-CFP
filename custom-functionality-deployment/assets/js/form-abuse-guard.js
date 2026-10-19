(() => {
  const challengeUrl = "/wp-json/wg-cfp/v1/form-challenge";
  const encoder = new TextEncoder();
  const toHex = (bytes) => Array.from(new Uint8Array(bytes), (byte) => byte.toString(16).padStart(2, "0")).join("");
  const digest = async (value) => toHex(await crypto.subtle.digest("SHA-256", encoder.encode(value)));
  const enabled = () => new Set((window.WGCfpFormAbuseGuardConfig?.formIds || []).map(Number));

  const hiddenField = (form, name) => {
    let input = form.querySelector(`[name="${name}"]`);
    if (!input) {
      input = document.createElement("input");
      input.type = "hidden";
      input.name = name;
      form.appendChild(input);
    }
    return input;
  };

  const status = (form) => {
    let node = form.querySelector("[data-wg-cfp-form-status]");
    if (!node) {
      node = document.createElement("p");
      node.dataset.wgCfpFormStatus = "";
      node.setAttribute("role", "status");
      node.setAttribute("aria-live", "polite");
      form.appendChild(node);
    }
    return node;
  };

  const setSubmit = (form, disabled) => form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
    button.disabled = disabled;
    button.setAttribute("aria-disabled", disabled ? "true" : "false");
  });

  const validChallenge = (challenge) => challenge
    && /^[A-Za-z0-9]{16,64}$/.test(challenge.id || "")
    && /^[A-Za-z0-9]{16,64}$/.test(challenge.salt || "")
    && /^[a-f0-9]{64}$/.test(challenge.signature || "")
    && Number.isInteger(challenge.expires)
    && challenge.expires * 1000 > Date.now()
    && Number.isInteger(challenge.difficulty)
    && challenge.difficulty >= 3
    && challenge.difficulty <= 5;

  const solve = async (challenge) => {
    const prefix = "0".repeat(challenge.difficulty);
    for (let nonce = 0; nonce < 2_000_000; nonce += 1) {
      if ((await digest(`${challenge.salt}:${nonce}`)).startsWith(prefix)) return String(nonce);
      if (nonce % 200 === 0) await new Promise((resolve) => setTimeout(resolve, 0));
    }
    throw new Error("challenge limit reached");
  };

  const init = async (root = document) => {
    root.querySelectorAll("form[data-gb-form-id]").forEach(async (form) => {
      const formId = Number(form.dataset.gbFormId || 0);
      if (!enabled().has(formId) || form.dataset.wgCfpFormGuard) return;
      form.dataset.wgCfpFormGuard = "pending";
      const message = status(form);
      setSubmit(form, true);
      try {
        const response = await fetch(`${challengeUrl}?form_id=${encodeURIComponent(formId)}`, { credentials: "same-origin", cache: "no-store", headers: { Accept: "application/json" } });
        const challenge = await response.json();
        if (!response.ok || !validChallenge(challenge)) throw new Error("invalid challenge");
        message.textContent = "Formularschutz wird vorbereitet …";
        const solution = await solve(challenge);
        hiddenField(form, "wg_cfp_challenge_id").value = challenge.id;
        hiddenField(form, "wg_cfp_challenge_signature").value = challenge.signature;
        hiddenField(form, "wg_cfp_challenge_solution").value = solution;
        message.textContent = "";
        form.dataset.wgCfpFormGuard = "ready";
        setSubmit(form, false);
      } catch (error) {
        form.dataset.wgCfpFormGuard = "failed";
        message.textContent = "Der Formularschutz konnte nicht vorbereitet werden. Bitte lade die Seite neu.";
      }
    });
  };

  window.WGCfpFormAbuseGuard = { init };
  document.addEventListener("DOMContentLoaded", () => init());
})();
