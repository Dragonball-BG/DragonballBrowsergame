<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dragonball New Adventures - Das Browsergame</title>
    <link rel="stylesheet" href="design/style.css">
</head>
<body>
<div id="header"></div>
<div id="wrapper">
    <div id="menu_left">
        <?php if (!$isLoggedIn): ?>
            <!-- Menu for users not logged in -->
            <p><h1>Login</h1></p>
            <p><a href="index.php?route=login">Zum Login</a></p>
            <p><a href="index.php?route=register">Zur Registrierung</a></p>
        <?php elseif ($isLoggedIn && !$hasCharacter): ?>
            <!-- Menu for logged-in users without a selected character -->
            <p><h1>Character</h1></p>
            <p><a href="index.php?route=create_character">Charakter Erstellung</a></p>
            <p><a href="index.php?route=character_picker">Charakter Auswählen</a></p>
        <?php elseif ($isLoggedIn && $hasCharacter): ?>
            <!-- Menu for logged-in users with a selected character -->
            <?php 
            $characterController = new CharacterController();
            $characterLocationId = $characterController->getCharacterLocationId();
            $characterController->displayCharacterDetails();
            ?>
            <p><h1>Profil</h1></p>
            <p><a href="index.php?route=logout" class="half1">Logout</a><a href="index.php?route=view_character_profile" class="half2">Profil</a></p>
            <p><a href="index.php?route=daten_aendern_form">Daten ändern</a></p>
            <p><h1>Location</h1></p>
            <a href="index.php?route=view_npcs&location_id=<?php echo $characterLocationId; ?>" class="location-npcs-button">View NPCs</a>
            <p><a href="index.php?route=view_active_quests">Quests anschauen</a></p>
        <?php endif; ?>
    </div>
    <div id="content_bg">
        <div id="content">
            <div id="menu_top">
                <a href="index.php">» News&nbsp;&nbsp;&nbsp;</a>
                <a href="https://discord.gg/tZDndFj7he" target="_blank">» Discord&nbsp;&nbsp;&nbsp;</a>
                <a href="index.php?route=infos">» Infos&nbsp;&nbsp;&nbsp;</a>
                <a href="index.php?route=rangliste">» ??? Kämpfer&nbsp;&nbsp;&nbsp;</a>
                <a href="#">» XXX Clans&nbsp;&nbsp;&nbsp;</a>
                <a href="index.php?route=online_user">» ??? User Online</a>
            </div>