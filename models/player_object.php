<?php

/**
 * Represents a PHPDraft player, or "pick" in the draft.
 * 
 * Each player is owned by a manager, who belongs to a draft.
 * 
 * Players carry draft information on them - such as which round and which pick
 * they exist at (player information will be blank if they are unchecked)
 * 
 * @property int $player_id The unique ID for this player
 * @property int $manager_id The ID of the manager this player belongs to
 * @property int $draft_id The ID of the draft this player belongs to
 * @property string $first_name The first name of the player
 * @property string $last_name The last name of the player
 * @property string $team The professional team the player plays for. Stored as three character abbreviation
 * @property string $position The position the player plays. Stored as one or three character abbreviation.
 * @property string $pick_time Timestamp of when the player was picked.
 * @property int $pick_duration Amount of seconds that were consumed during this pick
 * @property int $player_round Round the player was selected in
 * @property int $player_pick Pick the player was selected at
 */
class player_object {

	public $player_id;
	public $manager_id;
	public $draft_id;
	public $first_name;
	public $last_name;
	public $team;
	public $position;
	public $pick_time;
	public $pick_duration;
	public $player_round;
	public $player_pick;

	public function __construct($id = 0) {
		if(intval($id) == 0)
			return false;

		$id = intval($id);

		$player_result = mysql_query("SELECT * FROM players WHERE player_id = " . $id . " LIMIT 1");

		if(!$player_row = mysql_fetch_array($player_result))
			return false;

		$this->player_id = intval($player_row['player_id']);
		$this->manager_id = intval($player_row['manager_id']);
		$this->draft_id = intval($player_row['draft_id']);
		$this->first_name = $player_row['first_name'];
		$this->last_name = $player_row['last_name'];
		$this->team = $player_row['team'];
		$this->position = $player_row['position'];
		$this->pick_time = strtotime($player_row['pick_time']);
		$this->pick_duration = intval($player_row['pick_duration']);
		$this->player_round = intval($player_row['player_round']);
		$this->player_pick = intval($player_row['player_pick']);

		return true;
	}

	/**
	 * Saves or updates a player. NOTE: Does not update or save the player's pick time or pick duration. Those must be handled separately.
	 * @return bool true on success, false otherwise 
	 */
	public function savePlayer() {
		if($this->player_id > 0) {
			//TODO: Add a default value variable to trigger whether or not to set the pick time on player update. $setPickToNow = false
			$sql = "UPDATE players SET ".
			"manager_id = " . intval($this->manager_id) . ", ".
			"draft_id = " . intval($this->draft_id) . ", ".
			"first_name = " . mysql_real_escape_string($this->first_name) . ", ".
			"last_name = " . mysql_real_escape_string($this->last_name). ", ".
			"team = " . mysql_real_escape_string($this->team) . ", ".
			"position = " . mysql_real_escape_string($this->position) . ", ".
			"player_round = " . intval($this->player_round) . ", ".
			"player_pick = " . intval($this->player_pick) . ", ".
			"WHERE player_id = " . intval($this->player_id);
			return mysql_query($sql);
		} elseif($this->draft_id > 0 && $this->manager_id > 0) {
			//TODO: Investigate how to insert with empty fields.
			$sql = "INSERT INTO players ".
			"(manager_id, draft_id, player_round, player_pick) ".
			"VALUES ".
			"(" . intval($this->manager_id) . ", " . intval($this->draft_id) . ", " .  intval($this->player_round) . ", " . intval($this->player_pick) . ")";
			
			$result = mysql_query($sql);
			if(!$result)
				return false;
			
			$this->player_id = mysql_insert_id();
			
			return true;
		}else
			return false;
	}

	/**
	 * Get all players/picks for a given draft.
	 * @param int $draft_id ID of the draft to get players for
	 * @return array Player objects that belong to given draft. false on failure
	 */
	public static function getPlayersByDraft($draft_id) {
		$draft_id = intval($draft_id);

		if($draft_id == 0)
			return false;

		$players_result = mysql_query("SELECT * FROM players WHERE draft_id = " . $draft_id . " ORDER BY player_pick ASC");

		$players = array();

		while($player_row = mysql_fetch_array($players_result)) {
			$player = new player_object();
			$player->player_id = intval($player_row['player_id']);
			$player->manager_id = intval($player_row['manager_id']);
			$player->draft_id = intval($player_row['draft_id']);
			$player->first_name = $player_row['first_name'];
			$player->last_name = $player_row['last_name'];
			$player->team = $player_row['team'];
			$player->position = $player_row['position'];
			$player->pick_time = strtotime($player_row['pick_time']);
			$player->pick_duration = intval($player_row['pick_duration']);
			$player->player_round = intval($player_row['player_round']);
			$player->player_pick = intval($player_row['player_pick']);
			$players[] = $player;
		}

		return $players;
	}

	/**
	 * Get all players/picks for a given manager that have been selected.
	 * @param int $manager_id ID of the manager to get players for
	 * @return array Player objects that belong to given manager. false on failure
	 */
	public static function getSelectedPlayersByManager($manager_id) {
		$manager_id = intval($manager_id);

		if($manager_id == 0)
			return false;

		$players_result = mysql_query("SELECT * FROM players WHERE manager_id = " . $manager_id . " AND pick_time IS NOT NULL ORDER BY player_pick ASC");

		$players = array();

		while($player_row = mysql_fetch_array($players_result)) {
			$player = new player_object();
			$player->player_id = intval($player_row['player_id']);
			$player->manager_id = intval($player_row['manager_id']);
			$player->draft_id = intval($player_row['draft_id']);
			$player->first_name = $player_row['first_name'];
			$player->last_name = $player_row['last_name'];
			$player->team = $player_row['team'];
			$player->position = $player_row['position'];
			$player->pick_time = strtotime($player_row['pick_time']);
			$player->pick_duration = intval($player_row['pick_duration']);
			$player->player_round = intval($player_row['player_round']);
			$player->player_pick = intval($player_row['player_pick']);
			$players[] = $player;
		}

		return $players;
	}

	/**
	 * Get all selected players for a given round.
	 * @param int $draft_id ID of the draft for the given round
	 * @param int $round Round to get players for
	 * @return array Player objects that belong in a given round. false on failure
	 */
	public static function getSelectedPlayersByRound($draft_id, $round) {
		$draft_id = intval($draft_id);
		$round = intval($round);

		if($draft_id == 0 || $round == 0)
			return false;

		$players_result = mysql_query("SELECT * FROM players WHERE draft_id = " . $manager_id . " AND round = " . $round . " AND pick_time IS NOT NULL ORDER BY player_pick ASC");

		$players = array();

		while($player_row = mysql_fetch_array($players_result)) {
			$player = new player_object();
			$player->player_id = intval($player_row['player_id']);
			$player->manager_id = intval($player_row['manager_id']);
			$player->draft_id = intval($player_row['draft_id']);
			$player->first_name = $player_row['first_name'];
			$player->last_name = $player_row['last_name'];
			$player->team = $player_row['team'];
			$player->position = $player_row['position'];
			$player->pick_time = strtotime($player_row['pick_time']);
			$player->pick_duration = intval($player_row['pick_duration']);
			$player->player_round = intval($player_row['player_round']);
			$player->player_pick = intval($player_row['player_pick']);
			$players[] = $player;
		}

		return $players;
	}

	public static function deletePlayersByDraft($draft_id) {
		$draft_id = intval($draft_id);

		if($draft_id == 0)
			return false;

		$players = player_object::getPlayersByDraft($draft_id);

		$id_string = "0"; //TODO: Update this so it's cleaner? This is hacky.	

		foreach($players as $player) {
			$id_string .= "," . $player->player_id;
		}

		$sql = "DELETE FROM players WHERE player_id IN (" . mysql_escape_string($id_string) . ")";

		return mysql_query($sql);
	}
}

?>