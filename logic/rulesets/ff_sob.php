<?php

require_once 'ff_basic.php';

class book_ff_sob extends book_ff_basic {
    public function isDead() {
        $player = &$this->player;
        return ($player['stam'] < 1) || ($player['str'] < 1);
    }


    protected function storyModify($story) {
        $story = parent::storyModify($story);
        $story = str_ireplace('The Banshee', $this->player['shipname'], $story);
        return $story;
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollHumanCharacter($name, $gender, $emoji, $race, $adjective);
        $p['race'] = 'Pirate';
        $shipnames = file('resources/ship_names.txt');
        $p['shipname'] = trim($shipnames[array_rand($shipnames)]);
        if (!$adjective || $adjective == '?') {
            $adjectives = array('Bold', 'Bloodthirsty', 'Cut-throat', 'Despicable', 'Dread-Pirate', 'Foul', 'Fearsome', 'Horrible',
                'Hook-handed', 'Killer', 'Loathsome', 'Low', 'Mad', 'Murderous', 'Nasty', 'Navigator', 'Peg-legged',
                'Reviled', 'Ruthless', 'Strong', 'Scurviest', 'Tough', 'Terrible', 'Weird', 'Vile', 'Villainous');
            $p['adjective'] = trim($adjectives[array_rand($adjectives)]);
        }
        // starting items
        $p['gold'] = 20;
        $p['stuff'] = array('Cutlass (+0)');
        return $p;
    }


    protected function getStats() {
        $stats = parent::getStats();
        $stats['str'] = [
            'friendly' => 'Crew Strength',
            'alias' => ['strength', 'crewstrength'],
            'icons' => ':muscle:',
            'roll' => '2d6+6',
            'max' => 'roll',
            'testdice' => 3,
            'testpass' => 'Your crew is strong enough',
            'testfail' => 'Your crew is not strong enough',
        ];
        $stats['strike'] = [
            'friendly' => 'Crew Strike',
            'alias' => ['crewstrike'],
            'icons' => ':crossed_swords:',
            'roll' => '1d6+6',
            'max' => 'roll',
            'sheet' => 1,
        ];
        $stats['log'] = [
            'friendly' => 'Log',
            'icons' => ':closed_book:',
            'sheet' => 1,
        ];
        $stats['slaves'] = [
            'friendly' => 'Slaves',
            'icons' => ':chains:',
            'sheet' => 1,
        ];
        $stats['gold']['friendly'] = 'Booty';
        $stats['gold']['alias'][] = 'booty';
        $stats['gold']['sheet'] = 1;
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        global $config;

        $player = &$this->player;
        $attachments = parent::getCharcterSheetAttachments();
        // QOL for discord with 3 per row instead of two
        if ($config->discord_mode) {
            $attachments[0]['fields'][0]['value'] .= '  (Weapon: '.sprintf("%+d", $player['weapon']).')';
            unset($attachments[0]['fields'][3]);
        }
        unset($attachments[0]['fields'][4]);
        unset($attachments[0]['fields'][5]);
        $attachments[] = [
            'color'    => '#8b4513',
            'fields'   => array(
                [
                    'title' => 'Ship Name',
                    'value' => $player['shipname'],
                    'short' => true
                ],
                [
                    'title' => 'Crew Strike (strike)',
                    'value' => $player['strike']." / ".$player['max']['strike'],
                    'short' => true
                ],
                [
                    'title' => 'Crew Strength (str)',
                    'value' => $player['str']." / ".$player['max']['str'],
                    'short' => true
                ],
                [
                    'title' => 'Booty (Gold)',
                    'value' => $player['gold'],
                    'short' => true
                ],
                [
                    'title' => 'Slaves',
                    'value' => $player['slaves'],
                    'short' => true
                ],
                [
                    'title' => 'Log',
                    'value' => $player['log'].' days',
                    'short' => true
                ])
        ];
        return $attachments;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('battle', '_cmd_battle', ['oms', 'n', 'n', 'osl']);
    }


    //// !battle [name] <skill> <stamina> [maxrounds] (run battle logic)
    protected function _cmd_battle($cmd) {
        $player = &$this->player;
        // Construct battle player
        $bp = array(
            'name' => "The crew of ".$player['shipname'],
            'referrers' => ['you' => 'your crew', 'youare' => 'your crew is', 'your' => "your crew's"],
            'skill' => $player['strike'],
            'stam' => $player['str'],
            'luck' => 0,
            'weapon' => 0,
            'shield' => false
        );
        $out = $this->runFight(['player' => &$bp,
                'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                'monsterskill' => $cmd[2],
                'monsterstam' => $cmd[3],
                'maxrounds' => ($cmd[4]?$cmd[4]:50),
                'healthstatname' => 'strength']);

        $player['str'] = $bp['stam'];

        sendqmsg($out, ":crossed_swords:");
    }


}
