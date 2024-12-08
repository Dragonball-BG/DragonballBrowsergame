<div id="profile">
    <h1 class="profile">Deine Charaktere</h1>
    <div class="character-container">
        <?php foreach ($characters as $character): ?>
            <div class="character-card <?php if ($character['character_id'] == $activeCharacter['character_id']) echo 'active'; ?>">
                <div class="character-header">
                    <img src="<?= htmlspecialchars($character['race_picture']) ?>" alt="<?= htmlspecialchars($character['race_name'] ?: 'Unknown') ?>" class="race-image">
                    <h2><?= htmlspecialchars($character['name']) ?></h2>
                </div>
                <div class="character-details">
                    <p><strong>Level:</strong> <?= htmlspecialchars($character['level']) ?></p>
                    <p><strong>EXP:</strong> <?= htmlspecialchars($character['exp']) ?></p>
                    <div class="bar-container">
                        <div class="bar-health" style="width: <?= ($character['health'] / $character['max_health']) * 100 ?>%;"></div>
                    </div>
                    <p><strong>Health:</strong> <?= htmlspecialchars($character['health']) ?>/<?= htmlspecialchars($character['max_health']) ?></p>
                    <div class="bar-container">
                        <div class="bar-mana" style="width: <?= ($character['mana'] / $character['max_mana']) * 100 ?>%;"></div>
                    </div>
                    <p><strong>Mana:</strong> <?= htmlspecialchars($character['mana']) ?>/<?= htmlspecialchars($character['max_mana']) ?></p>
                    <p><strong>Ki:</strong> <?= htmlspecialchars($character['ki']) ?></p>
                    <p><strong>Zeni:</strong> <?= htmlspecialchars($character['zeni']) ?></p>
                </div>
                <div class="character-footer">
                    <p><strong>Race:</strong> <?= htmlspecialchars($character['race_name'] ?: 'Unknown') ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($character['location_name'] ?: 'Unknown') ?></p>
                    <p><strong>Status:</strong> <?= $character['alive'] ? 'Alive' : 'Deceased' ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
