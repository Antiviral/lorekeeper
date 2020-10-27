<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Adoption\Adoption;
use App\Models\Adoption\AdoptionStock;
use App\Models\Adoption\AdoptionCurrency;

class AdoptionService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Adoption Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of adoptions and adoption stock.
    |
    */

    /**********************************************************************************************
     
        ADOPTIONS

    **********************************************************************************************/
    
    /**
     * Updates a adoption.
     *
     * @param  \App\Models\Adoption\Adoption  $adoption
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Adoption\Adoption
     */
    public function updateAdoption($adoption, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(Adoption::where('name', $data['name'])->where('id', '!=', $adoption->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateAdoptionData($data, $adoption);

            $image = null;            
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $adoption->update($data);

            if ($adoption) $this->handleImage($image, $adoption->adoptionImagePath, $adoption->adoptionImageFileName);

            return $this->commitReturn($adoption);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Creates adoption stock.
     *
     * @param  \App\Models\Adoption\Adoption  $adoption
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Adoption\Adoption
     */
    public function createAdoptionStock($adoption, $data, $id)
    {
        DB::beginTransaction();

        try {
            if(!$data['cost']) throw new \Exception("The character is missing a cost.");

            $this->populateCosts(array_only($data, ['currency_id', 'cost']), $id);

            {
                $adoption->stock()->create([
                    'adoption_id'           => $adoption->id,
                    'character_id'          => $data['character_id'],
                    'use_user_bank'         => isset($data['use_user_bank']),
                    'use_character_bank'    => isset($data['use_character_bank']),
                ]);
            }

            return $this->commitReturn($adoption);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Updates adoption stock.
     *
     * @param  \App\Models\Adoption\Adoption  $adoption
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Adoption\Adoption
     */
    public function updateAdoptionStock($adoption, $data, $id)
    {

        DB::beginTransaction();

        try {
            if(!$data['cost']) throw new \Exception("The character is missing a cost.");
            if(!$data['currency_id']) throw new \Exception("The character is missing a currency type.");

            $this->populateCosts(array_only($data, ['currency_id', 'cost']), $id);

            $stock = AdoptionStock::find($id);

            $stock->adoption_id = 1;
            $stock->character_id = $data['character_id'];
            $stock->use_user_bank = isset($data['use_user_bank']);
            $stock->use_character_bank = isset($data['use_character_bank']);
            $stock->save();

            return $this->commitReturn($adoption);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a adoption.
     *
     * @param  array                  $data 
     * @param  \App\Models\Adoption\Adoption  $adoption
     * @return array
     */
    private function populateAdoptionData($data, $adoption = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        $data['is_active'] = isset($data['is_active']);
        
        if(isset($data['remove_image']))
        {
            if($adoption && $adoption->has_image && $data['remove_image']) 
            { 
                $data['has_image'] = 0; 
                $this->deleteImage($adoption->adoptionImagePath, $adoption->adoptionImageFileName); 
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Processes currencies for use to buy
     *
     * @param  array                  $data 
     * @param  \App\Models\Adoption\Adoption  $adoption
     * @return array
     */
    private function populateCosts($data, $id) {

        $stocks = AdoptionStock::find($id);
        // Delete existing currencies to prevent overlaps etc
        $stocks->currency()->delete();
        
        if(isset($data['currency_id'])) {
            foreach($data['currency_id'] as $key => $type)
            {
                AdoptionCurrency::create([
                    'stock_id'       => $id,
                    'currency_id' => $type,
                    'cost'   => $data['cost'][$key],
                ]);
            }
        }

    }
}