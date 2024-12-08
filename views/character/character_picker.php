<h1 class="profile">Wähle deinen Charakter aus</h1>

<?php if (isset($_GET['error'])): ?>
    <div class="error-message">
        <?php 
            switch ($_GET['error']) {
                case 'max_characters':
                    echo "Du hast bereits die maximale Anzahl an Charakteren erstellt!";
                    break;
                case 'server_error':
                    echo "Ein unerwarteter Fehler ist aufgetreten. Bitte versuche es erneut.";
                    break;
                case 'delete_success':
                    echo "Charakter erfolgreich gelöscht.";
                    break;
                case 'delete_error':
                    echo "Das Passwort war falsch. Charakter konnte nicht gelöscht werden.";
                    break;
                case 'invalid_character':
                    echo "Ungültiger Charakter. Aktion nicht erlaubt.";
                    break;
                case 'missing_fields':
                    echo "Bitte fülle alle erforderlichen Felder aus.";
                    break;
                case 'name_taken':
                    echo "Dieser Charaktername ist bereits vergeben.";
                    break;
                case 'no_characters':
                    echo "Du hast noch keine Charaktere erstellt. Bitte erstelle einen Charakter.";
                    break;
                case 'invalid_request':
                    echo "Ungültige Anfrage. Bitte versuche es erneut.";
                    break;
                default:
                    echo "Ein unbekannter Fehler ist aufgetreten.";
            }
        ?>
    </div>
<?php endif; ?>

<div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; margin: 20px 0;">
    <?php foreach ($characters as $character): 
        $healthPercent = ($character['health'] / $character['max_health']) * 100;
        $manaPercent = ($character['mana'] / $character['max_mana']) * 100;
    ?>
        <div style="width: 200px; background: linear-gradient(to bottom, #fdfdfd, #eaeaea); border: 1px solid #121212; border-radius: 5px; padding: 10px; box-shadow: 0 0 10px #000000; text-align: center; transition: all 0.2s ease-in-out;">
            <div style="width: 100%; margin-bottom: 10px;">
                <img src="<?php echo htmlspecialchars($character['race_picture']); ?>" alt="<?php echo htmlspecialchars($character['race_name']); ?>" style="width: 100%; border-radius: 5px; border: 2px solid #209bcf;">
            </div>
            <h2 style="font-size: 14px; margin-bottom: 5px; color: #111111;"><?php echo htmlspecialchars($character['name']); ?></h2>
            <p style="font-size: 12px; margin: 3px 0; color: #333333;"><strong>Level:</strong> <?php echo htmlspecialchars($character['level']); ?></p>
            <p style="font-size: 12px; margin: 3px 0; color: #333333;"><strong>Rasse:</strong> <?php echo htmlspecialchars($character['race_name']); ?></p>
            <p style="font-size: 12px; margin: 3px 0; color: #333333;"><strong>KI:</strong> <?php echo htmlspecialchars($character['ki']); ?></p>
            <div style="margin: 5px 0;">
                <p style="font-size: 12px; margin: 3px 0; color: #333333;"><strong>Lebenspunkte:</strong></p>
                <div style="width: 100%; height: 10px; background-color: #eaeaea; border-radius: 5px; overflow: hidden; border: 1px solid #121212;">
                    <div style="width: <?php echo $healthPercent; ?>%; height: 100%; background: linear-gradient(to right, #ff4d4d, #cc0000);"></div>
                </div>
            </div>
            <div style="margin: 5px 0;">
                <p style="font-size: 12px; margin: 3px 0; color: #333333;"><strong>Kraftpunkte:</strong></p>
                <div style="width: 100%; height: 10px; background-color: #eaeaea; border-radius: 5px; overflow: hidden; border: 1px solid #121212;">
                    <div style="width: <?php echo $manaPercent; ?>%; height: 100%; background: linear-gradient(to right, #4d94ff, #0044cc);"></div>
                </div>
            </div>
            <a href="index.php?route=select_character&character_id=<?php echo htmlspecialchars($character['character_id']); ?>" class="character-select-button">Select</a>
            <form action="index.php?route=delete_character" method="POST" style="margin-top: 10px;">
                <input type="hidden" name="character_id" value="<?php echo htmlspecialchars($character['character_id']); ?>">
                <input type="password" name="password" placeholder="Passwort eingeben" required style="width: 90%; margin-bottom: 5px; border: 1px solid #ccc; border-radius: 3px; padding: 5px;">
                <button type="submit" class="character-delete-button">Löschen</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<a href="index.php?route=create_character" class="create-character-button">Create New Character</a>