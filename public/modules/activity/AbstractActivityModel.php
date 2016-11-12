<?php
abstract class AbstractActivityModel {

	/**
	 * Returns one single activity object fetched from database for the given id.
	 *
	 * @param unknown $id
	 */
	public function getActivity(int $activity_id) {
		return getActivityTypeDetails ( $activity_id );
	}

	/**
	 * Used by getActivity to fetch the activity type specific data.
	 *
	 * @param unknown $id
	 */
	protected abstract function getActivityTypeDetails(int $activity_id);

	/**
	 * Saves an activity to the database.
	 * This does include the create as well as update case.
	 *
	 * @param unknown $id
	 *        	can be null for create case
	 * @return bool true if saving was successful, false otherwise
	 */
	public function saveActivity(int $activity_id) {
		$db = db::getInstance ();
		$login = new Login ();

		$userid = $login->currentUserID ();
		$uxtime = time ();

		$sql = "INSERT INTO activities (id, userid, create_time, type) VALUES ('NULL', '$userid', '$uxtime', '$type');";
		$db->query ( $sql );

		$activity_id = $db->insert_id;
		$this->saveActivityTypeDetails ( $activity_id );
	}

	/**
	 * Used by saveActivity to save the activity type specific data.
	 *
	 * @param unknown $id
	 */
	protected abstract function saveActivityTypeDetails(int $activity_id);

	/**
	 * Deletes the activity for given id from database.
	 *
	 * @param int $id
	 * @return bool true if delete was successful, false otherwise
	 */
	public function deleteActivity(int $activity_id) {
		$db = db::getInstance ();
		$env = Env::getInstance ();
		$login = new Login ();

		$userid = $login->currentUserID ();
		$actid = $this->getActivity ( $activity_id )->userid;
		if ($userid != $actid) {
			return false;
		}

		$sql = "UPDATE activities SET deleted = '1' WHERE id = '$activity_id';";
		$query = $db->query ( $sql );
		if ($query !== false) {
			$env->clearPost ( 'activity' );
			if (isset ( $env::$hooks ['delete_activity_hook'] )) {
				$env::$hooks ['delete_activity_hook'] ( $activity_id );
			}
			return true;
		}
		return false;
	}

	/**
	 * Creates all database tables necessary for a certain activity type to work.
	 *
	 * @param unknown $overwriteIfExists
	 *        	true if existing table shall be overwritten (for example for reset purpose)
	 */
	public abstract function createActivityTypeDatabaseTables($overwriteIfExists);
}