<?php
include_once 'session_bootstrap.php';
include_once 'db.php';
if (!isset($_SESSION['email'])) {
    header("Location: ../index.html");
    exit();
}

$db = new database();
$db->connect();

$section = $_GET['section'] ?? '';

switch ($section) {
    case 'orderHistory':
        renderOrders($db);
        break;
    case 'configurations':
        renderConfig($db);
        break;
    case 'security':
        renderSecurity($db);
        break;
    case 'products':
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === 1) {
            renderProducts($db);
        } else {
            echo "<p role='alert'>Accesso negato.</p>";
        }
        break;
    case 'users':
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === 1) {
            renderUsers($db);
        } else {
            echo "<p role='alert'>Accesso negato.</p>";
        }
        break;
    case 'messages':
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === 1) {
            renderMessages($db);
        } else {
            echo "<p role='alert'>Accesso negato.</p>";
        }
        break;
    case 'logout':
        renderLogout($db);
        break;
}
$db->close();

// scorciatoia per fare l'escape dei dati prima di stamparli (evita XSS)
function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function renderOrders($db)
{
    echo "<section aria-labelledby='order-history-title'>";
    echo "<h2 id='order-history-title'>Storico ordini</h2>";
    $orders = $db->getOrderHistory($_SESSION['email']);
    if ($orders && $orders->num_rows > 0) {
        $grouped = [];
        while ($row = $orders->fetch_assoc()) {
            $oid = $row['orderID'];
            if (!isset($grouped[$oid])) {
                $grouped[$oid] = [
                    'orderID'     => $oid,
                    'orderDate'   => $row['orderDate'],
                    'totalAmount' => $row['totalAmount'],
                    'items'       => []
                ];
            }
            $grouped[$oid]['items'][] = [
                'productName' => $row['productName'],
                'price'       => $row['price'],
                'quantity'    => $row['quantity']
            ];
        }

        echo "<ul class='admin-order-grid' aria-label='Elenco dei tuoi ordini'>";
        foreach ($grouped as $order) {
            $dateIso     = h(substr($order['orderDate'], 0, 10));
            $dateDisplay = h($order['orderDate']);
            $oid         = h($order['orderID']);
            $total       = h($order['totalAmount']);
            $itemCount   = count($order['items']);

            echo "<li class='admin-order-card'>"
                . "<header class='admin-order-header'>"
                . "<div class='admin-order-meta'>"
                . "<span class='admin-order-id'>Ordine #" . $oid . "</span>"
                . "<time class='admin-order-date' datetime='" . $dateIso . "'>" . $dateDisplay . "</time>"
                . "</div>"
                . "<span class='admin-order-total' aria-label='Totale ordine: " . $total . " euro'>"
                . $total . " €</span>"
                . "</header>"
                . "<p class='admin-order-summary'>"
                . $itemCount . ($itemCount === 1 ? ' articolo' : ' articoli')
                . "</p>"
                . "<ul class='admin-order-items'>";

            foreach ($order['items'] as $item) {
                $name     = h($item['productName']);
                $price    = h($item['price']);
                $qty      = (int)$item['quantity'];
                $subtotal = number_format($item['price'] * $item['quantity'], 2, '.', '');

                echo "<li class='admin-order-item'>"
                    . "<span class='admin-order-item-name'>" . $name . "</span>"
                    . "<span class='admin-order-item-qty'>× " . $qty . "</span>"
                    . "<span class='admin-order-item-price'>" . $price . " €</span>"
                    . "<span class='admin-order-item-subtotal'>" . $subtotal . " €</span>"
                    . "</li>";
            }

            echo "</ul>"
                . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Non hai ancora effettuato ordini.</p>";
    }
    echo "</section>";
}

function renderConfig($db)
{
    echo "<h2>Impostazioni account</h2>";

    // sticky form: se l'ultimo invio è stato rifiutato uso i valori inseriti dall'utente
    $sticky = $_SESSION['stickyConfig'] ?? null;
    unset($_SESSION['stickyConfig']);

    // feedback inline da query string
    if (!empty($_GET['error'])) {
        echo "<p class='form-error-msg active' role='alert'>" . h($_GET['error']) . "</p>";
    } elseif (!empty($_GET['success'])) {
        echo "<p class='form-error-msg active status-success' role='status'>" . h($_GET['success']) . "</p>";
    }

    $userInfo = $db->getUserInfo($_SESSION['email']);
    if ($userInfo && $userInfo->num_rows > 0) {
        while ($row = $userInfo->fetch_assoc()) {
            $name        = $sticky['name']        ?? $row['name'];
            $surname     = $sticky['surname']     ?? $row['surname'];
            $email       = $sticky['email']       ?? $row['email'];
            $phoneNumber = $sticky['phoneNumber'] ?? $row['phoneNumber'];

            echo "<div class='adminUpload adminUpload--wide adminUpload--grid'>
                <form action='php/account/modifyUserInfo.php' method='post' aria-labelledby='config-form-title'>
                    <h3 id='config-form-title' class='sr-only'>Modifica i tuoi dati</h3>

                    <div class='form-pair'>
                        <div class='form-group'>
                            <label for='config-name'>Nome</label>
                            <input id='config-name' name='name' type='text' value='" . h($name) . "' autocomplete='given-name' aria-required='true' required>
                        </div>
                        <div class='form-group'>
                            <label for='config-surname'>Cognome</label>
                            <input id='config-surname' name='surname' type='text' value='" . h($surname) . "' autocomplete='family-name' aria-required='true' required>
                        </div>
                    </div>

                    <div class='form-pair'>
                        <div class='form-group'>
                            <label for='config-email'>Email</label>
                            <input id='config-email' name='email' type='email' value='" . h($email) . "' autocomplete='email' aria-required='true' required>
                        </div>
                        <div class='form-group'>
                            <label for='config-phone'>Telefono</label>
                            <input id='config-phone' name='phoneNumber' type='tel' value='" . h($phoneNumber) . "' autocomplete='tel' aria-required='true' required>
                        </div>
                    </div>

                    <button name='submit' type='submit' class='button'>Salva modifiche</button>
                </form>
            </div>";
        }
    } else {
        echo "<p>Nessun dato utente trovato.</p>";
    }
}

function renderSecurity($db)
{
    echo "<h2>Sicurezza</h2>";

    // feedback inline da query string
    if (!empty($_GET['error'])) {
        echo "<p class='form-error-msg active' role='alert'>" . h($_GET['error']) . "</p>";
    } elseif (!empty($_GET['success'])) {
        echo "<p class='form-error-msg active status-success' role='status'>" . h($_GET['success']) . "</p>";
    }

    echo "<h3>Cambia password</h3>
        <div class='adminUpload adminUpload--wide adminUpload--grid'>
            <form action='php/account/changePassword.php' method='post' id='change-password-form' aria-describedby='change-password-help'>
                <p id='change-password-help'>Inserisci una nuova password che soddisfi tutti i requisiti.</p>

                <div class='form-pair'>
                    <div class='form-group'>
                        <label for='newPass'>Nuova password</label>
                        <input name='newPass' id='newPass' type='password' autocomplete='new-password' aria-describedby='security-password-requirements' aria-required='true' required>
                    </div>
                    <div class='form-group'>
                        <label for='confPass'>Conferma nuova password</label>
                        <input name='confPass' id='confPass' type='password' autocomplete='new-password' aria-describedby='password-error' aria-required='true' required>
                    </div>
                </div>

                <ul id='security-password-requirements' class='password-requirements' aria-live='polite'>
                    <li id='requirement-1' class='requirement-unmet'>Almeno 8 caratteri</li>
                    <li id='requirement-2' class='requirement-unmet'>Almeno un numero</li>
                    <li id='requirement-3' class='requirement-unmet'>Almeno una lettera maiuscola</li>
                    <li id='requirement-4' class='requirement-unmet'>Almeno un carattere speciale</li>
                </ul>

                <p id='password-error' class='form-error-msg' role='alert' aria-live='polite'></p>
                <button type='submit' name='submit' class='button'>Aggiorna password</button>
            </form>
        </div>";
}

function renderProducts($db)
{
    echo "<h2>Prodotti</h2>";

    echo "<div class='admin-toolbar'>
            <div class='catalog-search-box admin-search-box'>
                <i class='fas fa-search search-icon' aria-hidden='true'></i>
                <label for='admin-product-search' class='sr-only'>Cerca prodotti</label>
                <input type='text' class='search-bar' id='admin-product-search' placeholder='Cerca prodotti…' autocomplete='off'>
            </div>
            <button type='button' class='button' id='open-add-product'>
                <i class='fas fa-plus' aria-hidden='true'></i> Aggiungi prodotto
            </button>
        </div>";

    echo "<dialog id='add-product-dialog' class='admin-dialog' aria-labelledby='add-product-dialog-title'>
            <div class='admin-dialog-content adminUpload adminUpload--wide adminUpload--grid'>
                <header class='admin-dialog-header'>
                    <h3 id='add-product-dialog-title'>Aggiungi un nuovo prodotto</h3>
                    <button type='button' class='admin-dialog-close' id='close-add-product' aria-label='Chiudi finestra'>
                        <i class='fas fa-times' aria-hidden='true'></i>
                    </button>
                </header>
            <form id='product-upload' action='php/optionsPage.php' method='POST' enctype='multipart/form-data' aria-describedby='product-form-help'>
                <p id='product-form-help' class='sr-only'>Compila nome, descrizione, prezzo, disponibilità e immagine. Gli altri campi sono opzionali.</p>

                <div class='form-pair'>
                    <div>
                        <label for='product-name'>Nome prodotto</label>
                        <input type='text' id='product-name' name='name' aria-required='true' required/>
                    </div>
                    <div>
                        <label for='product-price'>Prezzo (€)</label>
                        <input type='number' step='0.01' min='0' id='product-price' name='price' aria-required='true' required/>
                    </div>
                </div>

                <div class='form-group'>
                    <label for='product-description'>Descrizione</label>
                    <input type='text' id='product-description' name='description' aria-required='true' required/>
                </div>

                <div class='form-pair'>
                    <div>
                        <label for='product-material'>Materiale</label>
                        <input type='text' id='product-material' name='material' maxlength='120' placeholder='Es. Vetro riciclato, iuta naturale'/>
                    </div>
                    <div>
                        <label for='product-author'>Autore / artigiano</label>
                        <input type='text' id='product-author' name='author' maxlength='80' placeholder='Es. Marco Ferretti'/>
                    </div>
                </div>

                <div class='form-pair'>
                    <div>
                        <label for='product-width'>Larghezza (cm)</label>
                        <input type='number' id='product-width' name='dimensionsWidth' min='0' step='0.1' placeholder='Es. 9'/>
                    </div>
                    <div>
                        <label for='product-height'>Altezza (cm)</label>
                        <input type='number' id='product-height' name='dimensionsHeight' min='0' step='0.1' placeholder='Es. 35'/>
                    </div>
                </div>

                <div class='form-pair'>
                    <div>
                        <label for='product-weight'>Peso</label>
                        <input type='text' id='product-weight' name='weight' maxlength='30' placeholder='Es. 1,0 kg'/>
                    </div>
                    <div></div>
                </div>

                <div class='form-pair'>
                    <div>
                        <label for='product-voltage'>Voltaggio</label>
                        <input type='text' id='product-voltage' name='voltage' maxlength='30' placeholder='Es. 220–240 V'/>
                    </div>
                    <div>
                        <label for='product-stock'>Disponibilità</label>
                        <select id='product-stock' name='inStock' aria-required='true' required>
                            <option value='true'>Sì, disponibile</option>
                            <option value='false'>No, esaurito</option>
                        </select>
                    </div>
                </div>

                <div class='form-pair'>
                    <div>
                        <label for='image-upload' class='button file-upload-label'>
                          <i class='fas fa-camera' aria-hidden='true'></i> Seleziona immagine
                        </label>
                        <input type='file' id='image-upload' name='image' class='sr-only' accept='image/png, image/jpg, image/jpeg, image/webp' aria-describedby='file-name-display' aria-required='true' required>
                        <p id='file-name-display' aria-live='polite'>Nessun file selezionato</p>
                    </div>
                </div>

                <p id='add-product-status' class='form-error-msg' role='status' aria-live='polite' aria-atomic='true'></p>
                <button type='submit' name='submit' class='button'>Aggiungi prodotto</button>
            </form>
            </div>
        </dialog>";

    echo "<dialog id='edit-product-dialog' class='admin-dialog' aria-labelledby='edit-product-dialog-title'>
            <div class='admin-dialog-content adminUpload adminUpload--wide adminUpload--grid'>
                <header class='admin-dialog-header'>
                    <h3 id='edit-product-dialog-title'>Modifica prodotto</h3>
                    <button type='button' class='admin-dialog-close' id='close-edit-product' aria-label='Chiudi finestra'>
                        <i class='fas fa-times' aria-hidden='true'></i>
                    </button>
                </header>
            <form id='edit-product-form' action='php/product/modifyProduct.php' method='POST' enctype='multipart/form-data' aria-describedby='edit-product-form-help'>
                <p id='edit-product-form-help' class='sr-only'>Modifica i campi del prodotto. L'immagine è opzionale: lasciarla vuota mantiene quella attuale.</p>
                <input type='hidden' id='edit-product-id' name='id'>

                <div class='form-pair'>
                    <div class='form-group'>
                        <label for='edit-product-name'>Nome prodotto</label>
                        <input type='text' id='edit-product-name' name='name' aria-required='true' required>
                    </div>
                    <div class='form-group'>
                        <label for='edit-product-price'>Prezzo (€)</label>
                        <input type='number' step='0.01' min='0' id='edit-product-price' name='price' aria-required='true' required>
                    </div>
                </div>

                <div class='form-group'>
                    <label for='edit-product-description'>Descrizione</label>
                    <input type='text' id='edit-product-description' name='description' aria-required='true' required>
                </div>

                <div class='form-pair'>
                    <div class='form-group'>
                        <label for='edit-product-material'>Materiale</label>
                        <input type='text' id='edit-product-material' name='material' maxlength='120' placeholder='Es. Vetro riciclato, iuta naturale'>
                    </div>
                    <div class='form-group'>
                        <label for='edit-product-author'>Autore / artigiano</label>
                        <input type='text' id='edit-product-author' name='author' maxlength='80' placeholder='Es. Marco Ferretti'>
                    </div>
                </div>

                <div class='form-pair'>
                    <div class='form-group'>
                        <label for='edit-product-width'>Larghezza (cm)</label>
                        <input type='number' id='edit-product-width' name='dimensionsWidth' min='0' step='0.1' placeholder='Es. 9'>
                    </div>
                    <div class='form-group'>
                        <label for='edit-product-height'>Altezza (cm)</label>
                        <input type='number' id='edit-product-height' name='dimensionsHeight' min='0' step='0.1' placeholder='Es. 35'>
                    </div>
                </div>

                <div class='form-pair'>
                    <div class='form-group'>
                        <label for='edit-product-weight'>Peso</label>
                        <input type='text' id='edit-product-weight' name='weight' maxlength='30' placeholder='Es. 1,0 kg'>
                    </div>
                    <div class='form-group'>
                        <label for='edit-product-voltage'>Voltaggio</label>
                        <input type='text' id='edit-product-voltage' name='voltage' maxlength='30' placeholder='Es. 220–240 V'>
                    </div>
                </div>

                <div class='form-group'>
                    <label for='edit-product-stock'>Disponibilità</label>
                    <select id='edit-product-stock' name='inStock' aria-required='true' required>
                        <option value='true'>Sì, disponibile</option>
                        <option value='false'>No, esaurito</option>
                    </select>
                </div>

                <div class='form-group'>
                    <label for='edit-image-upload' class='button file-upload-label'>
                        <i class='fas fa-camera' aria-hidden='true'></i> Cambia immagine
                    </label>
                    <input type='file' id='edit-image-upload' name='image' class='sr-only' accept='image/png, image/jpg, image/jpeg, image/webp' aria-describedby='edit-file-name-display'>
                    <p id='edit-file-name-display' aria-live='polite'>Mantieni immagine attuale</p>
                </div>

                <p id='edit-product-status' class='form-error-msg' role='status' aria-live='polite' aria-atomic='true'></p>
                <button type='submit' name='submit' class='button'>Salva modifiche</button>
            </form>
            </div>
        </dialog>";

    echo "<section aria-labelledby='products-available-title'>";
    echo "<h3 id='products-available-title'>Prodotti disponibili</h3>";

    $result = $db->getProducts();
    if ($result && $result->num_rows > 0) {
        echo "<p id='admin-product-search-status' class='sr-only' role='status' aria-live='polite'></p>";
        echo "<ul class='admin-product-grid' id='admin-product-list' aria-label='Elenco dei prodotti disponibili a catalogo'>";
        while ($p = $result->fetch_assoc()) {
            $name = h($p['productName']);
            $id = h($p['id']);
            $img = h($p['imageUrl']);
            $inStock = $p['inStock'] == 1;
            $stockClass = $inStock ? 'in-stock' : 'out-stock';
            $stockText = $inStock ? 'Disponibile' : 'Esaurito';
            $price = h($p['price']);
            $searchKey = strtolower($p['productName'] . ' ' . $p['description']);
            echo "<li class='admin-product-card' data-search='" . h($searchKey) . "'>"
                . "<div class='admin-product-media'>"
                . "<img src='assets/img/" . $img . "' alt='Foto del prodotto " . $name . "' loading='lazy' decoding='async'>"
                . "</div>"
                . "<div class='admin-product-body'>"
                . "<header class='admin-product-header'>"
                . "<h4 class='admin-product-name'>" . $name . "</h4>"
                . "<div class='admin-product-meta'>"
                . "<span class='admin-product-price' aria-label='Prezzo: " . $price . " euro'>" . $price . " €</span>"
                . "<span class='admin-product-stock " . $stockClass . "'>" . $stockText . "</span>"
                . "</div>"
                . "</header>"
                . "<p class='admin-product-description'>" . h($p['description']) . "</p>"
                . "<footer class='admin-product-actions'>"
                . "<button type='button' class='button edit-product-btn' data-edit-id='" . $id . "' data-edit-name='" . $name . "' aria-label='Modifica prodotto " . $name . "'>Modifica</button>"
                . "<form action='php/product/deleteProduct.php' method='post' aria-label='Elimina " . $name . "' onsubmit='return confirm(\"Sei sicuro di voler eliminare il prodotto " . $name . "?\");'>"
                . "<input type='hidden' name='id' value='" . $id . "'>"
                . "<button type='submit' name='submit' class='button button--danger' aria-label='Elimina prodotto " . $name . "'>Elimina</button>"
                . "</form>"
                . "</footer>"
                . "</div>"
                . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun prodotto disponibile.</p>";
    }
    echo "</section>";
}

function renderUsers($db)
{
    echo "<h2>Utenti</h2>";
    echo "<section aria-labelledby='registered-users-title'>";
    echo "<h3 id='registered-users-title'>Amministratori registrati</h3>";

    $result = $db->getUsers();
    if ($result && $result->num_rows > 0) {
        echo "<table class='data-table'>";
        echo "<caption class='sr-only'>Elenco degli amministratori</caption>";
        echo "<thead><tr>"
            . "<th scope='col'>Username</th>"
            . "<th scope='col'>Nome</th>"
            . "<th scope='col'>Email</th>"
            . "<th scope='col'>Telefono</th>"
            . "</tr></thead><tbody>";
        while ($u = $result->fetch_assoc()) {
            if ($u['isAdmin'] == 1) {
                $email = h($u['email']);
                $phone = h($u['phoneNumber']);
                echo "<tr>"
                    . "<th scope='row'>" . h($u['username']) . "</th>"
                    . "<td>" . h($u['name']) . " " . h($u['surname']) . "</td>"
                    . "<td><a href='mailto:" . $email . "'>" . $email . "</a></td>"
                    . "<td><a href='tel:" . $phone . "'>" . $phone . "</a></td>"
                    . "</tr>";
            }
        }
        echo "</tbody></table>";
    } else {
        echo "<p>Nessun utente registrato.</p>";
    }
    echo "</section>";

    echo "<section aria-labelledby='admin-register-title'>";
    echo "<h3 id='admin-register-title'>Registra un nuovo amministratore</h3>";
    echo "<div class='adminUpload adminUpload--wide adminUpload--grid'>
            <form action='php/account/register.php' method='post' id='access-form' novalidate>
              <div class='form-pair'>
                <div class='form-group'>
                  <label for='name-input'>Nome</label>
                  <input type='text' id='name-input' name='name' autocomplete='given-name' aria-describedby='admin-name-error' aria-required='true' required>
                  <p class='form-error-msg' id='admin-name-error' role='alert' aria-live='polite'></p>
                </div>
                <div class='form-group'>
                  <label for='surname-input'>Cognome</label>
                  <input type='text' id='surname-input' name='surname' autocomplete='family-name' aria-describedby='admin-surname-error' aria-required='true' required>
                  <p class='form-error-msg' id='admin-surname-error' role='alert' aria-live='polite'></p>
                </div>
              </div>
              <div class='form-pair'>
                <div class='form-group'>
                  <label for='email-input'>Email</label>
                  <input type='email' id='email-input' name='email' autocomplete='email' aria-describedby='admin-email-error' aria-required='true' required>
                  <p class='form-error-msg' id='admin-email-error' role='alert' aria-live='polite'></p>
                </div>
                <div class='form-group'>
                  <label for='phone-input'>Numero di telefono</label>
                  <input type='tel' id='phone-input' name='phone' autocomplete='tel' aria-describedby='admin-phone-help admin-phone-error' aria-required='true' required>
                  <p id='admin-phone-help' class='sr-only'>Numero di telefono internazionale: includere il prefisso del paese.</p>
                  <p class='form-error-msg' id='admin-phone-error' role='alert' aria-live='polite'></p>
                </div>
              </div>
              <div class='form-group'>
                <label for='password-input'>Password</label>
                <input type='password' id='password-input' name='password' autocomplete='new-password' aria-describedby='admin-password-requirements' aria-required='true' required>

                <p id='admin-password-requirements-label'>La password deve contenere:</p>

                <ul class='password-requirements' id='admin-password-requirements' aria-labelledby='admin-password-requirements-label' aria-live='polite'>
                    <li id='requirement-1' class='requirement-unmet'>Almeno 8 caratteri</li>
                    <li id='requirement-2' class='requirement-unmet'>Almeno un numero</li>
                    <li id='requirement-3' class='requirement-unmet'>Almeno una lettera maiuscola</li>
                    <li id='requirement-4' class='requirement-unmet'>Almeno un carattere speciale</li>
                </ul>
              </div>
              <input type='hidden' name='isAdmin' value='1'>
              <p class='form-error-msg' id='general-register-error' role='alert' aria-live='assertive'></p>
              <button type='submit' class='button'>Crea account amministratore</button>
            </form>
        </div>";
    echo "</section>";

    echo "<section aria-labelledby='make-admin-title'>";
    echo "<h3 id='make-admin-title'>Promuovi un utente esistente</h3>";
    echo "<div class='adminUpload adminUpload--inline'>
            <form action='php/account/changeToAdmin.php' method='post'>
                <div class='form-group'>
                  <label for='make-admin-username'>Username</label>
                  <input id='make-admin-username' name='username' type='text' aria-required='true' required>
                </div>
                <button type='submit' name='submit' class='button'>Promuovi ad amministratore</button>
            </form>
        </div>";
    echo "</section>";
}

function renderLogout($db)
{
    echo "<h2>Conferma logout</h2>";
    echo "<p>Sei sicuro di voler uscire dal tuo account?</p>";
    echo "<form action='./php/account/logout.php' method='POST'>";
    echo "<button type='submit' class='button'>Sì, esci</button>";
    echo "</form>";
}

function renderMessages($db)
{
    echo "<h2>Messaggi dai contatti</h2>";

    $subjectLabels = [
        'order' => 'Ordine / spedizione',
        'damage' => 'Articolo danneggiato',
        'custom' => 'Pezzi personalizzati',
        'general' => 'Informazioni generali'
    ];

    $activeFilter = $_GET['filter'] ?? null;
    if ($activeFilter !== null && !in_array($activeFilter, ['pending', 'handled', 'all'], true)) {
        $activeFilter = null;
    }
    $filter = $activeFilter ?? 'all';

    echo "<section aria-labelledby='messages-filter-title'>";
    echo "<h3 id='messages-filter-title' class='sr-only'>Filtro messaggi</h3>";
    echo "<form method='get' action='optionsPage.html' class='admin-filters' aria-label='Filtra messaggi per stato'>";
    echo "<input type='hidden' name='section' value='messages'>";
    foreach (['pending' => 'Da gestire', 'handled' => 'Gestiti', 'all' => 'Tutti'] as $value => $label) {
        $active = $activeFilter === $value ? ' aria-current="true" class="button active"' : ' class="button"';
        echo "<button type='submit' name='filter' value='" . $value . "'" . $active . ">" . $label . "</button>";
    }
    echo "</form>";
    echo "</section>";

    $sql = "SELECT id, name, surname, email, subject, message, createdAt, handled FROM messages";
    if ($filter === 'pending') {
        $sql .= " WHERE handled = 0";
    } elseif ($filter === 'handled') {
        $sql .= " WHERE handled = 1";
    }
    $sql .= " ORDER BY createdAt DESC";

    $result = $db->connection->query($sql);

    if ($result && $result->num_rows > 0) {
        echo "<ul class='admin-message-grid' id='admin-message-list' aria-label='Elenco dei messaggi ricevuti dal form contatti'>";
        while ($m = $result->fetch_assoc()) {
            $id        = (int)$m['id'];
            $sender    = h($m['name']) . ' ' . h($m['surname']);
            $email     = h($m['email']);
            $subjLabel = $subjectLabels[$m['subject']] ?? h($m['subject']);
            $msgText   = h($m['message']);
            $date      = h($m['createdAt']);
            $handled   = (int)$m['handled'] === 1;
            $stateText = $handled ? 'Gestito' : 'Da gestire';
            $stateClass = $handled ? 'handled' : 'pending';

            echo "<li class='admin-message-card' data-message-id='" . $id . "'>"
                . "<header class='admin-message-header'>"
                . "<div class='admin-message-meta'>"
                . "<span class='admin-message-sender'>" . $sender . "</span>"
                . "<a class='admin-message-email' href='mailto:" . $email . "'>" . $email . "</a>"
                . "<time class='admin-message-date'>" . $date . "</time>"
                . "</div>"
                . "<span class='admin-message-state " . $stateClass . "'>" . $stateText . "</span>"
                . "</header>"
                . "<div class='admin-message-subject'>" . $subjLabel . "</div>"
                . "<p class='admin-message-body'>" . $msgText . "</p>";

            echo "<form class='admin-message-reply' data-reply-id='" . $id . "' aria-label='Rispondi al messaggio di " . $sender . "'>"
                . "<label for='reply-" . $id . "' class='sr-only'>Risposta al messaggio</label>"
                . "<textarea id='reply-" . $id . "' name='reply' rows='2' placeholder='Scrivi una risposta...'></textarea>"
                . "<button type='submit' class='button'>Rispondi</button>"
                . "<p class='form-error-msg admin-message-status' role='status' aria-live='polite'></p>"
                . "</form>";

            echo "<footer class='admin-message-actions'>";
            if (!$handled) {
                echo "<form class='admin-message-action-form' data-action='handle' data-id='" . $id . "' aria-label='Segna come gestito il messaggio di " . $sender . "'>"
                    . "<button type='submit' class='button'>Segna come gestito</button>"
                    . "</form>";
            }
            echo "<form class='admin-message-action-form' data-action='delete' data-id='" . $id . "' aria-label='Elimina il messaggio di " . $sender . "'>"
                . "<button type='submit' class='button button--danger'>Elimina</button>"
                . "</form>";
            echo "</footer>";

            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun messaggio da mostrare.</p>";
    }
}
?>
