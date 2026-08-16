/* =========================================================================
   StyleHub storefront JS — server-backed cart + Ecommerce6-StyleHub theme UI behaviours.
   ========================================================================= */
(function () {
  "use strict";
  const $  = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
  const csrf = () => (document.querySelector('meta[name="csrf-token"]') || {}).content || "";
  const money = (n) => "৳" + Number(n).toLocaleString("en-US", { maximumFractionDigits: 0 });

  async function api(url, body) {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf(), "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
      body: JSON.stringify(body || {}),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      const err = new Error(data.message || "Request failed");
      err.data = data;
      throw err;
    }
    return data;
  }

  /* ---------------- Header scroll ---------------- */
  const header = $(".site-header");
  if (header) {
    const onScroll = () => header.classList.toggle("scrolled", window.scrollY > 16);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* ---------------- Overlay / panels ---------------- */
  const overlay = $("#overlay"), cartDrawer = $("#cartDrawer"), mobileMenu = $("#mobileMenu"), filterPanel = $("#filterPanel");
  const mobileCats = $("[data-mobile-menu]");
  const openOverlay = () => { overlay && overlay.classList.remove("opacity-0", "pointer-events-none", "hidden"); document.body.classList.add("no-scroll"); };
  const anyOpen = () =>
    (cartDrawer && !cartDrawer.classList.contains("translate-x-full")) ||
    (mobileMenu && !mobileMenu.classList.contains("-translate-x-full")) ||
    (filterPanel && !filterPanel.classList.contains("-translate-x-full"));
  const maybeClose = () => { if (!anyOpen()) { overlay && overlay.classList.add("opacity-0", "pointer-events-none"); document.body.classList.remove("no-scroll"); } };
  const closeAll = () => {
    if (window.closeLiveChatPanel) window.closeLiveChatPanel();
    const chatRoot = document.getElementById("liveChatRoot");
    if (chatRoot) chatRoot.style.visibility = "";
    const sideCartBtn = document.querySelector("[data-open-cart]");
    if (sideCartBtn) sideCartBtn.style.visibility = "";
    cartDrawer && cartDrawer.classList.add("translate-x-full");
    mobileMenu && mobileMenu.classList.add("-translate-x-full");
    filterPanel && filterPanel.classList.add("-translate-x-full");
    closeMobileSearch();
    closeAccountMenus();
    maybeClose();
  };

  function openCart() { 
    if (window.closeLiveChatPanel) window.closeLiveChatPanel();
    const chatRoot = document.getElementById("liveChatRoot");
    if (chatRoot) chatRoot.style.visibility = "hidden";
    const sideCartBtn = document.querySelector("[data-open-cart]");
    if (sideCartBtn) sideCartBtn.style.visibility = "hidden";
    cartDrawer && cartDrawer.classList.remove("translate-x-full"); 
    openOverlay(); 
  }
  function closeCart() { 
    const chatRoot = document.getElementById("liveChatRoot");
    if (chatRoot) chatRoot.style.visibility = "";
    const sideCartBtn = document.querySelector("[data-open-cart]");
    if (sideCartBtn) sideCartBtn.style.visibility = "";
    cartDrawer && cartDrawer.classList.add("translate-x-full"); 
    maybeClose(); 
  }

  // Phone taskbar: hamburger opens slide-out drawer (Sites 1–3 pattern)
  function openMobileMenu() {
    closeMobileSearch();
    closeAccountMenus();
    if (mobileMenu) {
      mobileMenu.classList.remove("-translate-x-full");
      openOverlay();
      return;
    }
    if (mobileCats) mobileCats.classList.toggle("hidden");
  }
  $$("[data-menu-btn], [data-open-menu]").forEach((b) => b.addEventListener("click", (e) => {
    e.preventDefault();
    openMobileMenu();
  }));
  $$("[data-close-menu]").forEach((b) => b.addEventListener("click", () => { mobileMenu && mobileMenu.classList.add("-translate-x-full"); maybeClose(); }));
  $$("[data-open-filter]").forEach((b) => b.addEventListener("click", () => { filterPanel && filterPanel.classList.remove("-translate-x-full"); openOverlay(); }));
  $$("[data-close-filter]").forEach((b) => b.addEventListener("click", () => { filterPanel && filterPanel.classList.add("-translate-x-full"); maybeClose(); }));
  $$("[data-open-cart]").forEach((b) => b.addEventListener("click", (e) => { e.preventDefault(); closeMobileSearch(); closeAccountMenus(); openCart(); }));
  $$("[data-close-cart]").forEach((b) => b.addEventListener("click", closeCart));
  if (overlay) overlay.addEventListener("click", closeAll);
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeAll(); });

  /* ---------------- Mobile search ---------------- */
  function closeMobileSearch() {
    $$("#mobileSearchPanel").forEach((panel) => panel.classList.add("hidden"));
    $$("[data-toggle-search]").forEach((b) => b.setAttribute("aria-expanded", "false"));
  }
  function openMobileSearch(btn) {
    closeAccountMenus();
    const panel = (btn && btn.closest("header")?.querySelector("#mobileSearchPanel")) || $("#mobileSearchPanel");
    if (!panel) return;
    panel.classList.remove("hidden");
    $$("[data-toggle-search]").forEach((b) => b.setAttribute("aria-expanded", "true"));
    const input = panel.querySelector("[data-mobile-search-input]");
    if (input) setTimeout(() => input.focus(), 30);
  }
  $$("[data-toggle-search]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const panel = btn.closest("header")?.querySelector("#mobileSearchPanel") || $("#mobileSearchPanel");
      if (!panel) return;
      const open = !panel.classList.contains("hidden");
      if (open) closeMobileSearch();
      else openMobileSearch(btn);
    });
  });

  /* ---------------- Account dropdown ---------------- */
  function closeAccountMenus() {
    $$("[data-account-dropdown]").forEach((d) => d.classList.add("hidden"));
    $$("[data-account-toggle]").forEach((b) => b.setAttribute("aria-expanded", "false"));
  }
  $$("[data-account-menu]").forEach((menu) => {
    const toggle = $("[data-account-toggle]", menu);
    const dropdown = $("[data-account-dropdown]", menu);
    if (!toggle || !dropdown) return;
    toggle.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const willOpen = dropdown.classList.contains("hidden");
      closeAccountMenus();
      closeMobileSearch();
      if (willOpen) {
        dropdown.classList.remove("hidden");
        toggle.setAttribute("aria-expanded", "true");
      }
    });
  });
  document.addEventListener("click", (e) => {
    if (!e.target.closest("[data-account-menu]")) closeAccountMenus();
  });

  /* ---------------- Mega menu ---------------- */
  const megaBtn = $("#megaBtn"), megaMenu = $("#megaMenu");
  if (megaBtn && megaMenu) {
    megaBtn.addEventListener("click", (e) => { e.stopPropagation(); megaMenu.classList.toggle("hidden"); });
    document.addEventListener("click", (e) => { if (!megaMenu.contains(e.target) && e.target !== megaBtn) megaMenu.classList.add("hidden"); });
  }

  /* ---------------- Cart badge + drawer sync (#cartItems / #cartEmpty) ---------------- */
  function applyCart(data) {
    if (!data) return;
    const count = data.cart ? data.cart.count : 0;
    const subtotal = data.cart ? data.cart.subtotal : 0;

    $$(".cart-count").forEach((el) => {
      el.textContent = count;
      el.classList.toggle("hidden", count === 0);
      el.classList.remove("bump");
      void el.offsetWidth;
      el.classList.add("bump");
    });
    $$(".cart-count-text").forEach((el) => {
      el.textContent = count + (count === 1 ? " Item" : " Items");
    });
    $$(".cart-total").forEach((el) => (el.textContent = money(subtotal)));

    const list = $("#cartItems");
    const empty = $("#cartEmpty");
    const sub = $("#cartSubtotal");

    if (typeof data.drawer === "string" && list) {
      list.innerHTML = data.drawer.trim();
      bindDrawer();
    }

    const hasItems = count > 0;
    if (list) list.classList.toggle("hidden", !hasItems);
    if (empty) empty.classList.toggle("hidden", hasItems);
    if (sub) sub.textContent = money(subtotal);
  }

  function bindDrawer() {
    $$("[data-cart-inc]").forEach((b) => b.addEventListener("click", () => changeQty(b.dataset.key, (+b.dataset.qty || 1) + 1)));
    $$("[data-cart-dec]").forEach((b) => b.addEventListener("click", () => changeQty(b.dataset.key, (+b.dataset.qty || 1) - 1)));
    $$("[data-cart-remove]").forEach((b) => b.addEventListener("click", async () => { applyCart(await api("/cart/remove", { key: b.dataset.key })); }));
  }
  async function changeQty(key, qty) { applyCart(await api("/cart/update", { key, qty: Math.max(0, qty) })); }
  bindDrawer();

  /* ---------------- Add to cart ---------------- */
  async function addToCart(productId, qty, variant, title, openAfter, redirectUrl, skuId) {
    try {
      const payload = { product_id: productId, qty: qty || 1, variant: variant || null };
      if (skuId) payload.sku_id = skuId;
      const data = await api("/cart/add", payload);
      applyCart(data);
      if (redirectUrl) {
        window.location.href = redirectUrl;
        return true;
      }
      toast((data && data.message) || `Added “${title || "item"}” to cart`);
      if (openAfter === true) openCart();
      return true;
    } catch (err) {
      toast(err.message || "Could not add to cart. Please try again.");
      return false;
    }
  }

  function getPdpSelectedVariant() {
    const missing = [];
    const parts = [];
    $$("[data-variant-group]").forEach((group) => {
      const name = group.dataset.variantGroup;
      const sel = group.querySelector(".variant-btn.is-selected");
      if (sel) {
        parts.push(name + ": " + sel.dataset.value);
      } else {
        missing.push(name);
      }
    });
    return { variant: parts.join(", ") || null, missing };
  }

  /* ---------------- Quick Select Variation Modal Logic ---------------- */
  const qm = $("#quickSelectModal");
  const qmContainer = qm ? $("[data-modal-container]", qm) : null;
  const qmCloseBtn = $("#closeQuickModal");
  const qmImg = $("#qmProductImg");
  const qmTitle = $("#qmProductTitle");
  const qmPrice = $("#qmPrice");
  const qmRegPrice = $("#qmRegularPrice");
  const qmDiscount = $("#qmDiscountBadge");
  const qmVariantsBox = $("#qmVariantsContainer");
  const qmErrorAlert = $("#qmErrorAlert");
  const qmErrorMessage = $("#qmErrorMessage");
  const qmQtyInput = $("#qmQty");
  const qmQtyInc = $("#qmQtyInc");
  const qmQtyDec = $("#qmQtyDec");

  let currentQmProduct = null;

  function updateQuickModalVariantAvailability() {
    if (!currentQmProduct || !currentQmProduct.skus) return;

    const selectedAttrs = {};
    $$("[data-qm-variant-group]").forEach((group) => {
      const type = group.dataset.qmVariantGroup;
      const sel = group.querySelector(".qm-variant-btn.is-selected");
      if (sel) {
        selectedAttrs[type] = sel.dataset.value;
      }
    });

    $$("[data-qm-variant-group]").forEach((group) => {
      const groupType = group.dataset.qmVariantGroup;
      const buttons = group.querySelectorAll(".qm-variant-btn");

      buttons.forEach((btn) => {
        const val = btn.dataset.value;
        const testAttrs = Object.assign({}, selectedAttrs, { [groupType]: val });

        const matchingSkus = currentQmProduct.skus.filter((s) => {
          let attrs = s.attributes;
          if (typeof attrs === "string") {
            try { attrs = JSON.parse(attrs); } catch (e) { attrs = {}; }
          }
          if (!attrs || typeof attrs !== "object") return false;

          const attrMap = {};
          Object.keys(attrs).forEach((k) => {
            attrMap[String(k).trim().toLowerCase()] = String(attrs[k]).trim().toLowerCase();
          });

          return Object.keys(testAttrs).every((k) => {
            const kLower = String(k).trim().toLowerCase();
            const expectedVal = String(testAttrs[k]).trim().toLowerCase();
            if (attrMap[kLower] === undefined) return true;
            return attrMap[kLower] === expectedVal;
          });
        });

        let totalStock = 0;
        if (matchingSkus.length > 0) {
          totalStock = matchingSkus.reduce((sum, s) => sum + (parseInt(s.stock, 10) || 0), 0);
        } else if (currentQmProduct.skus.length > 0) {
          totalStock = 0;
        } else {
          totalStock = parseInt(currentQmProduct.stock, 10) || 0;
        }

        if (totalStock <= 0) {
          btn.disabled = true;
          btn.className = "qm-variant-btn relative px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-stone-200 text-stone-400 bg-stone-100 line-through opacity-40 cursor-not-allowed pointer-events-none";
          btn.title = val + " is out of stock";
          btn.textContent = val;

          if (btn.classList.contains("is-selected")) {
            btn.classList.remove("is-selected", "border-2", "border-brand-500", "text-brand-600", "bg-brand-50/40");
          }
        } else {
          btn.disabled = false;
          if (btn.classList.contains("is-selected")) {
            btn.className = "qm-variant-btn is-selected px-3.5 py-1.5 rounded-lg text-xs font-semibold border-2 border-brand-500 text-brand-600 bg-brand-50/40 transition-all cursor-pointer";
          } else {
            btn.className = "qm-variant-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-stone-200 text-stone-700 hover:border-stone-400 transition-all cursor-pointer";
          }
          btn.title = "";
          btn.textContent = val;
        }
      });
    });
  }

  function syncQuickModalPrice() {
    if (!currentQmProduct) return;

    const basePrice = parseFloat(currentQmProduct.rawPrice || 0) || (currentQmProduct.price ? parseFloat(String(currentQmProduct.price).replace(/[^0-9.]/g, "")) : 0);
    const baseRegPrice = parseFloat(currentQmProduct.rawRegularPrice || 0) || (currentQmProduct.regularPrice ? parseFloat(String(currentQmProduct.regularPrice).replace(/[^0-9.]/g, "")) : 0);

    let priceAdj = 0;
    let matchedSku = null;

    if (currentQmProduct.skus && currentQmProduct.skus.length) {
      const selectedAttrs = {};
      $$("[data-qm-variant-group]").forEach((group) => {
        const type = group.dataset.qmVariantGroup;
        const sel = group.querySelector(".qm-variant-btn.is-selected");
        if (sel) {
          selectedAttrs[type] = sel.dataset.value;
        }
      });

      const selectedKeys = Object.keys(selectedAttrs);
      if (selectedKeys.length) {
        matchedSku = currentQmProduct.skus.find((s) => {
          let attrs = s.attributes;
          if (typeof attrs === "string") {
            try { attrs = JSON.parse(attrs); } catch (e) { attrs = {}; }
          }
          if (!attrs || typeof attrs !== "object") return false;

          const attrMap = {};
          Object.keys(attrs).forEach((k) => {
            attrMap[String(k).trim().toLowerCase()] = String(attrs[k]).trim().toLowerCase();
          });
          return selectedKeys.every((k) => {
            const kLower = String(k).trim().toLowerCase();
            const expectedVal = String(selectedAttrs[k]).trim().toLowerCase();
            return attrMap[kLower] === expectedVal;
          });
        });

        if (matchedSku) {
          currentQmProduct.selectedSkuId = matchedSku.id;
          priceAdj = parseFloat(matchedSku.price_adjustment) || 0;
        } else {
          currentQmProduct.selectedSkuId = null;
        }
      }
    }

    let finalPrice = basePrice;
    if (basePrice <= 0) {
      finalPrice = Math.max(0, priceAdj);
    } else {
      finalPrice = Math.max(0, basePrice + priceAdj);
    }

    let finalRegPrice = 0;
    if (baseRegPrice > 0) {
      finalRegPrice = Math.max(0, baseRegPrice + priceAdj);
    }

    if (qmPrice && finalPrice > 0) {
      qmPrice.textContent = "৳" + finalPrice.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (qmRegPrice) {
      if (finalRegPrice > 0 && finalRegPrice > finalPrice) {
        qmRegPrice.textContent = "৳" + finalRegPrice.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        qmRegPrice.classList.remove("hidden");
      } else {
        qmRegPrice.classList.add("hidden");
      }
    }

    if (qmDiscount) {
      if (finalRegPrice > 0 && finalRegPrice > finalPrice) {
        const discountPercent = Math.round(100 - (finalPrice / finalRegPrice * 100));
        if (discountPercent > 0) {
          qmDiscount.textContent = discountPercent + "% OFF";
          qmDiscount.classList.remove("hidden");
        } else {
          qmDiscount.classList.add("hidden");
        }
      } else {
        qmDiscount.classList.add("hidden");
      }
    }
  }

  function openQuickModal(data) {
    if (!qm) return;
    currentQmProduct = data;

    if (qmImg) qmImg.src = data.image || "";
    if (qmTitle) qmTitle.textContent = data.title || "";
    if (qmPrice) qmPrice.textContent = data.price || "";

    if (qmRegPrice) {
      if (data.regularPrice) {
        qmRegPrice.textContent = data.regularPrice;
        qmRegPrice.classList.remove("hidden");
      } else {
        qmRegPrice.classList.add("hidden");
      }
    }

    if (qmDiscount) {
      if (+data.discount > 0) {
        qmDiscount.textContent = data.discount + "% OFF";
        qmDiscount.classList.remove("hidden");
      } else {
        qmDiscount.classList.add("hidden");
      }
    }

    if (qmQtyInput) qmQtyInput.value = "1";
    if (qmErrorAlert) qmErrorAlert.classList.add("hidden");

    // Render variation groups
    if (qmVariantsBox) {
      qmVariantsBox.innerHTML = "";
      const variants = data.variants || {};
      Object.keys(variants).forEach((type) => {
        const options = variants[type] || [];
        if (!options.length) return;

        const groupDiv = document.createElement("div");
        groupDiv.setAttribute("data-qm-variant-group", type);

        const label = document.createElement("p");
        label.className = "text-xs font-bold uppercase tracking-wider text-stone-600 mb-1.5";
        label.textContent = type;

        const flex = document.createElement("div");
        flex.className = "flex flex-wrap gap-2";

        options.forEach((optVal, optIdx) => {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className = "qm-variant-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-stone-200 text-stone-700 hover:border-stone-400 transition-all cursor-pointer";
          btn.setAttribute("data-value", optVal);
          btn.textContent = optVal;

          if (optIdx === 0) {
            btn.classList.add("is-selected", "border-2", "border-brand-500", "text-brand-600", "bg-brand-50/40");
            btn.classList.remove("border-stone-200", "text-stone-700");
          }

          btn.addEventListener("click", () => {
            if (btn.disabled) return;
            flex.querySelectorAll(".qm-variant-btn").forEach((b) => {
              b.classList.remove("is-selected", "border-2", "border-brand-500", "text-brand-600", "bg-brand-50/40");
              if (!b.disabled) b.classList.add("border", "border-stone-200", "text-stone-700");
            });
            btn.classList.add("is-selected", "border-2", "border-brand-500", "text-brand-600", "bg-brand-50/40");
            btn.classList.remove("border-stone-200", "text-stone-700");
            if (qmErrorAlert) qmErrorAlert.classList.add("hidden");

            updateQuickModalVariantAvailability();
            syncQuickModalPrice();
          });

          flex.appendChild(btn);
        });

        groupDiv.appendChild(label);
        groupDiv.appendChild(flex);
        qmVariantsBox.appendChild(groupDiv);
      });

      updateQuickModalVariantAvailability();
      syncQuickModalPrice();
    }

    // Show modal
    qm.classList.remove("hidden", "pointer-events-none");
    setTimeout(() => {
      qm.classList.remove("opacity-0");
      if (qmContainer) {
        qmContainer.classList.remove("scale-95");
        qmContainer.classList.add("scale-100");
      }
    }, 10);
  }

  function closeQuickModal() {
    if (!qm) return;
    qm.classList.add("opacity-0");
    if (qmContainer) {
      qmContainer.classList.remove("scale-100");
      qmContainer.classList.add("scale-95");
    }
    setTimeout(() => {
      qm.classList.add("hidden", "pointer-events-none");
      currentQmProduct = null;
    }, 300);
  }

  if (qmCloseBtn) qmCloseBtn.addEventListener("click", closeQuickModal);
  if (qm) {
    qm.addEventListener("click", (e) => {
      if (e.target === qm) closeQuickModal();
    });
  }

  const qmAddBtn = $("#qmAddToCartBtn");
  if (qmAddBtn) {
    qmAddBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      if (!currentQmProduct) return;

      const missing = [];
      const parts = [];
      $$("[data-qm-variant-group]").forEach((group) => {
        const type = group.dataset.qmVariantGroup;
        const sel = group.querySelector(".qm-variant-btn.is-selected");
        if (sel) parts.push(type + ": " + sel.dataset.value);
        else missing.push(type);
      });

      if (missing.length > 0) {
        if (qmErrorAlert && qmErrorMessage) {
          qmErrorMessage.textContent = "Please select a " + missing.join(" and ") + ".";
          qmErrorAlert.classList.remove("hidden");
        }
        return;
      }

      const qty = Math.min(3, Math.max(1, +(qmQtyInput ? qmQtyInput.value : 1) || 1));
      const variantStr = parts.join(", ") || null;
      const skuId = currentQmProduct.selectedSkuId || null;

      const ok = await addToCart(currentQmProduct.productId, qty, variantStr, currentQmProduct.title, false, null, skuId);
      if (ok) {
        closeQuickModal();
      }
    });
  }

  const qmBuyBtn = $("#qmBuyNowBtn");
  if (qmBuyBtn) {
    qmBuyBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      if (!currentQmProduct) return;

      const missing = [];
      const parts = [];
      $$("[data-qm-variant-group]").forEach((group) => {
        const type = group.dataset.qmVariantGroup;
        const sel = group.querySelector(".qm-variant-btn.is-selected");
        if (sel) parts.push(type + ": " + sel.dataset.value);
        else missing.push(type);
      });

      if (missing.length > 0) {
        if (qmErrorAlert && qmErrorMessage) {
          qmErrorMessage.textContent = "Please select a " + missing.join(" and ") + ".";
          qmErrorAlert.classList.remove("hidden");
        }
        return;
      }

      const qty = Math.min(3, Math.max(1, +(qmQtyInput ? qmQtyInput.value : 1) || 1));
      const variantStr = parts.join(", ") || null;

      const ok = await addToCart(currentQmProduct.productId, qty, variantStr, currentQmProduct.title, false, "/checkout");
      if (ok) closeQuickModal();
    });
  }

  if (qmQtyInc && qmQtyInput) {
    qmQtyInc.addEventListener("click", () => {
      qmQtyInput.value = Math.min(3, Math.max(1, (+qmQtyInput.value || 1) + 1));
    });
  }
  if (qmQtyDec && qmQtyInput) {
    qmQtyDec.addEventListener("click", () => {
      qmQtyInput.value = Math.min(3, Math.max(1, (+qmQtyInput.value || 1) - 1));
    });
  }

  /* ---------------- Card Add To Cart Click Listener ---------------- */
  $$(".add-to-cart, [data-add-cart]").forEach((btn) => btn.addEventListener("click", (e) => {
    e.preventDefault();
    const productId = btn.dataset.productId || btn.dataset.id;
    const hasVariants = btn.dataset.hasVariants === "true";
    let variantsData = null;
    let skusData = null;
    try {
      if (btn.dataset.variants) {
        variantsData = typeof btn.dataset.variants === "string" ? JSON.parse(btn.dataset.variants) : btn.dataset.variants;
      }
    } catch (err) {
      console.warn("Variants parse error:", err);
    }
    try {
      if (btn.dataset.skus) {
        skusData = typeof btn.dataset.skus === "string" ? JSON.parse(btn.dataset.skus) : btn.dataset.skus;
      }
    } catch (err) {
      console.warn("SKUs parse error:", err);
    }

    if (hasVariants && variantsData && Object.keys(variantsData).length > 0) {
      openQuickModal({
        productId: productId,
        title: btn.dataset.title,
        price: btn.dataset.price,
        rawPrice: btn.dataset.rawPrice,
        regularPrice: btn.dataset.regularPrice,
        rawRegularPrice: btn.dataset.rawRegularPrice,
        discount: btn.dataset.discount,
        image: btn.dataset.image,
        variants: variantsData,
        skus: skusData,
      });
      return;
    }

    addToCart(productId, 1, null, btn.dataset.title, false);
  }));

  const pdBtn = $("#pdAddToCart");
  if (pdBtn) {
    pdBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const { variant, missing } = getPdpSelectedVariant();
      if (missing.length > 0) {
        toast("Please select a " + missing.join(" and ") + " before adding to cart.");
        return;
      }
      const qty = Math.max(1, +($("#pdQty") ? $("#pdQty").value : 1) || 1);
      const skuId = pdBtn.dataset.skuId || null;
      addToCart(pdBtn.dataset.productId, qty, variant, pdBtn.dataset.title, false, null, skuId);
    });
  }

  const pdBuyNow = $("#pdBuyNow") || $("[data-buy-now]");
  if (pdBuyNow) {
    pdBuyNow.addEventListener("click", (e) => {
      e.preventDefault();
      if (pdBuyNow.disabled) return;
      const source = pdBtn || pdBuyNow;
      const { variant, missing } = getPdpSelectedVariant();
      if (missing.length > 0) {
        toast("Please select a " + missing.join(" and ") + " before proceeding to checkout.");
        return;
      }
      const qty = Math.max(1, +($("#pdQty") ? $("#pdQty").value : 1) || 1);
      const checkoutUrl = pdBuyNow.dataset.checkoutUrl || "/checkout";
      const skuId = source.dataset.skuId || null;
      addToCart(source.dataset.productId || pdBuyNow.dataset.productId, qty, variant, source.dataset.title || pdBuyNow.dataset.title, false, checkoutUrl, skuId);
    });
  }

  $$("[data-stepper]").forEach((wrap) => {
    const input = $("input", wrap);
    if (!input) return;

    $("[data-inc]", wrap)?.addEventListener("click", (e) => {
      e.preventDefault();
      const rawMax = parseInt(input.getAttribute("max") || "3", 10) || 3;
      const maxVal = Math.min(3, rawMax);
      const curVal = parseInt(input.value, 10) || 1;
      input.value = Math.min(maxVal, curVal + 1);
    });

    $("[data-dec]", wrap)?.addEventListener("click", (e) => {
      e.preventDefault();
      const minVal = parseInt(input.getAttribute("min") || "1", 10) || 1;
      const curVal = parseInt(input.value, 10) || 1;
      input.value = Math.max(minVal, curVal - 1);
    });
  });

  let toastTimer;
  function toast(msg) {
    let t = $("#toast") || $("#StyleHub-toast");
    if (!t) { t = document.createElement("div"); t.id = "StyleHub-toast"; t.className = "fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] bg-ink text-white text-sm px-5 py-3 rounded-xl shadow-lg opacity-0 transition-opacity duration-300"; document.body.appendChild(t); }
    t.textContent = msg;
    t.style.opacity = "1";
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { t.style.opacity = "0"; }, 2200);
  }

  const revealEls = $$(".reveal, [data-reveal]");
  if ("IntersectionObserver" in window && revealEls.length) {
    const io = new IntersectionObserver((ents) => ents.forEach((en) => { if (en.isIntersecting) { en.target.classList.add("in-view", "revealed", "is-in"); io.unobserve(en.target); } }), { threshold: 0.08, rootMargin: "0px 0px -5% 0px" });
    revealEls.forEach((el) => io.observe(el));
  } else revealEls.forEach((el) => el.classList.add("in-view", "revealed", "is-in"));

  const toTop = $("#backToTop") || $("[data-back-top]");
  if (toTop) {
    const tog = () => { const hide = window.scrollY < 400; toTop.classList.toggle("opacity-0", hide); toTop.classList.toggle("pointer-events-none", hide); };
    window.addEventListener("scroll", tog, { passive: true }); tog();
    toTop.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
  }

  const mainImg = $("#galleryMain") || $("[data-gallery-main]");
  if (mainImg) $$("[data-thumb]").forEach((t) => t.addEventListener("click", () => {
    mainImg.src = t.dataset.thumb;
    $$("[data-thumb]").forEach((x) => {
      x.classList.remove("ring-2", "ring-brand-500", "border-brand-600", "border-2");
      x.classList.add("border", "border-gray-200");
    });
    t.classList.add("border-2", "border-brand-600");
    t.classList.remove("border-gray-200");
  }));

  $$("[data-tabs]").forEach((group) => {
    $$("[data-tab]", group).forEach((tab) => tab.addEventListener("click", () => {
      const name = tab.dataset.tab;
      $$("[data-tab]", group).forEach((t) => {
        t.classList.remove("tab-active", "bg-brand-600", "text-white", "border-brand-600");
        t.classList.add("text-gray-500", "border-transparent");
      });
      tab.classList.add("tab-active");
      tab.classList.remove("text-gray-500", "border-transparent");
      const root = group;
      $$("[data-pane], [data-panel]", root).forEach((p) => {
        const key = p.dataset.pane || p.dataset.panel;
        p.classList.toggle("hidden", key !== name);
      });
    }));
  });

  $$("[data-acc-toggle]").forEach((btn) => btn.addEventListener("click", () => {
    const body = btn.nextElementSibling, icon = $("[data-acc-icon]", btn);
    const open = body.style.maxHeight && body.style.maxHeight !== "0px";
    if (open) { body.style.maxHeight = "0px"; icon && (icon.style.transform = "rotate(0deg)"); }
    else { body.style.maxHeight = body.scrollHeight + "px"; icon && (icon.style.transform = "rotate(180deg)"); }
  }));

  const range = $("#priceRange");
  if (range) {
    const label = $("#priceRangeLabel");
    const form = range.closest("form");
    const applyBtn = $("#priceApplyBtn");
    const upd = () => {
      if (label) label.textContent = "৳0 — ৳" + Number(range.value).toLocaleString("en-US");
    };
    range.addEventListener("input", upd);
    upd();

    if (applyBtn && form) {
      applyBtn.addEventListener("click", () => {
        let maxInput = form.querySelector("#priceMax");
        let minInput = form.querySelector("#priceMin");
        if (!maxInput) {
          maxInput = document.createElement("input");
          maxInput.type = "hidden";
          maxInput.name = "max";
          maxInput.id = "priceMax";
          form.appendChild(maxInput);
        }
        if (!minInput) {
          minInput = document.createElement("input");
          minInput.type = "hidden";
          minInput.name = "min";
          minInput.id = "priceMin";
          form.appendChild(minInput);
        }
        maxInput.value = range.value;
        minInput.value = "0";
        form.submit();
      });
    }
  }

  /* ---------------- Countdowns (data-countdown-end ISO datetime) ---------------- */
  $$("[data-countdown-end]").forEach((cd) => {
    const endAttr = cd.getAttribute("data-countdown-end");
    const end = endAttr ? Date.parse(endAttr) : NaN;
    if (!end || Number.isNaN(end)) return;
    const pad = (n) => String(n).padStart(2, "0");
    const tick = () => {
      let diff = Math.max(0, end - Date.now());
      const d = Math.floor(diff / 8.64e7); diff -= d * 8.64e7;
      const h = Math.floor(diff / 3.6e6); diff -= h * 3.6e6;
      const m = Math.floor(diff / 6e4); diff -= m * 6e4;
      const s = Math.floor(diff / 1e3);
      if ($("[data-d]", cd)) $("[data-d]", cd).textContent = pad(d);
      if ($("[data-h]", cd)) $("[data-h]", cd).textContent = pad(h);
      if ($("[data-m]", cd)) $("[data-m]", cd).textContent = pad(m);
      if ($("[data-s]", cd)) $("[data-s]", cd).textContent = pad(s);
    };
    tick();
    setInterval(tick, 1000);
  });
  /* ---------------- Hero slider ---------------- */
  const slider = $("#heroSlider");
  if (slider) {
    const slides = $$("[data-slide]", slider);
    const dots = $$("[data-dot]", slider).length ? $$("[data-dot]", slider) : $$("[data-dot]");
    if (slides.length) {
      let idx = 0, timer;
      const go = (n) => {
        idx = (n + slides.length) % slides.length;
        slides.forEach((s, i) => s.classList.toggle("is-hidden", i !== idx));
        dots.forEach((d, i) => {
          d.classList.toggle("is-on", i === idx);
          d.classList.toggle("bg-white", i === idx);
          d.classList.toggle("w-6", i === idx);
          d.classList.toggle("bg-white/50", i !== idx);
          d.classList.toggle("w-2", i !== idx);
        });
      };
      const next = () => go(idx + 1);
      const start = () => { timer = setInterval(next, 5000); };
      const stop = () => clearInterval(timer);
      dots.forEach((d, i) => d.addEventListener("click", () => { go(i); stop(); start(); }));
      ($("[data-hero-next]", slider) || $("[data-hero-next]"))?.addEventListener("click", () => { next(); stop(); start(); });
      ($("[data-hero-prev]", slider) || $("[data-hero-prev]"))?.addEventListener("click", () => { go(idx - 1); stop(); start(); });
      slider.addEventListener("mouseenter", stop);
      slider.addEventListener("mouseleave", start);
      go(0);
      if (slides.length > 1) start();
    }
  }

  (function imageFallback() {
    const hueFrom = (s) => { let h = 0; for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) % 360; return h; };
    const ph = (src) => {
      const hue = hueFrom(src || "x");
      const c1 = `hsl(${hue},64%,62%)`, c2 = `hsl(${(hue + 38) % 360},66%,42%)`;
      const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='600' height='600'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='${c1}'/><stop offset='1' stop-color='${c2}'/></linearGradient></defs><rect width='600' height='600' fill='url(#g)'/><g fill='none' stroke='white' stroke-opacity='.55' stroke-width='14' stroke-linecap='round' stroke-linejoin='round'><rect x='170' y='185' width='260' height='230' rx='26'/><circle cx='250' cy='258' r='30'/><path d='M180 360l92-86 70 64 46-44 32 30'/></g></svg>`;
      return "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svg);
    };
    const fix = (img) => { if (img.dataset.ph) return; img.dataset.ph = "1"; img.src = ph(img.getAttribute("src") || img.alt || ""); };
    $$("img").forEach((img) => { if (img.complete && img.naturalWidth === 0 && img.getAttribute("src")) fix(img); else img.addEventListener("error", () => fix(img)); });
  })();

  $("#year") && ($("#year").textContent = new Date().getFullYear());

  window.Storefront = { openCart, closeCart, addToCart, applyCart };
})();
