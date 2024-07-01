<?php namespace App\Services\Item;

use App\Services\Service;
use Illuminate\Http\Request;

use DB;

use App\Services\InventoryManager;
use App\Services\CharacterManager;

use App\Models\Item\Item;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\Character\Character;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Rarity;

class RecruitService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Slot Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of slot type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData()
    {
        return [
            'rarities' => ['0' => 'Select Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses' => ['0' => 'Select Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'subtypes' => ['0' => 'Select Subtype'] + Subtype::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'isMyo' => true
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getTagData($tag)
    {
        //fetch data from DB, if there is no data then set to NULL instead
        $characterData['name'] = isset($tag->data['name']) ? $tag->data['name'] : null;
        $characterData['species_id'] = 1;
        $characterData['subtype_id'] = isset($tag->data['subtype_id']) && $tag->data['subtype_id'] ? $tag->data['subtype_id'] : null;
        $characterData['rarity_id'] = 1;
        $characterData['description'] = isset($tag->data['description']) && $tag->data['description'] ? $tag->data['description'] : null;
        $characterData['parsed_description'] = parse($characterData['description']);
        $characterData['sale_value'] = isset($tag->data['sale_value']) ? $tag->data['sale_value'] : 0;
        //the switches hate true/false, need to convert boolean to binary
        if( isset($tag->data['is_sellable']) && $tag->data['is_sellable'] == "true") { $characterData['is_sellable'] = 1; } else $characterData['is_sellable'] = 0;
        if( isset($tag->data['is_tradeable']) && $tag->data['is_tradeable'] == "true") { $characterData['is_tradeable'] = 1; } else $characterData['is_tradeable'] = 0;
        if( isset($tag->data['is_giftable']) && $tag->data['is_giftable'] == "true") { $characterData['is_giftable'] = 1; } else $characterData['is_giftable'] = 0;
        if( isset($tag->data['is_visible']) && $tag->data['is_visible'] == "true") { $characterData['is_visible'] = 1; } else $characterData['is_visible'] = 0;

        return $characterData;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for DB storage.
     *
     * @param  string  $tag
     * @param  array   $data
     * @return bool
     */
    public function updateData($tag, $data)
    {
        //put inputs into an array to transfer to the DB
        $characterData['name'] = isset($data['name']) ? $data['name'] : null;
        $characterData['species_id'] = 1;
        $characterData['subtype_id'] = isset($data['subtype_id']) && $data['subtype_id'] ? $data['subtype_id'] : null;
        $characterData['rarity_id'] = 1;
        $characterData['description'] = isset($data['description']) && $data['description'] ? $data['description'] : null;
        $characterData['parsed_description'] = parse($characterData['description']);
        $characterData['sale_value'] = isset($data['sale_value']) ? $data['sale_value'] : 0;
        //if the switch was toggled, set true, if null, set false
        $characterData['is_sellable'] = null;
        $characterData['is_tradeable'] = isset($data['is_tradeable']);
        $characterData['is_giftable'] = isset($data['is_giftable']);
        $characterData['is_visible'] = true;

        DB::beginTransaction();

        try {
            //get characterData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($characterData)]);

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

/**
 * Acts upon the item when used from the inventory.
 *
 * @param  \App\Models\User\UserItem  $stacks
 * @param  \App\Models\User\User      $user
 * @param  array                      $data
 * @return bool
 */
public function act($stacks, $user, $data)
{
    DB::beginTransaction();

    try {
        foreach ($stacks as $key => $stack) {
            // Validate ownership
            if ($stack->user_id != $user->id) {
                throw new \Exception("This item does not belong to you.");
            }

            // Attempt to delete the tag item and debit the stack
            if ((new InventoryManager)->debitStack($stack->user, 'Slot Used', ['data' => ''], $stack, $data['quantities'][$key])) {
                for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                    $characterData = $stack->item->tag('recruit')->data;

                    // Set user who is opening the item
                    $characterData['user_id'] = $user->id;

                    // Parse name with weighted random selection
                    if (isset($characterData['name'])) {
                        $characterData['name'] = $this->weightedRandom($characterData['name']);
                    } else {
                        $characterData['name'] = "recruit Slot"; // Default if name data is missing
                    }

                    // Other default values
                    $characterData['transferrable_at'] = null;
                    $characterData['is_myo_slot'] = 1;
                    $characterData['use_cropper'] = 0;
                    $characterData['x0'] = null;
                    $characterData['x1'] = null;
                    $characterData['y0'] = null;
                    $characterData['y1'] = null;
                    $characterData['image'] = null;
                    $characterData['thumbnail'] = null;
                    $characterData['artist_id'][0] = null;
                    $characterData['artist_url'][0] = null;
                    $characterData['designer_id'][0] = null;
                    $characterData['designer_url'][0] = null;
                    $characterData['feature_id'][0] = null;
                    $characterData['feature_data'][0] = null;

                    // Convert 'true' and 'false' as strings to true/null
                    $characterData['is_sellable'] = $stack->item->tag('recruit')->data['is_sellable'] == "true" ? true : null;
                    $characterData['is_tradeable'] = $stack->item->tag('recruit')->data['is_tradeable'] == "true" ? true : null;
                    $characterData['is_giftable'] = $stack->item->tag('recruit')->data['is_giftable'] == "true" ? true : null;
                    $characterData['is_visible'] = $stack->item->tag('recruit')->data['is_visible'] == "true" ? true : null;

                    // Distribute user rewards
                    $charService = new CharacterManager;
                    if ($character = $charService->createCharacter($characterData, $user, true)) {
                        flash('<a href="' . $character->url . '">' . $character->name . '</a> added to your team!')->success();
                    } else {
                        throw new \Exception("Failed to use slot.");
                    }
                }
            }
        }
        return $this->commitReturn(true);
    } catch (\Exception $e) {
        $this->setError('error', $e->getMessage());
    }
    return $this->rollbackReturn(false);
}

/**
 * Performs weighted random selection from an array of names with weights.
 *
 * @param  string  $namesWithWeightsString
 * @return string
 */
private function weightedRandom($namesWithWeightsString)
{
    $weightedNames = [];
    $entries = preg_split('/[\r\n]+/', $namesWithWeightsString, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($entries as $entry) {
        // Check if the entry contains a weight specifier
        if (strpos($entry, '^') !== false) {
            $parts = explode('^', $entry);
            $name = trim($parts[0]);
            $weight = isset($parts[1]) ? (int) trim($parts[1]) : 1;
        } else {
            // Default weight if not specified
            $name = trim($entry);
            $weight = 1;
        }

        for ($i = 0; $i < $weight; $i++) {
            $weightedNames[] = $name;
        }
    }

    // Perform weighted random selection
    $selectedName = $weightedNames[array_rand($weightedNames)];

    return "Recruit " . $selectedName; // Append " Recruit" at the end
}
}
