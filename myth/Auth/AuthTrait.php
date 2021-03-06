<?php

namespace Myth\Auth;

trait AuthTrait {

	/**
	 * Instance of Authentication Class
	 * @var null
	 */
	public $authenticate = null;

	/**
	 * Instance of Authorization class
	 * @var null
	 */
	public $authorize = null;

	private $classes_loaded = false;

	//--------------------------------------------------------------------

	/**
	 * Verifies that a user is logged in
	 *
	 * @param null $uri
	 */
	public function restrict($uri=null)
	{
	    $this->setupAuthClasses();

		if ($this->authenticate->isLoggedIn())
		{
			return true;
		}

		if (method_exists($this, 'setMessage'))
		{
			$this->setMessage('You must be logged in to view that page.');
		}

		if (empty($uri))
		{
			redirect( \Myth\Route::named('login') );
		}

		redirect($uri);
	}

	//--------------------------------------------------------------------


	/**
	 * Ensures that the current user is in at least one of the passed in
	 * groups. The groups can be passed in as either ID's or group names.
	 * You can pass either a single item or an array of items.
	 *
	 * If the user is not a member of one of the groups will return
	 * the user to the page they just came from as shown in
	 * $_SERVER['']
	 *
	 * Example:
	 *  restrictToGroups([1, 2, 3]);
	 *  restrictToGroups(14);
	 *  restrictToGroups('admins');
	 *  restrictToGroups( ['admins', 'moderators'] );
	 *
	 * @param mixed  $groups
	 * @param string $uri   The URI to redirect to on fail.
	 *
	 * @return bool
	 */
	public function restrictToGroups($groups, $uri='')
	{
	    $this->setupAuthClasses();

		if ($this->authenticate->isLoggedIn())
		{
			if ($this->authorize->inGroup($groups, $this->authenticate->id() ) )
			{
				return true;
			}
		}

		if (method_exists($this, 'setMessage'))
		{
			$this->setMessage('You do not have sufficient privileges to view that page.');
		}

		if (empty($uri))
		{
			redirect( \Myth\Route::named('login') .'?request_uri='. current_url() );
		}

		redirect($uri .'?request_uri='. current_url());
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that the current user has at least one of the passed in
	 * permissions. The permissions can be passed in either as ID's or names.
	 * You can pass either a single item or an array of items.
	 *
	 * If the user does not have one of the permissions it will return
	 * the user to the URI set in $url or the site root, and attempt
	 * to set a status message.
	 *
	 * @param $permissions
	 * @param string $uri   The URI to redirect to on fail.
	 *
	 * @return bool
	 */
	public function restrictWithPermissions($permissions, $uri='')
	{
	    $this->setupAuthClasses();

		if ($this->authenticate->isLoggedIn())
		{
			if ($this->authorize->hasPermission($permissions, $this->authenticate->id() ) )
			{
				return true;
			}
		}

		if (method_exists($this, 'setMessage'))
		{
			$this->setMessage('You do not have sufficient privileges to view that page.');
		}

		if (empty($uri))
		{
			redirect( \Myth\Route::named('login') .'?request_uri='. current_url() );
		}

		redirect($uri .'?request_uri='. current_url());
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that the Authentication and Authorization libraries are
	 * loaded and ready to go, if they are not already.
	 *
	 * Uses the following config values:
	 *      - auth.authenticate_lib
	 *      - auth.authorize_lib
	 */
	public function setupAuthClasses()
	{
		if ($this->classes_loaded)
		{
			return;
		}

		get_instance()->config->load('auth');

		/*
		 * Authentication
		 */
		$auth = config_item('auth.authenticate_lib');

		if (empty($auth)) {
			throw new \RuntimeException('No Authentication System chosen.');
		}

		$this->authenticate = new $auth( get_instance() );

		get_instance()->load->model('auth/user_model', 'user_model', true);
		$this->authenticate->useModel( get_instance()->user_model );

		// Try to log us in automatically.
		if (! $this->authenticate->isLoggedIn())
		{
			$this->authenticate->viaRemember();
		}

		/*
		 * Authorization
		 */
		$auth = config_item('auth.authorize_lib');

		if (empty($auth)) {
			throw new \RuntimeException('No Authorization System chosen.');
		}

		$this->authorize = new $auth();
	}

	//--------------------------------------------------------------------

}