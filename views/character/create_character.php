
<style>
    /* Style for race preview */
    .race-preview {
        text-align: center;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .race-preview img {
        max-width: 200px;
        max-height: 200px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .race-preview p {
        font-size: 14px;
        margin-top: 10px;
        color: #444;
    }
</style>
<script>
    // JavaScript to dynamically update the race preview
    function updateRacePreview() {
        const raceDropdown = document.getElementById('race');
        const racePreview = document.getElementById('racePreview');
        const raceImage = racePreview.querySelector('img');
        const raceName = racePreview.querySelector('p');

        const selectedOption = raceDropdown.options[raceDropdown.selectedIndex];
        const racePicture = selectedOption.getAttribute('data-picture');
        const raceNameText = selectedOption.text;

        if (racePicture) {
            raceImage.src = racePicture;
            raceImage.alt = raceNameText;
            raceName.textContent = raceNameText;
            racePreview.style.display = 'block';
        } else {
            racePreview.style.display = 'none';
        }
    }
</script>
<?php if (isset($_GET['error'])): ?>
    <div class="error-message">
        <?php 
            switch ($_GET['error']) {
                case 'name_taken':
                    echo "Character name is already taken. Please choose another name.!";
                    break;
                default:
                    echo "An unknown error occurred.";
            }
        ?>
    </div>
<?php endif; ?>
<form class="reg" action="index.php?route=store_character" method="post">
    <h1>Charakter Erstellung</h1>

    <!-- Character Name -->
    <label for="character_name" style="margin-left: 15px;">CharacterName:</label>
    <input type="text" id="character_name" name="character_name" placeholder="Enter character name" required>

    <!-- Race Selection -->
    <label for="race" style="margin-left: 15px;">Race:</label>
    <select id="race" name="race_id" required onchange="updateRacePreview()">
        <option value="" disabled selected>Select your race</option>
        <?php foreach ($races as $race): ?>
            <option value="<?= $race['race_id'] ?>" data-picture="<?= htmlspecialchars($race['picture']) ?>">
                <?= htmlspecialchars($race['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Race Preview -->
    <div id="racePreview" class="race-preview" style="display: none;">
        <img src="" alt="Race Preview">
        <p></p>
    </div>

    <!-- Attitude Selection -->
    <label for="attitude" style="margin-left: 15px;">Attitude:</label>
    <select id="attitude" name="attitude_id" required>
        <option value="" disabled selected>Select your attitude</option>
        <?php foreach ($attitudes as $attitude): ?>
            <option value="<?= $attitude['attitude_id'] ?>"><?= htmlspecialchars($attitude['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <!-- Submit Button -->
    <input type="submit" value="Create Character">
</form>
