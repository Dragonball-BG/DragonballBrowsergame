<h1>Aktive Quests</h1>

<div class="active-quests-container">
    <?php foreach ($quests as $quest): ?>
        <div class="quest-card">
            <h2><?php echo htmlspecialchars($quest['quest_name']); ?></h2>
            <p><?php echo htmlspecialchars($quest['description']); ?></p>
            <p><strong>Rewards:</strong></p>
            <ul>
                <li>Exp: <?php echo $quest['exp_reward']; ?></li>
                <li>Fame: <?php echo $quest['fame_reward']; ?></li>
                <li>Zeni: <?php echo $quest['zeni_reward']; ?></li>
            </ul>
            <?php if ($quest['can_end']): ?>
                <a href="index.php?route=end_quest&quest_id=<?php echo $quest['character_quest_id']; ?>" class="quest-button">Quest abgeben</a>
            <?php elseif ($quest['can_fail']): ?>
                <a href="index.php?route=fail_quest&quest_id=<?php echo $quest['character_quest_id']; ?>" class="quest-button fail">Quest fehlgeschlagen</a>
            <?php else: ?>
                <p>Die Quest lauft noch und kann nicht abgegeben oder abgebrochen werden.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>
