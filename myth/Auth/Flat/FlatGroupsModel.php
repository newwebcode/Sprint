<?php

namespace Myth\Auth\Models;

use Myth\Models\CIDbModel;

class FlatGroupsModel extends CIDbModel {

	protected $table_name = 'auth_groups';

	protected $soft_deletes = false;

	protected $set_created = false;

	protected $set_modified = false;

	protected $protected_attributes = ['id', 'submit'];

	protected $validation_rules = [
		[
			'field' => 'name',
			'label' => 'Name',
			'rules' => 'trim|max_length[255]|xss_clean'
		],
		[
			'field' => 'description',
			'label' => 'Description',
			'rules' => 'trim|max_length[255]|xss_clean'
		],
	];

	protected $insert_validate_rules = [
		'name'      => 'required|is_unique[auth_groups.name]'
	];

	protected $before_insert = [];
	protected $before_update = [];
	protected $after_insert  = [];
	protected $after_update  = [];


	protected $fields = [];

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Users
	//--------------------------------------------------------------------

	/**
	 * Adds a single user to a single group.
	 *
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return object
	 */
	public function addUserToGroup($user_id, $group_id)
	{
	    $data = [
		    'user_id'   => (int)$user_id,
		    'group_id'  => (int)$group_id
	    ];

		return $this->db->insert('auth_groups_users', $data);
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single user from a single group.
	 *
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return bool
	 */
	public function removeUserFromGroup($user_id, $group_id)
	{
	    return $this->where([
		    'user_id' => (int)$user_id,
		    'group_id' => (int)$group_id
	    ])->delete('auth_groups_users');
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single user from all groups.
	 *
	 * @param $user_id
	 *
	 * @return mixed
	 */
	public function removeUserFromAllGroups($user_id)
	{
	    return $this->db->where('user_id', (int)$user_id)
		                ->delete('auth_groups_users');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all groups that a user is a member of.
	 *
	 * @param $user_id
	 *
	 * @return object
	 */
	public function getGroupsForUser($user_id)
	{
	    return $this->select('auth_groups_users.*, auth_groups.name, auth_groups.description')
		            ->join('auth_groups', 'auth_groups.group_id = groups.id', 'left')
		            ->where('user_id', $user_id)
		            ->find_all();

	}

	//--------------------------------------------------------------------




	//--------------------------------------------------------------------
	// Permissions
	//--------------------------------------------------------------------

	public function addPermissionToGroup($permission_id, $group_id)
	{
		$data = [
			'permission_id' => (int)$permission_id,
			'group_id'      => (int)$group_id
		];

	    return $this->db->insert('auth_groups_permissions', $data);
	}

	//--------------------------------------------------------------------


	/**
	 * Removes a single permission from a single group.
	 *
	 * @param $permission_id
	 * @param $group_id
	 *
	 * @return mixed
	 */
	public function removePermissionFromGroup($permission_id, $group_id)
	{
	    return $this->db->where([
		    'permission_id' => $permission_id,
		    'group_id'      => $group_id
	    ])->delete('auth_groups_permissions');
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single permission from all groups.
	 *
	 * @param $permission_id
	 *
	 * @return mixed
	 */
	public function removePermissionFromAllGroups($permission_id)
	{
	    return $this->db->where('permission_id', $permission_id)
		                ->delete('auth_groups_permissions');
	}

	//--------------------------------------------------------------------


}