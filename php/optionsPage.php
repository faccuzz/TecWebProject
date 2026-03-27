<?php 
    include_once 'db.php';
    header('Content-Type: application/json');

    session_start();
    if(!isset($_SESSION['email'])){
        header("Location: ../index.html");
        exit();
    }
    
    $db = new database();
    $db->connect();
    $section = $_GET['section'] ?? '';
    switch($section){
        case 'products':
            renderProducts($db);
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
    }
    $db->close();

    function renderProducts($db){
        echo "<h2>Order history</h2>";
        $products = $db->getOrderHistory('user');
        if ($products->num_rows > 0) {
            while($row = $products->fetch_assoc()) {
                echo "<ul id='productsList'><li>" ."<p>" . $row["orderID"]. "</p><p>" . $row["orderDate"]. "</p><p>" . $row["productName"]. "</p><p>" . $row["price"]. "</p><p>" . $row["quantity"]. "</p></li></ul>";
            }
        } else {
            echo "No products found.";
        }
    }

    function renderConfig($db){
        echo "<h2>Account Settings</h2>";
        $userInfo = $db->getUserInfo('user');
        if ($userInfo->num_rows > 0) {
            while($row = $userInfo->fetch_assoc()) {
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

                <label for='street'>Street &amp; Number</label>
                <input id='street' name='street' type='text' value='" . $row["street"] . "' autocomplete='street-address'>

                <label for='city'>City</label>
                <input id='city' name='city' type='text' value='" . $row["city"] . "' autocomplete='address-level2'>

                <label for='postal'>Postal Code</label>
                <input id='postalCode' name='postalCode' type='text' value='" . $row["postalCode"] . "' autocomplete='postal-code'>

                <button name='submit' type='submit' class='button'>Save Changes</button>
            </form>
        </div>";
            }
        } else {
            echo "No user info found.";
        }
    }

    function renderWishlist($db){
        echo "<h2>Wishlist</h2>";
        $wishlist = $db->getWishlist('user');
        if ($wishlist->num_rows > 0) {
            while($row = $wishlist->fetch_assoc()) {
                echo "<ul id='productsList'><li>" ."<p>" . $row["productName"]. "</p><p>" . $row["price"]. "</p><p>" . $row["description"]. "</p></li></ul>";
            }
        } else {
            echo "No products added to your wishlist yet.";
        }
    }

    function renderSecurity($db){
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
?>
