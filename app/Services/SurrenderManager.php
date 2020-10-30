<?php namespace App\Services;

use App\Services\Service;

use Carbon\Carbon;

use DB;
use Config;
use Image;
use Notifications;
use Settings;

use App\Models\User\User;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Adoption\Surrender;

use App\Services\CurrencyManager;

class SurrenderManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Surrender Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of surrender data.
    |
    */

    /**
     * Creates a new surrender.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @param  bool                   $isClaim
     * @return mixed
     */
    public function createSurrender($data, $user)
    {
        DB::beginTransaction();

        try {
            // check that surrenders are open
            if(!Settings::get('is_surrenders_open')) throw new \Exception("Surrenders are closed.");

            // might be needed
            $characters = Character::where('user_id', $user->id)->where('id', $data['character_id'])->first();
            
            // Get a list of rewards, then create the surrender itself
            $surrender = Surrender::create([
                'user_id' => $user->id,
                'character_id' => $data['character_id'],
                'worth' => $data['worth'],
                'notes' => $data['notes'],
                'status' => 'Pending',
                ]);

            return $this->commitReturn($surrender);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Rejects a surrender.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function rejectSurrender($data, $user)
    {
        DB::beginTransaction();

        try {
            // 1. check that the surrender exists
            // 2. check that the surrender is pending
            if(!isset($data['surrender'])) $surrender = Surrender::where('status', 'Pending')->where('id', $data['id'])->first();
            elseif($data['surrender']->status == 'Pending') $surrender = $data['surrender'];
            else $surrender = null;
            if(!$surrender) throw new \Exception("Invalid surrender.");

            // The only things we need to set are: 
            // 1. staff comment
            // 2. staff ID
            // 3. status
            $surrender->update([
                'staff_comments' => $data['staff_comments'],
                'staff_id' => $user->id,
                'status' => 'Rejected'
            ]);

            // need to make notifications
            Notifications::create('SURRENDER_REJECTED', $surrender->user, [
                'staff_url' => $user->url,
                'staff_name' => $user->name,
                'surrender_id' => $surrender->id,
            ]);

            return $this->commitReturn($surrender);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Approves a surrender.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function approveSurrender($data, $user, CurrencyManager $service)
    {
        DB::beginTransaction();

        try {
            // 1. check that the surrender exists
            // 2. check that the surrender is pending
            $surrender = Surrender::where('status', 'Pending')->where('id', $data['id'])->first();
            if(!$surrender) throw new \Exception("Invalid surrender.");

            // Distribute user currency
            // need to fix -> check previous code
            if(!$service->creditCurrency('Adoption Surrender', $user, $surrender->user, $promptLogType, $promptData)) throw new \Exception("Failed to distribute currency to user.");

            // Add character to the adoption stock
            // pseudo code
            // $service2->createStock( - - - - )

            // Finally, set: 
			// 1. staff comments
            // 2. staff ID
            // 3. status
            $surrender->update([
			    'staff_comments' => $data['staff_comments'],
				'parsed_staff_comments' => $data['parsed_staff_comments'],
                'staff_id' => $user->id,
                'status' => 'Approved',
            ]);

            Notifications::create('SURRENDER_APPROVED', $surrender->user, [
                'staff_url' => $user->url,
                'staff_name' => $user->name,
                'surrender_id' => $surrender->id,
            ]);

            return $this->commitReturn($surrender);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
}