document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;

    if (path.includes('optionsPage.html')) {
        initOptionsPage();
    }
});

//Estrae larghezza e altezza del prodotto nel db
function parseDimensions(str) {
    if (!str) return { width: '', height: '' };
    const heightMatch = str.match(/H\s*([\d]+(?:[.,][\d]+)?)\s*cm/i);
    const widthMatch  = str.match(/([\d]+(?:[.,][\d]+)?)\s*(?:cm|×|x)/i);
    const norm = m => m ? m[1].replace(',', '.') : '';
    return {
        width:  norm(widthMatch),
        height: norm(heightMatch),
    };
}

const SECTION_LABELS = {
    orderHistory: 'Storico ordini',
    configurations: 'Impostazioni account',
    security: 'Sicurezza',
    products: 'Prodotti',
    users: 'Utenti',
    logout: 'Logout',
};

function announceSection(message) {
    let live = document.getElementById('options-live-region');
    if (!live) {
        live = document.createElement('div');
        live.id = 'options-live-region';
        live.setAttribute('role', 'status');
        live.setAttribute('aria-live', 'polite');
        live.className = 'sr-only';
        document.body.appendChild(live);
    }
    live.textContent = message;
}

function initOptionsPage() {
    const menu = document.getElementById('menu-nav');
    const areaContenuto = document.getElementById('contentZone');
    const loader = document.getElementById('page-loader');
    const adminItems = document.querySelectorAll('.admin-only');

    async function verifyAdmin() {
        try {
            const response = await fetch('./php/account/loginCheck.php');
            const data = await response.json();

            if (data.is_admin) {
                adminItems.forEach(item => { item.classList.add('is-revealed'); });
            } else {
                adminItems.forEach(item => item.remove());
            }
        } catch (error) {
            console.log('Errore di connessione al server');
        } finally {
            if (loader) {
                loader.classList.add('fade-out');
                loader.setAttribute('aria-hidden', 'true');
            }
        }
    }

    // uso questa sia quando l'utente clicca sul menu, sia all'avvio se nell'url c'è ?già la sezione
    function activateSection(sezione) {
        const targetBtn = menu.querySelector(`button[data-section="${sezione}"]`);
        if (!targetBtn) return;
        const elemento = targetBtn.closest('li');
        if (elemento.classList.contains('active')) return;

        document.querySelectorAll('#menu-nav li').forEach(li => {
            li.classList.remove('active');
            const innerBtn = li.querySelector('button');
            if (innerBtn) innerBtn.removeAttribute('aria-current');
        });
        elemento.classList.add('active');
        targetBtn.setAttribute('aria-current', 'true');

        caricaDati(sezione);
    }

    menu.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-section]');
        if (!button) return;
        const elemento = button.closest('li');

        //non ricarico se sezione già aperta
        if (elemento.classList.contains('active')) {
            event.preventDefault();
            return;
        }
        activateSection(button.getAttribute('data-section'));
    });

    // se nell'url c'è già la sezione, apro quella (es. dopo aver inviato un nuovo prodotto)
    const initialSection = new URLSearchParams(window.location.search).get('section');
    if (initialSection && SECTION_LABELS[initialSection]) {
        activateSection(initialSection);
    }

    async function caricaDati(sezione) {
        try {
            const risposta = await fetch(`./php/optionsPage.php?section=${sezione}`);
            const html = await risposta.text();
            areaContenuto.innerHTML = html;

            // sposto il focus sulla zona dei contenuti (preventScroll evita che anche la pagina si allinei al focus)
            areaContenuto.focus({ preventScroll: true });
            announceSection(`Sezione ${SECTION_LABELS[sezione] || sezione} caricata.`);

            switch (sezione) {
                case 'products': activateProductForm(); break;
                case 'users':
                    if (typeof initFormChecker === 'function') initFormChecker();
                    break;
                case 'security': initSecurityPasswordChecker(); break;
                case 'configurations': initConfigPhone(); break;
                case 'messages': initMessagesSection(); break;
            }
        } catch (error) {
            areaContenuto.innerHTML = '<p role="alert">Errore nel caricamento della sezione.</p>';
            console.error('Errore caricamento sezione:', error);
        }
    }

    function activateProductForm() {
        initProductSearch();
        initAddProductDialog();
        initEditProductDialog();

        const fileInput = document.getElementById('image-upload');
        const fileNameDisplay = document.getElementById('file-name-display');
        const productForm = document.getElementById('product-upload');
        const statusEl = document.getElementById('add-product-status');
        if (!productForm) return;

        function showStatus(msg, isError = true) {
            if (statusEl) {
                statusEl.textContent = msg;
                statusEl.classList.toggle('active', !!msg);
                statusEl.classList.toggle('status-success', !isError);
            }
            announceSection(msg);
        }

        if (fileInput) {
            fileInput.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file && fileNameDisplay) {
                    fileNameDisplay.textContent = 'File selezionato: ' + file.name;
                }
            });
        }

        productForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            showStatus('');
            const formData = new FormData(productForm);

            // controllo i campi obbligatori. fileInput.files serve per l'immagine
            const file = fileInput && fileInput.files[0];
            if (!formData.get('name') || !formData.get('description') || !formData.get('price') || !formData.get('inStock') || !file || file.size === 0) {
                showStatus('Compila tutti i campi obbligatori e seleziona un\'immagine.');
                return;
            }

            try {
                const response = await fetch('./php/product/saveProduct.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    showStatus('Prodotto aggiunto con successo.', false);
                    // forzo il reload
                    setTimeout(() => { window.location.assign('optionsPage.html?section=products&t=' + Date.now()); }, 600);
                } else {
                    showStatus('Errore: ' + (result.error || 'salvataggio non riuscito.'));
                }
            } catch (error) {
                console.error('Errore di caricamento:', error);
                showStatus('Errore di connessione al server.');
            }
        });
    }

    function initProductSearch() {
        const searchInput = document.getElementById('admin-product-search');
        const list = document.getElementById('admin-product-list');
        const statusEl = document.getElementById('admin-product-search-status');
        if (!searchInput || !list) return;

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            const cards = list.querySelectorAll('.admin-product-card');
            let visible = 0;
            cards.forEach((card) => {
                const key = card.dataset.search || '';
                const match = !query || key.includes(query);
                card.hidden = !match;
                if (match) visible++;
            });
            if (statusEl) {
                statusEl.textContent = query
                    ? `${visible} prodott${visible === 1 ? 'o' : 'i'} trovat${visible === 1 ? 'o' : 'i'}.`
                    : '';
            }
        });
    }

    function initAddProductDialog() {
        const dialog = document.getElementById('add-product-dialog');
        const openBtn = document.getElementById('open-add-product');
        const closeBtn = document.getElementById('close-add-product');
        if (!dialog || !openBtn || typeof dialog.showModal !== 'function') return;

        openBtn.addEventListener('click', () => {
            dialog.showModal();
            const firstField = dialog.querySelector('input, select, textarea, button');
            if (firstField) firstField.focus();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', () => dialog.close());
        }

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
    }

    verifyAdmin();
}

function initMessagesSection() {
    const contentZone = document.getElementById('contentZone');
    if (!contentZone) return;

    async function loadMessages(filter) {
        const url = filter
            ? `./php/optionsPage.php?section=messages&filter=${filter}`
            : `./php/optionsPage.php?section=messages`;
        try {
            const res = await fetch(url);
            const html = await res.text();
            contentZone.innerHTML = html;
            const newUrl = filter
                ? `optionsPage.html?section=messages&filter=${filter}`
                : `optionsPage.html?section=messages`;
            history.pushState({ section: 'messages', filter }, '', newUrl);
            announceSection(filter
                ? `Messaggi filtrati: ${filter}.`
                : 'Tutti i messaggi caricati.');
        } catch (err) {
            console.error('Errore caricamento messaggi:', err);
        }
    }

    contentZone.addEventListener('submit', async (event) => {
        const form = event.target;

        if (form.classList.contains('admin-filters')) {
            event.preventDefault();
            const clicked = event.submitter;
            const filter = clicked && clicked.name === 'filter' ? clicked.value : null;
            await loadMessages(filter);
            return;
        }

        if (form.classList.contains('admin-message-reply')) {
            event.preventDefault();
            const status = form.querySelector('.admin-message-status');
            if (status) {
                status.textContent = 'Funzione di risposta non ancora implementata.';
                status.classList.add('active');
            }
            return;
        }

        if (form.classList.contains('admin-message-action-form')) {
            event.preventDefault();
            const action = form.dataset.action;
            const id = form.dataset.id;
            if (!action || !id) return;
            if (action === 'delete' && !confirm('Eliminare definitivamente questo messaggio?')) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', action);

            try {
                await fetch('./php/contacts/updateMessage.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                const currentFilter = new URLSearchParams(window.location.search).get('filter');
                await loadMessages(currentFilter);
            } catch (err) {
                console.error('Errore aggiornamento messaggio:', err);
            }
        }
    });
}

function initEditProductDialog() {
    const dialog = document.getElementById('edit-product-dialog');
    const closeBtn = document.getElementById('close-edit-product');
    const form = document.getElementById('edit-product-form');
    const titleEl = document.getElementById('edit-product-dialog-title');
    const fileInput = document.getElementById('edit-image-upload');
    const fileDisplay = document.getElementById('edit-file-name-display');
    const statusEl = document.getElementById('edit-product-status');
    const list = document.getElementById('admin-product-list');
    if (!dialog || !form || !list || typeof dialog.showModal !== 'function') return;

    function setStatus(msg, isError = true) {
        if (!statusEl) return;
        statusEl.textContent = msg || '';
        statusEl.classList.toggle('active', !!msg);
        statusEl.classList.toggle('status-success', !!msg && !isError);
    }

    function setVal(elId, val) {
        const el = document.getElementById(elId);
        if (el && val !== undefined && val !== null) el.value = val;
    }

    list.addEventListener('click', async (event) => {
        const btn = event.target.closest('.edit-product-btn');
        if (!btn) return;
        const id = btn.dataset.editId;
        const name = btn.dataset.editName || 'prodotto';
        if (!id) return;

        setStatus('');
        form.reset();
        if (titleEl) titleEl.textContent = `Modifica prodotto: ${name}`;
        if (fileDisplay) fileDisplay.textContent = 'Mantieni immagine attuale';
        document.getElementById('edit-product-id').value = id;

        try {
            const res = await fetch(`./php/product/singleInfo.php?id=${id}`);
            const data = await res.json();
            setVal('edit-product-name',        data.productName);
            setVal('edit-product-price',       data.price);
            setVal('edit-product-description', data.description);
            setVal('edit-product-material',    data.material);
            setVal('edit-product-author',      data.author);
            setVal('edit-product-weight',      data.weight);
            setVal('edit-product-voltage',     data.voltage);
            const { width, height } = parseDimensions(data.dimensions);
            setVal('edit-product-width',  width);
            setVal('edit-product-height', height);
            const stockEl = document.getElementById('edit-product-stock');
            if (stockEl) stockEl.value = (data.inStock == 1) ? 'true' : 'false';
            if (fileDisplay && data.imageUrl) fileDisplay.textContent = `Attuale: ${data.imageUrl}`;
        } catch (err) {
            console.error('Errore caricamento prodotto:', err);
            setStatus('Impossibile caricare i dati del prodotto.');
        }

        dialog.showModal();
        const first = document.getElementById('edit-product-name');
        if (first) first.focus();
    });

    if (closeBtn) closeBtn.addEventListener('click', () => dialog.close());

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });

    if (fileInput) {
        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file && fileDisplay) fileDisplay.textContent = `Nuova: ${file.name}`;
        });
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setStatus('');
        const formData = new FormData(form);
        formData.append('submit', 'true');

        try {
            const res = await fetch('php/product/modifyProduct.php', { method: 'POST', body: formData });
            const result = await res.json();
            if (result.success) {
                setStatus('Prodotto modificato con successo.', false);
                setTimeout(() => {
                    window.location.assign('optionsPage.html?section=products&t=' + Date.now());
                }, 800);
            } else {
                setStatus('Errore: ' + (result.error || 'salvataggio non riuscito.'));
            }
        } catch (err) {
            console.error('Errore di connessione:', err);
            setStatus('Impossibile connettersi al server.');
        }
    });
}

function initConfigPhone() {
    const phoneEl = document.getElementById('config-phone');
    if (!phoneEl || !window.intlTelInput) return;
    try {
        window.intlTelInput(phoneEl, {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
            preferredCountries: ["it", "fr", "de", "gb"],
            separateDialCode: true,
            dropdownContainer: document.body,
            initialCountry: "it",
        });
        const selectedFlag = phoneEl.closest('.iti')?.querySelector('.iti__selected-flag');
        if (selectedFlag) {
            selectedFlag.removeAttribute('aria-activedescendant');
            selectedFlag.removeAttribute('aria-owns');
            selectedFlag.setAttribute('role', 'button');
            const observer = new MutationObserver(() => {
                if (selectedFlag.getAttribute('aria-expanded') !== 'true') {
                    selectedFlag.removeAttribute('aria-activedescendant');
                }
            });
            observer.observe(selectedFlag, { attributes: true, attributeFilter: ['aria-expanded'] });
        }
    } catch (err) {
        console.error("Errore intl-tel-input config:", err);
    }
}

function initSecurityPasswordChecker() {
    const form = document.getElementById('change-password-form');
    const newPassword = document.getElementById('newPassword');
    const confirmPwd = document.getElementById('confirmPwd');
    const errorMsg = document.getElementById('password-error');

    const req1 = document.getElementById('requirement-1');
    const req2 = document.getElementById('requirement-2');
    const req3 = document.getElementById('requirement-3');
    const req4 = document.getElementById('requirement-4');

    if (!newPassword || !form) return;

    function setReq(el, met) {
        if (!el) return;
        el.classList.toggle('requirement-met', met);
        el.classList.toggle('requirement-unmet', !met);
        const baseText = el.dataset.baseText || el.textContent.replace(/^[✓✗]\s*/, '');
        el.dataset.baseText = baseText;
        el.setAttribute('aria-label', `${baseText}: ${met ? 'soddisfatto' : 'non soddisfatto'}`);
    }

    //Stato iniziale: nessun requisito è ok
    [req1, req2, req3, req4].forEach(r => setReq(r, false));

    newPassword.addEventListener('input', () => {
        const checks = checkPasswordStrength(newPassword.value).checks;
        setReq(req1, checks.length);
        setReq(req2, checks.number);
        setReq(req3, checks.uppercase);
        setReq(req4, checks.special);
    });

    form.addEventListener('submit', (event) => {
        const result = checkPasswordStrength(newPassword.value);
        const isSecure = result.score === 4;

        if (!isSecure) {
            event.preventDefault();
            errorMsg.textContent = 'La password non soddisfa i requisiti.';
            errorMsg.classList.add('active');
            newPassword.focus();
            return false;
        }

        if (newPassword.value !== confirmPwd.value) {
            event.preventDefault();
            errorMsg.textContent = 'Le password non coincidono.';
            errorMsg.classList.add('active');
            confirmPwd.focus();
            return false;
        }

        errorMsg.classList.remove('active');
        errorMsg.textContent = '';
        return true;
    });
}
