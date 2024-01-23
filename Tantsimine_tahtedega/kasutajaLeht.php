<?php if (isset($_GET['code'])) {die(highlight_file(__FILE__, 1));}?>
<?php
require ('conf2.php');
session_start();

$registrationMessage = "";

if (isset($_REQUEST["komment"]) && isset($_REQUEST["uuskomment"]) && !empty($_REQUEST["uuskomment"]) && isAdmin()) {
    $newComment = trim($_REQUEST["uuskomment"]);

    if (!empty($newComment)) {
        global $yhendus;
        $kask = $yhendus->prepare("UPDATE tantsud SET kommentaarid=CONCAT(kommentaarid, ?) WHERE id=?");
        $kommentplus = $newComment;
        $kask->bind_param("si", $kommentplus, $_REQUEST["komment"]);
        $kask->execute();
        header("Location: $_SERVER[PHP_SELF]");
        $yhendus->close();
    }
}

//комментарий
if (isset($_REQUEST["komment"])) {
    if (!empty($_REQUEST["uuskomment"])) {
        global $yhendus;
        $kask = $yhendus->prepare("SELECT kommentaarid FROM tantsud WHERE id=?");
        $kask->bind_param("i", $_REQUEST["komment"]);
        $kask->execute();
        $kask->bind_result($currentComments);
        $kask->fetch();
        $kask->close();
        $newComment = $_REQUEST["uuskomment"] . "\n";
        $allComments = $currentComments . $newComment;

        $updatekask = $yhendus->prepare("UPDATE tantsud SET kommentaarid=? WHERE id=?");
        $updatekask->bind_param("si", $allComments, $_REQUEST["komment"]);
        $updatekask->execute();
        header("Location: $_SERVER[PHP_SELF]");
        $yhendus->close();
        exit();
    }
}

//пункты
if (isset($_REQUEST["heatants"])) {
    global $yhendus;
    $kask = $yhendus->prepare("UPDATE tantsud SET punktid=punktid+1 WHERE id=?");
    $kask->bind_param("i", $_REQUEST["heatants"]);
    $kask->execute();
}

if (isset($_REQUEST["halbtants"])) {
    global $yhendus;
    $kask = $yhendus->prepare("UPDATE tantsud SET punktid=punktid-1 WHERE id=?");
    $kask->bind_param("i", $_REQUEST["halbtants"]);
    $kask->execute();
}

if(isset($_REQUEST["paarinimi"]) && !empty($_REQUEST["paarinimi"]) && isAdmin()){
    global $yhendus;
    $kask=$yhendus->prepare("INSERT INTO tantsud (tantsupaar,ava_paev) values (?, NOW())");
    $kask->bind_param("s", $_REQUEST["paarinimi"]);
    $kask->execute();
    header("Location: $_SERVER[PHP_SELF]");
    $yhendus->close();
    exit();
}

if (isset($_POST["register"])) {
    $login = htmlspecialchars(trim($_POST['login']));
    $pass = htmlspecialchars(trim($_POST['pass']));

    $cool = "superpaev";
    $krypt = crypt($pass, $cool);

    global $yhendus;
    $kask = $yhendus->prepare("INSERT INTO kasutaja (kasutaja, parool) VALUES (?, ?)");

    try {
        $kask->bind_param("ss", $login, $krypt);
        $success = $kask->execute();

        if ($success) {
            $registrationMessage = "Registreerimine õnnestus!";
        } else {
            $registrationMessage = "Registreerimine ebaõnnestus. Palun proovige uuesti.";
        }
    } catch (mysqli_sql_exception $e) {
        $registrationMessage = "Registreerimine ebaõnnestus. Kasutajanimi on juba võetud.";
    }

    $kask->close();
}

function isAdmin()
{
    return isset($_SESSION['onAdmin']) && $_SESSION['onAdmin'];
}

?>

<!doctype html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tantsud tähtedega</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<header>
    <?php
    if (isset($_SESSION['kasutaja'])) {
        ?>
        <h3>Tere, <?= $_SESSION['kasutaja'] ?></h3>
        <a href="logout.php">Logi välja</a>
        <?php
    } else {
        ?>
        <a href="#" onclick="openModal()">Logi sisse</a>
        <a href="#" onclick="openRegisterModal()" id="register">Registreerimine</a>
        <?php
    }
    ?>
</header>
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Login</h2>
        <form action="login.php" method="post">
            <label for="login">Login:</label>

            <input type="text" id="login" name="login" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="pass" required>
            <br>
            <input type="submit" value="Login">
        </form>
    </div>
</div>

<div id="registerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRegisterModal()">&times;</span>
        <h2>Registreerimine</h2>
        <form method="post" action="kasutajaLeht.php" onsubmit="return validateRegistration()">
            <label for="login">Login:</label>
            <br>
            <input type="text" id="login" name="login" required>
            <br>
            <label for="pass">Parool:</label>
            <div class="password-input">
                <input type="password" id="pass" name="pass" required>
            </div>

            <label for="confirmPass">Parooli kinnitamine:</label>
            <br>
            <input type="password" id="confirmPass" name="confirmPass" required>
            <br>
            <input type="checkbox" id="showPass" onchange="togglePasswordVisibility()"> Näita salasõna
            <br>
            <br>
            <input type="submit" name="register" value="Register">
        </form>
    </div>
</div>


<h1>Tantsud tähtedega</h1>
<h2>Punktide lisamine</h2>
<?php
if (isset($_SESSION['kasutaja'])) {
    ?>
    <nav>
        <ul>
            <li><a href="kasutajaLeht.php">Kasutaja leht</a></li>
        </ul>
    </nav>
    <table>
        <tr>
            <th>Tansupaari nimi</th>
            <th>Punktid</th>
            <th>Kuupäev</th>
            <th>Kommentaarid</th>
            <th></th>
            <th>Lisa punkt</th>
            <th>Kustuta punkt</th>
        </tr>

        <?php
        global $yhendus;
        $kask = $yhendus->prepare("SELECT id, tantsupaar, punktid, ava_paev, kommentaarid FROM tantsud WHERE avalik=1");
        $kask->bind_result($id, $tantsupaar, $punktid, $avapaev, $komment);
        $kask->execute();
        while ($kask->fetch()) {
            echo "<tr>";
            $tantsupaar = htmlspecialchars($tantsupaar);
            echo "<td>" . $tantsupaar . "</td>";
            echo "<td>" . $punktid . "</td>";
            ?>
            <?php
            if (!isAdmin()) { 
            echo "<td>" . $avapaev . "</td>";
                ?>
                <?php
            } else {
                echo "<td>";
            }
            ?>
            <?php
            echo "<td>" . nl2br(htmlspecialchars($komment)) . "</td>";
            echo "<td>
            <form action='?'>
                    <input type='hidden' value='$id' name='komment'>
                    <input type='text' name='uuskomment' id='uuskomment'>
                    <input type='submit' value='OK'>
            </form>
        ";
            ?>
            <?php
            if (!isAdmin()) {

                echo "<td><a href='?heatants=$id'>Lisa +1punkt</a></td>";
                echo "<td><a href='?halbtants=$id'>Kustuta -1punkt</a></td>";
                ?>
                <?php
            } else {
                echo "<td>";

            }
            ?>
            <?php
        }
        ?>
    </table>
    <?php
    if (!isAdmin()) {
        ?>
        <br>
        <form action="?">
            <label for="paarinimi">Lisa uus paar</label>
            <input type="text" name="paarinimi" id="paarinimi">
            <input type="submit" value="Lisa paar">
        </form>
        <?php
    }
    ?>
    <?php
}
?>
</body>
<?php
if (!empty($registrationMessage)) {
    echo '<script>alert("' . $registrationMessage . '");</script>';
}
?>
<script>
    function openModal() {
        document.getElementById('loginModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('loginModal').style.display = 'none';
    }

    window.onclick = function (event) {
        var modal = document.getElementById('loginModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    function openRegisterModal() {
        document.getElementById('registerModal').style.display = 'block';
    }

    function closeRegisterModal() {
        document.getElementById('registerModal').style.display = 'none';
    }

    window.onclick = function (event) {
        var modal = document.getElementById('registerModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    function validateRegistration() {
        var password = document.getElementById('pass').value;
        var confirmPass = document.getElementById('confirmPass').value;

        if (password !== confirmPass) {
            alert('Paroolid ei vasta.');
            return false;
        }

        var regex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
        if (!regex.test(password)) {
            alert('Salasõna peab sisaldama vähemalt 8 märki, sealhulgas ühte suurtähte ja ühte numbrit.');
            return false;
        }

        return true;
    }

    function togglePasswordVisibility() {
        var passInput = document.getElementById('pass');
        var confirmPassInput = document.getElementById('confirmPass');
        var showPassCheckbox = document.getElementById('showPass');

        passInput.type = showPassCheckbox.checked ? 'text' : 'password';
        confirmPassInput.type = showPassCheckbox.checked ? 'text' : 'password';
    }
</script>

</html>
