<?php

require_once 'character.php';

class book_crystalm extends book_character {
    public function isDead() {
        // Everybody locked in
        $p = &$this->player;
        if (!$p['lockedin']) {
            return false;
        }
        foreach ($p['team'] as $t) {
            if (!$t['lockedin']) {
                return false;
            }
        }
        return true;
    }


    protected function getHelpFileId() {
        return 'crystalm';
    }


    protected function getStats() {
        $stats = array(
            'str' => [
                'friendly' => 'Strength',
                'alias' => ['strength'],
                'icons' => ':muscle:',
            ],
            'dex' => [
                'friendly' => 'Dexterity',
                'alias' => ['dexterity'],
                'icons' => ':juggling:',
            ],
            'int' => [
                'friendly' => 'Intelligence',
                'alias' => ['intelligence'],
                'icons' => ':thinking_face:',
            ],
            'crystals' => [
                'friendly' => 'Time Crystals',
                'alias' => ['timecrystals', 'cry'],
                'icons' => ':gem:',
            ],
            'time' => [
                'friendly' => 'Time',
                'icons' => [':hourglass:', ':hourglass_flowing_sand:'],
            ],
        );
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        global $config;
        $dm = $config->discord_mode;
        $player = &$this->player;

        $attachments[0]['color'] = $player['colourhex'];
        $attachments[0]['fields'] = [
            ['title' => 'Time Crystals',
                'value' => $player['crystals'],
                'short' => true],
            ['title' => 'Time',
                'value' => sprintf("%02dm %02ds", floor($player['time'] / 60), $player['time'] % 60),
                'short' => true],
        ];
        $team = $player['team'];
        $team[strtolower($player['name'])] = [
            'name' => "*".$player['name']."*",
            'lockedin' => $player['lockedin'],
            'str' => $player['str'],
            'dex' => $player['dex'],
            'int' => $player['int'],
            'stuff' => $player['stuff'],
        ];
        ksort($team);
        $namecol = $statscol = $stuffcol = "";
        foreach ($team as $cm) {
            if (count($cm['stuff']) > 0) {
                $item = $cm['stuff'][0];
            } else {
                $item = "_Nothing_";
            }
            $namecol .= ($cm['lockedin']?'~':'').$cm['name'].($cm['lockedin']?'~ _(Locked-in!)_':'')."\n";
            if ($dm) {
                $statscol .= "`Str: ".$cm['str']." Dex: ".$cm['dex']." Int: ".$cm['int']." `\n";
                $stuffcol .= "$item\n";
            } else {
                $statscol .= "`Str: ".$cm['str']." Dex: ".$cm['dex']." Int: ".$cm['int']."` (Item: $item)\n";
            }
        }
        $attachments[1]['color'] = $player['colourhex'];
        $attachments[1]['fields'] = [
            ['title' => 'Name',
                'value' => $namecol,
                'short' => true],
            ['title' => 'Stats',
                'value' => $statscol,
                'short' => true],
        ];
        if ($dm) {
            $attachments[1]['fields'][] = ['title' => 'Item',
                'value' => $stuffcol,
                'short' => true];
        }

        return $attachments;
    }


    protected function getStuffAttachment() {
        return false;
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        $p['adjective'] = 'Captain';
        $p['lockedin'] = false;
        $p['team'] = array();
        $p['str'] = $p['dex'] = $p['int'] = 7;
        $p['pagelist'] = "|";

        // team
        foreach (['str', 'dex', 'int'] as $spec) {
            $t = parent::rollCharacter();
            // Prevent duplicate names
            while (array_key_exists($t['name'], $p['team']) || $t['name'] == $p['name']) {
                $t = parent::rollCharacter();
            }
            $t['lockedin'] = false;
            $t['str'] = $t['dex'] = $t['int'] = 6;
            $t[$spec] = 9;
            $p['team'][strtolower($t['name'])] = $t;
        }
        return $p;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('stuff',                 '_cmd_stats',  ['i']);
        $this->registerCommand('test',                  '_cmd_test',   ['s', 'onm', 'on', 'on']);
        $this->registerCommand(['lose', 'use', 'lose'], '_cmd_drop',   ['oms']);
        $this->registerCommand('switch',                '_cmd_switch', ['s']);
        $this->registerCommand('lockedin',              '_cmd_lockedin');
    }


    //// !get / !take (add item to inventory/stuff list)
    protected function _cmd_get($cmd) {
        if (strtolower($cmd[1]) == 'crystal') {
            $this->addCommand("crystals +1");
            return;
        }
        parent::_cmd_get($cmd);
        if (count($this->player['stuff']) > 1) {
            $this->_cmd_drop(['drop', $this->player['stuff'][0]]);
        }
    }


    //// !lose - Crystal Maze version, only 1 thing to drop
    protected function _cmd_drop($cmd) {
        $verb = strtolower($cmd[0]);
        if (count($this->player['stuff']) == 0) {
            sendqmsg("*Nothing to $verb.*", ':interrobang:');
            return;
        }
        return parent::_cmd_drop([$verb, $this->player['stuff'][0]]);
    }


    //// !test <stat> <target>
    protected function _cmd_test($cmd) {
        $player = &$this->player;

        $stat = $this->getStatFromAlias(strtolower($cmd[1]), $this->getStats());
        if (in_array($stat, ['str', 'dex', 'int'])) {
            $target = $player[$stat];
        } else {
            sendqmsg("*Don't know how to test ".$stat."*", ':interrobang:');
            return;
        }
        $mod = $cmd[2]?(int)$cmd[2]:'';

        // Setup outcome pages to read if provided
        if ($cmd[3]) {
            $success_page = "page ".$cmd[3];
        }
        if ($cmd[4]) {
            $fail_page = "page ".$cmd[4];
        }

        // Describer
        switch ($stat) {
        case 'str':
            $desc = 'strong';
            break;
        case 'dex':
            $desc = 'dexterous';
            break;
        case 'int':
            $desc = 'smart';
            break;
        default:
            $desc = $stat;
        }

        // Roll dice
        list($roll, $emojidice) = roll_dice_string("2d6$mod");

        // Check roll versus target number
        if ($roll <= $target) {
            sendqmsg("_*".$player['name']." is $desc!*_\n_(_ $emojidice _ vs $target)_", ':smile:');
            // Show follow up page
            if (isset($success_page)) {
                $this->addCommand($success_page);
            }
        }
        else {
            sendqmsg("_*".$player['name']." is not $desc!*_\n_(_ $emojidice _ vs $target)_", ':frowning:');
            // Show follow up page
            if (isset($fail_page)) {
                $this->addCommand($fail_page);
            }
        }
    }


    //// !page <num> / !<num> (Read page from book)
    protected function _cmd_page($cmd) {
        $pl = &$this->player['pagelist'];
        $pn = $cmd[1];

        if (strpos($pl, "|$pn|") === false) {
            $pl .= "$pn|";
        }
        return parent::_cmd_page($cmd);
    }


    protected function storyModify($story) {
        $pl = $this->player['pagelist'];
        $story = preg_replace_callback($this->getPageMatchRegex(),
            function ($m) use ($pl) {
                if (strpos($pl, "|".$m[1]."|") === false) {
                    return $m[0];
                } else {
                    return '~'.$m[0].'~';
                }
            },
            $story);
        return $story;
    }


    protected function _cmd_switch($cmd) {
        $p = &$this->player;
        $pname = ucfirst($name);
        if ($name == strtolower($p['name'])) {
            sendqmsg("*Already playing as $pname.*", ':interrobang:');
            return;
        }
        if (!array_key_exists($name, $p['team'])) {
            sendqmsg("*$pname is not in your team*", ':interrobang:');
            return;
        }

        // Do switch
        $copyofcurrent = $p;
        unset($copyofcurrent['team']);
        unset($copyofcurrent['pagelist']);
        $np = $p['team'][$name];
        // Copy shared stats
        $np['crystals'] = $p['crystals'];
        $np['time'] = $p['time'];
        $np['lastpage'] = $p['lastpage'];
        $np['pagelist'] = $p['pagelist'];
        // Rebuild team
        $np['team'] = $p['team'];
        unset($np['team'][$name]);
        $np['team'][strtolower($p['name'])] = $copyofcurrent;
        $p = $np;
        sendqmsg("*Switched to $pname*", $p['emoji']);
    }


    protected function _cmd_lockedin($cmd) {
        $li = &$this->player['lockedin'];
        $name = $this->player['name'];
        $li = !$li;
        if ($li) {
            sendqmsg("*$name has been locked in!*", ':lock:');
        } else {
            sendqmsg("*$name has been freed!*", ':lock:');
        }
    }


}
