<?php

require_once 'ff_basic.php';

class book_ff_coh extends book_ff_basic {
    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        // Change fluff
        $p['race'] = 'Beast';
        $p['adjective'] = 'Creature of Havoc';
        $p['realname'] = $p['name'];
        $p['name'] = '';
        for ($c = 0; $c < strlen($p['realname']); $c++) {
            if ($c == 0 || rand(0, 3) == 0) {
                $p['name'] .= $p['realname'][$c];
            } else {
                $p['name'] .= '?';
            }
        }
        $p['emoji'] = ':japanese_ogre:';
        return $p;
    }


}
