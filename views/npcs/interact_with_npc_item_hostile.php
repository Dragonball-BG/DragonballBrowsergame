<div class="npc-interaction">
    <div class="npc-info">
        <img src="<?php echo htmlspecialchars($npc['image']); ?>" alt="<?php echo htmlspecialchars($npc['name']); ?>" class="npc-image">
        <div class="npc-dialogue">
            <p><?php echo htmlspecialchars($randomDialogue); ?></p>
        </div>
    </div>
    <div class="npc-actions">
        <a href="<?php echo htmlspecialchars($giveItemLink); ?>" class="npc-give-item-button">Give Item</a>
        <a href="<?php echo htmlspecialchars($refuseItemLink); ?>" class="npc-refuse-button">Refuse</a>
    </div>
</div>
