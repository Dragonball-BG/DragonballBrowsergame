<h1>NPCs in This Location</h1>

<div class="npc-container">
    <?php foreach ($npcs as $npc): ?>
        <div class="npc-card">
            <div class="npc-info">
                <img src="<?php echo htmlspecialchars($npc['image']); ?>" alt="<?php echo htmlspecialchars($npc['name']); ?>" class="npc-image">
                <div class="npc-dialogue">
                    <p>"Hello! I am <?php echo htmlspecialchars($npc['name']); ?>."</p>
                </div>
            </div>
            <div class="npc-details">
                <p><?php echo htmlspecialchars($npc['description']); ?></p>
                <div class="npc-actions">
                    <a href="index.php?route=interact_with_npc&npc_id=<?php echo $npc['npc_id']; ?>" class="npc-interact-button">Talk</a>
                    <?php if ($npc['is_fightable']): ?>
                        <a href="index.php?route=start_combat&npc_id=<?php echo $npc['npc_id']; ?>" class="npc-fight-button">Fight</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
