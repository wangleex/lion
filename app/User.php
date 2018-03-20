<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

		/*
		 * Get all callbacks for the user
		 */
		public function applications() {
			return $this->hasMany('App\Application');
		}

		/*
		 * Get this user's authorizations
		 */
		public function authorizations() {
			return $this->hasMany('App\Authorization');
		}

		public function getAuthorizedProvidersAttribute() {
			$authorized = [];
			$providers = config('services.providers');

			foreach($providers as $provider){
				$authorization = $this->authorizations()->where('provider', $provider)->first();
				$authorized[$provider] = ($authorization == True);
			}
			return $authorized;
		}

		/*
		 * Get all the user's queries
		 */
		public function queries() {
			return $this->hasMany('App\Query');
		}

		/*
		 * Get how many gigabytes of storage this user's data is taking up
		 */
		public function getQuotaUsedAttribute() {
			$used = $this->queries->reduce(function($carry, $query) {
				return $carry + $query->history->reduce(function($carry, $history) {
					return $carry + $history->size;
				},0);
			}, 0);
			return ($used/1024)/1024;
		}
}
