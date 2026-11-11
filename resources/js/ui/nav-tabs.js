const TABBAR_SELECTOR = '#app-tabbar';

function normalizePath(value, base = window.location.origin) {
    try {
        const url = new URL(value, base);
        let path = url.pathname || '/';

        if (path.length > 1) {
            path = path.replace(/\/+$/, '');
        }

        return path || '/';
    } catch (error) {
        return value || '/';
    }
}

function absoluteUrl(path) {
    try {
        return new URL(path, window.location.origin).href;
    } catch (error) {
        return path;
    }
}

function readJson(storage, key, fallback) {
    try {
        const raw = storage.getItem(key);
        return raw ? JSON.parse(raw) : fallback;
    } catch (error) {
        return fallback;
    }
}

function writeJson(storage, key, value) {
    try {
        storage.setItem(key, JSON.stringify(value));
    } catch (error) {
        // Storage bisa diblokir oleh browser; tab tetap jalan tanpa persist.
    }
}

function isValidTab(tab) {
    return tab && typeof tab === 'object' && typeof tab.path === 'string' && tab.path.startsWith('/');
}

function buildTabs(raw, homePath) {
    const seen = new Set([homePath]);
    const tabs = [];

    (Array.isArray(raw) ? raw : []).forEach((tab) => {
        if (!isValidTab(tab)) {
            return;
        }

        const path = normalizePath(tab.path);

        // Jangan simpan / buat tab khusus catat opname, tetap gunakan tab Stock Opname
        if (path === '/warehouse/opname/catat' || path.startsWith('/warehouse/opname/catat') || path.includes('/opname/catat')) {
            return;
        }

        if (seen.has(path)) {
            return;
        }

        seen.add(path);
        tabs.push({
            path,
            label: String(tab.label || 'Halaman'),
            icon: String(tab.icon || 'fa-file-lines'),
            color: String(tab.color || '#475569'),
            matched: Boolean(tab.matched),
            pinned: false,
        });
    });

    return [
        {
            path: homePath,
            label: 'Dashboard',
            icon: 'fa-th-large',
            color: '#2563eb',
            matched: true,
            pinned: true,
        },
        ...tabs,
    ];
}

function mergeCurrent(current, existing) {
    const keepExistingVisual = Boolean(existing && !current.matched);

    return {
        path: normalizePath(current.path),
        label: current.label || (existing && existing.label) || 'Halaman',
        icon: keepExistingVisual ? existing.icon : (current.icon || 'fa-file-lines'),
        color: keepExistingVisual ? existing.color : (current.color || '#475569'),
        matched: Boolean(current.matched),
        pinned: false,
    };
}

function capTabs(tabs, max, activePath) {
    const limit = Math.max(1, Number(max) || 10) - 1;
    const home = tabs[0];
    const rest = tabs.slice(1);
    const kept = rest.slice(-limit);

    if (activePath && !kept.some((tab) => tab.path === activePath)) {
        const activeTab = rest.find((tab) => tab.path === activePath);

        if (activeTab) {
            if (kept.length >= limit) {
                kept.shift();
            }

            kept.push(activeTab);
        }
    }

    return [home, ...kept];
}

function bindSidebarLinks(keys) {
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const target = event.target instanceof Element ? event.target : null;
        const link = target ? target.closest('a[data-tab-link]') : null;

        if (!link || link.dataset.disabled === 'true') {
            return;
        }

        const href = link.getAttribute('href');

        if (!href || href === '#' || href.startsWith('javascript:')) {
            return;
        }

        let url;

        try {
            url = new URL(href, window.location.href);
        } catch (error) {
            return;
        }

        if (url.origin !== window.location.origin) {
            return;
        }

        const path = normalizePath(url.pathname);
        const label = link.dataset.tabLabel || link.querySelector('span')?.textContent?.trim() || 'Halaman';
        const icon = link.dataset.tabIcon || 'fa-file-lines';
        const color = link.dataset.tabColor || '#475569';

        if (typeof window.__APP_OPEN_TAB__ === 'function') {
            event.preventDefault();
            event.stopPropagation();

            const closeBtn = document.querySelector('.sidebar-submenu-close');
            if (closeBtn) {
                closeBtn.click();
            } else {
                const submenu = document.getElementById('sidebar-submenu');
                if (submenu) submenu.classList.remove('is-open');
                const backdrop = document.getElementById('sidebar-submenu-backdrop');
                if (backdrop) backdrop.classList.add('hidden');
            }

            window.__APP_OPEN_TAB__({ path, label, icon, color });
        }
    });
}

function syncTopbarHeight() {
    const topbar = document.getElementById('app-topbar');

    if (!topbar) {
        return;
    }

    const apply = () => {
        const height = Math.ceil(topbar.getBoundingClientRect().height);
        document.documentElement.style.setProperty('--app-topbar-h', `${height}px`);
    };

    apply();

    if ('ResizeObserver' in window) {
        new ResizeObserver(apply).observe(topbar);
    }

    window.addEventListener('resize', apply);
    window.addEventListener('load', apply);
}

function initTabbar() {
    if (window.self !== window.top) {
        return;
    }

    const tabbar = document.querySelector(TABBAR_SELECTOR);

    if (!tabbar) {
        return;
    }

    const config = window.__APP_TABS_CONFIG__ || {};

    if (!config.storageKey || !config.current) {
        return;
    }

    const scroll = tabbar.querySelector('.ui-navtabs__scroll');
    const clearButton = tabbar.querySelector('#app-tabbar-clear');

    if (!scroll || !clearButton) {
        return;
    }

    const homePath = normalizePath(config.homePath || config.homeUrl || '/dashboard');
    const current = config.current;
    let currentPath = normalizePath(current.path || window.location.pathname);
    const initialPath = currentPath;
    const maxTabs = Number(config.max) || 10;

    const mainContainer = document.getElementById('app-main') || document.querySelector('#app-shell > main');
    const initialPane = document.getElementById('tab-pane-initial');
    let initialScrollY = 0;

    let tabs = buildTabs(readJson(localStorage, config.storageKey, []), homePath);

    const persist = (nextTabs, activePath) => {
        tabs = nextTabs;
        writeJson(localStorage, config.storageKey, tabs);

        try {
            sessionStorage.setItem(config.activeKey, JSON.stringify(activePath));
        } catch (error) {}
    };

    let loaderTimer = null;

    const showTabLoader = (text) => {
        const loader = document.getElementById('app-tab-loader');
        if (!loader) return;
        const textEl = document.getElementById('app-tab-loader-text');
        if (textEl && text) {
            textEl.textContent = text;
        }
        loader.classList.remove('hidden');
        if (loaderTimer) clearTimeout(loaderTimer);
        loaderTimer = setTimeout(() => {
            hideTabLoader();
        }, 8000);
    };

    const hideTabLoader = () => {
        if (loaderTimer) {
            clearTimeout(loaderTimer);
            loaderTimer = null;
        }
        const loader = document.getElementById('app-tab-loader');
        if (loader) {
            loader.classList.add('hidden');
        }
    };

    const getOrCreatePane = (tab) => {
        const path = normalizePath(tab.path);
        if (!mainContainer) return null;

        let pane = mainContainer.querySelector(`.ui-tab-pane[data-tab-pane-path="${path}"]`);
        if (pane) {
            if (pane.tagName === 'IFRAME' && pane.dataset.loaded !== 'true') {
                showTabLoader('Memuat ' + (tab.label || 'halaman') + '...');
            } else {
                hideTabLoader();
            }
            return pane;
        }

        if (path === initialPath && initialPane) {
            initialPane.dataset.tabPanePath = path;
            hideTabLoader();
            return initialPane;
        }

        showTabLoader('Memuat ' + (tab.label || 'halaman') + '...');

        const iframe = document.createElement('iframe');
        iframe.className = 'ui-tab-pane ui-tab-frame';
        iframe.dataset.tabPanePath = path;
        iframe.dataset.loaded = 'false';
        iframe.setAttribute('title', tab.label || 'Tab');
        iframe.style.display = 'none';

        const embedUrl = new URL(absoluteUrl(path));
        embedUrl.searchParams.set('tab_embed', '1');
        iframe.src = embedUrl.href;

        const onDoneLoading = () => {
            iframe.dataset.loaded = 'true';
            if (currentPath === path) {
                hideTabLoader();
            }
        };

        iframe.addEventListener('load', () => {
            onDoneLoading();
            try {
                const fw = iframe.contentWindow;
                if (!fw) return;
                if (fw.location.pathname.includes('/login')) {
                    window.top.location.assign('/login');
                    return;
                }
                const newPath = normalizePath(fw.location.pathname);
                const oldPath = iframe.dataset.tabPanePath;
                if (newPath && newPath !== oldPath && newPath !== '/') {
                    // Jangan ubah tab Stock Opname menjadi tab Catat Opname
                    if (newPath.includes('/opname/catat')) {
                        return;
                    }
                    iframe.dataset.tabPanePath = newPath;
                    const tabIdx = tabs.findIndex((t) => t.path === oldPath);
                    if (tabIdx !== -1) {
                        tabs[tabIdx].path = newPath;
                        let pageTitle = fw.document.title ? fw.document.title.replace(/\s*-\s*PT RAKHA NUSANTARA MEDIKA.*/i, '').trim() : '';
                        if (pageTitle && !oldPath.includes('/opname')) {
                            tabs[tabIdx].label = pageTitle;
                        }
                        persist(tabs, currentPath === oldPath ? newPath : currentPath);
                        render();
                    }
                    if (currentPath === oldPath) {
                        currentPath = newPath;
                        try {
                            history.replaceState(null, '', newPath);
                        } catch (e) {}
                    }
                }
            } catch (err) {}
        });

        iframe.addEventListener('error', () => {
            onDoneLoading();
        });

        mainContainer.appendChild(iframe);
        return iframe;
    };

    const dismissAllActiveModals = () => {
        // Tutup modal generik x-ui.modal (misal: Detail Opname, Panduan Opname, Scrap di Stock Opname)
        document.querySelectorAll('[data-ui-modal]:not(.hidden)').forEach((m) => {
            const b = m.querySelector('[data-ui-modal-backdrop]');
            const p = m.querySelector('[data-ui-modal-panel]');
            if (b) b.classList.add('opacity-0');
            if (p) p.classList.add('opacity-0', 'scale-95');
            m.classList.add('hidden');
            m.classList.remove('flex');
        });

        // Tutup modal berbasis modal-content-canvas (Warehouse Ops, Data Product, Kartu Stok, dll.)
        document.querySelectorAll('.modal-content-canvas:not(.hidden)').forEach((m) => {
            m.classList.add('hidden');
            m.classList.remove('flex');
        });

        // Panggil handler penutup spesifik jika tersedia
        if (typeof window.closeModal === 'function') {
            try { window.closeModal(); } catch (e) {}
        }
        if (typeof window.closeProductModal === 'function') {
            try { window.closeProductModal(); } catch (e) {}
        }
        if (typeof window.uiModalClose === 'function') {
            document.querySelectorAll('[data-ui-modal]').forEach((m) => {
                try { if (m.id) window.uiModalClose(m.id); } catch (e) {}
            });
        }

        // Pastikan modal di dalam tab iframe juga ditutup
        if (mainContainer) {
            mainContainer.querySelectorAll('iframe.ui-tab-frame').forEach((iframe) => {
                try {
                    const fw = iframe.contentWindow;
                    if (fw && fw.document) {
                        fw.document.querySelectorAll('[data-ui-modal]:not(.hidden), .modal-content-canvas:not(.hidden)').forEach((m) => {
                            m.classList.add('hidden');
                            m.classList.remove('flex');
                        });
                        if (typeof fw.closeModal === 'function') {
                            try { fw.closeModal(); } catch (e) {}
                        }
                        if (typeof fw.closeProductModal === 'function') {
                            try { fw.closeProductModal(); } catch (e) {}
                        }
                        fw.document.body.style.overflow = '';
                    }
                } catch (e) {}
            });
        }

        // Kembalikan scroll body dokumen utama
        document.body.style.overflow = '';
    };

    const switchToTab = (targetPath, tabMeta = null) => {
        dismissAllActiveModals();
        targetPath = normalizePath(targetPath);

        let targetTab = tabs.find((t) => t.path === targetPath);
        if (!targetTab) {
            targetTab = {
                path: targetPath,
                label: (tabMeta && tabMeta.label) || 'Halaman',
                icon: (tabMeta && tabMeta.icon) || 'fa-file-lines',
                color: (tabMeta && tabMeta.color) || '#475569',
                matched: true,
                pinned: targetPath === homePath,
            };
            tabs.push(targetTab);
        } else if (tabMeta) {
            if (tabMeta.label && (targetTab.label === 'Halaman' || !targetTab.label)) targetTab.label = tabMeta.label;
            if (tabMeta.icon && targetTab.icon === 'fa-file-lines') targetTab.icon = tabMeta.icon;
            if (tabMeta.color) targetTab.color = tabMeta.color;
        }

        if (currentPath === initialPath && initialPane) {
            initialScrollY = window.scrollY;
        }

        if (mainContainer) {
            mainContainer.querySelectorAll('.ui-tab-pane').forEach((p) => {
                p.style.display = 'none';
            });
        }

        const targetPane = getOrCreatePane(targetTab);
        if (targetPane) {
            if (targetPane === initialPane) {
                targetPane.style.display = 'flex';
                hideTabLoader();
            } else {
                targetPane.style.display = 'block';
                if (targetPane.dataset && targetPane.dataset.loaded === 'true') {
                    hideTabLoader();
                }
            }
        }

        if (targetPath === initialPath) {
            window.scrollTo(0, initialScrollY);
        } else {
            window.scrollTo(0, 0);
        }

        currentPath = targetPath;
        persist(capTabs(tabs, maxTabs, currentPath), currentPath);

        try {
            if (window.location.pathname !== targetPath) {
                history.pushState(null, '', targetPath);
            }
        } catch (e) {}

        if (targetTab.label && targetTab.label !== 'Halaman') {
            document.title = `${targetTab.label} - PT RAKHA NUSANTARA MEDIKA`;
        }

        render();
    };

    window.__APP_OPEN_TAB__ = (meta) => {
        switchToTab(meta.path, meta);
    };

    const closeTab = (path) => {
        path = normalizePath(path);
        const index = tabs.findIndex((tab) => tab.path === path);

        if (index === -1 || tabs[index].pinned) {
            return;
        }

        const wasActive = path === currentPath;
        const nextTab = tabs[index - 1] || tabs[0];
        const nextTabs = tabs.filter((tab) => tab.path !== path);

        if (mainContainer) {
            const pane = mainContainer.querySelector(`.ui-tab-pane[data-tab-pane-path="${path}"]`);
            if (pane) {
                pane.remove();
            }
        }

        persist(capTabs(nextTabs, maxTabs, wasActive ? nextTab.path : currentPath), wasActive ? nextTab.path : currentPath);

        if (wasActive) {
            switchToTab(nextTab.path);
            return;
        }

        render();
    };

    const render = () => {
        scroll.textContent = '';

        tabs.forEach((tab) => {
            const isActive = tab.path === currentPath;
            const link = document.createElement('a');

            link.href = absoluteUrl(tab.path);
            link.className = 'ui-navtab';
            link.dataset.tabPath = tab.path;
            link.title = tab.label;
            link.setAttribute('role', 'tab');
            link.setAttribute('aria-selected', isActive ? 'true' : 'false');

            if (isActive) {
                link.classList.add('is-active');
            }

            if (tab.pinned) {
                link.classList.add('ui-navtab--pinned');
            }

            const icon = document.createElement('i');
            icon.className = `fas ${tab.icon} ui-navtab__icon`;
            icon.style.color = tab.color;
            icon.setAttribute('aria-hidden', 'true');

            const label = document.createElement('span');
            label.className = 'ui-navtab__label';
            label.textContent = tab.label;

            link.append(icon, label);

            link.addEventListener('click', (event) => {
                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }
                event.preventDefault();
                switchToTab(tab.path);
            });

            if (!tab.pinned) {
                const close = document.createElement('button');
                close.type = 'button';
                close.className = 'ui-navtab__close';
                close.setAttribute('aria-label', `Tutup tab ${tab.label}`);
                close.innerHTML = '<i class="fas fa-xmark" aria-hidden="true"></i>';
                close.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    closeTab(tab.path);
                });
                link.appendChild(close);
            }

            scroll.appendChild(link);
        });

        clearButton.hidden = tabs.length <= 1;

        const activeElement = Array.from(scroll.querySelectorAll('.ui-navtab'))
            .find((element) => element.dataset.tabPath === currentPath);

        if (activeElement && scroll.scrollWidth > scroll.clientWidth) {
            const centered = activeElement.offsetLeft - ((scroll.clientWidth - activeElement.offsetWidth) / 2);
            scroll.scrollLeft = Math.max(0, centered);
        }
    };

    let currentIndex = tabs.findIndex((tab) => tab.path === currentPath);

    if (currentIndex === -1) {
        tabs.push(mergeCurrent(current, null));
    } else if (!tabs[currentIndex].pinned) {
        tabs[currentIndex] = mergeCurrent(current, tabs[currentIndex]);
    }

    persist(capTabs(tabs, maxTabs, currentPath), currentPath);
    render();

    clearButton.addEventListener('click', () => {
        if (mainContainer) {
            mainContainer.querySelectorAll('.ui-tab-pane').forEach((p) => {
                if (p.dataset.tabPanePath !== homePath) {
                    p.remove();
                }
            });
        }

        const homeOnly = buildTabs([], homePath);
        persist(homeOnly, homePath);
        switchToTab(homePath);
        render();
    });

    scroll.addEventListener('keydown', (event) => {
        const tabLinks = Array.from(scroll.querySelectorAll('.ui-navtab'));
        const focusedIndex = tabLinks.indexOf(document.activeElement);

        if (focusedIndex === -1) {
            return;
        }

        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
            event.preventDefault();
            const direction = event.key === 'ArrowRight' ? 1 : -1;
            const nextIndex = (focusedIndex + direction + tabLinks.length) % tabLinks.length;
            tabLinks[nextIndex].focus();
            return;
        }

        if (event.key === 'Delete' || event.key === 'Backspace') {
            event.preventDefault();
            const close = tabLinks[focusedIndex].querySelector('.ui-navtab__close');

            if (close) {
                close.click();
            }
        }
    });

    window.addEventListener('popstate', () => {
        const path = normalizePath(window.location.pathname);
        if (tabs.some((t) => t.path === path)) {
            switchToTab(path);
        }
    });

    window.addEventListener('message', (event) => {
        if (event.data && event.data.type === 'APP_OPEN_TAB') {
            try {
                const u = new URL(event.data.url, window.location.origin);
                switchToTab(normalizePath(u.pathname), {
                    label: event.data.label,
                    icon: event.data.icon,
                    color: event.data.color,
                });
            } catch (e) {}
        }
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== config.storageKey) {
            return;
        }

        tabs = buildTabs(readJson(localStorage, config.storageKey, []), homePath);
        render();
    });
}

function boot() {
    if (window.self !== window.top) {
        document.documentElement.classList.add('is-tab-frame');
        document.addEventListener('click', (event) => {
            const link = event.target instanceof Element ? event.target.closest('a[data-tab-link]') : null;
            if (link && !link.dataset.disabled) {
                event.preventDefault();
                const href = link.getAttribute('href');
                window.parent.postMessage({
                    type: 'APP_OPEN_TAB',
                    url: href,
                    label: link.dataset.tabLabel || link.querySelector('span')?.textContent?.trim() || 'Halaman',
                    icon: link.dataset.tabIcon,
                    color: link.dataset.tabColor,
                }, '*');
            }
        });
        return;
    }

    bindSidebarLinks(window.__APP_TABS_CONFIG__ || window.__APP_TAB_KEYS__ || {});
    syncTopbarHeight();
    initTabbar();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
