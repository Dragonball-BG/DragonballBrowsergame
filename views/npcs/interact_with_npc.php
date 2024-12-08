<div class="npc-interaction">
    <div class="npc-info">
        <img src="<?php echo htmlspecialchars($npc['image']); ?>" alt="<?php echo htmlspecialchars($npc['name']); ?>" class="npc-image">
        <div class="npc-dialogue">
            <p><?php echo htmlspecialchars($randomDialogue); ?></p>
        </div>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="message-box">
            <?php 
                switch ($_GET['message'] ?? $_GET['error']) { // Check both message and error keys
                    case 'item_given':
                        echo "Du hast den geforderten Gegenstand gegeben, und der NPC ist jetzt freundlich.";
                        break;
                    case 'item_taken':
                        echo "Du hast den Kampf verloren, und der NPC hat den Gegenstand genommen.";
                        break;
                    case 'fight_won_item_kept':
                        echo "Du hast den Kampf gewonnen, und der Gegenstand bleibt bei dir.";
                        break;
                    case 'item_refused_relationship_deducted':
                        echo "Du hast den Gegenstand verweigert. Beziehungs-Punkte wurden abgezogen.";
                        break;
                    case 'insufficient_quest_points':
                        echo "Du hast nicht genug Questpunkte, um diese Quest zu starten.";
                        break;
                    default:
                        echo "Unbekannte Aktion.";
                }
            ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($quests)): ?>
        <div class="quest-list">
            <?php foreach ($quests as $quest): ?>
                <div class="quest-card">
                    <h2><?php echo htmlspecialchars($quest['quest_name']); ?></h2>
                    <p><?php echo htmlspecialchars($quest['quest_description']); ?></p>
                    <p><strong>Rewards:</strong></p>
                    <ul>
                        <li>Exp: <?php echo $quest['exp_reward']; ?></li>
                        <li>Fame: <?php echo $quest['fame_reward']; ?></li>
                        <li>Zeni: <?php echo $quest['zeni_reward']; ?></li>
                    </ul>
                    <a href="index.php?route=start_quest&quest_id=<?php echo $quest['quest_id']; ?>&npc_id=<?php echo $npcId; ?>" class="start-quest-button">Start Quest</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Der NPC hat derzeit keine Quest fuer dich.</p>
    <?php endif; ?>
</div>
