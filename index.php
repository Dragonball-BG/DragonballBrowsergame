<?php
require_once 'helpers/Session.php';
require_once 'helpers/Database.php';

Session::start();

// Access control
$isLoggedIn = Session::has('user_id');
$hasCharacter = Session::has('selected_character_id');

require_once 'helpers/CharacterHelper.php';
require_once 'controllers/CharacterController.php';

include_once 'views/head.php';

require_once 'controllers/LoginController.php';
require_once 'controllers/CharacterPickerController.php';
require_once 'controllers/RegisterController.php';
require_once 'controllers/NewsController.php';
require_once 'controllers/QuestController.php';
require_once 'controllers/NPCController.php';

// Instantiate Helper
$characterHelper = new CharacterHelper();
if (Session::get('selected_character_id')) {
    $characterId = Session::get('selected_character_id');
    $characterHelper->checkAndUpdateLevel($characterId);
}

// Define routes
$routes = [
    'login' => 'LoginController@showLoginForm',
    'login_check' => 'LoginController@login',
    'logout' => 'LoginController@logout',
    'register' => 'RegisterController@showRegisterForm',
    'store_user' => 'RegisterController@storeUser',
    'character_picker' => 'CharacterPickerController@index', // Route to Character Picker
    'select_character' => 'CharacterPickerController@selectCharacter', // Select a character
    'switch_character' => 'CharacterController@switchCharacter',
    'create_character' => 'CharacterPickerController@createCharacter', // View to create a character
    'store_character' => 'CharacterPickerController@storeCharacter', // Save the new character
    'delete_character' => 'CharacterPickerController@deleteCharacter', // Route to delete a character
    'news' => 'NewsController@showNews', // Route to News
    'quests' => 'QuestController@viewQuests',
    'start_side_quest' => 'QuestController@startSideQuest',
    'start_main_story_quest' => 'QuestController@startMainStoryQuest',
    'interact_with_npc' => 'NPCController@interactWithNPC',
    'view_npcs' => 'NPCController@viewNPCsByLocation',
    'give_item' => 'NPCController@giveItem',
    'refuse_item_non_hostile' => 'NPCController@refuseItemNonHostile',
    'refuse_item' => 'NPCController@refuseItem',
    'start_quest' => 'NPCController@startQuest',
    'view_active_quests' => 'QuestController@viewActiveQuests',
    'end_quest' => 'QuestController@endQuest',
    'fail_quest' => 'QuestController@failQuest',
    'view_character_profile' => 'CharacterController@viewProfile',
];

$route = $_GET['route'] ?? 'login';

// Route guard helper
function routeGuard($condition, $redirectRoute) {
    if ($condition) {
        header("Location: index.php?route=$redirectRoute");
        exit;
    }
}

// Apply route guards
routeGuard(in_array($route, ['character_picker', 'create_character', 'dashboard']) && !$isLoggedIn, 'login');
routeGuard(in_array($route, ['dashboard']) && !$hasCharacter, 'character_picker');

// Route the request
if (array_key_exists($route, $routes)) {
    [$controllerName, $methodName] = explode('@', $routes[$route]);

    if (!class_exists($controllerName)) {
        echo "Controller not found: $controllerName";
    }

    $controller = new $controllerName();

    if (method_exists($controller, $methodName)) {
        $params = array_values(array_filter($_GET, fn($key) => $key !== 'route', ARRAY_FILTER_USE_KEY));

        call_user_func_array([$controller, $methodName], $params);
    } else {
        echo "Method not found in $controllerName: $methodName";
    }
} else {
    echo "Route not found: $route";
}

// Render content if available
$content = $content ?? '';
if (!empty($content)) {
    echo $content;
}

include_once 'views/footer.php';
?>
