<?php

require_once 'ff_basic.php';

class book_bvp extends book_ff_basic {
    public function getId() {
        return 'bvp';
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        // All stats start at 1
        $p['creationdice'] = array();
        $p['stam'] = $p['max']['stam'] = 1;
        $p['skill'] = $p['max']['skill'] = 1;
        $p['luck'] = $p['max']['luck'] = 1;
        return $p;
    }


}