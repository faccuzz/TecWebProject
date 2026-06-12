document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;

    if (path.includes('optionsPage.html')) {
        initOptionsPage();
    } else if (path.includes('modifyProduct.html')) {
        getProdotto();
        initModifyProductForm();
    }
});

// estrae larghezza e altezza dalla stringa salvata nel db (tipo "Ø 9 cm × H 33 cm")
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
                adminItems.forEach(item => { item.style.display = 'block'; });
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

    // uso questa sia quando l'utente clicca sul menu, sia all'avvio se nell'url c'è ?section=...
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

        // se la sezione è già aperta non ricarico
        if (elemento.classList.contains('active')) {
            event.preventDefault();
            return;
        }
        activateSection(button.getAttribute('data-section'));
    });

    // se nell'url c'è ?section=... (es. dopo che ho aggiunto un prodotto), apro quella
    const initialSection = new URLSearchParams(window.location.search).get('section');
    if (initialSection && SECTION_LABELS[initialSection]) {
        activateSection(initialSection);
    }

    async function caricaDati(sezione) {
        try {
            const risposta = await fetch(`./php/optionsPage.php?section=${sezione}`);
            const html = await risposta.text();
            areaContenuto.innerHTML = html;

            // sposto il focus sulla zona dei contenuti (preventScroll evita che la pagina salti su)
            areaContenuto.focus({ preventScroll: true });
            announceSection(`Sezione ${SECTION_LABELS[sezione] || sezione} caricata.`);

            switch (sezione) {
                case 'products': activateProductForm(); break;
                case 'users':
                    if (typeof initFormChecker === 'function') initFormChecker();
                    break;
                case 'security': initSecurityPasswordChecker(); break;
            }
        } catch (error) {
            areaContenuto.innerHTML = '<p role="alert">Errore nel caricamento della sezione.</p>';
            console.error('Errore caricamento sezione:', error);
        }
    }

    function activateProductForm() {
        const fileInput = document.getElementById('image-upload');
        const fileNameDisplay = document.getElementById('file-name-display');
        const productForm = document.getElementById('product-upload');
        const statusEl = document.getElementById('add-product-status');
        if (!productForm) return;

        function showStatus(msg, isError = true) {
            if (statusEl) {
                statusEl.textContent = msg;
                statusEl.classList.toggle('active', !!msg);
                statusEl.style.color = isError ? '' : 'var(--success-color)';
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

            // controllo i campi obbligatori. Per il file uso fileInput.files
            // perche FormData.get('image') ritorna comunque un File anche se vuoto
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
                    // forzo il reload anche se sono gia sulla stessa url
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

    verifyAdmin();
}

function getProdotto() {
    const id = new URLSearchParams(window.location.search).get('id');
    const idField = document.getElementById('id');
    if (idField) idField.value = id;

    fetch(`./php/product/singleInfo.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            const setVal = (elId, val) => {
                const el = document.getElementById(elId);
                if (el && val !== undefined && val !== null) el.value = val;
            };

            setVal('name',        data.productName);
            setVal('price',       data.price);
            setVal('description', data.description);
            setVal('material',    data.material);
            setVal('author',      data.author);
            setVal('weight',      data.weight);
            setVal('voltage',     data.voltage);

            // le dimensioni nel db sono in una sola stringa, le splitto nei due input
            const { width, height } = parseDimensions(data.dimensions);
            setVal('dimensionsWidth',  width);
            setVal('dimensionsHeight', height);

            const stockEl = document.getElementById('inStock');
            if (stockEl) stockEl.value = (data.inStock == 1) ? 'true' : 'false';

            const fileEl = document.getElementById('file-name-display');
            if (fileEl && data.imageUrl) fileEl.textContent = 'File selezionato: ' + data.imageUrl;
        })
        .catch(error => console.error('Errore caricamento prodotto:', error));
}

function initModifyProductForm() {
    const modifyForm = document.getElementById('product-upload');
    const fileInput = document.getElementById('image-upload');
    const fileNameDisplay = document.getElementById('file-name-display');
    const statusEl = document.getElementById('modify-status');
    if (!modifyForm) return;

    if (fileInput) {
        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file && fileNameDisplay) fileNameDisplay.textContent = file.name;
        });
    }

    function setStatus(msg) {
        if (statusEl) statusEl.textContent = msg;
    }

    modifyForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(modifyForm);
        formData.append('submit', 'true');

        try {
            const response = await fetch('php/product/modifyProduct.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                setStatus('Prodotto modificato con successo. Reindirizzamento in corso…');
                setTimeout(() => { window.location.href = 'optionsPage.html?section=products'; }, 1200);
            } else {
                setStatus('Errore durante la modifica: ' + (result.error || 'errore sconosciuto'));
            }
        } catch (error) {
            console.error('Errore di connessione:', error);
            setStatus('Impossibile connettersi al server per salvare le modifiche.');
        }
    });
}

function initSecurityPasswordChecker() {
    const form = document.getElementById('change-password-form');
    const newPass = document.getElementById('newPass');
    const confPass = document.getElementById('confPass');
    const errorMsg = document.getElementById('password-error');

    const req1 = document.getElementById('requirement-1');
    const req2 = document.getElementById('requirement-2');
    const req3 = document.getElementById('requirement-3');
    const req4 = document.getElementById('requirement-4');

    if (!newPass || !form) return;

    function setReq(el, met) {
        if (!el) return;
        el.classList.toggle('requirement-met', met);
        el.classList.toggle('requirement-unmet', !met);
        const baseText = el.dataset.baseText || el.textContent.replace(/^[✓✗]\s*/, '');
        el.dataset.baseText = baseText;
        el.setAttribute('aria-label', `${baseText}: ${met ? 'soddisfatto' : 'non soddisfatto'}`);
    }

    // stato iniziale: nessun requisito ok
    [req1, req2, req3, req4].forEach(r => setReq(r, false));

    newPass.addEventListener('input', () => {
        const checks = checkPasswordStrength(newPass.value).checks;
        setReq(req1, checks.length);
        setReq(req2, checks.number);
        setReq(req3, checks.uppercase);
        setReq(req4, checks.special);
    });

    form.addEventListener('submit', (event) => {
        const result = checkPasswordStrength(newPass.value);
        const isSecure = result.score === 4;

        if (!isSecure) {
            event.preventDefault();
            errorMsg.textContent = 'La password non soddisfa i requisiti.';
            errorMsg.classList.add('active');
            newPass.focus();
            return false;
        }

        if (newPass.value !== confPass.value) {
            event.preventDefault();
            errorMsg.textContent = 'Le password non coincidono.';
            errorMsg.classList.add('active');
            confPass.focus();
            return false;
        }

        errorMsg.classList.remove('active');
        errorMsg.textContent = '';
        return true;
    });
}
