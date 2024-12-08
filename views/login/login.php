<?php if (isset($_GET['error'])): ?>
    <div class="error-message">
        <?php 
            switch ($_GET['error']) {
                case 'missing_fields':
                    echo "Einige Felder wurden nicht ausgefüllt.";
                    break;
                case 'account_locked':
                    echo 'Dein Account ist gesperrt, bitte kontaktiere den Support.';
                    break;
                case 'invalid_credentials':
                    echo 'Username oder Passwort sind falsch.';
                    break;
                default:
                    echo "Ein Fehler unbekannter ist aufgetreten, versuche es bitte erneut.";
            }
        ?>
    </div>
<?php endif; ?>
<form class="reg" method="post" action="index.php?route=login_check">
    <h1>Login</h1>
    <p><input type="text" name="username" placeholder="Loginname"></p>
    <p><input type="password" name="password" placeholder="Passwort"></p>
    <p><input type="submit" value="Einloggen"></p>
</form>