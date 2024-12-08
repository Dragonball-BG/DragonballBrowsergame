<div id="character-info">
    <h1><?php echo htmlspecialchars($characterDetails['name']); ?></h1>
    <p><strong>KI:</strong> <?php echo htmlspecialchars($characterDetails['ki']); ?></p>
    <p><strong>Fame:</strong> <?php echo htmlspecialchars($characterDetails['fame']); ?></p>
    <p><strong>Zeni:</strong> <?php echo htmlspecialchars($characterDetails['zeni']); ?></p>
    <p><strong>Quest Punkte:</strong> <?php echo htmlspecialchars($characterDetails['quest_points']) . " / 20" ; ?></p>

    <!-- Health Bar -->
    <p><strong>Lebenspunkte:</strong></p>
    <p><?php echo htmlspecialchars($characterDetails['health']) . " / " . htmlspecialchars($characterDetails['max_health']); ?></p>
    <div class="bar-container">
        <div class="bar-health" style="width: <?php echo $healthPercent; ?>%;"></div>
    </div>

    <!-- Mana Bar -->
    <p><strong>Kraftpunkte:</strong></p>
    <p><?php echo htmlspecialchars($characterDetails['mana']) . " / " . htmlspecialchars($characterDetails['max_mana']); ?></p>
    <div class="bar-container">
        <div class="bar-mana" style="width: <?php echo $manaPercent; ?>%;"></div>
    </div>

    <!-- Level and EXP -->
    <p><strong>Level:</strong> <?php echo htmlspecialchars($characterDetails['level']); ?></p>
    <p><strong>Erfahrung:</strong> <?php echo htmlspecialchars($characterDetails['exp']) . " / " . htmlspecialchars($characterDetails['exp_to_next_level']); ?></p>
    <div class="bar-container">
        <div class="bar-exp" style="width: <?php echo $expPercent; ?>%;"></div>
    </div>

    <!-- Rank -->
    <p><strong>Rang:</strong> <?php echo htmlspecialchars($characterDetails['rank']); ?></p>

    <!-- Race and Attitude -->
    <p><strong>Rasse:</strong> <?php echo htmlspecialchars($characterDetails['race_name']); ?></p>
    <p><strong>Gesinnung:</strong> <?php echo htmlspecialchars($characterDetails['attitude_name']); ?></p>
    <?php
    $userId = Session::get('user_id');
    $stmt = $this->db->prepare(
        "SELECT character_id, name
        FROM characters
        WHERE user_id = ?"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $allCharacters = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    ?>

    <select onchange="location = this.value;">
        <option value="">Switch Character</option>
        <?php foreach ($allCharacters as $character): ?>
            <option value="index.php?route=switch_character&character_id=<?php echo $character['character_id']; ?>">
                <?php echo htmlspecialchars($character['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

</div>