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
    case 'logout':
        renderLogout($db);
        break;
}
$db->close();

//Anti Cross-Site Scripting
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
        echo "<table class='data-table'>";
        echo "<caption>Elenco dei tuoi ordini</caption>";
        echo "<thead><tr>"
            . "<th scope='col'>ID ordine</th>"
            . "<th scope='col'>Data</th>"
            . "<th scope='col'>Prodotto</th>"
            . "<th scope='col'>Prezzo</th>"
            . "<th scope='col'>Quantità</th>"
            . "</tr></thead>";
        echo "<tbody>";
        while ($row = $orders->fetch_assoc()) {
            $dateIso = h(substr($row['orderDate'], 0, 10));
            $dateDisplay = h($row['orderDate']);
            $priceLabel = h($row['price']) . ' euro';
            echo "<tr>"
                . "<th scope='row'>" . h($row['orderID']) . "</th>"
                . "<td><time datetime='" . $dateIso . "'>" . $dateDisplay . "</time></td>"
                . "<td>" . h($row['productName']) . "</td>"
                . "<td aria-label='" . $priceLabel . "'>" . h($row['price']) . " €</td>"
                . "<td>" . h($row['quantity']) . "</td>"
                . "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>Non hai ancora effettuato ordini.</p>";
    }
    echo "</section>";
}

function renderConfig($db)
{
    echo "<h2>Impostazioni account</h2>";
    $userInfo = $db->getUserInfo($_SESSION['email']);
    if ($userInfo && $userInfo->num_rows > 0) {
        while ($row = $userInfo->fetch_assoc()) {
            echo "<div class='adminUpload'>
                <form action='php/account/modifyUserInfo.php' method='post' aria-labelledby='config-form-title'>
                    <h3 id='config-form-title' class='sr-only'>Modifica i tuoi dati</h3>

                    <label for='config-name'>Nome</label>
                    <input id='config-name' name='name' type='text' value='" . h($row['name']) . "' autocomplete='given-name' aria-required='true' required>

                    <label for='config-surname'>Cognome</label>
                    <input id='config-surname' name='surname' type='text' value='" . h($row['surname']) . "' autocomplete='family-name' aria-required='true' required>

                    <label for='config-email'>Email</label>
                    <input id='config-email' name='email' type='email' value='" . h($row['email']) . "' autocomplete='email' aria-required='true' required>

                    <label for='config-phone'>Telefono</label>
                    <input id='config-phone' name='phoneNumber' type='tel' value='" . h($row['phoneNumber']) . "' autocomplete='tel' aria-required='true' required>

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
    echo "<h2>Sicurezza</h2>
        <h3>Cambia password</h3>
        <div class='adminUpload'>
            <form action='php/account/changePassword.php' method='post' id='change-password-form' aria-describedby='change-password-help'>
                <p id='change-password-help'>Inserisci una nuova password che soddisfi tutti i requisiti.</p>

                <label for='newPass'>Nuova password</label>
                <input name='newPass' id='newPass' type='password' autocomplete='new-password' aria-describedby='security-password-requirements' aria-required='true' required>

                <ul id='security-password-requirements' class='password-requirements' aria-live='polite'>
                    <li id='requirement-1' class='requirement-unmet'>Almeno 8 caratteri</li>
                    <li id='requirement-2' class='requirement-unmet'>Almeno un numero</li>
                    <li id='requirement-3' class='requirement-unmet'>Almeno una lettera maiuscola</li>
                    <li id='requirement-4' class='requirement-unmet'>Almeno un carattere speciale</li>
                </ul>

                <label for='confPass'>Conferma nuova password</label>
                <input name='confPass' id='confPass' type='password' autocomplete='new-password' aria-describedby='password-error' aria-required='true' required>

                <p id='password-error' class='form-error-msg' role='alert' aria-live='polite'></p>
                <button type='submit' name='submit' class='button'>Aggiorna password</button>
            </form>
        </div>";
}

function renderProducts($db)
{
    echo "<h2>Prodotti</h2>";
    echo "<div class='adminUpload adminUpload--wide'>
            <h3>Aggiungi un nuovo prodotto</h3>
            <form id='product-upload' action='php/optionsPage.php' method='POST' enctype='multipart/form-data' aria-describedby='product-form-help'>
                <p id='product-form-help' class='sr-only'>Tutti i campi sono obbligatori.</p>

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

                <label for='product-description'>Descrizione</label>
                <input type='text' id='product-description' name='description' aria-required='true' required/>

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
                        <label for='product-dimensions'>Dimensioni</label>
                        <input type='text' id='product-dimensions' name='dimensions' maxlength='60' placeholder='Es. Ø 9 cm × H 35 cm'/>
                    </div>
                    <div>
                        <label for='product-weight'>Peso</label>
                        <input type='text' id='product-weight' name='weight' maxlength='30' placeholder='Es. 1,0 kg'/>
                    </div>
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
                        <label for='image-upload' class='button'>
                          <i class='fas fa-camera' aria-hidden='true'></i> Seleziona immagine
                        </label>
                        <input type='file' id='image-upload' name='image' accept='image/png, image/jpg, image/jpeg, image/webp' aria-describedby='file-name-display'>
                        <p id='file-name-display'>Nessun file selezionato</p>
                    </div>
                </div>

                <button type='submit' name='submit' class='button'>Aggiungi prodotto</button>
            </form>
        </div>";

    echo "<section aria-labelledby='products-available-title'>";
    echo "<h3 id='products-available-title'>Prodotti disponibili</h3>";

    $result = $db->getProducts();
    if ($result && $result->num_rows > 0) {
        echo "<table class='data-table'>";
        echo "<caption class='sr-only'>Elenco dei prodotti disponibili a catalogo</caption>";
        echo "<thead><tr>"
            . "<th scope='col'>Nome</th>"
            . "<th scope='col'>Descrizione</th>"
            . "<th scope='col'>Immagine</th>"
            . "<th scope='col'>Disponibile</th>"
            . "<th scope='col'>Prezzo</th>"
            . "<th scope='col'>Azioni</th>"
            . "</tr></thead><tbody>";
        while ($p = $result->fetch_assoc()) {
            $name = h($p['productName']);
            $id = h($p['id']);
            $img = h($p['imageUrl']);
            $inStock = $p['inStock'] == 1;
            $stockLabel = $inStock ? 'Disponibile: sì' : 'Disponibile: no';
            $priceLabel = h($p['price']) . ' euro';
            echo "<tr>"
                . "<th scope='row'>" . $name . "</th>"
                . "<td>" . h($p['description']) . "</td>"
                . "<td><img src='assets/img/" . $img . "' alt='Foto del prodotto " . $name . "' loading='lazy' decoding='async' style='width:60px;height:60px;object-fit:cover;border-radius:4px;'></td>"
                . "<td aria-label='" . $stockLabel . "'>" . ($inStock ? 'Sì' : 'No') . "</td>"
                . "<td aria-label='" . $priceLabel . "'>" . h($p['price']) . " €</td>"
                . "<td>"
                . "<form action='php/product/deleteProduct.php' method='post' style='display:inline;' aria-label='Elimina " . $name . "'>"
                . "<input type='hidden' name='id' value='" . $id . "'>"
                . "<button type='submit' name='submit' class='button' aria-label='Elimina prodotto " . $name . "'>Elimina</button>"
                . "</form> "
                . "<a href='modifyProduct.html?id=" . $id . "' class='button' aria-label='Modifica prodotto " . $name . "'>Modifica</a>"
                . "</td>"
                . "</tr>";
        }
        echo "</tbody></table>";
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
    echo "<div class='adminUpload'>
            <form action='php/account/register.php' method='post' id='access-form' novalidate>
              <div class='form-group'>
                <label for='email-input'>Email</label>
                <input type='email' id='email-input' name='email' autocomplete='email' aria-describedby='admin-email-error' aria-required='true' required>
                <p class='form-error-msg' id='admin-email-error' role='alert' aria-live='polite'></p>
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
              <div class='form-group'>
                <label for='phone-input'>Numero di telefono</label>
                <input type='tel' id='phone-input' name='phone' autocomplete='tel' aria-describedby='admin-phone-help admin-phone-error' aria-required='true' required>
                <p id='admin-phone-help' class='sr-only'>Numero di telefono internazionale: includere il prefisso del paese.</p>
                <p class='form-error-msg' id='admin-phone-error' role='alert' aria-live='polite'></p>
              </div>
              <input type='hidden' name='isAdmin' value='1'>
              <p class='form-error-msg' id='general-register-error' role='alert' aria-live='assertive'></p>
              <button type='submit' class='button'>Crea account amministratore</button>
            </form>
        </div>";
    echo "</section>";

    echo "<section aria-labelledby='make-admin-title'>";
    echo "<h3 id='make-admin-title'>Promuovi un utente esistente</h3>";
    echo "<div class='adminUpload'>
            <form action='php/account/changeToAdmin.php' method='post'>
                <label for='make-admin-username'>Username</label>
                <input id='make-admin-username' name='username' type='text' aria-required='true' required>
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
?>
