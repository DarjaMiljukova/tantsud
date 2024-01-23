<?php if (isset($_GET['code'])) {die(highlight_file(__FILE__, 1));}?>
<?php
require ('conf2.php');
session_start();


//punktide lisamine
if(isset($_REQUEST["punktid0"])){
    global $yhendus;
    $kask=$yhendus->prepare("UPDATE tantsud SET punktid=0 WHERE id=?");
    $kask->bind_param("i", $_REQUEST["punktid0"]);
    $kask->execute();
}

if(isset($_REQUEST["peitmine"])){
    global $yhendus;
    $kask=$yhendus->prepare("UPDATE tantsud SET avalik=0 WHERE id=?");
    $kask->bind_param("i", $_REQUEST["peitmine"]);
    $kask->execute();
}

if(isset($_REQUEST["naitmine"])){
    global $yhendus;
    $kask=$yhendus->prepare("UPDATE tantsud SET avalik=1 WHERE id=?");
    $kask->bind_param("i", $_REQUEST["naitmine"]);
    $kask->execute();
}

if (isset($_REQUEST["kustutapaar"])) {
    global $yhendus;
    $kask = $yhendus->prepare("DELETE FROM tantsud WHERE id=?");
    $kask->bind_param("i", $_REQUEST["kustutapaar"]);
    $kask->execute();
}

if (isset($_REQUEST["kustutakomment"])){
    global $yhendus;
    $kask = $yhendus->prepare("UPDATE tantsud SET kommentaarid='' WHERE id=?");
    $kask->bind_param("i", $_REQUEST["kustutakomment"]);
    $kask->execute();
    header("Location: $_SERVER[PHP_SELF]");
    $yhendus->close();
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
    if(isset($_SESSION['kasutaja'])){
        ?>
        <h3>Tere, <?="$_SESSION[kasutaja]"?></h3>
        <a href="logout.php">Logi välja</a>
        <?php
    } else {
        ?>
        <a href="login.php">Logi sisse</a>
        <?php
    }
    ?>
</header>
<h1>Tantsud tähtedega</h1>
<h2>AdministreerimisLeht</h2>
<nav>
    <ul>
        <li><a href="adminLeht.php">Admin leht</a></li>
        <li><a href="kasutajaLeht.php">Kasutaja leht</a></li>
    </ul>
</nav>
<br>
<br>
<table>
    <tr>
        <th>Tansupaari nimi</th>
        <th>Punktid</th>
        <th>Kommentaarid</th>
        <th>Avalik</th>
        <th></th>
        <th></th>
        <th>Kustuta paar</th>
        <th>Kustuta kommentaarid</th>
    </tr>

<?php
global $yhendus;
    $kask=$yhendus->prepare("SELECT id, tantsupaar, punktid, ava_paev, kommentaarid, avalik FROM tantsud");
    $kask->bind_result($id, $tantsupaar, $punktid, $paev, $komment, $avalik);
    $kask->execute();
    while($kask->fetch()){
        $tekst="Näita";
        $seisund="naitmine";
        $tekst2="Kasutaja ei näe";
        if($avalik==1){
            $tekst="Peida";
            $seisund="peitmine";
            $tekst2="Kasutaja näeb";
        }


        echo "<tr>";
        $tantsupaar=htmlspecialchars($tantsupaar);
        echo "<td>".$tantsupaar."</td>";
        echo "<td>".$punktid."</td>";

        echo "<td>".$komment."</td>";
        echo "<td>".$avalik."/".$tekst2."</td>";
        echo "<td><a href='?punktid0=$id'>Punktid Nulliks!</a></td>";
        echo "<td><a href='?$seisund=$id'>$tekst</a></td>";
        echo "<td><a href='?kustutapaar=$id'>Kustuta paar</a></td>";
        echo "<td><a href='?kustutakomment=$id'>Kustuta kommentaarid</a></td>";
        echo "</tr>";
    }
?>
</table>
</body>
</html>