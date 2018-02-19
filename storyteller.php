<?php

// Other configuration settings. You can override these in config.php if you wish
define("MAX_EXECUTIONS",30);
require('config.php');
require('commands.php');
register_commands();

// Check the incoming data for the secret slack token
if ($_POST['token'] != SLACK_TOKEN) {
    header('HTTP/1.0 403 Forbidden');
    die('Access Denied. Token does not match');
}

// Uncomment for command-line debugging
/*if (isset($argv[1])) {
    $_POST['text'] = implode(" ",array_slice($argv,1));
    $_POST['trigger_word'] = '!';
}*/

// Note $commandlist is referenced as a global variable in the below functions.

$player = load();

// Split the command list by semi-colons. Allows multiple commands to be queued
// Note, some commands will queue other commands
$commandlist = explode(";",$_POST['text']);

$executions = 0;
while (sizeof($commandlist) > 0)
{
    // Process the next command in the list
    processcommand(array_shift($commandlist),$player);

    // If stamina ever drops to less than 1, the player if dead
    // Stop processing any queued commands and tell the player they are dead
    if ($player['stam'] < 1) {
        sendqmsg("_*You are dead.*_ :skull:",":skull:");
        break;
    }

    // Stop processing the queue after MAX_EXECUTIONS
    if ($executions++ > MAX_EXECUTIONS) {
        break;
    }
}

save($player);

die();

/// ----------------------------------------------------------------------------
/// Functions

// Process command text and call command's function
function processcommand($command, &$player)
{
    global $commandslist;

    // Split by whitespace
    // $cmd[0] is the command
    // $cmd[1...] are the parameters
    $cmd = preg_split('/\s+/', trim($command));

    // Remove trigger word from command
    $cmd[0] = substr($cmd[0],strlen($_POST['trigger_word']));
    $cmd[0] = trim(strtolower($cmd[0]));

    // pad the array, so we can safely check param values
    array_pad($cmd,10,null);

    // Special case for quick page lookup
    if (is_numeric($cmd[0])) {
        $cmd[1] = $cmd[0];
        $cmd[0] = 'page';
    }

    // look for a command function to call
    if (array_key_exists($cmd[0],$commandslist)) {
        call_user_func_array($commandslist[$cmd[0]],array($cmd,&$player));
    }
}

/// register new command
function register_command($name, $function)
{
    global $commandslist;

    if (!is_array($commandslist)) {
        $commandslist = array();
    }

    $commandslist[$name] = $function;
}

// Roll a new random character and return a 'player' array ready to be used elsewhere
function roll_character($name = null, $emoji = null) {
    $p = array('skill' => rand(1,6) + 6,             //1d6+6
               'stam' => rand(1,6) + rand(1,6) + 12, //2d6+12
               'luck' => rand(1,6) + 6,              //1d6+6
               'prov' => 10,
               'gold' => rand(1,6)-1, //1d6-1 (Note this is a customisation from the book's rules)
               'weapon' => 0,
               'lastpage' => 1,
               'stuff' => array('Sword','Leather Armor','Lantern'));

    // Random Potion
    // The book rules actually give you a choice, but this is a bit more fun
    switch(rand(1,3)) {
        case 1:
            $p['stuff'][] = 'Potion of Skill';
            break;
        case 2:
            $p['stuff'][] = 'Potion of Strength';
            break;
        case 3:
            $p['stuff'][] = 'Potion of Luck';
            // If the potion of luck is chosen, the player get 1 bonus luck
            $p['luck']++;
            break;
    }

    // Set maximums
    // The game won't (normally) allow you to exceed your inital scores
    $p['max']['skill']  = $p['skill'];
    $p['max']['stam']   = $p['stam'];
    $p['max']['luck']   = $p['luck'];
    $p['max']['prov']   = 99999;
    $p['max']['gold']   = 99999;
    $p['max']['weapon'] = 99999;

    // Character Fluff
    if ($name && $emoji) {
        $p['name'] = $name;
        $p['icon'] = $emoji;
    } else {
        // Get a random gender and name
        $gender = rand(0,1);
        $lines = file($gender?'resources/male_names.txt':'resources/female_names.txt');
        $p['name'] = trim($lines[array_rand($lines)]);

        // Get a random emoji to represent the character
        $male = array(':boy:',':man:',':person_with_blond_hair:',':older_man:');
        $female = array(':girl:',':woman:',':princess:',':older_woman:');
        $skintone = array(':skin-tone-2:',':skin-tone-3:',':skin-tone-4:',':skin-tone-5:');
        if ($gender) {
            $p['icon'] = $male[array_rand($male)].$skintone[array_rand($skintone)];
        } else {
            $p['icon'] = $female[array_rand($female)].$skintone[array_rand($skintone)];
        }
    }

    return $p;
}

// Load the player array from a serialized array
// If we can't find the file, generate a new character
function load()
{
    $save = file_get_contents('save.txt');
    if (!$save) {
        $p = roll_character();
    }
    else {
        $p = unserialize($save);
    }

    return $p;
}

// Serialize and save player array
function save($p)
{
    file_put_contents("save.txt",serialize($p));
}

// Convert number to html entity of dice emoji
function diceemoji($r)
{
    if ($r < 1 || $r > 6)
        return "BADDICE";

    return mb_convert_encoding('&#x'.(2679+$r).';', 'UTF-8', 'HTML-ENTITIES');
}

// Adds a new command to the command list
function addcommand($cmd)
{
    global $commandlist;
    return array_unshift($commandlist,$cmd);
}

/// ----------------------------------------------------------------------------
/// Send message to slack functions

// Convert the player array to a character sheet and send it to slack
// along with message $text
function send_charsheet($player, $text = "")
{
    $attachments = array([
        'color'    => '#ff6600',
        'fields'   => array(
        [
            'title' => 'Skill',
            'value' => $player['skill']." / ".$player['max']['skill'],
            'short' => true
        ],
        [
            'title' => 'Stamina (stam)',
            'value' => $player['stam']." / ".$player['max']['stam'],
            'short' => true
        ],
        [
            'title' => 'Luck',
            'value' => $player['luck']." / ".$player['max']['luck'],
            'short' => true
        ],
        [
            'title' => 'Weapon Bonus (weapon)',
            'value' => "+".$player['weapon'],
            'short' => true
        ],
        [
            'title' => 'Gold',
            'value' => $player['gold'],
            'short' => true
        ],
        [
            'title' => 'Provisons (prov)',
            'value' => $player['prov'],
            'short' => true
        ])
    ]);

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['icon'];
    }

    sendmsg($text."\n*".$player['name']."*",$attachments,$player['icon']);
}

// Send to slack a list of the player's stuff (inventory)
function send_stuff($player)
{
    $s = $player['stuff'];
    if (sizeof($s) == 0) {
        $s[] = "(Nothing!)";
    } else {
        natcasesort($s);
        $s = array_map("ucfirst",$s);
    }

    $attachments = array([
            'color'    => '#0066ff',
            'fields'   => array(
            [
                'title' => 'Inventory',
                'value' => implode("\n",array_slice($s, 0, floor(sizeof($s) / 2))),
                'short' => true
            ],
            [
                'title' => "",
                'value' => "\n".implode("\n",array_slice($s, floor(sizeof($s) / 2))),
                'short' => true
            ])
    ]);

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['icon'];
    }

    sendmsg("",$attachments,$icon);
}

// Send a direct message to a user or channel on slack
function senddirmsg($message, $user = false)
{
    if (!$user) {
        $user = $_POST['user_id'];
    }
    return sendmsg($message, true, ':green_book:', '@'.$user);
}

// Send a quick and basic message to slack
function sendqmsg($message, $icon = ':green_book:')
{
    return sendmsg($message, true, $icon);
}

// Full whistles and bells send message to slack
// Normally use one of the convenience functions above
function sendmsg($message, $attachments = array(), $icon = ':green_book:', $chan = false)
{
    $data = array(
        'text'        => $message,
        'icon_emoji'  => $icon,
        'attachments' => $attachments
    );
    if ($chan) {
        $data['channel'] = $chan;
    }
    $data_string = json_encode($data);
    $ch = curl_init(SLACK_HOOK);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
    //Execute CURL
    $result = curl_exec($ch);
    return $result;
}