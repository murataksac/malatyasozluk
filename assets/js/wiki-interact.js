/**
 * Malatya Sozluk - Interaktif Kontrol Paneli
 * Version: 1.6.0
 */

// =========================================================================
// 1. GLOBAL SEKME SISTEMI (Madde / Tartisma / Giris-Kayit)
// =========================================================================
function wikiSekmeAc(evt, sekmeId) {
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
    document.querySelectorAll('.wiki-sekme-alani').forEach(el => {
        el.style.display = "none";
        el.classList.remove('aktif-sekme');
    });
    document.querySelectorAll('.wiki-rail-btn, .wiki-sekme-buton').forEach(btn => btn.classList.remove('aktif'));

    const hedef = document.getElementById(sekmeId);
    if (hedef) {
        hedef.style.display = "block";
        hedef.classList.add('aktif-sekme');
    }
    if (evt && evt.currentTarget) evt.currentTarget.classList.add('aktif');
}

// =========================================================================
// 2. GLOBAL NAVBOX (SABLON) SISTEMI - Goster / Gizle
// =========================================================================
function wikiNavboxAc(el, evt) {
    if (evt) evt.stopPropagation();
    const content = el.nextElementSibling;
    const icon = el.querySelector('.toggle-icon');
    if (content && content.classList.contains('navbox-content')) {
        const isHidden = (content.style.display === 'none' || content.style.display === '');
        content.style.display = isHidden ? 'block' : 'none';
        if (icon) icon.textContent = isHidden ? '\u25b2 Gizle' : '\u25bc Goster';
    }
}

// =========================================================================
// 3. DARK MODE TOGGLE (Gece / Gunduz)
// =========================================================================
function wikiToggleDarkMode() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('malatya_dark_mode', isDark ? 'dark' : 'light');
    const btn = document.querySelector('.wiki-dark-mode-toggle');
    if (btn) btn.title = isDark ? 'Gunduz Moduna Gec' : 'Gece Moduna Gec';
}

// =========================================================================
// 4. HIZLI DUZENLEME MODAL
// =========================================================================
function getWikiEditorIcerik() {
    const editorId = 'wikihizlieditor';
    if (typeof tinyMCE !== 'undefined') {
        tinyMCE.triggerSave();
        const editor = tinyMCE.get(editorId);
        if (editor && !editor.isHidden()) {
            return editor.getContent();
        }
    }
    const el = document.getElementById(editorId);
    return el ? el.value : '';
}

function wikiHizliDuzenleAc() {
    const modal = document.getElementById("wiki-quick-edit-modal");
    if (!modal) return;
    modal.classList.add("active");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";

    setTimeout(() => {
        if (typeof tinyMCE !== 'undefined') {
            const ed = tinyMCE.get('wikihizlieditor');
            if (ed && !ed.isHidden()) { ed.focus(); return; }
        }
        const textarea = document.getElementById("wikihizlieditor");
        if (textarea) textarea.focus();
    }, 150);
}

function wikiHizliDuzenleKapat() {
    const modal = document.getElementById("wiki-quick-edit-modal");
    if (!modal) return;
    modal.classList.remove("active");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    const alertBox = document.getElementById("wiki-quick-edit-alert");
    if (alertBox) alertBox.style.display = "none";
}

function wikiHizliOnizleme() {
    const previewBox = document.getElementById("wiki-quick-preview-box");
    const previewContent = document.getElementById("wiki-preview-content");
    const toggleBtn = document.getElementById("wiki-btn-toggle-preview");

    if (!previewBox || !previewContent) return;

    if (previewBox.style.display !== "none") {
        previewBox.style.display = "none";
        if (toggleBtn) toggleBtn.innerHTML = '<span class="btn-icon">\ud83d\udc41\ufe0f</span> <span class="btn-text">Onizleme Goster</span>';
        return;
    }

    const content = getWikiEditorIcerik();
    previewBox.style.display = "block";
    previewContent.innerHTML = '<div style="color:#64748b; font-style:italic; padding:20px; text-align:center;">Onizleme hazirlaniyor...</div>';
    if (toggleBtn) toggleBtn.innerHTML = '<span class="btn-icon">\u25b2</span> <span class="btn-text">Onizlemeyi Gizle</span>';

    const ajaxUrl = (typeof malatya_ajax !== 'undefined' && malatya_ajax.ajax_url) ? malatya_ajax.ajax_url : '/wp-admin/admin-ajax.php';
    const nonceVal = (typeof malatya_ajax !== 'undefined' && malatya_ajax.nonce) ? malatya_ajax.nonce : '';

    const formData = new FormData();
    formData.append('action', 'malatya_hizli_onizleme');
    formData.append('content', content);
    formData.append('nonce', nonceVal);

    fetch(ajaxUrl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data && data.data.html) {
                previewContent.innerHTML = data.data.html;
            } else {
                previewContent.innerHTML = '<div style="color:#dc2626; padding:15px;">Onizleme olusturulamadi.</div>';
            }
        })
        .catch(err => {
            previewContent.innerHTML = '<div style="color:#dc2626; padding:15px;">Baglanti hatasi: ' + err.message + '</div>';
        });
}

function wikiHizliKaydet(postId) {
    const summaryInput = document.getElementById("wiki-edit-summary");
    const alertBox = document.getElementById("wiki-quick-edit-alert");
    const saveBtn = document.getElementById("wiki-btn-save-edit");

    const content = getWikiEditorIcerik().trim();
    if (content === "") {
        alert("Lutfen madde icerigini bos birakmayın.");
        return;
    }

    const summary = summaryInput ? summaryInput.value.trim() : "";
    const ajaxUrl = (typeof malatya_ajax !== 'undefined' && malatya_ajax.ajax_url) ? malatya_ajax.ajax_url : '/wp-admin/admin-ajax.php';
    const nonceVal = (typeof malatya_ajax !== 'undefined' && malatya_ajax.nonce) ? malatya_ajax.nonce : '';

    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="btn-icon">\u23f3</span> <span class="btn-text">Kaydediliyor...</span>';
    }
    if (alertBox) alertBox.style.display = "none";

    const formData = new FormData();
    formData.append('action', 'malatya_hizli_duzenle');
    formData.append('post_id', postId);
    formData.append('content', content);
    formData.append('summary', summary);
    formData.append('nonce', nonceVal);

    fetch(ajaxUrl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (alertBox) {
                    alertBox.className = "wiki-alert-box wiki-alert-success";
                    alertBox.innerHTML = '<strong>\u2714 Basarili:</strong> ' + (data.data.message || 'Degisiklikler kaydedildi.');
                    alertBox.style.display = "block";
                }
                if (data.data && data.data.direct) {
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<span class="btn-icon">\u2714</span> <span class="btn-text">Onaya Gonderildi</span>';
                    }
                    setTimeout(() => { wikiHizliDuzenleKapat(); }, 2800);
                }
            } else {
                if (alertBox) {
                    alertBox.className = "wiki-alert-box wiki-alert-error";
                    alertBox.innerHTML = '<strong>Hata:</strong> ' + (data.data.message || 'Degisiklik kaydedilemedi.');
                    alertBox.style.display = "block";
                }
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<span class="btn-icon">\ud83d\udcbe</span> <span class="btn-text">Tekrar Dene</span>';
                }
            }
        })
        .catch(err => {
            if (alertBox) {
                alertBox.className = "wiki-alert-box wiki-alert-error";
                alertBox.innerHTML = '<strong>Baglanti Hatasi:</strong> ' + err.message;
                alertBox.style.display = "block";
            }
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<span class="btn-icon">\ud83d\udcbe</span> <span class="btn-text">Tekrar Dene</span>';
            }
        });
}

// =========================================================================
// 5. PANO KOPYALAMA YARDIMCISI
// =========================================================================
function malatyaBaglantiKopyala(metin, btn) {
    if (!metin) return;

    function basariliGeriBildirim() {
        if (!btn) return;
        const eskiIcerik = btn.innerHTML;
        btn.classList.add('kopyalandi');
        btn.innerHTML = '<span class="paylas-simge">\u2714</span> <span>Kopyalandi!</span>';
        setTimeout(function() {
            btn.classList.remove('kopyalandi');
            btn.innerHTML = eskiIcerik;
        }, 2200);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(metin).then(basariliGeriBildirim).catch(function() {
            fallbackKopyala(metin);
            basariliGeriBildirim();
        });
    } else {
        fallbackKopyala(metin);
        basariliGeriBildirim();
    }
}

function fallbackKopyala(metin) {
    const tempInput = document.createElement('textarea');
    tempInput.value = metin;
    tempInput.style.position = 'fixed';
    tempInput.style.left = '-9999px';
    tempInput.style.top = '-9999px';
    document.body.appendChild(tempInput);
    tempInput.focus();
    tempInput.select();
    try { document.execCommand('copy'); } catch (err) { console.error('Kopyalama hatasi:', err); }
    document.body.removeChild(tempInput);
}

// =========================================================================
// 6. KAYNAKCA VURGULAMA (CITATION FLASH)
// =========================================================================
function highlightWikiElement(hedefEl) {
    if (!hedefEl) return;

    document.querySelectorAll('.wiki-ref-highlight').forEach(function(el) {
        el.classList.remove('wiki-ref-highlight');
    });

    const maddeSekmesi = document.getElementById('madde-icerigi');
    if (maddeSekmesi && maddeSekmesi.style.display === 'none') {
        if (typeof wikiSekmeAc === 'function') {
            wikiSekmeAc(null, 'madde-icerigi');
        } else {
            maddeSekmesi.style.display = 'block';
        }
    }

    hedefEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    void hedefEl.offsetWidth;
    hedefEl.classList.add('wiki-ref-highlight');

    setTimeout(function() {
        hedefEl.classList.remove('wiki-ref-highlight');
    }, 3000);
}

// =========================================================================
// DOMContentLoaded - TUM OLAY DINLEYICILER
// =========================================================================
document.addEventListener("DOMContentLoaded", function () {

    // --- A) DARK MODE: Sayfa yuklenince kaydedilen tercihi oku ---
    const savedMode = localStorage.getItem('malatya_dark_mode');
    if (savedMode === 'dark' || (!savedMode && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.body.classList.add('dark-mode');
    }

    // --- B) LIGHTBOX (Resim Buyutme) ---
    // HTML ekle
    if (!document.getElementById('wiki-image-lightbox')) {
        document.body.insertAdjacentHTML('beforeend', `
        <div id="wiki-image-lightbox" class="wiki-lightbox" role="dialog" aria-modal="true">
            <span class="wiki-lightbox-close" title="Kapat">&times;</span>
            <img class="wiki-lightbox-content" id="wiki-lightbox-img" alt="">
        </div>`);
    }

    const lightbox    = document.getElementById('wiki-image-lightbox');
    const lightboxImg = document.getElementById('wiki-lightbox-img');
    const lbClose     = document.querySelector('.wiki-lightbox-close');

    // Madde ici resimlere tıklayınca ac
    document.querySelectorAll('.entry-content img').forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', function (e) {
            if (this.parentElement.tagName === 'A') e.preventDefault();
            lightboxImg.src = this.src;
            lightboxImg.alt = this.alt || '';
            lightbox.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });

    if (lbClose) {
        lbClose.addEventListener('click', () => {
            lightbox.style.display = 'none';
            document.body.style.overflow = '';
        });
    }
    if (lightbox) {
        lightbox.addEventListener('click', e => {
            if (e.target !== lightboxImg) {
                lightbox.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && lightbox && lightbox.style.display === 'block') {
            lightbox.style.display = 'none';
            document.body.style.overflow = '';
        }
    });

    // --- C) MOBIL YAN MENU ---
    const menuBtn = document.getElementById("mobile-menu-toggle");
    const sidebar = document.querySelector(".site-sidebar");
    const overlay = document.getElementById("mobile-menu-overlay");
    const sidebarCloseBtn = document.getElementById("sidebar-close-btn");

    function toggleMobileSidebar(show) {
        if (!sidebar) return;
        const isActive = typeof show === 'boolean' ? show : !sidebar.classList.contains("active");
        if (isActive) {
            sidebar.classList.add("active");
            if (overlay) overlay.classList.add("active");
            document.body.style.overflow = "hidden";
            if (menuBtn) menuBtn.innerHTML = '<span class="toggle-icon">\u2715</span> <span class="toggle-text">Kapat</span>';
        } else {
            sidebar.classList.remove("active");
            if (overlay) overlay.classList.remove("active");
            document.body.style.overflow = "";
            if (menuBtn) menuBtn.innerHTML = '<span class="toggle-icon">\u2630</span> <span class="toggle-text">Menu</span>';
        }
    }

    if (menuBtn) menuBtn.addEventListener("click", function (e) { e.stopPropagation(); toggleMobileSidebar(); });
    if (sidebarCloseBtn) sidebarCloseBtn.addEventListener("click", function (e) { e.stopPropagation(); toggleMobileSidebar(false); });
    if (overlay) overlay.addEventListener("click", function () { toggleMobileSidebar(false); });

    // --- D) CANLI ARAMA (AJAX) + Ctrl+K ---
    const searchInput = document.getElementById('header-search-input') || document.querySelector('.header-search input');
    const searchForm  = document.querySelector('.header-search-form') || document.querySelector('.header-search form');

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            if (searchInput) { e.preventDefault(); searchInput.focus(); searchInput.select(); }
        }
    });

    if (searchInput && searchForm) {
        let resultBox = document.getElementById('wiki-canli-arama-kutusu');
        if (!resultBox) {
            resultBox = document.createElement('div');
            resultBox.id = 'wiki-canli-arama-kutusu';
            resultBox.className = 'floating-search-results';
            searchForm.parentNode.appendChild(resultBox);
        }

        let ajaxTimer;
        const ajaxUrl  = (typeof malatya_ajax !== 'undefined' && malatya_ajax.ajax_url) ? malatya_ajax.ajax_url : '/wp-admin/admin-ajax.php';
        const nonceVal = (typeof malatya_ajax !== 'undefined' && malatya_ajax.nonce) ? malatya_ajax.nonce : '';

        searchInput.addEventListener('keyup', function () {
            clearTimeout(ajaxTimer);
            const kelime = this.value.trim();
            if (kelime.length < 2) { resultBox.style.display = 'none'; return; }

            ajaxTimer = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'wiki_canli_arama');
                formData.append('kelime', kelime);
                if (nonceVal) formData.append('nonce', nonceVal);

                fetch(ajaxUrl, { method: 'POST', body: formData })
                    .then(res => res.text())
                    .then(data => { resultBox.innerHTML = data; resultBox.style.display = 'block'; })
                    .catch(err => { console.error('AJAX Arama hatasi:', err); });
            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!searchForm.contains(e.target) && !resultBox.contains(e.target)) {
                resultBox.style.display = 'none';
            }
        });
    }

    // --- E) SIDEBAR TOC (Icerikler Tablosu) ---
    const tocTarget = document.getElementById("wiki-sidebar-toc");
    const tocToggle = document.getElementById("wiki-toc-toggle");
    const headings  = document.querySelectorAll(".entry-content h2");

    if (tocTarget && headings.length >= 3) {
        let tocHtml = '<ul class="wiki-dinamik-menu">';
        headings.forEach((h, i) => {
            const id = h.id || "bolum-" + i;
            h.id = id;
            tocHtml += `<li><a href="#${id}">${h.innerText}</a></li>`;
        });
        tocHtml += '</ul>';
        tocTarget.innerHTML = tocHtml;
        const tocContainer = document.querySelector(".wiki-sidebar-toc-container");
        if (tocContainer) tocContainer.style.display = "block";

        if (tocToggle) {
            tocToggle.addEventListener("click", function (e) {
                e.preventDefault();
                const isHidden = tocTarget.style.display === "none";
                tocTarget.style.display = isHidden ? "block" : "none";
                this.innerText = isHidden ? "gizle" : "goster";
            });
        }
    }

    // --- F) RASTGELE MADDE Cache-Busting ---
    document.querySelectorAll('a[href*="rastgele"], a[href*="random=1"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const url = new URL(this.href, window.location.origin);
            url.searchParams.set('_t', Date.now());
            window.location.href = url.toString();
        });
    });

    // --- G) KAYNAKCA VURGULAMA (Referans linkleri) ---
    // [1] gibi linklere tiklaninca alttaki kaynagi vurgula
    document.querySelectorAll('.wiki-ref-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const hash = this.getAttribute('href');
            if (hash && hash.startsWith('#cite-')) {
                const targetEl = document.getElementById(hash.substring(1));
                if (targetEl) {
                    e.preventDefault();
                    history.pushState(null, null, hash);
                    highlightWikiElement(targetEl);
                }
            }
        });
    });

    // Kaynakca listesindeki geri donus oklarina tiklaninca metin icindeki [1] vurgula
    document.querySelectorAll('.wiki-cite-backlink').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const hash = this.getAttribute('href');
            if (hash && hash.startsWith('#ref-')) {
                const targetEl = document.getElementById(hash.substring(1));
                if (targetEl) {
                    e.preventDefault();
                    history.pushState(null, null, hash);
                    highlightWikiElement(targetEl);
                }
            }
        });
    });

    // Sayfa direkt #cite- veya #ref- hash'iyle acilmissa otomatik vurgula
    if (window.location.hash) {
        const hash = window.location.hash;
        if (hash.startsWith('#cite-') || hash.startsWith('#ref-')) {
            const targetEl = document.getElementById(hash.substring(1));
            if (targetEl) {
                setTimeout(function () { highlightWikiElement(targetEl); }, 400);
            }
        } else if (hash.startsWith('#respond') || hash.startsWith('#comment-')) {
            // Yorum formuna gidilmisse tartisma sekmesini ac
            const tartismaBtn = document.querySelector('.wiki-rail-btn[onclick*="tartisma-icerigi"]');
            if (tartismaBtn) tartismaBtn.click();
        }
    }

    // --- H) MOBIL ALT MENU: Ara butonu odak ---
    const mobileSearchBtn = document.querySelector('.mobile-bottom-nav .nav-item[data-action="search"]');
    if (mobileSearchBtn) {
        mobileSearchBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const inp = document.getElementById('header-search-input');
            if (inp) { inp.focus(); inp.scrollIntoView({ behavior: 'smooth' }); }
        });
    }

}); // DOMContentLoaded sonu
