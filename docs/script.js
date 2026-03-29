"use strict";

function initTheme() {
    const stored = localStorage.getItem("theme");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    applyTheme(stored || (prefersDark ? "dark" : "light"), false);

    document.querySelectorAll("#themeToggle, #mobileThemeToggle").forEach(toggle => {
        toggle.addEventListener("click", () => {
            const current = document.documentElement.getAttribute("data-theme");
            applyTheme(current === "dark" ? "light" : "dark");
        });
    });

    window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
        if (!localStorage.getItem("theme")) {
            applyTheme(e.matches ? "dark" : "light", false);
        }
    });
}

function applyTheme(theme, save = true) {
    document.documentElement.setAttribute("data-theme", theme);
    save && localStorage.setItem("theme", theme);

    const isDark = theme === "dark";
    const label = isDark ? "Switch to light theme" : "Switch to dark theme";

    document.querySelectorAll("#themeToggle, #mobileThemeToggle").forEach(toggle => {
        toggle.setAttribute("aria-label", label);
        toggle.setAttribute("aria-pressed", String(isDark));

        const badge = toggle.querySelector(".theme-badge");
        if (badge) badge.textContent = isDark ? "Dark" : "Light";
    });
}

function initNavigation() {
    const sidebar = document.getElementById("sidebar");
    const mobileToggle = document.getElementById("mobileSidebarToggle");
    const overlay = document.getElementById("sidebarOverlay");
    const navLinks = document.querySelectorAll(".nav-link");
    const isMobileQuery = window.matchMedia("(max-width: 768px)");

    function closeSidebar() {
        const isMobile = isMobileQuery.matches;
        sidebar.classList.remove("open");
        overlay?.classList.remove("active");
        mobileToggle?.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
        if (isMobile) sidebar.setAttribute("inert", "");
        mobileToggle?.focus();
    }

    function handleMobileState() {
        const isMobile = isMobileQuery.matches;
        if (isMobile && !sidebar.classList.contains("open")) {
            sidebar.setAttribute("inert", "");
        } else {
            sidebar.removeAttribute("inert");
        }
    }

    handleMobileState();
    window.addEventListener("resize", handleMobileState, { passive: true });

    mobileToggle?.addEventListener("click", () => {
        if (sidebar.classList.contains("open")) {
            closeSidebar();
        } else {
            sidebar.classList.add("open");
            sidebar.removeAttribute("inert");
            overlay?.classList.add("active");
            mobileToggle?.setAttribute("aria-expanded", "true");
            document.body.style.overflow = "hidden";
            requestAnimationFrame(() => {
                sidebar.querySelector(".nav-link, #sidebarSearch")?.focus();
            });
        }
    });

    overlay?.addEventListener("click", closeSidebar);

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && sidebar.classList.contains("open")) {
            closeSidebar();
        }
    });

    navLinks.forEach((link) => {
        link.addEventListener("click", (e) => {
            e.preventDefault();
            const id = link.getAttribute("href")?.slice(1);
            if (!id) return;

            const target = document.getElementById(id);
            if (target) {
                navLinks.forEach(l => l.classList.remove("active"));
                link.classList.add("active");
                target.scrollIntoView({ behavior: "smooth", block: "start" });
                if (isMobileQuery.matches) closeSidebar();
                history.pushState(null, "", `#${id}`);
            }
        });
    });

    const hash = window.location.hash.slice(1);
    if (hash) {
        const target = document.getElementById(hash);
        if (target) {
            setTimeout(() => {
                target.scrollIntoView({ behavior: "auto", block: "start" });
            }, 150);
            document.querySelector(`.nav-link[href="#${hash}"]`)?.classList.add("active");
        }
    }

    initScrollSpy();
}

function initScrollSpy() {
    const sections = document.querySelectorAll(".content-section");
    const navLinks = document.querySelectorAll(".nav-link");

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const id = entry.target.id;
            navLinks.forEach(link => {
                link.classList.toggle("active", link.getAttribute("href") === `#${id}`);
            });
        });
    }, { rootMargin: "-15% 0px -80% 0px", threshold: 0 });

    sections.forEach(section => observer.observe(section));
}

const COPY_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
const CHECK_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';

function initCodeBlocks() {
    document.querySelectorAll("pre").forEach(pre => {
        pre.removeAttribute("style");
        pre.querySelectorAll("[style]").forEach(el => el.removeAttribute("style"));

        const wrapper = pre.closest(".code-block-wrapper");
        if (wrapper) {
            const btn = wrapper.querySelector(".copy-btn");
            if (btn && !btn.dataset.wired) wireCopyBtn(btn, pre);
            return;
        }

        const newWrapper = document.createElement("div");
        newWrapper.className = "code-block-wrapper";
        pre.parentNode.insertBefore(newWrapper, pre);
        newWrapper.appendChild(pre);

        const lang = (pre.querySelector("code")?.className || "")
            .match(/language-(\w+)/)?.[1]?.toUpperCase() || "CODE";

        const header = document.createElement("div");
        header.className = "code-header";

        const langSpan = document.createElement("span");
        langSpan.className = "code-lang";
        langSpan.textContent = lang;

        const copyBtn = document.createElement("button");
        copyBtn.className = "copy-btn";
        copyBtn.setAttribute("type", "button");
        copyBtn.setAttribute("aria-label", "Copy code");
        copyBtn.innerHTML = `${COPY_ICON} Copy`;

        header.appendChild(langSpan);
        header.appendChild(copyBtn);
        newWrapper.insertBefore(header, pre);
        wireCopyBtn(copyBtn, pre);
    });

    document.querySelectorAll(".copy-btn[data-copy-target]").forEach(btn => {
        if (btn.dataset.wired) return;
        const target = document.getElementById(btn.dataset.copyTarget);
        target && wireCopyBtn(btn, target.closest("pre") || target);
    });
}

function wireCopyBtn(btn, pre) {
    btn.dataset.wired = "true";
    btn.addEventListener("click", async () => {
        const code = pre.querySelector("code") || pre;
        const text = code.textContent || "";

        try {
            await navigator.clipboard.writeText(text);
        } catch {
            const range = document.createRange();
            range.selectNodeContents(code);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            document.execCommand("copy");
            sel.removeAllRanges();
        }

        btn.innerHTML = `${CHECK_ICON} Copied!`;
        btn.classList.add("copied");
        btn.setAttribute("aria-label", "Copied!");

        clearTimeout(btn._copyTimer);
        btn._copyTimer = setTimeout(() => {
            btn.innerHTML = `${COPY_ICON} Copy`;
            btn.classList.remove("copied");
            btn.setAttribute("aria-label", "Copy code");
        }, 2200);
    });
}

function initSidebarSearch() {
    const search = document.getElementById("sidebarSearch");
    if (!search) return;

    const sections = document.querySelectorAll(".nav-section");

    search.addEventListener("input", () => {
        const query = search.value.trim().toLowerCase();
        sections.forEach(section => {
            let hasVisible = false;
            section.querySelectorAll(".nav-list li").forEach(li => {
                const text = li.querySelector(".nav-link")?.textContent.toLowerCase() || "";
                const matches = !query || text.includes(query);
                li.style.display = matches ? "" : "none";
                if (matches) hasVisible = true;
            });
            section.style.display = hasVisible ? "" : "none";
        });
    });

    search.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            search.value = "";
            search.dispatchEvent(new Event("input"));
            search.blur();
        }
    });
}

function handleResponsiveTables() {
    document.querySelectorAll(".data-table").forEach(table => {
        if (table.closest(".table-scroll")) return;

        const wrapper = document.createElement("div");
        wrapper.className = "table-scroll";
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initTheme();
    initNavigation();
    initCodeBlocks();
    initSidebarSearch();
    handleResponsiveTables();
});
