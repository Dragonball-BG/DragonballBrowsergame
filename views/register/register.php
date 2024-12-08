<?php if (isset($_GET['error'])): ?>
    <div class="error-message">
        <?php 
            switch ($_GET['error']) {
                case 'missing_fields':
                    echo 'Alle Felder sind required.';
                    break;
                case 'invalid_email':
                    echo 'Bitte gib eine valide E-Mail Addresse an.';
                    break;
                case 'user_exists':
                    echo 'Username oder E-Mail existieren bereits.';
                    break;
                case 'server_error':
                    echo 'Ein Fehler unbekannter ist aufgetreten, versuche es bitte erneut.';
                    break;
                default:
                    echo 'Ein Fehler unbekannter ist aufgetreten, versuche es bitte erneut.';
            }
        ?>
    </div>
<?php endif; ?>
<form class="reg" method="post" action="index.php?route=store_user">
    <h1>Registrierung</h1>
    <p><input type="text" name="login_name" placeholder="Login Name"></p>
    <p><input type="text" name="email" placeholder="E-Mail Adresse"></p>
    <p><input type="password" name="password" placeholder="Passwort"></p>
    <p><input type="password" name="password_confirm" placeholder="Passwort wiederholen"></p>
    <p><input type="text" name="betakey" placeholder="BetaKey"></p>
    <p><input type="submit" value="Registrieren"></p>
</form>