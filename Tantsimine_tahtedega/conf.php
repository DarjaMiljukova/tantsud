<?php
$kasutaja='darja';
$serverinimi='localhost';
$parool='123456';
$andmebaas='darja';
$yhendus=new mysqli($serverinimi,$kasutaja,$parool,$andmebaas);
$yhendus->set_charset('UTF8');