<?php
$kasutaja='d123180_darja';
$serverinimi='d123180.mysql.zonevs.eu';
$parool='BloodyKiller22032006';
$andmebaas='d123180_admebaas';
$yhendus=new mysqli($serverinimi,$kasutaja,$parool,$andmebaas);
$yhendus->set_charset('UTF8');