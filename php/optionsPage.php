<?php
include_once 'db.php';
session_start();
if(!isset($_SESSION['email'])){
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
            echo "Accesso negato.";
        }
        break;
    case 'users':
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === 1) {
            renderUsers($db);
        } else {
            echo "Accesso negato.";
        }
        break;
    case 'logout':
        renderLogout($db);
        break;
}
$db->close();

function renderOrders($db)
{
    echo "<h2>Order history</h2>";
    $products = $db->getOrderHistory($_SESSION['email']);
    if ($products->num_rows > 0) {
        while ($row = $products->fetch_assoc()) {
            echo "<ul id='productsList'><li>" . "<p>" . $row["orderID"] . "</p><p>" . $row["orderDate"] . "</p><p>" . $row["productName"] . "</p><p>" . $row["price"] . "</p><p>" . $row["quantity"] . "</p></li></ul>";
        }
    } else {
        echo "No products found.";
    }
}

function renderConfig($db)
{
    echo "<h2>Account Settings</h2>";
    $userInfo = $db->getUserInfo($_SESSION['email']);
    if ($userInfo->num_rows > 0) {
        while ($row = $userInfo->fetch_assoc()) {
            echo "<div class='adminUpload'>
                <form action='php/account/modifyUserInfo.php' method='post'>
                <label for='first-name'>First Name</label>
                <input id='name' name='name' type='text' value='" . $row["name"] . "' autocomplete='given-name'>

                <label for='last-name'>Last Name</label>
                <input id='surname' name='surname' type='text' value='" . $row["surname"] . "' autocomplete='family-name'>

                <label for='email'>Email</label>
                <input id='email' name='email' type='email' value='" . $row["email"] . "' autocomplete='email'>

                <label for='phone'>Phone</label>
                <input id='phoneNumber' name='phoneNumber' type='tel' value='" . $row["phoneNumber"] . "' autocomplete='tel'>

                <button name='submit' type='submit' class='button'>Save Changes</button>
            </form>
        </div>";
        }
    } else {
        echo "No user info found.";
    }
}

function renderSecurity($db)
{
    echo "<h2>Security</h2>
        <h2 class='section-title'>Change Password</h2>
        <div class='adminUpload'>
            <form action='php/account/changePassword.php' method='post' id='change-password-form'>
                <label for='new-pwd'>New Password</label>
                <input name='newPass' id='newPass' type='password' autocomplete='new-password'>

                <div id='password-requirements'>
                    <p id='requirement-1'>At least 8 Character</p>
                    <p id='requirement-2'>At least a Number</p>
                    <p id='requirement-3'>At least an Uppercase Letter</p>
                    <p id='requirement-4'>At least a Special Character</p>
                </div>

                <label for='conf-pwd'>Confirm New Password</label>
                <input name='confPass' id='confPass' type='password' autocomplete='new-password'>
               
                <p id='password-error' class='form-error-msg' style='color: red; display: none;'></p>
                <button type='submit' name='submit' class='button'>Update Password</button>
            </form>
        </div>";
}

function renderProducts($db)
{
    echo "<h2>Products</h2><br>";
    echo "<div class='adminUpload'>
                  <form id='product-upload' action='php/optionsPage.php' method='POST'>
                      <h2>Add a new product</h2>
                      <label for='nome'>Product Name:</label>
                      <input type='text' id='name' name='name' required/>
                      <label for='descrizione'>Product Description:</label>
                      <input type='text' id='description' name='description' required/>
                      <label for='prezzo'>Product Price:</label>
                      <input type='number' step='0.01' id='price' name='price' required/>
                      <label for='image-upload' class='button'>
                        <i class='fas fa-camera'></i> Seleziona immagine
                      </label>
                      <input type='file' id='image-upload' name='image' accept='image/png, image/jpg, image/jpeg, image/webp'>
                      <p id='file-name-display'> Nessun file selezionato </p><br>
                      <label for='inStock'>In Stock:</label>
                      <select id='inStock' name='inStock' required>
                          <option value='true'>Yes</option>
                          <option value='false'>No</option>
                      </select>
                      <input type='submit' name='submit' value='Add Product' class='button'/>
                  </form>
            </div><br>";
    echo "<div class='adminChoice'> <h3>Products Available</h3> <ul id='productsList'>";

    $result = $db->getProducts();
    if ($result && $result->num_rows > 0) {
        while ($p = $result->fetch_assoc()) {
            echo "<li class='card'> 
                    <h2>{$p['productName']}</h2> 
                    <div class='product'> 
                        <p>{$p['description']}</p>
                        <img src='assets/img/" . $p['imageUrl'] . "' 
                        style='width:100%; height:320px; object-fit: contain;'>
                    </div>
                    <p>In Stock: " . ($p['inStock'] == 1 ? 'Yes' : 'No') . "</p>
                    <p>Price: <strong>{$p['price']}€</strong></p> 
                    <form action='php/product/deleteProduct.php' method='post'>
                        <input type='hidden' name='id' value='" . $p['id'] . "'>
                        <input type='submit' name='submit' value='Delete' class='button'/>
                    </form>
                    <button class='button' onclick=\"window.location.href='modifyProduct.html?id={$p['id']}'\">Modify</button>
                </li>";
        }
    } else {
        echo "<li>No products available.</li>";
    }
    echo "</ul></div>";
}

function renderUsers($db)
{
    echo "<h2>Users</h2><br>";
        echo "<div class='adminChoice'> <h3>Registered Users</h3> <ul id='productsList'>";

        $result = $db->getUsers();
        if($result && $result->num_rows > 0){
        while($u = $result->fetch_assoc()){
            if($u['isAdmin'] == 1){
                echo "<li class='card'> 
                    <h2>{$u['username']}</h2> 
                    <p>Name: {$u['name']} {$u['surname']}</p>
                    <p>Email: {$u['email']}</p>
                    <p>Phone: {$u['phoneNumber']}</p>";
            }
        }
        } else {
            echo "<li>No users registered.</li>";
        }
        echo "</ul></div>";
        echo "<h2>Register admin account:</h2><br>
        <div class='adminUpload'> 
              <form action='php/account/register.php' method='post' id='access-form' novalidate>
              <div class='form-group'>

                <label for='email'>Email</label>
                <input type='email' id='email-input' name='email' autocomplete='email' required>
                <p class='form-error-msg'>Error placeholder</p>

            </div>
            <div class='form-group'>

                <label for='password'>Password</label>
                <input type='password' id='password-input' name='password' autocomplete='current-password' required>

                <p>Password must contain:</p>

                <div class='password-requirements'>
                    <p id='requirement-1'>
                        At least 8 Character
                    </p>
                    <p id='requirement-2'>
                        At least a Number
                    </p>
                    <p id='requirement-3'>
                        At least an Uppercase Letter
                    </p>
                    <p id='requirement-4'>
                        At least a Special Character
                    </p>
                </div>

            </div>
            <div class='form-group'>

                <label for='name'>Name</label>
                <input type='text' id='name-input' name='name' autocomplete='given-name' required>
                <p class='form-error-msg'>Error placeholder</p>

            </div>
            <div class='form-group'>

                <label for='surname'>Surname</label>
                <input type='text' id='surname-input' name='surname' autocomplete='family-name' required>
                <p class='form-error-msg'>Error placeholder</p>
            </div>
            <div class='form-group'>
                <label for='phone-input'>Phone Number</label>
                <input type='text' id='phone-input' name='phone' required>
                <p class='form-error-msg'>Error placeholder</p>
            </div>
            <input type='hidden' name='isAdmin' value='1'>
            <button type='submit'>Create admin account</button>
        </form></div><br>";

        echo "<h2>Add admin account:</h2> 
            <div class='adminUpload'>
            <form action='php/account/changeToAdmin.php' method='post'>
                <label for='username'>Username</label>
                <input id='username' name='username' type='text' required>

                <input type='submit' name='submit' value='Add as Admin'/>
            </form>";

}

function renderLogout($db)
{
    echo "<h2>Sei sicuro di voler effettuare il logout dall'account?</h2><br>";
    echo "<form action='./php/account/logout.php' method='POST'>";
    echo "<button type='submit' class='button'> Si, esci </button>";
    echo "</form>";
}
?>