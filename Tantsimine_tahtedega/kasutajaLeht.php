<?php
require ('conf.php');
session_start();


//kommentaaride lisamine

if(isset($_REQUEST["komment"])){
    if(!empty($_REQUEST["uuskomment"])){
        global $yhendus;
        $kask=$yhendus->prepare("UPDATE tantsud SET kommentaarid=CONCAT(kommentaarid,?) WHERE id=?");
        $kommentplus=$_REQUEST["uuskomment"]. "\n";
        $kask->bind_param("is", $kommentdplus, $_REQUEST["komment"]);
        $kask->execute();
        header("Location: $_SERVER[PHP_SELF]");
        $yhendus->close();
        //exit();

    }

}

//punktide lisamine
if(isset($_REQUEST["heatants"])){
    global $yhendus;
    $kask=$yhendus->prepare("UPDATE tantsud SET punktid=punktid+1 WHERE id=?");
    $kask->bind_param("i", $_REQUEST["heatants"]);
    $kask->execute();
}

//punktide kustutamine
if(isset($_REQUEST["halbtants"])){
    global $yhendus;
    $kask=$yhendus->prepare("UPDATE tantsud SET punktid=punktid-1 WHERE id=?");
    $kask->bind_param("i", $_REQUEST["halbtants"]);
    $kask->execute();
}

if(isset($_REQUEST["paarinimi"]) && !empty($_REQUEST["paarinimi"]) && isAdmin()){
    global $yhendus;
    $kask=$yhendus->prepare("INSERT INTO tantsud(tantsupaar, ava_paev) VALUES(?, NOW())");
    $kask->bind_param("s", $_REQUEST["paarinimi"]);
    $kask->execute();
    header("Location: $_SERVER[PHP_SELF]");
    $yhendus->close();
    //exit();
}

if (isset($_REQUEST["kustutapaar"])) {
    global $yhendus;
    $kask = $yhendus->prepare("DELETE FROM tantsud WHERE id=?");
    $kask->bind_param("i", $_REQUEST["kustutapaar"]);
    $kask->execute();
}

function isAdmin(){
    return isset ($_SESSION['onAdmin']) && $_SESSION['onAdmin'];
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

            <label for="password">Password:</label>
            <input type="password" id="password" name="pass" required>

            <input type="submit" value="Login">
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
        <th>Lisa punkt</th>
        <th>Kustuta punkt</th>
        <th>Kustuta paar</th>
    </tr>

<?php
global $yhendus;
    $kask=$yhendus->prepare("SELECT id, tantsupaar, punktid, ava_paev, kommentaarid FROM tantsud WHERE avalik=1");
    $kask->bind_result($id, $tantsupaar, $punktid, $avapaev, $komment);
    $kask->execute();
    while($kask->fetch()){
        echo "<tr>";
        $tantsupaar=htmlspecialchars($tantsupaar);
        echo "<td>".$tantsupaar."</td>";
        echo "<td>".$punktid."</td>";
        echo "<td>".$avapaev."</td>";
        echo "<td>".nl2br(htmlspecialchars($komment))."</td>";
        echo "<td>
<form action='?'>
        <input type='hidden' value='$id' name='komment'>
        <input type='text' name='uuskomment' id='uuskomment'>
        <input type='submit' value='OK'>
</form>
        ";
?>
        <?php
        if(!isAdmin()){

        echo "<td><a href='?heatants=$id'>Lisa +1punkt</a></td>";
        echo "<td><a href='?halbtants=$id'>Kustuta -1punkt</a></td>";
            ?>
            <?php
        }
        else{
            echo "<td>";
            echo "<td>";
        }
        ?>
    <?php
    if(isAdmin()){
               echo "<td><a href='?kustutapaar=$id'>Kustuta paar</a></td>";
        echo "</tr>";
    }
    else{
        echo "<td>";
    }
        ?>
    <?php
    }
    ?>



</table>
    <?php
    if(isAdmin()){
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
</script>

</html>