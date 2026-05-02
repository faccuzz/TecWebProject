<?php
include_once 'db.php';
session_start();
/*session_start();
if(!isset($_SESSION['email'])){
    header("Location: ../index.html");
    exit();
}*/

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
    case 'wishlist':
        renderWishlist($db);
        break;
    case 'security':
        renderSecurity($db);
        break;
    case 'products':
        renderProducts($db);
        break;
    case 'users':
        renderUsers($db);
        break;
    case 'logout':
        renderLogout($db);
        break;
}
$db->close();

function renderOrders($db)
{
    echo "<h2>Order history</h2>";
    $products = $db->getOrderHistory('user');
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
                <form action='php/modifyUserInfo.php' method='post'>
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

function renderWishlist($db)
{
    echo "<h2>Wishlist</h2>";
    $wishlist = $db->getWishlist('user');
    if ($wishlist->num_rows > 0) {
        while ($row = $wishlist->fetch_assoc()) {
            echo "<ul id='productsList'><li>" . "<p>" . $row["productName"] . "</p><p>" . $row["price"] . "</p><p>" . $row["description"] . "</p></li></ul>";
        }
    } else {
        echo "No products added to your wishlist yet.";
    }
}

function renderSecurity($db)
{
    echo "<h2>Security</h2>
        <h2 class='section-title'>Change Password</h2>
        <div class='adminUpload'>
            <form action='php/changePassword.php' method='post'>
                <label for='new-pwd'>New Password</label>
                <input id='newPass' type='password' autocomplete='new-password'>

                <label for='conf-pwd'>Confirm New Password</label>
                <input id='confPass' type='password' autocomplete='new-password'>

                <button type='submit' class='button'>Update Password</button>
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
                    <button onclick=\"deleteProduct('{$p['id']}')\">Delete</button>
                    <button onclick=\"modifyProduct('{$p['id']}')\">Modify</button></li>";
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
        echo "<h2>Add admin account:</h2> 
        <div class='adminUpload'>
            <form action='php/register.php' method='post'>
                <label for='username'>Username</label>
                <input id='username' name='username' type='text' required>

                <label for='password'>Password</label>
                <input id='password' name='password' type='password' required>

                <label for='name'>First Name</label>
                <input id='name' name='name' type='text' required>

                <label for='surname'>Last Name</label>
                <input id='surname' name='surname' type='text' required>

                <label for='email'>Email</label>
                <input id='email' name='email' type='email' required>

                <label for='phoneNumber'>Phone Number</label>
                <input id='phoneNumber' name='phoneNumber' type='tel'>

                <label for='street'>Street &amp; Number</label>
                <input id='street' name='street' type='text' required>

                <label for='city'>City</label>
                <input id='city' name='city' type='text' required>

                <label for='postalCode'>Postal Code</label>
                <input id='postalCode' name='postalCode' type='text' required>

                <label for='province'>Province</label>
                <input id='province' name='province' type='text' required>

                <label for='state'>State</label>
                <input id='state' name='state' type='text' required>
                <input type='hidden' name='isAdmin' value='true'>

                <button name='register' type='submit' class='button'>Add Admin</button>
            </form></div><br>";

        echo "<h2>Add admin account:</h2> 
            <div class='adminUpload'>
            <form action='php/changetoAdmin.php' method='post'>
                <label for='username'>Username</label>
                <input id='username' name='username' type='text' required>

                <input type='submit' name='submit' value='Add as Admin'/>
            </form>";

}

function renderLogout($db)
{
    echo "<h2>Sei sicuro di voler effettuare il logout dall'account?</h2><br>";
    echo "<form action='./php/account-managing/logout.php' method='POST'>";
    echo "<button type='submit' class='button'> Si, esci </button>";
    echo "</form>";
}
?>