# Storyteller Commands Help
## Quick Guide / Common Commands
Mostly everything you'll need to know.

### Reading
- `!<number>` Read page <number>. e.g. !42. `!look` Re-read last page.
- `!info` Show character sheet. `!stats` just shows stats, `!stuff` just shows inventory.

### Rolling Dice
- `!fight [name] <skill> <stamina>` Fight a monster with skill <skill> and stamina <stamina>. Name is optional and can contain spaces. e.g. `!fight Giant Spider 4 5`
- `!roll [dienumber]` Roll [dienumber] six-sided dice and sum the result. If dienumber is missing, rolls one die.
- `!test luck` or `!test skill` or `!test stam` Test the stat against two dice.
- `!randpage <page 1> [page 2] [page 3] [...]` Turn randomly to one of the listed pages.

### Character Management
- `!<stat> [+/-]<amount>` Set <stat> to <amount>. Use + or - to *alter* <stat> by <amount>. e.g. `!skill 3` or `!gold +2`
- `!<stat> max [+/-]<amount>` Set or alter the MAX of <stat> with <amount>. e.g. `!stam max -1`
<stat> values are: `skill`, `stam`, `luck`, `weapon`, `gold` and `prov`
- `!eat` Eats one provision for 4 stamina.
- `!get <item>` Adds <item> to your inventory.
- `!lose <item>` Removes <item> to your inventory. You can also use `!drop` and `!use` for different descriptions.
- `!shield <on/off>` Equips or removes the special shield item. When on gives a 1 in 6 chance to reduce damage by 1 when using !fight.
- `!newgame [name] [m/f] [emoji]` Rolls a new character and resets the game. Optionally set name, gender and emoji. e.g. `!newgame Jill f`

You can chain multiple commands together in one go with semicolons e.g. `!newgame; !1`

Still awake? Below is an exhaustive list of commands

## Complete Command List
For the nerds

### Reading
- `!<number>` or `!page <number>` Read page <number>. e.g. `!42`
- `!look` Re-read last page.

### Character Information
- `!info` or `!status` Show character stats and inventory.
- `!stats` or `!s` Show character stats only.
- `!stuff` or `!i` Show character inventory.
- `!newgame [name] [m/f] [emoji] [race] [adjective]` or `!ng` Rolls a new character and resets the game. Optionally customise the new character. Use `?` to randomise a field.

##### Some fun character ideas:
- `!ng ? m :male_mage::skin-tone-5: Human Wizard`
- `!ng ? ? :robot_face: Robot`
- `!ng Vaarsuvius Non-Binary :elf::skin-tone-2: Elf`

### Roll Automation
- `!test <stat> [successpage] [failpage]` Roll test for <stat>. Valid stats are: `luck`, `skill` and `stam`. Turn to [successpage] if successful, [failpage] otherwise (optional.)
- `!roll [dienumber]` Roll [dienumber] six-sided dice and sum the result. If dienumber is missing, rolls one die.
- `!luckyescape` or `!le` Test luck to try to negate damage. Lose 3 stamina on a failure and 1 stamina on a success.
- `!randpage <page 1> [page 2] [page 3] [...]` Turn randomly to one of the listed pages.

### Fight Automation
- `!fight [name] <skill> <stamina> [stopafter]` Fight a monster named [name] (optional) with skill <skill> and stamina <stamina>. Spaces are accepted in the name. Stop after [stopafter] rounds (optional.) You can use 3 special phrases for [stopafter]: `hitme`, `hitthem` and `hitany` to stop the fight in those situations.
- `!attack <skill> [damage]` or `!a <skill> [damage]` Perform a single attack roll versus a monster with skill <skill>. [damage] is taken from stamina on a fail (Default: 0) This is for manually running combat with special rules.
The following cover many custom fight rules:
- `!critfight [name] <skill> [who] [critchance]` Fight a monster named [name] (optional) with skill <skill> with critical strikes doing damage only. [who] is who has to roll the crits, `me` or `both` (Default: me). [critchance] is the chance of the crit hitting x in 6. (Default: 2)
- `!bonusfight [name] <skill> <stamina> <bonusdmg>` Fight a monster named [name] (optional) with skill <skill> and stamina <stamina>. After each round the monster has a 1/2 chance of doing <bonusdmg> damage.
- `!fighttwo <name 1> <skill 1> <stamina 1> [<name 2> <skill 2> <stamina 2>]` Fight two opponents at the same time. If a second monster isn't provided, you'll fight two copies of the first.
- `!vs <name 1> <skill 1> <stamina 1> <name 2> <skill 2> <stamina 2>` Fight two monsters against each other.

### Inventory Management
- `!get <item>` or `!take <item>` Adds <item> to your inventory. Attempts to automatically manage gold and provisions stats if used like "!get 5 gold"
- `!lose <item>` Removes <item> to your inventory. You don't have to provide a full match. e.g. Drop 'leather armor' with `!drop armor`. Will attempt to manage gold and provisions as above.
- `!drop <item>` or `!use <item>` As above, but with thematic descriptions.
- `!eat` Eats one provision for 4 stamina.
- `!pay <amount>` or `!spend <amount>` Subtracts <amount> of gold. See stats below.
- `!shield <on/off>` Equips or removes the special shield item. When on gives a 1 in 6 chance to reduce damage by 1 when using !fight (and variants.)

### Stats Management

`!<stat> [max] [+/-]<amount>` Set the stat called <stat> to <amount>. Valid <stat> values are: `skill`, `stam`, `luck`, `weapon`, `gold` and `prov`. If [max] is used, the stat's maximum is changed instead. Depending which booktype you are playing, additional stats may be available. If the number starts with a - or +, <amount> will be subtracted or added from the total. Otherwise the value is replaced with <amount>. Only weapon can be reduced below 0. 

##### Examples:
- `!stam -3` Take 3 stamina loss.
- `!weapon 2` Set weapon bonus to 2.
- `!luck max +1` Add 1 to maximum luck.

### Command Chaining & Fancy Stuff
- `!echo <message>` Simply repeats <message>. Useful to label outputs when chaining commands.
- You can chain multiple commands together in one go with semicolons `;` e.g. `!newgame; !1` The chain will stop automatically on player death.
- You can include magic substitutions in any command with curly brackets. There are two types:
-- Player information. Any of the stats will work, plus a few extra. Try `!echo Hello {name}.`
-- Dice rolls in the form <numdice>d[dicesides][+/-bonus]. If dicesides is omitted, 6 is assumed. e.g. `{1d}`, `{3d10}`, `{1d8-4}`, `{1d+3}`

##### Examples:
- `!eat;!eat;!eat` Eat 3 times.
- `!fight spider 4 5; !42` Fight a spider and turn to page 42 if you win.
- `!pay 5; !get Odd Potion` Pay 5 gold and receive an Odd Potion.
- `!echo Jim:; !roll 5; !echo Bob:; !roll 5` Roll 5 dice each for Jim and Bob.
- `!{1d400}` Turn to a random page between 1 - 400.
- `!stam -{1d}` Roll a 6-sided dice and subtract the result from stamina.
- `!skill max {skill}` Set your maximum skill to your current skill.
- `!ng {name} {gender} {emoji} {race} Second` Start a new game as the offspring of the last character