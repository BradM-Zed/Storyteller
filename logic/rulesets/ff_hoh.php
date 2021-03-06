<?php

require_once 'ff_basic.php';

class book_ff_hoh extends book_ff_basic {
    public function isDead() {
        $player = &$this->player;
        return ($player['stam'] < 1) || ($player['fear'] >= $player['max']['fear']);
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollHumanCharacter($name, $gender, $emoji, $race, $adjective);
        $p['weapon'] = -3;
        // Set race
        if (!$race || $race == '?') {
            $p['race'] = array('Cowardly', 'Ordinary', 'Sceptical', 'Open-Minded', 'Believer', 'Enlightened')[$p['fear']-7];
        }
        return $p;
    }


    protected function getStats() {
        $stats = parent::getStats();
        $stats['fear'] = [
            'friendly' => 'Fear',
            'icons' => ':scream:',
            'roll' => 0,
            'max' => '1d6+6',
        ];
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][5] = array (
            'title' => 'Fear',
            'value' => $this->player['fear']." / ".$this->player['max']['fear'],
            'short' => true
        );
        return $attachments;
    }


}
