<?php
require_once 'helpers/Session.php';
require_once 'helpers/Database.php';

Session::start();

// Access control
$isLoggedIn = Session::has('user_id');
$hasCharacter = Session::has('selected_character_id');

require_once 'controllers/CharacterController.php';

include_once 'views/head.php';

require_once 'controllers/LoginController.php';
require_once 'controllers/CharacterPickerController.php';
require_once 'controllers/RegisterController.php';
require_once 'controllers/NewsController.php';
require_once 'controllers/QuestController.php';
require_once 'controllers/NPCController.php';

// Instantiate Helper
$characterModel = new CharacterModel();
if (Session::get('selected_character_id')) {
    $characterId = Session::get('selected_character_id');
    $characterModel->checkAndUpdateLevel($characterId);
}

// Define routes

// Authentication Routes (Login/Logout)
$routes = [
    // LoginController
    'login' => 'LoginController@showLoginForm', // Show login form
    'login_check' => 'LoginController@login', // Handle login action
    'logout' => 'LoginController@logout', // Log out the user

    // RegisterController
    'register' => 'RegisterController@showRegisterForm', // Show registration form
    'store_user' => 'RegisterController@storeUser', // Store a new user
];

// Character Management Routes
$routes += [
    // CharacterPickerController
    'character_picker' => 'CharacterPickerController@index', // Show character picker
    'select_character' => 'CharacterPickerController@selectCharacter', // Select a character
    'create_character' => 'CharacterPickerController@createCharacter', // Show create character form
    'store_character' => 'CharacterPickerController@storeCharacter', // Save a new character
    'delete_character' => 'CharacterPickerController@deleteCharacter', // Delete a character

    // CharacterController
    'switch_character' => 'CharacterController@switchCharacter', // Switch between characters
    'view_character_profile' => 'CharacterController@viewProfile', // View character profile
];

// NPC Interaction Routes
$routes += [
    // NPCController
    'view_npcs' => 'NPCController@viewNPCsByLocation', // View NPCs by location
    'interact_with_npc' => 'NPCController@interactWithNPC', // Interact with an NPC
    'give_item' => 'NPCController@giveItem', // Give item to NPC
    'refuse_item_non_hostile' => 'NPCController@refuseItemNonHostile', // Refuse item for non-hostile NPC
    'refuse_item' => 'NPCController@refuseItem', // Refuse item for hostile NPC
    'start_quest' => 'NPCController@startQuest', // Start a quest from NPC
];

// Quest Management Routes
$routes += [
    // QuestController
    'quests' => 'QuestController@viewQuests', // View quests
    'start_side_quest' => 'QuestController@startSideQuest', // Start a side quest
    'start_main_story_quest' => 'QuestController@startMainStoryQuest', // Start a main story quest
    'view_active_quests' => 'QuestController@viewActiveQuests', // View active quests
    'end_quest' => 'QuestController@endQuest', // End a quest
    'fail_quest' => 'QuestController@failQuest', // Mark a quest as failed
];

// General Routes
$routes += [
    // NewsController
    'news' => 'NewsController@showNews', // Show news page
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
